<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Tools\Images;

interface IImage
{
	/**
	 * Returns every time new (no singleton) `\MvcCore\Ext\Tools\Image` instance implementation.
	 * If there is `Imagick` extension loaded and no `$preferredAdapter` presented,
	 * `Imagick` instance is always created more preferably than `GD` instance.
	 * If there is no `Imagick` and no `GD` extension loaded, new `\RuntimeException` exception is thrown.
	 * @param  int|\MvcCore\Ext\Tools\Images\IAdapter $preferredAdapter optional
	 * @throws \RuntimeException
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public static function & CreateInstance ($preferredAdapter = \MvcCore\Ext\Tools\Images\IAdapter::NONE);

	/**
	 * Set custom full directory path for computation temporary images.
	 * If no temporary path configured, there is automatically chosen temporary
	 * path by `ini_get('TMP')` or by `ini_get('TEMP')`;
	 * @param string $fullPath
	 */
	public static function SetTmpDirPath ($fullPath);

	/**
	 * @param string $hexColor Color in hexadecimal format with or without leading hash.
	 * @return array [$r, $g, $b, 'type' => 'RGB'];
	 */
	public static function HexColor2RgbArrayColor ($hexColor);

	/**
	 * Get image pixel width.
	 * @return int
	 */
	public function GetWidth ();

	/**
	 * Get image pixel height.
	 * @return int
	 */
	public function GetHeight ();

	/**
	 * Scale source image by width with maintaining the aspect ratio.
	 * @param int $width Pixel width.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & ResizeByWidth ($width);

	/**
	 * Scale source image by height with maintaining the aspect ratio.
	 * @param int $height Pixel height.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & ResizeByHeight ($height);

	/**
	 * Scale source image by total final count of pixels in the resized image.
	 * Useful for list of logotypes, where is necessary to scale all logotypes into
	 * the same visual space - with approximately the same importance by filled space,
	 * so not resized by height or not by width, logotypes have always different proportions.
	 * @param int $resizedImgTotalPixelsCount Pixels count, computed by target width × height,
	 *                                        so if you want all images with approximately the same
	 *                                        size around 100 × 100 pixels, value will be 100 × 100 = 10000.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & ResizeByPixelsCount ($resizedImgTotalPixelsCount);

	/**
	 * Image will be resized into sizes not larger than `$width`
	 * or `$height` params with maintaining the aspect ratio.
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & Contain ($width, $height);

	/**
	 * Image will be resized into given `$width` and `$height` to cover whole place,
	 * with optional orientation of source image to cover final place.
	 * Possible orientation values are integers with this meaning:
	 * - `1` - top left
	 * - `2` - top center
	 * - `3` - top right
	 * - `4` - middle left
	 * - `5` - middle center
	 * - `6` - middle right
	 * - `7` - bottom left
	 * - `8` - bottom center
	 * - `9` - bottom right
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @param int|\MvcCore\Ext\Tools\Images\IOrientation $orientation Possible orientation values are integers by `\MvcCore\Ext\Tools\Images\IOrientation` interface constants.
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & Cover ($width, $height, $orientation = \MvcCore\Ext\Tools\Images\IOrientation::MIDDLE_CENTER);

	/**
	 * Crop image by percentage value from left, top, right and bottom.
	 * @param int $xPercentage Percentage value to crop from left.
	 * @param int $yPercentage Percentage value to crop from top.
	 * @param int $widthPercentage Percentage value to crop from right.
	 * @param int $heightPercentage Percentage value to crop from bottom.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & CropPercent ($xPercentage, $yPercentage, $widthPercentage, $heightPercentage);

	/**
	 * Load image into resource by given file full path.
	 * @abstract
	 * @param string $imgFullPath
	 * @throws \RuntimeException
	 * @return bool|\MvcCore\Ext\Tools\Image
	 */
	public function & Load ($imgFullPath);

	/**
	 * Save image in desired full path by format and optional quality settings.
	 * @abstract
	 * @param string $fullPath
	 * @param string|\MvcCore\Ext\Tools\Images\IFormat $format `png` by default.
	 * @param int $quality `NULL` by default - no quality settings will be used.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & Save ($fullPath, $format = \MvcCore\Ext\Tools\Images\IFormat::PNG, $quality = NULL);

	/**
	 * Resize image to desired with and height without maintaining the aspect ratio.
	 * @abstract
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & Resize ($width, $height);

	/**
	 * Crop image from left, top right or bottom side.
	 * @abstract
	 * @param int $x Pixel size to crop from left.
	 * @param int $y Pixel size to crop from top.
	 * @param int $width Pixel size to crop from right.
	 * @param int $height Pixel size to crop from bottom.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & Crop ($x, $y, $width, $height);

	/**
	 * Image will be resized into sizes not larger than `$width`
	 * or `$height` params with maintaining the aspect ratio and
	 * places without image content will be filled with transparent
	 * background color.
	 * @abstract
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & Frame ($width, $height);

	/**
	 * Set background color for prepared image.
	 * @abstract
	 * @param string $hexColor Color in hexadecimal format with or without leading hash.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & SetBackgroundColor ($hexColor);

	/**
	 * Apply to whole image Photoshop like Unsharp Mask filter to sharp image.
	 * This method is very time consuming for `GD` image implementation!
	 * @abstract
	 * @param int   $amount    Typically: 50 - 200, min. 0, max. 500.
	 * @param float $radius    Typically: 0.5 - 1, min. 0, max. 50.
	 * @param int   $threshold Typically: 0 - 5, min. 0, max. 255.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & UnsharpMask ($amount, $radius, $threshold);

	/**
	 * Apply to whole image Photoshop like Channel Mask.
	 * Image given as first argument will be used as grayscale
	 * channel mask applied to this image instance.
	 * This method is very time consuming for `GD` image implementation!
	 * @abstract
	 * @param string $maskImgFullPath
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & ApplyMask ($maskImgFullPath);

	/**
	 * Convert whole image to grayscale.
	 * @abstract
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & Grayscale ();

	/**
	 * Applies a special effect to the image, similar to the effect achieved
	 * in a photo darkroom by sepia toning. Threshold ranges from 0 to QuantumRange
	 * and is a measure of the extent of the sepia toning. A threshold of 80 is
	 * a good starting point for a reasonable tone.
	 * @abstract
	 * @param float $threshold
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & Sepia ($threshold = 80);

	/**
	 * Round image corners with the same x-round and y-round sizes.
	 * This method is very time consuming for `GD` image implementation!
	 * @abstract
	 * @param float $x X-rounding.
	 * @param float $y Y-rounding.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & RoundCorners ($x, $y);

	/**
	 * Rotate image with optional background color, transparent by default.
	 * @abstract
	 * @param float $angle
	 * @param string $hexBgColor Color in hexadecimal format with or without leading hash. Transparent by default.
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & Rotate ($angle, $hexBgColor = 'transparent');

	/**
	 * Set background image. If background image has different sizes,
	 * it's resized without maintaining the aspect ratio to the same
	 * sizes as current image instance.
	 * @abstract
	 * @param string $bgImgFullPath
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & SetBackgroundImage ($image);

	/**
	 * Return `TRUE` if image is vector graphic. `FALSE` otherwise.
	 * Return always `FALSE` for `GD` images, `GD` library cannot work with vector graphics.
	 * @abstract
	 * @return bool
	 */
	public function IsVectorGraphic ();

	/**
	 * Composite one image onto another at the specified offset.
	 * @see http://php.net/manual/en/imagick.compositeimage.php
	 * @see http://php.net/manual/en/imagick.constants.php#imagick.constants.composite-default
	 * @abstract
	 * @param string $overlayImgFullPath
	 * @param int $x
	 * @param int $y
	 * @param int $alpha
	 * @param int|\MvcCore\Ext\Tools\Images\IComposite $composite
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Images\IImage
	 */
	public function & AddOverlay (
		$overlayImgFullPath, $x = 0, $y = 0, $alpha = NULL,
		$composite = \MvcCore\Ext\Tools\Images\IComposite::NORMAL
	);

	/**
	 * Create new empty image instance.
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @param string $hexBgColor Color in hexadecimal format with or without leading hash.
	 * @return resource
	 */
	public function & CreateEmptyImageResource ($width, $height, $hexBgColor = 'transparent');
}
