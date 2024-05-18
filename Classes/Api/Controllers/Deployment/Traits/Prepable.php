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

use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\ArtifactsFolderCreationException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\ArtifactsHtaccessFileCreationException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\FailedCreatingArtifactsFolderException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\ScaffoldFolderCreationException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Model\Artifact;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Model\Scaffold;

trait Prepable
{
    protected function createArtifactsFolder(): void
    {
        if (!file_exists(Artifact::getRootPath())) {
            $result = @mkdir(Artifact::getRootPath());
            if ($result !== true) {
                throw new ArtifactsFolderCreationException('Failed setting up artifacts folder');
            }
        }
    }

    protected function createArtifactsHtaccessFile(): void
    {
        $instruction = "order deny,allow\ndeny from all";

        if (!file_exists(Artifact::getHtaccessPath())) {
            @touch(Artifact::getHtaccessPath());
            $result = file_put_contents(Artifact::getHtaccessPath(), $instruction);
            if ($result === false) {
                throw new ArtifactsHtaccessFileCreationException('Failed setting up htaccess file for artifacts folder');
            }
        }
    }

    protected function createScaffoldLocalPackagesFolder(): void
    {
        if (!file_exists(Scaffold::getRootPath())) {
            $result = @mkdir(Scaffold::getRootPath());
            if ($result !== true) {
                throw new ScaffoldFolderCreationException('Failed setting up scaffold root folder');
            }
        }
    }
}