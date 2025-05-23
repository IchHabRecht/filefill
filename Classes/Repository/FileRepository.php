<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Repository;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Nicole Hummel <nicole-typo3@nimut.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class FileRepository
{
    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        protected readonly ProcessedFileRepository $processedFileRepository,
        protected readonly ResourceFactory $resourceFactory
    ) {
    }

    public function countByIdentifier(?int $storage = null): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file');
        $expressionBuilder = $queryBuilder->expr();
        $queryBuilder->getConcreteQueryBuilder()->select('COUNT(*) AS count', 'tx_filefill_identifier');
        $queryBuilder->from('sys_file')
            ->where(
                $expressionBuilder->neq(
                    'tx_filefill_identifier',
                    $queryBuilder->createNamedParameter('')
                )
            )
            ->groupBy('tx_filefill_identifier');

        if ($storage !== null) {
            $queryBuilder->andWhere(
                $expressionBuilder->eq(
                    'storage',
                    $queryBuilder->createNamedParameter($storage, ParameterType::INTEGER)
                )
            );
        }

        return $queryBuilder->executeQuery()
            ->fetchAllAssociative();
    }

    public function findByIdentifier(string $identifier, ?int $storage = null): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file');
        $expressionBuilder = $queryBuilder->expr();
        $queryBuilder->select('storage', 'identifier')
            ->from('sys_file')
            ->where(
                $expressionBuilder->eq(
                    'tx_filefill_identifier',
                    $queryBuilder->createNamedParameter($identifier)
                )
            )
            ->groupBy('tx_filefill_identifier', 'identifier', 'storage');

        if ($storage !== null) {
            $queryBuilder->andWhere(
                $expressionBuilder->eq(
                    'storage',
                    $queryBuilder->createNamedParameter($storage, ParameterType::INTEGER)
                )
            );
        }

        return $queryBuilder->executeQuery()
            ->fetchAllAssociative();
    }

    public function updateIdentifier(FileInterface $file, string $identifier): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file');
        $queryBuilder->update('sys_file')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($file->getUid(), ParameterType::INTEGER)
                )
            )
            ->set('tx_filefill_identifier', $identifier)
            ->executeStatement();
    }

    public function deleteByIdentifier(string $identifier, ?int $storage = null): int
    {
        $rows = $this->findByIdentifier($identifier, $storage);
        foreach ($rows as $row) {
            try {
                $file = $this->resourceFactory->getFileObjectByStorageAndIdentifier($row['storage'], $row['identifier']);
                if (!$file) {
                    continue;
                }

                // First delete all processed files, because file_exists is called on driver
                foreach ($this->processedFileRepository->findAllByOriginalFile($file) as $processedFile) {
                    if ($processedFile->exists()) {
                        $processedFile->delete(true);
                    }
                }

                // Use read-only absolute path to delete original file
                $absolutePath = $file->getForLocalProcessing(false);
                if (@unlink($absolutePath)) {
                    $this->updateIdentifier($file, '');
                }
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        return count($rows);
    }
}
