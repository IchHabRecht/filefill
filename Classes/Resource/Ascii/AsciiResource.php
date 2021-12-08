<?php

declare(strict_types=1);

namespace IchHabRecht\Filefill\Resource\Ascii;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Philipp Kitzberger <coding@kitze.net>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Resource\FileInterface;

class AsciiResource implements RemoteResourceInterface
{
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @param array $configuration
     */
    public function __construct($configuration = [])
    {
        $this->configuration = $configuration;
    }

    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        return isset($this->configuration[$fileObject->getExtension()]);
    }

    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        return $this->configuration[$fileObject->getExtension()];
    }
}
