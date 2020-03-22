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

use IchHabRecht\Filefill\Resource\Domain\DomainResource;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DomainResourceRepository
{
    /**
     * @return DomainResource[]
     */
    public function findAll()
    {
        if (version_compare(TYPO3_version, '<', '10')) {
            return $this->findAllBySysDomainRecords();
        }

        return $this->findAllBySiteConfiguration();
    }

    protected function findAllBySysDomainRecords(): array
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
            $urlParts = parse_url($row['domainName']);
            $url = ($urlParts['scheme'] ?? $_SERVER['REQUEST_SCHEME']) . '://' . $urlParts['host'];
            if (!isset($domainResources[$url])) {
                $domainResources[$url] = GeneralUtility::makeInstance(DomainResource::class, $url);
            }
        }

        return $domainResources;
    }

    protected function findAllBySiteConfiguration(): array
    {
        $domainResources = [];

        $sites = GeneralUtility::makeInstance(SiteFinder::class)->getAllSites();
        foreach ($sites as $site) {
            $url = ($site->getBase()->getScheme() ?: $_SERVER['REQUEST_SCHEME']) . '://' . $site->getBase()->getHost();
            if (!isset($domainResources[$url])) {
                $domainResources[$url] = GeneralUtility::makeInstance(DomainResource::class, $url);
            }

            foreach ($site->getConfiguration()['baseVariants'] ?? [] as $variant) {
                $urlParts = parse_url($variant['base']);
                $url = ($urlParts['scheme'] ?? $_SERVER['REQUEST_SCHEME']) . '://' . $urlParts['host'];
                if (!isset($domainResources[$url])) {
                    $domainResources[$url] = GeneralUtility::makeInstance(DomainResource::class, $url);
                }
            }
        }

        return $domainResources;
    }
}
