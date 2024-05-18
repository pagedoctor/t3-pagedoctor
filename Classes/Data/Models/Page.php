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
use Atkins\Pagedoctor\Data\Models\Dsl\Typeable;
use Atkins\Pagedoctor\Data\Models\SimpleTypes\Record;

class Page extends AbstractModel
{
    use Typeable;
    use Relations;
    use Inheritance;

    public const TABLE = 'pages';
    protected ?string $stiField = 'doktype';

    protected array $defaultFields = [
        [ "field_type" => "numbers", "config_type" => "input", "column" => "uid", "field_name" => "uid" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "pid", "field_name" => "pid" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "tstamp", "field_name" => "tstamp" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "crdate", "field_name" => "crdate" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "deleted", "field_name" => "deleted" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "hidden", "field_name" => "hidden" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "sorting", "field_name" => "sorting" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "starttime", "field_name" => "starttime" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "endtime", "field_name" => "endtime" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "fe_group", "field_name" => "fe_group" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "editlock", "field_name" => "editlock" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "sys_language_uid", "field_name" => "sys_language_uid" ],
    ];

    public static function initialize(Record $record): Page
    {
        return new self($record, self::newConnectionPool());
    }
}