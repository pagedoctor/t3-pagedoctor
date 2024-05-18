<?php

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

defined('TYPO3') or die('Access denied.');

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
    ExtensionManagementUtility::registerPageTSConfigFile(
        'pagedoctor',
        'Configuration/TSconfig/Page/Statics/RemoveAllFceElements.tsconfig',
        'Pagedoctor: Remove wizards | All'
    );
    ExtensionManagementUtility::registerPageTSConfigFile(
        'pagedoctor',
        'Configuration/TSconfig/Page/Statics/RemoveCommonFceElements.tsconfig',
        'Pagedoctor: Remove wizards | Only common'
    );
    ExtensionManagementUtility::registerPageTSConfigFile(
        'pagedoctor',
        'Configuration/TSconfig/Page/Statics/RemoveFormsFceElements.tsconfig',
        'Pagedoctor: Remove wizards | Only forms'
    );
    ExtensionManagementUtility::registerPageTSConfigFile(
        'pagedoctor',
        'Configuration/TSconfig/Page/Statics/RemoveMenuFceElements.tsconfig',
        'Pagedoctor: Remove wizards | Only menus'
    );
    ExtensionManagementUtility::registerPageTSConfigFile(
        'pagedoctor',
        'Configuration/TSconfig/Page/Statics/RemovePluginsFceElements.tsconfig',
        'Pagedoctor: Remove wizards | Only plugins'
    );
    ExtensionManagementUtility::registerPageTSConfigFile(
        'pagedoctor',
        'Configuration/TSconfig/Page/Statics/RemoveSpecialFceElements.tsconfig',
        'Pagedoctor: Remove wizards | Only specials'
    );
});