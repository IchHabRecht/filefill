<?php
declare(strict_types = 1);
namespace IchHabRecht\Filefill\Resource;

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

use IchHabRecht\Filefill\Exception\MissingInterfaceException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RemoteResourceCollection
{
    /**
     * @var ResourceInterface[]
     */
    protected $resources;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var FileInterface[]
     */
    protected static $fileIdentifierCache = [];

    public function __construct(array $resources, ResourceFactory $resourceFactory = null)
    {
        $this->resources = $resources;
        $this->resourceFactory = $resourceFactory ?: ResourceFactory::getInstance();
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return bool
     */
    public function save($fileIdentifier, $filePath)
    {
        // Do not try to download files that can be either processed or are not available in sys_file
        if ($this->fileCanBeReProcessed($fileIdentifier, $filePath) || static::$fileIdentifierCache[$fileIdentifier] === null) {
            return false;
        }

        foreach ($this->resources as $remote) {
            if (!$remote instanceof RemoteResourceInterface) {
                throw new MissingInterfaceException(
                    'Remote resource of type ' . get_class($remote) . ' doesn\'t implement IchHabRecht\\Filefill\\Resource\\RemoteResourceInterface',
                    1519680070
                );
            }
            if ($remote->hasFile($fileIdentifier, $filePath, static::$fileIdentifierCache[$fileIdentifier])) {
                $fileContent = $remote->getFile($fileIdentifier, $filePath, static::$fileIdentifierCache[$fileIdentifier]);
                if ($fileContent === false) {
                    continue;
                }

                $absoluteFilePath = PATH_site . $filePath;
                GeneralUtility::mkdir_deep(dirname($absoluteFilePath));

                if (filter_var($fileContent, FILTER_VALIDATE_URL)) {
                    file_put_contents($absoluteFilePath, fopen($fileContent, 'r'));
                } else {
                    GeneralUtility::writeFile($absoluteFilePath, $fileContent);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return bool
     */
    protected function fileCanBeReProcessed($fileIdentifier, $filePath)
    {
        if (!array_key_exists($fileIdentifier, static::$fileIdentifierCache)) {
            static::$fileIdentifierCache[$fileIdentifier] = null;
            $localPath = $filePath;
            $storage = $this->resourceFactory->getStorageObject(0, [], $localPath);
            if ($storage->getUid() !== 0) {
                static::$fileIdentifierCache[$fileIdentifier] = $this->getFileObjectFromStorage($storage, $fileIdentifier);
            }
        }

        return static::$fileIdentifierCache[$fileIdentifier] instanceof ProcessedFile
            && static::$fileIdentifierCache[$fileIdentifier]->getOriginalFile()->exists();
    }

    /**
     * @param ResourceStorage $storage
     * @param string $fileIdentifier
     * @return FileInterface|null
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
