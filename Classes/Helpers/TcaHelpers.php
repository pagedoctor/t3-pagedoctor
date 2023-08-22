<?php

declare(strict_types=1);

/*
 * Copyright 2008-2023 by Colin Atkins.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Atkins\Pagedoctor\Helpers;

use Atkins\Pagedoctor\DataProcessing\ProjectMappingLoader;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Atkins\Pagedoctor\Helpers\Exceptions\UnknownContentTypeException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Html\SanitizerBuilderFactory;
use TYPO3\CMS\Core\Html\SanitizerInitiator;
use TYPO3\HtmlSanitizer\Sanitizer;

class TcaHelpers
{
    const MAXIMUM_TYPE_COLUMNS = 50;

    /**
     * Determines content record labels.
     * @param $parameters
     * @return string
     */
    public function addCtrlLabels(&$parameters): void
    {
        $table = $parameters['table'];
        if (!array_key_exists('uid', $parameters['row'])) {
            $parameters['title'] = '';
            return;
        }
        $uid = $parameters['row']['uid'];
        if (!is_numeric($uid) && str_starts_with($uid, 'NEW')) return;
        $record = BackendUtility::getRecord($table, $uid);
        if (is_null($record)) return;
        $CType = $record['CType'];

        $resolveColumnList = function($columnList) use ($record) {
            $columns = [];

            foreach (explode(',', $columnList) as $column) {
                $value = (string) $record[$column];
                $sanitizedValue = trim(strip_tags(self::createSanitizer('default')->sanitize($value, self::createInitiator())));
                if (strlen($sanitizedValue)) {
                    $columns[] = $sanitizedValue;
                }
            }

            return implode(', ', $columns);
        };

        $resolveByDefaultLabelFields = function() use ($resolveColumnList) {
            $label = $resolveColumnList($GLOBALS['TCA']['tt_content']['ctrl']['label']);
            if (strlen($label)) {
                return $label;
            }
            return $resolveColumnList($GLOBALS['TCA']['tt_content']['ctrl']['label_alt']);
        };

        // Non Pagedoctor content elements are always resolved by default fields.
        if (!str_starts_with($CType, 'pagedoctor')) {
            $parameters['title'] = $resolveByDefaultLabelFields();
            return;
        }


        $projectMappingLoader = GeneralUtility::makeInstance(ProjectMappingLoader::class);
        $fieldDefinition = $projectMappingLoader->findFieldDefinition($CType);

        // Look for fields which have been marked as label.
        $columnList = implode(
            ',',
            array_filter(
                array_map(
                    function($field) {
                        if ($field->use_value_as_label) {
                            return $field->column;
                        }
                    },
                    $fieldDefinition->fields
                )
            )
        );


        // Try resolving label by defined labels.
        if (strlen($columnList) > 0) {
            $parameters['title'] = $resolveColumnList($columnList);
            return;
        }

        // Resolve by default columns when none specified.
        $label = $resolveByDefaultLabelFields();
        if (strlen($label) != 0) {
            $parameters['title'] = $label;
            return;
        }

        // When even default fields contained no content, use first textual field.
        $parameters['title'] = $resolveColumnList('tx_pagedoctor_text_1');
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

    /**
     * Adds columns to TCA ctrl array.
     * @param string $type
     * @return void
     * @throws UnknownContentTypeException
     */
    public static function addCtrlColumns(string $type): void
    {
        switch ($type) {
            case 'text':
                $columns = self::generateTextColumn();
                break;
            case 'integer':
                $columns = self::generateIntegerColumn();
                break;
            case 'asset':
                $columns = self::generateAssetColumn();
                break;
            default:
                throw new UnknownContentTypeException('Specified column type is not known');
        }

        foreach ($columns as $column) {
            ExtensionManagementUtility::addTCAcolumns('tt_content', $column);
        }
    }

    private static function generateRounds(): \Generator
    {
        for ($i = 1; $i <= self::MAXIMUM_TYPE_COLUMNS; $i++) {
            yield $i;
        }
    }

    private static function generateTextColumn(): \Generator
    {
        foreach (self::generateRounds() as $index) {
            yield [
                "tx_pagedoctor_text_{$index}" => [
                    'exclude' => 1,
                    'label' => "text_{$index}",
                    'config' => [
                        'type' => 'text',
                    ],
                ]
            ];
        }
    }

    private static function generateIntegerColumn(): \Generator
    {
        foreach (self::generateRounds() as $index) {
            yield [
                "tx_pagedoctor_integer_{$index}" => [
                    'exclude' => 1,
                    'label' => "integer_{$index}",
                    'config' => [
                        'type' => 'number',
                    ],
                ]
            ];
        }
    }

    private static function generateAssetColumn(): \Generator
    {
        foreach (self::generateRounds() as $index) {
            yield [
                "tx_pagedoctor_asset_{$index}" => [
                    'exclude' => 1,
                    'label' => "asset_{$index}",
                    'config' => [
                        'type' => 'file',
                    ],
                ]
            ];
        }
    }
}