<?php
/**
 * On the fly Thumbnail generation.
 * Creates thumbnails given by thumbs.php?img=/relative/path/to/image.jpg
 * relative to the base_dir given in config.inc.php
 * @author $Author:ray $
 * @version $Id:thumbs.php 677 2007-01-19 22:24:36Z ray $
 * @package ImageManager
 */

require_once('config.inc.php');
require_once('Classes/ImageManager.php');
require_once('Classes/Thumbnail.php');

//check for img parameter in the url
if(!isset($_GET['img']))
	{
	exit();
	}


$manager = new ImageManager($IMConfig);

//get the image and the full path to the image
$image = rawurldecode($_GET['img']);

// If the image is a URL, see if there is an x-thumbnail x-thumb or x-tn param on it
//  probably best to use x-tn to save space on the URL
if(preg_match('/^[a-z]+:\/\/.*[?&]x-(thumbnail|thumb|tn)=([^&]+)/i', $image, $Matches))
{
  // In which case, we will use the thumbnail
  header('location: ' . rawurldecode($Matches[2]));
  exit;
}

$fullpath = Files::makeFile($manager->getImagesDir(),$image);

//not a file, so exit
if(!is_file($fullpath))
	{
	exit();
	}

$imgInfo = @getImageSize($fullpath);

//Not an image, send default thumbnail
if(!is_array($imgInfo))
{
	//show the default image, otherwise we quit!
	$default = $manager->getDefaultThumb();
	if($default)
	{
		header('Location: '.$default);
		exit();
	}
}
//if the image is less than the thumbnail dimensions
//send the original image as thumbnail

if ($imgInfo[0] <= $IMConfig['thumbnail_width']
 && $imgInfo[1] <= $IMConfig['thumbnail_height'])
 {

	 header('Location: '. $manager->getFileURL($image));
	 exit();
 }

//Check for thumbnails
$thumbnail = $manager->getThumbName($fullpath);

if(is_file($thumbnail))
{
	//if the thumbnail is newer, send it
	if(filemtime($thumbnail) >= filemtime($fullpath))
	{
		header('Location: '.$manager->getThumbURL($image));
		exit();
	}
}

//creating thumbnails
$thumbnailer = new Thumbnail($IMConfig['thumbnail_width'],$IMConfig['thumbnail_height']);
$thumbnailer->createThumbnail($fullpath, $thumbnail);

//Check for NEW thumbnails
if(is_file($thumbnail))
{
	//send the new thumbnail
	header('Location: '.$manager->getThumbURL($image));
	exit();
}
else
{
	//show the default image, otherwise we quit!
	$default = $manager->getDefaultThumb();

	if($default)
		header('Location: '.$default);
}
?>
