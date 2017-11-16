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

interface Orientation
{
	const CENTER = 1;
	const TOP_LEFT = 2;
	const TOP_RIGHT = 3;
	const BOTTOM_LEFT = 4;
	const BOTTOM_RIGHT = 5;
	const CENTER_LEFT = 6;
	const CENTER_RIGHT = 7;
	const TOP_CENTER = 8;
	const BOTTOM_CENTER = 9;
}