<?php
declare(strict_types = 1);
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
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DeleteFiles
{
    /**
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * @var ProcessedFileRepository
     */
    protected $processedFileRepository;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    public function __construct(
        FileRepository $fileRepository = null,
        ProcessedFileRepository $processedFileRepository = null,
        ResourceFactory $resourceFactory = null
    ) {
        $this->fileRepository = $fileRepository ?: GeneralUtility::makeInstance(FileRepository::class);
        $this->processedFileRepository = $processedFileRepository ?: GeneralUtility::makeInstance(ProcessedFileRepository::class);
        $this->resourceFactory = $resourceFactory ?: GeneralUtility::makeInstance(ResourceFactory::class);
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

        $rows = $this->fileRepository->findByIdentifier($_POST['_save_tx_filefill_delete'], $id);
        foreach ($rows as $row) {
            try {
                $file = $this->resourceFactory->getFileObjectByStorageAndIdentifier($row['storage'], $row['identifier']);

                // First delete all processed files, because file_exists is called on driver
                foreach ($this->processedFileRepository->findAllByOriginalFile($file) as $processedFile) {
                    if ($processedFile->exists()) {
                        $processedFile->delete(true);
                    }
                }

                // Use read-only absolute path to delete original file
                $absolutePath = $file->getForLocalProcessing(false);
                if (@unlink($absolutePath)) {
                    $this->fileRepository->setIdentifier($file, '');
                }
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }
    }
}
