<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Tests\Functional;

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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractFunctionalTestCase extends FunctionalTestCase
{
    protected const STORAGE_FOLDER = 'wikipedia';

    protected $additionalFoldersToCreate = [
        self::STORAGE_FOLDER,
    ];

    protected $configurationToUseInTestInstance = [
        'EXTCONF' => [
            'filefill' => [
                'storages' => [
                    1 => [
                        [
                            'identifier' => 'domain',
                            'configuration' => 'https://upload.wikimedia.org',
                        ],
                        [
                            'identifier' => 'placeholder',
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

    protected $testExtensionsToLoad = [
        'typo3conf/ext/filefill',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $fixturePath = ORIGINAL_ROOT . 'typo3conf/ext/filefill/Tests/Functional/Fixtures/Database/';
        $this->importDataSet($fixturePath . 'sys_file_storage.xml');
        $this->importDataSet($fixturePath . 'sys_file.xml');
        $this->importDataSet($fixturePath . 'sys_file_metadata.xml');

        $this->setUpBackendUserFromFixture(1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->additionalFoldersToCreate as $folder) {
            GeneralUtility::rmdir($this->getInstancePath() . $folder, true);
            GeneralUtility::mkdir($this->getInstancePath() . $folder);
        }
    }
}
