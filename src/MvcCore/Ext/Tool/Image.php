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
	 * MvcCore Extension - Router Lang - version:
	 * Comparation by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '4.2.0';

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
	protected static $tmpDir = __DIR__;



	/**
	 * @return	void
	 */
    public function __destruct() {
        $this->removeTmpFiles();
    }

    /**
	 * @param int $height
	 */
    public function SetHeight ($height) {
        $this->height = $height;
    }

    /**
	 * @return int
	 */
    public function GetHeight () {
        return $this->height;
    }

    /**
	 * @param int $width
	 */
    public function SetWidth ($width) {
        $this->width = $width;
    }

    /**
	 * @return int
	 */
    public function GetWidth () {
        return $this->width;
    }

    /**
	 * @param  string $hexColor
	 * @return array
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
    public function ScaleByWidth ($width) {
        $height = round(($width / $this->getWidth()) * $this->getHeight(), 0);
        $this->resize(max(1, $width), max(1, $height));
        return $this;
    }

    /**
	 * @param  int   $height
	 * @return Image
	 */
    public function ScaleByHeight ($height) {
        $width = round(($height / $this->getHeight()) * $this->getWidth(), 0);
        $this->resize(max(1, $width), max(1, $height));
        return $this;
    }

    /**
	 * @param  int   $count
	 * @return Image
	 */
    public function ScaleByPixelsCount ($count) {
		$targetSqrt = sqrt(intval($count));
		$sourceSqrt = sqrt($this->getWidth() * $this->getHeight());
		$sqrtRatio = $targetSqrt / $sourceSqrt;
		$newWidth = intval(round($this->getWidth() * $sqrtRatio));
		$newHeight = intval(round($this->getHeight() * $sqrtRatio));
		$this->resize(max(1, $newWidth), max(1, $newHeight));
        return $this;
    }

    /**
	 * @param  int   $width
	 * @param  int   $height
	 * @return Image
	 */
    public function Contain ($width, $height) {
        $x = $this->getWidth() / $width;
        $y = $this->getHeight() / $height;
        if ($x <= 1 && $y <= 1 && !$this->isVectorGraphic()) {
            return $this;
        } else if ($x > $y) {
            $this->scaleByWidth($width);
        } else {
            $this->scaleByHeight($height);
        }
        return $this;
    }

    /**
	 * @param  int   $width
	 * @param  int   $height
	 * @param  int   $orientation
	 * @return Image
	 */
    public function Cover ($width, $height, $orientation = Image\Orientation::CENTER) {
        $ratio = $this->getWidth() / $this->getHeight();
        if (($width / $height) > $ratio) {
			$this->scaleByWidth($width);
        } else {
			$this->scaleByHeight($height);
        }
        if ($orientation == Image\Orientation::CENTER) {
            $cropX = ($this->getWidth() - $width)/2;
            $cropY = ($this->getHeight() - $height)/2;
        } else if ($orientation == Image\Orientation::TOP_LEFT) {
            $cropX = 0;
            $cropY = 0;
        } else if ($orientation == Image\Orientation::TOP_RIGHT) {
            $cropX = $this->getWidth() - $width;
            $cropY = 0;
        } else if ($orientation == Image\Orientation::BOTTOM_LEFT) {
            $cropX = 0;
            $cropY = $this->getHeight() - $height;
        } else if ($orientation == Image\Orientation::BOTTOM_RIGHT) {
            $cropX = $this->getWidth() - $width;
            $cropY = $this->getHeight() - $height;
        } else if ($orientation == Image\Orientation::CENTER_LEFT) {
            $cropX = 0;
            $cropY = ($this->getHeight() - $height)/2;
        } else if ($orientation == Image\Orientation::CENTER_RIGHT) {
            $cropX = $this->getWidth() - $width;
            $cropY = ($this->getHeight() - $height)/2;
        } else if ($orientation == Image\Orientation::TOP_CENTER) {
            $cropX = ($this->getWidth() - $width)/2;
            $cropY = 0;
        } else if ($orientation == Image\Orientation::BOTTOM_CENTER) {
            $cropX = ($this->getWidth() - $width)/2;
            $cropY = $this->getHeight() - $height;
        } else {
            $cropX = null;
            $cropY = null;
        }
        if ($cropX !== null && $cropY !== null) {
            $this->crop($cropX, $cropY, $width, $height);
        } else {
            throw new \InvalidArgumentException(
				"Cropping not processed, because X or Y is not defined or null."
			);
        }
        return $this;
    }

	/**
	 * @param  int   $width
	 * @param  int   $height
	 * @param  int   $x
	 * @param  int   $y
	 * @return Image
	 */
    public function CropPercent ($width, $height, $x, $y) {
        $originalWidth = $this->getWidth();
        $originalHeight = $this->getHeight();
        $widthPixel = $originalWidth * ($width / 100);
        $heightPixel = $originalHeight * ($height / 100);
        $xPixel = $originalWidth * ($x / 100);
        $yPixel = $originalHeight * ($y / 100);
        return $this->crop($xPixel, $yPixel, $widthPixel, $heightPixel);
    }



    /**
	 * @abstract
	 * @param  string $imgFullPath
	 * @return Image
	 */
    public abstract function Load ($imgFullPath);

    /**
	 * @abstract
	 * @param  string $fullPath
	 * @param  string $format
	 * @param  int    $quality
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
	 * @param  int   $amount    typically 50 - 200, min 0, max 500
	 * @param  float $radius    typically 0.5 - 1, min 0, max 50
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
	 * @return bool
	 */
    public abstract function IsVectorGraphic ();

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
	 * @param  string $overlayImgFullPath
	 * @param  int    $x
	 * @param  int    $y
	 * @param  int    $alpha
	 * @param  int    $composite
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
        $tmpFile = static::$tmpDir . "/" . uniqid() . "_image_tmp_file";
        $this->tmpFiles[] = $tmpFile;
        $this->save($tmpFile);
        $this->destroy();
        $this->load($tmpFile);
    }
}