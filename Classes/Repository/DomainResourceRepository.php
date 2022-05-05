<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Repository;

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

use IchHabRecht\Filefill\Resource\Handler\DomainResource;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DomainResourceRepository
{
    /**
     * @return DomainResource[]
     */
    public function findAll()
    {
        $domainResources = [];

        $sites = GeneralUtility::makeInstance(SiteFinder::class)->getAllSites();
        foreach ($sites as $site) {
            $siteConfiguration = $site->getConfiguration();
            $url = $siteConfiguration['base'];
            if (!isset($domainResources[$url])) {
                $domainResources[$url] = GeneralUtility::makeInstance(DomainResource::class, $url);
            }

            foreach ($siteConfiguration['baseVariants'] ?? [] as $variant) {
                if (!isset($domainResources[$variant['base']])) {
                    $domainResources[$variant['base']] = GeneralUtility::makeInstance(DomainResource::class, $variant['base']);
                }
            }
        }

        return $domainResources;
    }
}
