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

use Atkins\Pagedoctor\Data\Models\Exceptions\ColumnNotFoundException;
use Atkins\Pagedoctor\Data\Models\Exceptions\PropertyNotFoundException;
use Atkins\Pagedoctor\Data\Models\Exceptions\VirtualFieldNotFoundException;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Collection;

# Provides various helpers to go through collections of records.
class CollectionProxy
{
    public function __construct(
        protected array $records
    ) {}

    public function length(): int
    {
        return count($this->records);
    }

    public function count(): int
    {
        return $this->length();
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    # Get first record of collection.
    public function first()
    {
        return $this->records[0];
    }

    # Get second record of collection.
    public function second()
    {
        return $this->records[1];
    }

    # Get third record of collection.
    public function third()
    {
        return $this->records[2];
    }

    # Get last record of collection.
    public function last()
    {
        if ($this->length() == 1) return $this->first();
        return $this->records[$this->length()-1];
    }

    # Create a map of current records.
    public function map($func): Collection
    {
        return (new Collection($this->records))->map($func);
    }

    # Pluck just one column / field from the current records.
    public function pluck(string $field): Collection
    {
        return $this->map(function($el) use ($field) {
            try {
                $value = $el->getFieldValue($field);
            } catch (VirtualFieldNotFoundException $e) {
                try {
                    $value = $el->getRecord()->getArbitraryValue($field);
                } catch (ColumnNotFoundException $e) {
                    $fieldList = $el->getFieldNames()->merge($el->getRecord()->data_keys())->join(", ");
                    throw new PropertyNotFoundException("Property not found. Did you mean:\n$fieldList");
                }
            }

            return $value;
        });
    }

    # Get all uids of the current records.
    public function uids($field = 'uid'): Collection
    {
        return $this->pluck($field);
    }

    # Convert the collection proxies records to array.
    public function toArray(): array
    {
        return $this->records;
    }
}