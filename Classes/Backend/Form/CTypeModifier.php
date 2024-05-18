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

namespace Atkins\Pagedoctor\Backend\Form;

use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;
use Atkins\Pagedoctor\Mapping\ProjectMappingLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CTypeModifier extends ParentModifier
{
    /**
     * Check whether the current content row is an inline repeatable.
     * @param $params
     * @return bool
     */
    protected function isRepeatableRow($params): bool
    {
        return in_array(
            true,
            array_map(
                function($inlineParentFieldName) use ($params) {
                    return str_starts_with($params['inlineParentFieldName'], $inlineParentFieldName);
                },
                ['tx_pagedoctor_embed_', 'tx_pagedoctor_reference_']
            )
        );
    }

    public function process(array &$params, $parentObj): void
    {
        $this->record = GeneralUtility::makeInstance(Record::class, $params['row']);

        // if this tt_content element is inline element of mask
        if ($this->record->isRepeatableContent()
            && $this->isRepeatableRow($params)
        ) {
            $projectMappingLoader = GeneralUtility::makeInstance(ProjectMappingLoader::class);
            // check if for this CType a field definition exists in the project
            // if not remove it from the list of possible repeatables to include
            foreach ($params['items'] as $itemKey => $item) {
                $CType = $item['value'];
                // Remove non Pagedoctor CTypes by default
                if (!str_starts_with($CType, 'pagedoctor'))
                    unset($params['items'][$itemKey]);
                // When parent CType is present in TCA
                if (isset($params['inlineParentConfig']) && isset($params['inlineParentConfig']['parent_ctype'])) {
                    $projectMapping = $projectMappingLoader->loadProjectMappingByCType($params['inlineParentConfig']['parent_ctype']);
                    $type = $projectMapping->findType($params['inlineParentConfig']['parent_ctype']);
                    $field = $type->getFieldByColumn($params['inlineParentFieldName']);
                    if (!$field->isRepeatableAllowed($CType))
                        unset($params['items'][$itemKey]);
                }
            }
        }
    }
}
