<?php

namespace App\Services;

class ImageService
{
    public function __construct(
        private int $width = 3000,
        private int $height = 3000
    ) {}

    public function generate(): string
    {
        ini_set('memory_limit', '2048M');

        $image = imagecreatetruecolor($this->width, $this->height);

        for ($x = 0; $x < $this->width; $x += 10) {
            for ($y = 0; $y < $this->height; $y += 10) {
                $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
                imagefilledrectangle($image, $x, $y, $x + 10, $y + 10, $color);
            }
        }

        $outputPath = storage_path('app/generated-image.jpg');
        imagejpeg($image, $outputPath, 90);
        imagedestroy($image);

        return $outputPath;
    }
}
