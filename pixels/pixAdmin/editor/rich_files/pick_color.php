<?php

//language class
require_once('lang/class.rich_lang.php');

	//extract variables submitted to this page
	@extract($_GET);

	$rich_lang = new rich_lang($lang); //text data in current language
	$text = $rich_lang->item('ColorPicker');

?><html>
<head>
<title><?php echo $text['Title']; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $rich_lang->item("Charset"); ?>">

<link rel="StyleSheet" type="text/css" href="rich.css">

<script language="JavaScript" src="rich_dialog.js?ddd"></script>

<script language="JavaScript">
function select_color(){
  element = window.event.srcElement;

  if(element && element.tagName=='IMG' && element.parentElement){
    td = get_previous_object(element.parentNode,'TD');
    form_name.color.value = td.bgColor;

    color_td.bgColor = td.bgColor;
  }

}

//set initial color if specified any
function set_init_color(){
var color = window.dialogArguments;
	if (color) {
	    form_name.color.value = color;
	    color_td.bgColor = color;
	}
}
</script>

</head>

<body topmargin="0" leftmargin="0" rightmargin="0" class="re_dialog" onload="set_init_color(); resize_dialog();">
<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr class="re_dialog"><td align="center">

<table onclick="select_color();" border="1" cellspacing="0" cellpadding="0">

<script language="JavaScript">
var points = new Array("00","33","66","99","CC","FF");
var points_num = points.length;
var text = "";

  for(r=0;r<points_num;r++){
    text+="<TR>"
    for(g=points_num-1;g>=0;g--)
      for(b=points_num-1;b>=0;b--){
        color = points[r]+points[g]+points[b] 
        text += '<td bgColor="#'+color+'">'
             +  '<img src="images/space.gif" width="10" height="10"'
             +  ' alt="#'+color+'" style="cursor:hand"></td>';
      }
      text+="</tr>";
  }
  document.write(text);
</script>

</table>

<table border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td><img src="images/space.gif" width="1" height="10"></td></tr>
<tr>
<td id="color_td" bgcolor="#ffffff"><img src="images/space.gif" width="40" height="20"></td>
<td>&nbsp</td>
<form name="form_name" onsubmit="window.returnValue = form_name.color.value; window.close(); return false;">
<td>
<input type="Text" name="color" size="8" maxsize="8" value="#ffffff">
<input type="Button" value="<?php echo $rich_lang->item('Ok'); ?>" onclick="window.returnValue = form_name.color.value; window.close();">
<input type="Button" value="<?php echo $rich_lang->item('Cancel'); ?>" onclick="window.close();">
</td>
</form>
</tr></table>

</td></tr></table>
</body>
</html>