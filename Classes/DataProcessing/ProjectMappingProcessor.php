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

namespace Atkins\Pagedoctor\DataProcessing;

use Atkins\Pagedoctor\Helpers\FieldHelpers;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class ProjectMappingProcessor implements DataProcessorInterface
{
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'pagedoctor');
        $record = $processedData['data'];

        // Load project mapping field definition for this content type.
        $projectMappingLoader = GeneralUtility::makeInstance(ProjectMappingLoader::class);
        $fieldDefinition = $projectMappingLoader->findFieldDefinition($record['CType']);

        // Map records to target template variable.
        $processedData[$targetVariableName] = FieldHelpers::mapRecordToFields($record, $fieldDefinition);

        return $processedData;
    }
}