<?php

namespace Atkins\Pagedoctor\Deployment;
use Psr\Log\LoggerInterface;
use Atkins\Pagedoctor\Deployment\Exceptions\StoringDeploymentFailedException;
use Atkins\Pagedoctor\Deployment\Exceptions\FailedCreatingLocalPackagesFolderException;
use Atkins\Pagedoctor\Deployment\Exceptions\SanitizeFilenameException;
use Atkins\Pagedoctor\Deployment\Exceptions\FailedSettingUpHtaccessException;
use TYPO3\CMS\Core\Core\Environment;

class ScaffoldDeployer
{
    /**
     * @return void
     */
    private function beforeFilter(): void
    {
        $this->createLocalPackagesFolder();
    }

    /**
     * @throws Exceptions\ChecksumMismatchException
     */
    public function deploy(\stdClass $scaffold): void
    {
        $this->beforeFilter();

        $this->unzipScaffold($scaffold->checksum, $scaffold->zip_contents);
    }

    private function createLocalPackagesFolder(): void
    {
        if (!file_exists($this->localPackagesPath())) {
            $result = @mkdir($this->localPackagesPath());
            if ($result !== true) {
                throw new FailedCreatingLocalPackagesFolderException('Failed setting up local packages folder');
            }
        }
    }

    public function localPackagesPath(): string
    {
        return join('/', [
            Environment::getProjectPath(),
            'local_packages'
        ]);
    }

    private function scaffoldFilePath(): string
    {
        return join('/', [
            $this->localPackagesPath(),
            'scaffold.zip'
        ]);
    }

    private function unzipScaffold(string $checksum, string $zipContents): void
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('The Zip Util requires PHP\'s zip extension');
        }

        $binary = base64_decode($zipContents);
        $tempfile = tmpfile();
        $tempfilePath = stream_get_meta_data($tempfile)['uri'];
        file_put_contents($tempfilePath, $binary);
        $calculatedChecksum = hash_file('sha256', $tempfilePath);

        if ($calculatedChecksum !== $checksum)
            throw new Exceptions\ChecksumMismatchException('The received scaffold contents didn\'t match its transferred checksum');

        if (file_exists($this->scaffoldFilePath())) {
            throw new \Exception('Cannot overwrite existing scaffold file');
        }

        $result = file_put_contents($this->scaffoldFilePath(), $binary);
        if ($result === false)
            throw new StoringDeploymentFailedException('An error occurred during saving of the scaffold');


        $zip = new \ZipArchive();
        if ($zip->open($this->scaffoldFilePath()) !== true) {
            throw new FailedOpeningZipFileException('An error occurred while trying to open the scaffold file');
        }

        $nonSkipList = $this->generateZipFileExtractionNonSkipList($zip);
        if (!empty($nonSkipList)) {
            $zip->extractTo($this->localPackagesPath(), $nonSkipList);
        }

        $zip->close();

        @unlink($this->scaffoldFilePath());
    }


    private function generateZipFileExtractionNonSkipList(\ZipArchive $zip): array
    {
        $list = array();
        for ($idx = 0; $idx < $zip->numFiles; $idx++) {
            $path = $zip->getNameIndex($idx);
            if (!$this->shouldFileBeRemovedFromList($path)) {
                array_push($list, $path);
            }
        }
        return $list;
    }

    /**
     * Checks whether a file exists and if it should be skipped for extraction.
     * @param $path
     * @return bool
     */
    private function shouldFileBeRemovedFromList($path): bool {
        $absolutePath = join('/', [
            $this->localPackagesPath(),
            $path
        ]);

        /***
         * Always skip files and folders which exist.
         */
        if (file_exists($absolutePath)) {
            return true;
        }

        /**
         * Always skip existing folders.
         */
        if (is_dir($absolutePath)) {
            return true;
        }

        /**
         * Extract only non existing files.
         */
        return false;
    }
}