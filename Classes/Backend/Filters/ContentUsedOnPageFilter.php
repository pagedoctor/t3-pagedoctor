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

namespace Atkins\Pagedoctor\Backend\Filters;

use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;
use TYPO3\CMS\Backend\View\Event\IsContentUsedOnPageLayoutEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ContentUsedOnPageFilter
{
    public function __invoke(IsContentUsedOnPageLayoutEvent $event): void
    {
        // Get the current record from the event.
        $record = GeneralUtility::makeInstance(Record::class, $event->getRecord());

        // This code will be your domain logic to indicate if content
        // should be hidden in the page module.
        if ($record->isRepeatableContent()
            && $record->anyParentRepeatableReferences()
        ) {
            // Flag the current element as not used. Set it to true, if you
            // want to flag it as used and hide it from the page module.
            $event->setUsed(true);
        }
    }
}