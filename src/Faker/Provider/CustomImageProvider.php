<?php

namespace App\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base as BaseProvider;

/**
 * Class CustomImageProvider
 */
final class CustomImageProvider extends BaseProvider
{
    /**
     * CustomImageProvider constructor.
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
    }

    public function customImage(int $width, int $height)
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        $im = imagecreatetruecolor($width, $height);
        // Add light background color:
        $bgColor = imagecolorallocate($im, rand(100, 255), rand(100, 255), rand(100, 255));
        imagefill($im, 0, 0, $bgColor);

        // Save the image:
        $image       = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '.png';
        $isGenerated = imagepng($im, $image);

        // Free up memory:
        imagedestroy($im);

        if (!$isGenerated) {
            return false;
        }

        return $image;
    }
}
