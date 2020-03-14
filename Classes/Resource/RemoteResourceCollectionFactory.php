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

use IchHabRecht\Filefill\Exception\MissingInterfaceException;
use IchHabRecht\Filefill\Exception\UnknownResourceException;
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

        foreach ($configuration as $resource) {
            if (empty($resource['identifier'])) {
                continue;
            }

            if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'][$resource['identifier']]['handler'])) {
                throw new UnknownResourceException('Unexpected File Fill Resource configuration "' . $resource['identifier'] . '"', 1519788775);
            }

            $handler = GeneralUtility::makeInstance(
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'][$resource['identifier']]['handler'],
                $resource['configuration'] ?? null
            );

            if (!$handler instanceof RemoteResourceInterface) {
                throw new MissingInterfaceException(
                    'Resource handler for "' . $resource['identifier'] . '" doesn\'t implement IchHabRecht\\Filefill\\Resource\\RemoteResourceInterface',
                    1556472885
                );
            }

            $remoteResources[] = [
                'identifier' => $resource['identifier'],
                'handler' => $handler,
            ];
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

            $identifier = key($resource);

            if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['filefill']['resourceHandler'][$identifier])) {
                throw new UnknownResourceException('Unexpected File Fill Resource configuration "' . $identifier . '"', 1528326468);
            }

            $configuration[] = [
                'identifier' => $identifier,
                'configuration' => $resource[$identifier]['el'][$identifier]['vDEF'] ?? null,
            ];
        }

        return self::createRemoteResourceCollectionFromConfiguration($configuration);
    }
}
