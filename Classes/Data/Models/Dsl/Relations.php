<?php declare(strict_types=1);

#
# MIT License
#
# Copyright (c) 2024 Colin Atkins
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.
#

namespace Atkins\Pagedoctor\Data\Models\Dsl;

use Atkins\Pagedoctor\Data\Models\AbstractModel;
use Atkins\Pagedoctor\Data\Models\BeGroup;
use Atkins\Pagedoctor\Data\Models\BeUser;
use Atkins\Pagedoctor\Data\Models\Content;
use Atkins\Pagedoctor\Data\Models\Dsl\Relations\BelongsTo;
use Atkins\Pagedoctor\Data\Models\Dsl\Relations\HasMany;
use Atkins\Pagedoctor\Data\Models\Dsl\Relations\HasManyInline;
use Atkins\Pagedoctor\Data\Models\Dsl\Relations\HasManyPolymorphic;
use Atkins\Pagedoctor\Data\Models\Dsl\Relations\HasManyThrough;
use Atkins\Pagedoctor\Data\Models\Dsl\Relations\HasOne;
use Atkins\Pagedoctor\Data\Models\Exceptions\UnallowedReferenceTable;
use Atkins\Pagedoctor\Data\Models\Page;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Collection;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;
use Atkins\Pagedoctor\Data\Models\SysFileReference;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

# Has all macros needed to be appended to model classes.
# Supports 1:n, 1:1 and n:1 relations. As well as TYPO3 specific IRRE relations and polymorphic group relations.
trait Relations
{
    # Macro for 1:n relations at current model.
    # If a field is provided relations are filtered after the column fieldname.
    protected function hasMany(string $className, string $foreignKey = 'uid_foreign', Field $field = null): HasMany
    {
        $foreignTable = constant($className . '::TABLE');
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($foreignTable);

        $whereExpressions = [
            $queryBuilder->expr()->eq(
                "f.$foreignKey",
                $queryBuilder->createNamedParameter(
                    $this->record->getUid(),
                    \PDO::PARAM_INT
                )
            ),
            $queryBuilder->expr()->eq(
                'f.tablenames',
                $queryBuilder->createNamedParameter(
                    self::TABLE,
                    \PDO::PARAM_STR
                )
            )
        ];

        if (!is_null($field)) {
            $whereExpressions[] = $queryBuilder->expr()->eq(
                'f.fieldname',
                $queryBuilder->createNamedParameter(
                    $field->getColumn(),
                    \PDO::PARAM_STR
                )
            );
        }

        $result = $queryBuilder
            ->select('*')
            ->from($foreignTable, 'f')
            ->where(...$whereExpressions)
            ->orderBy('sorting_foreign')->executeQuery();

        $rows = [];
        $rows[$foreignTable] = [];

        while ($row = $result->fetchAssociative()) {
            $rows[$foreignTable][] = new Record($row);
        }

        return new HasMany($rows);
    }

    # Macro for 1:1 relations between models.
    protected function hasOne(string $className, string $foreignKey = 'uid_foreign'): HasOne
    {
        $foreignTable = constant($className . '::TABLE');
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($foreignTable);
        $result = $queryBuilder
            ->select('*')
            ->from($foreignTable, 'f')
            ->where(
                $queryBuilder->expr()->eq(
                    "f.$foreignKey",
                    $queryBuilder->createNamedParameter(
                        $this->record->getUid(),
                        \PDO::PARAM_INT
                    )
                )
            )
            ->setMaxResults(1)
            ->executeQuery();

        $rows = [];
        $rows[$foreignTable] = [];

        while ($row = $result->fetchAssociative()) {
            $rows[$foreignTable][] = new Record($row);
        }

        return new HasOne($rows);
    }

    # Macro for has many through relation at current model.
    protected function hasManyThrough(string $className, string $throughClass): HasManyThrough
    {
        $throughTable = constant($throughClass . '::TABLE');
        $foreignTable = constant($className . '::TABLE');
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($foreignTable);
        $result = $queryBuilder
            ->select('f.*')
            ->from($foreignTable, 'f')
            ->innerJoin(
                'f',
                $throughTable,
                't',
                $queryBuilder->expr()->eq("f.uid", "t.uid_foreign")
            )
            ->where(
                $queryBuilder->expr()->eq('t.uid_local', $queryBuilder->createNamedParameter($this->record->getUid(), \PDO::PARAM_INT)),
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('t.tablenames', $queryBuilder->createNamedParameter($foreignTable, \PDO::PARAM_STR))
                )
            )
            ->orderBy('t.sorting', 'ASC')
            ->executeQuery();

        $rows = [];

        while ($row = $result->fetchAssociative()) {
            $rows[] = new Record($row);
        }

        return new HasManyThrough($rows);
    }

    # Macro to assign many reference within one column.
    protected function hasManyPolymorphic(Field $field): HasManyPolymorphic
    {
        $column = $field->getColumn();
        $tca = $GLOBALS['TCA'][self::TABLE]['types'][$this->stiValue]['columnsOverrides'][$column];
        $config = $tca['config'];
        $allowed = $config['allowed'];

        # Fetch column references list
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $references = $queryBuilder
            ->select('t.'.$column.' as references')
            ->from(self::TABLE, 't')
            ->where(
                $queryBuilder->expr()->in('t.uid', $this->record->getUid())
            )
            ->executeQuery()
            ->fetchOne();

        # No references found
        if (in_array($references, [false, '0', ''])) {
            $references = '';
        }

        $rows = [];

        # Map references to table => ids representation to fetch all records
        foreach ($this->groupGroupReferences($allowed, $references) as $table => $ids) {
            $rows[$table] = [];

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
            $result = $queryBuilder
                ->select('t.*')
                ->from($table, 't')
                ->where(
                    $queryBuilder->expr()->in('t.uid', (new Collection($ids))->map(function($el) {
                        return $el;
                    })->join(','))
                )
                ->executeQuery();

            while ($row = $result->fetchAssociative()) {
                $rows[$table][] = new Record($row);
            }
        }

        return new HasManyPolymorphic($rows);
    }

    # Groups list and table-id-lists
    protected function groupGroupReferences(string $allowedList, string $referencesList): Collection
    {
        if (empty($allowedList) || empty($referencesList)) return new Collection([]);
        $references = (new Collection())->split(',', $referencesList);
        $allowed = (new Collection())->split(',', $allowedList);

        # Non table containing list
        if ($allowed->count() == 1) {
            $groupedRelations = new Collection([
                $allowed->first() => $references->map(function($id) use ($allowed) {
                    if (str_contains($id, '_')) {
                        $parts = explode('_', $id);
                        $id = array_pop($parts);
                        $table = implode('_', $parts);

                        if ($table !== $allowed->first()) {
                            throw new UnallowedReferenceTable('Unallowed reference table detected at ' . self::TABLE . ' record ' . $this->record->getUid());
                        }
                    }

                    return intval($id);
                })
            ]);
        } else {
            $groupedRelations = new Collection([]);

            foreach ($references as $reference) {
                $parts = explode('_', $reference);
                $id = array_pop($parts);
                $table = implode('_', $parts);

                if (!$groupedRelations->key_exists($table)) {
                    $groupedRelations[$table] = new Collection([]);
                }

                $groupedRelations[$table][] = intval($id);
            }
        }

        return $groupedRelations;
    }

    # Macro for querying IRRE-relation at current type model.
    protected function hasManyInline(Field $field): HasManyInline
    {
        $arr = [];
        $column = $field->getColumn();
        $tca = $GLOBALS['TCA'][self::TABLE]['types'][$this->stiValue]['columnsOverrides'][$column];
        $config = $tca['config'];

        $foreignField = $config['foreign_field'];
        $foreignTable = $config['foreign_table'];
        $foreignMatchFieldName = $config['foreign_match_fields']['tx_pagedoctor_fieldname'];
        $foreignTableField = $config['foreign_table_field'];

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $result = $queryBuilder
            ->select('f.*')
            ->from(self::TABLE, 'l')
            ->innerJoin(
                'l',
                $foreignTable,
                'f',
                $queryBuilder->expr()->eq("f.$foreignField", $queryBuilder->quoteIdentifier('l.uid'))
            )
            ->where(
                $queryBuilder->expr()->eq('l.uid', $queryBuilder->createNamedParameter($this->record->getUid(), \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq("f.$foreignTableField", $queryBuilder->createNamedParameter($foreignTable, \PDO::PARAM_STR)),
                $queryBuilder->expr()->eq('f.tx_pagedoctor_fieldname', $queryBuilder->createNamedParameter($foreignMatchFieldName, \PDO::PARAM_STR)),
            )
            ->executeQuery();

        $rows = [];

        while ($row = $result->fetchAssociative()) {
            $rows[] = new Record($row);
        }

        $arr[$foreignTable] = new Collection($rows);

        return new HasManyInline($arr);
    }

    # Macro for n:1 relation. Use this when you want to have direct model to model n:1 relation.
    protected function belongsTo(string $className, string $localKey = 'uid_local', string $foreignKey = 'uid'): BelongsTo
    {
        $foreignTable = constant($className . '::TABLE');
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($foreignTable);
        $result = $queryBuilder
            ->select('*')
            ->from($foreignTable, 'f')
            ->where(
                $queryBuilder->expr()->eq(
                    "f.$foreignKey",
                    $queryBuilder->createNamedParameter(
                        $this->record->getArbitraryValue($localKey),
                        \PDO::PARAM_INT
                    )
                )
            )
            ->setMaxResults(1)
            ->executeQuery();

        $rows = [];
        $rows[$foreignTable] = [];

        while ($row = $result->fetchAssociative()) {
            $rows[$foreignTable][] = new Record($row);
        }

        return new BelongsTo($rows);
    }

    protected static function newConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    # Loads relations into case classes marking a relation.
    public function loadRelations(): AbstractModel
    {
        foreach ($this->fields as $key => $field) {
            if (is_object($field))
                $class = get_class($field);
            else
                $class = $field;

            $this->fields[$key] = match($class) {
                HasManyPolymorphic::class => $field
                    ->with(BeUser::TABLE)
                    ->with(Content::TABLE)
                    ->with(Page::TABLE)
                    ->with(BeGroup::TABLE),
                HasManyInline::class => $field
                    ->with(Content::TABLE),
                HasMany::class => $field
                    ->with(Content::TABLE)
                    ->with(SysFileReference::TABLE),
                default => $field
            };

        }

        return $this;
    }

    # Initiates transformation of PHP objects by converting all relations to nested arrays.
    public function transform($level = 0): AbstractModel
    {
        $this->beforeFilter();

        foreach ($this->fields as $key => $field) {
            if (is_object($field))
                $class = get_class($field);
            else
                $class = $field;

            $this->fields[$key] = match($class) {
                HasManyPolymorphic::class,
                HasManyInline::class,
                HasMany::class => $field->convert($level),
                default => $field
            };

        }

        return $this;
    }
}