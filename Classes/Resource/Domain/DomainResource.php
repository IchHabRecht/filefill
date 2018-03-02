<?php
namespace IchHabRecht\Filefill\Resource\Domain;

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
        $this->url = ltrim($url, '/') . '/';
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

        return (empty($report['http_code']) && $report['error'] === 200) || $report['http_code'] === 200;
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
