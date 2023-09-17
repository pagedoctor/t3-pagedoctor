<?php

namespace Atkins\Pagedoctor\Controller;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;

final class PingController extends ApiController
{
    private string $version;
    private string $systemVersion;
    private bool $isComposerMode;

    public function __construct(
        private readonly TypoScriptService $typoScriptService,
        private readonly RenderingContextFactory $renderingContextFactory,
    ) {
    }

    protected function loadData(): void
    {
        $this->version = \Composer\InstalledVersions::getVersionRanges('pagedoctor/t3-pagedoctor');
        $this->systemVersion = \Composer\InstalledVersions::getVersionRanges('typo3/cms-core');
        $this->isComposerMode = Environment::isComposerMode();
    }

    protected function indexAction(): \GuzzleHttp\Psr7\Response
    {
        return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
            'version' => $this->version,
            'system_version' => $this->systemVersion,
            'is_composer_mode' => $this->isComposerMode
        ]));
    }

    protected function showAction(): \GuzzleHttp\Psr7\Response
    {
        throw new \RuntimeException('Action not implemented');
    }

    protected function createAction(): \GuzzleHttp\Psr7\Response
    {
        throw new \RuntimeException('Action not implemented');
    }
}