<?php
/*
Rich Editor, Version 2.1
Copyright (c) 2002-2006 V. Smolin All rights reserved.
http://www.richarea.com
re@richarea.com
*/

/*
    $user_agent = @$_SERVER["HTTP_USER_AGENT"];
    if(!$user_agent) $user_agent = $GLOBALS["HTTP_USER_AGENT"];

    //determine browser type
    $rich_prefix = '';
    $rich_browser= '';
    if(ereg("MSIE ([0-9|\.]+)", $user_agent, $regs) &&
       ereg("Win", $user_agent) && !strstr($user_agent, 'Opera')){
        $rich_browser = 'msie';
    }else{
//      if(ereg("Mozilla/([0-9|\.]+)", $user_agent, $regs) &&
//         ereg("Gecko", $user_agent) && (double)$regs[1]>=5.0){
            $rich_browser = 'ns';
            $rich_prefix = '_ns';
//      }
    }
*/

    $browser_info = rich_get_browser_info();
    $rich_browser = $browser_info['rich_browser'];
    $rich_prefix = $browser_info['rich_prefix'];

//language class
require_once(dirname(__FILE__).'/rich_files/lang/class.rich_lang.php');

//$rich_flag = false;
//$rich_lang = array();

class rich{
  var $caption;             //string, appearing before editor body
  var $name;                //unique name of the editor control
  var $value;               //initial editor content
  var $width;               //width of the editor (% or absolute values)
  var $height;              //height of the editor (% or absolute values)
  var $files_path;          //path to top folder for uploaded files
  var $files_url;           //url to top folder for uploaded files
  var $page_mode;           //=true, if whole page is edited
  var $absolute_path;       //=true, if absolute path used
  var $settings;            //editor settings
  var $default_stylesheet;  //stylesheet files loaded automatically
  var $snippets;            //html fragments
  var $font_list;           //list of fonts
  var $font_size_list;      //list of font sizes
  var $class_path;          //path to folder 'class'

  var $classname;
  var $dialog_ext;

  function rich($caption, $name, $value='', $width='', $height='',
                $files_path='/', $files_url='/', $page_mode=false,
                $absolute_path=true){
    $this->caption = $caption;
    $this->name = $name;
    $this->width = $width;
    $this->height = $height;

    $this->absolute_path = $absolute_path;
//  if($this->absolute_path) $this->files_path = "../".$files_path;
//      else $this->files_path = $_SERVER['DOCUMENT_ROOT'].$files_path;
    $this->files_path = $files_path;
    $this->files_url = $files_url;

    $this->page_mode = $page_mode;
//    if(!$this->page_mode) $value = '<body>'.$value.'</body>';
//    $this->value = htmlspecialchars($value);
    $this->value = $value;

    //editor settings
    $this->settings = array();
    //all buttons (and groups) visible - by default
    $this->settings['hidden_tb'] = array();
    //all buttons are allways active - by default
    $this->settings['active_tb'] = false;
    //default language is english
    $this->settings['lang'] = 'en';
    //default encoding (taken from lang file)
    $this->settings['charset'] = '';
    //default contnt mode is html
    $this->settings['xhtml'] = false;
    //default document lang
    $this->settings['doc_lang'] = '';
    //default document encoding
    $this->settings['doc_charset'] = '';
    //borders visibility by default
    $this->settings['borders_visibility'] = false;
    //insert <br> on enter keypress, otherwise -- <p>
    $this->settings['br_on_enter'] = true;
    //set JS code submitting form containing RE
    //eg 'document.getElementById("form_id").submit()'
    $this->settings['on_save'] = 'alert("Place your JS code here!");';
    //use Paste from Word instead of common Paste
    $this->settings['clean_paste'] = false;
    //editable regions mode
    $this->settings['editable_regions'] = false;
    //indent symbols used for source code formatting
    $this->settings['indent'] = '    ';

    //default stylesheets
    $this->default_stylesheet = array();
    //editor snippets
    $this->snippets = array();

    $this->font_list = array();
    $this->font_size_list = array();

    $this->classname = "rich";
    $this->dialog_ext = ".php";
    $this->class_path = false;

    $this->hide_tb('save'); //hide save button by default
  }

  function draw(){

    echo $this->get_rich();

  }

  //hide/show button or group of buttons
  //possible item names:
  //clipboard, history, text, align, list, indent, hr, remove_format, source,
  //table, adv_table, paragraph, font, style, size, color, image, flash, link,
  //paste_word, switch_borders, special_chars, form, snippets, page_properties,
  //help, absolute_position
  function hide_tb($name, $hide=true){
    $this->settings['hidden_tb'][$name] = $hide;
  }

  //check if button or group $name is visible
  function is_visible($name){
    return !isset($this->settings['hidden_tb'][$name]) ||
            !$this->settings['hidden_tb'][$name];
  }

  //switch to simple mode (minimum buttons)
  function simple_mode($mode=true){
    if($mode){
      $this->hide_tb('find');
      $this->hide_tb('list');
      $this->hide_tb('indent');
      $this->hide_tb('hr');
      $this->hide_tb('remove_format');
      $this->hide_tb('table');
      $this->hide_tb('paragraph');
      $this->hide_tb('font');
      $this->hide_tb('size');
      $this->hide_tb('style');
      $this->hide_tb('image');
      $this->hide_tb('flash');
      $this->hide_tb('link');
      $this->hide_tb('anchor');
      $this->hide_tb('paste_word');
      $this->hide_tb('switch_borders');
      $this->hide_tb('special_chars');
      $this->hide_tb('form');
      $this->hide_tb('snippets');
      $this->hide_tb('absolute_position');
      $this->hide_tb('page_properties');
    } else {
        $this->settings['hidden_tb'] = array(); //show all buttons
        $this->hide_tb('save'); //hide save button by default
    }
  }

  //in active mode a button, clicking on who will do no effect, get inactive
  function active_mode($active=true){
    $this->settings['active_tb'] = $active;
  }

  //sets path to stylesheet file to be loaded in editor body
  function set_default_stylesheet($style_path){
    if(gettype($style_path) == "array"){

      foreach($style_path as $k=>$val){
        $this->default_stylesheet[] = $val;
      }

    }else{
      $this->default_stylesheet[] = $style_path;
    }
  }

  //sets snippets ($snippets - an array of pairs $name/$code)
  //if $group is set, the add then snippets to this group
  function set_snippets($snippets, $group=''){
    $group = ereg_replace("['\"]", '', $group);

    if(isset($snippets['name'])){ //only one snippet

      $this->snippets[$group][] = $snippets;

    }else{ //array of snippets

      foreach($snippets as $k=>$val){
        //if name has any symbols and code not empty
        if(ereg("[^[[:space:]]]*", $val['name']) && $val['code']){
          $this->snippets[$group][] = $val;
        }
      }

    }

  }

  //sets font list
  function set_font_list($font_data){
    if(gettype($font_data) == "array"){

      foreach($font_data as $k=>$val){
        $this->font_list[] = $val;
      }

    }else{
      $this->font_list[] = $font_data;
    }
  }

  //sets font size list
  function set_font_size_list($font_data){
    if(gettype($font_data) == "array"){

      foreach($font_data as $k=>$val){
        $this->font_size_list[] = $val;
      }

    }else{
      $this->font_size_list[] = $font_data;
    }
  }

  //set language of the editor interface
  function set_lang($lang='en'){
    $this->settings['lang'] = $lang;
  }

  //switch content mode between html and xhtml1.0
  function xhtml_mode($xhtml=true){
    $this->settings['xhtml'] = $xhtml;
  }

    //set document language
    function set_doc_lang($lang='en'){
        $this->settings['doc_lang'] = $lang;
    }

    //set document charset
    function set_doc_charset($cs='iso-8859-1'){
        $this->settings['doc_charset'] = $cs;
    }

    //set borders visibility by default
    function set_borders_visibility($set_on=true){
        $this->settings['borders_visibility'] = $set_on;
    }

    //set if RE should insert <br> on key press instead of <p>
    function set_br_on_enter($set_on=true){
        $this->settings['br_on_enter'] = $set_on;
    }

    //use Paste from Word instead of common Paste
    function set_clean_paste($set_on=true){
        $this->settings['clean_paste'] = $set_on;
    }

    //set JS code submitting form containing RE
    //eg 'document.getElementById("form_id").submit()'
    function set_on_save_handler($on_save=''){
        if ($on_save != '') $on_save = 'save_in_textarea_all();'.$on_save;
        $this->settings['on_save'] = $on_save;
    }

    //set editable regions mode
    function set_editable_regions($set_on=true){
        $this->settings['editable_regions'] = $set_on;
//      $this->hide_tb('source');
    }

    //set indent symbols
    function set_indent($indent=''){
        $this->settings['indent'] = $indent;
    }

    //returns html code of a toolbar button
    function get_tb_action_icon($item, $image_name, $name, $id, &$lang) {
//      global $class_path;
        $class_path = $this->get_class_path();

        return '
  <img id="'.$item.'_'.$name.'" onclick="active_rich = '.$id.'; do_action(\''.$item.'\')" alt="'.$lang->item($item).'" src="'.$class_path.'rich_files/images/'.$image_name.'.gif" align="absMiddle" width="20" height="20" />
        ';
    }

    //returns html code of a toolbar button
    function get_tb_dialog_icon($item, $image_name, $name, $id, &$lang) {
//      global $class_path;
        $class_path = $this->get_class_path();

        return '
  <img id="'.$item.'_'.$name.'" onclick="active_rich = '.$id.'; show_dialog(\''.$item.'\')" alt="'.$lang->item($item).'" src="'.$class_path.'rich_files/images/'.$image_name.'.gif" align="absMiddle" width="20" height="20" />
        ';
    }

    //returns html code of a toolbar button for Mozilla-based browsers
    function get_tb_action_icon_ns($item, $image_name, $name, $id, &$lang) {
//      global $class_path;
        $class_path = $this->get_class_path();

        return '
  <img id="'.$item.'_'.$name.'" onmousedown="mouse_down(true, this);" onmouseup="mouse_down(false, this);" onclick="active_rich = '.$id.'; do_action(\''.$item.'\')" title="'.$lang->item($item).'" src="'.$class_path.'rich_files/images/'.$image_name.'.gif" align="absMiddle" width="20" height="20" onmouseover="mouse_over(true, this);" onmouseout="mouse_over(false, this);" />
        ';
    }

    //returns html code of a toolbar button for Mozilla-based browsers
    function get_tb_dialog_icon_ns($item, $image_name, $name, $id, &$lang) {
//      global $class_path;
        $class_path = $this->get_class_path();

        return '
  <img id="'.$item.'_'.$name.'" onmousedown="mouse_down(true, this);" onmouseup="mouse_down(false, this);" onclick="active_rich = '.$id.'; show_dialog(\''.$item.'\')" title="'.$lang->item($item).'" src="'.$class_path.'rich_files/images/'.$image_name.'.gif" align="absMiddle" width="20" height="20" onmouseover="mouse_over(true, this);" onmouseout="mouse_over(false, this);" />
        ';
    }

    //returns html code of toolbar groups' separator
    function get_delimiter() {
        return '<span class="re_delimiter"></span><span class="re_space"></span>';
    }

    /*
    * Settings
    *
    * NOTE: Any of the function overrides a corresponding value set by any of
    * the previous calling of the same function and is applied to all
    * editor instances
    *
    * Sessions must be enabled on server otherwise default settings from
    * file check_auth.inc.php used
    *
    * Function session_start() must be called before any of these functions
    */

    //set paths available for uploading, renaming, etc
    function set_avail_paths($paths = false) {
        if (!session_id()) return;
        if (is_array($paths)) $_SESSION['re_set_avail_paths_sess'] = $paths;
            else $_SESSION['re_set_avail_paths_sess'] = 'default';
    }

    //set max size available for files in folders for uploading with subfolders
    function set_total_size($size = 'default') {
        if (!session_id()) return;
        $_SESSION['re_set_total_size_sess'] = $size;
    }

    //set if user is permitted to upload files
    function set_can_upload($can_upload = 'default') {
        if (!session_id()) return;
        $_SESSION['re_set_can_upload_sess'] = $can_upload;
    }

    //set max size of file available for uploading
    function set_max_size($max_size = 'default') {
        if (!session_id()) return;
        $_SESSION['re_set_max_size_sess'] = $max_size;
    }

    //set permission to override existing files
    function set_allow_override($allow_override = 'default') {
        if (!session_id()) return;
        $_SESSION['re_set_allow_override_sess'] = $allow_override;
    }

    //set file extensions available for uploading and renaming
    function set_avail_files($files = false) {
        if (!session_id()) return;
        if (is_array($files)) $_SESSION['re_set_avail_files_sess'] = $files;
            else $_SESSION['re_set_avail_files_sess'] = 'default';
    }

    //set file extensions not available for uploading and renaming
    function set_not_avail_files($files = false) {
        if (!session_id()) return;
        if (is_array($files)) $_SESSION['re_set_not_avail_files_sess'] = $files;
            else $_SESSION['re_set_not_avail_files_sess'] = 'default';
    }

    //set image types available for uploading and renaming
    function set_avail_images($images = false) {
        if (!session_id()) return;
        if (is_array($images)) $_SESSION['re_set_avail_images_sess'] = $images;
            else $_SESSION['re_set_avail_images_sess'] = 'default';
    }

    //set file extensions not available for uploading and renaming
    function set_not_avail_images($images = false) {
        if (!session_id()) return;
        if (is_array($images)) $_SESSION['re_set_not_avail_images_sess'] = $images;
            else $_SESSION['re_set_not_avail_images_sess'] = 'default';
    }

    //set max size of images available for uploaded
    function set_max_image_size($width = false, $height = false) {
        if (!session_id()) return;
        if ($width !== false && $height !== false)
                $_SESSION['re_set_max_image_size_sess'] = array($width, $height);
            else $_SESSION['re_set_max_image_size_sess'] = 'default';
    }

    //set max size of flash files available for uploaded
    function set_max_flash_size($width = false, $height = false) {
        if (!session_id()) return;
        if ($width !== false && $height !== false)
                $_SESSION['re_set_max_flash_size_sess'] = array($width, $height);
            else $_SESSION['re_set_max_flash_size_sess'] = 'default';
    }

    //set permission to create folders
    function set_can_create_dir($can_create = 'default') {
        if (!session_id()) return;
        $_SESSION['re_set_can_create_dir_sess'] = $can_create;
    }

    //set permission to rename folders
    function set_can_rename_dir($can_rename = 'default') {
        if (!session_id()) return;
        $_SESSION['re_set_can_rename_dir_sess'] = $can_rename;
    }

    //set permission to delete folders
    function set_can_delete_dir($can_delete = 'default') {
        if (!session_id()) return;
        $_SESSION['re_set_can_delete_dir_sess'] = $can_delete;
    }

    //set permission to rename files
    function set_can_rename_file($can_rename = 'default') {
        if (!session_id()) return;
        $_SESSION['re_set_can_rename_file_sess'] = $can_rename;
    }

    //set permission to delete files
    function set_can_delete_file($can_delete = 'default') {
        if (!session_id()) return;
        $_SESSION['re_set_can_delete_file_sess'] = $can_delete;
    }

    //erase all settings stored in session varibles
    function set_default_settings() {
        $this->set_avail_paths();
        $this->set_total_size();
        $this->set_can_upload();
        $this->set_max_size();
        $this->set_allow_override();
        $this->set_avail_files();
        $this->set_not_avail_files();
        $this->set_avail_images();
        $this->set_not_avail_images();
        $this->set_max_image_size();
        $this->set_max_flash_size();
        $this->set_can_create_dir();
        $this->set_can_rename_dir();
        $this->set_can_delete_dir();
        $this->set_can_rename_file();
        $this->set_can_delete_file();
    }

    /*
    * Settings
    */

    //set path to folder class (to avoid using the appropriate global variable
    function set_class_path($class_path) {
        $this->class_path = $class_path;
    }

    //returns path to folder class, if not set returns value of the appropriate
    //global variable
    function get_class_path() {
        if ($this->class_path !== false) {
            return $this->class_path;
        } else {
            global $class_path;
            return $class_path;
        }
    }

    //returns code loading its JS and CSS files
    function get_head_code() {
        return rich_get_head_code($this->get_class_path());
    }

    //returns context_menu_data
    function get_context_menu_data(&$lang) {
        $lang_name = $lang->lang;

        //context menu data
        $cx_name = 'rich_cx_'.$lang_name;
        return "
var rich_cx_item_$lang_name = Array('InsertRow', 'DeleteRow', 'InsertColumn',
                        'DeleteColumn', 'InsertCell', 'DeleteCell');
var rich_cx_name_$lang_name = Array('".$lang->item('InsertRow')."', '".$lang->item('DeleteRow')."', '".$lang->item('InsertColumn')."',
                        '".$lang->item('DeleteColumn')."', '".$lang->item('InsertCell')."', '".$lang->item('DeleteCell')."');
var rich_cx_image_$lang_name = Array('insrow', 'delrow', 'inscol',
                        'delcol', 'inscell', 'delcell');
var rich_cx_length_$lang_name = rich_cx_item_".$lang_name.".length;
var $cx_name = Array();
".$cx_name."['TABLE'] = Array('".$lang->item('EditTable')."', 'CreateTable', 'table');
".$cx_name."['IMG'] = Array('".$lang->item('EditImage')."', 'CreateImage', 'image');
".$cx_name."['OBJECT'] = Array('".$lang->item('EditFlash')."', 'CreateFlash', 'flash');
".$cx_name."['HIDDEN'] = Array('".$lang->item('EditHidden')."', 'CreateHidden', 'hidden');
".$cx_name."['TEXT'] = ".$cx_name."['PASSWORD'] =
Array('".$lang->item('EditText')."', 'CreateText', 'text');
".$cx_name."['BUTTON'] = ".$cx_name."['RESET'] = ".$cx_name."['SUBMIT'] =
Array('".$lang->item('EditButton')."', 'CreateButton', 'button');
".$cx_name."['SELECT'] = Array('".$lang->item('EditSelect')."', 'CreateSelect', 'select');
".$cx_name."['CHECKBOX'] = Array('".$lang->item('EditCheckBox')."', 'CreateCheckBox', 'checkbox');
".$cx_name."['RADIO'] = Array('".$lang->item('EditButton')."', 'CreateRadio', 'radio');
".$cx_name."['TEXTAREA'] = Array('".$lang->item('EditTextArea')."', 'CreateTextArea', 'textarea');
".$cx_name."['CUT'] = '".$lang->item('Cut')."';
".$cx_name."['COPY'] = '".$lang->item('Copy')."';
".$cx_name."['PASTE'] = '".$lang->item('Paste')."';
".$cx_name."['PASTEWORD'] = '".$lang->item('PasteWord')."';
".$cx_name."['ADDIMAGE'] = '".$lang->item('CreateImage')."';
".$cx_name."['ADDLINK'] = '".$lang->item('CreateLink')."';
".$cx_name."['EDITLINK'] = '".$lang->item('EditLink')."';
".$cx_name."['EDITANCHOR'] = '".$lang->item('EditAnchor')."';
".$cx_name."['ADDFLASH'] = '".$lang->item('CreateFlash')."';
".$cx_name."['EDITFORM'] = '".$lang->item('EditForm')."';
".$cx_name."['ADDTABLE'] = '".$lang->item('CreateTable')."';
".$cx_name."['EDITCELL'] = '".$lang->item('EditCell')."';
".$cx_name."['EDITROW'] = '".$lang->item('EditRow')."';
".$cx_name."['MERGE'] = '".$lang->item('MergeCells')."';
".$cx_name."['SPLIT'] = '".$lang->item('SplitCell')."';
".$cx_name."['MERGEDOWN'] = '".$lang->item('MergeCellsDown')."';
".$cx_name."['SPLITDOWN'] = '".$lang->item('SplitCellDown')."';
        ";

    }

  function get_rich(){
    $browser_info = rich_get_browser_info();
    $rich_browser = $browser_info['rich_browser'];
//  global $rich_browser;

    if ($rich_browser != 'msie') {
        return $this->get_rich_ns();
    }

//    global $class_path;
//    global $rich_flag;
//    global $rich_lang;

    $class_path = $this->get_class_path();
    $rich_flag = rich_get_rich_flag();

    $files_path = $this->files_path;
//  if($this->absolute_path) $this->files_path = "../".$files_path;
//      else $this->files_path = $_SERVER['DOCUMENT_ROOT'].$files_path;
//  $this->files_path = $_SERVER['DOCUMENT_ROOT'].$files_path;

    //name of textarea element for sending text from form
    $name_area = $this->name;
/*
    if (ereg("^([^\[]+)\[(.+)\]", $this->name, $regs)) {
        $this->name = $regs[1];
        $id = $regs[2];
    } else $id = '';
*/

    if (ereg("^([^\[]+)\[(.+)\]$", $this->name, $regs)) {
        $this->name = $regs[1];
        $parts = split('\]\[', $regs[2]);
        $id = implode('_', $parts);
    } else $id = '';

    $name = $this->name."_ed".$id; //editor name
    $name_id = $name."_id";    //id of editor element

    //text data in current language
    $lang = new rich_lang($this->settings['lang']);

    //set document charset settings
    if (!$this->settings['doc_lang']) $this->settings['doc_lang'] = $this->settings['lang'];
    if (!$this->settings['doc_charset']) $this->settings['doc_charset'] = $lang->item('Charset');

    $result = '';

    if($this->caption) $result .= $this->caption.':<br />';

    $user_agent = @$_SERVER["HTTP_USER_AGENT"];
    if (!$user_agent) $user_agent = $GLOBALS["HTTP_USER_AGENT"];

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ||
        isset($GLOBALS['HTTPS']) && $GLOBALS['HTTPS'] ==  'on') $is_https = true;
    else $is_https = false;

    //$is_msie == true, if current brouser is MSIE
    if(ereg("MSIE ([0-9|\.]+)", $user_agent, $regs) &&
       ereg("Win", $user_agent)){
      $is_msie = true;
      $msie_version = (double)$regs[1];
    }else $is_msie = false;

    $value = $this->value;
    if ($this->settings['editable_regions'] && $msie_version >= 5.5) {
        $value = ereg_replace('(<!-- #BeginEditable "[^\"]*" -->)', '<div id="re_editable_region">\\1', $value);
        $value = ereg_replace('(<!-- #EndEditable -->)', '\\1</div>', $value);
    }

    $value = $this->convert_php_tags($value);

    if(!$this->page_mode && $is_msie) $value = '<body>'.$value.'</body>';
    $this->value = htmlspecialchars($value);

    if(!$rich_flag){
      $result .= '<iframe'.($is_https?' src="'.$class_path.'rich_files/images/space.gif"':'').' id="rich_fs_iframe" style="display:none;z-index:999"></iframe>';
    }

    $result .= '<!-- editor body -->

<div id="'.$name.'_div_id" name="rich_table">
<table id="'.$name.'_table_id" border="1" cellspacing="0" cellpadding="0" width="'.$this->width.'" height="'.$this->height.'" class="re_table">
';

    if($is_msie && (!$this->settings['editable_regions'] || $msie_version >= 5.5)){

        $result .= '<tr><td>

<!-- toolbar -->
<table bgcolor="buttonface" border="0" cellspacing="0" cellpadding="0" width="100%">
<tr onmousedown="mouse_down(true);" onmouseup="mouse_down(false);" onmouseover="mouse_over(true);" onmouseout="mouse_over(false);" ondragstart="return false;"><td class="re_toolbar">
        ';

        if($this->is_visible('save')){
            $result .= '
  <img id="Save_'.$name.'" onclick="active_rich = '.$name_id.'; '.str_replace('"', "'", $this->settings['on_save']).'" alt="'.$lang->item('Save').'" src="'.$class_path.'rich_files/images/save.gif" align="absMiddle" width="20" height="20" />
            ';
        }

        if ($this->is_visible('fullscreen') && $msie_version >= 5.5 ||
            $this->is_visible('preview') || $this->is_visible('find')) {

            $result .= '<nobr>';
            if($this->is_visible('fullscreen') && $msie_version >= 5.5){
                $result .= $this->get_tb_action_icon("FullScreen", "fullscreen", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('preview')) {
                $result .= $this->get_tb_dialog_icon("Preview", "preview", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('find')) {
                $result .= $this->get_tb_dialog_icon("Find", "find", $name,
                                                    $name_id, $lang);
            }

            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('clipboard')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon("Cut", "cut", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("Copy", "copy", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("Paste", "paste", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('history')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon("Undo", "undo", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("Redo", "redo", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('text')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon("Bold", "bold", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("Italic", "italic", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("Underline", "underline", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("Strikethrough", "strikethrough", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon("SuperScript", "superscript", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("SubScript", "subscript", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('align')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon("JustifyLeft", "left", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("JustifyCenter", "center", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("JustifyRight", "right", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("JustifyFull", "justify", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('list') || $this->is_visible('indent')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            if ($this->is_visible('list')) {
                $result .= $this->get_tb_action_icon("InsertOrderedList", "numlist", $name,
                                                    $name_id, $lang);
                $result .= $this->get_tb_action_icon("InsertUnorderedList", "bullist", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('indent')) {
                $result .= $this->get_tb_action_icon("Outdent", "outdent", $name,
                                                    $name_id, $lang);
                $result .= $this->get_tb_action_icon("Indent", "indent", $name,
                                                    $name_id, $lang);
            }

            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('hr') || $this->is_visible('remove_format')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            if ($this->is_visible('hr')) {
                $result .= $this->get_tb_action_icon("InsertHorizontalRule", "h_line", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('remove_format')) {
                $result .= $this->get_tb_action_icon("RemoveFormat", "rem_format", $name,
                                                    $name_id, $lang);
            }

            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('table')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_dialog_icon("CreateTable", "table", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_dialog_icon("EditTable", "table_prop", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("InsertRow", "insrow", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("DeleteRow", "delrow", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("InsertColumn", "inscol", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("DeleteColumn", "delcol", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('table') && $this->is_visible('adv_table')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon("InsertCell", "inscell", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("DeleteCell", "delcell", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("MergeCells", "mergecells", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("SplitCell", "splitcell", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("MergeCellsDown", "mergecellsdown", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("SplitCellDown", "splitcelldown", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('form')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_dialog_icon("CreateForm", "form", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_dialog_icon("CreateText", "text", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_dialog_icon("CreateTextArea", "textarea", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_dialog_icon("CreateButton", "button", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_dialog_icon("CreateSelect", "select", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_dialog_icon("CreateHidden", "hidden", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_dialog_icon("CreateCheckBox", "checkbox", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_dialog_icon("CreateRadio", "radio", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('paragraph')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
  <span class="re_text">'.$lang->item('FormatBlock').':</span>
<select onmousedown="active_rich = '.$name_id.'; this.disabled = true; re_show_list(this, true);" class="re_text" id="FormatBlock_'.$name.'">
            ';

            $result .= '
  <option value="<P>">Normal (P)</option>
  <option value="<H1>">Heading 1 (H1)</option>
  <option value="<H2>">Heading 2 (H2)</option>
  <option value="<H3>">Heading 3 (H3)</option>
  <option value="<H4>">Heading 4 (H4)</option>
  <option value="<H5>">Heading 5 (H5)</option>
  <option value="<H6>">Heading 6 (H6)</option>
  <option value="<PRE>">Preformatted (PRE)</option>
            ';

            $result .= '
</select>
<iframe'.($is_https?' src="'.$class_path.'rich_files/images/space.gif"':'').' width="263" height="100" id="FormatBlock_'.$name.'_iframe" class="re_list_iframe" style="position: absolute; display:none; z-index: 10000;" frameborder="0" unselectable="on"></iframe>
</nobr>
            ';
        }

        if ($this->is_visible('font')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
  <span class="re_text">'.$lang->item('FontName').':</span>
<select onmousedown="active_rich = '.$name_id.'; this.disabled = true; re_show_list(this, true);" class="re_text" id="FontName_'.$name.'">
            ';

            if (count($this->font_list)) {
                foreach ($this->font_list as $k=>$val) {
                    $result .= '  <option value="'.$val.'">'.$val.'</option>'."\n";
                }
            } else {
                $result .= '
  <option value="arial">Arial</option>
  <option value="arial black">Arial Black</option>
  <option value="garamond">Garamond</option>
  <option value="comic sans ms">Comic Sans MS</option>
  <option value="courier">Courier</option>
  <option value="courier new">Courier New</option>
  <option value="symbol">Symbol</option>
  <option value="times new roman" selected>Times New Roman</option>
  <option value="verdana">Verdana</option>
  <option value="webdings">Webdings</option>
  <option value="wingdings">Wingdings</option>
                ';
            }

            $result .= '
</select>
<iframe'.($is_https?' src="'.$class_path.'rich_files/images/space.gif"':'').' width="165" height="100" id="FontName_'.$name.'_iframe" class="re_list_iframe" style="position: absolute; display:none; z-index: 10000;" frameborder="0" unselectable="on"></iframe>
</nobr>
            ';
        }

        if ($this->is_visible('style')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
<span class="re_text">'.$lang->item('ClassName').':</span>
<select onmousedown="active_rich = '.$name_id.'; set_stylesheet_rules(); this.disabled = true; re_show_list(this);" class="re_text" id="ClassName_'.$name.'">
  <option value=""></option>
</select>
<iframe'.($is_https?' src="'.$class_path.'rich_files/images/space.gif"':'').' width="130" height="200" ini_height="200" id="ClassName_'.$name.'_iframe" class="re_list_iframe" style="position: absolute; display:none; z-index: 10000;" frameborder="0" unselectable="on"></iframe>
</nobr>
            ';
        }

        if ($this->is_visible('size')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
  <span class="re_text">'.$lang->item('FontSize').':</span>
<select onmousedown="active_rich = '.$name_id.'; this.disabled = true; re_show_list(this, true);" class="re_text" id="FontSize_'.$name.'">
            ';

            if (count($this->font_size_list)) {
                foreach ($this->font_size_list as $k=>$val) {
                    $result .= '  <option value="'.$val.'">'.$val.'</option>'."\n";
                }
            } else {
                $result .= '
  <option value=""></option>
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3" selected>3</option>
  <option value="4">4</option>
  <option value="5">5</option>
  <option value="6">6</option>
  <option value="7">7</option>
                ';
            }

            $result .= '
</select>
<iframe'.($is_https?' src="'.$class_path.'rich_files/images/space.gif"':'').' width="72" height="100" id="FontSize_'.$name.'_iframe" class="re_list_iframe" style="position: absolute; display:none; z-index: 10000;" frameborder="0" unselectable="on"></iframe>
</nobr>
            ';
        }

        if ($this->is_visible('color')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon("ForeColor", "fgcolor", $name,
                                                $name_id, $lang);
            $result .= $this->get_tb_action_icon("BackColor", "bgcolor", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('image') || $this->is_visible('flash') ||
            $this->is_visible('link') || $this->is_visible('anchor')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            if ($this->is_visible('image')) {
                $result .= $this->get_tb_dialog_icon("CreateImage", "image", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('flash')) {
                $result .= $this->get_tb_dialog_icon("CreateFlash", "flash", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('link')) {
                $result .= $this->get_tb_dialog_icon("CreateLink", "link", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('anchor')) {
                $result .= $this->get_tb_dialog_icon("CreateAnchor", "anchor", $name,
                                                    $name_id, $lang);
            }

            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('paste_word') || $this->is_visible('switch_borders') ||
            $this->is_visible('special_chars') ||
            $this->snippets && $this->is_visible('snippets') ||
            $this->is_visible('absolute_position') && $msie_version >= 5.5 ||
            $this->page_mode && $this->is_visible('page_properties')) {

            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            if ($this->is_visible('paste_word')) {
                $result .= $this->get_tb_action_icon("PasteWord", "paste_word", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('switch_borders')) {
                $result .= $this->get_tb_action_icon("SwitchBorders", "borders", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('special_chars')) {
                $result .= $this->get_tb_action_icon("InsertChar", "inschar", $name,
                                                    $name_id, $lang);
            }

            if ($this->snippets && $this->is_visible('snippets')) {
                $result .= $this->get_tb_dialog_icon("InsertSnippet", "snippet", $name,
                                                    $name_id, $lang);
            }

            if ($this->is_visible('absolute_position') && $msie_version >= 5.5) {
                $result .= $this->get_tb_action_icon("AbsolutePosition", "abspos", $name,
                                                    $name_id, $lang);
            }

            if ($this->page_mode && $this->is_visible('page_properties')) {
                $result .= $this->get_tb_dialog_icon("PageProperties", "page", $name,
                                                    $name_id, $lang);
            }

            if (isset($cms_curr_user) && $cms_curr_user && $cms_curr_user->id) {
                $result .= $this->get_tb_dialog_icon("CreateBlock", "block", $name,
                                                    $name_id, $lang);
            }

            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('help')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_dialog_icon("Help", "help", $name,
                                                $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('source')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
  <span class="re_text">'.$lang->item('Source').':</span><input type="checkbox" name="edit_mode" onClick="active_rich = '.$name_id.'; change_mode();" />
</nobr>
            ';
        }

        $result .= '

</td></tr>
</table>
<!-- toolbar -->

</td></tr>';

    }

    $result .= '

<tr><td height="100%">

<!-- text -->
    ';

    if ($is_msie) {
        $result .= '<iframe'.($is_https?' src="|'.$class_path.'rich_files/images/space.gif"':'').' class="re_editor" name="'.$name.'" id="'.$name_id.'" style="border: black thin; width:100%; height:100%">&nbsp;</iframe>';
    }

    $result .= '

<textarea name="'.$name_area.'" id="'.$name.'_area_id" rows="20" cols="60" style="';
    if($is_msie) $result .= 'display:none; ';
    $result .= 'border: black thin; height:'.$this->height.'; width:'.$this->width.'">'.$this->value.'</textarea>
<!-- text -->

</td></tr>
</table>
</div>
<!-- editor body -->
    ';

    if ($is_msie) {

        if (!$rich_flag) {
            $result .= '<iframe'.($is_https?' src="|'.$class_path.'rich_files/images/space.gif"':'').' id="rich_temp_iframe" width="1" height="1" style="width:1; height:1;">&nbsp;richarea.com</iframe>';
        }

        $result .= '

<script language="javascript">
    //settings
    '.$name.'_rich_mode = true;
    '.$name.'_rich_page_mode = '.(int)$this->page_mode.';
    '.$name.'_rich_border_mode = false;
    '.$name.'_rich_abspos_mode = false;
    '.$name.'_rich_active_mode = '.(int)$this->settings['active_tb'].';
    '.$name.'_prev_is_control = null;
    '.$name.'_rich_full_screen_mode = false;
    '.$name.'_rich_absolute_path = '.(int)$this->absolute_path.';
    '.$name.'_rich_xhtml_mode = '.(int)$this->settings['xhtml'].';
    '.$name.'_br_on_enter = '.(int)$this->settings['br_on_enter'].';
    '.$name.'_clean_paste = '.(int)$this->settings['clean_paste'].';

    '.$name.'_rich_doc_lang = "'.$this->settings['doc_lang'].'";
    '.$name.'_rich_doc_charset = "'.$this->settings['doc_charset'].'";

    '.$name.'_lang = "'.$this->settings['lang'].'";
    '.$name.'_files_path = "'.$this->files_path.'";
    '.$name.'_files_url = "'.$this->files_url.'";

    var '.$name.'_editable_regions = '.(int)$this->settings['editable_regions'].';
    var '.$name.'_indent = "'.$this->settings['indent'].'";

    //set initial content
    var '.$name.'_area_id = document.all.'.$name.'_area_id;

        ';

    if (!$this->settings['editable_regions']) $result .= $name.'_id.document.designMode = "On";';

        $result .= '

    '.$name.'_id.document.open();
    '.$name.'_id.document.write('.$name.'_area_id.value);
    '.$name.'_id.document.close();

    '.$name.'_id.document.body.dir = "'.$lang->item('Direction').'";
    var '.$name.'_rich_dir = "'.$lang->item('Direction').'";
    var '.$name.'_rich_mb = "'.$lang->item('Multibyte').'";

        ';

    if ($this->settings['editable_regions'] && $msie_version >= 5.5) {
        $result .= 'show_editable_regs('.$name.'_id);';
    }

        $result .='

    var '.$name.'_rich_css = Array();
        ';

        //draw stylesheet loading code
        if ($this->default_stylesheet) {
            $result .= "\n";
            foreach ($this->default_stylesheet as $k=>$val) {
                $result .= $name.'_id.document.createStyleSheet("'.$val.'");'."\n";
                $result .= $name.'_rich_css['.$k.'] = "'.$val.'";'."\n";
            }
        }

        if (!$rich_flag) {
            $result .= '
      var rich_msie_version = "'.$msie_version.'";
            ';

            //url of root dir of site
            if($is_https){
                $base_url = "https";
            }else{
                $base_url = "http";
            }
            $base_url .= '://'.$_SERVER["HTTP_HOST"];
            $result .= '
  var rich_base_url = "'.$base_url.'";
            ';

            $result .= '

    var rich_dialog_ext = "'.$this->dialog_ext.'";

            ';

            $result .= '
    var rich_sess_param = "';
            if (session_id()) {
                $result .= 're_session_id='.session_id();
            }
            $result .= '";
            ';

        }

        $result .= '

  active_rich = '.$name_id.';';

        if ($this->is_visible('style')) {
            $result .= 'set_stylesheet_rules();';
        }

        $result .= '
  add_handlers('.$name_id.');
        ';

        if ($this->settings['borders_visibility']) {
            $result .= '
    active_rich = '.$name_id.'; switch_borders(true);
            ';
        }

        //draw snippets
        $result .= "\n\n".'  var '.$name.'_snippets = new Array();'."\n";
        if ($this->snippets && $this->is_visible('snippets')) {
            foreach ($this->snippets as $k=>$val) {
                $result .= "\n".'       '.$name.'_snippets["'.$k.'"] = new Array();'."\n";
                foreach($val as $k2=>$val2){
                    $snippet_name = str_replace("'", "\'", $val2['name']);
                    $snippet_code = str_replace("'", "\'", $val2['code']);
                    $result .= '  '.$name.'_snippets["'.$k.'"]['.$k2.'] = Array(\''.$snippet_name.'\', \''.$snippet_code.'\');'."\n";
                }
            }
        }

        $lang_name = $this->settings['lang'];
//      if (!isset($rich_lang[$lang_name])) {
//          $rich_lang[$lang_name] = true;
        if (!rich_get_rich_lang($lang_name)) {
            //add context menu data
            $result .= $this->get_context_menu_data($lang);
        }

    }

    if ($is_msie) {
        if (!$rich_flag) {

            $result .= '
/* copyright

Rich Editor, Version 2.1
Copyright (c) 2002-2006 V. Smolin All rights reserved.
http://www.richarea.com
re@richarea.com

copyright */
            ';

            $result .= '
      rich_temp_iframe.document.designMode = "On";

      var rich_path = "'.$class_path.'rich_files/";

            ';

            $result .= 'var rich_popup = ';
            if ($msie_version >= 5.5){
                $result .= 'window.createPopup();
                ';
            }else{
                $result .= 'null;
                ';
            }

//          $rich_flag = true;
            $rich_flag = rich_get_rich_flag(1);
        }
    }

    $result .= '</script>';

    $result .= '
<!-- copyright

Rich Editor, Version 2.1
Copyright (c) 2002-2006 V. Smolin All rights reserved.
http://www.richarea.com
re@richarea.com

copyright -->
    ';

    return $result;
  }



  function get_rich_ns(){

//    global $class_path;
//    global $rich_flag;
//    global $rich_lang;

    $class_path = $this->get_class_path();
    $rich_flag = rich_get_rich_flag();

    $files_path = $this->files_path;
//  if($this->absolute_path) $this->files_path = "../".$files_path;
//      else $this->files_path = $_SERVER['DOCUMENT_ROOT'].$files_path;
//  $this->files_path = $_SERVER['DOCUMENT_ROOT'].$files_path;

    //name of textarea element for sending text from form
    $name_area = $this->name;
/*
    if (ereg("^([^\[]+)\[(.+)\]", $this->name, $regs)) {
        $this->name = $regs[1];
        $id = $regs[2];
    } else $id = '';
*/

    if (ereg("^([^\[]+)\[(.+)\]$", $this->name, $regs)) {
        $this->name = $regs[1];
        $parts = split('\]\[', $regs[2]);
        $id = implode('_', $parts);
    } else $id = '';

    $name = $this->name."_ed".$id; //editor name
    $name_id = $name."_id";    //id of editor element

    //text data in current language
    $lang = new rich_lang($this->settings['lang']);

    //set document charset settings
    if (!$this->settings['doc_lang']) $this->settings['doc_lang'] = $this->settings['lang'];
    if (!$this->settings['doc_charset']) $this->settings['doc_charset'] = $lang->item('Charset');

    $result = '';

    if($this->caption) $result .= $this->caption.":<br />";

    $user_agent = @$_SERVER["HTTP_USER_AGENT"];
    if(!$user_agent) $user_agent = $GLOBALS["HTTP_USER_AGENT"];

    if(ereg("Mozilla/([0-9|\.]+)", $user_agent, $regs) &&
       ereg("Gecko", $user_agent) && (double)$regs[1]>=5.0 &&
    ereg("rv:([0-9|\.]+)", $user_agent, $v_regs) &&
    (double)$v_regs[1]>=1.4){
      $is_ns = true;
      $msie_version = (double)$regs[1];

        if(ereg('Netscape', $user_agent, $regs)) $is_netscape = true;
            else $is_netscape = false;
        $is_netscape = true;

    } else {
        $is_ns = false;
        $is_netscape = false;
    }

    $value = $this->value;

    $value = $this->convert_php_tags($value);

    if ($is_ns && !$value) $value = '<br />';

    if(!$this->page_mode && $is_ns){
        $value = '<html><head></head><body>'.$value.'</body></html>';
    }

    $this->value = htmlspecialchars($value);

    if(!$rich_flag){
      $result .= '<iframe id="rich_fs_iframe" style="display:none;z-index:999"></iframe>';
    }

    $result .= '<!-- editor body -->

<div id="'.$name.'_div_id" name="rich_table">
<table id="'.$name.'_table_id" border="1" cellspacing="0" cellpadding="0" width="'.$this->width.'" height="'.$this->height.'" class="re_table">
';

    if ($is_ns && !$this->settings['editable_regions']) {

        $result .= '<tr><td vAlign="top">

<!-- toolbar -->
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr id="'.$name.'_tb_id" ondragstart="return false;"><td class="re_toolbar">
        ';

        if ($this->is_visible('save')) {
            $result .= '
  <img id="Save_'.$name.'" onmousedown="mouse_down(true, this);" onmouseup="mouse_down(false, this);" onclick="active_rich = '.$name_id.'; '.str_replace('"', "'", $this->settings['on_save']).'" title="'.$lang->item('Save').'" src="'.$class_path.'rich_files/images/save.gif" align="absMiddle" width="20" height="20" onmouseover="mouse_over(true, this);" onmouseout="mouse_over(false, this);" />
            ';
        }

        if ($this->is_visible('fullscreen') ||
            $this->is_visible('preview') || $this->is_visible('find')) {

            $result .= '<nobr>';
            if ($this->is_visible('fullscreen')) {
                $result .= $this->get_tb_action_icon_ns("FullScreen", "fullscreen", $name,
                                                    $name_id, $lang);
            }

//    if($this->is_visible('preview') || $this->is_visible('find')){

//      $result .= '<nobr>';
            if ($this->is_visible('preview')) {
                $result .= $this->get_tb_dialog_icon_ns("Preview", "preview", $name,
                                                    $name_id, $lang);
            }
            if($this->is_visible('find')){
          $result .= '
  <img id="Find_'.$name.'" onmousedown="mouse_down(true, this);" onmouseup="mouse_down(false, this);" onclick="active_rich = '.$name_id.'; show_dialog(\'Find\')" title="'.$lang->item('Find').'" src="'.$class_path.'rich_files/images/find.gif" align="absMiddle" width="20" height="20" onmouseover="mouse_over(true, this);" onmouseout="mouse_over(false, this);" />
          ';
            }
            $result .= '</nobr>
            ';

        }

        if ($this->is_visible('history')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon_ns("Undo", "undo", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("Redo", "redo", $name,
                                                    $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('text')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon_ns("Bold", "bold", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("Italic", "italic", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("Underline", "underline", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("Strikethrough", "strikethrough", $name,
                                                    $name_id, $lang);
            $result .= '
</nobr>
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon_ns("SuperScript", "superscript", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("SubScript", "subscript", $name,
                                                    $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('align')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon_ns("JustifyLeft", "left", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("JustifyCenter", "center", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("JustifyRight", "right", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("JustifyFull", "justify", $name,
                                                    $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('list') || $this->is_visible('indent')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            if ($this->is_visible('list')) {
                $result .= $this->get_tb_action_icon_ns("InsertOrderedList", "numlist", $name,
                                                        $name_id, $lang);
                $result .= $this->get_tb_action_icon_ns("InsertUnorderedList", "bullist", $name,
                                                        $name_id, $lang);
            }

            if ($this->is_visible('indent')) {
                $result .= $this->get_tb_action_icon_ns("Outdent", "outdent", $name,
                                                        $name_id, $lang);
                $result .= $this->get_tb_action_icon_ns("Indent", "indent", $name,
                                                        $name_id, $lang);
            }

            $result .= '
</nobr>
            ';
        }

        if( $this->is_visible('hr') || $this->is_visible('remove_format')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            if ($this->is_visible('hr')) {
                $result .= $this->get_tb_action_icon_ns("InsertHorizontalRule", "h_line", $name,
                                                        $name_id, $lang);
            }

            if ($this->is_visible('remove_format')) {
                $result .= $this->get_tb_action_icon_ns("RemoveFormat", "rem_format", $name,
                                                        $name_id, $lang);
            }

            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('table')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_dialog_icon_ns("CreateTable", "table", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_dialog_icon_ns("EditTable", "table_prop", $name,
                                                    $name_id, $lang);
//          $result .= $this->get_tb_dialog_icon_ns("EditCell", "cell_prop", $name,
//                                                  $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("InsertRow", "insrow", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("DeleteRow", "delrow", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("InsertColumn", "inscol", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("DeleteColumn", "delcol", $name,
                                                    $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('table') && $this->is_visible('adv_table')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon_ns("InsertCell", "inscell", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("DeleteCell", "delcell", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("MergeCells", "mergecells", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("SplitCell", "splitcell", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("MergeCellsDown", "mergecellsdown", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("SplitCellDown", "splitcelldown", $name,
                                                    $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('form')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_dialog_icon_ns("CreateForm", "form", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_dialog_icon_ns("CreateText", "text", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_dialog_icon_ns("CreateTextArea", "textarea", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_dialog_icon_ns("CreateButton", "button", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_dialog_icon_ns("CreateSelect", "select", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_dialog_icon_ns("CreateHidden", "hidden", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_dialog_icon_ns("CreateCheckBox", "checkbox", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_dialog_icon_ns("CreateRadio", "radio", $name,
                                                    $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('paragraph')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
  <span class="re_text">'.$lang->item('FormatBlock').':</span>
<select onmousedown="active_rich = '.$name_id.'; this.disabled = true; re_show_list(this, true); setTimeout(\'re_enable(\\\'FormatBlock_'.$name.'\\\')\', 100);" class="re_text" id="FormatBlock_'.$name.'">
  <option value=""></option>';

            $result .= '
  <option value="<p>">Normal (P)</option>
  <option value="<h1>">Heading 1 (H1)</option>
  <option value="<h2>">Heading 2 (H2)</option>
  <option value="<h3>">Heading 3 (H3)</option>
  <option value="<h4>">Heading 4 (H4)</option>
  <option value="<h5>">Heading 5 (H5)</option>
  <option value="<h6>">Heading 6 (H6)</option>
  <option value="<pre>">Preformatted (PRE)</option>';

            $result .= '
</select>
<iframe width="211" height="100" id="FormatBlock_'.$name.'_iframe" class="re_list_iframe" style="position: absolute; display:none; border: 1px solid #000000; z-index: 10000;" frameborder="0">&nbsp;</iframe>
</nobr>
            ';
        }

        if ($this->is_visible('font')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
  <span class="re_text">'.$lang->item('FontName').':</span>
<select onmousedown="active_rich = '.$name_id.'; this.disabled = true; re_show_list(this, true); setTimeout(\'re_enable(\\\'FontName_'.$name.'\\\')\', 100);" class="re_text" id="FontName_'.$name.'">
  <option value="" selected></option>
            ';

            if (count($this->font_list)) {
                foreach ($this->font_list as $k=>$val) {
                    $result .= '  <option value="'.strtolower($val).'">'.$val.'</option>'."\n";
                }
            } else {
                $result .= '
  <option value="arial">Arial</option>
  <option value="arial black">Arial Black</option>
  <option value="garamond">Garamond</option>
  <option value="comic sans ms">Comic Sans MS</option>
  <option value="courier">Courier</option>
  <option value="courier new">Courier New</option>
  <option value="symbol">Symbol</option>
  <option value="times new roman">Times New Roman</option>
  <option value="verdana">Verdana</option>
  <option value="webdings">Webdings</option>
  <option value="wingdings">Wingdings</option>
                ';
            }

            $result .= '
</select>
<iframe width="121" height="100" id="FontName_'.$name.'_iframe" class="re_list_iframe" style="position: absolute; display:none; border: 1px solid #000000; z-index: 10000;" frameborder="0">&nbsp;</iframe>
</nobr>
            ';
        }

        if ($this->is_visible('style')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
  <span class="re_text">'.$lang->item('ClassName').':</span>
<select onmousedown="active_rich = '.$name_id.'; set_stylesheet_rules(); this.disabled = true; re_show_list(this); setTimeout(\'re_enable(\\\'ClassName_'.$name.'\\\')\', 100);" class="re_text" id="ClassName_'.$name.'">
  <option value=""></option>
</select>
<iframe width="130" height="200" ini_height="200" id="ClassName_'.$name.'_iframe" class="re_list_iframe" style="position: absolute; display:none; border: 1px solid #000000; z-index: 10000;" frameborder="0">&nbsp;</iframe>
</nobr>
            ';
        }

        if ($this->is_visible('size')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
  <span class="re_text">'.$lang->item('FontSize').':</span>
<select onmousedown="active_rich = '.$name_id.'; this.disabled = true; re_show_list(this, true); setTimeout(\'re_enable(\\\'FontSize_'.$name.'\\\')\', 100);" class="re_text" id="FontSize_'.$name.'">
  <option value="" selected></option>
            ';

            if (count($this->font_size_list)) {
                foreach ($this->font_size_list as $k=>$val) {
                    $result .= '  <option value="'.$val.'">'.$val.'</option>'."\n";
                }
            } else {
                $result .= '
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3">3</option>
  <option value="4">4</option>
  <option value="5">5</option>
  <option value="6">6</option>
  <option value="7">7</option>
                ';
            }

            $result .= '
</select>
<iframe width="58" height="100" id="FontSize_'.$name.'_iframe" class="re_list_iframe" style="position: absolute; display:none; border: 1px solid #000000; z-index: 10000;" frameborder="0">&nbsp;</iframe>
</nobr>
            ';
        }

        if ($this->is_visible('color')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_action_icon_ns("ForeColor", "fgcolor", $name,
                                                    $name_id, $lang);
            $result .= $this->get_tb_action_icon_ns("HiliteColor", "bgcolor", $name,
                                                    $name_id, $lang);
            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('image') || $this->is_visible('link') ||
            $this->is_visible('anchor')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            if ($this->is_visible('image')) {
                $result .= $this->get_tb_dialog_icon_ns("CreateImage", "image", $name,
                                                        $name_id, $lang);
            }

            if ($this->is_visible('link')) {
                $result .= $this->get_tb_dialog_icon_ns("CreateLink", "link", $name,
                                                        $name_id, $lang);
            }

            if ($this->is_visible('anchor')) {
                $result .= $this->get_tb_dialog_icon_ns("CreateAnchor", "anchor", $name,
                                                    $name_id, $lang);
            }

            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('paste_word') || $this->is_visible('switch_borders') ||
            $this->is_visible('special_chars') ||
            $this->is_visible('snippets') ||
            $this->page_mode && $this->is_visible('page_properties')){
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            if ($this->is_visible('paste_word')) {
                $result .= $this->get_tb_action_icon_ns("PasteWord", "paste_word", $name,
                                                        $name_id, $lang);
            }

            if ($this->is_visible('switch_borders')) {
                $result .= $this->get_tb_action_icon_ns("SwitchBorders", "borders", $name,
                                                        $name_id, $lang);
            }

            if ($this->is_visible('special_chars')) {
                $result .= $this->get_tb_action_icon_ns("InsertChar", "inschar", $name,
                                                        $name_id, $lang);
            }

            if ($this->snippets && $this->is_visible('snippets')) {
                $result .= $this->get_tb_dialog_icon_ns("InsertSnippet", "snippet", $name,
                                                        $name_id, $lang);
            }

            if ($this->page_mode && $this->is_visible('page_properties')) {
                $result .= $this->get_tb_dialog_icon_ns("PageProperties", "page", $name,
                                                        $name_id, $lang);
            }

            if (isset($cms_curr_user) && $cms_curr_user && $cms_curr_user->id) {
                $result .= $this->get_tb_dialog_icon_ns("CreateBlock", "block", $name,
                                                        $name_id, $lang);
            }

            $result .= '
</nobr>
            ';
        }

        if ($this->is_visible('help')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();

            $result .= $this->get_tb_dialog_icon_ns("Help", "help", $name,
                                                    $name_id, $lang);
            $result .= '
</nobr>';
        }

        if ($this->is_visible('source')) {
            $result .= '
<nobr>
            ';
            $result .= $this->get_delimiter();
            $result .= '
  <span class="re_text">'.$lang->item('Source').':</span><input type="checkbox" name="edit_mode" id="Source_'.$name.'" onClick="active_rich = '.$name_id.'; change_mode();" />
</nobr>
            ';
        }

        $result .= '

</td></tr>
</table>
<!-- toolbar -->

</td></tr>';

    }


    $result .= '

<tr><td height="100%">

<!-- text -->
    ';

    $iframe_width = ($is_netscape?$this->width.'px':'100%');
    $iframe_height = ($is_netscape?($this->height-31>0?($this->height-31).'px':$this->height.'px'):'100%');
    $iframe_width = '100%';
    $iframe_height = '100%';

    if ($is_ns) {

        $result .= '<iframe class="re_editor" name="'.$name.'" id="'.$name_id.'" width="'.$iframe_width.'" height="'.$iframe_height.'" style="border: black thin; width:'.$iframe_width.'; height:'.$iframe_height.';"';
        $result .= ' onload="var re_src_obj = document.getElementById(\'Source_'.$name.'\'); if (re_src_obj) re_src_obj.checked=\'\';';
        if ($this->settings['borders_visibility']) {
            $result .= ' active_rich = '.$name_id.'; switch_borders(true);';
        }
        $result .= '"';
        $result .= '>&nbsp;</iframe>';
    }

    $result .= '
<textarea name="'.$name_area.'" id="'.$name.'_area_id" style="';
    if($is_ns) $result .= 'display:none; ';
    $result .= 'border: 0px; padding: 0; margin: 0; width:'.$iframe_width.'; height:'.$iframe_height.';'.($lang->item('Direction')!=''?' direction:'.$lang->item('Direction'):'').'">
'.$this->value.'</textarea>
<!-- text -->

</td></tr>
</table>
</div>
<!-- editor body -->
';

    if ($is_ns) {

        if (!$rich_flag) {
            $result .= '<iframe id="rich_temp_iframe" style="width:1px; height:1px; border:1px;">&nbsp;</iframe>';
            $result .= '<div id="re_context_div_id" style="display:none;position:absolute;z-Index:1000"></div>';
        }

        $result .= '

<script language="javascript">
    //settings
    var '.$name.'_rich_mode = true;
    var '.$name.'_rich_page_mode = '.(int)$this->page_mode.';
    var '.$name.'_rich_border_mode = false;
    var '.$name.'_rich_abspos_mode = false;
    var '.$name.'_rich_active_mode = '.(int)$this->settings['active_tb'].';
    var '.$name.'_prev_is_control = null;
    var '.$name.'_rich_full_screen_mode = false;
    var '.$name.'_rich_absolute_path = '.(int)$this->absolute_path.';
    var '.$name.'_rich_xhtml_mode = '.(int)$this->settings['xhtml'].';
    var '.$name.'_br_on_enter = '.(int)$this->settings['br_on_enter'].';
    var '.$name.'_clean_paste = '.(int)$this->settings['clean_paste'].';

    var '.$name.'_rich_doc_lang = "'.$this->settings['doc_lang'].'";
    var '.$name.'_rich_doc_charset = "'.$this->settings['doc_charset'].'";

    var '.$name.'_lang = "'.$this->settings['lang'].'";
    var '.$name.'_files_path = "'.$this->files_path.'";
    var '.$name.'_files_url = "'.$this->files_url.'";

    var '.$name.'_editable_regions = '.(int)$this->settings['editable_regions'].';
    var '.$name.'_indent = "'.$this->settings['indent'].'";

    var '.$name.'_first_click = 1;

    //set initial content
    var '.$name.'_area_id = document.getElementById("'.$name.'_area_id");

    '.$name.'_id = document.getElementById("'.$name.'_id").contentWindow;

        ';

        if (!$this->settings['editable_regions']) $result .= $name.'_id.document.designMode = "On";';

        $result .= '

    '.$name.'_id.document.open();
    '.$name.'_id.document.write('.$name.'_area_id.value);
    '.$name.'_id.document.close();
//  '.$name.'_id.document.designMode = "On";

    '.$name.'_id.document.dir = "'.$lang->item('Direction').'";
    var '.$name.'_rich_dir = "'.$lang->item('Direction').'";
    var '.$name.'_rich_mb = "'.$lang->item('Multibyte').'";

    var '.$name.'_rich_css = Array();
        ';

        //draw stylesheet loading code
        if ($this->default_stylesheet) {
            $result .= "\n";
            $result .= 'var head_tag = '.$name.'_id.document.getElementsByTagName(\'HEAD\')[0];'."\n";
            foreach ($this->default_stylesheet as $k=>$val) {
//          $result .= $name.'_id.document.createStyleSheet("'.$val.'");'."\n";
                $result .= 'var rich_sh_temp = \'<link rel="stylesheet" href="'.$val.'" type="text/css">\''."\n";
//          $result .= $name.'_id.document.body.innerHTML = rich_sh_temp+'.$name.'_id.document.body.innerHTML;'."\n";
                $result .= 'head_tag.innerHTML += rich_sh_temp;'."\n";
                $result .= $name.'_rich_css['.$k.'] = "'.$val.'";'."\n";
            }

        }


        if (!$rich_flag) {

            //url of root dir of site
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ||
                isset($GLOBALS['HTTPS']) && $GLOBALS['HTTPS'] ==  'on') $base_url = "https";
            else $base_url = "http";

            $base_url .= '://'.$_SERVER["HTTP_HOST"];
            $result .= '
    var rich_base_url = "'.$base_url.'";
            ';

            $result .= '

    var rich_dialog_ext = "'.$this->dialog_ext.'";

            ';

            $result .= '
    var rich_sess_param = "';
            if (session_id()) {
                $result .= 're_session_id='.session_id();
            }
            $result .= '";
            ';

            $result .= '

    var rich_fs_mode_on = false;

            ';

        }

        $result .= '

  active_rich = '.$name_id.';';

        if ($this->is_visible('style')) {
            $result .= 'set_stylesheet_rules();';
        }

        $result .= '
  add_handlers(document.getElementById(\''.$name_id.'\').contentWindow);
        ';

        //draw snippets
        $result .= "\n\n".'  var '.$name.'_snippets = new Array();'."\n";
        if ($this->snippets && $this->is_visible('snippets')) {
            foreach ($this->snippets as $k=>$val) {
                $result .= "\n".'       '.$name.'_snippets["'.$k.'"] = new Array();'."\n";
                foreach ($val as $k2=>$val2) {
                    $snippet_name = str_replace("'", "\'", $val2['name']);
                    $snippet_code = str_replace("'", "\'", $val2['code']);
                    $result .= '  '.$name.'_snippets["'.$k.'"]['.$k2.'] = Array(\''.$snippet_name.'\', \''.$snippet_code.'\');'."\n";
                }
            }

        }

        $lang_name = $this->settings['lang'];
//      if(!isset($rich_lang[$lang_name])){
//          $rich_lang[$lang_name] = true;
//      }
        if(!rich_get_rich_lang($lang_name)){
            //add context menu data
            $result .= $this->get_context_menu_data($lang);
        }

    }

    if ($is_ns) {
        if (!$rich_flag) {

            $result .= '
/* copyright

Rich Editor, Version 2.1
Copyright (c) 2002-2006 V. Smolin All rights reserved.
http://www.richarea.com
re@richarea.com

copyright */
            ';

            $result .= '
//    rich_temp_iframe.document.designMode = "On";

      var rich_path = "'.$class_path.'rich_files/";

            ';

//          $rich_flag = true;
            $rich_flag = rich_get_rich_flag(1);
        }
    }

    $result .= '</script>';

    $result .= '
<!-- copyright

Rich Editor, Version 2.1
Copyright (c) 2002-2006 V. Smolin All rights reserved.
http://www.richarea.com
re@richarea.com

copyright -->
    ';

    return $result;
  }

    function convert_php_tags($content){
        $content = str_replace('<?php', '<!--re_php_tag_open', $content);
        $content = str_replace('?>', 're_php_tag_close-->', $content);

        return $content;
    }
}

//returns information about current browser
function rich_get_browser_info() {
    $browser_info = array();

    $user_agent = @$_SERVER["HTTP_USER_AGENT"];
    if(!$user_agent) $user_agent = $GLOBALS["HTTP_USER_AGENT"];

    //determine browser type
    $rich_prefix = '';
    $rich_browser= '';
    if(ereg("MSIE ([0-9|\.]+)", $user_agent, $regs) &&
       ereg("Win", $user_agent) && !strstr($user_agent, 'Opera')){
        $rich_browser = 'msie';
    }else{
//      if(ereg("Mozilla/([0-9|\.]+)", $user_agent, $regs) &&
//         ereg("Gecko", $user_agent) && (double)$regs[1]>=5.0){
            $rich_browser = 'ns';
            $rich_prefix = '_ns';
//      }
    }

    $browser_info['rich_browser'] = $rich_browser;
    $browser_info['rich_prefix'] = $rich_prefix;
    return $browser_info;
}

//returns number of rich class instances created and increase the number by inc
function rich_get_rich_flag($inc=0){
static $rich_flag = 0;
    $rich_flag += $inc;
    return $rich_flag;
}

//returns true if element with key $lang is exist in $rich_lang otherwise
//returns false and add the element
function rich_get_rich_lang($lang){
static $rich_lang = array();

    if (isset($rich_lang[$lang])) {
        return true;
    } else {
        $rich_lang[$lang] = true;
        return false;
    }
}

//returns code loading its JS and CSS files
function rich_get_head_code($class_path) {
    $browser_info = rich_get_browser_info();
    $rich_prefix = $browser_info['rich_prefix'];

    return '
  <link rel="StyleSheet" type="text/css" href="'.$class_path.'rich_files/rich'.$rich_prefix.'.css">
  <script language="JavaScript" src="'.$class_path.'rich_files/rich'.$rich_prefix.'.js"></script>
  <script language="JavaScript" src="'.$class_path.'rich_files/rich_xhtml.js"></script>
    ';

}

?>
