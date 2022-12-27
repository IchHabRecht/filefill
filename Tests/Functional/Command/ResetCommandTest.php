<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Tests\Functional\Command;

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

use IchHabRecht\Filefill\Tests\Functional\AbstractFunctionalTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use TYPO3\CMS\Core\Console\CommandRegistry;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResetCommandTest extends AbstractFunctionalTestCase
{
    protected $commandIdentifier = 'filefill:reset';

    protected CommandRegistry $commandRegistry;

    protected ResourceFactory $resourceFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandRegistry = GeneralUtility::makeInstance(CommandRegistry::class);
        $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->create('default');
    }

    protected function assertPreConditions(): void
    {
        parent::assertPreConditions();

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_file');
        $rows = $queryBuilder->update('sys_file')
            ->set('missing', 1)
            ->execute();
        $this->assertNotEmpty($rows);
    }

    /**
     * @test
     */
    public function executeResetCommandForStorage()
    {
        $input = new ArrayInput([
            '--storage' => 1,
        ]);
        $output = new NullOutput();

        $command = $this->commandRegistry->getCommandByIdentifier($this->commandIdentifier);
        $statusCode = $command->run($input, $output);

        $this->assertEquals(0, $statusCode);

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_file');
        $rows = $queryBuilder->count('*')
            ->from('sys_file')
            ->where(
                $queryBuilder->expr()->eq(
                    'missing',
                    $queryBuilder->createNamedParameter(1)
                ),
                $queryBuilder->expr()->eq(
                    'storage',
                    $queryBuilder->createNamedParameter(1)
                )
            )
            ->execute()
            ->fetchOne();
        $this->assertEmpty($rows);

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_file');
        $rows = $queryBuilder->count('*')
            ->from('sys_file')
            ->where(
                $queryBuilder->expr()->eq(
                    'missing',
                    $queryBuilder->createNamedParameter(1)
                ),
                $queryBuilder->expr()->eq(
                    'storage',
                    $queryBuilder->createNamedParameter(2)
                )
            )
            ->execute()
            ->fetchOne();
        $this->assertNotEmpty($rows);
    }

    /**
     * @test
     */
    public function executeResetCommandForAll()
    {
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $command = $this->commandRegistry->getCommandByIdentifier($this->commandIdentifier);
        $statusCode = $command->run($input, $output);
        $this->assertEquals(0, $statusCode);

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_file');
        $rows = $queryBuilder->count('*')
            ->from('sys_file')
            ->where(
                $queryBuilder->expr()->eq(
                    'missing',
                    $queryBuilder->createNamedParameter(1)
                )
            )
            ->execute()
            ->fetchOne();
        $this->assertEmpty($rows);
    }
}
