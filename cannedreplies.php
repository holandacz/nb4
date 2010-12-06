<?php
error_reporting(E_ALL & ~E_NOTICE);
define('THIS_SCRIPT', 'cannedreplies2008');
$phrasegroups = array('posting', 'postbit', 'user', 'timezone', 'cprofilefield', 'cppermission');
$specialtemplates = array(
	'bbcodecache','smiliecache'
);
$globaltemplates = array(
	'cr_shell',
	'cr_edit',
	'cr_list',
	'cr_new',
	'cr_main',
	'cr_js',
	);
$actiontemplates = array(
	'editor_clientscript',
	'editor_jsoptions_font',
	'editor_jsoptions_size',
	'editor_smilie',
	'editor_smiliebox',
	'editor_smiliebox_row',
	'editor_smiliebox_straggler',
	'editor_toolbar_on',
	'newpost_disablesmiliesoption',
	);
require_once('./global.php');
require_once(DIR . '/includes/functions_editor.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/functions.php');
require_once(DIR . '/includes/functions_wysiwyg.php');
require_once(DIR . '/includes/functions_newpost.php');
require_once(DIR . '/includes/class_bbcode.php'); 

	if (!($permissions['cannedreplies'] & $vbulletin->bf_ugp['cannedreplies']['canusecr']))
	{
		print_no_permission();
	}
	$navbits = array();
	$navbits[$parent] = $vbphrase[cannedrepliesedit];
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');
		
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'modify';
}

// #############################################################################

if ($_REQUEST['do'] == 'kill')
{
    $id = $vbulletin->input->clean_gpc('g', 'id', TYPE_UINT);
	$userid = $vbulletin->userinfo['userid'];
	if (!$id)
	{
		$vbulletin->url = "cannedreplies.php";
		eval(print_standard_redirect(''));
	}
    $db->query_write("DELETE FROM ".TABLE_PREFIX."cannedreplies WHERE id=$id AND userid=$userid");
	$vbulletin->url = "cannedreplies.php";
	eval(print_standard_redirect(''));
}

// #############################################################################

if ($_REQUEST['do'] == 'delete')
{
    $vbulletin->url = "cannedreplies.php";
	eval(print_standard_redirect(''));
}

// #############################################################################

if ($_POST['do'] == 'insert')
{
    $title = $vbulletin->input->clean_gpc('p', 'title', TYPE_STR);
		if (!$title)
		{
			$vbulletin->url = "cannedreplies.php?do=add";
			eval(print_standard_redirect(''));
		}
	$vbulletin->input->clean_array_gpc('p', array(
		'message'      => TYPE_STR,
		'wysiwyg'			 => TYPE_BOOL,
	));
	if ($vbulletin->GPC['wysiwyg'])
	{
		$reply = convert_wysiwyg_html_to_bbcode($vbulletin->GPC['message'], 1);
	}
	else
	{
		$reply = $vbulletin->GPC['message'];
	}
		if (!$reply)
		{
			$vbulletin->url = "cannedreplies.php?do=add";
			eval(print_standard_redirect(''));
		}
	$db->query_write("INSERT INTO ".TABLE_PREFIX."cannedreplies (userid, title, reply) VALUES ('".$vbulletin->userinfo['userid']."', '".addslashes($title)."', '".addslashes($reply)."') ");
	$vbulletin->url = "cannedreplies.php";	
	eval(print_standard_redirect(''));
}

// #############################################################################

if ($_REQUEST['do'] == 'add')
{
    $editorid = construct_edit_toolbar($reply, 0, 'nonforum', iif($vbulletin->options['privallowsmilies'], 1, 0));
	$countcrs = $vbulletin->db->query_first("SELECT COUNT(*) AS countrows FROM " . TABLE_PREFIX . "cannedreplies WHERE userid='".$vbulletin->userinfo['userid']."' ");
	$noofcrs = $countcrs['countrows'];
	if ($noofcrs < $permissions['maxcr']) {
	eval('$html = "' . fetch_template('cr_new') . '";');
	} else {
	$errortext = "You can only have ".$permissions['maxcr']." Canned Replies";
	eval('$html = $errortext;');
	}
	eval('print_output("' . fetch_template('cr_shell') . '");');
}

// #############################################################################

if ($_POST['do'] == 'update')
{
	$userid = $vbulletin->userinfo['userid'];
	$id = $vbulletin->input->clean_gpc('p', 'id', TYPE_UINT);
		if (!$id)
		{
			$vbulletin->url = "cannedreplies.php";
			eval(print_standard_redirect(''));
		}
	$title = $vbulletin->input->clean_gpc('p', 'title', TYPE_STR);
	$title = addslashes($title);
		if (!$title)
		{
			$vbulletin->url = "cannedreplies.php?do=edit&id=".$id;
			eval(print_standard_redirect(''));
		}
	$vbulletin->input->clean_array_gpc('p', array(
		'message'      => TYPE_STR,
		'wysiwyg'			 => TYPE_BOOL,
	));
	if ($vbulletin->GPC['wysiwyg'])
	{
		$reply = convert_wysiwyg_html_to_bbcode($vbulletin->GPC['message'], 1);
	}
	else
	{
		$reply = $vbulletin->GPC['message'];
	}
	if ($vbulletin->GPC['wysiwyg'])
	{
		$reply = convert_wysiwyg_html_to_bbcode($vbulletin->GPC['message'], 1);
	}
	else
	{
		$reply = $vbulletin->GPC['message'];
	}
	$db->query_write("UPDATE ".TABLE_PREFIX."cannedreplies SET title = '".$title."', reply = '".addslashes($reply)."' WHERE id='".$id."' AND userid='".$userid."' ");
	$vbulletin->url = "cannedreplies.php";
	eval(print_standard_redirect(''));
}

// #############################################################################

if ($_REQUEST['do'] == 'edit')
{
$id = $vbulletin->input->clean_gpc('g', 'id', TYPE_UINT);
	if (!$id)
	{
		$vbulletin->url = "cannedreplies.php";
		eval(print_standard_redirect(''));
	}
$userid = $vbulletin->userinfo['userid'];
$reply = $db->query_first("SELECT id, title, reply FROM " . TABLE_PREFIX . "cannedreplies WHERE userid='".$userid."' AND id='".$id."' ORDER BY title ASC ");
	$title = $reply['title'];
	$reply = $reply['reply'];
	$editorid = construct_edit_toolbar($reply, 0, 'nonforum', iif($vbulletin->options['privallowsmilies'], 1, 0));
	eval('$html .= "' . fetch_template('cr_edit') . '";');
	eval('print_output("' . fetch_template('cr_shell') . '");');
}

// #############################################################################

if ($_REQUEST['do'] == 'modify')
{
    eval('$html = "' . fetch_template('cr_main') . '";');
	$rowcount = 1;
	$crs = $db->query_read("SELECT id, title, reply FROM " . TABLE_PREFIX . "cannedreplies WHERE userid='".addslashes($vbulletin->userinfo['userid'])."' ORDER BY title ASC");
	while($cr = $db->fetch_array($crs)){
			require_once('./global.php');
			require_once('./includes/class_bbcode.php'); 
			$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list(), true); 
			$cr[reply] = $bbcode_parser->do_parse($cr[reply],false,true,true,true,true,false); 
			$cr[title2] = addslashes($cr[title]);
	  if($rowcount%2)
	  {
	  	$rowcolor=1;
	  }
	  else
	  {
	  	$rowcolor=2;
	  }
		eval('$html .= "' . fetch_template('cr_list') . '";');
		$rowcount ++;
	}
	eval('print_output("' . fetch_template('cr_shell') . '");');
}
?>