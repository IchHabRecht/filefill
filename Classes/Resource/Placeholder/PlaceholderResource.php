<?php
declare(strict_types = 1);
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
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlaceholderResource implements RemoteResourceInterface
{
    /**
     * @var array
     */
    protected $allowedFileExtensions = [
        'gif',
        'jpeg',
        'jpg',
        'png',
    ];

    /**
     * @var string
     */
    protected $url = 'http://via.placeholder.com/';

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface $fileObject
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        return $fileObject instanceof FileInterface
            && in_array($fileObject->getExtension(), $this->allowedFileExtensions, true);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface $fileObject
     * @return string
     */
    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        $fileExtension = $fileObject->getExtension();
        $size = max(1, $fileObject->getProperty('width'))
            . 'x' . max(1, $fileObject->getProperty('height'))
            . $fileExtension;

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
}
