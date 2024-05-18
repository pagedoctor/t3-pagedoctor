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

class SysFile extends AbstractModel
{
    use Typeable;
    use Relations;
    use Inheritance;

    public const TABLE = 'sys_file';
    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';

    protected array $defaultFields = [
        [ "field_type" => "numbers", "config_type" => "input", "column" => "uid", "field_name" => "uid" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "pid", "field_name" => "pid" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "tstamp", "field_name" => "tstamp" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "last_indexed", "field_name" => "last_indexed" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "missing", "field_name" => "missing" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "storage", "field_name" => "storage" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "type", "field_name" => "type" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "metadata", "field_name" => "metadata" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "identifier", "field_name" => "identifier" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "identifier_hash", "field_name" => "identifier_hash" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "folder_hash", "field_name" => "folder_hash" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "extension", "field_name" => "extension" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "mime_type", "field_name" => "mime_type" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "name", "field_name" => "name" ],
        [ "field_type" => "text", "config_type" => "input", "column" => "sha1", "field_name" => "sha1" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "size", "field_name" => "size" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "creation_date", "field_name" => "creation_date" ],
        [ "field_type" => "numbers", "config_type" => "input", "column" => "modification_date", "field_name" => "modification_date" ],
    ];

    public static function initialize(Record $record): SysFile
    {
        return new self($record, self::newConnectionPool());
    }

    public function identifier(): string
    {
        return $this->record->getArbitraryValue('identifier');
    }

    public function type(): string
    {
        return match($this->record->getArbitraryValue('type')) {
            '2' => self::TYPE_IMAGE,
            '3' => self::TYPE_AUDIO,
            '4' => self::TYPE_VIDEO
        };
    }

    public function isImage(): bool
    {
        return $this->type() === self::TYPE_IMAGE;
    }

    public function isAudio(): bool
    {
        return $this->type() === self::TYPE_AUDIO;
    }

    public function isVideo(): bool
    {
        return $this->type() === self::TYPE_VIDEO;
    }

    protected function loadStiInheritance(): void
    {
        $this->stiValue = '1';
    }
}