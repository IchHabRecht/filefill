<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\EventListener;

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

use TYPO3\CMS\Core\Resource\Event\BeforeFileProcessingEvent;

class FileProcessingEventEventListener
{
    public function __invoke(BeforeFileProcessingEvent $event)
    {
        // Call exists() function to ensure driver fetches missing files
        $processedFile = $event->getProcessedFile();
        $processedFile->exists();

        $file = $event->getFile();
        $file->exists();
    }
}
