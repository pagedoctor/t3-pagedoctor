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

use Atkins\Pagedoctor\Api\Controllers\Traits\Authorizable;
use Atkins\Pagedoctor\Api\Controllers\Traits\Validateable;
use Atkins\Pagedoctor\Api\Models\RequestModel;
use Atkins\Pagedoctor\Api\Models\ResponseModel;
use Atkins\Pagedoctor\PagedoctorException;
use TYPO3\CMS\Core\Http\ServerRequest;

abstract class ApiController
{
    use Authorizable;
    use Validateable;

    protected RequestModel $requestModel;
    protected ResponseModel $responseModel;
    protected array $typoScriptConfiguration;
    protected ServerRequest $request;
    protected string $requestBody;

    const RESPONSE_ERROR = 'error';
    const RESPONSE_SUCCESS = 'success';

    protected function beforeFilter(): void
    {
        $this->authorize();
        $this->validateInbound();
    }

    protected function afterFilter(): void
    {
        $this->validateOutbound();
    }

    protected function render(array $typoScriptConfiguration, ServerRequest $request, string $modelClass): string
    {
        $this->typoScriptConfiguration = $typoScriptConfiguration;
        $this->request = $request;
        $this->requestBody = $this->request->getBody()->getContents();
        $this->requestModel = $modelClass::fromJson($modelClass, $this->requestBody);

        try {
            $this->beforeFilter();
            $this->responseModel = $this->execute();
            $this->afterFilter();
        } catch (PagedoctorException $e) {
            $this->responseModel = $this->createResponseModel(ResponseModel::class);
            $this->responseModel->status = self::RESPONSE_ERROR;
            $this->responseModel->exception = $e::class;
            $this->responseModel->message = $e->getMessage();
        }

        return $this->responseModel->toJson();
    }

    protected function defaultResponseAttributes(): array
    {
        return [
            \Composer\InstalledVersions::getVersionRanges('pagedoctor/t3-pagedoctor'),
            self::RESPONSE_SUCCESS,
            '',
            ''
        ];
    }

    protected function createResponseModel($responseModel, array $envelope = [[]]): ResponseModel
    {
        $class = new \ReflectionClass($responseModel);
        $args = array_merge(
            $this->defaultResponseAttributes(),
            $envelope
        );
        $instance = $class->newInstanceArgs($args);
        return $instance;
    }

    abstract public function execute(): ResponseModel;
}
