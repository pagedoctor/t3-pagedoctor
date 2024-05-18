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

namespace Atkins\Pagedoctor\Mapping\ProjectMapping;

use Atkins\Pagedoctor\Mapping\Traits\Mappable;

class Field
{
    use Mappable;

    private ?string $field_type = null;
    private ?string $config_type = null;
    private ?string $column = null;
    private ?string $column_fk = null;
    private ?string $field_name = null;
    private ?array $tab = null;
    private ?string $label = null;
    private ?string $description = null;
    private ?bool $use_value_as_label = null;
    private ?array $config = null;
    private ?array $allowed_repeatables = null;
    private ?array $allowed_sources = null;

    public function __construct(
        private readonly array $decodedJson
    ) {
        $this->doMapping();
    }

    public function matchesColumn(string $column): bool
    {
        return $column == $this->column;
    }

    public function getFieldName(): string
    {
        return $this->field_name;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getFieldType(): string
    {
        return $this->field_type;
    }

    public function getConfigType(): string
    {
        return $this->config_type ?? 'text';
    }

    public function isRepeatableAllowed(string $CType): bool
    {
        return in_array($CType, $this->allowed_repeatables);
    }

    public function isSourceAllowed(string $source): bool
    {
        return in_array($source, $this->allowed_sources);
    }

    public function allowedSources(): array
    {
        return $this->allowed_sources;
    }
}