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

namespace Atkins\Pagedoctor\Backend\Preview;

use Atkins\Pagedoctor\Backend\Preview\Exceptions\MissingPagedoctorType;
use Atkins\Pagedoctor\Backend\Preview\Exceptions\PrimaryKeyInvalid;
use Atkins\Pagedoctor\Backend\Preview\Exceptions\PrimaryKeyMissing;
use Atkins\Pagedoctor\Data\Models\AbstractModel;
use Atkins\Pagedoctor\Data\Models\Exceptions\RecordNotFound;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Collection;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;
use Atkins\Pagedoctor\Mapping\Exceptions\CTypeNotFound;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Html\SanitizerBuilderFactory;
use TYPO3\CMS\Core\Html\SanitizerInitiator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\HtmlSanitizer\Builder\BuilderInterface;
use TYPO3\HtmlSanitizer\Sanitizer;

class LabelRenderer
{

    private array $parameters;
    private AbstractModel $model;
    private Record $record;

    private function beforeFilter(): void
    {
        $this->prechecks();
        $this->loadRecord();
        $this->loadModel();
    }

    private function prechecks(): void
    {
        # We need a records primary key.
        if (!is_array($this->parameters) || !array_key_exists('row', $this->parameters) || is_null($this->parameters['row']) || !array_key_exists('uid', $this->parameters['row'])) {
            throw new PrimaryKeyMissing('No primary key found for label rendering');
        }

        # Unsaved records can not be processed.
        $uid = $this->parameters['row']['uid'];
        if (!is_numeric($uid) && str_starts_with($uid, 'NEW')) {
            throw new PrimaryKeyInvalid('Record was new and not saved');
        }

        # Non Pagedoctor content elements are always resolved by default fields.
        $ctype = $this->parameters['row']['CType'];
        if (is_string($ctype)) {
            $ctype = [
                $this->parameters['row']['CType']
            ];
        }
        if (in_array(true, array_map(function($ctype) {
            return !str_starts_with($ctype, 'pagedoctor');
        }, $ctype))) {
            throw new MissingPagedoctorType('No CType was found at parameters');
        }
    }

    private function loadRecord(): void
    {
        $row = BackendUtility::getRecord($this->parameters['table'], $this->parameters['row']['uid']);
        if (is_null($row)) {
            throw new RecordNotFound();
        }
        $this->record = new Record($row);
    }

    private function loadModel(): void
    {
        $clazz = AbstractModel::mapTableToClassName($this->parameters['table']);
        $this->model = call_user_func($clazz . '::find', $this->parameters['row']['uid']);
    }

    public function render(&$parameters): void
    {
        $this->parameters = $parameters;

        try {
            $this->beforeFilter();

            # Fetch type of model and create list of columns to fetch values from.
            $type = $this->model->getType();

            # Look for user defined fields which have been marked to be used as label.
            $columnList = implode(
                ',',
                array_filter(
                    array_map(
                        function($field) {
                            if (isset($field->use_value_as_label) && $field->use_value_as_label) {
                                return $field->column;
                            }
                        },
                        $type->getFields()
                    )
                )
            );

            # Resolve by user defined labels.
            if (strlen($columnList) > 0) {
                $parameters['title'] = $this->loadListOfColumns($columnList);
                return;
            }

            # Pick first textual column.
            $firstTextualValue = $this->loadListOfColumns('tx_pagedoctor_text_1');
            if (strlen($firstTextualValue)) {
                $parameters['title'] = $firstTextualValue;
                return;
            }

            # When none have been marked and no textual value found, pick the label of the type, followed by the uid.
            $parameters['title'] = implode(
                ' ',
                [
                    $type->getName(),
                    '#' . $this->record->getUid()
                ]
            );
        } catch (PrimaryKeyMissing|PrimaryKeyInvalid $e) {
            $parameters['title'] = '';
            return;
        } catch (MissingPagedoctorType|CTypeNotFound|RecordNotFound $e) {
            return;
        }
    }

    private function resolveByDefaultLabels(): string
    {
        $label = $this->loadListOfColumns($GLOBALS['TCA'][$this->parameters['table']]['ctrl']['label']);
        if (strlen($label)) {
            return $label;
        }
        return $this->loadListOfColumns($GLOBALS['TCA'][$this->parameters['table']]['ctrl']['label_alt']);
    }

    private function loadListOfColumns($columnList): string
    {
        $columns = [];
        $list = (new Collection())->split(',', $columnList)->toArray();

        foreach ($list as $column) {
            $value = (string) $this->record->getArbitraryValue($column);
            $sanitizedValue = trim(strip_tags(self::createSanitizer('default')->sanitize($value, self::createInitiator())));
            if (strlen($sanitizedValue)) {
                $columns[] = $sanitizedValue;
            }
        }

        return implode(', ', $columns);
    }

    private static function createInitiator(): SanitizerInitiator
    {
        return GeneralUtility::makeInstance(SanitizerInitiator::class, self::class);
    }

    private static function createSanitizer(string $build): Sanitizer
    {
        if (class_exists($build) && is_a($build, BuilderInterface::class, true)) {
            $builder = GeneralUtility::makeInstance($build);
        } else {
            $factory = GeneralUtility::makeInstance(SanitizerBuilderFactory::class);
            $builder = $factory->build($build);
        }
        return $builder->build();
    }
}