<?php

//language class
require_once('lang/class.rich_lang.php');

	//extract variables submitted to this page
	@extract($_GET);


	$rich_lang = new rich_lang($lang); //text data in current language

	$text = $rich_lang->item('Table');
	$cell_text = $rich_lang->item('TableCell');
	$align = $rich_lang->item('TextAlign');
	$valign = $rich_lang->item('vAlign');

	if (!isset($browser)) $browser = '';

	if ($browser == 'ns') $postfix = '_ns';
		else $postfix = '';

	if (!isset($tab) || $tab == '') $tab = 'table';

?><html><head>
<title><?php echo $text['Title']; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $rich_lang->item("Charset"); ?>">

<link rel="StyleSheet" type="text/css" href="rich<?php echo $postfix; ?>.css">

<script language="JavaScript" src="rich_dialog<?php echo $postfix; ?>.js?eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee"></script>

<script  language="JavaScript">

var rich_path = ''; //path to directory with editor files, here - current directory

var is_init = false; //=true, if window is initialized

var obj;

var rich_dialog_ext = '<?php echo $re_ext; ?>';

var rich_sess_param = '<?php if (isset($re_session_id)) echo 're_session_id='.$re_session_id; ?>';

var parent_win = null; //window that opened this dialog
var active_rich = null; //current active RE instance

<?php
  if(isset($files_path)) echo "var dialog_files_path = '$files_path';\n";
  if(isset($files_url)) echo "var dialog_files_url = '$files_url';\n";
  if(isset($lang)) echo "var dialog_lang = '$lang';\n";

	echo "var do_create = ";
	if (isset($do_create) && $do_create) echo "true";
		else echo "false";
	echo ";\n";
?>


/*
	COMMON FUNCTIONS
*/

//init all tabs
function init_table_tabs_dialog(){

var forms = document.getElementsByTagName('FORM');

	for (var i=0; i<forms.length; i++) {
		var el_name = String(forms[i].name).replace('_form_name', '');

		if (do_create && el_name != 'table') continue;

		//call init function for the element
		forms[i].id = 'form_name';
		eval('init_'+el_name+'_dialog()');
		forms[i].id = forms[i].name;
	}

	show_tab("<?php echo $tab; ?>");

	update_preview();

}

//update all tabs objects
function return_table_tabs_parameters(){

var forms = document.getElementsByTagName('FORM');

	//first unset current form
	var cur_form = document.getElementById('form_name');
	if (cur_form) cur_form.id = cur_form.name;

	for (var i=0; i<forms.length; i++) {
		var el_name = String(forms[i].name).replace('_form_name', '');

		if (do_create && el_name != 'table') continue;

		//call init function for the element
		forms[i].id = 'form_name';
		eval('return_'+el_name+'_parameters()');
		forms[i].id = forms[i].name;
	}

	if (cur_form) cur_form.id = 'form_name';

}


function on_temp_image_load(temp_image){

  if(window.is_init){

	var background_image_id = String(temp_image.id).replace('_background_temp_image', '');

	background_image_id += '_background_image';
    set_preview_table_image(document.getElementById(background_image_id),
                            temp_image.getAttribute('src', 2),
                            temp_image.width,
                            temp_image.height);

//    window.is_init = false;

	update_preview();
  }
}


//gets values of table cell 'object'
function get_cell(object){
var attrib = new Array();

	attrib['width'] = object.getAttribute('width');
	attrib['height'] = object.getAttribute('height');
	attrib['align'] = object.getAttribute('align');
	attrib['vAlign'] = object.getAttribute('vAlign');
	attrib['bgColor'] = object.getAttribute('bgColor');
	attrib['background'] = object.getAttribute('background');
	attrib['noWrap'] = object.getAttribute('noWrap');
	attrib['borderColor'] = object.getAttribute('borderColor');
	attrib['borderColorLight'] = object.getAttribute('borderColorLight');
	attrib['borderColorDark'] = object.getAttribute('borderColorDark');
	return attrib;
}


//redraw table in preview
function update_preview(){
var preview_iframe = document.getElementById('preview_iframe').contentWindow;



var forms = document.getElementsByTagName('FORM');
var form_params = new Array();

	//first unset current form
	var cur_form = document.getElementById('form_name');
	if (cur_form) cur_form.id = cur_form.name;

	for (var i=0; i<forms.length; i++) {
		var el_name = String(forms[i].name).replace('_form_name', '');

		//call init function for the element
		forms[i].id = 'form_name';
		form_params[el_name] = get_parameters();
		forms[i].id = forms[i].name;
	}

	if (cur_form) cur_form.id = 'form_name';


var style_text = '';

	for (var i in form_params) {
		var params = form_params[i];

		for (j=0; j<params.length; j++){
			param = params[j].match(/([^=]*)=([\s|\S|\n]*)/);

			switch (param[1]) {
				case 'background':
				case 'bgColor':
				case 'align':
				case 'vAlign':
				case 'cellPadding':
				case 'cellSpacing':
				case 'border':
				case 'borderColor':
				case 'borderColorLight':
				case 'borderColorDark':
					if (param[2]) eval('var '+i+'_'+param[1]+' = \''+' '+param[1]+'="'+param[2]+'"\'');
						else eval('var '+i+'_'+param[1]+' = "";');
					break;
				default:
					break;
			}


			if (i == 'table' && param[2]) {
				if (param[1] == 'textAlign') style_text += 'text-align:'+param[2]+';';
				if (param[1] == 'borderCollapse') style_text += 'border-collapse:collapse;';
			}

		}

	}


	if (style_text != '') style_text = ' style="'+style_text+'"';


var text2set =
'<table width="70%"'+style_text+table_background+table_bgColor+table_align+table_cellPadding+table_cellSpacing+table_border+table_borderColor+table_borderColorLight+table_borderColorDark+'>'+
'<tr'+tr_align+tr_vAlign+tr_background+tr_bgColor+tr_borderColor+tr_borderColorLight+tr_borderColorDark+'>'+
'	<td width="33%"><?php echo $text['CurrentRow']; ?>:</td>'+
'	<td'+td_align+td_vAlign+td_background+td_bgColor+td_borderColor+td_borderColorLight+td_borderColorDark+'><?php echo $text['CurrentCell']; ?>:</td>'+
'	<td width="33%" height="40">&nbsp;</td>'+
'</tr>'+
'<tr height="10">'+
'	<td><img src="images/space.gif" width="1" height="1"></td>'+
'	<td><img src="images/space.gif" width="1" height="1"></td>'+
'	<td><img src="images/space.gif" width="1" height="1"></td>'+
'</tr>'+
'</table>'+
'<font style="font-size:10px"><?php echo $text['PreviewText']; ?>'+
'</font>';

	preview_iframe.document.open();
	preview_iframe.document.write(text2set);
	preview_iframe.document.close();


}


/*
	!COMMON FUNCTIONS
*/


/*
	===========================================================================
	MOZILLA
	===========================================================================
*/


<?php if ($browser == 'ns') : ?>


/*
	CELL FUNCTIONS
*/

function init_td_dialog(){
var obj = get_opener_object();

	if(!obj){
		var sel = opener.active_rich.getSelection()
		var r = sel.getRangeAt(0);
		var container = r.startContainer;
		var container_type = container.nodeType;
        obj = get_previous_object(container.parentNode,'TD');
	}

var parameters = get_cell(obj); //get values of table attributes
	init_dialog(parameters, true);
}

function return_td_parameters(){
var form_name = document.getElementById('form_name');

  parameters = get_parameters();

  if(parameters && opener){
	obj = get_opener_object();

	if(!obj){
		var sel = opener.active_rich.getSelection()
		var r = sel.getRangeAt(0);
		var container = r.startContainer;
		var container_type = container.nodeType;
        obj = get_previous_object(container.parentNode,'TD');
	}

	if(obj) edit_object(obj, parameters);
  }

}

/*
	!CELL FUNCTIONS
*/


/*
	ROW FUNCTIONS
*/

function init_tr_dialog(){
var obj = get_opener_object();

	if(!obj){
		var sel = opener.active_rich.getSelection()
		var r = sel.getRangeAt(0);
		var container = r.startContainer;
		var container_type = container.nodeType;
        obj = get_previous_object(container.parentNode,'TR');
	}

//get values of row attributes (all them we can find among cell properties)
var parameters = get_cell(obj);
	init_dialog(parameters, true);
}

function return_tr_parameters(){
var form_name = document.getElementById('form_name');

  parameters = get_parameters();

  if(parameters && opener){
	obj = get_opener_object();

	if(!obj){
		var sel = opener.active_rich.getSelection()
		var r = sel.getRangeAt(0);
		var container = r.startContainer;
		var container_type = container.nodeType;
        obj = get_previous_object(container.parentNode,'TR');
	}

	if(obj) edit_object(obj, parameters);
  }

}

/*
	!ROW FUNCTIONS
*/


/*
	TABLE FUNCTIONS
*/

function init_table_dialog(){
var obj = get_opener_object();

	if(!obj || !obj.tagName || obj.tagName != 'TABLE'){
		obj = null;
		var sel = opener.active_rich.getSelection();
		var r = sel.getRangeAt(0);
		var container = r.startContainer;
		var container_type = container.nodeType;
        if (!do_create) obj = get_previous_object(container.parentNode,'TABLE');
	}

var parameters = get_table(obj); //get values of table attributes
var form_name = document.getElementById('form_name');
var editing_mode = init_dialog(parameters, true);

  if(!editing_mode){//initializing
    form_name.rows.value = 2;
    form_name.columns.value = 2;
    form_name.border.value = 1;
  }

}

function return_table_parameters(){
var form_name = document.getElementById('form_name');

  parameters = get_parameters();

  if(parameters && opener){
	var obj = get_opener_object();

	if(!obj || !obj.tagName || obj.tagName != 'TABLE'){
		obj = null;
		var sel = opener.active_rich.getSelection();
		var r = sel.getRangeAt(0);
		var container = r.startContainer;
		var container_type = container.nodeType;
        if (!do_create) obj = get_previous_object(container.parentNode,'TABLE');
	}

	if(obj) edit_object(obj, parameters);
		else create_table(parameters);

  }

}

function set_dialog_image(name, params){
var src;
var width;
var height;

  if(params){
    src = '';
    width = 0;
    height = 0;

    for(i=0;i<params.length;i++){
      param = params[i].match(/([^=]*)=(.*)/);
      switch(param[1]){
        case 'width':
        case 'height':
          if(param[2]) eval(param[1]+"="+param[2]+";");
          break;
        case 'src':
          eval(param[1]+"='"+param[2]+"';");
          break;
        default:
          break;
      }
  
    }

    if(src && width && height){
      set_preview_table_image(document.getElementById(name+"_image"),
                              src, width, height);

		update_preview();
    }
  }

}

//get values of attributes of table 'table'
function get_table(table){
var abs_path;

  if(!table) return null;

  eval('abs_path = opener.'+opener.active_rich.name+'_rich_absolute_path;');//1-absolute paths

  var attrib;
  attrib = new Array();

	if (table.rows.item(0)) {
		var obj = table.rows.item(0).childNodes;
		var cols = 0;

		for (var j=0;j<obj.length;j++) {
			if (obj[j].tagName == 'TD') cols++;
		}

		var rows = table.rows.length;
	} else {

		var rows = null;
		var obj = table.firstChild;
		if (!obj) return null;
    
		if (obj.tagName != 'TR') {
			rows = obj.childNodes.length;
			obj = obj.firstChild;
			if (!obj || obj.tagName != 'TR') return null;
		} else {
			rows = table.childNodes.length;
		}
    
		var cols = 0;
		for (var j=0;j<obj.childNodes.length;j++) {
			if (obj.childNodes[j].tagName == 'TD') cols++;
		}

	}	

  attrib['rows'] = rows;
//  attrib['rows'] = table.childNodes.length;
//  attrib['columns'] = table.rows[0].cells.length;
  attrib['columns'] = cols;
  attrib['width'] = table.style.width;
  attrib['height'] = table.style.height;
  attrib['border'] = table.getAttribute('border');
  attrib['align'] = table.getAttribute('align');
  attrib['cellSpacing'] = table.getAttribute('cellSpacing');
  attrib['cellPadding'] = table.getAttribute('cellPadding');
  attrib['background'] = table.getAttribute('background');
  if(attrib['background'] && !abs_path) attrib['background'] = opener.del_base_url(attrib['background']);
  attrib['bgColor'] = table.getAttribute('bgColor');
  attrib['borderColor'] = table.getAttribute('borderColor');
  attrib['borderColorLight'] = table.getAttribute('borderColorLight');
  attrib['borderColorDark'] = table.getAttribute('borderColorDark');

  attrib['textAlign'] = table.style.textAlign;
  attrib['borderCollapse'] = table.style.borderCollapse;

  return attrib;
}

//creates table using parameter array params
function create_table(params){
var table;
var param;
var rows;
var columns;
var width;
var height;
var cell;
var row;
var i;
var border_mode;
var text_align;
var border_collapse;

	if(!params || !opener) return;

var parent = opener.active_rich;
var selection = parent.getSelection()
var range = selection.getRangeAt(0);

  table = document.createElement("TABLE"); //create object - tag table

  //set values of given attributes
  for(i=0;i<params.length;i++){
    param = params[i].match(/([^=]*)=(.*)/);
    if(param[1] != 'rows' && param[1] != 'columns' &&
       param[1] != 'width' && param[1] != 'height' &&
		param[1] != 'textAlign' && param[1] != 'borderCollapse'){
      if(param[2]) table.setAttribute(param[1], param[2]);
    }else{
      if(param[1] == 'rows') rows = param[2];
      if(param[1] == 'columns') columns = param[2];
      if(param[1] == 'width') width = param[2];
      if(param[1] == 'height') height = param[2];

      if(param[1] == 'textAlign')  text_align= param[2];
      if(param[1] == 'borderCollapse') border_collapse = param[2];
    }
  }

  if( !(rows > 0 && columns > 0) ) return;

  if(width) table.style.width = width;
  if(height) table.style.height = height;

  table.removeAttribute('width');
  table.removeAttribute('height');

	if (text_align) table.style.textAlign = text_align;
	if (border_collapse) table.style.borderCollapse = border_collapse;

  var cell_width = 100/columns+'%';

  //create necessary quantity of rows and columns
  for(i=0;i<rows;i++){
    row = document.createElement("TR");
    for(j=0;j<columns;j++){
      cell = document.createElement("TD");
      row.appendChild(cell);
      cell.innerHTML = '&nbsp;';
//      cell.setAttribute("width", cell_width);
    }
    table.appendChild(row);
  }

  var active_rich = parent;

  opener.paste_node(table);

  //current border mode
  eval('border_mode = active_rich.window.parent.'+active_rich.name+'_rich_border_mode;');
  //make table borders visible if necessary
  opener.set_borders(border_mode);
  opener.set_borders(border_mode); //first time return for tables rows' lengths = 0

}


//do this when closing editing window to make borderCollapse change visible
//problem is when we change border with borderCollapse set the changes are not
//seen in Firefox 1.5.0.1
function re_restore_borders(){

  var active_rich = parent;

  eval('border_mode = active_rich.window.parent.'+active_rich.name+'_rich_border_mode;');
  opener.set_borders(border_mode);
  opener.set_borders(border_mode);

}

/*
	!TABLE FUNCTIONS
*/


/*
	===========================================================================
	MOZILLA
	===========================================================================
*/


<?php else : ?>


/*
	===========================================================================
	IE
	===========================================================================
*/


/*
	TABLE FUNCTIONS
*/

var table_to_edit = null;

function init_table_dialog(){
var args = window.dialogArguments;
	parent_win = args['parent_win'];
	active_rich = parent_win.active_rich;

var sel = active_rich.document.selection;
var r = sel.createRange();

	if (sel.type == 'Control') var is_control = true;
		else var is_control = false;

var attribs = null;

	if (is_control && r.commonParentElement) { //table already exists
		var element = r.commonParentElement();
	} else {
		if (!do_create && r.parentElement) { //find table to edit
			var element = get_previous_object(r.parentElement(), 'TABLE');
		}
	}

	if (element && element.tagName == 'TABLE') {
		attribs = get_table(element); //get values of table attributes
		table_to_edit = element;
	}

	var editing_mode = init_dialog(attribs);

	if(!editing_mode){//initializing
		document.form_name.rows.value = 2;
		document.form_name.columns.value = 2;
		document.form_name.border.value = 1;
	}

}

function return_table_parameters(){
//window.returnValue=get_parameters();

	var parameters = get_parameters();

	if(table_to_edit) edit_object(table_to_edit, parameters);
		else create_table(parameters);

}

//get values of attributes of table 'table'
function get_table(table){
var attrib;
	attrib = new Array();

	var abs_path;

	eval('abs_path = parent_win.'+active_rich.name+'_rich_absolute_path;');//1-absolute paths

	attrib['rows'] = table.rows.length;
	attrib['columns'] = table.rows(0).cells.length;
	attrib['width'] = table.style.width;
	if (!attrib['width']) attrib['width'] = table.width;
	attrib['height'] = table.style.height;
	if (!attrib['height']) attrib['height'] = table.height;
	attrib['border'] = table.getAttribute('border');
	attrib['align'] = table.getAttribute('align');
	attrib['cellSpacing'] = table.getAttribute('cellSpacing');
	attrib['cellPadding'] = table.getAttribute('cellPadding');
	attrib['bgColor'] = table.getAttribute('bgColor');
	attrib['borderColor'] = table.getAttribute('borderColor');
	attrib['borderColorLight'] = table.getAttribute('borderColorLight');
	attrib['borderColorDark'] = table.getAttribute('borderColorDark');
	attrib['background'] = table.getAttribute('background', 2);
	if(!abs_path) attrib['background'] = parent_win.del_base_url(attrib['background']);

	attrib['textAlign'] = table.style.textAlign;
	attrib['borderCollapse'] = table.style.borderCollapse;

	return attrib;
}

//creates table using parameter array params
function create_table(params){
var table;
var param;
var rows;
var columns;
var width;
var height;
var cell;
var row;
var selection;
var range;
var i;
var text_align;
var border_collapse;

	table = document.createElement("TABLE"); //create object - tag table

	//set values of given attributes
	for(i=0;i<params.length;i++){
		param = params[i].match(/([^=]*)=(.*)/);
		if(param[1] != 'rows' && param[1] != 'columns' &&
			param[1] != 'width' && param[1] != 'height' &&
			param[1] != 'textAlign' && param[1] != 'borderCollapse'){
			if(param[2]) table.setAttribute(param[1], param[2]);
		}else{
			if(param[1] == 'rows') rows = param[2];
			if(param[1] == 'columns') columns = param[2];
			if(param[1] == 'width') width = param[2];
			if(param[1] == 'height') height = param[2];

	      if(param[1] == 'textAlign')  text_align= param[2];
	      if(param[1] == 'borderCollapse') border_collapse = param[2];
		}
	}

	if( !(rows > 0 && columns > 0) ) return;

	if(width) table.style.width = width;
	if(height) table.style.height = height;

	table.removeAttribute('width');
	table.removeAttribute('height');

	if (text_align) table.style.textAlign = text_align;
	if (border_collapse) table.style.borderCollapse = border_collapse;


	//current border mode
	eval('var border_mode = parent_win.'+active_rich.name+'_rich_border_mode;');

//	if (border_mode) table.style.border = '1px dashed #CCCCCC';

	//create necessary quantity of rows and columns
	for(i=0;i<rows;i++){
		row = document.createElement("TR");
		for(j=0;j<columns;j++){
			cell = document.createElement("TD");
//			if (border_mode) cell.style.border = '1px dashed #CCCCCC';
			row.appendChild(cell);
		}
		table.appendChild(row);
	}

	selection = active_rich.document.selection;
	range = selection.createRange();
	range.pasteHTML(table.outerHTML);

	if (border_mode) parent_win.set_borders(true);
}

/*
	!TABLE FUNCTIONS
*/


/*
	ROW FUNCTIONS
*/

var row_to_edit = null;

function init_tr_dialog(){
var args = window.dialogArguments;
	parent_win = args['parent_win'];
	active_rich = parent_win.active_rich;

var sel = active_rich.document.selection;
var r = sel.createRange();

var attribs = null;

	if(r.parentElement) {
		row_to_edit = get_previous_object(r.parentElement(), 'TR');
//get values of row attributes (all them we can find among cell properties)
		if (row_to_edit) attribs = get_cell(row_to_edit);
	}

	init_dialog(attribs);

}

function return_tr_parameters(){

	if (row_to_edit) edit_object(row_to_edit, get_parameters());
}

/*
	!ROW FUNCTIONS
*/


/*
	CELL FUNCTIONS
*/

var cell_to_edit = null;

function init_td_dialog(){
var args = window.dialogArguments;
	parent_win = args['parent_win'];
	active_rich = parent_win.active_rich;

var sel = active_rich.document.selection;
var r = sel.createRange();

	if (sel.type == 'Control') var is_control = true;
		else var is_control = false;

var attribs = null;

	if(r.parentElement) {
		cell_to_edit = get_previous_object(r.parentElement(), 'TD');
		if (cell_to_edit) attribs = get_cell(cell_to_edit); //get values of table attributes
	} else {
	if(r.commonParentElement) element = r.commonParentElement();
		cell_to_edit = get_previous_object(r.commonParentElement(), 'TD');

	}

//alert('TD');
	init_dialog(attribs);

}

function return_td_parameters(){
//	window.returnValue=get_parameters();

	if (cell_to_edit) edit_object(cell_to_edit, get_parameters());
}

/*
	!CELL FUNCTIONS
*/


/*
	===========================================================================
	IE
	===========================================================================
*/


<?php endif; ?>


//=========================
var active_tab_name = null;
//=========================


<?php

	$tabs = array(
'table'	=> $text['Table'],
'tr'	=> $text['Row'],
'td'	=> $text['Cell']
	);

?>

var tab_names = new Array(<?php

	$flag = false;
	foreach ($tabs as $k=>$val) {
		if (!$flag) $flag = true;
			else echo ', ';
		echo '"'.$k.'"';
	}
?>);


var tab_show_flags = Array(); //to resize tabs only once


var elements = new Array(
	'top1', 'top2', 'top3', 'top4', 'left1', 'right1', 'right2',
	'bottom1', 'bottom2', 'bottom3', 'bottom4'
);

var tab_class = new Array();
	tab_class[-1] = Array(
'', 're_tab_border', 're_tab_border', '', 're_tab_border', '', '',
're_tab_border', '', '', ''
	);

	tab_class[0] = Array(
'', '', 're_tab_border', 're_tab_right2', 're_tab_border', 're_tab_right1', 're_tab_right2',
'', 're_tab_border', 're_tab_right1', 're_tab_right2'
	);

	tab_class[1] = Array(
're_tab_border', '', '', 're_tab_right2', '', 're_tab_right1', 're_tab_right2',
're_tab_border', '', '', ''
	);

	tab_class[2] = Array(
'', '', 're_tab_border', 're_tab_right2', 're_tab_border', 're_tab_right1', 're_tab_right2',
're_tab_border', 're_tab_border', 're_tab_border', 're_tab_border'
	);


//change state of tab 'name'
function show_tab(name) {
var i, j;
var tab_indx = null;


	//find index of active tab
	for (i in tab_names) {
		if (tab_names[i] == name) tab_indx = i;
	}

	if (!tab_indx) return; //tab not found

	//set states of all tabs
	for (i in tab_names) {

		//choose visibility scheme for the tab
		var class_indx = i-tab_indx>=-1&&i-tab_indx<=1?i-tab_indx:2;
		for (j in elements) {

			eval ('document.getElementById("'+elements[j]+'_'+tab_names[i]+'").className = tab_class[class_indx][j]');

			var tab_class_name = 're_tab_off';
			var tab_display = 'none';
			if (i == tab_indx) {

				tab_class_name = 're_tab_on';
				tab_display = '';
				active_tab_name = name;

			}

			eval ('document.getElementById("tab_'+tab_names[i]+'").className = tab_class_name');
			eval ('document.getElementById("div_'+tab_names[i]+'").style.display = tab_display');

		}
	}

	//change ids of all forms, so current one has id 'form_name'
var forms = document.getElementsByTagName('FORM');

	for (i=0; i<forms.length; i++) {
		var el_name = forms[i].name;

		if (el_name == name+'_form_name') forms[i].id = 'form_name';
			else forms[i].id = forms[i].name;
	}

	if (!tab_show_flags[name]) {
<?php if ($browser == 'ns') : ?>
//		window.sizeToContent(); //correct window size
<?php else : ?>
		resize_dialog(); //correct window size
<?php endif; ?>
		tab_show_flags[name] = true;
	}

}


</script>


<style type="text/css">

.re_tab_tr {
	height: 25px;
}

.re_tab_on {
	height: 19px;
<?php if ($browser == 'ns') : ?>
	cursor: pointer;
<?php else : ?>
	cursor: hand;
<?php endif; ?>
}

.re_tab_off {
	height: 17px;
<?php if ($browser == 'ns') : ?>
	cursor: pointer;
<?php else : ?>
	cursor: hand;
<?php endif; ?>
}

.re_tab_border {
	background: #FFFFFF;
}

.re_tab_right1 {
	background: buttonshadow;
}

.re_tab_right2 {
	background: #000000;
}

</style>


</head>

<body class="re_dialog" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0" onload="init_table_tabs_dialog();">

<table border="0" cellpadding="0" cellspacing="2" width="100%" height="100%">

<tr>

<td vAlign="top">

<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
<tr>

<td vAlign="top">
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
<tr class="re_tab_tr">


<td width="2" nowrap>
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
<tr><td height="100%"></td></tr>
<tr><td class="re_tab_border" height="1" nowrap></td></tr>
</table>
</td>

<?php

	foreach ($tabs as $k=>$val) {

		echo '
<td vAlign="bottom">
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td colspan="2" id="top1_'.$k.'"></td>
<td class="re_tab_border" width="1" height="1" nowrap></td>
<td colspan="2" id="top2_'.$k.'"></td>
</tr>
<tr>
<td width="1" height="1" nowrap></td>
<td class="re_tab_border" width="1" height="1" nowrap id="top3_'.$k.'"></td>
<td></td>
<td class="re_tab_right2" width="1" height="1" nowrap id="top4_'.$k.'"></td>
<td width="1" height="1" nowrap></td>
</tr>
<tr>
<td class="re_tab_border" width="1" height="1" nowrap id="left1_'.$k.'"></td>
<td></td>
<td class="re_tab_off" id="tab_'.$k.'" unselectable="on"'.($do_create&&$k!='table'?'':' onclick="show_tab(\''.$k.'\');"').($do_create&&$k!='table'?' '.($browser=='ns'?'style="color:buttonshadow"':'disabled'):'').'>&nbsp;&nbsp;'.$val.'&nbsp;&nbsp;</td>
<td class="re_tab_right1" width="1" height="1" nowrap id="right1_'.$k.'"></td>
<td class="re_tab_right2" width="1" height="1" nowrap id="right2_'.$k.'"></td>
</tr>
<tr class="re_tab_border" id="bottom1_'.$k.'">
<td class="re_tab_border" width="1" height="1" nowrap id="bottom2_'.$k.'"></td>
<td colspan="2" height="1" nowrap></td>
<td class="re_tab_border" width="1" height="1" nowrap id="bottom3_'.$k.'"></td>
<td class="re_tab_border" width="1" height="1" nowrap id="bottom4_'.$k.'"></td>
</tr>
</table>
</td>
		';

	}

?>


<td width="100%">
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
<tr><td colspan="2"></td></tr>
<tr>
<td class="re_tab_border" width="100%"></td>
<td class="re_tab_right2" width="1" height="1" nowrap></td>
</tr>
</table>
</td>

</tr></table>

</td>

</tr>

<tr height="100%">
<td colspan="7" height="100%" vAlign="top">

<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
<tr>
<td rowspan="2" class="re_tab_border" width="1" nowrap></td>

<td vAlign="top" width="100%" height="100%">


<!--
	TABLE PROPERTIES
-->

<!-- image to determine sizes of chosen image -->
<div style="<?php if ($browser == 'ns') echo 'display:none;'; ?>position:absolute; top:0; left:0; visibility:hidden">
<img name="temp_image" id="table_background_temp_image" src="images/space.gif" onload="on_temp_image_load(this);">
</div>
<!-- image to determine sizes of chosen image -->

<!-- image to determine sizes of chosen image -->
<div style="<?php if ($browser == 'ns') echo 'display:none;'; ?>position:absolute; top:0; left:0; visibility:hidden">
<img name="temp_image" id="tr_background_temp_image" src="images/space.gif" onload="on_temp_image_load(this);">
</div>
<!-- image to determine sizes of chosen image -->

<!-- image to determine sizes of chosen image -->
<div style="<?php if ($browser == 'ns') echo 'display:none;'; ?>position:absolute; top:0; left:0; visibility:hidden">
<img name="temp_image" id="td_background_temp_image" src="images/space.gif" onload="on_temp_image_load(this);">
</div>
<!-- image to determine sizes of chosen image -->

<div id="div_table" <?php if ($tab != 'table') echo 'style="display:none"'; ?>>

<table border="0" cellspacing="6" cellpadding="2" width="100%" height="100%"><tr><td class="re_dialog" vAlign="top">



<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%"><tr><td vAlign="top">



<FIELDSET style="padding-top:0;padding-left:5;padding-right:5;padding-bottom:5"><LEGEND><b><?php echo $text['Sizes']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<form name="table_form_name" id="table_form_name">

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Rows, Columns and Sizes -->
<tr>
<td align="right" width="70"><?php echo $text['Rows']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50"><input name="rows" type="text" value="" size="4" maxlength="3" style="width:50"></td>
<!--<td width="50">&nbsp;</td>-->

<td align="right" width="115"><?php echo $text['Width']; ?>:</td>
<td width="5">&nbsp;</td>
<td><input name="width" type="text" value="" size="4" maxlength="5" style="width:50"></td>
</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>
<td align="right" width="70"><?php echo $text['Columns']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50"><input name="columns" type="text" value="" size="4" maxlength="3" style="width:50"></td>
<!--<td width="50">&nbsp;</td>-->

<td align="right" width="115"><?php echo $text['Height']; ?>:</td>
<td width="5">&nbsp;</td>
<td><input name="height" type="text" value="" size="4" maxlength="5" style="width:50"></td>
</tr>
<!-- Rows, Columns and Sizes -->

</table>

</FIELDSET>

<FIELDSET style="padding:5;"><LEGEND><b><?php echo $text['Background']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Background -->
<tr>
<td align="right" width="70"><?php echo $text['Color']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="table_bgColor_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="bgColor" id="table_bgColor" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>
<td width="10">&nbsp;</td>
<td align="right"><?php echo $text['Image']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<!--
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td></td>
<td>
-->

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table border="0" cellspacing="0" cellpadding="0" width="100"><tr><td width="100" height="100" align="center" vAlign="center" nowrap><img name="background_image" id="table_background_image" src="images/space.gif" height="100" width="100" onClick="set_image(event); return false;"></td></tr></table></td>
<td><input type="checkbox" name="background" id="table_background" onClick="if(checked) set_image(event); update_preview();"></td>
</tr>
</table>

<!--
</td>
</tr>
</table>
-->

</td>

</tr>
<!-- Background -->

</table>

</FIELDSET>


<FIELDSET style="padding:5"><LEGEND><b><?php echo $text['Align']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Rows, Columns and Sizes -->
<tr>
<td align="right" width="70"><?php echo $text['TableAlign']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="75" style="width:75">

<select name="align" onchange="update_preview();" style="width:80">
  <option value=""></option>
<?php
	foreach ($align as $k=>$val) {
		echo '<option value="'.$k.'">'.$val.'</option>';
	}
?>
</select>

</td>

<td align="right" width="115"><?php echo $text['Text']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="75" style="width:75">

<select name="textAlign" onchange="update_preview();" style="width:80">
  <option value=""></option>
<?php
	foreach ($align as $k=>$val) {
		echo '<option value="'.$k.'">'.$val.'</option>';
	}
?>
</select>

</td>
</tr>
<!-- Rows, Columns and Sizes -->

</table>

</FIELDSET>



</td>
<?php if ($browser != 'ns') : ?>
<td width="5">&nbsp;</td>
<?php endif; ?>
<td vAlign="top">



<FIELDSET style="padding-top:0;padding-left:5;padding-right:5;padding-bottom:5"><LEGEND><b><?php echo $text['Padding&Spacing']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Padding and Spacing -->
<tr>
<td align="right" width="115"><?php echo $text['Padding']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50"><input name="cellPadding" type="text" value="" size="4" maxlength="3" style="width:50" onchange="update_preview();"></td>
</tr>

<tr><td colspan="6"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>
<td align="right" width="115"><?php echo $text['Spacing']; ?>:</td>
<td width="5">&nbsp;</td>
<td><input name="cellSpacing" type="text" value="" size="4" maxlength="3" style="width:50" onchange="update_preview();"></td>
</tr>
<!-- Padding and Spacing -->

</table>

</FIELDSET>

<FIELDSET style="padding:5;"><LEGEND><b><?php echo $text['Border']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr><td><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Border -->
<tr>
<td align="right" width="115"><?php echo $text['Width']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50"><input name="border" type="text" value="" size="4" maxlength="3" style="width:50" onchange="update_preview();"></td>
</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>

<td align="right" width="115"><?php echo $text['Color']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50">

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="table_borderColor_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="borderColor" id="table_borderColor" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>

</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>
<td align="right" width="115"><?php echo $text['Colorlight']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="table_borderColorLight_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="borderColorLight" id="table_borderColorLight" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>
</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>
<td align="right" width="115"><?php echo $text['Colordark']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="table_borderColorDark_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="borderColorDark" id="table_borderColorDark" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>
</tr>

<tr><td><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>
<td align="right" width="115"><?php echo $text['BorderCollapse']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50"><input type="checkbox" name="borderCollapse" onclick="update_preview();"></td>
</tr>

<!-- Border -->

</table>

</FIELDSET>


</td></tr>
</table>


		
</td></tr>

</form>

</table>

</div>

<!--
	!TABLE PROPERTIES
-->


<!--
	ROW PROPERTIES
-->

<div id="div_tr" <?php if ($tab != 'tr') echo 'style="display:none"'; ?>>

<table border="0" cellspacing="6" cellpadding="2" width="100%" height="100%"><tr><td class="re_dialog" vAlign="top">



<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%"><tr><td vAlign="top" nowrap>



<FIELDSET style="padding-top:0;padding-left:5;padding-right:5;padding-bottom:5"><LEGEND><b><?php echo $cell_text['Sizes']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<form name="tr_form_name" id="tr_form_name">

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Rows, Columns and Sizes -->
<tr>
<td align="right" width="70"><?php echo $text['Height']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50"><input name="height" type="text" value="" size="4" maxlength="5" style="width:50"></td>
<!--<td width="50">&nbsp;</td>-->

<td align="right" width="115"><?php echo $cell_text['Align']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="75" style="width:75">

<select name="align" onchange="update_preview();">
  <option value=""></option>
<?php
	foreach ($align as $k=>$val) {
		echo '<option value="'.$k.'">'.$val.'</option>';
	}
?>
</select>

</td>
</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>
<td colspan="3"></td>

<td align="right" width="115"><?php echo $cell_text['vAlign']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="75" style="width:75">

<select name="vAlign" onchange="update_preview();">
  <option value=""></option>
<?php
	foreach ($valign as $k=>$val) {
		echo '<option value="'.$k.'">'.$val.'</option>';
	}
?>
</select>

</td>
</tr>
<!-- Rows, Columns and Sizes -->

</table>

</FIELDSET>

<FIELDSET style="padding:5;"><LEGEND><b><?php echo $text['Background']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Background -->
<tr>
<td align="right" width="70"><?php echo $text['Color']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="tr_bgColor_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="bgColor" id="tr_bgColor" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>
<td width="10">&nbsp;</td>
<td align="right"><?php echo $text['Image']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table border="0" cellspacing="0" cellpadding="0" width="100"><tr><td width="100" height="100" align="center" vAlign="center" nowrap><img name="background_image" id="tr_background_image" src="images/space.gif" height="100" width="100" onClick="set_image(event); return false;"></td></tr></table></td>
<td><input type="checkbox" name="background" id="tr_background" onClick="if(checked) set_image(event); update_preview();"></td>
</tr>
</table>

</td>

</tr>
<!-- Background -->

</table>

</FIELDSET>



</td>
<?php if ($browser != 'ns') : ?>
<td width="5">&nbsp;</td>
<?php endif; ?>
<td vAlign="top">



<FIELDSET style="padding-top:0;padding-left:5;padding-right:5;padding-bottom:5"><LEGEND><b><?php echo $text['Border']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr><td><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Border -->
<tr>

<td align="right" width="115"><?php echo $text['Color']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50">

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="tr_borderColor_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="borderColor" id="tr_borderColor" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>

</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>

<td align="right" width="115"><?php echo $text['Colorlight']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="tr_borderColorLight_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="borderColorLight" id="tr_borderColorLight" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>
</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>

<td align="right" width="115"><?php echo $text['Colordark']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="tr_borderColorDark_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="borderColorDark" id="tr_borderColorDark" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>
</tr>

<!-- Border -->

</table>

</FIELDSET>



</td></tr></table>


		
</td></tr>

</form>

</table>


</div>

<!--
	!ROW PROPERTIES
-->


<!--
	CELL PROPERTIES
-->

<div id="div_td" <?php if ($tab != 'td') echo 'style="display:none"'; ?>>

<table border="0" cellspacing="6" cellpadding="2" width="100%" height="100%"><tr><td class="re_dialog" vAlign="top">



<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%"><tr><td vAlign="top" nowrap>



<FIELDSET style="padding-top:0;padding-left:5;padding-right:5;padding-bottom:5"><LEGEND><b><?php echo $cell_text['Sizes']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<form name="td_form_name" id="td_form_name">

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Rows, Columns and Sizes -->
<tr>
<td align="right" width="70"><?php echo $text['Width']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50"><input name="width" type="text" value="" size="4" maxlength="5" style="width:50"></td>
<!--<td width="50">&nbsp;</td>-->

<td align="right" width="115"><?php echo $cell_text['Align']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="75" style="width:75">

<select name="align" onchange="update_preview();">
  <option value=""></option>
<?php
	foreach ($align as $k=>$val) {
		echo '<option value="'.$k.'">'.$val.'</option>';
	}
?>
</select>

</td>
</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>
<td align="right" width="70"><?php echo $text['Height']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50"><input name="height" type="text" value="" size="4" maxlength="5" style="width:50"></td>
<!--<td width="50">&nbsp;</td>-->

<td align="right" width="115"><?php echo $cell_text['vAlign']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="75" style="width:75">

<select name="vAlign" onchange="update_preview();">
  <option value=""></option>
<?php
	foreach ($valign as $k=>$val) {
		echo '<option value="'.$k.'">'.$val.'</option>';
	}
?>
</select>

</td>
</tr>
<!-- Rows, Columns and Sizes -->

</table>

</FIELDSET>

<FIELDSET style="padding:5;"><LEGEND><b><?php echo $text['Background']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Background -->
<tr>
<td align="right" width="70"><?php echo $text['Color']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="td_bgColor_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="bgColor" id="td_bgColor" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>
<td width="10">&nbsp;</td>
<td align="right"><?php echo $text['Image']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table border="0" cellspacing="0" cellpadding="0" width="100"><tr><td width="100" height="100" align="center" vAlign="center" nowrap><img name="background_image" id="td_background_image" src="images/space.gif" height="100" width="100" onClick="set_image(event); return false;"></td></tr></table></td>
<td><input type="checkbox" name="background" id="td_background" onClick="if(checked) set_image(event); update_preview();"></td>
</tr>
</table>

</td>

</tr>
<!-- Background -->

</table>

</FIELDSET>



</td>
<?php if ($browser != 'ns') : ?>
<td width="5">&nbsp;</td>
<?php endif; ?>
<td vAlign="top">



<FIELDSET style="padding-top:0;padding-left:5;padding-right:5;padding-bottom:5"><LEGEND><b><?php echo $text['Border']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr><td><img src="images/space.gif" width="1" height="5"></td></tr>

<!-- Border -->
<tr>

<td align="right" width="115"><?php echo $text['Color']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50">

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="td_borderColor_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="borderColor" id="td_borderColor" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>

</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>

<td align="right" width="115"><?php echo $text['Colorlight']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="td_borderColorLight_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="borderColorLight" id="td_borderColorLight" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>
</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>

<td align="right" width="115"><?php echo $text['Colordark']; ?>:</td>
<td width="5">&nbsp;</td>
<td>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table id="td_borderColorDark_table" bgColor="#ffffff" height="20" width="20" onClick="set_color(event);"><tr><td></td></tr></table></td>
<td><input type="checkbox" name="borderColorDark" id="td_borderColorDark" onClick="if(checked) set_color(event); update_preview();"></td>
</tr>
</table>

</td>
</tr>

<tr><td colspan="8"><img src="images/space.gif" width="1" height="5"></td></tr>

<tr>

<td align="right" width="115"><?php echo $cell_text['NoWrap']; ?>:</td>
<td width="5">&nbsp;</td>
<td width="50"><input type="checkbox" name="noWrap"></td>

</tr>

<!-- Border -->

</table>

</FIELDSET>



</td></tr></table>


		
</td></tr>

</form>

</table>


</div>

<!--
	!CELL PROPERTIES
-->


</td>

<td rowspan="2" class="re_tab_right1" width="1" nowrap></td>
<td rowspan="3" class="re_tab_right2" width="1" nowrap></td>
</tr>

<tr>
<td class="re_tab_right1" height="1" nowrap></td>
</tr>

<tr>
<td colspan="3" class="re_tab_right2" height="1" nowrap></td>
</tr>

</table>

</td>
</tr>


</table>

</td>
</tr>

<tr height="1">
<td vAlign="top">

<FIELDSET style="padding:5;"><LEGEND><b><?php echo $text['Preview']; ?></b></LEGEND>

<table border="0" cellspacing="0" cellpadding="2" width="100%" height="95">
<tr><td>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td colspan="2" bgColor="#FFFFFF" style="border: 1px solid #000000">

<iframe style="border: 0px solid #000000; padding:0" frameborder="0" width="100%" height="95" id="preview_iframe">
</iframe>

</td>

<td>

<table border="0" cellspacing="0" cellpadding="0" align="center" width="2" height="100%">
<tr height="2"><td></td></tr>
<tr bgColor="#000000"><td></td></tr>
</table>

</td>
</tr>

<tr>
<td width="2" height="2" nowrap></td>
<td width="100%" height="2" bgColor="#000000"></td>
<td width="2" height="2" nowrap bgColor="#000000"></td>
</tr>

</table>

</td></tr>
</table>

</FIELDSET>

</td>
</tr>


<tr height="1">
<td>
<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr><td><img src="images/space.gif" width="1" height="10"></td></tr>

<tr align="center"><td vAlign="center">
<?php if (!isset($do_create) || !$do_create || $do_create == 2) : ?>
<!--<input type="Button" value="<?php echo $text['Apply']; ?>" onclick="update_preview(); return_table_tabs_parameters();">-->
<?php endif; ?>
<input type="Button" value="<?php echo $rich_lang->item('Ok'); ?>" onclick="return_table_tabs_parameters();<?php if ($browser == 'ns') echo ' re_restore_borders();'; ?> window.close();">
<input type="Button" value="<?php echo $rich_lang->item('Cancel'); ?>" onclick="window.close();">
</td></tr>

<tr><td><img src="images/space.gif" width="1" height="5"></td></tr>

</table>
</td>
</tr>


</table>


</body>
</html>