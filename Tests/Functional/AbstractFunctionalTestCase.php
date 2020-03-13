<?php
declare(strict_types = 1);
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

class AbstractFunctionalTestCase extends FunctionalTestCase
{
    protected $additionalFoldersToCreate = [
        'wikipedia',
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
                    ],
                ],
            ],
        ],
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/filefill',
    ];

    protected function setUp()
    {
        parent::setUp();

        $fixturePath = ORIGINAL_ROOT . 'typo3conf/ext/filefill/Tests/Functional/Fixtures/Database/';
        $this->importDataSet($fixturePath . 'sys_file_storage.xml');
        $this->importDataSet($fixturePath . 'sys_file.xml');

        $this->setUpBackendUserFromFixture(1);
    }
}
