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

namespace Atkins\Pagedoctor\Api\Controllers;

use Atkins\Pagedoctor\Api\Controllers\Deployment\DeployerService;
use Atkins\Pagedoctor\Api\Models\Scaffold;
use Atkins\Pagedoctor\Api\Models\ResponseModel;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ScaffoldController extends ApiController
{
    public function __construct(
        private readonly DeployerService $deployerService
    ) {}

    public function ingress(string $_, array $typoScriptConfiguration, ServerRequest $request): string
    {
        return $this->render($typoScriptConfiguration, $request, Scaffold\Request::class);
    }

    public function execute(): ResponseModel
    {
        $this->deployerService->deploy(
            GeneralUtility::makeInstance(\Atkins\Pagedoctor\Api\Controllers\Deployment\Model\Scaffold::class,
                \Atkins\Pagedoctor\Api\Controllers\Deployment\Model\Scaffold::getRandomFilename(),
                $this->requestModel->checksum,
                $this->requestModel->zip_contents
            )
        );
        return $this->createResponseModel(Scaffold\Response::class);
    }
}