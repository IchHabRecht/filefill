<?php
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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DomainResourceRepository
{
    /**
     * @return DomainResource[]
     */
    public function findAll()
    {
        $domainResources = array();

        $databaseConnection = $this->getDatabaseConnection();

        $orderBy = '';
        if (!empty($GLOBALS['TCA']['sys_domain']['ctrl']['sortby'])) {
            $orderBy = $GLOBALS['TCA']['sys_domain']['ctrl']['sortby'] . ' ASC';
        } elseif (!empty($GLOBALS['TCA']['sys_domain']['ctrl']['default_sortby'])) {
            $orderBy = $databaseConnection->stripOrderBy($GLOBALS['TCA']['sys_domain']['ctrl']['default_sortby']);
        }

        $result = $databaseConnection->exec_SELECTquery(
            'domainName',
            'sys_domain',
            'domainName!=' . $databaseConnection->fullQuoteStr(GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'), 'sys_domain')
            . ' AND redirectTo=' . $databaseConnection->fullQuoteStr('', 'sys_domain')
            . BackendUtility::deleteClause('sys_domain'),
            '',
            $orderBy
        );

        while ($row = $result->fetch_assoc()) {
            $url = 'http://' . $row['domainName'];
            $domainResources[] = GeneralUtility::makeInstance('IchHabRecht\\Filefill\\Resource\\Domain\\DomainResource', $url);
        }

        return $domainResources;
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
