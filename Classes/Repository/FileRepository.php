<?php
namespace IchHabRecht\Filefill\Repository;

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

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileRepository
{
    /**
     * @var ProcessedFileRepository
     */
    protected $processedFileRepository;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    public function __construct(
        ProcessedFileRepository $processedFileRepository = null,
        ResourceFactory $resourceFactory = null
    ) {
        $this->processedFileRepository = $processedFileRepository ?: GeneralUtility::makeInstance(ProcessedFileRepository::class);
        $this->resourceFactory = $resourceFactory ?: GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * @param int|null $storage
     * @return array
     */
    public function countByIdentifier($storage = null)
    {
        $databaseConnection = $this->getDatabaseConnection();

        $whereClause = 'tx_filefill_identifier!=\'\'';
        if ($storage !== null) {
            $whereClause .= ' AND storage=' . (int)$storage;
        }

        return $databaseConnection->exec_SELECTgetRows(
            'COUNT(*) AS count, tx_filefill_identifier',
            'sys_file',
            $whereClause,
            'tx_filefill_identifier'
        );
    }

    /**
     * @param string $identifier
     * @param int|null $storage
     * @return array
     */
    public function findByIdentifier($identifier, $storage = null)
    {
        $databaseConnection = $this->getDatabaseConnection();

        $whereClause = 'tx_filefill_identifier=' . $databaseConnection->fullQuoteStr($identifier, 'sys_file');
        if ($storage !== null) {
            $whereClause .= ' AND storage=' . (int)$storage;
        }

        return $databaseConnection->exec_SELECTgetRows(
            'storage, identifier',
            'sys_file',
            $whereClause,
            'tx_filefill_identifier, identifier, storage'
        );
    }

    /**
     * @param string $fileIdentifier
     * @param string $identifier
     */
    public function updateIdentifier($fileIdentifier, $identifier)
    {
        $databaseConnection = $this->getDatabaseConnection();
        $databaseConnection->exec_UPDATEquery(
            'sys_file',
            'identifier=' . $databaseConnection->fullQuoteStr($fileIdentifier, 'sys_file'),
            [
                'tx_filefill_identifier' => $identifier,
            ]
        );
    }

    /**
     * @param string $identifier
     * @param int|null $storage
     * @return int
     */
    public function deleteByIdentifier($identifier, $storage = null)
    {
        $rows = $this->findByIdentifier($identifier, $storage);
        foreach ($rows as $row) {
            try {
                $file = $this->resourceFactory->getFileObjectByStorageAndIdentifier($row['storage'], $row['identifier']);

                // First delete all processed files, because file_exists is called on driver
                foreach ($this->processedFileRepository->findAllByOriginalFile($file) as $processedFile) {
                    if ($processedFile->exists()) {
                        $processedFile->delete(true);
                    }
                }

                // Use read-only absolute path to delete original file
                $absolutePath = $file->getForLocalProcessing(false);
                if (@unlink($absolutePath)) {
                    $this->updateIdentifier($file->getIdentifier(), '');
                }
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        return count($rows);
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
