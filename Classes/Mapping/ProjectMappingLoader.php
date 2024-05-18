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

namespace Atkins\Pagedoctor\Mapping;

use Atkins\Pagedoctor\Mapping\Exceptions\CTypeNotFound;
use Atkins\Pagedoctor\Mapping\Exceptions\DataProcessingNodeNotFound;
use Atkins\Pagedoctor\Mapping\Exceptions\InvalidProjectMappingPath;
use Atkins\Pagedoctor\Mapping\Exceptions\MappingPathNodeMissing;
use Atkins\Pagedoctor\Mapping\Exceptions\PagedoctorDataProcessingIdNotFound;
use Atkins\Pagedoctor\Mapping\Exceptions\ProjectMappingUnloadable;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProjectMappingLoader
{
    public function __construct(
        private readonly FrontendInterface $cache
    ) { }

    private function readCachedProjectMapping(string $mappingPath): ProjectMapping
    {
        $projectMapping = $this->cache->get($this->cacheIdentifier($mappingPath));
        if ($projectMapping === false) {
            $projectMapping = $this->readProjectMappingFromFile($mappingPath);
            $this->cache->set($this->cacheIdentifier($mappingPath), $projectMapping, [], 0);
        }
        return $projectMapping;
    }

    /**
     * @param string $mappingPath
     * @return ProjectMapping
     */
    private function readProjectMappingFromFile(string $mappingPath): ProjectMapping
    {
        if (empty($mappingPath)) {
            throw new InvalidProjectMappingPath('No proper project mapping path found. Did you cleared your cache already?');
        }
        $absolutePath = GeneralUtility::getFileAbsFileName($mappingPath);
        $json = file_get_contents($absolutePath);
        return GeneralUtility::makeInstance(ProjectMapping::class, json_decode($json, true));
    }

    /**
     * Loads the project mapping from the provided mappingPath.
     * @param string $mappingPath EXT: based path to json schema file
     * @return ProjectMapping
     */
    public function loadProjectMappingByPath(string $mappingPath): ProjectMapping
    {
        return $this->readCachedProjectMapping($mappingPath);
    }

    /**
     * Loads the project mapping only by the CType
     * @param string $CType
     * @return ProjectMapping
     */
    public function loadProjectMappingByCType(string $CType): ProjectMapping
    {
        $setup = GeneralUtility::makeInstance(ConfigurationManager::class)->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        if (!in_array($CType, array_keys($setup['tt_content.']))) {
            throw new CTypeNotFound(
                'Content Type "' . $CType . '" does not exist'
            );
        }

        try {
            if (!in_array('dataProcessing.', array_keys($setup['tt_content.'][$CType . '.']))) {
                throw new DataProcessingNodeNotFound(
                    'No dataProcessing node was found for CType ' . $CType
                );
            }

            if (!in_array('1312757633.', array_keys($setup['tt_content.'][$CType . '.']['dataProcessing.']))) {
                throw new PagedoctorDataProcessingIdNotFound(
                    'No Pagedoctor dataProcessing id (1312757633) was found for CType  ' . $CType
                );
            }

            if (!in_array('mapping', array_keys($setup['tt_content.'][$CType . '.']['dataProcessing.']['1312757633.']))) {
                throw new MappingPathNodeMissing(
                    'Mapping node was missing for CType  ' . $CType
                );
            }
        } catch (DataProcessingNodeNotFound|PagedoctorDataProcessingIdNotFound|MappingPathNodeMissing $e) {
            throw new ProjectMappingUnloadable("Mapping for CType $CType unloadable");
        }

        $mappingPath = $setup['tt_content.'][$CType . '.']['dataProcessing.']['1312757633.']['mapping'];

        return $this->loadProjectMappingByPath($mappingPath);
    }

    private function cacheIdentifier(string $mappingPath): string
    {
        return 'pagedoctor-mapping-'.md5($mappingPath);
    }
}