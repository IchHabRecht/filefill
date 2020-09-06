<?php

declare(strict_types=1);

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

use IchHabRecht\Filefill\Resource\Driver\FileFillDriver;
use IchHabRecht\Filefill\Resource\RemoteResourceCollectionFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResourceFactorySlot implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct()
    {
        if (version_compare(TYPO3_version, '9.0', '<')) {
            $this->setLogger(GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__));
        }
    }

    public function initializeResourceStorage(ResourceFactory $resourceFactory, ResourceStorage $resourceStorage)
    {
        $storageRecord = $resourceStorage->getStorageRecord();
        $isLocalDriver = $storageRecord['driver'] === 'Local';
        $isRecordEnabled = !empty($storageRecord['tx_filefill_enable']) && !empty($storageRecord['tx_filefill_resources']);
        $isStorageConfigured = !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['storages'][$resourceStorage->getUid()]);

        if (!$isLocalDriver || (!$isRecordEnabled && !$isStorageConfigured)) {
            if ($resourceStorage->getUid() > 0) {
                $this->logger->info(
                    sprintf('No filefill support for storage %s (%d) configured', $resourceStorage->getName(), $resourceStorage->getUid()),
                    [
                        'isLocalDriver' => $isLocalDriver,
                        'isRecordEnabled' => $isRecordEnabled,
                        'isStorageConfigured' => $isStorageConfigured,
                    ]
                );
            }

            return;
        }

        $closure = \Closure::bind(function () use ($resourceStorage) {
            return $resourceStorage->driver;
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
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['storages'][$resourceStorage->getUid()]
            );
        }

        $driverObject = GeneralUtility::makeInstance(
            FileFillDriver::class,
            $resourceStorage->getConfiguration(),
            $originalDriverObject,
            $remoteResourceCollection
        );
        $driverObject->setStorageUid($storageRecord['uid']);
        $driverObject->mergeConfigurationCapabilities($resourceStorage->getCapabilities());
        try {
            $driverObject->processConfiguration();
        } catch (InvalidConfigurationException $e) {
            // Intended fallthrough
        }
        $driverObject->initialize();

        $resourceStorage->setDriver($driverObject);
    }
}
