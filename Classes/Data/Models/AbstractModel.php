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

namespace Atkins\Pagedoctor\Data\Models;

use Atkins\Pagedoctor\Data\Models\Exceptions\ColumnNotFoundException;
use Atkins\Pagedoctor\Data\Models\Exceptions\TableToClassMappingException;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Collection;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Type;
use TYPO3\CMS\Core\Database\ConnectionPool;

abstract class AbstractModel
{
    protected array $fields = [];

    protected bool $pristine = true;

    abstract public function loadRelations(): AbstractModel;
    abstract public static function initialize(Record $record): AbstractModel;
    abstract protected function loadStiInheritance(): void;
    abstract public function transform(int $level): AbstractModel;

    abstract protected function loadContentVirtualFields(Record $record, array $fields): void;

    protected array $defaultFields = [
        [ "field_type" => "numbers", "config_type" => "input", "column" => "uid", "field_name" => "uid" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "pid", "field_name" => "pid" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "tstamp", "field_name" => "tstamp" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "crdate", "field_name" => "crdate" ],
    ];

    protected ?string $stiField = null;

    protected ?string $stiValue = null;

    public function __construct(
        protected readonly Record $record,
        protected readonly ConnectionPool $connectionPool
    ) {
        try {
            $this->loadStiInheritance();
            $this->loadContentVirtualFields($this->record, $this->getDefaultFields()->toArray());
        } catch(ColumnNotFoundException $e) {
            throw new ColumnNotFoundException('Column ' . $e->getMessage() . ' not found at model ' . get_class($this) . '. Did you forget to add it?');
        }
    }

    protected function beforeFilter()
    {
        if ($this->pristine) {
            $this->loadRelations();

            $this->pristine = false;
        }
    }

    protected function getDefaultFields(): Collection
    {
        return (new Collection($this->defaultFields))->map(function($field) {
            return Type::mapFieldToObject($field);
        });
    }

    public function toArray(): array
    {
        return $this->fields;
    }

    # Map between table and PHP-class.
    public static function mapTableToClassName(string $table)
    {
        switch ($table) {
            case Content::TABLE:
                return Content::class;
            case Page::TABLE:
                return Page::class;
            case SysFile::TABLE:
                return SysFile::class;
            case SysFileMetadata::TABLE:
                return SysFileMetadata::class;
            case SysFileStorage::TABLE:
                return SysFileStorage::class;
            case SysFileReference::TABLE:
                return SysFileReference::class;
            case BeGroup::TABLE:
                return BeGroup::class;
            case BeUser::TABLE:
                return BeUser::class;
        }
        throw new TableToClassMappingException('Table to class mapping missing for: ' . $table);
    }
}