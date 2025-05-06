<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Tests\Unit\Resource\Handler;

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

use IchHabRecht\Filefill\Resource\Handler\StaticFileResource;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\TypoScriptStringFactory;

class StaticFileResourceTest extends TestCase
{
    protected array $configuration = [
        'path/to/example/file.txt' => 'Hello world!',
        'another' => [
            'path' => [
                'to' => [
                    'anotherFile.txt' => 'Lorem ipsum',
                    '*.youtube' => 'yiJjpKzCVE4',
                ],
                '*' => 'This file was found in /another/path folder.',
            ],
        ],
        '*.vimeo' => '143018597',
        '*' => 'This is some static text for all other files.',
    ];

    protected string $tsConfiguration = <<< EOT
path/to/example/file\.txt = Hello world!
another {
    path {
        to {
            anotherFile\.txt = Lorem ipsum
            *\.youtube = yiJjpKzCVE4
        }
        * = This file was found in /another/path folder.
    }
}
*\.vimeo = 143018597
* = This is some static text for all other files.
EOT;

    public static function getFileReturnsContentDataProvider(): array
    {
        return [
            'absolute file' => [
                '/path/to/example/file.txt',
                'Hello world!',
            ],
            'nested path to file' => [
                '/another/path/to/anotherFile.txt',
                'Lorem ipsum',
            ],
            'nested path to file extension' => [
                '/another/path/to/example/file.youtube',
                'yiJjpKzCVE4',
            ],
            'nested path to default file' => [
                '/another/path/to/example/file.txt',
                'This file was found in /another/path folder.',
            ],
            'nested path to default file (parent folder)' => [
                '/another/path/example/file.txt',
                'This file was found in /another/path folder.',
            ],
            'default file extension' => [
                '/example/path/to/file.vimeo',
                '143018597',
            ],
            'default file' => [
                '/example/path/to/file.txt',
                'This is some static text for all other files.',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getFileReturnsContentDataProvider
     */
    public function getFileReturnsContentForArrayConfiguration(string $filePath, string $expectation): void
    {
        $subject = $this->getStaticFileResource($this->configuration);
        $this->assertEquals($expectation, $subject->getFile($filePath, $filePath));
    }

    /**
     * @test
     * @dataProvider getFileReturnsContentDataProvider
     */
    public function getFileReturnsContentForTypoScriptConfiguration(string $filePath, string $expectation): void
    {
        $subject = $this->getStaticFileResource($this->tsConfiguration);
        $this->assertEquals($expectation, $subject->getFile($filePath, $filePath));
    }

    protected function getStaticFileResource($configuration): StaticFileResource
    {
        $rootNode = $this->getMockBuilder(RootNode::class)
            ->onlyMethods(['toArray'])
            ->getMock();
        $rootNode->expects($this->atMost(1))
            ->method('toArray')
            ->willReturn(
                [
                    'path\\slashto\\slashexample\\slashfile.txt' => 'Hello world!',
                    'another.' => [
                        'path.' => [
                            'to.' => [
                                'anotherFile.txt' => 'Lorem ipsum',
                                '\\asterisk.youtube' => 'yiJjpKzCVE4',
                            ],
                            '\\asterisk' => 'This file was found in \\slashanother\\slashpath folder.',
                        ],
                    ],
                    '\\asterisk.vimeo' => '143018597',
                    '\\asterisk' => 'This is some static text for all other files.',
                ]
            );
        $typoScriptStringFactory = $this->getMockBuilder(TypoScriptStringFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['parseFromStringWithIncludes'])
            ->getMock();
        $typoScriptStringFactory->expects($this->atMost(1))
            ->method('parseFromStringWithIncludes')
            ->willReturn($rootNode);

        return new StaticFileResource($configuration, $typoScriptStringFactory);
    }
}
