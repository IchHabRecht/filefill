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

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
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

        $html = array();
        $html[] = '<div class="form-control-wrap">';

        if ($count === 0) {
            $html[] = '<div class="typo3-message message-ok">'
                . $this->getLanguageService()->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.no_missing')
                . '</div>';
        } else {
            $html[] = '<div class="typo3-message message-error">'
                . sprintf(
                    $this->getLanguageService()->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.missing_files'),
                    $count
                )
                . '</div>';
            $html[] = '</div>';
            $html[] = '<div class="form-control-wrap t3js-module-docheader">';
            $html[] = '<div class="t3-form-field-item">';
            $html[] = '<button class="btn" type="submit" name="_savedok_x" value="filefill">';
            $html[] = IconUtility::getSpriteIcon('actions-document-save');
            $html[] = ' ' . $this->getLanguageService()->sL('LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.reset');
            $html[] = '</button>';
            $html[] = '</div>';
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
