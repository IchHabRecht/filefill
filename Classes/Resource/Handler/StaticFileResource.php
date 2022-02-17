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

use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StaticFileResource implements RemoteResourceInterface
{
    /**
     * @var array
     */
    protected $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        return true;
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface $fileObject
     * @return string
     */
    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        return $this->getFileContent($fileIdentifier, $this->configuration);
    }

    protected function getFileContent($fileIdentifier, $configuration): string
    {
        $content = '';

        $fileInfo = pathinfo($fileIdentifier);

        // Find content by absolute file path
        if (isset($configuration[$fileIdentifier])) {
            $content = is_array($configuration[$fileIdentifier]) ? $this->getFileContent($fileIdentifier, $configuration[$fileIdentifier]) : $configuration[$fileIdentifier];
        }

        // Find content by any folder configuration
        if (empty($content)) {
            $unusedDirnames = [$fileInfo['basename']];
            $dirnames = GeneralUtility::trimExplode('/', $fileInfo['dirname'], true);
            while (empty($content) && !empty($dirnames)) {
                $dirname = '/' . implode('/', $dirnames) . '/';
                if (isset($configuration[$dirname])) {
                    $newFilePath = implode('/', array_reverse($unusedDirnames));
                    $content = is_array($configuration[$dirname]) ? $this->getFileContent($newFilePath, $configuration[$dirname]) : $configuration[$dirname];
                }
                $unusedDirnames[] = array_pop($dirnames);
            }
        }

        // Find content by basename
        if (empty($content) && isset($configuration[$fileInfo['basename']])) {
            $content = $configuration[$fileInfo['basename']];
        }

        // Find content by extension
        $extension = '*.' . $fileInfo['extension'];
        if (empty($content) && isset($configuration[$extension])) {
            $content = $configuration[$extension];
        }

        // Find content by default
        if (empty($content) && isset($configuration['*'])) {
            $content = $configuration['*'];
        }

        return $content;
    }
}
