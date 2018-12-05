<?php

	include_once('../loader.phpt');

	use \MvcCore\Ext\Tools;

	$imagesSources = __DIR__ . "/images-sources";
	$imagesThumbnails = __DIR__ . "/images-thumbnails";
	$di = new \DirectoryIterator($imagesSources);
	Tools\Image::SetTmpDirPath(__DIR__ . "/tmp");

	/** @var $sourceImg \SplFileInfo */
	$timeBeforeResizing = microtime(TRUE);
	$resizedImagesCount = 0;
	foreach ($di as $sourceImg) {
		if ($sourceImg->isDir()) continue;

		$image = Tools\Image::CreateInstance(/* adapter will be chosen automatically */)
		//$image = Tools\Image::CreateInstance(Tools\Images\IAdapter::IMAGICK)
		//$image = Tools\Image::CreateInstance(Tools\Images\IAdapter::GD)
			->Load($imagesSources . "/" . $sourceImg->getFilename())
			->ResizeByHeight(150)
			->UnsharpMask(150, 0.7, 5)
			->Save(
				$imagesThumbnails . "/" . $sourceImg->getFilename(),
				Tools\Images\IFormat::JPG,
				100
			);
		$resizedImagesCount++;
	}
	$resizingSpendedTime = microtime(TRUE) -  $timeBeforeResizing;

	header("Content-Type: text/html; charset=utf-8");

?><!DOCTYPE html>
<html lang="en">
	<head>
		<title>Multiple Images Demo</title>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	</head>
	<body>
		<h1>Multiple Images Demo</h1>
		<p>Used adapter type: <code><?php echo '\\'.get_class($image); ?></code></p>
		<p>Spended time to process <?php echo $resizedImagesCount; ?> images: <?php echo number_format($resizingSpendedTime, 3, '.', ','); ?> seconds.</p>
		<p>Average time to process 1 image: <?php echo number_format($resizingSpendedTime / $resizedImagesCount, 3, '.', ','); ?> seconds.</p>
		<?php $counter = 0; foreach ($di as $sourceImg): ?>
			<?php if ($sourceImg->isDir()) continue; ?>
			<?php if ($counter++ % 4 === 0) echo '<br />'; ?>
			<img src="images-thumbnails/<?php
				echo $sourceImg->getFilename() . "?_=" . time();
			?>" width="auto" height="150" />
		<?php endforeach; ?>
	</body>
</html>
