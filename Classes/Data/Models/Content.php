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

namespace Atkins\Pagedoctor\Data\Models;

use Atkins\Pagedoctor\Data\Models\Dsl\Inheritance;
use Atkins\Pagedoctor\Data\Models\Dsl\Queries;
use Atkins\Pagedoctor\Data\Models\Dsl\Relations;
use Atkins\Pagedoctor\Data\Models\Dsl\Scopes;
use Atkins\Pagedoctor\Data\Models\Dsl\Typeable;
use Atkins\Pagedoctor\Data\Models\Exceptions\ColumnNotFoundException;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Collection;
use Atkins\Pagedoctor\Mapping\ProjectMapping\Type;
use Atkins\Pagedoctor\Mapping\ProjectMappingLoader;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;

class Content extends AbstractModel
{
    use Typeable;
    use Relations;
    use Queries;
    use Scopes;
    use Inheritance;

    public const TABLE = 'tt_content';
    protected ?string $stiField = 'CType';

    protected array $defaultFields = [
        [ "field_type" => "numbers", "column" => "uid",              "field_name" => "uid" ],
        [ "field_type" => "numbers", "column" => "pid",              "field_name" => "pid" ],
        [ "field_type" => "numbers", "column" => "tstamp",           "field_name" => "tstamp" ],
        [ "field_type" => "numbers", "column" => "crdate",           "field_name" => "crdate" ],
        [ "field_type" => "numbers", "column" => "deleted",          "field_name" => "deleted" ],
        [ "field_type" => "numbers", "column" => "hidden",           "field_name" => "hidden" ],
        [ "field_type" => "numbers", "column" => "starttime",        "field_name" => "starttime" ],
        [ "field_type" => "numbers", "column" => "endtime",          "field_name" => "endtime" ],
        [ "field_type" => "numbers", "column" => "fe_group",         "field_name" => "fe_group" ],
        [ "field_type" => "numbers", "column" => "sorting",          "field_name" => "sorting" ],
        [ "field_type" => "numbers", "column" => "editlock",         "field_name" => "editlock" ],
        [ "field_type" => "numbers", "column" => "sys_language_uid", "field_name" => "sys_language_uid" ],
        [ "field_type" => "text",    "column" => "CType",            "field_name" => "ctype" ],
    ];

    public function __construct(
        protected readonly Type $type,
        protected readonly Record $record,
        protected readonly ConnectionPool $connectionPool
    ) {
        try {
            $this->loadStiInheritance();
            $this->loadContentVirtualFields($this->record, $this->getDefaultFields()->merge(new Collection($this->type->getFields()))->toArray());
        } catch(ColumnNotFoundException $e) {
            throw new ColumnNotFoundException('Column ' . $e->getMessage() . ' not found at model ' . get_class($this) . '. Did you forget to add it?');
        }
    }

    public static function initialize(Record $record): Content
    {
        $projectMapping = GeneralUtility::makeInstance(ProjectMappingLoader::class)->loadProjectMappingByCType($record->getCType());
        $type = $projectMapping->findType(
            $record->getCType()
        );
        return new self($type, $record, self::newConnectionPool());
    }

    public function getType(): Type
    {
        return $this->type;
    }


}