<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\EventListener;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Nicole Hummel <nicole-typo3@nimut.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;

class FlexFormDataStructureParsedEventListener
{
    public function __invoke(AfterFlexFormDataStructureParsedEvent $event)
    {
        $identifier = $event->getIdentifier();
        if (($identifier['tableName'] ?? '') !== 'sys_file_storage'
            || ($identifier['fieldName'] ?? '') !== 'tx_filefill_resources'
        ) {
            return;
        }

        $dataStructure = $event->getDataStructure();
        $dataStructure['sheets']['sDEF']['ROOT']['el']['resources']['el'] = [];

        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'] ?? [] as $resource => $configuration) {
            if (empty($configuration['title'])
                || empty($configuration['config'])
                || empty($configuration['handler'])
            ) {
                continue;
            }

            $dataStructure['sheets']['sDEF']['ROOT']['el']['resources']['el'][$resource] = [
                'el' => [
                    $resource => $configuration['config'],
                ],
                'title' => $configuration['title'],
                'type' => 'array',
            ];
        }

        $event->setDataStructure($dataStructure);
    }
}
