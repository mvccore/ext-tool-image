# MvcCore - Extension - Tool - Image

[![Latest Stable Version](https://img.shields.io/badge/Stable-v5.0.0-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-tool-image/releases)
[![License](https://img.shields.io/badge/License-BSD%203-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.4-brightgreen.svg?style=plastic)

## Installation
```shell
composer require mvccore/ext-tool-image
```

## Features
Extension helps to process many popular web images operations:
```php
Image	Image::AddOverlay($overlayImgFullPath, $x, $y, $alpha, $composite); // Adding overlay image is not implemented for Gd adapter
Image	Image::ApplyMask($maskImgFullPath);
Image	Image::Contain($width, $height);
Image	Image::Cover($width, $height, $orientation);
Image	Image::CreateEmptyImageResource($width, $height, $hexBgColor = 'transparent');
Image	Image::Crop($x, $y, $width, $height);
Image	Image::CropPercent($xPercentage, $yPercentage, $widthPercentage, $heightPercentage);
Image	Image::Frame($width, $height);
int	Image::GetHeight();
int	Image::GetWidth();
Image	Image::Grayscale();
array	Image::HexColor2RgbArrayColor($hexColor);
bool	Image::IsVectorGraphic();
Image	Image::Load($imgFullPath);
Image	Image::Resize($width, $height);
Image	Image::ResizeByHeight($height);
Image	Image::ResizeByPixelsCount($count);
Image	Image::ResizeByWidth($width);
Image	Image::Rotate($angle);
Image	Image::RoundCorners($x, $y);
Image	Image::Sepia($threshold = 80);
Image	Image::SetBackgroundColor($hexColor);
Image	Image::SetBackgroundImage($bgImgFullPath);
Image	Image::Save($imgFullPath, $format, $quality);
Image	Image::UnsharpMask($amount, $radius, $threshold);
```

## Usage
```php
<?php

include_once('vendor/autoload.php');

use \MvcCore\Ext\Tools;

// resize `source.jpg` to `thumb.jpg`:
$image = Tools\Image::CreateInstance()
    ->Load(__DIR__ . "/source.jpg")
    ->ResizeByHeight(150)
    ->UnsharpMask(300, 0.7, 50)
    ->Save(
        __DIR__ . "/thumb.jpg",
        Tools\Images\IFormat::JPG,
        100
    );

// display original and resized image:
echo '<html><body>',
     '<img src="source.jpg" />',
     '<br />',
     '<img src="thumb.jpg" />',
     '</body></html>';
```

# Imagick Windows binaries for dummies:
- http://windows.php.net/downloads/pecl/deps/
- http://windows.php.net/downloads/pecl/releases/imagick/3.4.3/
