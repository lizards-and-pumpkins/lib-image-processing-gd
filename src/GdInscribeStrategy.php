<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidColorException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;

class GdInscribeStrategy implements ImageProcessingStrategy
{
    use ResizeImageTrait;

    /**
     * @var string
     */
    private $width;

    /**
     * @var string
     */
    private $height;

    /**
     * @var string
     */
    private $backgroundColor;

    /**
     * @param string $width
     * @param string $height
     * @param string $backgroundColor
     */
    public function __construct($width, $height, $backgroundColor)
    {
        $this->width = $width;
        $this->height = $height;
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * @param string $binaryImageData
     * @return string
     */
    public function processBinaryImageData($binaryImageData)
    {
        $this->validateImageDimensions($this->width, $this->height);
        $this->validateBackgroundColor();

        $imageInfo = $this->getImageInfo($binaryImageData);
        $this->validateImageType($imageInfo);

        $image = imagecreatefromstring($binaryImageData);
        $resultImage = imagecreatetruecolor($this->width, $this->height);
        imagefill($resultImage, 0, 0, $this->backgroundColor);

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];

        $inscribeRatio = $this->getInscribeRatio($sourceWidth, $sourceHeight);
        
        $inscribedWidth = $sourceWidth * $inscribeRatio;
        $inscribedHeight = $sourceHeight * $inscribeRatio;
        
        $xPosition = ($this->width - $inscribedWidth) / 2;
        $yPosition = ($this->height - $inscribedHeight) / 2;

        imagecopyresampled(
            $resultImage,
            $image,
            $xPosition,
            $yPosition,
            0,
            0,
            $inscribedWidth,
            $inscribedHeight,
            $sourceWidth,
            $sourceHeight
        );

        return $this->getBinaryImageOutput($resultImage, $imageInfo);
    }

    private function validateBackgroundColor()
    {
        if (!is_int($this->backgroundColor)) {
            throw new InvalidColorException(
                sprintf('Background fill color must be an integer, got "%s".', gettype($this->backgroundColor))
            );
        }
    }

    /**
     * @param int $sourceWidth
     * @param int $sourceHeight
     * @return float
     */
    private function getInscribeRatio($sourceWidth, $sourceHeight)
    {
        $widthRatio = $this->width / $sourceWidth;
        $heightRatio = $this->height / $sourceHeight;

        if ($widthRatio < $heightRatio) {
            return $widthRatio;
        }
        
        return $heightRatio;
    }
}
