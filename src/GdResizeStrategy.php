<?php

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

    /**
     * @param int $width
     * @param int $height
     */
    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param string $binaryImageData
     * @return string
     */
    public function processBinaryImageData($binaryImageData)
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
