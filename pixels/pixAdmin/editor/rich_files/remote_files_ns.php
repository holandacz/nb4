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
	$text = $rich_lang->item('RemoteFiles');

	if(isset($action)){
		if(isset($file)) $file = stripslashes($file);
			else $file = '';
		if(isset($del_path)) $del_path = stripslashes($del_path);
			else $del_path = '';
		if(isset($name)) $name = stripslashes($name);
			else $name = '';

		//check if have permissions to make any action
		if (!$re_set_avail_paths) {
			$avail = true;
		} else {
			$avail = false;
			foreach ($re_set_avail_paths as $key=>$val) {
				$pos = strpos($del_path, $val);
				if ($pos !== false && $pos == 0) {
					$avail = true;
					break;
				}
			}
		}

		$delete_result = false;
		$dir_exists = false;
		$rename_result = false;

		$do_redir = false;

		if ($avail) {

			//deletes files and directories
			if ($action == 'delete' && $file != '') {
				$abs_del_path = abs_path($del_path);
				if (is_file($abs_del_path.$file) && $re_set_can_delete_file) { //file
					$delete_result = @unlink($abs_del_path.$file);
				} else { //directory
					if ($re_set_can_delete_dir) $delete_result = @rmdir($abs_del_path.$file);
				}

				$do_redir = true;
			}
    
			if ($action == 'save_create_dir' && $re_set_can_create_dir) {
				$dir_path = abs_path($del_path).$name;
    
				$oldmask = umask(0);
				$dir_exists = @mkdir($dir_path, 0777);
				umask($oldmask);

				$do_redir = true;
			}

			if($action == 'save_rename' && isset($name)){
				$abs_del_path = abs_path($del_path);
				$file_to_rename = $abs_del_path.$file;
				$new_name = $abs_del_path.$name;
				$its_file = is_file($file_to_rename);

				if ($its_file && $re_set_can_rename_file ||
					!$its_file && $re_set_can_rename_dir) {

					//check if file extension is available
					$size = @getimagesize($file_to_rename);
					$lower_file_ext = strtolower(file_ext($name));

					if ((!$re_set_avail_files ||
						in_array($lower_file_ext, $re_set_avail_files)) &&
						(!$re_set_not_avail_files ||
						!in_array($lower_file_ext, $re_set_not_avail_files))) {
						$rename_result = @rename($file_to_rename, $new_name);
					} else {
						if (is_file($file_to_rename)) $wrong_ext = true;
					}

				}
//				$del_path = $del_path.$file;

				$do_redir = true;
			}

			if ($do_redir) {
				$redir_url = 'remote_files_ns.php?files_path='.$files_path.'&files_url='.$files_url.'&file_type='.$file_type.'&del_path='.$del_path.'&lang='.$lang;
				if (isset($_GET['re_session_id'])) $redir_url .= '&re_session_id='.$re_session_id;
				if ($action == 'save_create_dir') $redir_url .= '&action=reload';
				Header('Location: '.$redir_url);
			}

		}

	}

	if (isset($local_del_path)) {
		$redir_url = 'remote_files_ns.php?files_path='.$files_path.'&files_url='.$files_url.'&file_type='.$file_type.'&del_path='.$local_del_path.'&lang='.$lang.'&action=reload';
		if (isset($_GET['re_session_id'])) $redir_url .= '&re_session_id='.$re_session_id;
		Header('Location: '.$redir_url);
		exit;

//		$del_path = stripslashes($local_del_path);
//		$action = 'reload';
	}

?>
<html>
<head>
<title>Remote Files</title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $rich_lang->item("Charset"); ?>">

<link rel="StyleSheet" type="text/css" href="rich_ns.css">

<script language="JavaScript">

  //load images in cash
  temp_img = new Image();
  temp_img.src = 'images/minus.gif';
  temp_img.src = 'images/plus.gif';

  rich_path = ''; //path to directory with editor files, here - current directory

//calls parser of remote files select
function select_remote_file(url, width, height){
  window.top.window.parse_select_remote_file(url, width, height);
}

//switch visibility of id-th directory content
function switch_div(id, path, url){
  eval('obj = document.getElementById("dir_div'+id+'");'); //div of directory
  eval('obj_img = document.getElementById("dir_img'+id+'");'); //image of directory

  if(obj.style.display){
    obj.style.display='';
    obj_img.src = 'images/minus.gif';
  }else{
    if(id>1){
      obj.style.display='none';
      obj_img.src = 'images/plus.gif';
    }
  }
}

//set current directory for file uploading
function set_cur_dir(id, path, url){
	if (!id_number) return; //directory tree is not loaded completely

  eval("obj_a = document.getElementById('dir_a"+id+"');"); //link of directory

	if (!obj_a) return;

  if(window.top.document.getElementById('local_files').contentDocument &&
     window.top.document.getElementById('local_files').contentDocument.getElementById('local_files_form')){
    var local_form = window.top.document.getElementById('local_files').contentDocument.getElementById('local_files_form');

    local_form.files_path.value = path;
    local_form.files_url.value = url;
  }

  //save path to parent dir in a form hidden
  document.getElementById('remote_files_form').del_path.value = path;

  //highlight name of last clicked directory
  for(i=1;i<=id_number;i++){
    eval("document.getElementById('dir_a"+i+"').style.color = '';");
  }
  obj_a.style.color = 'red';

}

//check if value is not empty
function not_empty(value){
  return !value.match(/^\s*$/g);
}

//check if dir name not empty
function validate_dir_name(form){
  if(form.action.value != 'cancel' && !not_empty(form.name.value)){
    form.name.focus();
    alert('<?php echo $text['InputAName']; ?>');
    return false;
  }
  return true;
}

</script>

</head>

<body class="re_dialog" topmargin="0" leftmargin="0" rightmargin="0">

<div id="re_loading_div_id" style="display:none;position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:999;">
<table border="0" sellspacing="0" sellpadding="0" width="100%" height="100%">
<tr>
<td>
</td>
</tr>
</table>
</div>

<form name="remote_files_form" id="remote_files_form" onsubmit="return validate_dir_name(this);">
<input type="hidden" name="files_path" value="<?php echo $files_path; ?>">
<input type="hidden" name="files_url" value="<?php echo $files_url; ?>">
<input type="hidden" name="file_type" value="<?php echo $file_type; ?>">
<input type="hidden" name="lang" value="<?php echo $lang; ?>">

<!-- list of remote files -->
<table border="0" cellspacing="0" cellpadding="2" width="100%" class="re_dialog"><tr vAlign="top"><td>

<?php if(isset($action) && ($action == 'rename' || $action == 'create_dir')) : ?>

<input type="hidden" name="action" value="save_<?php echo $action; ?>">
<input type="hidden" name="del_path" value="<?php echo $del_path; ?>">
<input type="hidden" name="file" value="<?php if (isset($file)) echo $file; ?>">

<table border="0" cellspacing="0" cellpadding="0" width="100%" align="top">
<tr><td>
<?php
  $dir = str_replace($files_path, $text['root'].'/', $del_path);
  if($action == 'rename') echo $text['NewNameFor'].' \''.$file.'\'';
    else echo $text['CreateFolderIn'].' '.$dir;
?>
:</td></tr>
<tr>
<td width="100%"><input name="name" type="text" value="<?php if (isset($file)) echo $file; ?>" style="width:100%;height:22"></td>
</tr>
<tr>
<td align="center"><input type="submit" value="<?php echo $rich_lang->item('Ok'); ?>" style="height=22">
<input type="button" value="<?php echo $rich_lang->item('Cancel'); ?>" style="height=22" onclick="form.action.value='cancel'; form.submit();"></td>
</tr>
</table>

<?php else : ?>

<input type="hidden" name="action" value="create_dir">
<input type="hidden" name="del_path" value="<?php echo $files_path; ?>">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%" style="font:14;">

<!--<tr><td colspan="2"><b>File name:</b></td></tr>-->

<?php

	//compares file/dir names
	function cmp($a, $b) {
		return strcmp($a['name'], $b['name']);
	}

  //draw tree of directories and files from $files_path directory
  //$level - level of the current directory, id - unique number for divs
  function draw_dir_tree($files_path, $files_url, $file_type='', &$id, $level=1){
    global $initial_files_path;
    global $initial_files_url;
    global $del_path;
	global $text;
	global $lang;

	global $cur_dir_id;

//    global $last_id;
//    global $last_path;
//    global $last_url;

    //prepare list of files and directories
    $entries = array();
    $files  = array();
    $indx = 1;

    $abs_files_path = abs_path($files_path);
    $handle = @opendir($abs_files_path);
    if($handle){
  
      while(($file = readdir($handle)) !== false){
  
        if($file != '.' && $file != '..'){
          if(is_file($abs_files_path.$file)){ //files
            $files[] = $file;
          }else{ //directory
            $entries[$indx]['name'] = $file;
            $entries[$indx]['is_dir'] = true;
            $indx++;
          }
  
        }
  
      }
      closedir($handle);
  
    } else {
		echo $text['WrongFilesPath'].'&nbsp;"'.$files_path.'"!';
		return;
	}

	usort($entries, "cmp");
	usort($files, "cmp");
  
    //add list of files to list of directories
    while(list($k,$val)=each($files)){
      $entries[$indx++]['name'] = $val;
    }

    if( $level>1 && !($del_path && ereg("^$files_path(.*)",$del_path)) ){
      $closed = true;
    }else $closed = false;

    if($level == 1){
	  $res = '';

		global $re_set_total_size;
		if ($re_set_total_size) {
			$total_dir_size =
				get_total_dir_size(abs_path(stripslashes($files_path)));
			$res .= '<tr><td class="re_remote_dirsize">'.$text['FreeSpace'].':&nbsp;'.convert_file_size($re_set_total_size-$total_dir_size).'/'.convert_file_size($re_set_total_size).'</td></tr>';
		}

      $res .= '<tr>';
      $res .= '<td>';

      $res .= '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td>';

      $res .= '<b><a class="re_remote" id="dir_a'.$id.'" href="javascript: set_cur_dir('.$id.',\''.$initial_files_path.'\',\''.$initial_files_url.'\');" style="color:red">'.$text['root'].'</a></b>';
// style="color:red"
      $res .= '</td>';

      $res .= '<td align="right">';

	  global $re_set_can_create_dir;
	  if ($re_set_can_create_dir) {
		$res .= '<a class="re_remote" href="#" onclick="window.document.getElementById(\'remote_files_form\').submit(); return false;">'.$text['CreateFolder'].'</a>';
	  } else $res .= '&nbsp';
	  $res .= '</td>';

      $res .= '</tr>';
      $res .= '</table>';

      $res .= '</td>';
      $res .= '</tr>';

	  echo $res;
    }

	$res = '';

    $res .= '<tr height=0><td height=0><div id="dir_div'.$id.'"';
    //if just created/renamed or deleted file/dir, make all parent dirs visible
    if($closed){
      $res .= ' style="display:none;"';
    }
    $res .= '>';

    $res .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">';

    //adjust row heights
    $res .= '<tr><td>';
    $res .= '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td>';
    $res .= '</td></tr></table>';
    $res .= '</td></tr>';

	echo $res;

    //draw content of the current directory
    if($entries){
		global $re_set_can_rename_file;
		global $re_set_can_delete_file;
		global $re_set_can_rename_dir;
		global $re_set_can_delete_dir;
  
      while(list($k,$val)=each($entries)){
        $file = $val['name'];
    
        if(!isset($val['is_dir']) || !$val['is_dir']){ //files

          switch($file_type){
    
            case "image":
            case "flash":
              $size = @getimagesize($abs_files_path.$file);
              if($size[2]!=4 && $size[2]!=13 && $file_type=='image' && $size[2] ||
                 ($size[2]==4 || $size[2]==13) && $file_type=='flash'){ //image&flash
				$res = '';

                $res .= "<tr onmouseover=\"bgColor='#6699CC';\" onmouseout=\"bgColor='';\"><td width=\"100%\">\n";

                $res .= '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td>';

                $res .= indent($level);
                $res .= '<a class="re_remote" href="#" onClick="select_remote_file(\''.correct_path($files_url.$file).'\','.$size[0].','.$size[1].'); return false;">'.$file.'</a>&nbsp;';
                $res .= "</td>";

                $res .= '<td width="1">';
				if ($re_set_can_rename_file) {
					$res .= '<a class="re_remote" href="?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=rename&del_path='.$files_path.'&file='.rawurlencode($file).'&lang='.$lang.(isset($_SESSION)?'&'.SID:'').'">r</a>';
				}
				$res .= '&nbsp;</td>';

                $res .= '<td width="1">';
				if ($re_set_can_delete_file) {
					$res .= '<a class="re_remote" href="?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=delete&del_path='.$files_path.'&file='.rawurlencode($file).'&lang='.$lang.(isset($_SESSION)?'&'.SID:'').'" onclick="javascript: if (window.confirm(\''.$text['Delete'].' \\\''.str_replace('\'', '\\\'', $file).'\\\'?\')) return true; else return false;">x</a>';
				}
				$res .= '</td>';
//                echo '<td width="1"><a class="re_remote" href="javascript: window.location = \'?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=rename&del_path='.$files_path.'&file='.$file.'&lang='.$lang.'\';">r</a>&nbsp;</td>';
//                echo '<td width="1"><a class="re_remote" href="javascript: if (window.confirm(\'Delete \\\''.$file.'\\\'?\')) window.location = \'?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=delete&del_path='.$files_path.'&file='.$file.'&lang='.$lang.'\';">x</a></td>';

                $res .= '</tr></table></td>';

                $res .= "</tr>\n";

                //adjust row heights
                $res .= '<tr><td>';
                $res .= '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td>';
                $res .= '</td></tr></table>';
                $res .= '</td></tr>';

				echo $res;

              }
              break;
    
            default:
			  $res = '';

              $res .= "<tr onmouseover=\"bgColor='#6699CC';\" onmouseout=\"bgColor='';\"><td width=\"100%\">\n";

              $res .= '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td>';

              $res .= indent($level);
              $res .= '<a class="re_remote" href="#" onClick="select_remote_file(\''.correct_path($files_url.$file).'\'); return false;">'.$file.'</a>&nbsp;';
              $res .= "</td>";

				$res .= '<td width="1">';
				if ($re_set_can_rename_file) {
					$res .= '<a class="re_remote" href="?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=rename&del_path='.$files_path.'&file='.rawurlencode($file).'&lang='.$lang.(isset($_SESSION)?'&'.SID:'').'">r</a>';
				}
				$res .= '&nbsp;</td>';

				$res .= '<td width="1">';
				if ($re_set_can_delete_file) {
					$res .= '<a class="re_remote" href="?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=delete&del_path='.$files_path.'&file='.rawurlencode($file).'&lang='.$lang.(isset($_SESSION)?'&'.SID:'').'" onclick="javascript: if (window.confirm(\''.$text['Delete'].' \\\''.str_replace('\'', '\\\'', $file).'\\\'?\')) return true; else return false;">x</a>';
				}
				$res .= '</td>';

//              echo '<td width="1"><a class="re_remote" href="javascript: window.location = \'?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=rename&del_path='.$files_path.'&file='.rawurlencode($file).'&lang='.$lang.'\';">r</a>&nbsp;</td>';
//              echo '<td width="1"><a class="re_remote" href="javascript: if (window.confirm(\'Delete \\\''.$file.'\\\'?\')) window.location = \'?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=delete&del_path='.$files_path.'&file='.$file.'&lang='.$lang.'\';">x</a></td>';

              $res .= '</tr></table></td>';

              $res .= "</tr>\n";

              //adjust row heights
              $res .= '<tr><td>';
              $res .= '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td>';
              $res .= '</td></tr></table>';
              $res .= '</td></tr>';

			  echo $res;

              break;
    
          }//switch
    
        }else{ //directories

          $id++; //get unique div id

          if( !($del_path && ereg("^$files_path$file/(.*)",$del_path)) ){
            $closed = true;
          } else {
			$closed = false;

			if ($del_path == $files_path.$file.'/') $cur_dir_id = $id;
		  }

		  $res = '';

          $res .= "<tr onmouseover=\"bgColor='#6699CC';\" onmouseout=\"bgColor='';\"><td width=\"100%\">\n";

          $res .= '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td>';

          $res .= indent($level);

          $res .= '<img width="11" height="11" id="dir_img'.$id.'" style="cursor:hand" onclick="switch_div('.$id.');" src="images/';
          if(!$closed) $res .= 'minus.gif';
            else $res .= 'plus.gif';
          $res .= '">&nbsp;';

          $res .= '<b><a class="re_remote" id="dir_a'.$id.'" href="javascript: set_cur_dir('.$id.',\''.str_replace('\'', '\\\'', $files_path.$file).'/\',\''.str_replace('\'', '\\\'', $files_url.$file).'/\');">'.$file.'</a></b>&nbsp;</td>';

			$res .= '<td width="1">';
			if ($re_set_can_rename_dir) {
				$res .= '<a class="re_remote" href="?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=rename&del_path='.$files_path.'&file='.rawurlencode($file).'&lang='.$lang.(isset($_SESSION)?'&'.SID:'').'">r</a>';
			}
			$res .= '&nbsp;</td>';

			$res .= '<td width="1">';
			if ($re_set_can_delete_dir) {
				$res .= '<a class="re_remote" href="?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=delete&del_path='.$files_path.'&file='.rawurlencode($file).'&lang='.$lang.(isset($_SESSION)?'&'.SID:'').'" onclick="javascript: if (window.confirm(\''.$text['Delete'].' \\\''.str_replace('\'', '\\\'', $file).'\\\'?\')) return true; else return false;">x</a>';
			}
			$res .= '</td>';

//          echo '<td width="1"><a class="re_remote" href="javascript: window.location = \'?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=rename&del_path='.$files_path.'&file='.rawurlencode($file).'&lang='.$lang.'\';">r</a>&nbsp;</td>';
//          echo '<td width="1"><a class="re_remote" href="javascript: if (window.confirm(\'Delete \\\''.$file.'\\\'?\')) window.location = \'?files_path='.$initial_files_path.'&files_url='.$initial_files_url.'&file_type='.$file_type.'&action=delete&del_path='.$files_path.'&file='.rawurlencode($file).'&lang='.$lang.'\';">x</a></td>';

          $res .= '</tr></table></td>';

          $res .= "</tr>\n";

		  echo $res;

          draw_dir_tree($files_path.$file.'/', $files_url.$file.'/', $file_type,
                        $id, $level+1);
        }
    
      }

    }

    echo '</table></div></td></tr>';

   }

  //to see different directory levels
  function indent($level){
	$result = '';
    $symb = '&nbsp;&nbsp;';
    for($i=0;$i<$level;$i++){
      $result .= $symb;
    }

    return $result;
  }

  $initial_files_path = $files_path;
  $initial_files_url = $files_url;

  $id = 1;
  $cur_dir_id = 1;
  draw_dir_tree($files_path, $files_url, $file_type, $id);

?>

<tr><td height="100%">&nbsp;</td></tr>

</table>

<?php endif; ?>

</td></tr>

</table>
<!-- !list of remote files -->

</form>

<script language="JavaScript">
  id_number = <?php echo isset($id)?(int)$id:0; ?>; //number of divs

<?php
//  if($last_id){
//    echo 'set_cur_dir('.$last_id.',\''.$last_path.'\',\''.$last_url.'\');';
//  }

	if (!isset($cur_dir_id))  $cur_dir_id = 1;
	if (isset($del_path) && $cur_dir_id != 1 &&
		isset($action) && $action == 'reload') {
		$cur_dir = str_replace($files_path, '', $del_path);
	} else {
		$cur_dir_id = 1;
		$cur_dir = '';
	}

	echo "\n";
	echo 'set_cur_dir("'.$cur_dir_id.'","'.$files_path.$cur_dir.'","'.$files_url.$cur_dir.'");';
	echo "\n";

/*
	if (isset($action) &&
		($action == 'save_create_dir' || $action == 'save_rename' ||
		 $action == 'delete')) {
		echo "\n";
		echo 'set_cur_dir(1,"'.$files_path.'","'.$files_url.'");';
		echo "\n";
	}
*/
?>

<?php

	if (isset($action)) {

		if ($avail) {

			//actions denied
			if ($action == 'save_create_dir' && !$dir_exists) {
				echo "alert('".$text['CannotCreateFolder']." \'".str_replace('\'', '\\\'', $name)."\'!');\n";
			}

			if ($action == 'save_rename' && !$rename_result) {
				if (isset($wrong_ext)) {
					echo "alert('".$text['WrongExt']." \'".str_replace('\'', '\\\'', $name)."\'!');\n";
				} else {
					echo "alert('".$text['CannotRename']." \'".str_replace('\'', '\\\'', $file)."\'!');\n";
				}
			}

			if ($action == 'delete' && !$delete_result) {
				echo "alert('".$text['CannotDelete']." \'".str_replace('\'', '\\\'', $file)."\'!');\n";
			}

		} else {
			if ($action == 'save_create_dir' || $action == 'save_rename' ||
				$action == 'delete') {
				echo "alert('".$text['NoAccessToThisDir']." \'".$text['root'].str_replace('\'', '\\\'', ereg_replace('^'.$files_path, '/', $del_path))."\'!');\n";
			}
		}

	}

?>

</script>

</body>
</html>