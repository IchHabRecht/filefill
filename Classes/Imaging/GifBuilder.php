<?php

namespace IchHabRecht\Filefill\Imaging;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GifBuilder extends \TYPO3\CMS\Frontend\Imaging\GifBuilder
{
    public function fileName(): string
    {
        $fileName = parent::fileName();
        $temporaryPath = Environment::getVarPath() . '/transient/';
        if (!is_dir($temporaryPath)) {
            GeneralUtility::mkdir_deep($temporaryPath);
        }

        return $temporaryPath . basename($fileName);
    }
}
