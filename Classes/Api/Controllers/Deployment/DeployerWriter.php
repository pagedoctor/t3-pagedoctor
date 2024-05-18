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

use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\DeploymentChecksumMismatchException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\DeploymentExistsException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Exceptions\DeploymentPutException;
use Atkins\Pagedoctor\Api\Controllers\Deployment\Model\AbstractDeployment;

final class DeployerWriter {
    public function storeDeployment(AbstractDeployment $deployment): void
    {
        $binary = base64_decode($deployment->getZipContents());
        $tempfile = tmpfile();
        $tempfilePath = stream_get_meta_data($tempfile)['uri'];
        file_put_contents($tempfilePath, $binary);
        $calculatedChecksum = hash_file('sha256', $tempfilePath);

        if ($calculatedChecksum !== $deployment->getChecksum())
            throw new DeploymentChecksumMismatchException('Received deployment contents didn\'t match its transferred checksum');

        if (!file_exists($deployment->getTargetPath())) {
            $result = file_put_contents($deployment->getTargetPath(), $binary);
            if ($result === false)
                throw new DeploymentPutException('Failed putting deployment to disk at ' . $deployment->getTargetPath());
        } else {
            throw new DeploymentExistsException('Deployment already exists at ' . $deployment->getTargetPath());
        }
    }
}