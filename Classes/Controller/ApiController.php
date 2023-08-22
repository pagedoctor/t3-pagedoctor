<?php

declare(strict_types=1);

/*
 * Copyright 2008-2023 by Colin Atkins.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Atkins\Pagedoctor\Controller;


use Atkins\Pagedoctor\Controller\Exceptions\AuthenticationException;
use Atkins\Pagedoctor\Controller\Exceptions\AuthorizationException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use JsonSchema\Validator;
use Atkins\Pagedoctor\Controller\Exception\ControllerActionUnknownException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\ServerRequest;


abstract class ApiController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    private $typoScriptConfiguration;

    const RESPONSE_ERROR = 'error';
    const RESPONSE_SUCCESS = 'success';

    /**
     * Runs dependencies before action method.
     * @throws Exception
     * @throws AuthenticationException
     * @throws Exceptions\BackendConfigurationException
     * @throws Exceptions\MalformedRequestException
     * @throws AuthorizationException
     * @throws Exceptions\RequestInvalidException
     */
    private function beforeFilter($typoScriptConfiguration, $request) {
        $this->authorizeRequest($typoScriptConfiguration, $request);
        if (!in_array(strtolower($_SERVER['REQUEST_METHOD']), ['get', 'options'])) $this->validateRequest($typoScriptConfiguration, $request);
        $this->loadData();
    }

    /**
     * Loads request specific data.
     * @return void
     */
    abstract protected function loadData(): void;

    /**
     * The ingress where the requests comes in first.
     * @param string $_ unused, but needed as this is called via userfunc and passes a string as first parameter
     * @param array $typoScriptConfiguration TypoScript configuration specified in USER Content Object
     */
    public function ingress(string $_, array $typoScriptConfiguration, ServerRequest $request): string
    {
        $this->typoScriptConfiguration = $typoScriptConfiguration;

        try {
            $this->beforeFilter($typoScriptConfiguration, $request);
            switch ($typoScriptConfiguration['action']) {
                case 'index':
                    $response = $this->indexAction($typoScriptConfiguration, $request);
                    break;
                case 'show':
                    $response = $this->showAction($typoScriptConfiguration, $request);
                    break;
                case 'create':
                    $response = $this->createAction($typoScriptConfiguration, $request);
                    break;
                default:
                    throw new \Atkins\Pagedoctor\Controller\Exceptions\ControllerActionUnknownException("Controller action \"${$typoScriptConfiguration['action']}\" not specified");
            }
        } catch(\Opis\JsonSchema\Exceptions\SchemaException $e) {
            $this->logger->error("During an incoming Pagedoctor request the JSON schema was invalid. The validation error was: \n" . $e->getMessage());
            $response = $this->constructResponse(self::RESPONSE_ERROR);
        } catch(\Atkins\Pagedoctor\Controller\Exceptions\MalformedRequestException $e) {
            $this->logger->error($e->getMessage());
            $response = $this->constructResponse(self::RESPONSE_ERROR);
        } catch(\Atkins\Pagedoctor\Controller\Exceptions\AuthenticationException|\Atkins\Pagedoctor\Controller\Exceptions\AuthorizationException $e) {
            $this->logger->error($e->getMessage());
            $response = $this->constructResponse(self::RESPONSE_ERROR);
        } catch(\Atkins\Pagedoctor\Controller\Exceptions\BackendConfigurationException $e) {
            $this->logger->error($e->getMessage());
            $response = $this->constructResponse(self::RESPONSE_ERROR);
        } catch(\Atkins\Pagedoctor\Controller\Exceptions\ControllerActionUnknownException $e) {
            $this->logger->error($e->getMessage());
            $response = $this->constructResponse(self::RESPONSE_ERROR);
        }

        return $this->sendResponse($response);
    }

    /**
     * @param array $typoScriptConfiguration
     * @param ServerRequest $request
     * @return \GuzzleHttp\Psr7\Response
     */
    abstract protected function showAction(): \GuzzleHttp\Psr7\Response;

    /**
     * @param array $typoScriptConfiguration
     * @param ServerRequest $request
     * @return \GuzzleHttp\Psr7\Response
     */
    abstract protected function createAction(): \GuzzleHttp\Psr7\Response;

    /**
     * @param array $typoScriptConfiguration
     * @param ServerRequest $request
     * @return \GuzzleHttp\Psr7\Response
     */
    abstract protected function indexAction(): \GuzzleHttp\Psr7\Response;

    /**
     * @param ServerRequest $request
     * @return void
     * @throws \Atkins\Pagedoctor\Controller\Exceptions\AuthenticationException
     * @throws \Atkins\Pagedoctor\Controller\Exceptions\MalformedRequestException
     */
    private function authorizeRequest($typoScriptConfiguration, $request): void
    {
        $signatureHeader = $request->getHeader('X-Pagedoctor-Signature');
        if (!$signatureHeader || count($signatureHeader) == 0)
            throw new Exceptions\MalformedRequestException('Incoming request had no signature header');


        $nonceHeader = $request->getHeader('X-Pagedoctor-Nonce');
        if (!$nonceHeader || count($nonceHeader) == 0)
            throw new Exceptions\MalformedRequestException('Incoming request had no nonce header');


        $calculatedSignature = strtolower(
            hash_hmac('sha256', implode(',', [
                strtolower($_SERVER['REQUEST_METHOD']),
                'application/json',
                $typoScriptConfiguration['schema'],
                $nonceHeader[0]
            ]), $this->fetchCredentials()['secret'])
        );

        if ($signatureHeader[0] !== $calculatedSignature)
            throw new Exceptions\AuthenticationException('Request signature was invalid. '
                . 'Have you set the secret under ADMIN Tools > Settings > Extension Configuration? '
                . 'If you did and the secret matches that what was specified within Pagedoctor under "Servers", '
                . 'this can potentially mean that there was a Man-in-the-Middle attack during the request, or'
                . 'even simpler someone tried to access the Pagedoctor API.');
    }

    /**
     * @param ServerRequest $request
     * @param string $schemaFilename
     * @return void
     * @throws Exception
     * @throws Exceptions\RequestInvalidException
     */
    private function validateRequest(array $typoScriptConfiguration, ServerRequest $request): void
    {
        $schemaFilename = $this->sanitizeFilename($typoScriptConfiguration['schema']);

        $schema = json_decode(file_get_contents(
            \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:pagedoctor/Resources/Private/Schemata/' . $schemaFilename)
        ));

        $validator = new Validator;
        $contents = $this->parsePayload($request);
        $validator->validate($contents, $schema);

        if (!$validator->isValid()) {
            foreach ($validator->getErrors() as $error) {
                throw new \Atkins\Pagedoctor\Controller\Exceptions\RequestInvalidException($error);
            }
        }
    }

    /**
     * @param ServerRequest $request
     * @return void
     */
    protected function parsePayload(ServerRequest $request): \stdClass
    {
        return json_decode($request->getBody()->getContents());
    }

    /**
     * @param array $payload
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function constructResponse(string $status): \GuzzleHttp\Psr7\Response
    {
        return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
            'status' => $status
        ]));
    }

    /**
     * @param \GuzzleHttp\Psr7\Response $response
     * @return string
     */
    private function sendResponse(\GuzzleHttp\Psr7\Response $response): string
    {
        return $response->getBody()->getContents();
    }

    private function backendConfiguration(): mixed
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('pagedoctor');
    }

    /**
     * Protects inserted file path against directory traversal.
     * @param $filepath
     * @return string
     * @throws Exception
     */
    public function sanitizeFilename($filename): string
    {
        $filename = basename($filename);

        if (strpos($filename, '..') !== false)
            throw new Exception('Schema filename isn\'t allowed to contain two dots due to security reasons');

        return $filename;
    }

    /**
     * @return array
     * @throws Exceptions\BackendConfigurationException
     */
    private function fetchCredentials(): array
    {
        if (!is_array($this->backendConfiguration()) || !array_key_exists('secret', $this->backendConfiguration()))
            throw new Exceptions\BackendConfigurationException('Extension configuration not properly set');
        if (empty($this->backendConfiguration()['secret']))
            throw new Exceptions\BackendConfigurationException("API Key or Secret not set."
                . " Have you set the API settings under ADMIN Tools > Settings > Extension Configuration?");

        return [
            'secret' => $this->backendConfiguration()['secret']
        ];
    }
}