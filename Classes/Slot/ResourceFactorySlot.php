<?php
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

use IchHabRecht\Filefill\Resource\RemoteResourceCollectionFactory;
use TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResourceFactorySlot
{
    public function initializeResourceStorage(ResourceFactory $resourceFactory, ResourceStorage $resourceStorage)
    {
        $storageRecord = $resourceStorage->getStorageRecord();
        $isLocalDriver = $storageRecord['driver'] === 'Local';
        $isRecordEnabled = !empty($storageRecord['tx_filefill_enable']) && !empty($storageRecord['tx_filefill_resources']);
        $isStorageConfigured = !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['storages'][$resourceStorage->getUid()]);

        if (!$isLocalDriver || (!$isRecordEnabled && !$isStorageConfigured)) {
            return;
        }

        $closure = \Closure::bind(function () use ($resourceStorage) {
            return $resourceStorage->driver;
        }, null, 'TYPO3\\CMS\\Core\\Resource\\ResourceStorage');
        $originalDriverObject = $closure();

        if ($isRecordEnabled) {
            $remoteResourceCollection = RemoteResourceCollectionFactory::createRemoteResourceCollectionFromFlexForm(
                $storageRecord['tx_filefill_resources']
            );
        } else {
            $remoteResourceCollection = RemoteResourceCollectionFactory::createRemoteResourceCollectionFromConfiguration(
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['storages'][$resourceStorage->getUid()]
            );
        }

        $driverObject = GeneralUtility::makeInstance(
            'IchHabRecht\\Filefill\\Resource\\Driver\\FileFillDriver',
            $resourceStorage->getConfiguration(),
            $originalDriverObject,
            $remoteResourceCollection
        );
        $driverObject->setStorageUid($storageRecord['uid']);
        $driverObject->mergeConfigurationCapabilities($resourceStorage->getCapabilities());
        try {
            $driverObject->processConfiguration();
        } catch (InvalidConfigurationException $e) {
        }
        $driverObject->initialize();

        $resourceStorage->setDriver($driverObject);
    }
}
