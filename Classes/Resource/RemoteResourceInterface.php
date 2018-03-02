<?php
namespace IchHabRecht\Filefill\Resource;

interface RemoteResourceInterface
{
    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath);

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return string
     */
    public function getFile($fileIdentifier, $filePath);
}
