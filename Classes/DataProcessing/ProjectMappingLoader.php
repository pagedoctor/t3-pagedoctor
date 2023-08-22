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

use http\Exception\InvalidArgumentException;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProjectMappingLoader implements SingletonInterface
{

    private array $registry = [];


    /**
     * Loads the ProjectMapping.json file from disk.
     * @param string $mappingPath unresolved path to mapping
     * @return \stdClass
     * @throws \TYPO3\CMS\Core\Package\Exception
     */
    private function loadMappingFromPath(string $mappingPath): \stdClass
    {
        # TODO: add caching here
        $absolutePath = ExtensionManagementUtility::resolvePackagePath($mappingPath);
        $json = file_get_contents($absolutePath);
        return json_decode($json);
    }

    /**
     * Fetches the field definition by CType.
     * @param string $CType
     * @return \stdClass
     * @throws \TYPO3\CMS\Core\Package\Exception
     */
    public function findFieldDefinition(string $CType): \stdClass
    {
        $mappingPath = $this->resolveMappingPathByCType($CType);

        if (!array_key_exists($mappingPath, $this->registry)) {
            $this->registry[$mappingPath] = $this->loadMappingFromPath($mappingPath);
        }

        return $this->filterFieldDefinitions($mappingPath, $CType);
    }

    private function resolveMappingPathByCType(string $CType)
    {
        $setup = GeneralUtility::makeInstance(ConfigurationManager::class)->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        $typoscriptObjectPath = 'tt_content.' . $CType;
        $pathSegments = GeneralUtility::trimExplode('.', $typoscriptObjectPath);
        $lastSegment = (string)array_pop($pathSegments);

        foreach ($pathSegments as $segment) {
            if (!array_key_exists($segment . '.', $setup)) {
                throw new \Exception(
                    'Content Type "' . $typoscriptObjectPath . '" does not exist',
                    1253191023
                );
            }
            $setup = $setup[$segment . '.'];
        }

        return $setup[$lastSegment . '.']['dataProcessing.']['1312757633.']['mapping'];
    }

    private function filterFieldDefinitions(string $mapping, string $CType): \stdClass
    {
        foreach ($this->registry[$mapping]->types as $type) {
            if ($type->CType == $CType) {
                return $type;
            }
        }
        throw new InvalidArgumentException("Unknown type requested $CType");
    }
}