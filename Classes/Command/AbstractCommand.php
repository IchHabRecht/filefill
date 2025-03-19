<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
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
                $expressionBuilder->or(
                    $expressionBuilder->eq(
                        'tx_filefill_enable',
                        $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)
                    ),
                    $expressionBuilder->in(
                        'uid',
                        $queryBuilder->createNamedParameter($configuredStorages, ArrayParameterType::INTEGER)
                    )
                )
            )
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_combine(array_map('intval', array_column($rows, 'uid')), $rows);
    }
}
