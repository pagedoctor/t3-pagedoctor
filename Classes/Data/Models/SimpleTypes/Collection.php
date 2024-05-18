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

namespace Atkins\Pagedoctor\Data\Models\SimpleTypes;

class Collection extends \ArrayObject
{
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function isNotEmpty(): bool
    {
        return $this->count() !== 0;
    }

    public function first()
    {
        return $this->getArrayCopy()[0];
    }

    public function second()
    {
        return $this->getArrayCopy()[1];
    }

    public function third()
    {
        return $this->getArrayCopy()[2];
    }

    public function last()
    {
        return $this->getArrayCopy()[count($this->getArrayCopy())-1];
    }

    public function map($func): Collection
    {
        return new self(array_map(function($el) use ($func) {
            return $func($el);
        }, $this->getArrayCopy()));
    }

    public function filter($func): Collection
    {
        return new self(array_filter($this->getArrayCopy(), function($el) use ($func) {
            return $func($el);
        }));
    }

    public function sort($func): Collection
    {
        $array = $this->getArrayCopy();
        usort($array, $func);
        return new Collection($array);
    }

    public function merge(Collection $collection): Collection
    {
        return new Collection(array_merge((array) $this, (array) $collection));
    }

    public function join(string $separator = ''): string
    {
        return implode($separator, $this->getArrayCopy());
    }

    public function split(string $separator, string $string): Collection
    {
        $chunks = array_filter(explode($separator, $string),'strlen');
        return new Collection($chunks);
    }

    public function key_exists(string $key): bool
    {
        return array_key_exists($key, $this->getArrayCopy());
    }

    public function includes($value): bool
    {
        return in_array($value, $this->getArrayCopy());
    }

    public static function fromString(array $input): Collection
    {
        return new self($input);
    }

    public function toArray(): array
    {
        return $this->getArrayCopy();
    }
}