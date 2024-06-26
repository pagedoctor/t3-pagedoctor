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

namespace Atkins\Pagedoctor\Api\Controllers\Deployment\Model;

use TYPO3\CMS\Core\Core\Environment;

final class Scaffold extends AbstractDeployment
{
    public static function getRootPath(): string
    {
        return implode('/', [
            Environment::getProjectPath(),
            'local_packages'
        ]);

    }

    public static function getRandomFilename(): string
    {
        return implode('.', [
            'scaffold',
            uniqid(),
            'zip'
        ]);

    }

    public function isExtractable(): bool
    {
        return true;
    }

    public function unlink(): bool
    {
        return unlink($this->getTargetPath());
    }
}