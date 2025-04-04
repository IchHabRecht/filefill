<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Resource\Handler;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Nicole Hummel <nicole-typo3@nimut.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use IchHabRecht\Filefill\Imaging\GifBuilder;
use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImageBuilderResource implements RemoteResourceInterface
{
    protected array $allowedFileExtensions = [
        'gif',
        'jpeg',
        'jpg',
        'png',
    ];

    protected readonly string $backgroundColor;
    protected readonly string $textColor;

    public function __construct(array|string $configuration)
    {
        if (!is_array($configuration)) {
            $colors = GeneralUtility::trimExplode(',', $configuration);
            $configuration = [
                'backgroundColor' => '#' . (ltrim($colors[0] ?? '', '#') ?: 'FFFFFF'),
                'textColor' => '#' . (ltrim($colors[1] ?? '', '#') ?: '000000'),
            ];
        }

        $this->backgroundColor = $configuration['backgroundColor'] ?? '#FFFFFF';
        $this->textColor = $configuration['textColor'] ?? '#000000';
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface $fileObject
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath, ?FileInterface $fileObject = null): bool
    {
        return $GLOBALS['TYPO3_CONF_VARS']['GFX']['gdlib']
            && $fileObject instanceof FileInterface
            && in_array($fileObject->getExtension(), $this->allowedFileExtensions, true);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface $fileObject
     * @return string|false
     */
    public function getFile($fileIdentifier, $filePath, ?FileInterface $fileObject = null): string
    {
        $content = '';

        $height = max(1, $fileObject?->getProperty('height'));
        $width = max(1, $fileObject?->getProperty('width'));

        $fileArray = [
            'XY' => sprintf('%d,%d', $width, $height),
            'backColor' => $this->backgroundColor,
            'format' => $fileObject?->getExtension() ?: 'png',
            '10' => 'BOX',
            '10.' => [
                'dimensions' => sprintf('%d,%d,%d,%d', 0, 0, $width, $height),
                'color' => $this->backgroundColor,
            ],
            '20' => 'TEXT',
            '20.' => [
                'text' => sprintf('%d x %d', $width, $height),
                'fontColor' => $this->textColor,
                'fontSize' => floor($width / 10),
                'niceText' => 1,
                'align' => 'center',
                'offset' => sprintf('%d,%d', 0, floor($height / 2 + $width / 20)),
            ],
        ];
        $gifBuilder = GeneralUtility::makeInstance(GifBuilder::class);
        $gifBuilder->start($fileArray, []);
        $theImage = $gifBuilder->gifBuild()?->getFullPath();
        if (file_exists($theImage)) {
            $content = file_get_contents($theImage);
            unlink($theImage);
        }

        return $content;
    }
}
