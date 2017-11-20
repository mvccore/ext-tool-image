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

namespace MvcCore\Ext\Tool\Image;

use \MvcCore\Ext\Tool,
	\MvcCore\Ext\Tool\Image;

class Gd extends Tool\Image
{
	/**
	 * @var resource
	 */
	protected $resource;

	/**
	 * @param  mixed	  $imgFullPath
	 * @return bool|Image
	 */
	public function Load ($imgFullPath) {
		$loaded = $this->resource = @imagecreatefromstring(
			file_get_contents($imgFullPath)
		);
		if (!$loaded) return FALSE;
		// set dimensions
		list($width, $height) = getimagesize($imgFullPath);
		$this->setWidth($width);
		$this->setHeight($height);
		return $this;
	}

	/**
	 * @param  string			  $fullPath
	 * @param  string|Image\Format $format
	 * @param  int				 $quality
	 * @return Image
	 */
	public function Save ($fullPath, $format = Image\Format::PNG, $quality = NULL) {
		$format = strtolower($format);
		if (!$format) $format = "png";
		if ($format == Image\Format::JPG) $format = "jpeg";
		$functionName = 'image' . $format;
		if (!function_exists($functionName)) {
			$functionName = "imagepng";
		}
		// always create a PNG24
		if ($format == Image\Format::PNG) {
			imagesavealpha($this->resource, TRUE);
		}
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
	 * @param  int   $width
	 * @param  int   $height
	 * @return Image
	 */
	public function Resize ($width, $height) {
		$newImg = $this->createImage($width, $height);
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
	 * @param  int   $x
	 * @param  int   $y
	 * @param  int   $width
	 * @param  int   $height
	 * @return Image
	 */
	public function Crop ($x, $y, $width, $height) {
		$x = min($this->GetWidth(), max(0, $x));
		$y = min($this->GetHeight(), max(0, $y));
		$width   = min($width,  $this->GetWidth() - $x);
		$height  = min($height, $this->GetHeight() - $y);
		$newImg = $this->createImage($width, $height);
		imagecopy($newImg, $this->resource, 0, 0, $x, $y, $width, $height);
		$this->resource = $newImg;
		$this->setWidth($width);
		$this->setHeight($height);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * @param  int   $width
	 * @param  int   $height
	 * @return Image
	 */
	public function Frame ($width, $height) {
		$this->Contain($width, $height);
		$x = ($width - $this->GetWidth()) / 2;
		$y = ($height - $this->GetHeight()) / 2;
		$newImage = $this->createImage($width, $height);
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
	 * @param  string $hexColor
	 * @return Image
	 */
	public function SetBackgroundColor ($hexColor) {
		list($r, $g, $b) = $this->HexColor2RgbArrayColor($hexColor);
		// just imagefill() on the existing image doesn't work,
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
	 * @param  int   $amount	typically 50 - 200, min 0, max 500
	 * @param  float $radius	typically 0.5 - 1, min 0, max 50
	 * @param  int   $threshold typically 0 - 5, min 0, max 255
	 * @return Image
	 */
	public function UnsharpMask ($amount, $radius, $threshold) {
		Image\Gd\UnsharpMask::Process(
			$this->resource, $amount, $radius, $threshold
		);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * @param  string $maskImgFullPath
	 * @throws \InvalidArgumentException
	 * @return Image
	 */
	public function ApplyMask ($maskImgFullPath) {
		if (is_file($maskImgFullPath)) {
			$maskResource = @imagecreatefromstring(
				file_get_contents($maskImgFullPath)
			);
			if ($maskResource) {
				Image\Gd\ApplyMask::Process(
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
	 * @return Image
	 */
	public function Grayscale () {
		imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * @return Image
	 */
	public function Sepia () {
		imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
		imagefilter($this->resource, IMG_FILTER_COLORIZE, 100, 50, 0);
		$this->reinitializeImage();
		return $this;
	}

	/**
	 * @param  float $x
	 * @param  float $y
	 * @return Image
	 */
	public function RoundCorners ($x, $y) {
		Image\Gd\RoundCorners::Process($this->resource, $x, $y);
		return $this;
	}

	/**
	 * @param  float  $angle
	 * @param  string $hexBgColor
	 * @return Image
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
	 * @param  string $bgImgFullPath
	 * @return Image
	 */
	public function SetBackgroundImage ($bgImgFullPath) {
		$w = $this->GetWidth();
		$h  =$this->GetHeight();
		$newImg = imagecreatetruecolor($w, $h);
		imagesavealpha($newImg, TRUE);
		imagealphablending($newImg, TRUE);
		$bgImg = @imagecreatefromstring(
			file_get_contents($bgImgFullPath)
		);
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
		return $this;
	}

	/**
	 * GD graphic library doesn't support any vector images processing.
	 * @return bool
	 */
	public function IsVectorGraphic () {
		trigger_error(
			"GD graphic library doesn't support any vector images processing. [\\" . __CLASS__ . "::" . __METHOD__ . "()]",
			E_USER_NOTICE
		);
		return FALSE;
	}

	/**
	 * @param  string			  $overlayImgFullPath
	 * @param  int				 $x
	 * @param  int				 $y
	 * @param  int				 $alpha
	 * @param  int|Image\Composite $composite
	 * @return Image
	 */
	public function AddOverlay (
		$overlayImgFullPath, $x = 0, $y = 0, $alpha = NULL,
		$composite = Image\Composite::NORMAL
	) {
		trigger_error(
			"Adding an overlay image not implemented. [\\" . __CLASS__ . "::" . __METHOD__ . "()]",
			E_USER_NOTICE
		);
		return $this;
	}



	/**
	 * @return void
	 */
	protected function destroy() {
		imagedestroy($this->resource);
	}

	/**
	 * @param  int	  $width
	 * @param  int	  $height
	 * @return resource
	 */
	protected function createImage ($width, $height) {
		$newImg = imagecreatetruecolor($width, $height);
		imagesavealpha($newImg, true);
		imagealphablending($newImg, false);
		$trans_colour = imagecolorallocatealpha($newImg, 255, 0, 0, 127);
		imagefill($newImg, 0, 0, $trans_colour);
		return $newImg;
	}
}