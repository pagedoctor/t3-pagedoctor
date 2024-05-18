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

use Atkins\Pagedoctor\Mapping\ProjectMapping\Exceptions\FieldNotFound;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Exceptions\FieldTypeUnknown;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field\AssetsField;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field\EmbedsField;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field\NumbersField;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field\PalettesField;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field\ReferencesField;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field\RichtextField;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field\TextField;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field\ValuelistField;
use Atkins\Pagedoctor\Mapping\Traits\Mappable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Type
{
    use Mappable;

    private ?string $CType = null;
    private ?array $tier = null;
    private ?string $name = null;
    private ?string $slug = null;
    private ?string $template_name = null;
    private ?string $description = null;
    /**
     * @var Field[]
     */
    private ?array $fields = null;

    public function __construct(
        private readonly array $decodedJson
    ) {
        $this->doMapping();
        $this->initializeFields();
    }

    private function initializeFields(): void
    {
        $this->fields = array_map(function($field){
            return Type::mapFieldToObject($field);
        }, $this->decodedJson['fields']);
    }

    public static function mapFieldToObject(array $field): AssetsField|EmbedsField|NumbersField|PalettesField|ReferencesField|RichtextField|TextField|ValuelistField
    {
        $fieldType = match($field['field_type']) {
            'text', 'richtext', 'numbers', 'valuelist', 'assets', 'embeds', 'references', 'palettes' => ucfirst($field['field_type']),
            default => throw new FieldTypeUnknown('Field type is unknown ' . $field['field_type'])
        };
        return GeneralUtility::makeInstance('Atkins\\Pagedoctor\\Mapping\\ProjectMapping\\Field\\' . $fieldType . 'Field', $field);
    }

    public function matchesCType($CType): bool
    {
        return $CType == $this->CType;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getFieldByColumn(string $column): Field
    {
        foreach ($this->getFields() as $field) {
            if ($field->getColumn() == $column)
                return $field;
        }
        throw new FieldNotFound("No field definition found for column $column");
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function cType(): string
    {
        return $this->CType;
    }

    public function getName(): string
    {
        return $this->name;
    }
}