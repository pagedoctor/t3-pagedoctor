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

namespace Atkins\Pagedoctor\Data\Models\Dsl\Relations;

use Atkins\Pagedoctor\Data\Models\AbstractModel;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Collection;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;
use Atkins\Pagedoctor\Mapping\Exceptions\ProjectMappingUnloadable;

abstract class AbstractRelation extends CollectionProxy
{
    # Converts Records into PHP objects.
    public function with($table)
    {
        if (!array_key_exists($table, $this->records)) return $this;

        $this->records[$table] = (new Collection($this->records[$table]))->map(function($record) use ($table) {
            if (!is_a($record, Record::class)) return $record;
            return $this->mapRowToObject($table, $record);
        });

        return $this;
    }

    # Turns database row into PHP-object.
    protected function mapRowToObject(string $foreignTable, Record $record)
    {
        try {
            return call_user_func_array([AbstractModel::mapTableToClassName($foreignTable), 'initialize'], [$record]);
        } catch (ProjectMappingUnloadable $e) {
            return $record;
        }
    }

    # Initiates converting of relations which lie in objects to a nested arrays for usage in fluid.
    public function convert($level = 0, $maxlevel = 6): array
    {
        $arr = [];

        foreach ($this->records as $table => $objects) {
            $arr[$table] = [];
            foreach ($objects as $object) {
                if (is_a($object, AbstractModel::class)) {
                    if ($level < $maxlevel) {
                        $level++;
                        $object = $object->loadRelations()->transform($level);
                    }
                    $arr[$table][] = $object->toArray();
                } else {
                    $arr[$table][] = $object;
                }

            }
        }

        return $arr;
    }

    # Returns all records
    public function toArray(): array
    {
        return $this->records;
    }
}