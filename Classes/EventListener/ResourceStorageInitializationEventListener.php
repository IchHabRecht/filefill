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

use IchHabRecht\Filefill\Resource\Driver\FileFillDriver;
use IchHabRecht\Filefill\Resource\RemoteResourceCollectionFactory;
use TYPO3\CMS\Core\Resource\Event\AfterResourceStorageInitializationEvent;
use TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResourceStorageInitializationEventListener
{
    public function __invoke(AfterResourceStorageInitializationEvent $event)
    {
        $storage = $event->getStorage();
        $storageRecord = $storage->getStorageRecord();
        $isLocalDriver = $storageRecord['driver'] === 'Local';
        $isRecordEnabled = !empty($storageRecord['tx_filefill_enable']) && !empty($storageRecord['tx_filefill_resources']);
        $isStorageConfigured = !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['storages'][$storage->getUid()]);

        if (!$isLocalDriver || (!$isRecordEnabled && !$isStorageConfigured)) {
            return;
        }

        $closure = \Closure::bind(function () use ($storage) {
            return $storage->driver;
        }, null, ResourceStorage::class);
        $originalDriverObject = $closure();

        // TYPO3 initializes storage records multiple times
        // Filefill need to prevent recursive initialization here
        if ($originalDriverObject instanceof FileFillDriver) {
            return;
        }
        if ($isRecordEnabled) {
            $remoteResourceCollection = RemoteResourceCollectionFactory::createRemoteResourceCollectionFromFlexForm(
                $storageRecord['tx_filefill_resources']
            );
        } else {
            $remoteResourceCollection = RemoteResourceCollectionFactory::createRemoteResourceCollectionFromConfiguration(
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['storages'][$storage->getUid()]
            );
        }

        $driverObject = GeneralUtility::makeInstance(
            FileFillDriver::class,
            $storage->getConfiguration(),
            $originalDriverObject,
            $remoteResourceCollection
        );
        $driverObject->setStorageUid($storageRecord['uid']);
        $driverObject->mergeConfigurationCapabilities($storage->getCapabilities());
        try {
            $driverObject->processConfiguration();
        } catch (InvalidConfigurationException $e) {
            // Intended fallthrough
        }
        $driverObject->initialize();

        $storage->setDriver($driverObject);
    }
}
