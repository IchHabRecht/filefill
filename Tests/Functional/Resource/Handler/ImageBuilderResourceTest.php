<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Tests\Functional\Resource\Handler;

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

use IchHabRecht\Filefill\Resource\Handler\ImageBuilderResource;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ImageBuilderResourceTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/filefill',
    ];

    public static function getFileKeepsAspectRatioDataProvider(): array
    {
        return [
            'landscape above default limit' => [
                [],
                'png',
                [3072, 2048],
                [2000, 1333],
            ],
            'portrait above default limit' => [
                [],
                'png',
                [2048, 3072],
                [1333, 2000],
            ],
            'negative limit falls back to default' => [
                ['maxWidth' => -1],
                'png',
                [3072, 2048],
                [2000, 1333],
            ],
            'maxWidth' => [
                ['maxWidth' => 1440],
                'jpg',
                [3072, 2048],
                [1440, 960],
            ],
            'maxHeight' => [
                ['maxHeight' => 600],
                'gif',
                [3072, 2048],
                [900, 600],
            ],
            'raised limits' => [
                ['maxWidth' => 3000, 'maxHeight' => 3000, 'text' => false],
                'png',
                [3072, 2048],
                [3000, 2000],
            ],
            'no upscaling' => [
                ['maxWidth' => 1440],
                'png',
                [500, 300],
                [500, 300],
            ],
            'wide image' => [
                [],
                'png',
                [8000, 200],
                [2000, 50],
            ],
            'webp' => [
                ['maxWidth' => 1440],
                'webp',
                [3072, 2048],
                [1440, 960],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getFileKeepsAspectRatioDataProvider
     * @param array $configuration
     * @param string $extension
     * @param int[] $originalSize
     * @param int[] $expectedSize
     */
    public function getFileKeepsAspectRatio(array $configuration, string $extension, array $originalSize, array $expectedSize): void
    {
        $file = $this->createMock(FileInterface::class);
        $file->method('getExtension')->willReturn($extension);
        $file->method('getProperty')->willReturnMap([
            ['width', $originalSize[0]],
            ['height', $originalSize[1]],
        ]);

        $imageBuilderResource = new ImageBuilderResource($configuration);
        $this->assertTrue($imageBuilderResource->hasFile('', '', $file));

        $imageSize = getimagesizefromstring($imageBuilderResource->getFile('', '', $file));
        $this->assertSame($expectedSize, [$imageSize[0], $imageSize[1]]);
    }
}
