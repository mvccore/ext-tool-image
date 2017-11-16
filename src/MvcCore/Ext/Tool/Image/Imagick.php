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

class Imagick extends Tool\Image
{
    /** @var \Imagick */
    protected $resource;

    /** @var string */
    protected $imgFullPath;

    /**
	 * @param  string     $imgFullPath
	 * @throws \Exception
	 * @return bool|Image
     */
    public function Load ($imgFullPath) {
        if ($this->resource) {
            unset($this->resource);
            $this->resource = null;
        }
        try {
            $this->resource = new \Imagick($imgFullPath);
            if (!$this->resource->readImage($imgFullPath)) {
                return false;
            }
            $this->imgFullPath = $imgFullPath;
        } catch (\Exception $e) {
            throw new \Exception(
				"Image '$imgFullPath' was not possible to load by \Imagick::readImage();", 0, $e
			);
        }
        // set dimensions
        $this->SetWidth($this->resource->getImageWidth());
        $this->SetHeight($this->resource->getImageHeight());
        return $this;
    }

    /**
	 * @param  string $fullPath
	 * @param  string $format
	 * @param  int    $quality
	 * @return Image
	 */
    public function Save ($fullPath, $format = Image\Format::PNG, $quality = NULL) {
        if (!$format) $format = Image\Format::PNG;
        $this->resource->stripimage();
        $this->resource->setImageFormat($format);
        if (is_int($quality)) {
            $this->resource->setCompressionQuality($quality);
            $this->resource->setImageCompressionQuality($quality);
        }
        $this->resource->writeImage($fullPath);
        return $this;
    }

    /**
	 * @param  int   $width
	 * @param  int   $height
	 * @return Image
	 */
    public function Resize ($width, $height) {
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
        $this->SetWidth($width);
        $this->SetHeight($height);
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
        $this->resource->cropImage($width, $height, $x, $y);
        $this->resource->setImagePage($width, $height, 0, 0);
        $this->SetWidth($width);
        $this->SetHeight($height);
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
        $newImage->compositeImage(
			$this->resource, Image\Imagick\Composite::NORMAL,
			$x, $y
		);
        $this->resource = $newImage;
        $this->SetWidth($width);
        $this->SetHeight($height);
        $this->reinitializeImage();
        return $this;
    }

	/**
	 * @param  string $hexColor
	 * @return Image
	 */
    public function SetBackgroundColor ($hexColor) {
        $newImage = $this->createImage(
			$this->GetWidth(), $this->GetHeight(), $hexColor
		);
        $newImage->compositeImage(
			$this->resource, Image\Imagick\Composite::NORMAL,
			0, 0
		);
        $this->resource = $newImage;
        $this->reinitializeImage();
        return $this;
    }

    /**
	 * @param  int   $amount    typically 50 - 200, min 0, max 500
	 * @param  float $radius    typically 0.5 - 1, min 0, max 50
	 * @param  int   $threshold typically 0 - 5, min 0, max 255
	 * @return Image
	 */
    public function UnsharpMask ($amount, $radius, $threshold) {
		$sigma = ($radius < 1) ? $radius : sqrt($radius) ;
		$amount = ($amount * 2.55) / 100;
		$threshold = $threshold / 255;
		$this->resource->unsharpMaskImage(
			$radius , $sigma , $amount , $threshold
		);
		return $this;
    }

	/**
	 * @param  string $maskImgFullPath
	 * @return Image
	 */
    public function ApplyMask ($maskImgFullPath) {
        if (is_file($maskImgFullPath)) {
            $this->resource->setImageMatte(1);
            $newImage = new Imagick();
            $newImage->readimage($maskImgFullPath);
            $newImage->resizeimage(
				$this->GetWidth(), $this->GetHeight(),
				\Imagick::FILTER_UNDEFINED, 1, false
			);
            $this->resource->compositeImage(
				$newImage, \Imagick::COMPOSITE_DSTIN, 0 ,0
			);
        } else {
			throw "Mask image not found: '$maskImgFullPath'.";
		}
        $this->reinitializeImage();
        return $this;
    }

	/**
	 * @return Image
	 */
    public function Grayscale () {
        $this->resource->setImageType(\Imagick::IMGTYPE_GRAYSCALEMATTE);
        $this->reinitializeImage();
        return $this;
    }

    /**
	 * @return Image
	 */
    public function Sepia () {
        $this->resource->sepiatoneimage(85);
        $this->reinitializeImage();
        return $this;
    }

	/**
	 * @return bool
	 */
	public function IsVectorGraphic () {
        try {
            $type = $this->resource->getimageformat();
            $vectorTypes = array(
				"EPT","EPDF","EPI","EPS","EPS2",
				"EPS3","EPSF","EPSI","EPT","PDF",
				"PFA","PFB","PFM","PS","PS2",
				"PS3","PSB","SVG","SVGZ"
			);
            if (in_array($type, $vectorTypes)) {
                return TRUE;
            }
        }
		catch (Exception $e) {
        }
        return FALSE;
    }

    /**
	 * @param  float $x
	 * @param  float $y
	 * @return Image
	 */
    public function RoundCorners ($x, $y) {
        $this->resource->roundCorners($x, $y);
        $this->reinitializeImage();
        return $this;
    }

	/**
	 * @param  float $angle
	 * @return Image
	 */
    public function Rotate ($angle) {
        $this->resource->rotateImage(new \ImagickPixel('none'), $angle);
        $this->SetWidth($this->resource->getimagewidth());
        $this->SetHeight($this->resource->getimageheight());
        $this->reinitializeImage();
        return $this;
    }

	/**
	 * @param  string $bgImgFullPath
	 * @return Image
	 */
    public function SetBackgroundImage ($bgImgFullPath) {
        if (is_file($bgImgFullPath)) {
            $newImage = new Imagick();
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
			throw "Background image not found: '$bgImgFullPath'.";
		}
        $this->reinitializeImage();
        return $this;
    }

	/**
	 * @param  string $overlayImgFullPath
	 * @param  int    $x
	 * @param  int    $y
	 * @param  int    $alpha
	 * @param  int    $composite
	 * @return Image
	 */
    public function AddOverlay (
		$overlayImgFullPath, $x = 0, $y = 0, $alpha = NULL,
		$composite = Image\Imagick\Composite::NORMAL
	) {
        // 100 alpha is default
        if (!is_int($alpha)) $alpha = 100;
        $alpha = round($alpha / 100, 1);
        if (is_file($overlayImgFullPath)) {
            $newImage = new Imagick();
            $newImage->readimage($overlayImgFullPath);
            $newImage->evaluateImage(
				\Imagick::EVALUATE_MULTIPLY, $alpha, \Imagick::CHANNEL_ALPHA
			);
            $this->resource->compositeImage(
				$newImage, $composite, $x ,$y
			);
        } else {
			throw "Overlay image not found: '$overlayImgFullPath'.";
		}
        $this->reinitializeImage();
        return $this;
    }



    /**
     * @return void
     */
    protected function destroy() {
        $this->resource->destroy();
    }

    /**
     * @param  int     $width
	 * @param  int     $height
     * @return Imagick
     */
    protected  function createImage ($width, $height, $hexColor = "none") {
        $newImage = new Imagick();
        $newImage->newimage($width, $height, $hexColor);
        $this->reinitializeImage();
        return $newImage;
    }
}