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
use Atkins\Pagedoctor\Data\Models\Dsl\Relations;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;
use Atkins\Pagedoctor\Data\Models\Dsl\Typeable;

class SysFileReference extends AbstractModel
{
    use Typeable;
    use Relations;
    use Inheritance;

    public const TABLE = 'sys_file_reference';
    protected ?string $stiField = 'type';

    protected array $defaultFields = [
        [ "field_type" => "numbers", "column" => "uid", "field_name" => "uid" ],
        [ "field_type" => "numbers", "column" => "pid", "field_name" => "pid" ],
        [ "field_type" => "numbers", "column" => "tstamp", "field_name" => "tstamp" ],
        [ "field_type" => "numbers", "column" => "crdate", "field_name" => "crdate" ],
        [ "field_type" => "numbers", "column" => "deleted", "field_name" => "deleted" ],
        [ "field_type" => "numbers", "column" => "hidden", "field_name" => "hidden" ],
        [ "field_type" => "numbers", "column" => "sys_language_uid", "field_name" => "sys_language_uid" ],
        [ "field_type" => "numbers", "column" => "uid_local", "field_name" => "uid_local" ],
        [ "field_type" => "numbers", "column" => "uid_foreign", "field_name" => "uid_foreign" ],
        [ "field_type" => "text", "column" => "tablenames", "field_name" => "tablenames" ],
        [ "field_type" => "text", "column" => "fieldname", "field_name" => "fieldname" ],
        [ "field_type" => "numbers", "column" => "sorting_foreign", "field_name" => "sorting_foreign" ],
        [ "field_type" => "text", "column" => "title", "field_name" => "title" ],
        [ "field_type" => "text", "column" => "description", "field_name" => "description" ],
        [ "field_type" => "text", "column" => "alternative", "field_name" => "alternative" ],
        [ "field_type" => "text", "column" => "link", "field_name" => "link" ],
        [ "field_type" => "text", "column" => "crop", "field_name" => "crop" ],
        [ "field_type" => "numbers", "column" => "autoplay", "field_name" => "autoplay" ],
    ];

    public static function initialize(Record $record): SysFileReference
    {
        return new self($record, self::newConnectionPool());
    }
}