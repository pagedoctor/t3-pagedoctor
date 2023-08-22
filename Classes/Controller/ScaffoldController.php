<?php

namespace Atkins\Pagedoctor\Controller;

use Atkins\Pagedoctor\Deployment\ArtifactDeployer;
use \Atkins\Pagedoctor\Deployment\Exceptions\StoringDeploymentFailedException;
use \Atkins\Pagedoctor\Deployment\Exceptions\FailedCreatingArtifactsFolderException;
use \Atkins\Pagedoctor\Deployment\Exceptions\FailedSettingUpHtaccessException;
use \Atkins\Pagedoctor\Deployment\Exceptions\SanitizeFilenameException;
use Atkins\Pagedoctor\Deployment\ScaffoldDeployer;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;

final class ScaffoldController extends ApiController
{

    public function __construct(
        private readonly TypoScriptService $typoScriptService,
        private readonly RenderingContextFactory $renderingContextFactory,
        private readonly ScaffoldDeployer $scaffoldDeployer,
        private readonly ServerRequest $request
    ) {
    }

    protected function loadData(): void
    {
    }

    protected function createAction(): \GuzzleHttp\Psr7\Response
    {
        $scaffold = $this->scaffoldParams();

        try {
            $this->scaffoldDeployer->deploy($scaffold);
            $response = $this->constructResponse(self::RESPONSE_SUCCESS);
        } catch(\Atkins\Pagedoctor\Deployment\Exceptions\ChecksumMismatchException|SanitizeFilenameException|StoringDeploymentFailedException|FailedCreatingArtifactsFolderException|FailedSettingUpHtaccessException $e) {
            $this->logger->error($e->getMessage());
            $response = $this->constructResponse(self::RESPONSE_ERROR);
        }

        return $response;
    }

    private function scaffoldParams(): \stdClass {
        return $this->parsePayload($this->request);
    }

    protected function showAction(): \GuzzleHttp\Psr7\Response
    {
        throw new \RuntimeException('Action not implemented');
    }

    protected function indexAction(): \GuzzleHttp\Psr7\Response
    {
        throw new \RuntimeException('Action not implemented');
    }
}