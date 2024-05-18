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

class SysFileStorage extends AbstractModel
{
    use Typeable;
    use Relations;
    use Inheritance;

    public const TABLE = 'sys_file_storage';

    protected array $defaultFields = [
        [ "field_type" => "numbers", "config_type" => "input", "column" => "uid", "field_name" => "uid" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "pid", "field_name" => "pid" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "tstamp", "field_name" => "tstamp" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "crdate", "field_name" => "crdate" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "deleted", "field_name" => "deleted" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "description", "field_name" => "description" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "name", "field_name" => "name" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "driver", "field_name" => "driver" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "configuration", "field_name" => "configuration" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "is_default", "field_name" => "is_default" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "is_browsable", "field_name" => "is_browsable" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "is_public", "field_name" => "is_public" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "is_writable", "field_name" => "is_writable" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "is_online", "field_name" => "is_online" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "auto_extract_metadata", "field_name" => "auto_extract_metadata" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "processingfolder", "field_name" => "processingfolder" ],
    ];

    public static function initialize(Record $record): SysFileStorage
    {
        return new self($record, self::newConnectionPool());
    }

    protected function loadStiInheritance(): void
    {
        $this->stiValue = '0';
    }
}