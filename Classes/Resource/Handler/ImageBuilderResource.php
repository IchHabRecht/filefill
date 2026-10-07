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
        'avif',
        'gif',
        'jpeg',
        'jpg',
        'png',
        'webp',
    ];

    protected readonly string $backgroundColor;
    protected readonly string $textColor;
    protected readonly int $maxWidth;
    protected readonly int $maxHeight;
    protected readonly bool $text;

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
        // Fall back to the default limit of GifBuilder::start()
        $this->maxWidth = max(0, (int)($configuration['maxWidth'] ?? 0)) ?: 2000;
        $this->maxHeight = max(0, (int)($configuration['maxHeight'] ?? 0)) ?: 2000;
        $this->text = (bool)($configuration['text'] ?? true);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface $fileObject
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath, ?FileInterface $fileObject = null): bool
    {
        $extension = $fileObject?->getExtension();

        // Only offer formats GD can write (e.g. imagewebp)
        return in_array($extension, $this->allowedFileExtensions, true)
            && function_exists('image' . ($extension === 'jpg' ? 'jpeg' : $extension));
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface $fileObject
     * @return string|false
     */
    public function getFile($fileIdentifier, $filePath, ?FileInterface $fileObject = null): string|false
    {
        $content = false;

        $originalHeight = max(1, (int)$fileObject?->getProperty('height'));
        $originalWidth = max(1, (int)$fileObject?->getProperty('width'));

        // GifBuilder clamps each axis on its own, so scale proportionally beforehand to keep the aspect ratio
        $scale = min(1, $this->maxWidth / $originalWidth, $this->maxHeight / $originalHeight);
        $height = max(1, (int)round($originalHeight * $scale));
        $width = max(1, (int)round($originalWidth * $scale));

        $fileArray = [
            'XY' => sprintf('%d,%d', $width, $height),
            'maxWidth' => $this->maxWidth,
            'maxHeight' => $this->maxHeight,
            'backColor' => $this->backgroundColor,
            'format' => $fileObject?->getExtension() ?: 'png',
            '10' => 'BOX',
            '10.' => [
                'dimensions' => sprintf('%d,%d,%d,%d', 0, 0, $width, $height),
                'color' => $this->backgroundColor,
            ],
        ];
        if ($this->text) {
            // Limit the font size by the height as well, so the text fits into wide images
            $fontSize = floor(min($width / 10, $height / 2));
            $fileArray['20'] = 'TEXT';
            $fileArray['20.'] = [
                'text' => sprintf('%d x %d', $originalWidth, $originalHeight),
                'fontColor' => $this->textColor,
                'fontSize' => $fontSize,
                'align' => 'center',
                'offset' => sprintf('%d,%d', 0, floor($height / 2 + $fontSize / 2)),
            ];
        }
        $gifBuilder = GeneralUtility::makeInstance(GifBuilder::class);
        $gifBuilder->start($fileArray, []);
        $theImage = $gifBuilder->gifBuild()?->getFullPath();
        if ($theImage !== null && file_exists($theImage)) {
            $content = file_get_contents($theImage);
            unlink($theImage);
        }

        return $content;
    }
}
