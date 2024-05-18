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

namespace Atkins\Pagedoctor\Api\Controllers\Traits;

use Atkins\Pagedoctor\Api\Controllers\Exceptions\ExtconfInvalidException;
use Atkins\Pagedoctor\Api\Controllers\Exceptions\MissingHeaderNonceException;
use Atkins\Pagedoctor\Api\Controllers\Exceptions\MissingHeaderSignatureException;
use Atkins\Pagedoctor\Api\Controllers\Exceptions\SecretEmptyException;
use Atkins\Pagedoctor\Api\Controllers\Exceptions\SignatureMismatchException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

trait Authorizable
{
    protected function authorize(): void
    {
        $signatureHeader = $this->request->getHeader('X-Pagedoctor-Signature');
        if (!$signatureHeader || count($signatureHeader) == 0)
            throw new MissingHeaderSignatureException('Incoming request had no signature header');


        $nonceHeader = $this->request->getHeader('X-Pagedoctor-Nonce');
        if (!$nonceHeader || count($nonceHeader) == 0)
            throw new MissingHeaderNonceException('Incoming request had no nonce header');

        $calculatedSignature = strtolower(
            hash_hmac('sha256', implode(',', [
                strtolower($this->request->getMethod()),
                'application/json',
                'request',
                $nonceHeader[0]
            ]), $this->fetchCredentials()['secret'])
        );

        if ($signatureHeader[0] !== $calculatedSignature)
            throw new SignatureMismatchException('Request signature was invalid. '
                . 'Have you set the secret under ADMIN Tools > Settings > Extension Configuration? '
                . 'If you did and the secret matches that what was specified within Pagedoctor under "Servers", '
                . 'this can potentially mean that there was a Man-in-the-Middle attack during the request, or'
                . 'even simpler someone tried to access the Pagedoctor API.');
    }

    protected function backendConfiguration(): mixed
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('pagedoctor');
    }

    protected function fetchCredentials(): array
    {
        if (!is_array($this->backendConfiguration()) || !array_key_exists('secret', $this->backendConfiguration()))
            throw new ExtconfInvalidException('Extension configuration not properly set');
        if (empty($this->backendConfiguration()['secret']))
            throw new SecretEmptyException("Secret not set."
                . " Have you set the Secret under ADMIN Tools > Settings > Extension Configuration?");

        return [
            'secret' => $this->backendConfiguration()['secret']
        ];
    }
}