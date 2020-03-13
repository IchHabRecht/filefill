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

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilefillTest extends AbstractFunctionalTestCase
{
    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var string
     */
    protected $domainResourcePath = 'wikipedia/commons/5/58/Logo_TYPO3.svg';

    /**
     * @var string
     */
    protected $placeholderResourcePath = 'wikipedia/Logo_TYPO3.png';

    protected function setUp()
    {
        parent::setUp();

        $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * @test
     */
    public function fileExistsWithDomainResource()
    {
        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($this->domainResourcePath);
        $file->exists();

        $this->assertFileExists($this->getInstancePath() . $this->domainResourcePath);
    }

    /**
     * @test
     */
    public function fileExistsWithPlaceholderResource()
    {
        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($this->placeholderResourcePath);
        $file->exists();

        $this->assertFileExists($this->getInstancePath() . $this->placeholderResourcePath);
    }
}
