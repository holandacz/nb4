<?php

//fix path with national symbols inside
function correct_path($path){
	$parts = split("/",$path);
	foreach ($parts as $k=>$val) {
		if (isset($result)) {
			$result .= '/';
		} else {
			if (substr($val, strlen($val)-1, 1) == ':') { //leave protocol prefix unchanged
				$result = $val;
				continue;
			} else $result = '';
		}
		$result .= rawurlencode(stripslashes($val));
	}
	return $result;
}

//make path absolute
function abs_path($path) {
	if (isset($path[0]) && $path[0] != '/') return rel_path($path);
	return $_SERVER['DOCUMENT_ROOT'].$path;
}

//fix relative path
function rel_path($path) {
	return '../'.$path;
}

//returns extension of file $file_name
function file_ext($file_name) {
	$dot_pos = strrpos($file_name,'.');
	if ($dot_pos === false) return '';

	return substr($file_name,$dot_pos+1);
}

//returns total size of all files in folder $path including subfolders
function get_total_dir_size($path) {

	$handle = @opendir($path);
	if ($handle) {

		$files = array();
		$folders = array();
		$size = 0;
		while (($file = readdir($handle)) !== false) {

			if ($file != '.' && $file != '..') {
				if (is_file($path.$file)) {
					$files[] = $file;
				} else {
					$folders[] = $file;
				}

			}

		}
		closedir($handle);

		//count size of files in the folder
		foreach ($files as $k=>$file) {
			$size += filesize($path.$file);
		}

		//count size of files in subfolders
		foreach ($folders as $k=>$file) {
			$size += get_total_dir_size($path.$file.'/');
		}

		return $size;
	}

	return false;

}

//returns image extension (jpg, gif, etc)
//by its code returned by getimagesize function
function get_image_ext($type_code) {
$ext = false;

	switch ($type_code) {
		case 1:
			$ext = 'gif';
			break;
		case 2:
			$ext = 'jpg';
			break;
		case 3:
			$ext = 'png';
			break;
		case 6:
			$ext = 'bmp';
			break;
		default:
			$ext = false;
			break;
	}

	return $ext;
}

//convert file size to number of b, Kb, Mb, or Gb depending of the size
function convert_file_size($size) {
	if ($size < 0) $size = 0;

	if ($size/(1024*1024*1024) >= 1) return ((int)($size/(1024*1024*1024)*10)/10).'Gb';
	if ($size/(1024*1024) >= 1) return ((int)($size/(1024*1024)*10)/10).'Mb';
	if ($size/1024 >= 1) return ((int)($size/1024*10)/10).'Kb';
	return $size.'b';
}
?>