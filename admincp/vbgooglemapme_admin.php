<?php
/*======================================================================*\
|| #################################################################### ||
|| # VBGooglemap Member Edition admin 3.0.0. by StonyArc for 3.6.x     # ||
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is ©2000-2007 StonyArc		          # ||
|| # Email: stonyarc@xboxusersgroup.com or admin@stonyarc.com         # ||
|| # Copyright notice must remain intact						      # ||
|| #################################################################### ||
\*======================================================================*/

// ######################### ERROR REPORTING #############################
error_reporting(E_ALL & ~E_NOTICE);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
define('NO_REGISTER_GLOBALS', 1);

$phrasegroups       = array('vbgooglemapmecp');
$specialtemplates   = array();


require_once './global.php';
require_once('./includes/functions_misc.php');

// Get general settings
$me_settings = $db->query("SELECT * FROM " . TABLE_PREFIX . "googlemapme_settings");
$megetsettings=$db->fetch_array($me_settings);

if ($_REQUEST['do'] == "settings") {
// build the backend settings form
	print_cp_header($vbphrase['vbgooglemapme']);
	print_form_header($vbphrase['vbgmea_do_settings'],'updatesettings');
	print_table_header($vbphrase['general_settings']);
	print_yes_no_row($vbphrase['vbgmea_active'], 'googlemapme_active', $megetsettings[googlemapme_active]);
	print_yes_no_row($vbphrase['vbgmea_autoapprove'], 'googlemapme_autoapprove', $megetsettings[googlemapme_autoapprove]);
	$map_type = array(
		'1' => 'Normal',
		'2' => 'Hybrid',
		'3' => 'Satellite',
	);

	print_select_row($vbphrase['vbgmea_maptype'], 'googlemapme_maptype', $map_type, $megetsettings['googlemapme_maptype']);

	print_input_row($vbphrase['vbgmea_perpage'],'googlemapme_spots_perpage',$megetsettings[googlemapme_spots_perpage],0);
	print_input_row($vbphrase['vbgmea_key'],'googlemapme_key',$megetsettings[googlemapme_key],0);
	print_yes_no_row($vbphrase['vbgmea_smallmapcontrol'], 'googlemapme_smallmapcontrol', $megetsettings[googlemapme_smallmapcontrol]);
	print_yes_no_row($vbphrase['vbgmea_mouseoversupport'], 'googlemapme_mouseoversupport', $megetsettings[googlemapme_mouseoversupport]);
	print_input_row($vbphrase['vbgmea_width'],'googlemapme_width',$megetsettings[googlemapme_width],0);
	print_input_row($vbphrase['vbgmea_height'],'googlemapme_height',$megetsettings[googlemapme_height],0);

	print_input_row($vbphrase['vbgmea_zoom'],'googlemapme_zoom',$megetsettings[googlemapme_zoom],0);
	print_input_row($vbphrase['vbgmea_linkzoom'],'googlemapme_linkzoom',$megetsettings[googlemapme_linkzoom],0);
	print_input_row($vbphrase['vbgmea_lat'],'googlemapme_lat',$megetsettings[googlemapme_lat],0);
	print_input_row($vbphrase['vbgmea_lng'],'googlemapme_lng',$megetsettings[googlemapme_lng],0);
	print_input_row($vbphrase['vbgmea_balloon'],'googlemapme_balloon',$megetsettings[googlemapme_balloon],0);
	print_input_row($vbphrase['vbgmea_textchars'],'googlemapme_textchars',$megetsettings[googlemapme_textchars],0);
	print_input_row($vbphrase['vbgmea_textcharstitle'],'googlemapme_textcharstitle',$megetsettings[googlemapme_textcharstitle],0);

	print_submit_row($vbphrase['update'], 0);
	print_table_break();
	print_table_header($vbphrase['credits_and_copyright'], 2);
	print_label_row($vbphrase['vbgooglemapme_copyright']);
	print_table_footer();
	print_cp_footer();
	exit;
}

if ($_REQUEST['do'] == "updatesettings") {
// update the new settings

	$megetsettings = &$_POST;
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "googlemapme_settings");
	$db->query_write("INSERT INTO " . TABLE_PREFIX . "googlemapme_settings (googlemapme_active,googlemapme_autoapprove,googlemapme_maptype,googlemapme_spots_perpage,googlemapme_key,googlemapme_smallmapcontrol,googlemapme_mouseoversupport,googlemapme_width,googlemapme_height,googlemapme_zoom,googlemapme_linkzoom,googlemapme_lat,googlemapme_lng,googlemapme_balloon,googlemapme_textchars,googlemapme_textcharstitle) VALUES ('".$megetsettings[googlemapme_active]."','".$megetsettings[googlemapme_autoapprove]."','".$megetsettings[googlemapme_maptype]."','".$megetsettings[googlemapme_spots_perpage]."','".$megetsettings[googlemapme_key]."','".$megetsettings[googlemapme_smallmapcontrol]."','".$megetsettings[googlemapme_mouseoversupport]."','".$megetsettings[googlemapme_width]."','".$megetsettings[googlemapme_height]."','".$megetsettings[googlemapme_zoom]."','".$megetsettings[googlemapme_linkzoom]."','".$megetsettings[googlemapme_lat]."','".$megetsettings[googlemapme_lng]."','".$megetsettings[googlemapme_balloon]."','".$megetsettings[googlemapme_textchars]."','".$megetsettings[googlemapme_textcharstitle]."')");

	build_datastore('vbgooglemapme', serialize($db->query_first("SELECT * FROM " . TABLE_PREFIX . "googlemapme_settings")));
	define('CP_REDIRECT', $_SERVER['PHP_SELF'].'?do=settings');
	print_stop_message('vbgooglemapme_cp_redirect');
}

if ($_REQUEST['do'] == "viewug") {
// Get the maker overview

	print_cp_header($vbphrase['vbgooglemapme']);
	print_form_header($vbphrase['vbgmea_do_settings'], '');
	print_table_header($vbphrase['vbgmea_marker_overview'],15);
	print_description_row($vbphrase['vbgmea_desc_marker_overview'], 0, 5, 'thead');

	print_cells_row(array("usergroupid","title","marker","on legend","Edit"),1,'thead',-1);
	
	$getug=$db->query_read("select usergroupid,title,googlemapmetype,showonvbgme from ".TABLE_PREFIX."usergroup order by usergroupid asc");

	while($ugs = $db->fetch_array($getug))
	{
			print_cells_row(array(
			"{$ugs['usergroupid']}","{$ugs['title']}","{$ugs['googlemapmetype']}","{$ugs['showonvbgme']}","<a href='?do=editug&usergroupid={$ugs['usergroupid']}'>Edit this usergroup</a>"),'','',1);
	
	}
	print_table_footer();
	print_cp_footer();
	exit;
}

if ($_REQUEST['do'] == 'support')
{
	print_cp_header($vbphrase['vbgooglemapme']);
	print_table_start();
	print_table_header($vbphrase['support_overview'], 2);
	print_label_row($vbphrase['support_desc']);
	print_label_row($vbphrase['support_url']);
	print_table_break();
	print_table_header($vbphrase['credits_and_copyright'], 2);
	print_label_row($vbphrase['vbgmea_credits']);
	print_label_row($vbphrase['vbgooglemapme_copyright']);
	print_table_break();
	print_table_header($vbphrase['vbgmea_version_title'], 2);
	print_label_row($vbphrase['vbgmea_version_control']);
	print_label_row($vbphrase['vbgooglemapme_copyright']);
	print_label_row($vbphrase['vbgooglemapme_donate']);

	print_table_footer();
	print_cp_footer();
	exit;
}
if ($_REQUEST['do'] == "editug") {
// Edit the category

	print_cp_header($vbphrase['vbgooglemapme']);
	$settings = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "googlemapme_settings");
	if(!$usergroup=$db->query_first("select usergroupid,title,googlemapmetype,showonvbgme from ".TABLE_PREFIX."usergroup where usergroupid='{$_REQUEST['usergroupid']}'"))
	{
		echo "No Group available [ <a href='javascript:history.go(-1);'>Return to previous page</a> ]";
		exit;
	}
	$googlemapmetype = array(
		'yellow' => 'yellow',
		'orange' => 'orange',
		'darkorange' => 'dark orange',
		'pink' => 'pink',
		'purple' => 'purple',
		'red' => 'red',
		'darkred' => 'dark red',
		'lightgreen' => 'light green',
		'green' => 'green',
		'darkgreen' => 'dark green',
		'lightblue' => 'light blue',
		'blue' => 'blue',
		'darkblue' => 'dark blue',
		'gray' => 'gray',
		'black' => 'black',
	);
	print_form_header($vbphrase['vbgmea_do_settings'], 'update_ug');
	echo "<input type='hidden' value='{$usergroup['usergroupid']}' name='usergroupid'>";
	print_table_header($vbphrase['vbgmea_edit_marker']);
	print_description_row($vbphrase['vbgmea_desc_marker_update'], 0, 2, 'thead');
	print_label_row($usergroup['title']);
	print_yes_no_row($vbphrase['vbgmea_showonvbgme'], 'showonvbgme', $usergroup['showonvbgme']);

	print_select_row("googlemapmetype", 'googlemapmetype', $googlemapmetype, $usergroup['googlemapmetype']);

	print_label_row(
	
"
	<img src=\"../images/googlemapme/mm_20_black.png\" alt=\"\"> Black&nbsp;
<img src=\"../images/googlemapme/mm_20_blue.png\" alt=\"\"> Blue&nbsp;
<img src=\"../images/googlemapme/mm_20_darkblue.png\" alt=\"\"> Dark blue&nbsp;
<img src=\"../images/googlemapme/mm_20_darkgreen.png\" alt=\"\"> Dark green&nbsp;
<img src=\"../images/googlemapme/mm_20_darkorange.png\" alt=\"\"> Dark orange&nbsp;
<img src=\"../images/googlemapme/mm_20_darkred.png\" alt=\"\"> Dark red&nbsp;
<img src=\"../images/googlemapme/mm_20_gray.png\" alt=\"\"> Gray&nbsp;
<img src=\"../images/googlemapme/mm_20_green.png\" alt=\"\"> Green&nbsp;
<img src=\"../images/googlemapme/mm_20_lightblue.png\" alt=\"\"> Light blue&nbsp;
<img src=\"../images/googlemapme/mm_20_lightgreen.png\" alt=\"\"> Light green&nbsp;
<img src=\"../images/googlemapme/mm_20_orange.png\" alt=\"\"> Orange&nbsp;
<img src=\"../images/googlemapme/mm_20_pink.png\" alt=\"\"> Pink&nbsp;
<img src=\"../images/googlemapme/mm_20_purple.png\" alt=\"\"> Purple&nbsp;
<img src=\"../images/googlemapme/mm_20_red.png\" alt=\"\"> Red&nbsp;
<img src=\"../images/googlemapme/mm_20_yellow.png\" alt=\"\"> Yellow&nbsp;

	
	
"
	
	
	
	);
	print_submit_row($vbphrase['update'], 0);
	print_table_footer();

	print_cp_footer();
	exit;
}

if ($_REQUEST['do'] == "update_ug") {
// Update the category

	print_cp_header($vbphrase['vbgooglemapme']);
	if(!$usergroup=$db->query_first("select usergroupid,title,googlemapmetype from ".TABLE_PREFIX."usergroup where usergroupid='{$_POST['usergroupid']}'"))
	{
		echo "No Group available [ <a href='javascript:history.go(-1);'>Return to previous page</a> ]";
		exit;
	}

	$db->query_write("update ".TABLE_PREFIX."usergroup set googlemapmetype='{$_POST['googlemapmetype']}',showonvbgme='{$_POST['showonvbgme']}' WHERE usergroupid='{$usergroup['usergroupid']}'");
	define('CP_REDIRECT', 'vbgooglemapme_admin.php?do=viewug');
	print_stop_message('vbgooglemapme_cp_redirect');
	
}







if ($_REQUEST['do'] == "element") {
// View all current vbgooglemap ME elements
	print_cp_header($vbphrase['vbgooglemapme']);
	if(!$category=$db->query_first("select mapid from ".TABLE_PREFIX."googlemapme"))
	{
		echo "No Elements available [ <a href='javascript:history.go(-1);'>Return to previous page</a> ]";
		exit;
	}
	print_form_header($vbphrase['vbgmea_do_settings'], '');
	print_table_header($vbphrase['vbgmea_elements_overview'],10);
	print_description_row("[ <a href='javascript:history.go(-1);'>Return to previous page</a> ]", 0, 10, 'thead');
	print_cells_row(array("mapid","username","usergroup","marker","title_map","status","Approve","Edit","Delete",),'1','thead',-1);
	
	$getelements=$db->query_read("select * from ".TABLE_PREFIX."googlemapme order by mapid");
	while($elements = $db->fetch_array($getelements))
	{
	if ($elements['approve'] == 0)
	{
	$status ="<FONT COLOR='#FF0000'>offline</FONT>";
	}
	else
	{
	$status ="<FONT COLOR='#99CC00'>online</FONT>";	
	}
	$marker=$db->query_first("select title,googlemapmetype from ".TABLE_PREFIX."usergroup where usergroupid='{$elements['usergroupid']}'");

	print_cells_row(array(
			"{$elements['mapid']}</b>",
			"{$elements['username']}",
			"{$marker['title']}",
			"{$marker['googlemapmetype']}",
			"{$elements['title_map']}",
			"$status",
			"<a href='?do=approve&mapid={$elements['mapid']}'>Change status</a>",
			"<a href='?do=editelement&mapid={$elements['mapid']}'>Edit</a>",
			"<a href='?do=deleteelement&mapid={$elements['mapid']}'>Delete</a>"),
			 '','',1);
	}
	print_table_footer();
	print_cp_footer();
	exit;
}
if ($_REQUEST['do'] == "uelement") {
// View all current unapproved vbgooglemap ME elements

	print_cp_header($vbphrase['vbgooglemapme']);
	if(!$category=$db->query_first("select mapid from ".TABLE_PREFIX."googlemapme"))
	{
		echo "No Elements available [ <a href='javascript:history.go(-1);'>Return to previous page</a> ]";
		exit;
	}
	print_form_header($vbphrase['vbgmea_do_settings'], '');
	print_table_header($vbphrase['vbgmea_elements_overview'],10);
	print_description_row("[ <a href='javascript:history.go(-1);'>Return to previous page</a> ]", 0, 10, 'thead');
	print_cells_row(array("mapid","username","usergroup","marker","title_map","status","Approve","Edit","Delete",),'1','thead',-1);
	
	$getelements=$db->query_read("select * from ".TABLE_PREFIX."googlemapme where approve ='0' order by mapid");
	while($elements = $db->fetch_array($getelements))
	{
	if ($elements['approve'] == 0)
	{
	$status ="<FONT COLOR='#FF0000'>offline</FONT>";
	}
	else
	{
	$status ="<FONT COLOR='#99CC00'>online</FONT>";	
	}
	$marker=$db->query_first("select title,googlemapmetype from ".TABLE_PREFIX."usergroup where usergroupid='{$elements['usergroupid']}'");

	print_cells_row(array(
			"{$elements['mapid']}</b>",
			"{$elements['username']}",
			"{$marker['title']}",
			"{$marker['googlemapmetype']}",
			"{$elements['title_map']}",
			"$status",
			"<a href='?do=approve&mapid={$elements['mapid']}'>Change status</a>",
			"<a href='?do=editelement&mapid={$elements['mapid']}'>Edit</a>",
			"<a href='?do=deleteelement&mapid={$elements['mapid']}'>Delete</a>"),
			 '','',1);
	}
	print_table_footer();
	print_cp_footer();
	exit;
}
if ($_REQUEST['do'] == "editelement") 
{
// Edit a specific element
	print_cp_header($vbphrase['vbgooglemapme']);
	if(!$elements=$db->query_first("select * from ".TABLE_PREFIX."googlemapme where mapid='{$_REQUEST['mapid']}'"))
	{
		echo "No Element available [ <a href='javascript:history.go(-1);'>Return to previous page</a> ]";
		exit;
	}	
	print_form_header($vbphrase['vbgmea_do_settings'], 'update_element');
	echo "<input type='hidden' value='{$elements['mapid']}' name='mapid'>";
	print_table_header($vbphrase['vbgmea_edit_element']);
	print_description_row($vbphrase['vbgmea_desc_element_update'], 0, 2, 'thead');
	print_input_row("title_map", 'title_map',$elements['title_map']);
	print_input_row("text_map", 'text_map',$elements['text_map']);
	print_input_row("lat_map", 'lat_map',$elements['lat_map']);
	print_input_row("lng_map", 'lng_map',$elements['lng_map']);
	print_input_row("pimage", 'pimage',$elements['pimage']);
	print_yes_no_row("approve", 'approve', $elements[approve]);
	print_submit_row();
	print_table_footer();
	print_cp_footer();
	exit;
}
if ($_REQUEST['do'] == "update_element") 
{
// commit changes to database 
	print_cp_header($vbphrase['vbgooglemapme']);
	if(!$elements=$db->query_first("select * from ".TABLE_PREFIX."googlemapme where mapid='{$_REQUEST['mapid']}'"))
	{
		echo "No Element available [ <a href='javascript:history.go(-1);'>Return to previous page</a> ]";
		exit;
	}	

    $title_map = addslashes(htmlspecialchars(trim($_POST['title_map'])));
    $text_map = preg_replace("/\n|\r\n|\r/", "", nl2br(addslashes(htmlspecialchars(trim($_POST['text_map'])))));
    $lat_map = addslashes(htmlspecialchars(trim($_POST['lat_map'])));
    $lng_map = addslashes(htmlspecialchars(trim($_POST['lng_map'])));
    $pimage = addslashes(htmlspecialchars(trim($_POST['pimage'])));
    $approve = $_POST['approve'];

	$db->query_write("update ".TABLE_PREFIX."googlemapme set title_map='$title_map',text_map='$text_map',lat_map='$lat_map',lng_map='$lng_map',pimage='$pimage',approve='$approve' where mapid='{$_REQUEST['mapid']}' ");
	
	define('CP_REDIRECT', 'vbgooglemapme_admin.php?do=element');
	print_stop_message('vbgooglemapme_cp_redirect');

}
if ($_REQUEST['do'] == "approve") 
{
// approve elements quick status change
	print_cp_header($vbphrase['vbgooglemapme']);
	if(!$elements=$db->query_first("select * from ".TABLE_PREFIX."googlemapme where mapid='{$_REQUEST['mapid']}'"))
	{
		echo "No Element available [ <a href='javascript:history.go(-1);'>Return to previous page</a> ]";
		exit;
	}	
	
	if ($elements['approve']==1)
	{
	$db->query("UPDATE " . TABLE_PREFIX . "googlemapme SET approve='0' where mapid='".$_REQUEST['mapid']."'");
	}
	else
	{
	$db->query("UPDATE " . TABLE_PREFIX . "googlemapme SET approve='1' where mapid='".$_REQUEST['mapid']."'");
	}
	define('CP_REDIRECT', 'vbgooglemapme_admin.php?do=element');
	print_stop_message('vbgooglemapme_cp_redirect');

}

if($_REQUEST['do']=="deleteelement"){
// Delete confirmation question

	print_cp_header($vbphrase['vbgooglemapme']);
	if(!$elements=$db->query_first("select * from ".TABLE_PREFIX."googlemapme where mapid='{$_REQUEST['mapid']}'"))
	{
		echo "No Element available [ <a href='javascript:history.go(-1);'>Return to previous page</a> ]";
		exit;
	}	
	print_form_header($vbphrase['vbgmea_do_settings'], 'confirmdeleteelements');
	echo "<input type='hidden' name='mapid' value='".$elements['mapid']."'>";
	print_table_header($vbphrase['vbgmea_delete_element']);
	print_description_row($vbphrase['vbgmea_desc_element_delete']." ".$vbphrase['vbgmea_desc_element_delete_warn'], 0, 2, 'thead');
	print_yes_no_row($vbphrase['vbgmea_desc_element_delete']." <b>".$elements['title_map']."</b>",'deletecheck','');
	print_submit_row();
	print_table_footer();
	print_cp_footer();
	exit;
}

if ($_REQUEST['do'] == "confirmdeleteelements") 
{
// Final deletion in the database

	print_cp_header($vbphrase['vbgooglemapme']);
	if(!$elements=$db->query_first("select * from ".TABLE_PREFIX."googlemapme where mapid='{$_REQUEST['mapid']}'"))
	{
		echo "No Element available [ <a href='javascript:history.go(-1);'>Return to previous page</a> ]";
		exit;
	}	
	if($_REQUEST['deletecheck']!=1)
	{
	define('CP_REDIRECT', 'vbgooglemapme_admin.php?do=element');
	print_stop_message('vbgooglemapme_cp_redirect');
	} 
	else 
	{
	$db->query_write("delete from ".TABLE_PREFIX."googlemapme where mapid='".$elements['mapid']."'");
	define('CP_REDIRECT', 'vbgooglemapme_admin.php?do=element');
	print_stop_message('vbgooglemapme_cp_redirect');
	}
}





?>

