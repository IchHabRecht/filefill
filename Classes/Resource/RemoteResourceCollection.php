<?php
namespace IchHabRecht\Filefill\Resource;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RemoteResourceCollection
{
    /**
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * @var array
     */
    protected $resources;

    public function __construct(array $resources, FileRepository $fileRepository = null)
    {
        $this->resources = $resources;
        $this->fileRepository = $fileRepository ?: GeneralUtility::makeInstance(FileRepository::class);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @return bool
     */
    public function save($fileIdentifier, $filePath)
    {
        foreach ($this->resources as $resource) {
            if (!$resource['handler'] instanceof RemoteResourceInterface) {
                throw new \RuntimeException(
                    'Remote resource of type ' . get_class($resource['handler']) . ' doesn\'t implement IchHabRecht\\Filefill\\Resource\\RemoteResourceInterface',
                    1519680070
                );
            }
            if ($resource['handler']->hasFile($fileIdentifier, $filePath)) {
                $fileContent = $resource['handler']->getFile($fileIdentifier, $filePath);
                if ($fileContent === false) {
                    continue;
                }
                if (is_resource($fileContent) && get_resource_type($fileContent) !== 'stream') {
                    throw new \RuntimeException(
                        'Cannot handle resource type "' . get_resource_type($fileContent) . '" as file content',
                        1583421958
                    );
                }

                $absoluteFilePath = PATH_site . $filePath;
                GeneralUtility::mkdir_deep(dirname($absoluteFilePath));
                file_put_contents($absoluteFilePath, $fileContent);

                $this->fileRepository->updateIdentifier($fileIdentifier, $resource['identifier']);

                if (is_resource($fileContent) && get_resource_type($fileContent) === 'stream') {
                    fclose($fileContent);
                }

                return true;
            }
        }

        return false;
    }
}
