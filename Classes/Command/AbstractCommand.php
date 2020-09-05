<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractCommand extends Command
{
    protected function getEnabledStorages(): array
    {
        $configuredStorages = array_keys($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['storages'] ?? ['0' => '']);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_storage');
        $expressionBuilder = $queryBuilder->expr();
        $rows = $queryBuilder->select('uid', 'name')
            ->from('sys_file_storage')
            ->where(
                $expressionBuilder->orX(
                    $expressionBuilder->eq(
                        'tx_filefill_enable',
                        $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                    ),
                    $expressionBuilder->in(
                        'uid',
                        $queryBuilder->createNamedParameter($configuredStorages, Connection::PARAM_INT_ARRAY)
                    )
                )
            )
            ->orderBy('uid')
            ->execute()
            ->fetchAll();

        return array_combine(array_map('intval', array_column($rows, 'uid')), $rows);
    }
}
