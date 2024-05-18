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

namespace Atkins\Pagedoctor\Data\Models\Dsl;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DefaultRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait Scopes
{
    # Used for default queries not concerning workspaces and frontend groups.
    public static function defaultScope(): QueryBuilder
    {
        $queryBuilder = self::newConnectionPool()->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->setRestrictions(
            GeneralUtility::makeInstance(DefaultRestrictionContainer::class)
        );
        return $queryBuilder;
    }

    # Used for any frontend related query.
    public static function frontendScope(): QueryBuilder
    {
        $queryBuilder = self::newConnectionPool()->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->setRestrictions(
            GeneralUtility::makeInstance(FrontendRestrictionContainer::class)
        );
        return $queryBuilder;
    }

    # Used for backend queries with no restrictions except deleted records.
    public static function unscoped(): QueryBuilder
    {
        $connectionPool = self::newConnectionPool();
        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll()->add(
            GeneralUtility::makeInstance(DeletedRestriction::class)
        );
        return $queryBuilder;
    }
}