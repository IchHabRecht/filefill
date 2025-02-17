<?php
namespace IchHabRecht\Filefill\Imaging;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GifBuilder extends \TYPO3\CMS\Frontend\Imaging\GifBuilder
{
    public function fileName($pre)
    {
        $fileName = parent::fileName($pre);
        $absoluteVarPath = Environment::getVarPath(); // e.g. "/var/www/html/var"
        $publicPath = Environment::getPublicPath();    // e.g. "/var/www/html/public"

        // Calculate the relative path from the public directory to the var directory:
        if (strpos($absoluteVarPath, $publicPath) === 0) {
            // Case 1: var is inside public (e.g. public/var)
            $relativeVarPath = ltrim(substr($absoluteVarPath, strlen($publicPath)), '/');
        } else {
            // Case 2: var is outside public (typical for Composer installations)
            $publicParts = explode('/', trim($publicPath, '/'));
            $varParts = explode('/', trim($absoluteVarPath, '/'));
            $commonParts = [];
            $max = min(count($publicParts), count($varParts));
            for ($i = 0; $i < $max; $i++) {
                if ($publicParts[$i] === $varParts[$i]) {
                    $commonParts[] = $publicParts[$i];
                } else {
                    break;
                }
            }
            $levelsUp = count($publicParts) - count($commonParts);
            $relativePath = str_repeat('../', $levelsUp);
            $remainder = implode('/', array_slice($varParts, count($commonParts)));
            if ($remainder !== '') {
                $relativePath .= $remainder;
            }
            $relativeVarPath = $relativePath;
        }

        // Ensure that the temporary folder exists (relative to public)
        $absoluteTemporaryPath = $publicPath . '/' . $relativeVarPath . '/transient/';
        if (!is_dir($absoluteTemporaryPath)) {
            GeneralUtility::mkdir_deep($absoluteTemporaryPath);
        }

        // Return: the relative path (from the public directory) to the temporary image
        return $relativeVarPath . '/transient/' . basename($fileName);
    }
}
