<?php
	if (isset($_GET['re_session_id'])) session_id($_GET['re_session_id']);

	@include('check_auth.inc.php');
	@include('re_common_lib.inc.php');

//language class
require_once('lang/class.rich_lang.php');

	//extract variables submitted to this page
	@extract($_GET);
	@extract($_POST);

	$rich_lang = new rich_lang($lang); //text data in current language
	$text = $rich_lang->item('LocalFiles');
	$rem_text = $rich_lang->item('RemoteFiles');

	if (isset($action) && $action == "upload_file" && $re_set_can_upload) {

		$uploaded = false;

		//check if have permissions to make any action
		if (!$re_set_avail_paths) {
			$avail = true;
		} else {
			$avail = false;
			foreach ($re_set_avail_paths as $key=>$val) {
				$pos = strpos($files_path, $val);
				if ($pos !== false && $pos == 0) {
					$avail = true;
					break;
				}
			}
		}

		if ($avail) {

			$local_file = $_FILES['local_file']['tmp_name'];
			if (!$local_file) $local_file = $HTTP_POST_FILES['local_file']['tmp_name'];
			$local_file = str_replace("\\\\", "\\", $local_file);
//    if(file_exists($local_file)){

			$new_file_size = filesize($local_file);

			//check if size of the file do not exceed max limit
			if ($re_set_max_size < 0 || $new_file_size <= $re_set_max_size) {

				//calculate total size if necessary as it could be slow if
				//number of files and folder is very big
				if ($re_set_total_size > 0) {
					$total_dir_size =
					get_total_dir_size(abs_path(stripslashes($initial_files_path)));
				} else $total_dir_size = false;

				if ($re_set_total_size == 0 || $total_dir_size === false ||
					$new_file_size+$total_dir_size <= $re_set_total_size) {

					$name = $_FILES['local_file']['name'];
					if (!$name) $name = $HTTP_POST_FILES['local_file']['name'];
					$upload_file = abs_path(stripslashes($files_path.$name));
    
					//check if can override existing file if necessary
					if (!file_exists($upload_file) || $re_set_allow_override) {
						$size = @getimagesize($local_file);
						$lower_file_ext = strtolower(file_ext($name));

						if ((!$re_set_avail_files ||
							in_array($lower_file_ext, $re_set_avail_files)) &&
							(!$re_set_not_avail_files ||
							!in_array($lower_file_ext, $re_set_not_avail_files))) {
							$avail_ext = true;

							if($size[2]!=4 && $size[2]!=13 && $size[2]) { //image
    
								//determine type of image
								$lower_file_ext = get_image_ext($size[2]);
        
								if ((!$re_set_avail_images ||
						in_array($lower_file_ext, $re_set_avail_images)) &&
									(!$re_set_not_avail_images ||
						!in_array($lower_file_ext, $re_set_not_avail_images))) {
        
									//check max image size limit
									if ((!$re_set_max_image_size[0] ||
										$size[0] <= $re_set_max_image_size[0]) &&
										(!$re_set_max_image_size[1] ||
										$size[1] <= $re_set_max_image_size[1])) {
									} else {
										$wrong_image_size = true;

										if ($re_set_max_image_size[0] &&
											$size[0] > $re_set_max_image_size[0])
											$wrong_image_width = true;
    
										if ($re_set_max_image_size[1] &&
											$size[1] > $re_set_max_image_size[1])
											$wrong_image_height = true;
										}
        
								} else $wrong_image_type = true;

							}
        
							//check flash size limits
							if ($size[2]==4 || $size[2]==13) { //flash
								//check max image size limit
								if ((!$re_set_max_flash_size[0] ||
									$size[0] <= $re_set_max_flash_size[0]) &&
									(!$re_set_max_flash_size[1] ||
									$size[1] <= $re_set_max_flash_size[1])) {
									$avail_size = true;
								} else {
									$wrong_flash_size = true;

									if ($re_set_max_flash_size[0] &&
										$size[0] > $re_set_max_flash_size[0])
										$wrong_flash_width = true;

									if ($re_set_max_flash_size[1] &&
										$size[1] > $re_set_max_flash_size[1])
										$wrong_flash_height = true;
								}
							}

						} else $wrong_ext = true;
    
						if (!isset($wrong_ext) && !isset($wrong_image_size) &&
					!isset($wrong_image_type) && !isset($wrong_flash_size)) {
							$uploaded = @move_uploaded_file($local_file, $upload_file);
						}
    
					} else $cannot_override = true; //if (!file_exists($upload_file)

				} else $total_size_exceeded = true; //if ($re_set_total_size == 0

			} else $max_size_exceeded = true; //if ($re_set_max_size < 0

//    }
		}//if ($avail) {

	}

?>
<html>
<head>
<title>Local Files</title>

<link rel="StyleSheet" type="text/css" href="rich.css">

<script  language="JavaScript">

  rich_path = ''; //path to directory with editor files, here - current directory

//actions performed after loading file on server
function on_load_local(){

<?php

  if(isset($uploaded) && $uploaded){

    switch($file_type){
      case 'image':
        echo '
var url = \''.correct_path($files_url.$name).'\';
window.top.document.form_name.preview_image.src=url;
window.top.document.form_name.src.value=url;
window.top.window.is_local_file = false;
		';

		if ($upload_only) {
			 echo '
//window.top.remote_files.window.location = window.top.remote_files.window.location+"&action=reload";
window.top.remote_files.window.location = window.top.remote_files.window.location+"&local_del_path='.$files_path.'";
			';
		} else {
			echo '
window.top.return_image_parameters();
			';
		}

        break;
      case 'link':
        echo '
window.top.document.form_name.href.value=\''.correct_path($files_url.$name).'\';
window.top.window.is_local_file = false;
		';

		if ($upload_only) {
			 echo '
//window.top.remote_files.window.location = window.top.remote_files.window.location+"&action=reload";
window.top.remote_files.window.location = window.top.remote_files.window.location+"&local_del_path='.$files_path.'";
			';
		} else {
			echo '
window.top.return_link_parameters();
			';
		}

        break;
      case 'flash':
        echo '
var url = \''.correct_path($files_url.$name).'\';
window.top.flash_src = url;
window.top.set_preview_flash(window.top.flash_src);
window.top.document.form_name.src.value=url;
window.top.window.is_local_file = false;
		';

		if ($upload_only) {
			 echo '
//window.top.remote_files.window.location = window.top.remote_files.window.location+"&action=reload";
window.top.remote_files.window.location = window.top.remote_files.window.location+"&local_del_path='.$files_path.'";
			';
		} else {
			echo '
window.top.return_flash_parameters();
			';
		}

        break;
      default:
        break;
    }

  }

	if (isset($action) && $action == "upload_file") {

		echo "\nhide_loading_div();\n";

		if (!$re_set_can_upload) {
			echo "alert('".$text['CannotUpload']."');\n";
		} else {

			if (!$avail) {
				echo "alert('".$text['NoAccessToThisDir']." \'".$rem_text['root'].str_replace('\'', '\\\'', ereg_replace('^'.$initial_files_path, '/', $files_path))."\'!');\n";
			} else {
				$reason_found = false;

				if (isset($total_size_exceeded)) {
					echo "alert('".$text['TotalSizeExceeded']." ".convert_file_size($re_set_total_size)."');\n";
					$reason_found = true;
				}

				if (isset($max_size_exceeded)) {
					echo "alert('".$text['MaxSizeExceeded']." ".convert_file_size($re_set_max_size)."');\n";
					$reason_found = true;
				}

				if (isset($cannot_override)) {
					echo "alert('".$text['CannotOverride']."');\n";
					$reason_found = true;
				}

				if (isset($wrong_ext)) {
					echo "alert('".$text['WrongExt']." ".$name."');\n";
					$reason_found = true;
				}

				if (isset($wrong_image_type)) {
					echo "alert('".$text['WrongImageType']."');\n";
					$reason_found = true;
				}

				if (isset($wrong_image_size)) {
					if (isset($wrong_image_width) && !isset($wrong_image_height)) {
						echo "alert('".$text['WrongImageWidth']." ".$re_set_max_image_size[0]."');\n";
					} else if (isset($wrong_image_height) && !isset($wrong_image_width)) {
						echo "alert('".$text['WrongImageHeight']." ".$re_set_max_image_size[1]."');\n";
					} else echo "alert('".$text['WrongImageSize']." ".$re_set_max_image_size[0]."x".$re_set_max_image_size[1]."');\n";
					$reason_found = true;
				}

				if (isset($wrong_flash_size)) {
					if (isset($wrong_flash_width) && !isset($wrong_flash_height)) {
						echo "alert('".$text['WrongFlashWidth']." ".$re_set_max_flash_size[0]."');\n";
					} else if (isset($wrong_flash_height) && !isset($wrong_flash_width)) {
						echo "alert('".$text['WrongFlashHeight']." ".$re_set_max_flash_size[1]."');\n";
					} else echo "alert('".$text['WrongFlashSize']." ".$re_set_max_flash_size[0]."x".$re_set_max_flash_size[1]."');\n";
					$reason_found = true;
				}

				if (!$uploaded && !$reason_found) {
					echo "alert('".$text['UploadError']."');\n";
				}

			}

		}
	}

?>
}

//show div saying that loading in progress
function show_loading_div(win){
var parent_win = window.top;
	if (win) parent_win = win;

var div_obj = parent_win.document.getElementById('re_loading_div_id');
	div_obj.style.top = 2*(parseInt(parent_win.dialogWidth)-50)/5+'px';
	div_obj.style.left = 2*(parseInt(parent_win.dialogHeight)-120)/5+'px';
	div_obj.style.display = '';

	//disable src or href properties' fields
	var parent_form = parent_win.document.forms('form_name');
	var src_obj = parent_form('src');
	if (src_obj) src_obj.disabled = true;
	var href_obj = parent_form('href');
	if (href_obj) href_obj.disabled = true;

	//disable Ok button
	parent_win.document.getElementById('re_ok_button_id').disabled = true;
	parent_win.document.getElementById('re_upload_button_id').disabled = true;

	//place invisible div above remote and local files windows to disable them
	try {
		parent_win.document.remote_files.document.getElementById('re_loading_div_id').style.display = '';
		parent_win.document.local_files.document.getElementById('re_loading_div_id').style.display = '';
	} catch(e) {}

}

//hide div saying that loading in progress
function hide_loading_div(){
var parent_win = window.top;
var div_obj = parent_win.document.getElementById('re_loading_div_id');
	div_obj.style.display = 'none';

	var parent_form = parent_win.document.forms('form_name');
	var src_obj = parent_form('src');
	if (src_obj) src_obj.disabled = false;
	var href_obj = parent_form('href');
	if (href_obj) href_obj.disabled = false;

	parent_win.document.getElementById('re_ok_button_id').disabled = false;
	parent_win.document.getElementById('re_upload_button_id').disabled = false;

	try {
		parent_win.document.remote_files.document.getElementById('re_loading_div_id').style.display = 'none';
	} catch(e) {}
}

</script>

</head>

<body class="re_dialog" topmargin="0" leftmargin="0" rightmargin="0" onload="on_load_local();">

<div id="re_loading_div_id" style="display:none;position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:999;">
<table border="0" sellspacing="0" sellpadding="0" width="100%" height="100%">
<tr>
<td>
</td>
</tr>
</table>
</div>

<form name="local_files_form" method="post" enctype="multipart/form-data" action="" onsubmit="show_loading_div(window.top);">
<input type="hidden" name="action" value="upload_file">
<input type="hidden" name="upload_only" value="0">
<input type="hidden" name="initial_files_path" value="<?php if (isset($initial_files_path)) echo stripslashes($initial_files_path); else echo $files_path; ?>">
<input type="hidden" name="files_path" value="<?php echo $files_path; ?>">
<input type="hidden" name="files_url" value="<?php echo $files_url; ?>">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr><td width="100%" height="100%" vAlign="center" class="re_dialog">
<input name="local_file" type="file" style="width:100%;height=22" onpropertychange="window.top.window.select_local_file();" onfocus="window.top.window.select_local_file();">
</td></tr>
</table>

</form>
</body>
</html>