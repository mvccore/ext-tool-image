<?php

	// load library without composer
	$rdi = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__.'/../src/MvcCore/Ext/Tools'));
	foreach($rdi as $item)
		if (!$item->isDir())
			include_once($item->getPath() . '/' . $item->getFilename());
