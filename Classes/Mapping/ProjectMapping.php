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

namespace Atkins\Pagedoctor\Mapping;

use Atkins\Pagedoctor\Mapping\ProjectMapping\Type;
use Atkins\Pagedoctor\Mapping\Traits\Mappable;

class ProjectMapping
{
    use Mappable;

    private ?string $name = null;
    private ?string $slug = null;
    private ?string $extension_key = null;
    private ?string $description = null;
    private ?bool $use_view_scaffolding = null;
    private ?array $types = [];

    public function __construct(
        private readonly array $decodedJson
    ) {
        $this->doMapping();
        $this->initializeTypes();
    }

    private function initializeTypes(): void
    {
        $this->types = $this->initializeSubObjects(Type::class, $this->types);
    }

    public function findType(string $CType): Type
    {
        $objects = array_filter($this->types, function(Type $type) use ($CType) {
            return $type->matchesCType($CType);
        });
        return reset($objects);
    }
}