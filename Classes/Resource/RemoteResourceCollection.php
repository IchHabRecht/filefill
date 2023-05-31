<?php

declare(strict_types=1);

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
use IchHabRecht\Filefill\Exception\UnknownResourceException;
use IchHabRecht\Filefill\Repository\FileRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RemoteResourceCollection implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var FileRepository
     */
    protected $fileRepository;

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

    /**
     * RemoteResourceCollection constructor.
     *
     * @param array $resources
     * @param ResourceFactory|null $resourceFactory
     * @param FileRepository|null $fileRepository
     */
    public function __construct(array $resources, ResourceFactory $resourceFactory = null, FileRepository $fileRepository = null)
    {
        $this->resources = $resources;
        $this->resourceFactory = $resourceFactory ?: GeneralUtility::makeInstance(ResourceFactory::class);
        $this->fileRepository = $fileRepository ?: GeneralUtility::makeInstance(FileRepository::class);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return resource|string|null
     */
    public function get($fileIdentifier, $filePath)
    {
        // Do not try to download files that can be either processed or are not available in sys_file
        if ($this->fileCanBeReProcessed($fileIdentifier, $filePath) || static::$fileIdentifierCache[$filePath] === null) {
            return null;
        }

        $this->logger->debug(
            sprintf('Fetching file "%s" (%s)', $filePath, $fileIdentifier),
            array_map(function ($resource) {
                return $resource['identifier'];
            }, $this->resources)
        );

        foreach ($this->resources as $resource) {
            if (!$resource['handler'] instanceof RemoteResourceInterface) {
                throw new MissingInterfaceException(
                    'Remote resource of type ' . get_class($resource['handler']) . ' doesn\'t implement IchHabRecht\\Filefill\\Resource\\RemoteResourceInterface',
                    1519680070
                );
            }

            $file = static::$fileIdentifierCache[$filePath];
            if ($resource['handler']->hasFile($fileIdentifier, $filePath, $file)) {
                $fileContent = $resource['handler']->getFile($fileIdentifier, $filePath, $file);
                if ($fileContent === false) {
                    $this->logger->debug(
                        sprintf('Resource "%s" returned empty content', $resource['identifier']),
                        [
                            'fileIdentifier' => $fileIdentifier,
                            'filePath' => $filePath,
                        ]
                    );
                    continue;
                }
                if (is_resource($fileContent) && get_resource_type($fileContent) !== 'stream') {
                    throw new UnknownResourceException(
                        'Cannot handle resource type "' . get_resource_type($fileContent) . '" as file content',
                        1583421958
                    );
                }

                $this->fileRepository->updateIdentifier($file, $resource['identifier']);
                $this->logger->debug(
                    sprintf('Resource "%s" found file', $resource['identifier']),
                    [
                        'fileIdentifier' => $fileIdentifier,
                        'filePath' => $filePath,
                    ]
                );

                return $fileContent;
            }
            $this->logger->debug(
                sprintf('Resource "%s" couldn\'t handle file', $resource['identifier']),
                [
                        'fileIdentifier' => $fileIdentifier,
                        'filePath' => $filePath,
                    ]
            );
        }

        return null;
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return bool
     */
    protected function fileCanBeReProcessed($fileIdentifier, $filePath)
    {
        if (!array_key_exists($filePath, static::$fileIdentifierCache)) {
            static::$fileIdentifierCache[$filePath] = null;
            $localPath = $filePath;
            $storage = $this->resourceFactory->getStorageObject(0, [], $localPath);
            if ($storage->getUid() !== 0) {
                static::$fileIdentifierCache[$filePath] = $this->getFileObjectFromStorage($storage, $fileIdentifier);
            }
        }

        return static::$fileIdentifierCache[$filePath] instanceof ProcessedFile
            && static::$fileIdentifierCache[$filePath]->getOriginalFile()->exists();
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
                ->from('sys_file_processedfile')->where($expressionBuilder->eq(
                'storage',
                $queryBuilder->createNamedParameter((int)$storage->getUid(), \PDO::PARAM_INT)
            ), $expressionBuilder->eq(
                'identifier',
                $queryBuilder->createNamedParameter($fileIdentifier, \PDO::PARAM_STR)
            ))->executeQuery()
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
