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

<link rel="StyleSheet" type="text/css" href="rich_ns.css">

<script language="JavaScript" src="rich_dialog_ns.js?f"></script>

<script  language="JavaScript">

  rich_path = ''; //path to directory with editor files, here - current directory

  var is_local_file = false; //=true, if local file is uploaded

function return_link_parameters(){

	if(window.is_local_file){
		window.is_local_file = false;
	    document.getElementById('local_files').contentWindow.show_loading_div();
	    document.getElementById('local_files').contentDocument.getElementById('local_files_form').submit();

		return;
	}

	parameters = get_parameters();

	if(parameters){

	var link = get_link_obj();
		if(link) edit_object(link, parameters); //edit link
			else if (!create_link(parameters)) return;
	 }

//  window.returnValue = parameters;

	window.close();
}

//parses remote file select
function parse_select_remote_file(url, width, height){
var form_name = document.getElementById('form_name');

  window.is_local_file = false;

  form_name.href.value = url;

}

//parses local file select
function select_local_file(){
  window.is_local_file = true;
}

function init_link_dialog(){
var parameters = get_link(get_link_obj()); //get values of image attributes

  init_dialog(parameters);

}

function get_link_obj(){
var sel = opener.active_rich.getSelection()
var range = sel.getRangeAt(0);
var container = range.startContainer;
var link = null;
var object;

	if(container.parentNode){ //check, if text is inside a reference
		link = get_previous_object(container.parentNode,'A');
      }

	object = get_opener_object();
	if(object) link = get_previous_object(object,'A');

	return link;
}

//create reference using array of parameters 'params'
function create_link(params){
var i;
var param;
var selection;
var range;
var object;
var inner_object;
var inner_text;
var link_text;
var link = Array();

	if(!params || !opener) return;

var parent = opener.active_rich;
var sel = parent.getSelection()
var range = sel.getRangeAt(0);
var container = range.startContainer;
var container_type = container.nodeType;

//	link = document.createElement("A"); //create object - tag A

	//set values of the attributes given
	for(i=0;i<params.length;i++){
		param = params[i].match(/([^=]*)=(.*)/);

//		if(param[1] == 'href' && !param[2]) return; //empty reference
//			else if(param[2]) link[param[1]] = param[2];
		if(param[2]) link[param[1]] = param[2];

	}

	//neither reference nor anchor name are set
	if (!link['href']) return false;

var rich_link = 'http://rich_link'; //temp link
opener.active_rich.document.execCommand("CreateLink", false, rich_link);

//fix temporary links as Mozilla-based browsers could create multiple links
//in result of CreateLink command (if selection is not plain text but html)
var links = opener.active_rich.document.getElementsByTagName('A');
var link_len = links.length;
	for (i=0; i<link_len; i++) {
		if (links[i].getAttribute('href') == rich_link) {
			links[i].setAttribute('href', link['href']);
			if (link['title']) links[i].setAttribute('title', link['title']);
			if (link['name']) links[i].setAttribute('name', link['name']);
			if (link['target']) links[i].setAttribute('target', link['target']);
		}
	}

	return true;
}

//gets values of attributes of reference 'link'
function get_link(link){
	var attrib = new Array();
	var abs_path;

	if(!opener || !link) return null;

	eval('abs_path = opener.'+opener.active_rich.name+'_rich_absolute_path;');//1-absolute paths

	attrib['target'] = link.getAttribute('target');
	attrib['name'] = link.getAttribute('name');
	attrib['title'] = link.getAttribute('title');
	attrib['href'] = link.getAttribute('href', 2);
	if(!abs_path && attrib['href']) attrib['href'] = opener.del_base_url(attrib['href']);

	return attrib;
}

</script>

</head>

<body topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0" class="re_dialog" onload="init_link_dialog();">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%" class="re_dialog">

<form name="form_name" id="form_name" onsubmit="return_link_parameters(); return false;">

<tr>

<!-- window of remote files select -->
<td colspan="4" width="100%" height="320">

<?php
	if (isset($_GET['re_session_id'])) {
		$sess_params = '&re_session_id='.$_GET['re_session_id'];
	} else $sess_params = '';
?>
<iframe width="99%" height="320" src="remote_files_ns<?php echo $re_ext; ?>?initial_files_path=<?php echo $files_path; ?>&files_path=<?php echo $files_path; ?>&files_url=<?php echo $files_url; ?>&lang=<?php echo $lang; ?>&file_type=link<?php echo $sess_params; ?>" name="remote_files" id="remote_files" border="0" scrolling="yes" style="width:99%; height:320"></iframe>

</td>
<!-- !window of remote files select -->

</tr>

<!-- window of local files select -->
<tr><td colspan="4" width="100%" height="27">

<iframe src="local_files_ns<?php echo $re_ext; ?>?files_path=<?php echo $files_path; ?>&files_url=<?php echo $files_url; ?>&lang=<?php echo $lang; ?>&file_type=link<?php echo $sess_params; ?>" name="local_files" id="local_files" border="0" scrolling="no" style="width:100%; height:22;"></iframe>

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
