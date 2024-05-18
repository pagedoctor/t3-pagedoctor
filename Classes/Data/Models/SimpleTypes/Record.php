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

namespace Atkins\Pagedoctor\Data\Models\SimpleTypes;

use Atkins\Pagedoctor\ColumnNotFoundException;
use Atkins\Pagedoctor\Pagedoctor;
use TYPO3\CMS\Backend\Utility\BackendUtility;

final class Record
{
    // wrapper class for record array

    public function __construct(
        private readonly array $data
    ) {}

    public function getUid(): int
    {
        return intval($this->data['uid']);
    }

    public function colPos(): int
    {
        return intval($this->data['colPos'] ?? 0);
    }

    public function getCType(): string
    {
        return (string) $this->data['CType'];
    }

    public function getArbitraryValue($column)
    {
        if (!array_key_exists($column, $this->data)) {
            throw new \Atkins\Pagedoctor\Data\Models\Exceptions\ColumnNotFoundException($column);
        }
        return $this->data[$column];
    }

    public function isRepeatableContent(): bool
    {
        return $this->colPos() === Pagedoctor::EMBEDS_COLPOS;
    }

    public function anyParentRepeatableReferences(): bool
    {
        return in_array(true, array_map(function($key){
            return str_starts_with($key, 'tx_pagedoctor_embed_parent_') && intval($this->data[$key]) > 0;
        }, array_keys($this->data)));
    }

    public function getPreviewTemplateFilePath(): string
    {
        return BackendUtility::getPagesTSconfig($this->data['pid'])['mod.']['web_layout.']['tt_content.']['preview.'][$this->getCType()] ?? '';
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function data_keys(): Collection
    {
        return new Collection(array_keys($this->data));
    }
}