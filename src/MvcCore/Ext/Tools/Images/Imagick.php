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

class Imagick extends \MvcCore\Ext\Tools\Image
{
	/**
	 * @var \Imagick
	 */
	protected $resource;

	/**
	 * @var string
	 */
	protected $imgFullPath;

	/**
	 * Load image into resource by given file fullpath.
	 * @param string $imgFullPath
	 * @throws \RuntimeException
	 * @return bool|\MvcCore\Ext\Tools\Image
	 */
	public function & Load ($imgFullPath) {
		$result = FALSE;
		if ($this->resource) {
			unset($this->resource);
			$this->resource = NULL;
		}
		try {
			$this->resource = new \Imagick($imgFullPath);
			if (!$this->resource->readImage($imgFullPath)) {
				return $result;
			}
			$this->imgFullPath = $imgFullPath;
		} catch (\Exception $e) {
			throw new \RuntimeException(
				"Image `$imgFullPath` was not possible to load by `\Imagick::readImage();`. "
				.$e->getMessage() ,$e->getCode()
			);
		}
		// set dimensions
		$this->setWidth($this->resource->getImageWidth());
		$this->setHeight($this->resource->getImageHeight());
		return $this;
	}

	/**
	 * Save image in desired full path by format and optional quality settings.
	 * @param  string $fullPath
	 * @param string|\MvcCore\Ext\Tools\Images\IFormat $format `png` by default.
	 * @param int $quality `NULL` by default - no quality settings will be used.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function & Save ($fullPath, $format = \MvcCore\Ext\Tools\Images\IFormat::PNG, $quality = NULL) {
		if (!$format) $format = \MvcCore\Ext\Tools\Images\IFormat::PNG;
		$this->resource->stripimage();
		$this->resource->setImageFormat($format);
		if ($quality !== NULL && is_int($quality)) {
			$this->resource->setCompressionQuality($quality);
			$this->resource->setImageCompressionQuality($quality);
		}
		if (file_exists($fullPath)) unlink($fullPath);
		$this->resource->writeImage($fullPath);
		return $this;
	}

	/**
	 * Resize image to desired with and height without maintaining the aspect ratio.
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function & Resize ($width, $height) {
		// this is the check for vector formats because
		// they need to have a resolution set this does
		// only work if "resize" is the first step
		// in the image-pipeline
		if($this->IsVectorGraphic()) {
			// the resolution has to be set before loading the image,
			// that's why we have to destroy the instance and load it again
			$res = $this->resource->getImageResolution();
			$xRatio = $res['x'] / $this->resource->getImageWidth();
			$yRatio = $res['y'] / $this->resource->getImageHeight();
			$this->resource->removeImage();
			$this->resource->setResolution($width * $xRatio, $height * $yRatio);
			$this->resource->readImage($this->imgFullPath);
		} else {
			$this->resource->resizeimage(
				$width, $height, \Imagick::FILTER_UNDEFINED, 1, false
			);
		}
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
	public function & Crop ($x, $y, $width, $height) {
		$this->resource->cropImage($width, $height, $x, $y);
		$this->resource->setImagePage($width, $height, 0, 0);
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
	public function & Frame ($width, $height) {
		$this->Contain($width, $height);
		$x = ($width - $this->GetWidth()) / 2;
		$y = ($height - $this->GetHeight()) / 2;
		$newImage = $this->CreateEmptyImageResource($width, $height);
		$newImage->compositeImage(
			$this->resource, \MvcCore\Ext\Tools\Images\Imagicks\IComposite::NORMAL,
			$x, $y
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
	public function & SetBackgroundColor ($hexColor = 'transparent') {
		$newImage = $this->CreateEmptyImageResource(
			$this->GetWidth(), $this->GetHeight(), $hexColor
		);
		$newImage->compositeImage(
			$this->resource, \MvcCore\Ext\Tools\Images\Imagicks\IComposite::NORMAL,
			0, 0
		);
		$this->resource = $newImage;
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Apply to whole image Photoshop like Unsharp Mask filter to sharp image.
	 * This method is time consuming!
	 * @param int   $amount    Typically: 50 - 200, min. 0, max. 500.
	 * @param float $radius    Typically: 0.5 - 1, min. 0, max. 50.
	 * @param int   $threshold Typically: 0 - 5, min. 0, max. 255.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function & UnsharpMask ($amount, $radius, $threshold) {
		$sigma = ($radius < 1) ? $radius : sqrt($radius) ;
		$amount = ($amount * 2.55) / 100;
		$threshold = $threshold / 255;
		$this->resource->unsharpMaskImage(
			$radius , $sigma , $amount , $threshold
		);
		return $this;
	}

	/**
	 * Apply to whole image Photoshop like Channel Mask.
	 * Image given as first argument will be used as grayscale
	 * channel mask applied to this image instance.
	 * This method is time consuming!
	 * @param string $maskImgFullPath
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function & ApplyMask ($maskImgFullPath) {
		if (is_file($maskImgFullPath)) {
			$this->resource->setImageMatte(TRUE);
			$newImage = new \Imagick($maskImgFullPath);
			$newImage->readimage($maskImgFullPath);
			$newImage->resizeimage(
				$this->GetWidth(), $this->GetHeight(),
				\Imagick::FILTER_UNDEFINED, 1, false
			);
			$this->resource->compositeImage(
				$newImage, \Imagick::COMPOSITE_DSTIN, 0 ,0
			);
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
	public function & Grayscale () {
		$this->resource->setImageType(\Imagick::IMGTYPE_GRAYSCALEMATTE);
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
	public function & Sepia ($threshold = 80) {
		$this->resource->sepiatoneimage($threshold);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Round image corners with the same x-round and y-round sizes.
	 * @param float $x X-rounding.
	 * @param float $y Y-rounding.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function & RoundCorners ($x, $y) {
		$this->resource->roundCorners($x, $y);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Rotate image with optional background color, transparent by default.
	 * @param float $angle
	 * @param string $hexBgColor Color in hexadecimal format with or without leading hash. Transparent by default.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function & Rotate ($angle, $hexBgColor = 'transparent') {
		$this->resource->rotateImage(
			new \ImagickPixel($hexBgColor == 'transparent' ? 'rgba(0%, 0%, 0%, 0.0)' : $hexBgColor),
			$angle
		);
		$this->setWidth($this->resource->getimagewidth());
		$this->setHeight($this->resource->getimageheight());
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Set background image. If background image has different sizes,
	 * it's resized without maintaining the aspect ratio to the same
	 * sizes as current image instance.
	 * @param  string $bgImgFullPath
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function & SetBackgroundImage ($bgImgFullPath) {
		if (is_file($bgImgFullPath)) {
			$newImage = new \Imagick($bgImgFullPath);
			$newImage->readimage($bgImgFullPath);
			$newImage->resizeimage(
				$this->GetWidth(), $this->GetHeight(),
				\Imagick::FILTER_UNDEFINED, 1, FALSE
			);
			$newImage->compositeImage(
				$this->resource,
				\Imagick::COMPOSITE_DEFAULT,
				0 ,0
			);
			$this->resource = $newImage;
		} else {
			throw new \InvalidArgumentException(
				"Background image not found: '$bgImgFullPath'."
			);
		}
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Return `TRUE` if image is vector graphic. `FALSE` otherwise.
	 * @return bool
	 */
	public function IsVectorGraphic () {
		$result = FALSE;
		try {
			$type = $this->resource->getimageformat();
			$vectorTypes = [
				"EPT","EPDF","EPI","EPS","EPS2",
				"EPS3","EPSF","EPSI","EPT","PDF",
				"PFA","PFB","PFM","PS","PS2",
				"PS3","PSB","SVG","SVGZ"
			];
			if (in_array($type, $vectorTypes)) {
				$result = TRUE;
			}
		} catch (\Exception $e) {
		}
		return $result;
	}

	/**
	 * Composite one image onto another at the specified offset.
	 * @see http://php.net/manual/en/imagick.compositeimage.php
	 * @see http://php.net/manual/en/imagick.constants.php#imagick.constants.composite-default
	 * @param string $overlayImgFullPath
	 * @param int $x
	 * @param int $y
	 * @param int $alpha
	 * @param int|\MvcCore\Ext\Tools\Images\Imagicks\IComposite $composite
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Tools\Image
	 */
	public function & AddOverlay (
		$overlayImgFullPath, $x = 0, $y = 0, $alpha = NULL,
		$composite = \MvcCore\Ext\Tools\Images\Imagicks\IComposite::NORMAL
	) {
		// 100 alpha is default
		if (!is_int($alpha)) $alpha = 100;
		$alpha = round($alpha / 100, 1);
		if (is_file($overlayImgFullPath)) {
			$newImage = new \Imagick($overlayImgFullPath);
			$newImage->readimage($overlayImgFullPath);
			$newImage->evaluateImage(
				\Imagick::EVALUATE_MULTIPLY, $alpha, \Imagick::CHANNEL_ALPHA
			);
			$this->resource->compositeImage(
				$newImage, $composite, $x ,$y
			);
		} else {
			throw new \InvalidArgumentException(
				"Overlay image not found: '$overlayImgFullPath'."
			);
		}
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * Create new empty`\Imagick` image instance.
	 * @param int $width Pixel width.
	 * @param int $height Pixel height.
	 * @param string $hexBgColor Color in hexadecimal format with or without leading hash.
	 * @return \Imagick
	 */
	public function & CreateEmptyImageResource ($width, $height, $hexBgColor = 'transparent') {
		$newImage = new \Imagick();
		$newImage->newimage(
			$width, $height,
			new \ImagickPixel($hexBgColor == 'transparent' ? 'rgba(0%, 0%, 0%, 0.0)' : $hexBgColor)
		);
		$newImage->setImageFormat('png');
		$this->reinitializeImage();
		return $newImage;
	}

	/**
	 * Destroy current image instance resource in RAM.
	 * @return \MvcCore\Ext\Tools\Image
	 */
	protected function & destroy() {
		$this->resource->destroy();
		return $this;
	}
}
