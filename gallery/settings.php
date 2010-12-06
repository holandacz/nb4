<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ####################### SET PHP ENVIRONMENT ############################
// report all errors except notice level errors
error_reporting(E_ALL & ~E_NOTICE);

@ignore_user_abort(true);
if (@ini_get('safe_mode') != 1)
{
	@set_time_limit(0);
	@ini_set('max_execution_time',0);
}
@ini_set('memory_limit','128M');
@ini_set('post_max_size','30M');
@ini_set('upload_max_filesize','30M');
@ini_set('magic_quotes_runtime',false);
@ini_set('magic_quotes_sybase',false);

// ######################## GRAB THE FULL PATH ############################
require_once('./config.php');

// #################### DEFINE IMPORTANT CONSTANTS ########################
define('PHOTOPLOG_SCRIPT','init'); // DO NOT REMOVE THIS!
define('PHOTOPLOG_VERSION','Pro v.2.1.4.8');

// these are to bypass print_no_permission exits in vB global.php
define('THIS_SCRIPT','image');
define('CSRF_PROTECTION',true);
$_REQUEST['a'] = 'ver';

// ################### PRE-CACHE TEMPLATES AND DATA #######################
$phrasegroups = $specialtemplates = $globaltemplates = $actiontemplates = array();
require_once('./templates.php');

// ####################### REQUIRE VB BACK-END ############################
define('PHOTOPLOG_BWD', (($getcwd = getcwd()) ? $getcwd : '.'));

chdir(PHOTOPLOG_FWD);
require_once('./global.php');
chdir(PHOTOPLOG_BWD);

// be rid of the vB global.php print_no_permission exit bypass
unset($a,$_REQUEST['a']);

// #################### INITIALIZE SOME VARIABLES #########################
$photoplog = array(); // DO NOT REMOVE THIS!
$photoplog['location'] = $vbulletin->options['photoplog_script_dir'];
$photoplog['bbcodeurl'] = $vbulletin->options['photoplog_bbcode_link'];

require_once(DIR.'/includes/photoplog_prefix.php'); // DO NOT REMOVE THIS!

// please, do not hide, change, remove, etcetera, unless unbranded license purchased - visit www.photoplog.com for official support
$photoplog['powered_by'] = '';
// visit www.photoplog.com for official support - please, do not hide, change, remove, etcetera, unless unbranded license purchased

($hook = vBulletinHook::fetch_hook('photoplog_global_start')) ? eval($hook) : false;

// ###################### SET BIT OPTION VALUES ###########################
$photoplog_categoryoptions = array(
	'allowhtml' => 1,
	'allowsmilies' => 2,
	'allowbbcode' => 4,
	'allowimgcode' => 8,
	'allowparseurl' => 16,
	'allowcomments' => 32,
	'issearchable' => 64,
	'ismembersfolder' => 128,
	'actasdivider' => 256,
	'allowdeschtml' => 512,
	'openforsubcats' => 1024
);

// ###################### GRAB DATASTORE VALUES ###########################
$photoplog_ds_catopts = array();
if (!defined('PHOTOPLOG_HTTPD'))
{
	$photoplog_ds_catopts = $vbulletin->photoplog_dscat;
	if (
		!is_array($vbulletin->photoplog_dscat)
			&&
		$vbulletin->photoplog_dscat[0] == 'a'
			&&
		$vbulletin->photoplog_dscat[1] == ':'
	)
	{
		$photoplog_ds_catopts = unserialize($vbulletin->photoplog_dscat);
	}
	if (!is_array($photoplog_ds_catopts))
	{
		$photoplog_ds_catopts = array();
	}
}

// ###################### REQUIRE PLOG BACK-END ###########################
if (is_file($vbulletin->options['photoplog_full_path'].'/functions.php'))
{
	require_once($vbulletin->options['photoplog_full_path'].'/functions.php');
}
else
{
	echo "<br /><br /><strong>
		Incorrect PhotoPlog setting! Go to
		ACP -> PhotoPlog Pro -> General Settings and make the correction.
		</strong><br /><br />";
	exit();
}
require_once('./authenticate.php');

// #################### INITIALIZE ADMIN SQL BIT ##########################
require_once(DIR . '/includes/adminfunctions.php');

$photoplog['canadminforums'] = 0;
$photoplog_admin_sql1 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.moderate = 0';
$photoplog_admin_sql1a = 'AND f1.moderate = 0';
$photoplog_admin_sql2 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_ratecomment.moderate = 0';
$photoplog_admin_sql2a = 'AND f2.moderate = 0';
$photoplog_admin_sql3 = 'AND IFNULL(' . PHOTOPLOG_PREFIX . 'photoplog_ratecomment.moderate,0) = 0';
$photoplog_admin_sql4 = PHOTOPLOG_PREFIX . 'photoplog_fileuploads.num_comments0 AS num_comments,
					' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.num_ratings0 AS num_ratings,
					' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.sum_ratings0 AS sum_ratings,
					IF(' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.num_ratings0 > 0,' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.sum_ratings0 / ' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.num_ratings0,0) AS ave_ratings,
					' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.last_comment_dateline0 AS last_comment_dateline,
					' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.last_comment_id0 AS last_comment_id';
$photoplog_admin_sql5 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_catcounts.moderate = 0';

if (can_administer('canadminforums'))
{
	$photoplog['canadminforums'] = 1;
	$photoplog_admin_sql1 = '';
	$photoplog_admin_sql1a = '';
	$photoplog_admin_sql2 = '';
	$photoplog_admin_sql2a = '';
	$photoplog_admin_sql3 = '';
	$photoplog_admin_sql4 = PHOTOPLOG_PREFIX . 'photoplog_fileuploads.num_comments1 AS num_comments,
						' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.num_ratings1 AS num_ratings,
						' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.sum_ratings1 AS sum_ratings,
						IF(' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.num_ratings1 > 0,' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.sum_ratings1 / ' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.num_ratings1,0) AS ave_ratings,
						' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.last_comment_dateline1 AS last_comment_dateline,
						' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.last_comment_id1 AS last_comment_id';
	$photoplog_admin_sql5 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_catcounts.moderate = 1';
}

// ###################### CHECK FOR PLOG ACTIVE ###########################
if (!$vbulletin->options['photoplog_is_active'] && !can_administer('canadminforums'))
{
	photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbulletin->options['photoplog_off_reason']);
}

$photoplog_off_warn = 0;
if (!$vbulletin->options['photoplog_is_active'])
{
	$photoplog_off_warn = 1;
}

// ################### INITIALIZE SUB NAVBAR LINKS ########################
$vbulletin->input->clean_array_gpc('g', array(
	'n' => TYPE_UINT
));

$photoplog_file_id = $vbulletin->GPC['n'];

$photoplog_file_info_links = '';

if (!defined('PHOTOPLOG_HTTPD'))
{
	$photoplog_link_catid = 0;

	if ($photoplog_file_id)
	{
		$photoplog_file_info_links = $db->query_first("SELECT catid, userid,
			fileid, filename, title, description, fielddata, moderate, username,
			dimensions, filesize, dateline, views, exifinfo, setid,
			$photoplog_admin_sql4
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid = ".intval($photoplog_file_id)."
			$photoplog_catid_sql1
			$photoplog_admin_sql1
		");

		$photoplog_link_catid = intval($photoplog_file_info_links['catid']);
	}

	if (!$photoplog_link_catid && isset($_GET['c']) && is_numeric($_GET['c']) && $_GET['c'] > 0)
	{
		$vbulletin->input->clean_array_gpc('g', array(
			'c' => TYPE_UINT
		));
		$photoplog_link_catid = $vbulletin->GPC['c'];
	}

	if (defined('PHOTOPLOG_USER1'))
	{
		$photoplog_view_loc = $photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q'];
		$photoplog['view_link'] = "<a href=\"".$photoplog_view_loc."\">".$vbphrase['photoplog_home']."</a>";
	}
	if (defined('PHOTOPLOG_USER2'))
	{
		$photoplog_upload_loc = $photoplog['location'].'/upload.php'.$vbulletin->session->vars['sessionurl_q'];
		if ($photoplog_link_catid)
		{
			$photoplog_upload_loc = $photoplog['location'].'/upload.php?'.$vbulletin->session->vars['sessionurl'].'c='.$photoplog_link_catid;
		}
		$photoplog['upload_link'] = "<a href=\"".$photoplog_upload_loc."\">".$vbphrase['photoplog_upload']."</a>";
	}
	if (defined('PHOTOPLOG_USER3') && $photoplog_file_id && $photoplog_file_info_links && $photoplog_file_info_links['userid'] == $vbulletin->userinfo['userid'])
	{
		$photoplog_edit_loc = $photoplog['location'].'/edit.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog_file_id;
		$photoplog['edit_link'] = "<a href=\"".$photoplog_edit_loc."\">".$vbphrase['photoplog_edit']."</a>";
	}
	if (defined('PHOTOPLOG_USER4') && $photoplog_file_id && $photoplog_file_info_links && $photoplog_file_info_links['userid'] == $vbulletin->userinfo['userid'])
	{
		$photoplog_delete_loc = $photoplog['location'].'/delete.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog_file_id;
		$photoplog['delete_link'] = "<a href=\"".$photoplog_delete_loc."\">".$vbphrase['photoplog_delete']."</a>";
	}
	if (defined('PHOTOPLOG_USER5') && $photoplog_file_id && $photoplog_file_info_links)
	{
		$photoplog_edit_loc = $photoplog['location'].'/edit.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog_file_id;
		$photoplog['edit_link'] = "<a href=\"".$photoplog_edit_loc."\">".$vbphrase['photoplog_edit']."</a>";
	}
	if (defined('PHOTOPLOG_USER6') && $photoplog_file_id && $photoplog_file_info_links)
	{
		$photoplog_delete_loc = $photoplog['location'].'/delete.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog_file_id;
		$photoplog['delete_link'] = "<a href=\"".$photoplog_delete_loc."\">".$vbphrase['photoplog_delete']."</a>";
	}
	if (defined('PHOTOPLOG_USER13'))
	{
		$photoplog_search_loc = $photoplog['location'].'/search.php'.$vbulletin->session->vars['sessionurl_q'];
		$photoplog['search_link'] = "<a href=\"".$photoplog_search_loc."\">".$vbphrase['photoplog_search']."</a>";
	}
	if (defined('PHOTOPLOG_USER14'))
	{
		$photoplog_slideshow_loc = $photoplog['location'].'/slideshow.php'.$vbulletin->session->vars['sessionurl_q'];
		$photoplog['slideshow_link'] = "<a href=\"".$photoplog_slideshow_loc."\">".$vbphrase['photoplog_slideshow']."</a>";
	}
	if (defined('PHOTOPLOG_USER15'))
	{
		$photoplog_albums_loc = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
		$photoplog['albums_link'] = "<a href=\"".$photoplog_albums_loc."\">".$vbphrase['photoplog_albums']."</a>";
	}
}

if ($photoplog['view_link'] || $photoplog['upload_link'] || $photoplog['edit_link'] || $photoplog['delete_link'] || $photoplog['search_link'] || $photoplog['slideshow_link'] || $photoplog['albums_link'])
{
	$photoplog['catidurl'] = '';
	if (isset($_GET['c']) && is_numeric($_GET['c']) && $_GET['c'] > 0)
	{
		$vbulletin->input->clean_array_gpc('g', array(
			'c' => TYPE_UINT
		));
		$photoplog['catidurl'] = '&amp;c='.$vbulletin->GPC['c'];
	}

	($hook = vBulletinHook::fetch_hook('photoplog_settings_subnavbar')) ? eval($hook) : false;

	eval('$photoplog[\'sub_navbar\'] = "' . fetch_template('photoplog_sub_navbar') . '";');
}

// ###################### INITIALIZE THUMB BITS ###########################
if (defined('PHOTOPLOG_RANDOM') && !defined('PHOTOPLOG_HTTPD') && !isset($_REQUEST['c']) && !isset($_REQUEST['n']) && !isset($_REQUEST['u']) && !isset($_REQUEST['q']) && !isset($_REQUEST['v']))
{
	require_once('./thumbnails.php');
}

// ####################### INITIALIZE LETTER BAR ##########################
if (defined('PHOTOPLOG_RANDOM') && !defined('PHOTOPLOG_HTTPD') && !isset($_REQUEST['c']) && !isset($_REQUEST['n']) && !isset($_REQUEST['v']))
{
	if (isset($_GET['q']))
	{
		$vbulletin->input->clean_array_gpc('g', array(
			'q' => TYPE_NOHTML
		));
		$photoplog['letter_id'] = strtolower($vbulletin->GPC['q']);
	}

	$photoplog_letter_arr = array(
		'1' => '#', 'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F',
		'g' => 'G', 'h' => 'H', 'i' => 'I', 'j' => 'J', 'k' => 'K', 'l' => 'L', 'm' => 'M',
		'n' => 'N', 'o' => 'O', 'p' => 'P', 'q' => 'Q', 'r' => 'R', 's' => 'S', 't' => 'T',
		'u' => 'U', 'v' => 'V', 'w' => 'W', 'x' => 'X', 'y' => 'Y', 'z' => 'Z'
	);
	$photoplog_folder_flag = 0;
	$photoplog_letter_bar_bits = '';
	foreach ($photoplog_letter_arr AS $photoplog_letter_key => $photoplog_letter_val)
	{
		($hook = vBulletinHook::fetch_hook('photoplog_settings_letterbarbit')) ? eval($hook) : false;

		eval('$photoplog_letter_bar_bits .= "' . fetch_template('photoplog_letter_bar_bit') . '";');
	}

	($hook = vBulletinHook::fetch_hook('photoplog_settings_letterbar')) ? eval($hook) : false;

	eval('$photoplog[\'letter_bar\'] = "' . fetch_template('photoplog_letter_bar') . '";');
}

// ##################### INITIALIZE CATEGORY BITS #########################
if (
	defined('PHOTOPLOG_RANDOM') && !defined('PHOTOPLOG_HTTPD') &&
	!$photoplog_perm_fileid && !$photoplog_perm_catid && !$photoplog_perm_commentid &&
	!isset($_REQUEST['u']) && !isset($_REQUEST['q']) && !isset($_REQUEST['page']) && !isset($_REQUEST['v'])
)
{
	require_once('./listing.php');
}

// ##################### INITIALIZE JAVASCRIPT BIT ########################
$vbulletin->input->clean_array_gpc('c', array(
	COOKIE_PREFIX.'photoplogjs' => TYPE_BOOL
));
$photoplog['jsactive'] = intval($vbulletin->GPC[COOKIE_PREFIX.'photoplogjs']);

$photoplog_cookiepath = $vbulletin->options['cookiepath'];
$photoplog_cookiedomain = $vbulletin->options['cookiedomain'];
$vbulletin->options['cookiepath'] = '/';
$vbulletin->options['cookiedomain'] = '';
vbsetcookie('photoplogjs', '0', false);
$vbulletin->options['cookiepath'] = $photoplog_cookiepath;
$vbulletin->options['cookiedomain'] = $photoplog_cookiedomain;
unset($photoplog_cookiepath, $photoplog_cookiedomain);

//##################### INITIALIZE STATISTICS BAR #########################
if (defined('PHOTOPLOG_RANDOM') && !defined('PHOTOPLOG_HTTPD') && !isset($_REQUEST['c']) && !isset($_REQUEST['n']) && !isset($_REQUEST['u']) && !isset($_REQUEST['q']) && !isset($_REQUEST['page']) && !isset($_REQUEST['v']))
{
	$photoplog_numbermembers = vb_number_format($vbulletin->userstats['numbermembers']);
	$photoplog_activemembers = vb_number_format($vbulletin->userstats['activemembers']);
	$photoplog_showactivemembers = ($vbulletin->options['activememberdays'] > 0 && ($vbulletin->options['activememberoptions'] & 2)) ? true : false;

	$photoplog_do_sql = "WHERE catid > 0";
	$photoplog_do_arr = array();

	foreach ($photoplog_ds_catopts AS $photoplog_ds_catid => $photoplog_ds_value)
	{
		$photoplog_ds_catid = intval($photoplog_ds_catid);
		if (
			$photoplog_ds_catopts[$photoplog_ds_catid]['parentid'] < 0
				&&
			$photoplog_ds_catopts[$photoplog_ds_catid]['displayorder'] != 0
		)
		{
			$photoplog_child_list = array();
			if (isset($photoplog_list_relatives[$photoplog_ds_catid]))
			{
				$photoplog_child_list = $photoplog_list_relatives[$photoplog_ds_catid];
			}
			$photoplog_memy_catids = array_merge(array($photoplog_ds_catid),$photoplog_child_list);

			foreach ($photoplog_memy_catids AS $photoplog_memy_catid)
			{
				if ($photoplog_ds_catopts[$photoplog_memy_catid]['displayorder'] != 0)
				{
					$photoplog_do_arr[] = intval($photoplog_memy_catid);
				}
			}

			unset($photoplog_child_list, $photoplog_memy_catids);
		}
	}

	if (count($photoplog_do_arr))
	{
		$photoplog_do_sql = "WHERE catid IN (" . implode(",", $photoplog_do_arr) .")";
	}

	$photoplog_numberfiles = 0;
	$photoplog_numbercomments = 0;
	$photoplog_numberviews = 0;
	$photoplog_sumdiskspace = 0;

	if (defined('PHOTOPLOG_USER1') || defined('PHOTOPLOG_USER7'))
	{
		$photoplog_stats_cnt = $db->query_first_slave("SELECT SUM(num_views) AS sum1,
			SUM(sum_filesize) AS sum2, SUM(num_uploads) AS sum3, SUM(num_comments) AS sum4
			FROM " . PHOTOPLOG_PREFIX . "photoplog_catcounts
			$photoplog_do_sql
			$photoplog_catid_sql3
			$photoplog_admin_sql5
			AND num_uploads > 0
		");
		if (defined('PHOTOPLOG_USER1'))
		{
			$photoplog_numberviews = vb_number_format(intval($photoplog_stats_cnt['sum1']));
			//$photoplog_sumdiskspace = vb_number_format(intval($photoplog_stats_cnt['sum2']), 1, true);
			$photoplog_sumdiskspace = round(floatval($photoplog_stats_cnt['sum2'] / 1073741824), 2) . ' ' . $vbphrase[gigabytes];
			$photoplog_numberfiles = vb_number_format(intval($photoplog_stats_cnt['sum3']));
		}
		if (defined('PHOTOPLOG_USER7'))
		{
			$photoplog_numbercomments = vb_number_format(intval($photoplog_stats_cnt['sum4']));
		}
		$db->free_result($photoplog_stats_cnt);
	}

	($hook = vBulletinHook::fetch_hook('photoplog_settings_statbar')) ? eval($hook) : false;

	eval('$photoplog[\'stat_bar\'] = "' . fetch_template('photoplog_stat_bar') . '";');
}

?>