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

namespace Atkins\Pagedoctor\Backend\Preview;

use Atkins\Pagedoctor\DataProcessing\ProjectMappingLoader;
use Atkins\Pagedoctor\DataProcessing\ProjectMappingProcessor;
use Atkins\Pagedoctor\Helpers\FieldHelpers;
use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Fluid\View\StandaloneView;


class PagedoctorContentPreviewRenderer implements PreviewRendererInterface
{
    public function __construct(
        private readonly ProjectMappingProcessor $projectMappingProcessor,
    ) {
    }

    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        return '';
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();

        // Load and map fields.
        $projectMappingLoader = GeneralUtility::makeInstance(ProjectMappingLoader::class);
        $fieldDefinition = $projectMappingLoader->findFieldDefinition($record['CType']);
        $variables = FieldHelpers::mapRecordToFields($record, $fieldDefinition);

        // Determine preview template.
        $tsConfig = BackendUtility::getPagesTSconfig($record['pid'])['mod.']['web_layout.']['tt_content.']['preview.'][$record['CType']] ?? [];
        $fluidTemplateFile = $tsConfig;
        if ($fluidTemplateFile) {
            $fluidTemplateFile = GeneralUtility::getFileAbsFileName($fluidTemplateFile);
        }

        // Load template.
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($fluidTemplateFile);
        $view->assignMultiple([
            'data' => $record,
            'pagedoctor' => $variables
        ]);

        return $view->render();
    }

    public function renderPageModulePreviewFooter(GridColumnItem $item): string
    {
        return '';
    }

    public function wrapPageModulePreview(string $previewHeader, string $previewContent, GridColumnItem $item): string
    {
        return $previewContent;
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}