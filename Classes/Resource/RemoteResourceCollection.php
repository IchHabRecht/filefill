<?php
namespace IchHabRecht\Filefill\Resource;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class RemoteResourceCollection
{
    /**
     * @var array
     */
    protected $resources;

    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return bool
     */
    public function save($fileIdentifier, $filePath)
    {
        foreach ($this->resources as $remote) {
        }

        return false;
    }
}
