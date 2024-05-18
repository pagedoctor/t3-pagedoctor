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

use Atkins\Pagedoctor\Data\Models\Exceptions\UndefinedConfigTypeException;
use Atkins\Pagedoctor\Data\Models\Exceptions\UndefinedFieldTypeDetectedException;
use Atkins\Pagedoctor\Data\Models\Exceptions\VirtualFieldNotFoundException;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Collection;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;
use Atkins\Pagedoctor\Data\Models\SysFileReference;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Field;

trait Typeable
{
    # Loads types field values into private array.
    protected function loadContentVirtualFields(Record $record, array $fields): void
    {
        foreach ($fields as $field) {
            $fieldName = $field->getFieldName();
            $this->fields[$fieldName] = $this->getValueForFieldType($record, $field);
        }
    }

    # For internal use only. Use $model->getFieldValue() instead.
    # @internal
    protected function getValueForFieldType(Record $record, Field $field)
    {
        $fieldClassNamespace = 'Atkins\Pagedoctor\Mapping\ProjectMapping\Field';
        switch (get_class($field)) {
            case "$fieldClassNamespace\AssetsField":
            case "$fieldClassNamespace\NumbersField":
            case "$fieldClassNamespace\PalettesField":
            case "$fieldClassNamespace\EmbedsField":
            case "$fieldClassNamespace\ReferencesField":
            case "$fieldClassNamespace\RichtextField":
            case "$fieldClassNamespace\TextField":
            case "$fieldClassNamespace\ValuelistField":
                return $this->typesafe($record, $field);
            default:
                $undefinedFieldTypeClass = get_class($field);
                throw new UndefinedFieldTypeDetectedException("Undefined field type $undefinedFieldTypeClass");
        }
    }

    # Get a models field value. Use this to access the models field values.
    public function getFieldValue(string $fieldName)
    {
        $this->beforeFilter();

        if (!array_key_exists($fieldName, $this->fields)) {
            throw new VirtualFieldNotFoundException('Field with name ' . $fieldName . ' not found in virtual fields');
        }
        return $this->fields[$fieldName];
    }

    # Set a models field values.
    public function setFieldValue(string $fieldName, $value): void
    {
        $this->beforeFilter();

        // TBD.
    }

    # Get a collection of all field names.
    public function getFieldNames(): Collection
    {
        return new Collection(array_keys($this->fields));
    }

    # Converts the field value into simple type or complex types.
    protected function typesafe(Record $record, Field $field)
    {
        $value = $record->getArbitraryValue($field->getColumn());
        $configType = $field->getConfigType();
        return match ($configType) {
            'input', 'text', 'link', 'ckeditor', 'trix' => strval($value),
            'integer', 'select', 'checkbox', 'radio' => intval($value),
            'date', 'datetime', 'time' => intval($value),
            'decimal' => floatval($value),
            'image', 'assets' => $this->hasMany(SysFileReference::class, 'uid_foreign', $field),
            'inline' => $this->hasManyInline($field),
            'group' => $this->hasManyPolymorphic($field),
            default => throw new UndefinedConfigTypeException("Undefined configtype $configType"),
        };
    }
}