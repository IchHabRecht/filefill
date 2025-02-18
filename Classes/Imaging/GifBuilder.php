<?php
namespace IchHabRecht\Filefill\Imaging;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class GifBuilder extends \TYPO3\CMS\Frontend\Imaging\GifBuilder
{
    public function fileName($pre)
    {
        $fileName = parent::fileName($pre);
        $absoluteVarPath = Environment::getVarPath();
        $publicPath = Environment::getPublicPath();

        // Calculate the relative path from the public directory to the var directory:
        $relativeVarPath = PathUtility::getRelativePath($publicPath, $absoluteVarPath);

        // Ensure that the temporary folder exists (relative to public)
        $absoluteTemporaryPath = $publicPath . '/' . $relativeVarPath . '/transient/';
        if (!is_dir($absoluteTemporaryPath)) {
            GeneralUtility::mkdir_deep($absoluteTemporaryPath);
        }

        // Return: the relative path (from the public directory) to the temporary image
        return $relativeVarPath . '/transient/' . basename($fileName);
    }
}
