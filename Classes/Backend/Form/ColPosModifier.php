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
use Atkins\Pagedoctor\Pagedoctor;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ColPosModifier extends ParentModifier
{

    /**
     * Modify the items array to set that only certain Pagedoctor repeatables are allowed within a repeatable field.
     * @param array $params
     * @param $parentObj
     * @return void
     */
    public function process(array &$params, $parentObj): void
    {
        $this->record = GeneralUtility::makeInstance(Record::class, $params['row']);
        $this->filterColPosItems($params);
    }

    /**
     * Modify allowed colPos when is repeatable content.
     * @param array $params
     * @param $parentObj
     * @return void
     */
    private function filterColPosItems(array &$params): void
    {
        // The following allows only the Pagedoctor ColPos in the Dropdown
        if ($this->record->isRepeatableContent()) {
            $params['items'] = [
                [
                    'Pagedoctor Embeds',
                    Pagedoctor::EMBEDS_COLPOS,
                    null,
                    null,
                ],
            ];
        }
    }
}