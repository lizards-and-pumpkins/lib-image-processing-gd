<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;

class GdResizeStrategy implements ImageProcessingStrategy
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

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function processBinaryImageData(string $binaryImageData) : string
    {
        $this->validateImageDimensions($this->width, $this->height);

        $imageInfo = $this->getImageInfo($binaryImageData);
        $this->validateImageType($imageInfo);

        $image = imagecreatefromstring($binaryImageData);
        $resultImage = imagecreatetruecolor($this->width, $this->height);

        imagecopyresampled($resultImage, $image, 0, 0, 0, 0, $this->width, $this->height, $imageInfo[0], $imageInfo[1]);

        return $this->getBinaryImageOutput($resultImage, $imageInfo);
    }
}
