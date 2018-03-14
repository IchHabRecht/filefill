<?php
namespace IchHabRecht\Filefill\UserFunc;

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
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

class CheckMissingFiles
{
    /**
     * @param array $parameterArray
     * @return string
     */
    public function render(array $parameterArray)
    {
        $databaseConnection = $this->getDatabaseConnection();
        $count = $databaseConnection->exec_SELECTcountRows(
            '*',
            'sys_file',
            'storage=' . (int)$parameterArray['row']['uid'] . ' AND missing=1'
        );

        $html = [];
        $html[] = '<div class="form-control-wrap">';

        if ($count === 0) {
            $html[] = '<span class="badge badge-success">'
                . $this->getLanguageService()->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.no_missing')
                . '</span>';
        } else {
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $html[] = '<span class="badge badge-danger">'
                . sprintf(
                    $this->getLanguageService()->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.missing_files'),
                    $count
                )
                . '</span>';
            $html[] = '</div>';
            $html[] = '<div class="form-control-wrap t3js-module-docheader">';
            $html[] = '<a class="btn btn-default t3js-editform-submitButton" data-name="_save_tx_filefill_missing" data-form="EditDocumentController" data-value="1">';
            $html[] = $iconFactory->getIcon('actions-database-reload', Icon::SIZE_SMALL);
            $html[] = ' ' . $this->getLanguageService()->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.reset');
            $html[] = '</a>';
        }

        $html[] = '</div>';

        return implode('', $html);
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
