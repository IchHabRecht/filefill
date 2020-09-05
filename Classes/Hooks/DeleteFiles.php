<?php

declare(strict_types=1);

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

use IchHabRecht\Filefill\Repository\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DeleteFiles
{
    /**
     * @var FileRepository
     */
    protected $fileRepository;

    public function __construct(
        FileRepository $fileRepository = null
    ) {
        $this->fileRepository = $fileRepository ?: GeneralUtility::makeInstance(FileRepository::class);
    }

    /**
     * @param string $status
     * @param string $table
     * @param $id
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id)
    {
        if ($table !== 'sys_file_storage'
            || empty($_POST['_save_tx_filefill_delete'])
            || !MathUtility::canBeInterpretedAsInteger($id)
        ) {
            return;
        }

        $this->fileRepository->deleteByIdentifier($_POST['_save_tx_filefill_delete'], $id);
    }
}
