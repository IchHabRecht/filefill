<?php
declare(strict_types = 1);
namespace IchHabRecht\Filefill\Resource\Domain;

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

use IchHabRecht\Filefill\Repository\DomainResourceRepository;
use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SysDomainResource implements RemoteResourceInterface
{
    /**
     * @var DomainResource[]
     */
    protected $domainResources;

    /**
     * @var DomainResource[]
     */
    protected static $fileIdentifierCache = [];

    /**
     * @param string $configuration
     * @param DomainResourceRepository $domainResourceRepository
     */
    public function __construct($configuration, DomainResourceRepository $domainResourceRepository = null)
    {
        if ($domainResourceRepository === null) {
            $domainResourceRepository = GeneralUtility::makeInstance(DomainResourceRepository::class);
        }
        $this->domainResources = $domainResourceRepository->findAll();
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        if (!isset(static::$fileIdentifierCache[$fileIdentifier])) {
            static::$fileIdentifierCache[$fileIdentifier] = null;
            foreach ($this->domainResources as $domainResource) {
                if ($domainResource->hasFile($fileIdentifier, $filePath)) {
                    static::$fileIdentifierCache[$fileIdentifier] = $domainResource;
                    break;
                }
            }
        }

        return static::$fileIdentifierCache[$fileIdentifier] instanceof DomainResource;
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return string
     */
    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        return static::$fileIdentifierCache[$fileIdentifier]->getFile($fileIdentifier, $filePath);
    }
}
