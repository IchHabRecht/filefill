<?php
declare(strict_types = 1);
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

use Doctrine\DBAL\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileRepository
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection = null)
    {
        $this->connection = $connection ?: GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file');
    }

    public function countByIdentifier($storage = null): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expressionBuilder = $queryBuilder->expr();
        $queryBuilder->getConcreteQueryBuilder()->select('COUNT(*) AS count', 'tx_filefill_identifier');
        $queryBuilder->from('sys_file')
            ->where(
                $expressionBuilder->neq(
                    'tx_filefill_identifier',
                    $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                )
            )
            ->groupBy('tx_filefill_identifier');

        if ($storage !== null) {
            $queryBuilder->andWhere(
                $expressionBuilder->eq(
                    'storage',
                    $queryBuilder->createNamedParameter($storage, \PDO::PARAM_INT)
                )
            );
        }

        return $queryBuilder->execute()
            ->fetchAll();
    }

    public function findByIdentifier(string $identifier, $storage = null): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expressionBuilder = $queryBuilder->expr();
        $queryBuilder->select('storage', 'identifier')
            ->from('sys_file')
            ->where(
                $expressionBuilder->eq(
                    'tx_filefill_identifier',
                    $queryBuilder->createNamedParameter($identifier, \PDO::PARAM_STR)
                )
            )
            ->groupBy('tx_filefill_identifier', 'identifier', 'storage');

        if ($storage !== null) {
            $queryBuilder->andWhere(
                $expressionBuilder->eq(
                    'storage',
                    $queryBuilder->createNamedParameter($storage, \PDO::PARAM_INT)
                )
            );
        }

        return $queryBuilder->execute()
            ->fetchAll();
    }

    public function setIdentifier(FileInterface $file, string $identifier)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->update('sys_file')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($file->getUid(), \PDO::PARAM_INT)
                )
            )
            ->set('tx_filefill_identifier', $identifier)
            ->execute();
    }
}
