<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidBinaryImageDataException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidImageDimensionException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd\GdInscribeStrategy
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd\ResizeImageTrait
 */
class GdInscribeStrategyTest extends TestCase
{
    protected function setUp()
    {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('The PHP extension imagick is not installed');
        }
    }
    
    public function testImageProcessorStrategyInterfaceIsImplemented()
    {
        $strategy = new GdInscribeStrategy(1, 1, 0);
        $this->assertInstanceOf(ImageProcessingStrategy::class, $strategy);
    }

    public function testExceptionIsThrownIfWidthIsNotAnInteger()
    {
        $this->expectException(\TypeError::class);
        new GdInscribeStrategy('foo', 1, 0);
    }

    public function testExceptionIsThrownIfWidthIsNotPositive()
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image width should be greater then zero, got 0.');
        (new GdInscribeStrategy(0, 1, 0))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfHeightIsNotAnInteger()
    {
        $this->expectException(\TypeError::class);
        new GdInscribeStrategy(1, 'foo', 0);
    }

    public function testExceptionIsThrownIfHeightIsNotPositive()
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image height should be greater then zero, got -1.');
        (new GdInscribeStrategy(1, -1, 0))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfBackgroundColorIsNotAnInteger()
    {
        $this->expectException(\TypeError::class);
        (new GdInscribeStrategy(1, 1, 'red'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageStreamIsNotValid()
    {
        $this->expectException(InvalidBinaryImageDataException::class);
        (new GdInscribeStrategy(1, 1, 0))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageFormatIsNotSupported()
    {
        $this->expectException(InvalidBinaryImageDataException::class);

        $imageStream = file_get_contents(__DIR__ . '/../fixture/blank.ico');

        (new GdInscribeStrategy(1, 1, 0))->processBinaryImageData($imageStream);
    }

    /**
     * @dataProvider frameDimensionsProvider
     */
    public function testImageIsInscribedIntoLandscapeFrame(int $frameWidth, int $frameHeight)
    {
        $imageStream = file_get_contents(__DIR__ . '/../fixture/image.jpg');

        $strategy = new GdInscribeStrategy($frameWidth, $frameHeight, 0);
        $result = $strategy->processBinaryImageData($imageStream);
        $resultImageInfo = getimagesizefromstring($result);

        $this->assertEquals($frameWidth, $resultImageInfo[0]);
        $this->assertEquals($frameHeight, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }

    /**
     * @return array[]
     */
    public function frameDimensionsProvider() : array
    {
        return [
            [15, 10],
            [15, 15],
            [10, 15],
        ];
    }
}
