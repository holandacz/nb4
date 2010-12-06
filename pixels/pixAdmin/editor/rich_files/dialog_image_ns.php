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

<link rel="StyleSheet" type="text/css" href="rich_ns.css">

<script language="JavaScript" src="rich_dialog_ns.js"></script>

<script  language="JavaScript">

  rich_path = ''; //path to directory with editor files, here - current directory

  var is_local_file = false; //=true, if upload local file
  var is_init = false; //=true, if image load window initialization is performed

  var loaded = false; //=true if window is created

function return_image_parameters(){
var form_name = document.getElementById('form_name');

//  src = form_name.preview_image.src;

//  if(src.search("images/space.gif") >= 0 && !window.is_local_file) return; //nothing chosen

  //upload a local file
  if(window.is_local_file){
    window.is_local_file = false;
    document.getElementById('local_files').contentWindow.show_loading_div();
    document.getElementById('local_files').contentDocument.getElementById('local_files_form').submit();

    return;
  }

  if (!form_name.src.value) return;

  parameters = get_parameters();

  //add one more parameter - path to image
//  parameters[parameters.length] = "src="+src;

  if(opener){

    if('<?php echo $name; ?>' == 'dialog'){
    	opener.set_dialog_image('<?php if (isset($element_name)) echo $element_name; ?>', parameters);
    }else{
		if(parameters){
			var object = get_opener_object();
			if(object && object.tagName && object.tagName == 'IMG') edit_object(object, parameters);
				else create_image(parameters);
		 }
    }

  }

  window.close();
}

function on_temp_image_load(){
var form_name = document.getElementById('form_name');

//alert('loaded = '+loaded);
  if(!loaded) return;

  if(window.is_local_file){ //local image

    local_window = window.local_files.window;
  
    form_name.width.value = form_name.temp_image.width;
    form_name.height.value = form_name.temp_image.height;
  
    //set image preview
    set_preview_image(document.getElementById('local_files').contentDocument.getElementById('local_files_form').local_file.value,
                      form_name.temp_image.width,
                      form_name.temp_image.height);

  }else{ //remote image
    form_name.width.value = form_name.temp_image.width;
    form_name.height.value = form_name.temp_image.height;
  
    //set image preview
    set_preview_image(form_name.temp_image.getAttribute('src', 2),
                      form_name.temp_image.width,
                      form_name.temp_image.height);

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

//makes image preview when dialog window opens
function on_image_load(){
var obj = get_opener_object();

//	if(!obj) return;

var parameters = new Array();

//alert('||' + '<?php if (isset($element_name)) echo $element_name; ?>');
    if('<?php echo $name; ?>' == 'dialog' &&
	'<?php if (isset($element_name)) echo $element_name; ?>' != ''){
    	parameters['src'] = opener.document.getElementById('<?php if (isset($element_name)) echo $element_name; ?>_image').getAttribute('src', 2);
//    	parameters['src'] = opener.document.getElementById('form_name').<?php if (isset($element_name)) echo $element_name; ?>_image.getAttribute('src',2);
    }else{
		parameters = get_image(obj); //get values of image attributes
    }

//  if(!parameters) return;

  init_dialog(parameters);

var form_name = document.getElementById('form_name');

	if(!parameters) { //creation mode
		form_name.border.value = '0';
		return;
	}

  if(parameters['src']){
    if(parameters['width'] && parameters['height']){

      form_name.width.value = parameters['width'];
      form_name.height.value = parameters['height'];
  
      set_preview_image(parameters['src'],
                        parameters['width'],
                        parameters['height']);

    }else{ //neither width, nor height are defined - determine them
      window.is_init = true;
      form_name.temp_image.src = parameters['src'];
    }
  }

}

//parses remote files select
function parse_select_remote_file(url, width, height){
var form_name = document.getElementById('form_name');

  window.is_local_file = false;

  form_name.src.value = url;
  form_name.temp_image.src = url;

  //when image 'temp_image' is loaded, event 'onload' arose
  //setting sizes of the image
}

function select_local_file(){
var form_name = document.getElementById('form_name');

  window.is_local_file = true;

var obj = document.getElementById('local_files').contentDocument.getElementById('local_files_form').local_file;

  form_name.temp_image.src = 'file://'+document.getElementById('local_files').contentDocument.getElementById('local_files_form').local_file.value;

  //when image 'temp_image' is loaded, event 'onload' arose
  //setting sizes of the image
}

//make preview of the image chosen with size not exceeding 100x100
function set_preview_image(url, w, h){
var preview_size;
var form_name = document.getElementById('form_name');

  preview_size = 100;

  w = parseInt(w);
  h = parseInt(h);

  if(!w || !h) return;

  if(w > h){ //width > height
    form_name.preview_image.width = preview_size;
    form_name.preview_image.height = preview_size*h/w;
  }else{ //height > width
    form_name.preview_image.height = preview_size;
    form_name.preview_image.width = preview_size*w/h;
  }

  if(is_local_file) {
	form_name.preview_image.src = 'file://'+url;
  } else {
	form_name.preview_image.src = url;
	form_name.preview_image.title = url;
  }
}

//get values of attributes of image 'image'
function get_image(image){
var attrib;
var width;
var height;
var alt;
var abs_path;

  if(!image || !image.tagName || image.tagName != 'IMG') return null;

  eval('abs_path = opener.'+opener.active_rich.name+'_rich_absolute_path;');//1-absolute paths

  attrib = new Array();

  width = image.style.width;
  height = image.style.height;

  if(width) attrib['width'] = width;
  if(height) attrib['height'] = height;

  attrib['src'] = image.getAttribute('src');
  if(!abs_path) attrib['src'] = opener.del_base_url(attrib['src']);
  attrib['border'] = image.getAttribute('border');
  attrib['align'] = image.getAttribute('align');
  attrib['hspace'] = image.getAttribute('hspace');
  attrib['vspace'] = image.getAttribute('vspace');
  alt = image.getAttribute('alt');
  if(alt == '') alt = image.getAttribute('title');
  attrib['alt'] = alt;

  return attrib;
}

//creates image using array of parameters 'params'
function create_image(params){
var image;
var param;
var width;
var height;
var i;
var temp_rich;

	if(!params || !opener) return;

var parent = opener.active_rich;
var sel = parent.getSelection()
var range = sel.getRangeAt(0);

  image = document.createElement("IMG"); //create object - tag IMG

  //set values of the given attributes
  for(i=0;i<params.length;i++){
    param = params[i].match(/([^=]*)=(.*)/);

    if(param[1] != 'width' && param[1] != 'height'){
      if(param[2]){
		image.setAttribute(param[1], param[2]);
		if(param[1] == 'alt') image.setAttribute('title', param[2]);
	  }
    }else{
      if(param[1] == 'width') width = param[2];
      if(param[1] == 'height') height = param[2];
    }

  }

  if(width) image.style.width = width;
  if(height) image.style.height = height;

  image.removeAttribute('width');
  image.removeAttribute('height');

  var active_rich = parent;

  opener.paste_node(image);

}

</script>

</head>

<body class="re_dialog" topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0" onload="loaded = true; on_image_load();" onblur="window.focus();">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">

<form name="form_name" id="form_name">

<tr>

<!-- window for remote files select  -->
<td width="100%" height="100%">

<?php
	if (isset($_GET['re_session_id'])) {
		$sess_params = '&re_session_id='.$_GET['re_session_id'];
	} else $sess_params = '';
?>
<iframe width="99%" height="360" src="remote_files_ns<?php echo $re_ext; ?>?initial_files_path=<?php echo $files_path; ?>&files_path=<?php echo $files_path; ?>&files_url=<?php echo $files_url; ?>&lang=<?php echo $lang; ?>&file_type=image<?php echo $sess_params; ?>" name="remote_files" id="remote_files" border="0" scrolling="yes" style="width:99%; height:360"></iframe>

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
<td colspan="2" width="100%">

<table border="0" cellspacing="0" cellpadding="0" height="100%">
<tr>
<td><?php echo $text['Src']; ?>:</td>
<td width="100%">
<input name="src" type="text" value="" maxlength="255" style="width:100%;">
</td>
</tr>
</table>

</td>
</tr>
<!-- !field for url -->

<!-- window for local files select -->
<tr><td colspan="2" width="100%" height="27">

<iframe src="local_files_ns<?php echo $re_ext; ?>?files_path=<?php echo $files_path; ?>&files_url=<?php echo $files_url; ?>&lang=<?php echo $lang; ?>&file_type=image<?php echo $sess_params; ?>" name="local_files" id="local_files" border="0" scrolling="no" style="width:100%; height:22;"></iframe>

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
