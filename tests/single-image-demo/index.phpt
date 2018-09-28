<?php

	include_once('../loader.phpt');

	use \MvcCore\Ext\Tools;

	// resize `source.jpg` to `thumb.jpg`:
	$image = Tools\Image::CreateInstance()
		->Load(__DIR__ . "/source.jpg")
		->ResizeByHeight(150)
		->UnsharpMask(300, 0.7, 5)
		->Save(
			__DIR__ . "/thumb.jpg",
			Tools\Images\IFormat::JPG,
			100
		);


	// display original and resized image:
	echo '<html><body>',
     '<h1>MvcCore Extension "mvccore/ext-tool-image" Single Image Demo:</h1>',
     '<hr />',
     '<img src="source.jpg" />',
     '<hr />',
     '<img src="thumb.jpg?_='.time().'" />',
     '</body></html>';
