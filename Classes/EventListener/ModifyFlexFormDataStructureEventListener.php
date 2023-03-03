<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\EventListener;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Nicole Cordes <typo3@cordes.co>
 * (c) Elias Häußler <elias@haeussler.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use IchHabRecht\Filefill\Hooks\FlexFormToolsHook;
use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ModifyFlexFormDataStructureEventListener
{
    private FlexFormToolsHook $hook;

    public function __construct()
    {
        $this->hook = GeneralUtility::makeInstance(FlexFormToolsHook::class);
    }

    public function __invoke(AfterFlexFormDataStructureParsedEvent $event)
    {
        $dataStructure = $this->hook->parseDataStructureByIdentifierPostProcess(
            $event->getDataStructure(),
            $event->getIdentifier()
        );
        $event->setDataStructure($dataStructure);
    }
}
