<?php
/*
	TimThumb script created by Tim McDaniels and Darren Hoyt with tweaks by Ben Gillbanks
	http://code.google.com/p/timthumb/

	MIT License: http://www.opensource.org/licenses/mit-license.php

	Paramters
	---------
	w: width
	h: height
	zc: zoom crop (0 or 1)
	q: quality (default is 75 and max is 100)
	
	HTML example: <img src="/scripts/timthumb.php?src=/images/whatever.jpg&w=150&h=200&zc=1" alt="" />
*/

/*
$sizeLimits = array(
	"100x100",
	"150x150",
);
*/

define ('CACHE_SIZE', 250);		// number of files to store before clearing cache
define ('CACHE_CLEAR', 5);		// maximum number of files to delete on each cache clear
define ('VERSION', '1.09');		// version number (to force a cache refresh

$imageFilters = array(
	"1" => array(IMG_FILTER_NEGATE, 0),
	"2" => array(IMG_FILTER_GRAYSCALE, 0),
	"3" => array(IMG_FILTER_BRIGHTNESS, 1),
	"4" => array(IMG_FILTER_CONTRAST, 1),
	"5" => array(IMG_FILTER_COLORIZE, 4),
	"6" => array(IMG_FILTER_EDGEDETECT, 0),
	"7" => array(IMG_FILTER_EMBOSS, 0),
	"8" => array(IMG_FILTER_GAUSSIAN_BLUR, 0),
	"9" => array(IMG_FILTER_SELECTIVE_BLUR, 0),
	"10" => array(IMG_FILTER_MEAN_REMOVAL, 0),
	"11" => array(IMG_FILTER_SMOOTH, 0),
);

// sort out image source
$src = get_request("src", "");
if($src == "" || strlen($src) <= 3) {
	displayError("no image specified");
}

// clean params before use
$src = cleanSource($src);
// last modified time (for caching)
$lastModified = filemtime($src);

// get properties
$new_width 		= preg_replace("/[^0-9]+/", "", get_request("w", 0));
$new_height 	= preg_replace("/[^0-9]+/", "", get_request("h", 0));
$zoom_crop 		= preg_replace("/[^0-9]+/", "", get_request("zc", 1));
$quality 		= preg_replace("/[^0-9]+/", "", get_request("q", 80));
$filters		= get_request("f", "");

if ($new_width == 0 && $new_height == 0) {
	$new_width = 100;
	$new_height = 100;
}

// set path to cache directory (default is ./cache)
// this can be changed to a different location
$cache_dir = './cache';

// get mime type of src
$mime_type = mime_type($src);

// check to see if this image is in the cache already
check_cache( $cache_dir, $mime_type );

// if not in cache then clear some space and generate a new file
cleanCache();

ini_set('memory_limit', "30M");

// make sure that the src is gif/jpg/png
if(!valid_src_mime_type($mime_type)) {
	displayError("Invalid src mime type: " .$mime_type);
}

// check to see if GD function exist
if(!function_exists('imagecreatetruecolor')) {
	displayError("GD Library Error: imagecreatetruecolor does not exist");
}

if(strlen($src) && file_exists($src)) {

	// open the existing image
	$image = open_image($mime_type, $src);
	if($image === false) {
		displayError('Unable to open image : ' . $src);
	}

	// Get original width and height
	$width = imagesx($image);
	$height = imagesy($image);
	
	// don't allow new width or height to be greater than the original
	if( $new_width > $width ) {
		$new_width = $width;
	}
	if( $new_height > $height ) {
		$new_height = $height;
	}

	// generate new w/h if not provided
	if( $new_width && !$new_height ) {
		
		$new_height = $height * ( $new_width / $width );
		
	} elseif($new_height && !$new_width) {
		
		$new_width = $width * ( $new_height / $height );
		
	} elseif(!$new_width && !$new_height) {
		
		$new_width = $width;
		$new_height = $height;
		
	}
	
	// create a new true color image
	$canvas = imagecreatetruecolor( $new_width, $new_height );
	imagealphablending($canvas, false);
	// Create a new transparent color for image
	$color = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
	// Completely fill the background of the new image with allocated color.
	imagefill($canvas, 0, 0, $color);
	// Restore transparency blending
	imagesavealpha($canvas, true);

	if( $zoom_crop ) {

		$src_x = $src_y = 0;
		$src_w = $width;
		$src_h = $height;

		$cmp_x = $width  / $new_width;
		$cmp_y = $height / $new_height;

		// calculate x or y coordinate and width or height of source

		if ( $cmp_x > $cmp_y ) {

			$src_w = round( ( $width / $cmp_x * $cmp_y ) );
			$src_x = round( ( $width - ( $width / $cmp_x * $cmp_y ) ) / 2 );

		} elseif ( $cmp_y > $cmp_x ) {

			$src_h = round( ( $height / $cmp_y * $cmp_x ) );
			$src_y = round( ( $height - ( $height / $cmp_y * $cmp_x ) ) / 2 );

		}
		
		imagecopyresampled( $canvas, $image, 0, 0, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h );

	} else {

		// copy and resize part of an image with resampling
		imagecopyresampled( $canvas, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

	}
	
	if ($filters != "") {
		// apply filters to image
		$filterList = explode("|", $filters);
		foreach($filterList as $fl) {
			$filterSettings = explode(",", $fl);
			if(isset($imageFilters[$filterSettings[0]])) {
			
				for($i = 0; $i < 4; $i ++) {
					if(!isset($filterSettings[$i])) {
						$filterSettings[$i] = null;
					}
				}
				
				switch($imageFilters[$filterSettings[0]][1]) {
				
					case 1:
					
						imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1]);
						break;
					
					case 2:
					
						imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1], $filterSettings[2]);
						break;
					
					case 3:
					
						imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1], $filterSettings[2], $filterSettings[3]);
						break;
					
					default:
					
						imagefilter($canvas, $imageFilters[$filterSettings[0]][0]);
						break;
						
				}
			}
		}
	}
	
	// output image to browser based on mime type
	show_image($mime_type, $canvas, $cache_dir);
	
	// remove image from memory
	imagedestroy($canvas);
	
} else {

	if(strlen($src)) {
		displayError("image " . $src . " not found");
	} else {
		displayError("no source specified");
	}
	
}

/**
 * 
 */
function show_image($mime_type, $image_resized, $cache_dir) {

	global $quality;

	// check to see if we can write to the cache directory
	$is_writable = 0;
	$cache_file_name = $cache_dir . '/' . get_cache_file();

	if(touch($cache_file_name)) {
		
		// give 666 permissions so that the developer 
		// can overwrite web server user
		chmod($cache_file_name, 0666);
		$is_writable = 1;
		
	} else {
		
		$cache_file_name = NULL;
		header('Content-type: ' . $mime_type);
		
	}

	$quality = floor($quality * 0.09);

	imagepng($image_resized, $cache_file_name, $quality);
	
	if($is_writable) {
		show_cache_file($cache_dir, $mime_type);
	}

	imagedestroy($image_resized);
	
	displayError("error showing image");

}

/**
 * 
 */
function get_request( $property, $default = 0 ) {
	
	if( isset($_REQUEST[$property]) ) {
	
		return $_REQUEST[$property];
		
	} else {
	
		return $default;
		
	}
	
}

/**
 * 
 */
function open_image($mime_type, $src) {

	if(stristr($mime_type, 'gif')) {
	
		$image = imagecreatefromgif($src);
		
	} elseif(stristr($mime_type, 'jpeg')) {
	
		@ini_set('gd.jpeg_ignore_warning', 1);
		$image = imagecreatefromjpeg($src);
		
	} elseif( stristr($mime_type, 'png')) {
	
		$image = imagecreatefrompng($src);
		
	}
	
	return $image;

}

/**
 * clean out old files from the cache
 * you can change the number of files to store and to delete per loop in the defines at the top of the code
 */
function cleanCache() {

	$files = glob("cache/*", GLOB_BRACE);
	
	$yesterday = time() - (24 * 60 * 60);
	
	if (count($files) > 0) {
		
		usort($files, "filemtime_compare");
		$i = 0;
		
		if (count($files) > CACHE_SIZE) {
			
			foreach ($files as $file) {
				
				$i ++;
				
				if ($i >= CACHE_CLEAR) {
					return;
				}
				
				if (filemtime($file) > $yesterday) {
					return;
				}
				
				unlink($file);
				
			}
			
		}
		
	}

}

/**
 * compare the file time of two files
 */
function filemtime_compare($a, $b) {

	return filemtime($a) - filemtime($b);
	
}

/**
 * determine the file mime type
 */
function mime_type($file) {

	if (stristr(PHP_OS, 'WIN')) { 
		$os = 'WIN';
	} else { 
		$os = PHP_OS;
	}

	$mime_type = '';

	if (function_exists('mime_content_type')) {
		$mime_type = mime_content_type($file);
	}
	
	// use PECL fileinfo to determine mime type
	if (!valid_src_mime_type($mime_type)) {
		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mime_type = finfo_file($finfo, $file);
			finfo_close($finfo);
		}
	}

	// try to determine mime type by using unix file command
	// this should not be executed on windows
    if (!valid_src_mime_type($mime_type) && $os != "WIN") {
		if (preg_match("/FREEBSD|LINUX/", $os)) {
			$mime_type = trim(@shell_exec('file -bi "' . $file . '"'));
		}
	}

	// use file's extension to determine mime type
	if (!valid_src_mime_type($mime_type)) {

		// set defaults
		$mime_type = 'image/png';
		// file details
		$fileDetails = pathinfo($file);
		$ext = strtolower($fileDetails["extension"]);
		// mime types
		$types = array(
 			'jpg'  => 'image/jpeg',
 			'jpeg' => 'image/jpeg',
 			'png'  => 'image/png',
 			'gif'  => 'image/gif'
 		);
		
		if (strlen($ext) && strlen($types[$ext])) {
			$mime_type = $types[$ext];
		}
		
	}
	
	return $mime_type;

}

/**
 * 
 */
function valid_src_mime_type($mime_type) {

	if (preg_match("/jpg|jpeg|gif|png/i", $mime_type)) {
		return true;
	}
	
	return false;

}

/**
 * 
 */
function check_cache($cache_dir, $mime_type) {

	// make sure cache dir exists
	if (!file_exists($cache_dir)) {
		// give 777 permissions so that developer can overwrite
		// files created by web server user
		mkdir($cache_dir);
		chmod($cache_dir, 0777);
	}

	show_cache_file($cache_dir, $mime_type);

}

/**
 * 
 */
function show_cache_file($cache_dir) {

	$cache_file = $cache_dir . '/' . get_cache_file();

	if (file_exists($cache_file)) {
    	
		$gmdate_mod = gmdate("D, d M Y H:i:s", filemtime($cache_file));
		
		if(! strstr($gmdate_mod, "GMT")) {
			$gmdate_mod .= " GMT";
		}
		
		if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
		
			// check for updates
			$if_modified_since = preg_replace("/;.*$/", "", $_SERVER["HTTP_IF_MODIFIED_SINCE"]);
			
			if ($if_modified_since == $gmdate_mod) {
				header("HTTP/1.1 304 Not Modified");
				exit;
			}

		}
		
		$fileSize = filesize($cache_file);
		
		// send headers then display image
		header("Content-Type: image/png");
		header("Accept-Ranges: bytes");
		header("Last-Modified: " . $gmdate_mod);
		header("Content-Length: " . $fileSize);
		header("Cache-Control: max-age=9999, must-revalidate");
		header("Expires: " . $gmdate_mod);
		
		readfile($cache_file);
		
		exit;

	}
	
}

/**
 * 
 */
function get_cache_file() {

	global $lastModified;
	static $cache_file;
	
	if(!$cache_file) {
		$cachename = $_SERVER['QUERY_STRING'] . VERSION . $lastModified;
		$cache_file = md5($cachename) . '.png';
	}
	
	return $cache_file;

}

/**
 * check to if the url is valid or not
 */
function valid_extension ($ext) {

	if (preg_match("/jpg|jpeg|png|gif/i", $ext)) {
		return TRUE;
	} else {
		return FALSE;
	}
	
}

/**
 * tidy up the image source url
 */
function cleanSource($src) {

	// remove slash from start of string
	if(strpos($src, "/") == 0) {
		$src = substr($src, -(strlen($src) - 1));
	}

	// remove http/ https/ ftp
	$src = preg_replace("/^((ht|f)tp(s|):\/\/)/i", "", $src);
	// remove domain name from the source url
	$host = $_SERVER["HTTP_HOST"];
	$src = str_replace($host, "", $src);
	$host = str_replace("www.", "", $host);
	$src = str_replace($host, "", $src);

	// don't allow users the ability to use '../' 
	// in order to gain access to files below document root

	// src should be specified relative to document root like:
	// src=images/img.jpg or src=/images/img.jpg
	// not like:
	// src=../images/img.jpg
	$src = preg_replace("/\.\.+\//", "", $src);
	
	// get path to image on file system
	$src = get_document_root($src) . '/' . $src;	

	return $src;

}

/**
 * 
 */

function get_document_root ($src) {

	// check for unix servers
	if(@file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $src)) {
		return $_SERVER['DOCUMENT_ROOT'];
	}
	
	// check from script filename (to get all directories to timthumb location)
	$parts = array_diff(explode('/', $_SERVER['SCRIPT_FILENAME']), explode('/', $_SERVER['DOCUMENT_ROOT']));
	$path = $_SERVER['DOCUMENT_ROOT'] . '/';
	foreach ($parts as $part) {
		$path .= $part . '/';
		if (file_exists($path . $src)) {
			return $path;
		}
	}	
	
	// the relative paths below are useful if timthumb is moved outside of document root
	// specifically if installed in wordpress themes like mimbo pro:
	// /wp-content/themes/mimbopro/scripts/timthumb.php
	$paths = array(
		".",
		"..",
		"../..",
		"../../..",
		"../../../..",
		"../../../../.."
	);
	
	foreach($paths as $path) {
		if(@file_exists($path . '/' . $src)) {
			return $path;
		}
	}
	
	// special check for microsoft servers
	if(!isset($_SERVER['DOCUMENT_ROOT'])) {
    	$path = str_replace("/", "\\", $_SERVER['ORIG_PATH_INFO']);
    	$path = str_replace($path, "", $_SERVER['SCRIPT_FILENAME']);
    	
    	if( @file_exists( $path . '/' . $src ) ) {
    		return $path;
    	}
	}	
	
	displayError('file not found ' . $src);

}

/**
 * generic error message
 */
function displayError($errorString = '') {

	header('HTTP/1.1 400 Bad Request');
	die($errorString);
	
}
?>