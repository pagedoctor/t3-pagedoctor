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
use Atkins\Pagedoctor\Data\Models\Dsl\Relations\CollectionProxy;
use Atkins\Pagedoctor\Data\Models\Exceptions\RecordNotFound;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;

trait Queries
{
    public static function find($id, $primaryKeyColumn = 'uid', $overrideQueryBuilder = null): AbstractModel
    {
        $queryBuilder = self::unscoped();

        if (!is_null($overrideQueryBuilder)) $queryBuilder = $overrideQueryBuilder;

        $whereExpressions = [
            $queryBuilder->expr()->eq(
                "t.$primaryKeyColumn",
                $queryBuilder->createNamedParameter(
                    $id,
                    \PDO::PARAM_INT
                )
            ),
        ];

        $result = $queryBuilder
            ->select('*')
            ->from(self::TABLE, 't')
            ->where(...$whereExpressions)
            ->setMaxResults(1)
            ->executeQuery();

        $rows = [];

        while ($row = $result->fetchAssociative()) {
            $rows[] = call_user_func(get_called_class() . "::initialize", new Record($row));
        }

        $collectionProxy = new CollectionProxy($rows);

        if ($collectionProxy->isEmpty())
            throw new RecordNotFound('Record not found for table ' .  self::TABLE . ' with id ' . $id);

        return $collectionProxy->first();
    }
}