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
            if (!$remote instanceof RemoteResourceInterface) {
                throw new \RuntimeException(
                    'Remote resource of type ' . get_class($remote) . ' doesn\'t implement IchHabRecht\\Filefill\\Resource\\RemoteResourceInterface',
                    1519680070
                );
            }
            if ($remote->hasFile($fileIdentifier, $filePath)) {
                $fileContent = $remote->getFile($fileIdentifier, $filePath);
                if ($fileContent === false) {
                    continue;
                }
                $absoluteFilePath = PATH_site . $filePath;
                GeneralUtility::mkdir_deep(dirname($absoluteFilePath), '');
                GeneralUtility::writeFile($absoluteFilePath, $fileContent);

                return true;
            }
        }

        return false;
    }
}
