# MvcCore Extension - Tool - Image

[![Latest Stable Version](https://img.shields.io/badge/Stable-v4.2.1-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-tool-image/releases)
[![License](https://img.shields.io/badge/Licence-BSD-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.3-brightgreen.svg?style=plastic)

MvcCore extension to process many popular web images operations:
```php
Image	Image::AddOverlay($overlayImgFullPath, $x, $y, $alpha, $composite) // Adding overlay image is not implemented for Gd adapter.
Image	Image::ApplyMask($maskImgFullPath)
Image	Image::Contain($width, $height)
Image	Image::Cover($width, $height, $orientation)
Image	Image::Crop($x, $y, $width, $height)
Image	Image::CropPercent($xPercentage, $yPercentage, $widthPercentage, $heightPercentage)
Image	Image::Frame($width, $height)
int	Image::GetHeight()
int	Image::GetWidth()
Image	Image::Grayscale()
array	Image::HexColor2RgbArrayColor($hexColor)
bool	Image::IsVectorGraphic()
Image	Image::Load($imgFullPath);
Image	Image::Resize($width, $height)
Image	Image::ResizeByHeight($height)
Image	Image::ResizeByPixelsCount($count)
Image	Image::ResizeByWidth($width)
Image	Image::Rotate($angle)
Image	Image::RoundCorners($x, $y)
Image	Image::Sepia()
Image	Image::SetBackgroundColor($hexColor)
Image	Image::SetBackgroundImage($bgImgFullPath)
Image	Image::Save($imgFullPath, $format, $quality)
Image	Image::UnsharpMask($amount, $radius, $threshold);
```

## Installation
```shell
composer require mvccore/ext-tool-image
```

## Usage
```php
use \MvcCore\Ext\Tool,
    \MvcCore\Ext\Tool\Image;

$image = Tool\Image::GetInstance()
    ->Load(__DIR__ . "/source.jpg")
    ->ScaleByHeight(150)
    ->UnsharpMask(300, 0.7, 50)
    ->Save(
        __DIR__ . "/thumb.jpg",
        Tool\Image\Format::JPG,
        95
    );
```

# Imagick Windows binaries for dummies:
- http://windows.php.net/downloads/pecl/deps/
- http://windows.php.net/downloads/pecl/releases/imagick/3.4.3/
