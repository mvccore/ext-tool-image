<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md
 */

namespace MvcCore\Ext\Tools\Images;

class Gd extends \MvcCore\Ext\Tools\Image {

	/**
	 * @var resource
	 */
	protected $resource;

	/**
	 * Load image into resource by given file full path.
	 * @param string $imgFullPath
	 * @throws \RuntimeException
	 * @return bool|\MvcCore\Ext\Tools\Image|\MvcCore\Ext\Tools\Images\IImage
	 */
	public function Load ($imgFullPath) {
		$result = FALSE;
		$loaded = $this->resource = @imagecreatefromstring(
			file_get_contents($imgFullPath)
		);
		if (!$loaded) return $result;
		// set dimensions
		list($width, $height) = getimagesize($imgFullPath);
		$this->setWidth($width);
		$this->setHeight($height);
		return $this;
	}

	/**
	 * Save image in desired full path by format and optional quality settings.
	 * @param string $fullPath
	 * @param string|\MvcCore\Ext\Tools\Images\IFormat $format `png` by default.
	 * @param int $quality `NULL` by default - no quality settings will be used.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function Save ($fullPath, $format = \MvcCore\Ext\Tools\Images\IFormat::PNG, $quality = NULL) {
		$format = strtolower($format);
		if (!$format) $format = 'png';
		if ($format == \MvcCore\Ext\Tools\Images\IFormat::JPG) $format = 'jpeg';
		$functionName = 'image' . $format;
		if (!function_exists($functionName)) {
			$functionName = 'imagepng';
		}
		// always create a PNG24
		if ($format == \MvcCore\Ext\Tools\Images\IFormat::PNG) {
			imagesavealpha($this->resource, TRUE);
		}
		if (file_exists($fullPath)) unlink($fullPath);
		switch ($format) {
			case 'jpeg':
				$quality = is_int($quality) ? $quality : 100;
				$functionName($this->resource, $fullPath, $quality);
				break;
			default:
				$functionName($this->resource, $fullPath);
		}
		return $this;
	}

	/**
	 * Resize image to desired with and height without maintaining the aspect ratio.
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function Resize ($width, $height) {
		$newImg = $this->CreateEmptyImageResource($width, $height);
		imagecopyresampled(
			$newImg, $this->resource,
			0, 0, 0, 0,
			$width, $height,
			$this->GetWidth(), $this->GetHeight()
		);
		$this->resource = $newImg;
		$this->setWidth($width);
		$this->setHeight($height);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Crop image from left, top right or bottom side.
	 * @param int $x Pixel size to crop from left.
	 * @param int $y Pixel size to crop from top.
	 * @param int $width Pixel size to crop from right.
	 * @param int $height Pixel size to crop from bottom.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function Crop ($x, $y, $width, $height) {
		$x = min($this->GetWidth(), max(0, $x));
		$y = min($this->GetHeight(), max(0, $y));
		$width   = min($width,  $this->GetWidth() - $x);
		$height  = min($height, $this->GetHeight() - $y);
		$newImg = $this->CreateEmptyImageResource($width, $height);
		imagecopy($newImg, $this->resource, 0, 0, $x, $y, $width, $height);
		$this->resource = $newImg;
		$this->setWidth($width);
		$this->setHeight($height);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Image will be resized into sizes not larger than `$width`
	 * or `$height` params with maintaining the aspect ratio and
	 * places without image content will be filled with transparent
	 * background color.
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function Frame ($width, $height) {
		$this->Contain($width, $height);
		$x = ($width - $this->GetWidth()) / 2;
		$y = ($height - $this->GetHeight()) / 2;
		$newImage = $this->CreateEmptyImageResource($width, $height);
		imagecopy(
			$newImage, $this->resource,
			$x, $y, 0, 0,
			$this->GetWidth(), $this->GetHeight()
		);
		$this->resource = $newImage;
		$this->setWidth($width);
		$this->setHeight($height);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Set background color for prepared image.
	 * @param string $hexColor Color in hexadecimal format with or without leading hash.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function SetBackgroundColor ($hexColor) {
		list($r, $g, $b) = static::HexColor2RgbArrayColor($hexColor);
		// just `imagefill()` on the existing image doesn't work,
		// so we have to create a new image, fill it and then merge
		// the source image with the background-image together
		$newImg = imagecreatetruecolor(
			$this->GetWidth(), $this->GetHeight()
		);
		$color = imagecolorallocate($newImg, $r, $g, $b);
		imagefill($newImg, 0, 0, $color);
		imagecopy(
			$newImg, $this->resource,
			0, 0, 0, 0,
			$this->GetWidth(), $this->GetHeight()
		);
		$this->resource = $newImg;
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Apply to whole image Photoshop like Unsharp Mask filter to sharp image.
	 * This method is very time consuming!
	 * @param int   $amount    Typically: 50 - 200, min. 0, max. 500.
	 * @param float $radius    Typically: 0.5 - 1, min. 0, max. 50.
	 * @param int   $threshold Typically: 0 - 5, min. 0, max. 255.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function UnsharpMask ($amount, $radius, $threshold) {
		\MvcCore\Ext\Tools\Images\Gds\UnsharpMask::Process(
			$this->resource, $amount, $radius, $threshold
		);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Apply to whole image Photoshop like Channel Mask.
	 * Image given as first argument will be used as grayscale
	 * channel mask applied to this image instance.
	 * This method is very time consuming!
	 * @param string $maskImgFullPath
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function ApplyMask ($maskImgFullPath) {
		if (is_file($maskImgFullPath)) {
			$maskResource = @imagecreatefromstring(
				file_get_contents($maskImgFullPath)
			);
			if ($maskResource) {
				\MvcCore\Ext\Tools\Images\Gds\ApplyMask::Process(
					$this->resource, $maskResource
				);
			} else {
				throw new \InvalidArgumentException(
					"Mask image not possible to read: '$maskImgFullPath'."
				);
			}
		} else {
			throw new \InvalidArgumentException(
				"Mask image not found: '$maskImgFullPath'."
			);
		}
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Convert whole image to grayscale.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function Grayscale () {
		imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Applies a special effect to the image, similar to the effect achieved
	 * in a photo darkroom by sepia toning. Threshold ranges from 0 to QuantumRange
	 * and is a measure of the extent of the sepia toning. A threshold of 80 is
	 * a good starting point for a reasonable tone.
	 * @param float $threshold
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function Sepia ($threshold = 80) {
		$ratio = $threshold / 100.0;
		imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
		imagefilter($this->resource, IMG_FILTER_BRIGHTNESS, -10);
		imagefilter($this->resource, IMG_FILTER_CONTRAST, -20);
		imagefilter($this->resource, IMG_FILTER_COLORIZE, 120 * $ratio, 60 * $ratio, 0, 0);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Round image corners with the same x-round and y-round sizes.
	 * This method is very time consuming!
	 * @param float $x X-rounding.
	 * @param float $y Y-rounding.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function RoundCorners ($x, $y) {
		\MvcCore\Ext\Tools\Images\Gds\RoundCorners::Process($this->resource, $x, $y);
		return $this;
	}

	/**
	 * Rotate image with optional background color, transparent by default.
	 * @param float $angle
	 * @param string $hexBgColor Color in hexadecimal format with or without leading hash. Transparent by default.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function Rotate ($angle, $hexBgColor = 'transparent') {
		if ($hexBgColor == 'transparent') {
			$transColor = imagecolorallocatealpha(
				$this->resource, 0, 0, 0, 127
			);
			imagefill($this->resource, 0, 0, $transColor);
			$this->resource = imagerotate($this->resource, -$angle, $transColor);
			imagealphablending($this->resource, TRUE);
			imagesavealpha($this->resource, TRUE);
		} else {
			list($r, $g, $b) = static::HexColor2RgbArrayColor($hexBgColor);
			$bgColor = imagecolorallocate(
				$this->resource, $r, $g, $b
			);
			$this->resource = imagerotate(
				$this->resource, - $angle, $bgColor, 0
			);
		}
		$this->setWidth(imagesx($this->resource));
		$this->setHeight(imagesy($this->resource));
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Set background image. If background image has different sizes,
	 * it's resized without maintaining the aspect ratio to the same
	 * sizes as current image instance.
	 * @param string $bgImgFullPath
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function SetBackgroundImage ($bgImgFullPath) {
		if (is_file($bgImgFullPath)) {
			$bgImg = @imagecreatefromstring(
				file_get_contents($bgImgFullPath)
			);
			if ($bgImg) {
				$w = $this->GetWidth();
				$h  =$this->GetHeight();
				$newImg = imagecreatetruecolor($w, $h);
				imagesavealpha($newImg, TRUE);
				imagealphablending($newImg, TRUE);
				$bgw = imagesx($bgImg);
				$bgh = imagesy($bgImg);
				if ($bgw != $w || $bgh != $h) {
					imagecopyresampled($newImg, $bgImg, 0, 0, 0, 0, $w, $h, $bgw, $bgh);
				} else {
					imagecopy($newImg, $bgImg, 0, 0, 0, 0, $w, $h);
				}
				imagedestroy($bgImg);
				imagecopy($newImg, $this->resource, 0, 0, 0, 0, $w, $h);
				imagedestroy($this->resource);
				$this->resource = $newImg;
				$this->reinitializeImage();
			} else {
				throw new \InvalidArgumentException(
					"Background image not possible to read: '$bgImgFullPath'."
				);
			}
		} else {
			throw new \InvalidArgumentException(
				"Background image not found: '$bgImgFullPath'."
			);
		}
		return $this;
	}

	/**
	 * Return always `FALSE`, because `GD` graphic library doesn't
	 * support any vector images processing.
	 * @return bool
	 */
	public function IsVectorGraphic () {
		return FALSE;
	}

	/**
	 * Do nothing with image, because `GD` graphic library doesn't
	 * support any overlay images processing. There will be triggered `E_USER_NOTICE`.
	 * @param string $overlayImgFullPath
	 * @param int $x
	 * @param int $y
	 * @param int $alpha
	 * @param int|\MvcCore\Ext\Tools\Images\IComposite $composite
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function AddOverlay (
		$overlayImgFullPath, $x = 0, $y = 0, $alpha = NULL,
		$composite = \MvcCore\Ext\Tools\Images\IComposite::NORMAL
	) {
		trigger_error(
			"Adding an overlay image not implemented. [\\".get_class()."::".__METHOD__."()]",
			E_USER_NOTICE
		);
		return $this;
	}

	/**
	 * Create new empty `GD` image resource instance.
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @param string $hexBgColor Color in hexadecimal format with or without leading hash.
	 * @return resource
	 */
	public function CreateEmptyImageResource ($width, $height, $hexBgColor = 'transparent') {
		$newImg = imagecreatetruecolor($width, $height);
		imagesavealpha($newImg, true);
		imagealphablending($newImg, false);
		if ($hexBgColor == 'transparent') {
			$colour = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
		} else {
			list($r, $g, $b) = static::HexColor2RgbArrayColor($hexBgColor);
			$colour = imagecolorallocatealpha($newImg, $r, $g, $b, 0);
		}
		imagefill($newImg, 0, 0, $colour);
		return $newImg;
	}

	/**
	 * Destroy current image instance resource in RAM.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	protected function destroy() {
		imagedestroy($this->resource);
		return $this;
	}
}
