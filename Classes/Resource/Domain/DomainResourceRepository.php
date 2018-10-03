<?php
declare(strict_types = 1);
namespace IchHabRecht\Filefill\Resource\Domain;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DomainResourceRepository
{
    /**
     * @return DomainResource[]
     */
    public function findAll()
    {
        $domainResources = [];

        $orderBy = [];
        if (!empty($GLOBALS['TCA']['sys_domain']['ctrl']['sortby'])) {
            $orderBy = [[$GLOBALS['TCA']['sys_domain']['ctrl']['sortby'], 'ASC']];
        } elseif (!empty($GLOBALS['TCA']['sys_domain']['ctrl']['default_sortby'])) {
            $orderBy = QueryHelper::parseOrderBy($GLOBALS['TCA']['sys_domain']['ctrl']['default_sortby']);
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_domain');
        $expressionBuilder = $queryBuilder->expr();
        $queryBuilder->select('domainName')
            ->from('sys_domain')
            ->where(
                $expressionBuilder->neq(
                    'domainName',
                    $queryBuilder->createNamedParameter(GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'), \PDO::PARAM_STR)
                )
            );
        if (version_compare(TYPO3_version, '<', '9')) {
            $queryBuilder->andWhere(
                $expressionBuilder->eq(
                    'redirectTo',
                    $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                )
            );
        }
        foreach ($orderBy as $orderByAndDirection) {
            $queryBuilder->addOrderBy(...$orderByAndDirection);
        }
        $result = $queryBuilder->execute();

        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $url = 'http://' . $row['domainName'];
            $domainResources[] = GeneralUtility::makeInstance(DomainResource::class, $url);
        }

        return $domainResources;
    }
}
