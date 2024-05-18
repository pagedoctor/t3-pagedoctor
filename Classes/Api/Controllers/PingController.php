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

use Atkins\Pagedoctor\Api\Models\Ping;
use Atkins\Pagedoctor\Api\Models\ResponseModel;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequest;

final class PingController extends ApiController
{
    public function ingress(string $_, array $typoScriptConfiguration, ServerRequest $request): string
    {
        return $this->render($typoScriptConfiguration, $request, Ping\Request::class);
    }

    public function execute(): ResponseModel
    {
        return $this->createResponseModel(Ping\Response::class, [[
            'version' => \Composer\InstalledVersions::getVersionRanges('pagedoctor/t3-pagedoctor'),
            'system_version' => \Composer\InstalledVersions::getVersionRanges('typo3/cms-core'),
            'is_composer_mode' => Environment::isComposerMode()
        ]]);
    }
}