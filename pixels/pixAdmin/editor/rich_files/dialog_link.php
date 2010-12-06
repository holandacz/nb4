<?php

//language class
require_once('lang/class.rich_lang.php');

	//extract variables submitted to this page
	@extract($_GET);

	$rich_lang = new rich_lang($lang); //text data in current language
	$text = $rich_lang->item('Link');
	$uploading = $rich_lang->item('Uploading');

?><html>
<head>
<title><?php echo $text['Title']; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $rich_lang->item("Charset"); ?>">

<link rel="StyleSheet" type="text/css" href="rich.css">

<script language="JavaScript" src="rich_dialog.js?yyyyyyyy"></script>

<script  language="JavaScript">

  rich_path = ''; //path to directory with editor files, here - current directory

  var is_local_file = false; //=true, if local file is uploaded

var parent_win = null; //window that opened this dialog
var active_rich = null; //current active RE instance

var link_to_edit = null;

function init_link_dialog(){
var args = window.dialogArguments;
	parent_win = args['parent_win'];
	active_rich = parent_win.active_rich;

var sel = active_rich.document.selection;
var r = sel.createRange();

	if (sel.type == 'Control') var is_control = true;
		else var is_control = false;

var attribs = null;

	if (r.parentElement) { //check, if text is inside a reference
		link_to_edit = get_previous_object(r.parentElement(),'A');
	} else {
		if (r.commonParentElement) { //check, if control is inside a reference
			link_to_edit = parent_win.get_previous_object(r.commonParentElement(), 'A');
		}
	}

	if (link_to_edit) attribs = get_link(link_to_edit); //get values of link attributes

	init_dialog(attribs);
}

function return_link_parameters(){

	if (window.is_local_file) {
		window.is_local_file = false;
	    document.local_files.show_loading_div();
		document.local_files.local_files_form.submit();

		return;
	}

	parameters = get_parameters();

	if(link_to_edit) edit_object(link_to_edit, parameters);
		else if (!create_link(parameters)) return;

//  window.returnValue = parameters;

	window.close();
}

//parses remote file select
function parse_select_remote_file(url, width, height){

  window.is_local_file = false;

  document.form_name.href.value = url;

}

//parses local file select
function select_local_file(){
  window.is_local_file = true;
}

//create reference using array of parameters 'params'
function create_link(params){
var i;
var param;
var selection;
var range;
var object;
var link = Array();

	link = document.createElement("A"); //create object - tag A

	//set values of the attributes given
	for(i=0;i<params.length;i++){
		param = params[i].match(/([^=]*)=(.*)/);

		link[param[1]] = param[2];

	}

	//neither reference nor anchor name are set
	if (!link['href']) return false;

	selection = active_rich.document.selection;
	range = selection.createRange();

	range.execCommand("CreateLink", false, link['href']);

	if(selection.type == 'Control'){//get html text of the control
		object = range(0).parentNode;
	}else{//get html text of the selection
		object = range.parentElement();
	}

	if (object) {
		if(!link['href']) object.removeAttribute('href');
		if(link.target) object.target = link['target'];
		if(link.name) object.name = link['name'];
		if(link.title) object.title = link['title'];

		return true;
	}
	return false;
}

//gets values of attributes of reference 'link'
function get_link(link){
	var attrib = new Array();
	var abs_path;

	eval('abs_path = parent_win.'+active_rich.name+'_rich_absolute_path;');//1-absolute paths

	attrib['target'] = link.getAttribute('target');
	attrib['name'] = link.getAttribute('name');
	attrib['title'] = link.getAttribute('title');
	attrib['href'] = link.getAttribute('href', 2);

	//fix link attributes
	var loc = String(parent_win.document.location).replace('?', '\\?');
	loc = loc.split('#');
	var re = new RegExp(loc[0]+'#', 'gi');
	RegExp.multiline = true;
	attrib['href'] = String(attrib['href']).replace(re, '#');

	if(!abs_path) attrib['href'] = parent_win.del_base_url(attrib['href']);

	return attrib;
}

</script>

</head>

<body topmargin="0" leftmargin="0" rightmargin="0" class="re_dialog" onload="init_link_dialog();">

<form name="form_name" onsubmit="return_link_parameters(); return false;">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%" class="re_dialog">

<tr>

<!-- window of remote files select -->
<td colspan="4" width="100%" height="100%">

<?php
	if (isset($_GET['re_session_id'])) {
		$sess_params = '&re_session_id='.$_GET['re_session_id'];
	} else $sess_params = '';
?>
<iframe src="remote_files<?php echo $re_ext; ?>?initial_files_path=<?php echo $files_path; ?>&files_path=<?php echo $files_path; ?>&files_url=<?php echo $files_url; ?>&lang=<?php echo $lang; ?>&file_type=link<?php echo $sess_params; ?>" name="remote_files" id="remote_files_id" border="0" scrolling="yes" style="width:100%; height:100%"></iframe>

</td>
<!-- !window of remote files select -->

</tr>

<!-- window of local files select -->
<tr><td colspan="4" width="100%" height="27">

<iframe src="local_files<?php echo $re_ext; ?>?files_path=<?php echo $files_path; ?>&files_url=<?php echo $files_url; ?>&lang=<?php echo $lang; ?>&file_type=link<?php echo $sess_params; ?>" name="local_files" id="local_files_id" border="0" scrolling="no" style="width:100%; height:100%;"></iframe>

</td></tr>
<!-- !window of local files select -->

<!-- reference attribute -->
<tr>

<td width="1"><?php echo $text['Target']; ?>:</td>
<td>
<select name="target">
  <option value=""></option>
<?php
	foreach ($text['Targets'] as $k=>$val) {
		echo '<option value="'.$k.'">'.$val.'</option>';
	}
?>
</select>
</td>

<td width="1"><?php echo $text['Name']; ?>:</td>
<td width="100%"><input name="name" type="text" value="" style="width:100%;"></td>

</tr>

<tr>
<td width="1"><?php echo $text['Hint']; ?>:</td>
<td width="100%" colspan="3"><input name="title" type="text" value="" style="width:100%;"></td>
</tr>

<tr>
<td width="1"><?php echo $text['Link']; ?>:</td>
<td width="100%" colspan="3"><input name="href" type="text" value="" style="width:100%;"></td>
</tr>
<!-- !reference attribute -->

<tr><td colspan="4" height="1">
<center>
<input type="Button" value="<?php echo $rich_lang->item('Upload'); ?>" onclick="re_upload();" id="re_upload_button_id">
<input type="Button" value="<?php echo $rich_lang->item('Ok'); ?>" onclick="return_link_parameters();" id="re_ok_button_id">
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
