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

use IchHabRecht\Filefill\Repository\FileRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\RequestFactory;
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

        $this->fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
    }

    #[Test]
    public function fileExistsWithDomainResource()
    {
        $this->skipTestIfDomainResourceIsNotReachable();

        $domainResourcePath = self::STORAGE_FOLDER . '/commons/5/58/Logo_TYPO3.svg';

        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($domainResourcePath);
        $file->exists();

        $this->assertFileExists($this->getAbsoluteFilePath($domainResourcePath));

        $this->assertStringNotEqualsFile($this->getAbsoluteFilePath($domainResourcePath), '');

        $rows = $this->fileRepository->findByIdentifier('domain', 2);
        if ($rows === []) {
            // Wikimedia may have started throttling between the initial probe
            // and the actual fetch, making the placehold resource serve the
            // file instead - re-check before failing
            $this->skipTestIfDomainResourceIsNotReachable();
        }
        $this->assertCount(1, $rows);
    }

    #[Test]
    public function fileExistsWithPlaceholderResource()
    {
        $placeholderResourcePath = self::STORAGE_FOLDER . '/Logo_TYPO3.png';

        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($placeholderResourcePath);
        $file->exists();

        $this->assertFileExists($this->getAbsoluteFilePath($placeholderResourcePath));

        $this->assertStringNotEqualsFile($this->getAbsoluteFilePath($placeholderResourcePath), '');

        $rows = $this->fileRepository->findByIdentifier('placehold', 2);
        $this->assertCount(1, $rows);
    }

    public static function fileExistsWithImageBuilderResourceDataProvider()
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

    #[Test]
    #[DataProvider('fileExistsWithImageBuilderResourceDataProvider')]
    public function fileExistsWithImageBuilderResource(string $fileName)
    {
        $fileResourcePath = self::STORAGE_FOLDER . '/' . $fileName;

        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($fileResourcePath);
        $file->exists();

        $this->assertFileExists($this->getAbsoluteFilePath($fileResourcePath));
        $this->assertStringNotEqualsFile($this->getAbsoluteFilePath($fileResourcePath), '');
    }

    public static function fileExistsWithStaticResourceDataProvider()
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

    #[Test]
    #[DataProvider('fileExistsWithStaticResourceDataProvider')]
    public function fileExistsWithStaticResource(string $fileName, string $content)
    {
        $fileResourcePath = self::STORAGE_FOLDER . '/' . $fileName;

        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($fileResourcePath);
        $file->exists();

        $this->assertFileExists($this->getAbsoluteFilePath($fileResourcePath));

        $this->assertStringEqualsFile($this->getAbsoluteFilePath($fileResourcePath), $content);
    }

    /**
     * Wikimedia throttles requests from cloud IP ranges (e.g. GitHub Actions
     * runners), see https://w.wiki/4wJS. As this test asserts that the file
     * has been fetched by the domain resource, it has to be skipped instead
     * of falling back to the next configured resource.
     */
    protected function skipTestIfDomainResourceIsNotReachable(): void
    {
        try {
            $statusCode = GeneralUtility::makeInstance(RequestFactory::class)
                ->request('https://upload.wikimedia.org/wikipedia/commons/5/58/Logo_TYPO3.svg')
                ->getStatusCode();
        } catch (\Throwable $e) {
            $statusCode = 0;
        }

        if ($statusCode !== 200) {
            self::markTestSkipped('upload.wikimedia.org is not reachable, status code ' . $statusCode);
        }
    }
}
