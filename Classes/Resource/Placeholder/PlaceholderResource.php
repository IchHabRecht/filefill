<?php
declare(strict_types = 1);
namespace IchHabRecht\Filefill\Resource\Placeholder;

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

use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlaceholderResource implements RemoteResourceInterface
{
    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var array
     */
    protected $allowedFileExtensions = [
        'gif',
        'jpeg',
        'jpg',
        'png',
    ];

    /**
     * @var string
     */
    protected $url = 'http://via.placeholder.com/';

    /**
     * @var FileInterface[]
     */
    protected static $fileIdentifierCache = [];

    public function __construct(ResourceFactory $resourceFactory = null)
    {
        $this->resourceFactory = $resourceFactory ?: ResourceFactory::getInstance();
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath)
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($fileExtension, $this->allowedFileExtensions, true)) {
            return false;
        }

        if (!isset(static::$fileIdentifierCache[$fileIdentifier])) {
            $fileObject = null;
            $localPath = $filePath;
            $storage = $this->resourceFactory->getStorageObject(0, [], $localPath);
            if ($storage->getUid() !== 0) {
                $fileObject = $this->getFileObjectFromStorage($storage, $fileIdentifier);
            }
            static::$fileIdentifierCache[$fileIdentifier] = $fileObject ?? false;
        }

        return static::$fileIdentifierCache[$fileIdentifier] !== false;
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return string
     */
    public function getFile(
        $fileIdentifier,
        $filePath
    ) {
        if (!isset(static::$fileIdentifierCache[$fileIdentifier]) && !$this->hasFile($fileIdentifier, $filePath)) {
            return false;
        }

        $fileObject = static::$fileIdentifierCache[$fileIdentifier];
        $size = max(1, $fileObject->getProperty('width')) . 'x' . max(1, $fileObject->getProperty('height'));
        $fileExtension = $fileObject->getExtension();
        if (in_array($fileExtension, $this->allowedFileExtensions, true)) {
            $size .= '.' . $fileExtension;
        }

        $content = GeneralUtility::getUrl($this->url . $size, 0, false, $report);
        // Currently the API sends PNG images instead of GIF
        // Check for PNG image and convert to GIF manually
        if ($fileExtension === 'gif' && substr(bin2hex($content), 0, 16) === '89504e470d0a1a0a') {
            $image = imagecreatefromstring($content);
            ob_start();
            imagegif($image);
            $content = ob_get_contents();
            imagedestroy($image);
            ob_end_clean();
        }

        return $content;
    }

    /**
     * @param ResourceStorage $storage
     * @param string $fileIdentifier
     * @return File|null
     */
    protected function getFileObjectFromStorage(ResourceStorage $storage, string $fileIdentifier)
    {
        $fileObject = null;

        if (!$storage->isWithinProcessingFolder($fileIdentifier)) {
            try {
                $fileObject = $this->resourceFactory->getFileObjectByStorageAndIdentifier($storage->getUid(), $fileIdentifier);
            } catch (\InvalidArgumentException $e) {
                return null;
            }
        } else {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_processedfile');
            $expressionBuilder = $queryBuilder->expr();
            $databaseRow = $queryBuilder->select('*')
                ->from('sys_file_processedfile')
                ->where(
                    $expressionBuilder->eq(
                        'storage',
                        $queryBuilder->createNamedParameter((int)$storage->getUid(), \PDO::PARAM_INT)
                    ),
                    $expressionBuilder->eq(
                        'identifier',
                        $queryBuilder->createNamedParameter($fileIdentifier, \PDO::PARAM_STR)
                    )
                )
                ->execute()
                ->fetch(\PDO::FETCH_ASSOC);
            if (empty($databaseRow)) {
                return null;
            }

            $originalFile = $this->resourceFactory->getFileObject((int)$databaseRow['original']);
            $taskType = $databaseRow['task_type'];
            $configuration = unserialize($databaseRow['configuration'], ['allowed_classes' => false]);

            $fileObject = GeneralUtility::makeInstance(
                ProcessedFile::class,
                $originalFile,
                $taskType,
                $configuration,
                $databaseRow
            );
        }

        return $fileObject;
    }
}
