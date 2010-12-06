<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// #################### PREVENT UNAUTHORIZED USERS ########################
if (!defined('PHOTOPLOG_SCRIPT'))
{
	exit(); // DO NOT REMOVE THIS!
}

require_once('./permissions.php');

$photoplog_auth_access = 0;

($hook = vBulletinHook::fetch_hook('photoplog_authenticate_start')) ? eval($hook) : false;

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanviewfiles'])
{
	define('PHOTOPLOG_USER1','view');
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'view')
	{
		$photoplog_auth_access = 1;
	}
}

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadfiles'])
{
	define('PHOTOPLOG_USER2','upload');
	if (defined('PHOTOPLOG_LEVEL') && (PHOTOPLOG_LEVEL == 'upload' || PHOTOPLOG_LEVEL == 'editor'))
	{
		$photoplog_auth_access = 1;
	}
}

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcaneditownfiles'])
{
	define('PHOTOPLOG_USER3','editown');
	if (defined('PHOTOPLOG_LEVEL') && (PHOTOPLOG_LEVEL == 'edit' || PHOTOPLOG_LEVEL == 'editor'))
	{
		$photoplog_auth_access = 1;
	}
}

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcandeleteownfiles'])
{
	define('PHOTOPLOG_USER4','deleteown');
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'delete')
	{
		$photoplog_auth_access = 1;
	}
}

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcaneditotherfiles'])
{
	define('PHOTOPLOG_USER5','editothers');
	if (defined('PHOTOPLOG_LEVEL') && (PHOTOPLOG_LEVEL == 'edit' || PHOTOPLOG_LEVEL == 'editor'))
	{
		$photoplog_auth_access = 1;
	}
}

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcandeleteotherfiles'])
{
	define('PHOTOPLOG_USER6','deleteothers');
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'delete')
	{
		$photoplog_auth_access = 1;
	}
}

$photoplog['comment_view'] = 0;
if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanviewcomments'])
{
	define('PHOTOPLOG_USER7','commentview');
	$photoplog['comment_view'] = 1;
	// view comments inside view file
}

$photoplog['comment_make'] = 0;
if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcancommentonfiles'])
{
	define('PHOTOPLOG_USER8','commentmake');
	$photoplog['comment_make'] = 1;
	if (defined('PHOTOPLOG_LEVEL') && (PHOTOPLOG_LEVEL == 'comment' || PHOTOPLOG_LEVEL == 'editor'))
	{
		$photoplog_auth_access = 1;
	}
}

$photoplog['comment_editown'] = 0;
if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcaneditowncomments'])
{
	define('PHOTOPLOG_USER9','commenteditown');
	$photoplog['comment_editown'] = 1;
	if (defined('PHOTOPLOG_LEVEL') && (PHOTOPLOG_LEVEL == 'comment' || PHOTOPLOG_LEVEL == 'editor'))
	{
		$photoplog_auth_access = 1;
	}
}

$photoplog['comment_deleteown'] = 0;
if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcandeleteowncomments'])
{
	define('PHOTOPLOG_USER10','commentdeleteown');
	$photoplog['comment_deleteown'] = 1;
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'comment')
	{
		$photoplog_auth_access = 1;
	}
}

$photoplog['comment_editothers'] = 0;
if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcaneditothercomments'])
{
	define('PHOTOPLOG_USER11','commenteditothers');
	$photoplog['comment_editothers'] = 1;
	if (defined('PHOTOPLOG_LEVEL') && (PHOTOPLOG_LEVEL == 'comment' || PHOTOPLOG_LEVEL == 'editor'))
	{
		$photoplog_auth_access = 1;
	}
}

$photoplog['comment_deleteothers'] = 0;
if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcandeleteothercomments'])
{
	define('PHOTOPLOG_USER12','commentdeleteothers');
	$photoplog['comment_deleteothers'] = 1;
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'comment')
	{
		$photoplog_auth_access = 1;
	}
}

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanusesearchfeature'])
{
	define('PHOTOPLOG_USER13','search');
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'search')
	{
		$photoplog_auth_access = 1;
	}
}

$photoplog['slideshow_feature'] = 0;
if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanuseslideshowfeature'])
{
	define('PHOTOPLOG_USER14','slideshow');
	$photoplog['slideshow_feature'] = 1;
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'slideshow')
	{
		$photoplog_auth_access = 1;
	}
}

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanusealbumfeature'])
{
	define('PHOTOPLOG_USER15','albums');
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'albums')
	{
		$photoplog_auth_access = 1;
	}
}

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcansuggestcategories'])
{
	define('PHOTOPLOG_USER16','suggestcategories');
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'categories')
	{
		$photoplog_auth_access = 1;
	}
}

if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcancreateunmoderatedcategories'])
{
	define('PHOTOPLOG_USER17','createcategories');
	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'categories')
	{
		$photoplog_auth_access = 1;
	}
}

if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'bypass')
{
	// for misc.php and private.php redirects, for ajax.php and selector.php bypass
	// also for inline.php as permissions are checked within that file by category
	$photoplog_auth_access = 1;
}

($hook = vBulletinHook::fetch_hook('photoplog_authenticate_complete')) ? eval($hook) : false;

if (!$photoplog_auth_access)
{
	$photoplog_base_href = "<base href=\"".$vbulletin->options['bburl']."/".$vbulletin->options['forumhome'].".php\" />\n";
	$headinclude = $photoplog_base_href.$headinclude.'<!-- +23C3P1ba074ef086016ddff72781c41f51cd491b79d5+ -->';
	$show['permission_error'] = true;
	$navbits = array();
	$navbits[$photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q']] = htmlspecialchars_uni($vbphrase['photoplog_photoplog']);
	$navbits[''] = $vbphrase['photoplog_access_denied'];
	$navbits = construct_navbits($navbits);
	$pagetitle = $vbphrase['photoplog_photoplog'].' - '.$vbphrase['photoplog_access_denied'];
	if ($vbulletin->options['hometitle'] && $vbulletin->options['hometitle'] != $vbphrase['photoplog_photoplog'])
	{
		$pagetitle = $vbulletin->options['hometitle'].' - '.$pagetitle;
	}
	eval('$navbar = "' . fetch_template('navbar') . '";');
	$footer = $photoplog['powered_by'] . $footer;
	eval('print_output("' . fetch_template('STANDARD_ERROR') . '");');
	exit();
}

?>