<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;

class GdInscribeStrategy implements ImageProcessingStrategy
{
    use ResizeImageTrait;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $backgroundColor;

    public function __construct(int $width, int $height, int $backgroundColor)
    {
        $this->width = $width;
        $this->height = $height;
        $this->backgroundColor = $backgroundColor;
    }

    public function processBinaryImageData(string $binaryImageData) : string
    {
        $this->validateImageDimensions($this->width, $this->height);

        $imageInfo = $this->getImageInfo($binaryImageData);
        $this->validateImageType($imageInfo);

        $image = imagecreatefromstring($binaryImageData);
        $resultImage = imagecreatetruecolor($this->width, $this->height);
        imagefill($resultImage, 0, 0, $this->backgroundColor);

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];

        $inscribeRatio = $this->getInscribeRatio($sourceWidth, $sourceHeight);
        
        $inscribedWidth = (int) round($sourceWidth * $inscribeRatio);
        $inscribedHeight = (int) round($sourceHeight * $inscribeRatio);
        
        $xPosition = (int) round(($this->width - $inscribedWidth) / 2);
        $yPosition = (int) round(($this->height - $inscribedHeight) / 2);

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

    private function getInscribeRatio(int $sourceWidth, int $sourceHeight) : float
    {
        $widthRatio = $this->width / $sourceWidth;
        $heightRatio = $this->height / $sourceHeight;

        if ($widthRatio < $heightRatio) {
            return $widthRatio;
        }
        
        return $heightRatio;
    }
}
