<?php
namespace IchHabRecht\Filefill\Resource\Placeholder;

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
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlaceholderResource implements RemoteResourceInterface
{
    /**
     * @var array
     */
    protected $allowedFileExtensions = array(
        'gif',
        'jpeg',
        'jpg',
        'png',
    );

    /**
     * @var string
     */
    protected $url = 'http://via.placeholder.com/';

    /**
     * @var FileInterface[]
     */
    protected static $fileIdentifierCache = array();

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath)
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($fileExtension, $this->allowedFileExtensions, true)) {
            return false;
        }

        if (!isset(static::$fileIdentifierCache[$fileIdentifier])) {
            $resourceFactory = ResourceFactory::getInstance();
            $localPath = $filePath;
            $storage = $resourceFactory->getStorageObject(0, array(), $localPath);
            if ($storage->getUid() === 0) {
                static::$fileIdentifierCache[$fileIdentifier] = false;

                return false;
            }
            if (!$storage->isWithinProcessingFolder($fileIdentifier)) {
                try {
                    $fileObject = $resourceFactory->getFileObjectByStorageAndIdentifier($storage->getUid(), $fileIdentifier);
                } catch (\InvalidArgumentException $e) {
                    static::$fileIdentifierCache[$fileIdentifier] = false;

                    return false;
                }
            } else {
                $databaseConnection = $this->getDatabaseConnection();
                $databaseRow = $databaseConnection->exec_SELECTgetSingleRow(
                    '*',
                    'sys_file_processedfile',
                    'storage = ' . (int)$storage->getUid() .
                    ' AND identifier = ' . $databaseConnection->fullQuoteStr($fileIdentifier, 'sys_file_processedfile')
                );
                if (empty($databaseRow)) {
                    static::$fileIdentifierCache[$fileIdentifier] = false;

                    return false;
                }

                $originalFile = $resourceFactory->getFileObject((int)$databaseRow['original']);
                $taskType = $databaseRow['task_type'];
                $configuration = unserialize($databaseRow['configuration']);

                $fileObject = GeneralUtility::makeInstance(
                    'TYPO3\\CMS\\Core\\Resource\\ProcessedFile',
                    $originalFile,
                    $taskType,
                    $configuration,
                    $databaseRow
                );
            }
            static::$fileIdentifierCache[$fileIdentifier] = $fileObject;
        }

        return static::$fileIdentifierCache[$fileIdentifier] !== false;
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return string
     */
    public function getFile(
        $fileIdentifier,
        $filePath
    ) {
        if (!isset(static::$fileIdentifierCache[$fileIdentifier]) && !$this->hasFile($fileIdentifier, $filePath)) {
            return false;
        }

        $fileObject = static::$fileIdentifierCache[$fileIdentifier];
        $size = max(1, $fileObject->getProperty('width')) . 'x' . max(1, $fileObject->getProperty('height'));
        $fileExtension = $fileObject->getExtension();
        if (in_array($fileExtension, $this->allowedFileExtensions, true)) {
            $size .= '.' . $fileExtension;
        }

        $content = GeneralUtility::getUrl($this->url . $size, 0, false, $report);
        // Currently the API sends PNG images instead of GIF
        // Check for PNG image and convert to GIF manually
        if ($fileExtension === 'gif' && substr(bin2hex($content), 0, 16) === '89504e470d0a1a0a') {
            $image = imagecreatefromstring($content);
            ob_start();
            imagegif($image);
            $content = ob_get_contents();
            imagedestroy($image);
            ob_end_clean();
        }

        return $content;
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
