<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Tests\Unit\Resource\Handler;

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

use IchHabRecht\Filefill\Resource\Handler\StaticFileResource;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class StaticFileResourceTest extends UnitTestCase
{
    protected $configuration = [
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

    protected $tsConfiguration = <<< EOT
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

    public function getFileReturnsContentDataProvider(): array
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
    public function getFileReturnsContentForArrayConfiguration(string $filePath, string $expectation)
    {
        $subject = new StaticFileResource($this->configuration);
        $this->assertEquals($expectation, $subject->getFile($filePath, $filePath));
    }

    /**
     * @test
     * @dataProvider getFileReturnsContentDataProvider
     */
    public function getFileReturnsContentForTypoScriptConfiguration(string $filePath, string $expectation)
    {
        $subject = new StaticFileResource($this->tsConfiguration);
        $this->assertEquals($expectation, $subject->getFile($filePath, $filePath));
    }
}
