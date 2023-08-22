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

use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FieldHelpers
{
    /**
     * Maps a tt_content record to the Pagedoctor field definition.
     * @param array $record
     * @param array $fieldDefinition
     * @return array
     */
    public static function mapRecordToFields(array $record, \stdClass $fieldDefinition)
    {
        $mappedFields = [];

        $uid = $record['uid'];
        if ($record['_LOCALIZED_UID'] ?? false) {
            $uid = $record['_LOCALIZED_UID'];
        } elseif ($record['_PAGES_OVERLAY_UID'] ?? false) {
            $uid = $record['_PAGES_OVERLAY_UID'];
        }

        // using is_numeric in favor to is_int
        // due to some rare cases where uids are provided as strings
        if (!is_numeric($uid)) {
            return;
        }

        // Cast to int for findByRelation call
        $uid = (int)$uid;

        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);

        foreach ($fieldDefinition->fields as $field) {
            foreach (array_keys($record) as $ttContentColumn) {
                if ($field->column == $ttContentColumn) {
                    if ($field->field_type == 'assets') {
                        $mappedFields[$field->field_name] = $fileRepository->findByRelation('tt_content', $field->column, $uid);
                    } else {
                        $mappedFields[$field->field_name] = $record[$ttContentColumn];
                    }
                }
            }
        }

        return $mappedFields;
    }
}