<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Resource\Handler;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Nicole Hummel <nicole-typo3@nimut.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use GuzzleHttp\Exception\RequestException;
use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlaceholdResource implements RemoteResourceInterface
{
    protected array $allowedFileExtensions = [
        'avif',
        'gif',
        'jpeg',
        'jpg',
        'png',
        'svg',
        'webp',
    ];

    protected RequestFactory $requestFactory;
    protected string $url = 'https://placehold.co/';

    public function __construct($_, RequestFactory $requestFactory = null)
    {
        $this->requestFactory = $requestFactory ?: GeneralUtility::makeInstance(RequestFactory::class);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface $fileObject
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null): bool
    {
        return $fileObject instanceof FileInterface
            && in_array($fileObject->getExtension(), $this->allowedFileExtensions, true);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface $fileObject
     * @return string|false
     */
    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null): false|string
    {
        try {
            $fileExtension = $fileObject?->getExtension() ?: 'png';
            $size = sprintf(
                '%dx%d.%s',
                max(1, $fileObject?->getProperty('width')),
                max(1, $fileObject?->getProperty('height')),
                $fileExtension
            );
            $response = $this->requestFactory->request($this->url . $size);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return false;
        }
    }
}
