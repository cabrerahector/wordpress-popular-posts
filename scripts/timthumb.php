<?php

// TimThumb script created by Tim McDaniels and Darren Hoyt with tweaks by Ben Gillbanks
// http://code.google.com/p/timthumb/

// MIT License: http://www.opensource.org/licenses/mit-license.php

/* Parameters allowed: */

// w: width
// h: height
// zc: zoom crop (0 or 1)
// q: quality (default is 75 and max is 100)

// HTML example: <img src="/scripts/timthumb.php?src=/images/whatever.jpg&w=150&h=200&zc=1" alt="" />

error_reporting(E_ALL);

if(!isset($_REQUEST["src"])) {
	die("no image specified");
}

// clean params before use
$src = clean_source( $_REQUEST[ "src" ] );

// set document root
$doc_root = get_document_root($src);

// get path to image on file system
$src = $doc_root . '/' . $src;

$new_width = preg_replace( "/[^0-9]+/", "", get_request( 'w', 100 ) );
$new_height = preg_replace( "/[^0-9]+/", "", get_request( 'h', 100 ) );
$zoom_crop = preg_replace( "/[^0-9]+/", "", get_request( 'zc', 1 ) );
$quality = preg_replace( "/[^0-9]+/", "", get_request( '9', 80 ) );

// set path to cache directory (default is ./cache)
// this can be changed to a different location
$cache_dir = './cache';

// get mime type of src
$mime_type = mime_type($src);

// check to see if this image is in the cache already
//check_cache($cache_dir, $mime_type);

// make sure that the src is gif/jpg/png
if(!valid_src_mime_type($mime_type)) {
	die("Invalid src mime type: $mime_type");
}

// check to see if GD function exist
if(!function_exists('imagecreatetruecolor')) {
	die("GD Library Error: imagecreatetruecolor does not exist");
}

if(strlen($src) && file_exists($src)) {

	// open the existing image
	$image = open_image($mime_type, $src);
	if($image === false) {
		die('Unable to open image : ' . $src);
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
		
	// output image to browser based on mime type
	show_image( $mime_type, $canvas, $quality, $cache_dir );
	
	// remove image from memory
	imagedestroy( $canvas );
	
} else {

	if(strlen($src)) {
		die($src . ' not found.');
	} else {
		die('no source specified.');
	}
	
}

function show_image( $mime_type, $image_resized, $quality, $cache_dir ) {

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
	
	if(stristr($mime_type, 'gif')) {
	
		imagegif($image_resized, $cache_file_name);
		
	} elseif(stristr($mime_type, 'jpeg')) {
	
		imagejpeg($image_resized, $cache_file_name, $quality);
		
	} elseif(stristr($mime_type, 'png')) {
	
		$quality = floor($quality * 0.09);		
		imagepng($image_resized, $cache_file_name, $quality);
		
	}
	
	if($is_writable) {
		show_cache_file( $cache_dir, $mime_type );
	}

	die();

}

function get_request( $property, $default = 0 ) {
	
	if( isset($_REQUEST[$property]) ) {
		return $_REQUEST[$property];
	} else {
		return $default;
	}
	
}

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

function mime_type($file) {

    $os = strtolower(php_uname());
	$mime_type = '';

	// use PECL fileinfo to determine mime type
	if( function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME);
		$mime_type = finfo_file($finfo, $file);
		finfo_close($finfo);
	}

	// try to determine mime type by using unix file command
	// this should not be executed on windows
    if(!valid_src_mime_type($mime_type) && !(eregi('windows', $os))) {
		if(preg_match("/freebsd|linux/", $os)) {
			$mime_type = trim(@shell_exec('file -bi $file'));
		}
	}

	// use file's extension to determine mime type
	if(!valid_src_mime_type($mime_type)) {

		// set defaults
		$mime_type = 'image/jpeg';
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
		
		if(strlen($ext) && strlen($types[$ext])) {
			$mime_type = $types[$ext];
		}
		
	}
	
	return $mime_type;

}

function valid_src_mime_type($mime_type) {

	if(preg_match("/jpg|jpeg|gif|png/i", $mime_type)) {
		return true;
	}
	return false;

}

function check_cache($cache_dir, $mime_type) {

	// make sure cache dir exists
	if(!file_exists($cache_dir)) {
		// give 777 permissions so that developer can overwrite
		// files created by web server user
		mkdir($cache_dir);
		chmod($cache_dir, 0777);
	}

	show_cache_file($cache_dir, $mime_type);

}

function show_cache_file($cache_dir, $mime_type) {

	$cache_file = $cache_dir . '/' . get_cache_file();

	if( file_exists( $cache_file ) ) {
    	
	    if( isset( $_SERVER[ "HTTP_IF_MODIFIED_SINCE" ] ) ) {
		
			// check for updates
			$if_modified_since = preg_replace( '/;.*$/', '', $_SERVER[ "HTTP_IF_MODIFIED_SINCE" ] );					
			$gmdate_mod = gmdate( 'D, d M Y H:i:s', filemtime( $cache_file ) );
			
			if( strstr( $gmdate_mod, 'GMT' ) ) {
				$gmdate_mod .= " GMT";
			}
			
			if ( $if_modified_since == $gmdate_mod ) {
				header( "HTTP/1.1 304 Not Modified" );
				exit;
			}

		}
		
		$fileSize = filesize($cache_file);
				
		// send headers then display image
		header("Content-Type: " . $mime_type);
		//header("Accept-Ranges: bytes");
		header("Last-Modified: " . gmdate('D, d M Y H:i:s', filemtime($cache_file)) . " GMT");
		header("Content-Length: " . $fileSize);
		header("Cache-Control: max-age=9999, must-revalidate");
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 9999) . "GMT");
		
		readfile($cache_file);
		
		die();

	}
	
}

function get_cache_file () {

	global $quality;

	static $cache_file;
	if(!$cache_file) {
		$frags = split( "\.", $_REQUEST['src'] );
		$ext = strtolower( $frags[ count( $frags ) - 1 ] );
		if(!valid_extension($ext)) { $ext = 'jpg'; }
		$cachename = get_request( 'src', 'timthumb' ) . get_request( 'w', 100 ) . get_request( 'h', 100 ) . get_request( 'zc', 1 ) . get_request( '9', 80 );
		$cache_file = md5( $cachename ) . '.' . $ext;
	}
	return $cache_file;

}

function valid_extension ($ext) {

	if( preg_match( "/jpg|jpeg|png|gif/i", $ext ) ) return 1;
	return 0;

}

function clean_source ( $src ) {

	// remove http/ https/ ftp
	$src = preg_replace("/^((ht|f)tp(s|):\/\/)/i", "", $src);
	// remove domain name from the source url
	$host = $_SERVER["HTTP_HOST"];
	$src = str_replace($host, "", $src);
	$host = str_replace("www.", "", $host);
	$src = str_replace($host, "", $src);
	
	//$src = preg_replace( "/(?:^\/+|\.{2,}\/+?)/", "", $src );
	//$src = preg_replace( '/^\w+:\/\/[^\/]+/', '', $src );

	// don't allow users the ability to use '../' 
	// in order to gain access to files below document root

	// src should be specified relative to document root like:
	// src=images/img.jpg or src=/images/img.jpg
	// not like:
	// src=../images/img.jpg
	$src = preg_replace( "/\.\.+\//", "", $src );

	return $src;

}

function get_document_root ($src) {
	if( @file_exists( $_SERVER['DOCUMENT_ROOT'] . '/' . $src ) ) {
		return $_SERVER['DOCUMENT_ROOT'];
	}
	// the relative paths below are useful if timthumb is moved outside of document root
	// specifically if installed in wordpress themes like mimbo pro:
	// /wp-content/themes/mimbopro/scripts/timthumb.php
	$paths = array( '..', '../..', '../../..', '../../../..' );
	foreach( $paths as $path ) {
		if( @file_exists( $path . '/' . $src ) ) {
			return $path;
		}
	}

}

?>
