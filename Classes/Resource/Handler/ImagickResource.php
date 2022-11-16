<?php

namespace IchHabRecht\Filefill\Resource\Handler;

use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Resource\FileInterface;

class ImagickResource implements RemoteResourceInterface
{
    /**
     * @var array
     */
    protected array $allowedFileExtensions = [
        'gif',
        'jpeg',
        'jpg',
        'png',
        'svg',
    ];

    protected string $color = '#f4f4f4';

    protected string $textColor = '#515151';

    /**
     * @param array|string $configuration
     */
    public function __construct($configuration)
    {
        if (is_array($configuration)) {
            if (isset($configuration['allowedFileExtensions'])) {
                if (!is_array($configuration['allowedFileExtensions'])) {
                    $this->allowedFileExtensions = explode(',', $configuration['allowedFileExtensions']);
                } else {
                    $this->allowedFileExtensions = $configuration['allowedFileExtensions'];
                }
            }

            if (isset($configuration['color'])) {
                if (str_starts_with($configuration['color'], '#') && (strlen($configuration['color']) === 4 || strlen($configuration['color']) === 7)) {
                    $this->color = $configuration['color'];
                }
            }

            if (isset($configuration['textColor'])) {
                if (str_starts_with($configuration['textColor'], '#') && (strlen($configuration['textColor']) === 4 || strlen($configuration['textColor']) === 7)) {
                    $this->textColor = $configuration['textColor'];
                }
            }
        }
        $this->allowedFileExtensions = array_map('strtolower', $this->allowedFileExtensions);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        if (!(class_exists(\Imagick::class) || extension_loaded('imagick'))) {
            throw new \RuntimeException('please install imagick');
        }

        return $fileObject instanceof FileInterface
            && in_array($fileObject->getExtension(), $this->allowedFileExtensions, true);
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return resource|string|false
     */
    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        if (!(class_exists(\Imagick::class) || extension_loaded('imagick'))) {
            throw new \RuntimeException('please install imagick');
        }

        try {
            if ($fileObject instanceof FileInterface) {
                $fileExtension = $fileObject->getExtension();
                $width = max(1, $fileObject->getProperty('width'));
                $height = max(1, $fileObject->getProperty('height'));

                $color = new \ImagickPixel($this->color);
                $image = new \Imagick();
                $image->newImage($width, $height, $color);
                $image->setImageFormat($fileExtension);

                $text = "$width x $height";
                $draw = new \ImagickDraw();
                $draw->setFontSize(20);
                $draw->setFillColor(new \ImagickPixel($this->textColor));
                $draw->setGravity(\Imagick::GRAVITY_CENTER);
                $image->annotateImage($draw, 0, 0, 0, $text);

                return $image->getImageBlob();
            }
        } catch (\ImagickException|\ImagickDrawException|\ImagickPixelException $exception) {
            return false;
        }

        return false;
    }
}
