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

namespace MvcCore\Ext\Tool\Image\Imagick;

use \MvcCore\Ext\Tool\Image;

interface Composite
{
	/**
	 * The default composite operator
	 * @var int
	 */
	const NORMAL = 40;

	/**
	 * Undefined composite operator
	 * @var int
	 */
	const UNDEFINED = 0;

	/**
	 * No composite operator defined
	 * @var int
	 */
	const NO = 1;

	/**
	 * The result of image + image
	 * @var int
	 */
	const ADD = 2;

	/**
	 * The result is the same shape as image, with composite image obscuring image where the image shapes overlap
	 * @var int
	 */
	const ATOP = 3;

	/**
	 * Blends the image
	 * @var int
	 */
	const BLEND = 4;

	/**
	 * The same as MULTIPLY, except the source is converted to grayscale first.
	 * @var int
	 */
	const BUMPMAP = 5;

	/**
	 * Makes the target image transparent
	 * @var int
	 */
	const CLEAR = 7;

	/**
	 * Darkens the destination image to reflect the source image
	 * @var int
	 */
	const COLORBURN = 8;

	/**
	 * Brightens the destination image to reflect the source image
	 * @var int
	 */
	const COLORDODGE = 9;

	/**
	 * Colorizes the target image using the composite image
	 * @var int
	 */
	const COLORIZE = 10;

	/**
	 * Copies black from the source to target
	 * @var int
	 */
	const COPYBLACK = 11;

	/**
	 * Copies blue from the source to target
	 * @var int
	 */
	const COPYBLUE = 12;

	/**
	 * Copies the source image on the target image
	 * @var int
	 */
	const COPY = 13;

	/**
	 * Copies cyan from the source to target
	 * @var int
	 */
	const COPYCYAN = 14;

	/**
	 * Copies green from the source to target
	 * @var int
	 */
	const COPYGREEN = 15;

	/**
	 * Copies magenta from the source to target
	 * @var int
	 */
	const COPYMAGENTA = 16;

	/**
	 * Copies opacity from the source to target
	 * @var int
	 */
	const COPYOPACITY = 17;

	/**
	 * Copies red from the source to target
	 * @var int
	 */
	const COPYRED = 18;

	/**
	 * Copies yellow from the source to target
	 * @var int
	 */
	const COPYYELLOW = 19;

	/**
	 * Darkens the target image
	 * @var int
	 */
	const DARKEN = 20;

	/**
	 * The part of the destination lying inside of the source is composited over the source and replaces the destination
	 * @var int
	 */
	const DSTATOP = 21;

	/**
	 * The target is left untouched
	 * @var int
	 */
	const DST = 22;

	/**
	 * The parts inside the source replace the target
	 * @var int
	 */
	const DSTIN = 23;

	/**
	 * The parts outside the source replace the target
	 * @var int
	 */
	const DSTOUT = 24;

	/**
	 * Target replaces the source
	 * @var int
	 */
	const DSTOVER = 25;

	/**
	 * Subtracts the darker of the two constituent colors from the lighter
	 * @var int
	 */
	const DIFFERENCE = 26;

	/**
	 * Shifts target image pixels as defined by the source
	 * @var int
	 */
	const DISPLACE = 27;

	/**
	 * Dissolves the source in to the target
	 * @var int
	 */
	const DISSOLVE = 28;

	/**
	 * Produces an effect similar to that of \ Imagick::DIFFERENCE, but appears as lower contrast
	 * @var int
	 */
	const EXCLUSION = 29;

	/**
	 * Multiplies or screens the colors, dependent on the source color value
	 * @var int
	 */
	const HARDLIGHT = 30;

	/**
	 * Modifies the hue of the target as defined by source
	 * @var int
	 */
	const HUE = 31;

	/**
	 * Composites source into the target
	 * @var int
	 */
	const IN = 32;

	/**
	 * Lightens the target as defined by source
	 * @var int
	 */
	const LIGHTEN = 33;

	/**
	 * Luminizes the target as defined by source
	 * @var int
	 */
	const LUMINIZE = 35;

	/**
	 * Subtracts the source from the target
	 * @var int
	 */
	const MINUS = 36;

	/**
	 * Modulates the target brightness, saturation and hue as defined by source
	 * @var int
	 */
	const MODULATE = 37;

	/**
	 * Multiplies the target to the source
	 * @var int
	 */
	const MULTIPLY = 38;

	/**
	 * Composites outer parts of the source on the target
	 * @var int
	 */
	const OUT = 39;

	/**
	 * Composites source over the target
	 * @var int
	 */
	const OVER = 40;

	/**
	 * Overlays the source on the target
	 * @var int
	 */
	const OVERLAY = 41;

	/**
	 * Adds the source to the target
	 * @var int
	 */
	const PLUS = 42;

	/**
	 * Replaces the target with the source
	 * @var int
	 */
	const REPLACE = 43;

	/**
	 * Saturates the target as defined by the source
	 * @var int
	 */
	const SATURATE = 44;

	/**
	 * The source and destination are complemented and then multiplied and then replace the destination
	 * @var int
	 */
	const SCREEN = 45;

	/**
	 * Darkens or lightens the colors, dependent on the source
	 * @var int
	 */
	const SOFTLIGHT = 46;

	/**
	 * The part of the source lying inside of the destination is composited onto the destination
	 * @var int
	 */
	const SRCATOP = 47;

	/**
	 * The source is copied to the destination
	 * @var int
	 */
	const SRC = 48;

	/**
	 * The part of the source lying inside of the destination replaces the destination
	 * @var int
	 */
	const SRCIN = 49;

	/**
	 * The part of the source lying outside of the destination replaces the destination
	 * @var int
	 */
	const SRCOUT = 50;

	/**
	 * The source replaces the destination
	 * @var int
	 */
	const SRCOVER = 51;

	/**
	 * Subtract the colors in the source image from the destination image
	 * @var int
	 */
	const SUBTRACT = 52;

	/**
	 * The source is composited on the target as defined by source threshold
	 * @var int
	 */
	const THRESHOLD = 53;

	/**
	 * The part of the source that lies outside of the destination is combined with the part of the destination that lies outside of the source
	 * @var int
	 */
	const _XOR = 54;
}