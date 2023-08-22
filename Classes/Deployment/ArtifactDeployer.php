<?php

namespace Atkins\Pagedoctor\Deployment;
use Psr\Log\LoggerInterface;
use Atkins\Pagedoctor\Deployment\Exceptions\StoringDeploymentFailedException;
use Atkins\Pagedoctor\Deployment\Exceptions\FailedCreatingArtifactsFolderException;
use Atkins\Pagedoctor\Deployment\Exceptions\SanitizeFilenameException;
use Atkins\Pagedoctor\Deployment\Exceptions\FailedSettingUpHtaccessException;
use TYPO3\CMS\Core\Core\Environment;

class ArtifactDeployer
{
    /**
     * @return void
     */
    private function beforeFilter(): void
    {
        $this->createArtifactsFolder();
        $this->createHtaccessFile();
    }

    /**
     * @throws Exceptions\ChecksumMismatchException
     */
    public function deploy(\stdClass $deployment): void
    {
        $this->beforeFilter();

        $this->storeArtifact($deployment->checksum, $deployment->zip_contents, $deployment->artifact_filename);
    }


    private function createArtifactsFolder(): void
    {
        if (!file_exists($this->artifactsPath())) {
            $result = @mkdir($this->artifactsPath());
            if ($result !== true) {
                throw new FailedCreatingArtifactsFolderException('Failed setting up artifacts folder');
            }
        }
    }

    public function createHtaccessFile(): void
    {
        $instruction = "order deny,allow\ndeny from all";

        if (!file_exists($this->htaccessFilePath())) {
            @touch($this->htaccessFilePath());
            $result = file_put_contents($this->htaccessFilePath(), $instruction);
            if ($result !== true) {
                throw new FailedSettingUpHtaccessException('Failed setting up htaccess file for artifacts folder');
            }
        }
    }

    public function artifactsPath(): string
    {
        return (Environment::isComposerMode() ? Environment::getProjectPath() . '/artifacts' : Environment::getConfigPath() . '/ignorenoncomposermodefornow');
    }

    public function htaccessFilePath(): string
    {
        return $this->artifactsPath() . '/.htaccess';
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
            throw new SanitizeFilenameException('Schema filename isn\'t allowed to contain two dots due to security reasons');

        return $filename;
    }

    private function storeArtifact(string $checksum, string $zipContents, string $artifactFilename): void
    {
        $binary = base64_decode($zipContents);
        $tempfile = tmpfile();
        $tempfilePath = stream_get_meta_data($tempfile)['uri'];
        file_put_contents($tempfilePath, $binary);
        $calculatedChecksum = hash_file('sha256', $tempfilePath);

        if ($calculatedChecksum !== $checksum)
            throw new Exceptions\ChecksumMismatchException('The received artifact contents didn\'t match its transferred checksum');

        if (!file_exists($this->artifactFilepath($artifactFilename))) {
            $result = file_put_contents($this->artifactFilepath($artifactFilename), $binary);
            if ($result === false)
                throw new StoringDeploymentFailedException('An error occurred during saving of the artifact');
        }
    }

    private function artifactFilepath($artifactFilename): string
    {
        return $this->artifactsPath() . 'ArtifactDeployer.php/' . $this->sanitizeFilename($artifactFilename);
    }
}