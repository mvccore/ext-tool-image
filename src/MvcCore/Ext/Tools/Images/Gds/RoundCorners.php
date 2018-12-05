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

namespace MvcCore\Ext\Tools\Images\Gds;

class RoundCorners {

	/**
	 * @param  resource $img
	 * @param  float	$x
	 * @param  float	$y
	 * @return void
	 */
	public static function Process (& $img, $x, $y) {
		$x *= 2;
		$y *= 2;

		// Get sizes and set up new picture
		$w = imagesx($img);
		$h = imagesy($img);

		// create mask image
		$maskImg = imagecreatetruecolor($w, $h);
		$maskWhite = imagecolorallocate($maskImg, 255, 255, 255);

		// mask corners
		$xHalf = $x / 2;
		$yHalf = $y / 2;
		$fix = 1;
		// mask - top left corner
		imagefilledellipse($maskImg, $xHalf, $yHalf, $x, $y, $maskWhite);
		// mask - top right corner
		imagefilledellipse($maskImg, $w - $xHalf - $fix, $yHalf, $x, $y, $maskWhite);
		// mask - bottom left corner
		imagefilledellipse($maskImg, $xHalf, $h - $yHalf - $fix, $x, $y, $maskWhite);
		// mask - bottom right corner
		imagefilledellipse($maskImg, $w - $xHalf - $fix, $h - $yHalf - $fix, $x, $y, $maskWhite);
		// mask - center fills
		$points = [
			$xHalf, 0,
			$w - $xHalf - $fix, 0,
			$w, $yHalf,
			$w, $h - $yHalf - $fix,
			$w - $xHalf - $fix, $h,
			$xHalf, $h,
			0, $h - $yHalf - $fix,
			0, $yHalf
		];
		imagefilledpolygon($maskImg, $points, 8, $maskWhite);

		// create new result image
		$newImg = imagecreatetruecolor($w, $h);
		imagesavealpha($newImg, TRUE);
		imagefill(
			$newImg, 0, 0,
			imagecolorallocatealpha($newImg, 0, 0, 0, 127)
		);

		// copy flat places into new image
		imagecopy($newImg, $img, $xHalf, 0, $xHalf, 0, $w - $x, $yHalf);
		imagecopy($newImg, $img, 0, $yHalf, 0, $yHalf, $w, $h - $y);
		imagecopy($newImg, $img, $xHalf, $h - $yHalf, $xHalf, $h - $yHalf, $w - $x, $yHalf);

		// top left corner
		self::copyCornerWithMask($img, $maskImg, $newImg, 0, 0, $xHalf, $yHalf);
		self::copyCornerWithMask($img, $maskImg, $newImg, $w - $xHalf, 0, $w, $yHalf);
		self::copyCornerWithMask($img, $maskImg, $newImg, 0, $h - $yHalf, $xHalf, $h);
		self::copyCornerWithMask($img, $maskImg, $newImg, $w - $xHalf, $h - $yHalf, $w, $h);

		imagedestroy($img);
		$img = $newImg;

		imagedestroy($maskImg);
	}

	protected static function copyCornerWithMask (
		& $img, & $maskImg, & $newImg,
		$x, $y, $w, $h
	) {
		for ($i = $x; $i < $w; $i += 1) {
			for ($j = $y; $j < $h; $j += 1) {
				$rgba = imagecolorsforindex(
					$maskImg, imagecolorat($maskImg, $i, $j)
				);
				$whiteRatio = (
					$rgba['red'] + $rgba['green'] + $rgba['blue']
				) / 765;
				$alpha = 127 - round(127 * $whiteRatio);
				$color = imagecolorsforindex(
					$img, imagecolorat($img, $i, $j)
				);
				imagesetpixel(
					$newImg,
					$i, $j,
					imagecolorallocatealpha(
						$newImg,
						$color['red'],
						$color['green'],
						$color['blue'],
						$alpha
					)
				);
			}
		}
	}
}
