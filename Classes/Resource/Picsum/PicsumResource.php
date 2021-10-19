<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Resource\Picsum;

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

use GuzzleHttp\Exception\RequestException;
use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PicsumResource implements RemoteResourceInterface
{
    /**
     * @var array
     */
    protected $allowedFileExtensions = [
        'jpeg',
        'jpg',
    ];

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var string
     */
    protected $url = 'https://picsum.photos/id/%s/%s/%s.%s';

    public function __construct($_, RequestFactory $requestFactory = null)
    {
        $this->requestFactory = $requestFactory ?: GeneralUtility::makeInstance(RequestFactory::class);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        if (is_null($fileObject)) {
            return false;
        }
        return $fileObject instanceof FileInterface
            && in_array($fileObject->getExtension(), $this->allowedFileExtensions, true);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return string
     */
    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        if (is_null($fileObject)) {
            return false;
        }
        try {
            // Generate a fixed numerical ID from 1 to 999 from the hashed identifier.
            // Same identifier will get the same ID again.
            // Reason: There are about 1000 different images on Lorem Picsum, see https://picsum.photos/images
            $id = (int)preg_replace('/^.*?(\d).*?(\d).*?(\d).*?$/', '\1\2\3', $fileObject->getHashedIdentifier());
            $width = max(1, $fileObject->getProperty('width'));
            $height = max(1, $fileObject->getProperty('height'));
            $fileExtension = $fileObject->getExtension();
            $url = sprintf($this->url, $id, $width, $height, $fileExtension);
            $response = $this->requestFactory->request($url);
            $content = $response->getBody()->getContents();

            return $content;
        } catch (RequestException $e) {
            return false;
        }
    }
}
