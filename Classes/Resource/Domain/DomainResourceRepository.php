<?php
namespace IchHabRecht\Filefill\Resource\Domain;

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
        $domainResources = [];

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
            $domainResources[] = GeneralUtility::makeInstance(DomainResource::class, $url);
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
