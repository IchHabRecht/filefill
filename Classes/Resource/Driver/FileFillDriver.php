<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Resource\Driver;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Nicole Cordes <typo3@cordes.co>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use IchHabRecht\Filefill\Resource\RemoteResourceCollection;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileFillDriver extends LocalDriver
{
    /**
     * @var DriverInterface
     */
    protected $originalDriverObject;

    /**
     * @var RemoteResourceCollection
     */
    protected $remoteResourceCollection;

    public function __construct(array $configuration, DriverInterface $originalDriverObject, RemoteResourceCollection $remoteResourceCollection)
    {
        parent::__construct($configuration);

        $this->originalDriverObject = $originalDriverObject;
        $this->remoteResourceCollection = $remoteResourceCollection;
    }

    /**
     * @param string $fileIdentifier
     * @return bool
     */
    public function fileExists($fileIdentifier)
    {
        $this->ensureFileExists($fileIdentifier);

        return $this->originalDriverObject->fileExists($fileIdentifier);
    }

    /**
     * Checks if a folder exists.
     *
     * @param string $folderIdentifier
     * @return bool
     */
    public function folderExists($folderIdentifier)
    {
        if (parent::folderExists($folderIdentifier)) {
            return true;
        }

        $folderIdentifier = rtrim($folderIdentifier, '/');
        $pathinfo = pathinfo($folderIdentifier);
        if (!empty($pathinfo['basename']) && !empty($pathinfo['extension'])) {
            $this->ensureFileExists($folderIdentifier);
        }

        return false;
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function getPublicUrl($identifier)
    {
        $this->ensureFileExists($identifier);

        return $this->originalDriverObject->getPublicUrl($identifier);
    }

    /**
     * @param string $fileIdentifier
     * @return string
     */
    public function getFileContents($fileIdentifier)
    {
        $this->ensureFileExists($fileIdentifier);

        return $this->originalDriverObject->getFileContents($fileIdentifier);
    }

    /**
     * @param string $fileIdentifier
     * @param bool $writable
     * @return string
     */
    public function getFileForLocalProcessing($fileIdentifier, $writable = true)
    {
        $this->ensureFileExists($fileIdentifier);

        return $this->originalDriverObject->getFileForLocalProcessing($fileIdentifier, $writable);
    }

    /**
     * @param string $fileIdentifier
     * @param array $propertiesToExtract
     * @return array
     */
    public function getFileInfoByIdentifier($fileIdentifier, array $propertiesToExtract = [])
    {
        $this->ensureFileExists($fileIdentifier);

        return $this->originalDriverObject->getFileInfoByIdentifier($fileIdentifier, $propertiesToExtract);
    }

    /**
     * @param string $identifier
     * @return array
     */
    public function getPermissions($identifier)
    {
        $this->ensureFileExists($identifier);

        return $this->originalDriverObject->getPermissions($identifier);
    }

    /**
     * @param string $identifier
     * @return void
     */
    public function dumpFileContents($identifier)
    {
        $this->ensureFileExists($identifier);

        $this->originalDriverObject->dumpFileContents($identifier);
    }

    /**
     * @return bool
     */
    public function isCaseSensitiveFileSystem()
    {
        return true;
    }

    /**
     * @param string $fileIdentifier
     * @return bool
     */
    protected function ensureFileExists($fileIdentifier)
    {
        $absoluteFilePath = $this->getAbsolutePath($fileIdentifier, false);
        if (empty($absoluteFilePath) || file_exists($absoluteFilePath)) {
            return true;
        }

        $fileName = basename($absoluteFilePath);
        if (empty($fileName)) {
            return true;
        }

        $filePath = $this->originalDriverObject->getPublicUrl($fileIdentifier);

        $fileContent = $this->remoteResourceCollection->get($fileIdentifier, $filePath);
        if ($fileContent !== null) {
            $absoluteFilePath = $this->getAbsolutePath($fileIdentifier);
            GeneralUtility::mkdir_deep(dirname($absoluteFilePath));
            file_put_contents($absoluteFilePath, $fileContent);

            if (is_resource($fileContent) && get_resource_type($fileContent) === 'stream') {
                fclose($fileContent);
            }
        }

        return true;
    }

    /**
     * Returns the absolute path of a file or folder.
     *
     * @param string $fileIdentifier
     * @param bool $callOriginalDriver
     * @return string
     */
    protected function getAbsolutePath($fileIdentifier, $callOriginalDriver = true)
    {
        $relativeFilePath = ltrim($this->canonicalizeAndCheckFileIdentifier($fileIdentifier, $callOriginalDriver), '/');

        return $this->absoluteBasePath . $relativeFilePath;
    }

    /**
     * Makes sure the Path given as parameter is valid
     *
     * @param string $fileIdentifier The file path (including the file name!)
     * @param bool $callOriginalDriver
     * @return string
     */
    protected function canonicalizeAndCheckFileIdentifier($fileIdentifier, $callOriginalDriver = true)
    {
        return $callOriginalDriver
            ? $this->originalDriverObject->canonicalizeAndCheckFileIdentifier($fileIdentifier)
            : parent::canonicalizeAndCheckFileIdentifier($fileIdentifier);
    }
}
