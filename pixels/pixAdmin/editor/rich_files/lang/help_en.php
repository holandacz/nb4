<?php

$class_path = '../../';
//language class
require_once('class.rich_lang.php');

    //extract variables submitted to this page
    @extract($_GET);

    $rich_lang = new rich_lang($lang, false); //text data in current language

?><HTML>

<HEAD>
    <TITLE>Rich Editor Help</TITLE>

    <META name="Copyright" content="Copyright (c) 2002-2004 Vyacheslav Smolin">
    <META http-equiv="Content-Type" content="text/html; charset=<?php echo $rich_lang->item("Charset"); ?>">

<STYLE type="text/css">
.help_tips{
  font-family: "Verdana";
  font-size: xx-small;
}
.help_text p,div{
  font-family: "Verdana";
  font-size: x-small;
}
td.help_action{
  text-align: center;
  vertical-align: center;
}

a.rich_link {
  text-decoration:none;
  color:blue;
}
</STYLE>

</HEAD>

<BODY
BGCOLOR="#FFFFFF"
TEXT="#000000"
LINK="#0000FF"
VLINK="#840084"
ALINK="#0000FF"
>

<H1><a href="http://www.richarea.com/" target="_blank" class="rich_link" title="Rich Editor">Editor</a> Help</H1>


<HR>

<!--
<P class="help_text">
<div>
To insert &lt;br&gt; instead of a new paragraph press Shift+Enter instead of Enter.
</div>
</P>
-->

<H2>Available actions</H2>

<P>
<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="0" WIDTH="100%" class="help_tips">
<TR BGCOLOR="#CCCCCC">
<TD class="help_action">
<B>Action</B>
</TD>
<TD ALIGN="CENTER" VALIGN="CENTER" WIDTH="100%">
<B>Description</B>
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('FullScreen'); ?>" src="../images/fullscreen.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Switch the editor in fullscreen mode (if there are no other editors in the
fullscreen mode yet)<br><br>

If the mode is on, the corresponding button is drawing pressed<br><br>

<b>Note: </b>the mode is available in MSIE 5.5+ only
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Preview'); ?>" src="../images/preview.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Shows preview of the content being edited in the separate window<br><br>

Use it to see, how the content will look in your browser
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Find'); ?>" src="../images/find.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to find/replace text inside the editor<br><br>

You can make case-sensitive search and/or look for whole words only
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Cut'); ?>" src="../images/cut.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Cut selected portion of the document to clipboard
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Copy'); ?>" src="../images/copy.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Copy selected portion of the document to clipboard
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Paste'); ?>" src="../images/paste.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Paste content of the clipboard to the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Undo'); ?>" src="../images/undo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Undo the previous change to the document
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Redo'); ?>" src="../images/redo.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Redo the last undone change to the document
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Bold'); ?>" src="../images/bold.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Set/unset bold style to selected text or to text of the current cursor position
(unset if the text is bold and set otherwise)
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Italic'); ?>" src="../images/italic.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Set/unset italic style to selected text or to text of the current cursor position
(unset if the text is italic and set otherwise)
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Underline'); ?>" src="../images/underline.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Set/unset underlined style to selected text or to text of the current cursor
position (unset if the text is underlined and set otherwise)
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Strikethrough'); ?>" src="../images/strikethrough.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Make selected text or to text of the current cursor position striked/unstriked
through (unstrike if the text has been striked before and strike otherwise)
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SuperScript'); ?>" src="../images/superscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Set/unset superscript (upper index) style to selected text or to text of the
current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SubScript'); ?>" src="../images/subscript.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Set/unset subscript (lower index) style to selected text or to text of the
current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyLeft'); ?>" src="../images/left.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Align selected portion of the document or text of the current cursor position to
the left
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyCenter'); ?>" src="../images/center.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Align selected portion of the document or text of the current cursor position to
the center
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyRight'); ?>" src="../images/right.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Align selected portion of the document or text of the current cursor position to
the right
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('JustifyFull'); ?>" src="../images/justify.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Justify selected portion of the document or text of the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertOrderedList'); ?>" src="../images/numlist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Convert selected text or text of the current cursor position to ordered list
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertUnorderedList'); ?>" src="../images/bullist.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Convert selected text or text of the current cursor position to unordered list
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Outdent'); ?>" src="../images/outdent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Outdent text of the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Indent'); ?>" src="../images/indent.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Indent text of the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertHorizontalRule'); ?>" src="../images/h_line.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Insert a horizontal line in the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('RemoveFormat'); ?>" src="../images/rem_format.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Remove formatting of selected text or text of the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTable'); ?>" src="../images/table.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a table dialog window to create a table in the current cursor position or
to change properties of the selected table<br><br>

Table properties to set/modify:<br>
Rows -- number of rows in table; columns -- number of columns in table; width --
width of table; height -- height of table; backgroung color -- background color
of table; backgroung image -- background image of table; padding -- padding
around table cells; spacing -- spacing between table cells; border width --
width of border around table cells; border colorlight -- color of bottom and
right borders of table cells; border colorlight -- color of left and top borders
of table cells<br><br>

Cell properties to modify:<br>
Width -- width of the cell; height -- height of the cell; vAlign -- vertical
alignment of the cell; color -- background color of the cell<br><br>

To edit properties of a table do right click on its border or select it and then
click on the corresponding button in toolbar<br>
To edit table cell properties -- right click inside the cell<br><br>

To set colors, click on the corresponding checkbox or on the colored square
left to the checkbox control<br>
To set background image, click on the corresponding checkbox or inside the
space between the checkbox and text 'Image:'<br>
To unset color/image switch the corresponding checkbox off<br><br>

<b>Note:</b> fields 'rows' and 'columns' could not be modified -- use
insert/delete row/column buttons instead

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertRow'); ?>" src="../images/insrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Insert a row after the row where cursor currently is
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteRow'); ?>" src="../images/delrow.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Delete the row where cursor currently is
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertColumn'); ?>" src="../images/inscol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Insert a column after the column where cursor currently is
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteColumn'); ?>" src="../images/delcol.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Delete the column where cursor currently is
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertCell'); ?>" src="../images/inscell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Insert a cell after the cell where cursor currently is
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('DeleteCell'); ?>" src="../images/delcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Delete the cell where cursor currently is
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('MergeCells'); ?>" src="../images/mergecells.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Merge the current cell with the next cell if exists
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SplitCell'); ?>" src="../images/splitcell.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Split the current cell in two if the cells where merged earlier (ie colspan of
the current cell is greater than 1)
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateForm'); ?>" src="../images/form.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a form dialog window to create a form in the current cursor position
<br><br>

Form properties to set/modify:<br>
Name -- name of form; method -- method for sending data of the form (get/post);
action -- destination the form must submit data to<br><br>

Switch invisible borders on to see all forms in document<br><br>

To change properties of the desired form do right click inside it<br><br>

To edit properties of other form elements do right click on its borders or
select them and then click on the corresponding button in toolbar

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateText'); ?>" src="../images/text.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to create a text form element in the current cursor
position<br><br>

Text field properties to set/modify:<br>
Name -- name of the element; value -- initial value of the element;
type -- type of the text field (text/password); maximum characters --
maximum number of characters available to type in the element;
characters width -- width of the text element in characters

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateTextArea'); ?>" src="../images/textarea.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to create a text area element in the current cursor
position<br><br>

Text area properties to set/modify:<br>
Name -- name of the element; value -- initial value of the element;
rows -- height of the element in rows of characters; columns --
width of the text area element in characters

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateButton'); ?>" src="../images/button.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to create a button element in the current cursor position
<br><br>

Button properties to set/modify:<br>
Name -- name of the element; value -- initial value of the element;
type -- type of the button (button/reset/submit)

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateSelect'); ?>" src="../images/select.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to create a list box in the current cursor position
<br><br>

List properties to set/modify:<br>
Name -- name of the element; Size -- specifies the number of rows in the list
that should be visible at the same time; Multiple -- if set, allows multiple
selections. If not set, the list element only permits single selections;
Items -- set of options of the list element
<br><br>

Properties of list options to set/modify:<br>
Text -- contents of the option; Value -- initial value of the option;
Selected -- if set, specifies that this option is pre-selected
<br><br>

Buttons:<br>
Add -- adds an option with values specified in Text, Value and Selected
elements; Delete -- delete selected option from the list of options; Update --
update selected option with values specified in Text, Value and Selected
elements; /\ -- moves selected option one position up; \/ -- moves selected
option one position down

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateHidden'); ?>" src="../images/hidden.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to create a hidden field element in the current cursor
position<br><br>

Hidden field properties to set/modify:<br>
Name -- name of the element; value -- value of the element

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateCheckBox'); ?>" src="../images/checkbox.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to create a checkbox element in the current cursor
position<br><br>

Checkbox properties to set/modify:<br>
Name -- name of the element; value -- value of the element; checked -- if
set the checkbox is drawn turned on, otherwise - off

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateRadio'); ?>" src="../images/radio.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to create a radio button in the current cursor position
<br><br>

Radio button properties to set/modify:<br>
Name -- name of the element; value -- value of the element; checked -- if
set the checkbox is drawn turned on, otherwise - off

</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FormatBlock'); ?>
</TD>
<TD VALIGN="TOP">
Set a paragraph format for selected text or text of the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontName'); ?>
</TD>
<TD VALIGN="TOP">
Change font of selected text or text of the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('ClassName'); ?>
</TD>
<TD VALIGN="TOP">
Set a stylesheet style for selected portion of the document
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('FontSize'); ?>
</TD>
<TD VALIGN="TOP">
Set font size for selected text or text of the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('ForeColor'); ?>" src="../images/fgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Change color of selected text or text of the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('BackColor'); ?>" src="../images/bgcolor.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Change background color for selected text or text of the current cursor position
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateImage'); ?>" src="../images/image.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls an image dialog window to create an image in the current cursor position
or to change properties of the selected image<br><br>

Image dialog window consists of four parts:<br>
file manager (in the left part of the window), preview area (in top right
corner), properties area (below preview area) and file uploading form (in the
very bottom of the window)<br><br>

File manager shows tree of folders from the top folder for uploaded files.<br>
To see subfolders and files stored in a folder, click on the corresponding
'+' image. To close contents of a folder, click on the corresponding '-' image.
<br>
Name of the current folder for file uploading is highlighted in red color. To
change folder for file uploading, click on name of the folder required.<br>
To create a new folder, click on 'create folder' link, input a name and press
'ok' button to confirm or 'cancel' to decline the action.<br>
Click on 'r'/'x' links to rename/delete file/folder.<br><br>
<b>Note:</b> folder could be deleted only if there are no folders/files in it
<br><br>

When an image is selected in file manager or in file uploading form (in field
for path to selected client-side file) its preview is shown in preview area
<br><br>

Click on the button in uploading form to select a client-side file to upload
<br><br>

Image properties to set/modify:<br>
Align -- horizontal alignment of image; width -- width of image;
height -- height of image; border -- width of image border; vspace -- vertical
space between image and text; hspace -- horisontal space between image and text;
alt -- alternative text for image (is shown when mouse cursor is over the image);
Source -- defines image location
<br><br>

To edit properties of an image do right click on it or select it and then click
on the corresponding button in toolbar

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateFlash'); ?>" src="../images/flash.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a flash dialog window to create a flash in the current cursor position or
to change properties of the selected flash object<br><br>

Flash properties to set/modify:<br>
Align -- horizontal alignment of flash; width -- width of flash;
height -- height of flash; play -- if set flash starts playing on loading;
loop -- if set flash is playing repeatedly; menu -- if set full flash menu is
shown on right mouse click; bgcolor -- background color of flash; quality --
defines quality of flash playing; Source -- defines flash location<br><br>

Work with server/client side flash is the same as in image dialog window<br><br>

To edit properties of a flash do right click on it or select it and then click
on the corresponding button in toolbar<br><br>

<b>Note: </b>you can do right click on flash objects to edit them in MSIE 6.0+
only
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('CreateLink'); ?>" src="../images/link.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a link dialog window to create a hyperlink on the selected text or control
or to change properties of the selected existing hyperlink<br><br>

Link properties to set/modify:<br>
target -- defines a target for the link to be open in; link -- defines an URL
for the link; name -- name of the link (use it to create anchors); title --
the text is show when mouse cursor is over the link<br><br>

<b>Note: </b>to remove a link, open link dialog window and remove string of
property 'link'<br><br>

To edit properties of a link select the whole link text or its part and then
click on the corresponding button in toolbar. To create an anchor you can
specify value of 'name' attribute only<br><br>

Work with server/client side files is the same as in image dialog window

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PasteWord'); ?>" src="../images/paste_word.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Paste MS Word formatted content from the clipboard in the current cursor
position. This removes all empty tags and style formatting before pasting
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('SwitchBorders'); ?>" src="../images/borders.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Show all invisible table borders. All tables and cells have broken gray borders
<br>
This action do not change the document itself<br><br>

If the mode is on, the corresponding button is drawing pressed
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertChar'); ?>" src="../images/inschar.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to insert a special character in the current cursor
position<br><br>

Click on the desired symbol to insert it
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('InsertSnippet'); ?>" src="../images/snippet.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a dialog window to insert a snippet (custom html code). The snippet
selected is shown in preview window
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('AbsolutePosition'); ?>" src="../images/abspos.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Toggle absolute positioning for the selected element
</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('PageProperties'); ?>" src="../images/page.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Calls a page dialog window to change properties of the current page in editor<br><br>

Page properties to modify:<br>
Title -- title of the page (data inside tag 'title'); description --
description of the page (content of meta field 'description'); keywords -- set
of keywords for the page (content of meta field 'keywords'); color --
background color of the page; image -- background image of the page; text --
color of text; link -- color of hyperlinks; visited link -- color of visited
hyperlinks; active links -- color of active (not visited) hyperlinks<br><br>

To set colors, click on the corresponding checkbox or on the colored square
left to the checkbox control<br>
To set background image, click on the corresponding checkbox or inside the
space between the checkbox and text 'Image:'<br>
To unset color/image switch the corresponding checkbox off<br><br>

<b>Note:</b> the feature is available in page mode only

</TD>
</TR>

<TR>
<TD class="help_action">
  <img alt="<?php echo $rich_lang->item('Help'); ?>" src="../images/help.gif" align="absMiddle" width="20" height="20">
</TD>
<TD VALIGN="TOP">
Shows this help window (if it is not open yet)
</TD>
</TR>

<TR>
<TD class="help_action">
<?php echo $rich_lang->item('Source'); ?>
</TD>
<TD VALIGN="TOP">
Switch the editor between WYWIWYG and source code modes
</TD>
</TR>

</TABLE>
</P>

<P class="help_text">
<div>
<b>Note: </b>some of the actions could be disabled by your administrator!
</div>
</P>

<HR>

<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="0" WIDTH="100%">
<TR>
<TD ALIGN="RIGHT">
<A HREF="#TOP">Top of the document</A>
</TD>
</TR>
</TABLE>

</BODY>

</HTML>