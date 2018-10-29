<?php
namespace IchHabRecht\Filefill\Hooks;

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
use TYPO3\CMS\Core\Utility\MathUtility;

class ResetMissingFiles
{
    /**
     * @param string $status
     * @param string $table
     * @param $id
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id)
    {
        if ($table !== 'sys_file_storage'
            || empty($_POST['_save_tx_filefill_missing'])
            || !MathUtility::canBeInterpretedAsInteger($id)
        ) {
            return;
        }
        $databaseConnection = $this->getDatabaseConnection();
        $databaseConnection->exec_UPDATEquery(
            'sys_file',
            'storage=' . (int)$id . ' AND missing=1',
            [
                'missing' => 0,
            ]
        );
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
