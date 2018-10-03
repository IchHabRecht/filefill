<?php
declare(strict_types = 1);
namespace IchHabRecht\Filefill\Slot;

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

use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\Service\FileProcessingService;

class FileProcessingServiceSlot
{
    public function ensureOriginalFileExists(FileProcessingService $fileProcessingService, DriverInterface $driver, ProcessedFile $processedFile, File $file)
    {
        // Call exists() function to ensure driver fetches missing files
        $processedFile->exists();
        $file->exists();
    }
}
