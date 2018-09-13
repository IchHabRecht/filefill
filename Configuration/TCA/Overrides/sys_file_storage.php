<?php

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

defined('TYPO3_MODE') || die();

$tempColumns = array(
    'tx_filefill_enable' => array(
        'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.enable',
        'displayCond' => 'FIELD:driver:=:Local',
        'config' => array(
            'type' => 'check',
            'default' => 0,
        ),
    ),
    'tx_filefill_resources' => array(
        'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.resources',
        'displayCond' => 'FIELD:driver:=:Local',
        'config' => array(
            'type' => 'flex',
            'ds' => array(
                'default' => 'FILE:EXT:filefill/Configuration/FlexForms/Resources.xml',
            ),
        ),
    ),
    'tx_filefill_missing' => array(
        'label' => 'LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill.missing',
        'displayCond' => 'FIELD:driver:=:Local',
        'config' => array(
            'type' => 'user',
            'userFunc' => 'IchHabRecht\\Filefill\\UserFunc\\CheckMissingFiles->render',
        ),
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_storage', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_file_storage',
    '--div--;LLL:EXT:filefill/Resources/Private/Language/locallang_db.xlf:sys_file_storage.filefill,'
    . 'tx_filefill_enable, tx_filefill_resources, tx_filefill_missing',
    '',
    'after:processingfolder'
);
