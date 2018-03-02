<?php
namespace IchHabRecht\Filefill\Resource;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class RemoteResourceCollectionFactory
{
    /**
     * @param string $configuration
     * @return RemoteResourceCollection
     */
    public static function createRemoteResourceCollectionFromConfiguration($configuration)
    {
        $remoteResources = [];

        $resourcesConfiguration = GeneralUtility::xml2array($configuration);

        foreach ((array)$resourcesConfiguration['data']['sDEF']['lDEF']['resources']['el'] as $resource) {
        }

        return GeneralUtility::makeInstance(RemoteResourceCollection::class, $remoteResources);
    }
}
