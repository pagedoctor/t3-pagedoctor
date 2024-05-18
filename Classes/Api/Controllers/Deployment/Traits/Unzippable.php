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

namespace Atkins\Pagedoctor\Api\Controllers\Deployment\Traits;

use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\DeploymentUnextractableException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\ScaffoldZipExtensionException;

trait Unzippable
{
    protected function loadExtensions(): void
    {
        if (!extension_loaded('zip')) {
            throw new ScaffoldZipExtensionException('ZIP extension missing');
        }
    }

    /**
     * @return void
     * @throws DeploymentUnextractableException
     */
    protected function cancelIfNonExtractable(): void
    {
        if ($this->deployment->isExtractable() === false)
            throw new DeploymentUnextractableException('Skipped unextractable deployment');
    }

    protected function getNonSkipList(): array
    {
        $list = array();
        for ($idx = 0; $idx < $this->zip->numFiles; $idx++) {
            $path = $this->zip->getNameIndex($idx);
            if (!$this->shouldFileBeRemovedFromList($path)) {
                array_push($list, $path);
            }
        }
        return $list;
    }

    /**
     * Checks whether a file exists and if it should be skipped for extraction.
     * @param $path
     * @return bool
     */
    protected function shouldFileBeRemovedFromList($path): bool {
        $absolutePath = join('/', [
            $this->deployment::getRootPath(),
            $path
        ]);

        /***
         * Always skip files and folders which exist.
         */
        if (file_exists($absolutePath)) {
            return true;
        }

        /**
         * Always skip existing folders.
         */
        if (is_dir($absolutePath)) {
            return true;
        }

        /**
         * Extract only non existing files.
         */
        return false;
    }
}