<?php
defined('TYPO3') or die('Access denied.');

call_user_func(function () {
    $extensionKey = 'pagedoctor';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript/Api',
        'Pagedoctor: Enable Remote API Access'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript/ParseFunc',
        'Pagedoctor: Enable RTE Output (ParseFunc)'
    );

    /**
     * Default
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'Pagedoctor Content Elements'
    );
});
