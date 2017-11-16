# MvcCore Extension - Tool - Image

[![Latest Stable Version](https://img.shields.io/badge/Stable-v4.2.0-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-tool-image/releases)
[![License](https://img.shields.io/badge/Licence-BSD-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.3-brightgreen.svg?style=plastic)

MvcCore extension to process many popular web images operations:
```php
Image	Image::SetHeight($height)
Image	Image::SetWidth($width)
Image	Image::Resize($width, $height)
Image	Image::ScaleByWidth($width)
Image	Image::ScaleByHeight($height)
Image	Image::ScaleByPixelsCount($count)
Image	Image::Contain($width, $height)
Image	Image::Cover($width, $height, $orientation)
Image	Image::Frame($width, $height)
Image	Image::Rotate($angle)
Image	Image::Crop($x, $y, $width, $height)
Image	Image::SetBackgroundColor($hexColor)
Image	Image::SetBackgroundImage($bgImgFullPath)
Image	Image::RoundCorners($x, $y)
Image	Image::AddOverlay($overlayImgFullPath, $x, $y, $alpha, $composite)
Image	Image::CropPercent($width, $height, $x, $y)
Image	Image::Grayscale()
Image	Image::Sepia()
Image	Image::Load($imgFullPath);
Image	Image::UnsharpMask($amount, $radius, $threshold);
Image	Image::ApplyMask($maskImgFullPath)
Image	Image::Save($imgFullPath, $format, $quality)
int		Image::GetHeight()
int		Image::GetWidth()
array	Image::HexColor2RgbArrayColor($hexColor)
bool	Image::IsVectorGraphic()
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

# IMagick Windows binaries:
- http://windows.php.net/downloads/pecl/deps/
- http://windows.php.net/downloads/pecl/releases/imagick/3.4.3/