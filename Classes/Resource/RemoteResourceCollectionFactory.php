<?php
declare(strict_types = 1);
namespace IchHabRecht\Filefill\Resource;

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

use IchHabRecht\Filefill\Exception\UnknownResourceException;
use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use IchHabRecht\Filefill\Resource\Domain\DomainResource;
use IchHabRecht\Filefill\Resource\Domain\DomainResourceRepository;
use IchHabRecht\Filefill\Resource\Placeholder\PlaceholderResource;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RemoteResourceCollectionFactory
{
    /**
     * @param array $configuration
     * @throws \RuntimeException
     * @return RemoteResourceCollection
     */
    public static function createRemoteResourceCollectionFromConfiguration(array $configuration)
    {
        $remoteResources = [];

        foreach ($configuration as $key => $resource) {
            if (empty($resource)) {
                continue;
            }

            switch ($key) {
                case 'domain':
                    foreach ($resource as $domain) {
                        $remoteResources[] = GeneralUtility::makeInstance(DomainResource::class, $domain);
                    }
                    break;
                case 'sys_domain':
                    $domainResourceRepository = GeneralUtility::makeInstance(DomainResourceRepository::class);
                    $remoteResources = array_merge($remoteResources, $domainResourceRepository->findAll());
                    break;
                case 'placeholder':
                    $remoteResources[] = GeneralUtility::makeInstance(PlaceholderResource::class);
                    break;
                default:
                    $remoteResource = self::getConfiguredClassIfSuitable($key);
                    if ($remoteResource !== null) {
                        $remoteResources[] = $remoteResource;
                    } else {
                        throw new UnknownResourceException('Unexpected File Fill Resource configuration "' . $key . '"', 1519788775);
                    }
            }
        }

        return GeneralUtility::makeInstance(RemoteResourceCollection::class, $remoteResources);
    }

    /**
     * @param string $flexForm
     * @throws \RuntimeException
     * @return RemoteResourceCollection
     */
    public static function createRemoteResourceCollectionFromFlexForm($flexForm)
    {
        $configuration = [];

        $resourcesConfiguration = GeneralUtility::xml2array($flexForm);

        foreach ((array)$resourcesConfiguration['data']['sDEF']['lDEF']['resources']['el'] as $resource) {
            if (empty($resource)) {
                continue;
            }

            $key = key($resource);

            switch ($key) {
                case 'domain':
                    if (empty($configuration[$key])) {
                        $configuration[$key] = [];
                    }
                    $configuration[$key] = array_merge(
                        $configuration[$key],
                        [$resource['domain']['el']['domain']['vDEF']]
                    );
                    break;
                case 'sys_domain':
                case 'placeholder':
                    $configuration[$key] = true;
                    break;
                default:
                    $remoteResource = self::getConfiguredClassIfSuitable($key);
                    if ($remoteResource !== null) {
                        $configuration[$key] = true;
                    } else {
                        throw new UnknownResourceException('Unexpected File Fill Resource configuration "' . $key . '"', 1528326468);
                    }
            }
        }

        return self::createRemoteResourceCollectionFromConfiguration($configuration);
    }

    /**
     * Returns an object of the given class if it implements the required interface
     * 
     *
     * @param string $potentialClassName
     * @return RemoteResourceInterface
     */
    protected static function getConfiguredClassIfSuitable($potentialClassName)
    {
        $remoteResource = GeneralUtility::makeInstance((string)$potentialClassName);
        if ($remoteResource instanceof RemoteResourceInterface) {
            return $remoteResource;
        }
        return null;
    }
}
