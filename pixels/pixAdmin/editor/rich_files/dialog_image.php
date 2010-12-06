<?php

//language class
require_once('lang/class.rich_lang.php');

	//extract variables submitted to this page
	@extract($_GET);

	$rich_lang = new rich_lang($lang); //text data in current language
	$text = $rich_lang->item('Image');
	$align = $rich_lang->item('Align');
	$uploading = $rich_lang->item('Uploading');

?><html>
<head>
<title><?php echo $text['Title']; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $rich_lang->item("Charset"); ?>">

<link rel="StyleSheet" type="text/css" href="rich.css">

<script language="JavaScript" src="rich_dialog.js?aaa"></script>

<script  language="JavaScript">

  rich_path = ''; //path to directory with editor files, here - current directory

  var is_local_file = false; //=true, if upload local file
  var is_init = false; //=true, if image load window initialization is performed

  var loaded = false; //=true if window is created

var parent_win = null; //window that opened this dialog
var active_rich = null; //current active RE instance

function return_image_parameters(){
//  src = form_name.preview_image.src;

//  if(src.search("images/space.gif") >= 0) return; //nothing chosen

  //upload a local file
  if(window.is_local_file){
    window.is_local_file = false;
    document.local_files.show_loading_div();
    document.local_files.local_files_form.submit();

    return;
  }

  if (!form_name.src.value) return;

	params = get_parameters();

  //add one more parameter - path to image
//  parameters[parameters.length] = "src="+src;

	if ('<?php echo $name; ?>' == 'dialog') { //image win opened by another win

		if (params) {
			var src = '';
			var width = 0;
			var height = 0;

			for (i=0;i<params.length;i++) {
				var param = params[i].match(/([^=]*)=(.*)/);
				switch (param[1]) {
					case 'width':
					case 'height':
						if (param[2]) eval(param[1]+"="+param[2]+";");
						break;
					case 'src':
						eval(param[1]+"='"+param[2]+"';");
						break;
					default:
						break;
				}

			}

			if (src && width && height) {
				var args = window.dialogArguments;
				var name = args&&args['name']?args['name']:'background';
				parent_win.set_preview_table_image(
							parent_win.document.getElementById(name+"_image"),
							src, width, height);
//				parent_win.set_preview_table_image(eval("parent_win.document.forms('form_name')."+name+"_image"),
//										src, width, height);
				eval("parent_win.document.forms('form_name')."+name+".checked = true;");
			}

		}
	} else {
		if (image_to_edit) edit_object(image_to_edit, params);
			else create_image(params)
	}

//  window.returnValue = parameters;

  window.close();
}

function on_temp_image_load(){
  if(!loaded) return;

  if(window.is_local_file){ //local image

    local_window = window.local_files.window;
  
    document.all.form_name.width.value = document.form_name.temp_image.width;
    document.all.form_name.height.value = document.form_name.temp_image.height;
  
    //set image preview
    set_preview_image(local_window.local_files_form.local_file.value,
                      document.form_name.temp_image.width,
                      document.form_name.temp_image.height);

  }else{ //remote image
    document.all.form_name.width.value = document.form_name.temp_image.width;
    document.all.form_name.height.value = document.form_name.temp_image.height;
  
    //set image preview
    set_preview_image(document.form_name.temp_image.getAttribute('src', 2),
                      document.form_name.temp_image.width,
                      document.form_name.temp_image.height);

  }

  return; //because init could fail (when edited image does not exist)

  if(window.is_init){
    document.all['width'].value = document.form_name.temp_image.width;
    document.all['height'].value = document.form_name.temp_image.height;

    set_preview_image(window.dialogArguments['src'],
                      document.form_name.temp_image.width,
                      document.form_name.temp_image.height);

    window.is_init = false;
  }
}

var image_to_edit = null;

//makes image preview when dialog window opens
function on_image_load(){
var args = window.dialogArguments;
	parent_win = args['parent_win'];
	active_rich = parent_win.active_rich;

var attribs = null; //parameters transmitted to the window

	if ('<?php echo $name; ?>' == 'dialog') { //image win opened by another win
		attribs = args;
	} else {

		var sel = active_rich.document.selection;
		var r = sel.createRange();

		if (sel.type == 'Control') var is_control = true;
			else var is_control = false;

		if (is_control && r.commonParentElement) { //image already exists
			var element = r.commonParentElement();
			if (element.tagName == 'IMG') {
				attribs = get_image(element); //get values of image attributes
				image_to_edit = element;
			}
		}

	}

	init_dialog(attribs);

	if(attribs && attribs['src']){
		if(attribs['width'] && attribs['height']){
			document.all['width'].value = attribs['width'];
			document.all['height'].value = attribs['height'];
  
			set_preview_image(attribs['src'], attribs['width'], attribs['height']);

		}else{ //neither width, nor height are defined - determine them
			window.is_init = true;
			document.form_name.temp_image.src = attribs['src'];
		}
	}

	if(!attribs) { //no parameters - creation mode
      document.form_name.border.value = '0';
	}

}

//parses remote files select
function parse_select_remote_file(url, width, height){
  window.is_local_file = false;

  form_name.src.value = url;
  form_name.temp_image.src = url;

  //when image 'temp_image' is loaded, event 'onload' arose
  //setting sizes of the image
}

function select_local_file(){
  window.is_local_file = true;

  form_name.temp_image.src = window.local_files.window.local_files_form.local_file.value;

  //when image 'temp_image' is loaded, event 'onload' arose
  //setting sizes of the image
}

//make preview of the image chosen with size not exceeding 100x100
function set_preview_image(url, w, h){
var preview_size;

  preview_size = 100;

  w = parseInt(w);
  h = parseInt(h);

  if(!w || !h) return;

  if(w > h){ //width > height
    document.form_name.preview_image.width = preview_size;
    document.form_name.preview_image.height = preview_size*h/w;
  }else{ //height > width
    document.form_name.preview_image.height = preview_size;
    document.form_name.preview_image.width = preview_size*w/h;
  }

  document.form_name.preview_image.src = url;
  document.form_name.preview_image.alt = url;

}

//creates image using array of parameters 'params'
function create_image(params){
var image;
var param;
var width;
var height;
var selection;
var range;
var i;

	image = document.createElement("IMG"); //create object - tag IMG

	//set values of the given attributes
	for(i=0;i<params.length;i++){
		param = params[i].match(/([^=]*)=(.*)/);

		if(param[1] != 'width' && param[1] != 'height'){
			if(param[2]) image.setAttribute(param[1], param[2]);
		}else{
			if(param[1] == 'width') width = param[2];
			if(param[1] == 'height') height = param[2];
		}

	}

	if(width) image.style.width = width;
	if(height) image.style.height = height;

	image.removeAttribute('width');
	image.removeAttribute('height');

	selection = active_rich.document.selection;
	range = selection.createRange();
	range.pasteHTML(image.outerHTML);

}

//get values of attributes of image 'image'
function get_image(image){
var attrib;
var width;
var height;
var abs_path;

	eval('abs_path = parent_win.'+active_rich.name+'_rich_absolute_path;');//1-absolute paths

	attrib = new Array();

	width = image.style.width;
	height = image.style.height;

	if(width) attrib['width'] = width;
	if(height) attrib['height'] = height;

	attrib['border'] = image.getAttribute('border');
	attrib['align'] = image.getAttribute('align');
	attrib['hspace'] = image.getAttribute('hspace');
	attrib['vspace'] = image.getAttribute('vspace');
	attrib['alt'] = image.getAttribute('alt');
	attrib['src'] = image.getAttribute('src');
	if(!abs_path) attrib['src'] = parent_win.del_base_url(attrib['src']);

	return attrib;
}

</script>

</head>

<body topmargin="0" leftmargin="0" rightmargin="0" class="re_dialog" onload="loaded = true; on_image_load();">

<form name="form_name">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%" class="re_dialog">

<tr>

<!-- window for remote files select  -->
<td width="100%" height="100%">

<?php
	if (isset($_GET['re_session_id'])) {
		$sess_params = '&re_session_id='.$_GET['re_session_id'];
	} else $sess_params = '';
?>
<iframe src="remote_files<?php echo $re_ext; ?>?initial_files_path=<?php echo $files_path; ?>&files_path=<?php echo $files_path; ?>&files_url=<?php echo $files_url; ?>&lang=<?php echo $lang; ?>&file_type=image<?php echo $sess_params; ?>" name="remote_files" id="remote_files_id" border="0" scrolling="yes" style="width:100%; height:100%"></iframe>

</td>
<!-- !window for remote files select -->

<!-- window of preview and attributes of image -->
<td height="100%" vAlign="top">

<!-- image for determining sizes of chosen image -->
<div style="position:absolute; top:0; left:0; visibility:hidden">
<img name="temp_image" src="images/space.gif" onload="on_temp_image_load();">
</div>
<!-- image for determining sizes of chosen image -->

<table border="0" cellspacing="0" cellpadding="0" height="100%">

<tr height="100%"><td border="1" height="100%" colspan="2" align="center" vAlign="center">
<table border="1" cellspacing="0" cellpadding="0" width="100%" height="100%"><tr><td align="center" vAlign="center">
<img name="preview_image" src="images/space.gif" border="0" width="1" height="1">
</td></tr></table>
</td></tr>

<!-- image attributes -->
<tr>
<td height="1"><?php echo $text['Align']; ?>:</td>
<td>
<select name="align" style="width:70">
  <option value=""></option>
<?php
	foreach ($align as $k=>$val) {
		echo '<option value="'.$k.'">'.$val.'</option>';
	}
?>
</select>
</td>
</tr>


<tr>
<td><?php echo $text['Width']; ?>:</td>
<td><input name="width" type="text" value="" size="4" maxlength="5" style="width:50"></td>
</tr>

<tr>
<td><?php echo $text['Height']; ?>:</td>
<td><input name="height" type="text" value="" size="4" maxlength="5" style="width:50"></td>
</tr>

<tr>
<td><?php echo $text['Border']; ?>:</td>
<td><input name="border" type="text" value="" size="4" maxlength="4" style="width:50"></td>
</tr>

<tr>
<td><?php echo $text['Vspace']; ?>:</td>
<td><input name="vspace" type="text" value="" size="4" maxlength="4" style="width:50"></td>
</tr>

<tr>
<td><?php echo $text['Hspace']; ?>:</td>
<td><input name="hspace" type="text" value="" size="4" maxlength="4" style="width:50"></td>
</tr>

<tr>
<td><?php echo $text['Alt']; ?>:</td>
<td><input name="alt" type="text" value="" size="4" maxlength="255" style="width:70;"></td>
</tr>
<!-- image attributes -->

</table>

</td>
<!-- !window of preview and attributes of image -->

</tr>

<!-- field for url -->
<tr>
<td colspan="2">

<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td width="1"><?php echo $text['Src']; ?>:</td>
<td width="100%">
<input name="src" type="text" value="" style="width:100%">
</td>
</tr>
</table>

</td>
</tr>
<!-- !field for url -->

<!-- window for local files select -->
<tr><td colspan="2" width="100%" height="27">

<iframe src="local_files<?php echo $re_ext; ?>?files_path=<?php echo $files_path; ?>&files_url=<?php echo $files_url; ?>&lang=<?php echo $lang; ?>&file_type=image<?php echo $sess_params; ?>" name="local_files" id="local_files_id" border="0" scrolling="no" style="width:100%; height:100%;"></iframe>

</td></tr>
<!-- !window for local files select -->

<tr><td colspan="2" height="1">
<center>
<input type="Button" value="<?php echo $rich_lang->item('Upload'); ?>" onclick="re_upload();" id="re_upload_button_id">
<input type="Button" value="<?php echo $rich_lang->item('Ok'); ?>" onclick="return_image_parameters();" id="re_ok_button_id">
<input type="Button" value="<?php echo $rich_lang->item('Cancel'); ?>" onclick="window.close();">
</center>
</td></tr>

</table>

</form>

<div id="re_loading_div_id" style="display:none;position:absolute;top:0px;left:0px;z-index:999">
<table border="1" bordercolor="#000000" bgColor="#0099ff" cellspacing="0" cellpadding="8" width="120px" height="50px">
<tr>
<td align="middle" vAlign="center"><font style="color:#ffffff;font-family:verdana,arial;font-size:13px"><?php echo $uploading; ?></font></td>
</td></tr>
</table>
</div>

</body>
</html>
