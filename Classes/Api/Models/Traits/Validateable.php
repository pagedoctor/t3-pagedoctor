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

namespace Atkins\Pagedoctor\Api\Models\Traits;

use Atkins\Pagedoctor\Api\Models\Exceptions\ValidationFailedException;
use Atkins\Pagedoctor\PagedoctorException;
use JsonSchema\Validator;

trait Validateable
{
    public function valid(string $payload): bool
    {
        $endpoint = $this->sanitizeFilename($this->resolveEndpoint());
        $direction = $this->sanitizeFilename($this->resolveDirection());

        $schema = json_decode(file_get_contents(
            \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:pagedoctor/Resources/Private/Schemata/' . $endpoint . '/' . $direction . '.json')
        ));

        $validator = new Validator;
        $deserializedJson = self::deserializeJson($payload);
        $validator->validate($deserializedJson, $schema);

        try {
            if (!$validator->isValid()) {
                foreach ($validator->getErrors() as $error) {
                    $message = $error['message'];
                    throw new ValidationFailedException("Validation failed due to the following: $message");
                }
            }
        } catch (ValidationFailedException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns the current classes parent folder, e.g. Artifact, Ping, Scaffold etc.
     * @return string
     */
    public function resolveEndpoint(): string
    {
        $array = explode('\\', get_class($this));
        end($array);
        return prev($array);
    }

    /**
     * Returns the current class name, its either Request or Response
     * @return string
     */
    public function resolveDirection(): string
    {
        $array = explode('\\', get_class($this));
        return strtolower(end($array));
    }

    /**
     * Protects inserted file path against directory traversal.
     * @param $filepath
     * @return string
     * @throws PagedoctorException
     */
    public function sanitizeFilename($filename): string
    {
        $filename = basename($filename);

        if (str_contains($filename, '..'))
            throw new PagedoctorException('Schema filename isn\'t allowed to contain two dots due to security reasons');

        return $filename;
    }
}