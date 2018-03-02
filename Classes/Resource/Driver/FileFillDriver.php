<?php
namespace IchHabRecht\Filefill\Resource\Driver;

use IchHabRecht\Filefill\Resource\RemoteResourceCollection;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;

class FileFillDriver extends LocalDriver
{
    /**
     * @var DriverInterface
     */
    protected $originalDriverObject;

    /**
     * @var RemoteResourceCollection
     */
    protected $remoteResourceCollection;

    public function __construct(array $configuration, DriverInterface $originalDriverObject, RemoteResourceCollection $remoteResourceCollection)
    {
        parent::__construct($configuration);

        $this->originalDriverObject = $originalDriverObject;
        $this->remoteResourceCollection = $remoteResourceCollection;
    }

    /**
     * @param string $fileIdentifier
     * @return bool
     */
    public function fileExists($fileIdentifier)
    {
        $this->ensureFileExists($fileIdentifier);

        return $this->originalDriverObject->fileExists($fileIdentifier);
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function getPublicUrl($identifier)
    {
        $this->ensureFileExists($identifier);

        return $this->originalDriverObject->getPublicUrl($identifier);
    }

    /**
     * @param string $fileIdentifier
     * @return string
     */
    public function getFileContents($fileIdentifier)
    {
        $this->ensureFileExists($fileIdentifier);

        return $this->originalDriverObject->getFileContents($fileIdentifier);
    }

    /**
     * @param string $fileIdentifier
     * @param bool $writable
     * @return string
     */
    public function getFileForLocalProcessing($fileIdentifier, $writable = true)
    {
        $this->ensureFileExists($fileIdentifier);

        return $this->originalDriverObject->getFileForLocalProcessing($fileIdentifier, $writable);
    }

    /**
     * @param string $identifier
     * @return array
     */
    public function getPermissions($identifier)
    {
        $this->ensureFileExists($identifier);

        return $this->originalDriverObject->getPermissions($identifier);
    }

    /**
     * @param string $identifier
     * @return void
     */
    public function dumpFileContents($identifier)
    {
        $this->ensureFileExists($identifier);

        $this->originalDriverObject->dumpFileContents($identifier);
    }

    /**
     * @param string $fileIdentifier
     * @return bool
     */
    protected function ensureFileExists($fileIdentifier)
    {
        return true;
    }
}
