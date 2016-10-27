<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidBinaryImageDataException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidImageDimensionException;

trait ResizeImageTrait
{
    private function validateImageDimensions(int $width, int $height)
    {
        if ($width <= 0) {
            throw new InvalidImageDimensionException(
                sprintf('Image width should be greater then zero, got %s.', $width)
            );
        }

        if ($height <= 0) {
            throw new InvalidImageDimensionException(
                sprintf('Image height should be greater then zero, got %s.', $height)
            );
        }
    }
    
    /**
     * @param string[] $imageInfo
     * @return string
     */
    private function getSaveFunctionName(array $imageInfo) : string
    {
        return 'image' . strtolower(preg_replace('/.*\//', '', $imageInfo['mime']));
    }

    /**
     * @param string[] $imageInfo
     */
    private function validateImageType(array $imageInfo)
    {
        $saveFunctionName = $this->getSaveFunctionName($imageInfo);

        if (!function_exists($saveFunctionName)) {
            throw new InvalidBinaryImageDataException(sprintf('MIME type "%s" is not supported.', $imageInfo['mime']));
        }
    }

    /**
     * @param string $binaryImageData
     * @return mixed[]
     */
    private function getImageInfo(string $binaryImageData) : array
    {
        $imageInfo = @getimagesizefromstring($binaryImageData);

        if (false === $imageInfo) {
            throw new InvalidBinaryImageDataException('Failed to get image info.');
        }

        return $imageInfo;
    }

    /**
     * @param resource $image
     * @param string[] $imageInfo
     * @return string
     */
    private function getBinaryImageOutput($image, array $imageInfo) : string
    {
        $saveFunctionName = $this->getSaveFunctionName($imageInfo);

        ob_start();
        $saveFunctionName($image);
        $binaryImageData = ob_get_contents();
        ob_end_clean();

        return $binaryImageData;
    }
}
