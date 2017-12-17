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

namespace MvcCore\Ext\Tool;

use \MvcCore\Ext\Tool\Image;

abstract class Image
{
	/**
	 * MvcCore Extension - Tool - Image - version:
	 * Comparation by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '4.3.1';

	/**
	 * Set full directory path for computation temporary images, __DIR__ by default.
	 * @param string $fullPath
	 */
	public static function SetTmpDirPath ($fullPath) {
		static::$tmpDir = $fullPath;
	}

	/**
	 * Returns new supported \MvcCore\Ext\Tool\Image instance implementation.
	 * @throws \RuntimeException
	 * @param  int|Image\Adapter $preferedAdapter optional
	 * @return Image\Gd|Image\Imagick
	 */
	public static function GetInstance ($preferedAdapter = Image\Adapter::NONE) {
		$imagick = extension_loaded("imagick");
		$gd = extension_loaded("gd");
		if ($preferedAdapter == Image\Adapter::IMAGICK) {
				return new Image\Imagick;
		} else if ($preferedAdapter == Image\Adapter::GD) {
			return new Image\Gd;
		} else {
			if ($imagick) {
				return new Image\Imagick;
			} else if ($gd) {
				return new Image\Gd;
			} else {
				throw new \RuntimeException(
					"No PHP extension for image processing installed ('gd' or 'imagick')."
				);
			}
		}
	}



	/** @var int */
	protected $width;

	/** @var int */
	protected $height;

	/** @var mixed */
	protected $resource;

	/** @var array */
	protected $tmpFiles = array();

	/**
	 * Full directory path for computation temporary images.
	 * @var string
	 */
	protected static $tmpDir = NULL;

	/**
	 * @return	void
	 */
	public function __destruct() {
		$this->removeTmpFiles();
	}

	/**
	 * @return int
	 */
	public function GetWidth () {
		return $this->width;
	}

	/**
	 * @return int
	 */
	public function GetHeight () {
		return $this->height;
	}

	/**
	 * @param  string $hexColor Color in hexadecimal format with or without leading hash.
	 * @return array array($r, $g, $b, 'type' => 'RGB');
	 */
	public function HexColor2RgbArrayColor ($hexColor) {
		$hexColor = trim($hexColor, '#');
		$r = hexdec(substr($hexColor, 1, 2));
		$g = hexdec(substr($hexColor, 3, 2));
		$b = hexdec(substr($hexColor, 5, 2));
		return array($r, $g, $b, 'type' => 'RGB');
	}

	/**
	 * @param  int   $width
	 * @return Image
	 */
	public function ResizeByWidth ($width) {
		$height = round(($width / $this->GetWidth()) * $this->GetHeight(), 0);
		$this->Resize(max(1, $width), max(1, $height));
		return $this;
	}

	/**
	 * @param  int   $height
	 * @return Image
	 */
	public function ResizeByHeight ($height) {
		$width = round(($height / $this->GetHeight()) * $this->GetWidth(), 0);
		$this->Resize(max(1, $width), max(1, $height));
		return $this;
	}

	/**
	 * Scale source image by total final count of pixels in the resized image.
	 * Usefull for list of logotypes, where is necessary to scale all logotypes into
	 * the same visual space - with approximately the same importance by filled space,
	 * so not resized by height or not by width, logotypes have always different proportions.
	 * @param  int   $resizedImgTotalPixelsCount
	 * @return Image
	 */
	public function ResizeByPixelsCount ($resizedImgTotalPixelsCount) {
		$targetSqrt = sqrt($resizedImgTotalPixelsCount);
		$sourceSqrt = sqrt($this->GetWidth() * $this->GetHeight());
		$sqrtRatio = $targetSqrt / $sourceSqrt;
		$newWidth = intval(round($this->GetWidth() * $sqrtRatio));
		$newHeight = intval(round($this->GetHeight() * $sqrtRatio));
		$this->Resize(max(1, $newWidth), max(1, $newHeight));
		return $this;
	}

	/**
	 * Image will be resized into sizes not larger than $width or $height params.
	 * @param  int   $width
	 * @param  int   $height
	 * @return Image
	 */
	public function Contain ($width, $height) {
		$x = $this->GetWidth() / $width;
		$y = $this->GetHeight() / $height;
		if ($x <= 1 && $y <= 1 && !$this->IsVectorGraphic()) {
			return $this;
		} else if ($x > $y) {
			$this->ResizeByWidth($width);
		} else {
			$this->ResizeByHeight($height);
		}
		return $this;
	}

	/**
	 * Image will be resized into given $width and $height to cover whole place,
	 * with optional orientation of source image to cover final place.
	 * @param  int				   $width
	 * @param  int				   $height
	 * @param  int|Image\Orientation $orientation
	 * @throws \InvalidArgumentException
	 * @return Image
	 */
	public function Cover ($width, $height, $orientation = Image\Orientation::CENTER) {
		$ratio = $this->GetWidth() / $this->GetHeight();
		if (($width / $height) > $ratio) {
			$this->ResizeByWidth($width);
		} else {
			$this->ResizeByHeight($height);
		}
		if ($orientation == Image\Orientation::CENTER) {
			$cropX = ($this->GetWidth() - $width)/2;
			$cropY = ($this->GetHeight() - $height)/2;
		} else if ($orientation == Image\Orientation::TOP_LEFT) {
			$cropX = 0;
			$cropY = 0;
		} else if ($orientation == Image\Orientation::TOP_RIGHT) {
			$cropX = $this->GetWidth() - $width;
			$cropY = 0;
		} else if ($orientation == Image\Orientation::BOTTOM_LEFT) {
			$cropX = 0;
			$cropY = $this->GetHeight() - $height;
		} else if ($orientation == Image\Orientation::BOTTOM_RIGHT) {
			$cropX = $this->GetWidth() - $width;
			$cropY = $this->GetHeight() - $height;
		} else if ($orientation == Image\Orientation::CENTER_LEFT) {
			$cropX = 0;
			$cropY = ($this->GetHeight() - $height)/2;
		} else if ($orientation == Image\Orientation::CENTER_RIGHT) {
			$cropX = $this->GetWidth() - $width;
			$cropY = ($this->GetHeight() - $height)/2;
		} else if ($orientation == Image\Orientation::TOP_CENTER) {
			$cropX = ($this->GetWidth() - $width)/2;
			$cropY = 0;
		} else if ($orientation == Image\Orientation::BOTTOM_CENTER) {
			$cropX = ($this->GetWidth() - $width)/2;
			$cropY = $this->GetHeight() - $height;
		} else {
			$cropX = null;
			$cropY = null;
		}
		if ($cropX !== null && $cropY !== null) {
			$this->Crop($cropX, $cropY, $width, $height);
		} else {
			throw new \InvalidArgumentException(
				"Cropping not processed, because X or Y is not defined or null."
			);
		}
		return $this;
	}

	/**
	 * @param  int   $xPercentage
	 * @param  int   $yPercentage
	 * @param  int   $widthPercentage
	 * @param  int   $heightPercentage
	 * @return Image
	 */
	public function CropPercent ($xPercentage, $yPercentage, $widthPercentage, $heightPercentage) {
		$originalWidth = $this->GetWidth();
		$originalHeight = $this->GetHeight();
		$widthPixel = $originalWidth * ($widthPercentage / 100);
		$heightPixel = $originalHeight * ($heightPercentage / 100);
		$xPixel = $originalWidth * ($xPercentage / 100);
		$yPixel = $originalHeight * ($yPercentage / 100);
		return $this->Crop($xPixel, $yPixel, $widthPixel, $heightPixel);
	}



	/**
	 * @abstract
	 * @param  string $imgFullPath
	 * @return Image
	 */
	public abstract function Load ($imgFullPath);

	/**
	 * @abstract
	 * @param  string			  $fullPath
	 * @param  string|Image\Format $format
	 * @param  int				 $quality
	 * @return Image
	 */
	public abstract function Save ($fullPath, $format = Image\Format::PNG, $quality = NULL);

	/**
	 * @abstract
	 * @param  int   $width
	 * @param  int   $height
	 * @return Image
	 */
	public abstract function Resize ($width, $height);

	/**
	 * @abstract
	 * @param  int   $x
	 * @param  int   $y
	 * @param  int   $width
	 * @param  int   $height
	 * @return Image
	 */
	public abstract function Crop ($x, $y, $width, $height);

	/**
	 * Image will be resized into given width and height with
	 * original aspect ration and transparent background color.
	 * @abstract
	 * @param  int   $width
	 * @param  int   $height
	 * @return Image
	 */
	public abstract function Frame ($width, $height);

	/**
	 * @abstract
	 * @param  string $hexColor
	 * @return Image
	 */
	public abstract function SetBackgroundColor ($hexColor);

	/**
	 * @abstract
	 * @param  int   $amount	typically 50 - 200, min 0, max 500
	 * @param  float $radius	typically 0.5 - 1, min 0, max 50
	 * @param  int   $threshold typically 0 - 5, min 0, max 255
	 * @return Image
	 */
	public abstract function UnsharpMask ($amount, $radius, $threshold);

	/**
	 * @abstract
	 * @param  string $maskImgFullPath
	 * @return Image
	 */
	public abstract function ApplyMask ($maskImgFullPath);

	/**
	 * @abstract
	 * @return Image
	 */
	public abstract function Grayscale ();

	/**
	 * @abstract
	 * @return Image
	 */
	public abstract function Sepia ();

	/**
	 * @abstract
	 * @param  float $x
	 * @param  float $y
	 * @return Image
	 */
	public abstract function RoundCorners ($x, $y);

	/**
	 * @abstract
	 * @param  float $angle
	 * @return Image
	 */
	public abstract function Rotate ($angle);

	/**
	 * @abstract
	 * @param  string $bgImgFullPath
	 * @return Image
	 */
	public abstract function SetBackgroundImage ($image);

	/**
	 * @abstract
	 * @return bool
	 */
	public abstract function IsVectorGraphic ();

	/**
	 * @abstract
	 * @param  string			  $overlayImgFullPath
	 * @param  int				 $x
	 * @param  int				 $y
	 * @param  int				 $alpha
	 * @param  int|Image\Composite $composite
	 * @return Image
	 */
	public abstract function AddOverlay (
		$overlayImgFullPath, $x = 0, $y = 0, $alpha = NULL,
		$composite = Image\Composite::NORMAL
	);



	/**
	 * @abstract
	 * @return void
	 */
	protected abstract function destroy ();



	/**
	 * @param int $width
	 */
	protected function setWidth ($width) {
		$this->width = $width;
	}

	/**
	 * @param int $height
	 */
	protected function setHeight ($height) {
		$this->height = $height;
	}

	/**
	 * @return void
	 */
	protected function removeTmpFiles () {
		if (!empty($this->tmpFiles)) {
			foreach ($this->tmpFiles as $tmpFile) {
				if (file_exists($tmpFile)) @unlink($tmpFile);
			}
		}
	}

	/**
	 * @return void
	 */
	protected function reinitializeImage() {
		$tmpFile = static::$tmpDir . "/MvcCore_Ext_Tool_Image_TMP_" . uniqid();
		$this->tmpFiles[] = $tmpFile;
		$this->Save($tmpFile);
		$this->destroy();
		$this->Load($tmpFile);
	}
}
Image::SetTmpDirPath(
	ini_get('TMP')
		? ini_get('TMP')
		: ini_get('TEMP')
);