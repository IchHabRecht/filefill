<?php
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

use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DomainResource implements RemoteResourceInterface
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = rtrim($url, '/') . '/';
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath)
    {
        $report = [];
        GeneralUtility::getUrl($this->url . ltrim($filePath, '/'), 2, false, $report);

        $isCurlResponse = in_array($report['lib'], ['cURL', 'GuzzleHttp'], true)
            && (
                (empty($report['http_code']) && (int)$report['error'] === 200)
                || (int)$report['http_code'] === 200
            );
        $isSocketResponse = $report['lib'] === 'socket' && $report['error'] === 0;

        return $isCurlResponse || $isSocketResponse;
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return string
     */
    public function getFile($fileIdentifier, $filePath)
    {
        return GeneralUtility::getUrl($this->url . ltrim($filePath, '/'), 0, false, $report);
    }
}
