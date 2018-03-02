<?php
namespace IchHabRecht\Filefill\Slot;

use IchHabRecht\Filefill\Resource\Driver\FileFillDriver;
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
        if ($storageRecord['driver'] !== 'Local'
            || empty($storageRecord['tx_filefill_enable'])
            || empty($storageRecord['tx_filefill_resources'])
        ) {
            return;
        }

        $closure = \Closure::bind(function () use ($resourceStorage) {
            return $resourceStorage->driver;
        }, null, ResourceStorage::class);
        $originalDriverObject = $closure();

        $remoteResourceCollection = RemoteResourceCollectionFactory::createRemoteResourceCollectionFromConfiguration(
            $storageRecord['tx_filefill_resources']
        );

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
        }
        $driverObject->initialize();

        $resourceStorage->setDriver($driverObject);
    }
}
