<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Tests\Functional;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class AbstractFunctionalTestCase extends FunctionalTestCase
{
    protected const STORAGE_FOLDER = 'wikipedia';

    protected array $additionalFoldersToCreate = [
        'fileadmin',
        self::STORAGE_FOLDER,
    ];

    protected array $configurationToUseInTestInstance = [
        // Wikimedia throttles requests with a generic User-Agent from cloud/CI
        // IP ranges, see https://w.wiki/4wJS
        'HTTP' => [
            'headers' => [
                'User-Agent' => 'TYPO3-filefill-tests/1.0 (+https://github.com/IchHabRecht/filefill)',
            ],
        ],
        'EXTCONF' => [
            'filefill' => [
                'storages' => [
                    2 => [
                        [
                            'identifier' => 'domain',
                            'configuration' => 'https://upload.wikimedia.org',
                        ],
                        [
                            'identifier' => 'placehold',
                        ],
                        [
                            'identifier' => 'static',
                            'configuration' => [
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
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/filefill',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $fixturePath = ORIGINAL_ROOT . 'typo3conf/ext/filefill/Tests/Functional/Fixtures/Database/';
        $this->importCSVDataSet($fixturePath . 'be_users.csv');
        $this->importCSVDataSet($fixturePath . 'sys_file_storage.csv');
        $this->importCSVDataSet($fixturePath . 'sys_file.csv');
        $this->importCSVDataSet($fixturePath . 'sys_file_metadata.csv');

        $this->setUpBackendUser(1);
    }

    protected function tearDown(): void
    {
        foreach ($this->additionalFoldersToCreate as $folder) {
            $absoluteFolderPath = $this->getAbsoluteFilePath($folder);
            GeneralUtility::rmdir($absoluteFolderPath, true);
            GeneralUtility::mkdir($absoluteFolderPath);
        }
        parent::tearDown();
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected function getAbsoluteFilePath(string $filePath)
    {
        return implode(
            '/',
            [
                rtrim(self::getInstancePath(), '\\/'),
                ltrim($filePath, '\\/'),
            ]
        );
    }
}
