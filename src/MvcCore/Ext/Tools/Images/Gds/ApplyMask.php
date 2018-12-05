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

class ApplyMask {

	/**
	 * @param resource $image 
	 * @param resource $mask 
	 * @return void
	 */
	public static function Process (& $image, & $mask) {

		// Get sizes and set up new picture
		$xSize = imagesx($image);
		$ySize = imagesy($image);

		$newPicture = imagecreatetruecolor($xSize, $ySize);
		imagesavealpha($newPicture, TRUE);
		imagefill(
			$newPicture, 0, 0,
			imagecolorallocatealpha($newPicture, 0, 0, 0, 127)
		);

		// Resize mask if necessary
		if ($xSize != imagesx($mask) || $ySize != imagesy($mask)) {
			$tempPic = imagecreatetruecolor($xSize, $ySize);
			imagecopyresampled(
				$tempPic, $mask, 0, 0, 0, 0,
				$xSize, $ySize, imagesx($mask), imagesy($mask)
			);
			imagedestroy($mask);
			$mask = $tempPic;
		}

		// Perform pixel-based alpha map application
		for ($x = 0; $x < $xSize; $x++) {
			for($y = 0; $y < $ySize; $y++) {
				$rgba = imagecolorsforindex(
					$mask, imagecolorat($mask, $x, $y)
				);
				$whiteRatio = (
					$rgba['red'] + $rgba['green'] + $rgba['blue']
				) / 765;
				$alpha = 127 - round(127 * $whiteRatio);
				$color = imagecolorsforindex(
					$image, imagecolorat($image, $x, $y)
				);
				imagesetpixel(
					$newPicture,
					$x, $y,
					imagecolorallocatealpha(
						$newPicture,
						$color['red'],
						$color['green'],
						$color['blue'],
						$alpha
					)
				);
			}
		}

		// Copy back to original picture
		imagedestroy($image);
		$image = $newPicture;
	}
}
