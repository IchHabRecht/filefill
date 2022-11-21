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

use IchHabRecht\Filefill\Repository\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilefillTest extends AbstractFunctionalTestCase
{
    /**
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileRepository = new FileRepository();
        $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * @test
     */
    public function fileExistsWithDomainResource()
    {
        $domainResourcePath = self::STORAGE_FOLDER . '/commons/5/58/Logo_TYPO3.svg';

        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($domainResourcePath);
        $file->exists();

        $this->assertFileExists($this->getAbsoluteFilePath($domainResourcePath));

        $this->assertStringNotEqualsFile($this->getAbsoluteFilePath($domainResourcePath), '');

        $rows = $this->fileRepository->findByIdentifier('domain', 1);
        $this->assertCount(1, $rows);
    }

    /**
     * @test
     */
    public function fileExistsWithPlaceholderResource()
    {
        $placeholderResourcePath = self::STORAGE_FOLDER . '/Logo_TYPO3.png';

        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($placeholderResourcePath);
        $file->exists();

        $this->assertFileExists($this->getAbsoluteFilePath($placeholderResourcePath));

        $this->assertStringNotEqualsFile($this->getAbsoluteFilePath($placeholderResourcePath), '');

        $rows = $this->fileRepository->findByIdentifier('placeholder', 1);
        $this->assertCount(1, $rows);
    }

    public function fileExistsWithImageBuilderResourceDataProvider()
    {
        return [
            'Logo_TYPO3.png' => [
                'Logo_TYPO3.png',
            ],
            'introduction/images/typo3-book-backend-login.png' => [
                'introduction/images/typo3-book-backend-login.png',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider fileExistsWithImageBuilderResourceDataProvider
     * @param string $fileName
     */
    public function fileExistsWithImageBuilderResource(string $fileName)
    {
        $fileResourcePath = self::STORAGE_FOLDER . '/' . $fileName;

        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($fileResourcePath);
        $file->exists();

        $this->assertFileExists($this->getAbsoluteFilePath($fileResourcePath));
        $this->assertStringNotEqualsFile($this->getAbsoluteFilePath($fileResourcePath), '');
    }

    public function fileExistsWithStaticResourceDataProvider()
    {
        return [
            'path/to/example/file.txt' => [
                'path/to/example/file.txt',
                'Hello world!',
            ],
            'another/path/to/anotherFile.txt' => [
                'another/path/to/anotherFile.txt',
                'Lorem ipsum',
            ],
            'another/path/to/typo3_-_still_here.youtube' => [
                'another/path/to/typo3_-_still_here.youtube',
                'yiJjpKzCVE4',
            ],
            'another/path/lorem.pdf' => [
                'another/path/lorem.pdf',
                'This file was found in /another/path folder.',
            ],
            'path/to/typo3_-_still_here.vimeo' => [
                'path/to/typo3_-_still_here.vimeo',
                '143018597',
            ],
            'just/another/path/to/file.zip' => [
                'just/another/path/to/file.zip',
                'This is some static text for all other files.',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider fileExistsWithStaticResourceDataProvider
     * @param string $fileName
     * @param string $content
     */
    public function fileExistsWithStaticResource(string $fileName, string $content)
    {
        $fileResourcePath = self::STORAGE_FOLDER . '/' . $fileName;

        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($fileResourcePath);
        $file->exists();

        $this->assertFileExists($this->getAbsoluteFilePath($fileResourcePath));

        $this->assertStringEqualsFile($this->getAbsoluteFilePath($fileResourcePath), $content);
    }
}
