<?php
/*

Check here if users have permission to uploading/renaming/deleting files/folders
Eg you can check here a session var, or records in your DB. It depends on
security policy on your sites or in your web applications

Example:

	session_start();
	if (!$_SESSION['user_authorized']) {
		echo 'You are not allowed to work with files';
		echo ' on server using the WYSIWYG editor!';
		exit;
	}

*/


	/*
	* Rich Editor settings defining permissions to files/dirs on server
	*
	* You can change their values manually or read them from DB or/and
	* session variables to make them changable in your applications
	*/


	/*
	* Common
	*/

	/*
	* defines paths available for performing any actions (upload, rename, etc)
	* The actions are permitted in folders which paths start with one of them
	* If not set then any path is available
	* Eg: array('/user_files/john/'); //this path is relative to site root
	*  or array('../user_files/john/'); //this path is relative to folder class
	*/
	$re_set_avail_paths = array();

	/*
	* defines max size (in bytes) available for all files in folder for uploading
	* including subfolders
	* No restriction if set to 0
	*/
	$re_set_total_size = 0;

	/*
	* Common
	*/


	/*
	* Uploading
	*/

	/*
	* user is permitted to upload files on server
	* (the rest of uploading settings is applied if this one set to true only)
	*/
	$re_set_can_upload = true;

	/*
	* set max size (in bytes) of uploaded file available (-1 if no restrictions)
	*/
	$re_set_max_size = -1;

	/*
	* set permission to upload if file with the same name exists yet
	*/
	$re_set_allow_override = true;

	/*
	* file extensions available for uploading and renaming (all if not set)
	* Eg: $re_set_avail_files = array ('zip', 'txt');
	*/
	$re_set_avail_files = array();

	/*
	* file extensions not available for uploading and renaming
	* (no restrictions if not set). Higher priority then $re_set_avail_files!
	* Eg: $re_set_not_avail_files = array ('php', 'pl');
	*/
	$re_set_not_avail_files = array();

	/*
	* types of images available for uploading and renaming (all if not set)
	* possible values: 'jpg', 'gif', 'png', 'bmp'
	* Eg: $re_set_avail_images = array ('jpg', 'gif', 'png');
	*/
	$re_set_avail_images = array();

	/*
	* types of images not available for uploading and renaming
	* (no restrictions if not set). Higher priority then $re_set_avail_images!
	* possible values: 'jpg', 'gif', 'png', 'bmp'
	* Eg: $re_set_not_avail_images = array ('bmp', 'gif');
	*/
	$re_set_not_avail_images = array();

	/*
	* max size of images available for uploading (0s if no restrictions)
	* set as array (width, height)
	*/
	$re_set_max_image_size = array(0, 0);

	/*
	* max size of flash files available for uploading (0s if no restrictions)
	* set as array (width, height)
	*/
	$re_set_max_flash_size = array(0, 0);

	/*
	* Uploading
	*/


	/*
	* File browser
	*/

	/*
	* set permission to create folders
	*/
	$re_set_can_create_dir = true;

	/*
	* set permission to rename folders
	*/
	$re_set_can_rename_dir = true;

	/*
	* set permission to delete folders
	*/
	$re_set_can_delete_dir = true;

	/*
	* set permission to rename files
	*/
	$re_set_can_rename_file = true;

	/*
	* set permission to delete files
	*/
	$re_set_can_delete_file = true;
	
	/*
	* File browser
	*/


	/*
	* uncomment the following line if you need to use not only these default
	* settings, but Settings' RE API as well, as it requires sessions
	* Or you can uncomment it for all cases if you always use sessions
	*
	* Initially commented as sessions are not required by RE, but if
	* sessions are not configured correctly on your server a warning will be
	* printed by PHP every time session_start() is called
	*/
//	session_start();


	/*
	* Change default settings if the appropriate values has been set via
	* Settings' RE API
	*/

	$re_set_list = array(

're_set_avail_paths', 're_set_total_size', 're_set_can_upload',
're_set_max_size', 're_set_allow_override', 're_set_avail_files',
're_set_not_avail_files', 're_set_avail_images', 're_set_not_avail_images',
're_set_max_image_size', 're_set_max_flash_size', 're_set_can_create_dir',
're_set_can_rename_dir', 're_set_can_delete_dir', 're_set_can_rename_file',
're_set_can_delete_file'

	);

	foreach ($re_set_list as $re_set_item) {
		if (isset($_SESSION[$re_set_item.'_sess']) &&
			$_SESSION[$re_set_item.'_sess'] != 'default') {
			$$re_set_item = $_SESSION[$re_set_item.'_sess'];
		}
	}
?>