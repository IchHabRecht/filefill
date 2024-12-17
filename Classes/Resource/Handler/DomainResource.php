<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Resource\Handler;

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
use GuzzleHttp\Exception\TransferException;
use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

class DomainResource implements RemoteResourceInterface
{
    protected RequestFactory $requestFactory;

    protected string $url;

    /**
     * @param string $configuration
     * @param ?RequestFactory $requestFactory
     */
    public function __construct($configuration, RequestFactory $requestFactory = null)
    {
        $this->requestFactory = $requestFactory ?: GeneralUtility::makeInstance(RequestFactory::class);
        $urlParts = parse_url((string)$configuration);
        $urlParts['scheme'] = $urlParts['scheme'] ?? $_SERVER['REQUEST_SCHEME'];
        $this->url = rtrim(HttpUtility::buildUrl($urlParts), '/') . '/';
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        try {
            $response = $this->requestFactory->request($this->url . ltrim($filePath, '/'), 'HEAD');

            return $response->getStatusCode() === 200;
        } catch (TransferException $e) {
            return false;
        }
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return resource|string
     */
    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        try {
            $fileName = $this->url . ltrim($filePath, '/');

            return @fopen($fileName, 'r') ?: $this->requestFactory->request($fileName)->getBody()->getContents();
        } catch (RequestException $e) {
            return false;
        }
    }
}
