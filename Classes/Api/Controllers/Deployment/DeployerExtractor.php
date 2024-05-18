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

namespace Atkins\Pagedoctor\Api\Controllers\Deployment;

use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\DeploymentUnextractableException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\ScaffoldZipOpeningException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Model\AbstractDeployment;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Model\Scaffold;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Traits\Unzippable;

final class DeployerExtractor {

    use Unzippable;

    private AbstractDeployment $deployment;
    private \ZipArchive $zip;

    private function beforeFilter(): void
    {
        $this->loadExtensions();
        $this->cancelIfNonExtractable();
    }

    public function extractDeployment(AbstractDeployment $deployment): void
    {
        $this->deployment = $deployment;
        $this->zip = new \ZipArchive();

        try {
            $this->beforeFilter();

            if ($this->zip->open($this->deployment->getTargetPath()) !== true)
                throw new ScaffoldZipOpeningException('Failed opening scaffold zip file at ' . $this->deployment->getTargetPath());

            if (!empty($this->getNonSkipList())) {
                $this->zip->extractTo(Scaffold::getRootPath(), $this->getNonSkipList());
            }

            $this->zip->close();
            $this->deployment->unlink();
        } catch (DeploymentUnextractableException $e) {
            return;
        }
    }
}