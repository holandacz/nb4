<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// #################### PREVENT UNAUTHORIZED USERS ########################
if (!defined('VB_AREA') || !defined('THIS_SCRIPT') || !defined('DIR'))
{
	exit(); // DO NOT REMOVE THIS!
}

if (THIS_SCRIPT != 'memberlist')
{
	exit(); // this is only for the memberlist plugin !!!
}

// ####################### GRAB PLOG BACK-END #############################
if (!defined('PHOTOPLOG_SCRIPT'))
{
	require_once(DIR.'/includes/photoplog_prefix.php');

	$photoplog = array();
	$photoplog_ds_catquery = $db->query_first("SELECT data
		FROM " . TABLE_PREFIX . "datastore
		WHERE title = 'photoplog_dscat'
	");
	$photoplog_ds_catopts = unserialize($photoplog_ds_catquery['data']);
	$db->free_result($photoplog_ds_catquery);

	if (!is_array($photoplog_ds_catopts))
	{
		$photoplog_ds_catopts = array();
	}

	define('PHOTOPLOG_SCRIPT','init');
	require_once($vbulletin->options['photoplog_full_path'].'/permissions.php');
}

$photoplog['location'] = $vbulletin->options['photoplog_script_dir'];

// #################### INITIALIZE ADMIN SQL BIT ##########################
require_once(DIR . '/includes/adminfunctions.php');

$photoplog_admin_sql1a = 'AND f1.moderate = 0';

if (can_administer('canadminforums'))
{
	$photoplog_admin_sql1a = '';
}

// #################### DETERMINE THUMB DISPLAY ###########################
$vbulletin->input->clean_array_gpc('r', array(
	'photoplog_fileslower' => TYPE_UINT,
	'photoplog_filesupper' => TYPE_UINT,
	'photoplog_commentslower' => TYPE_UINT,
	'photoplog_commentsupper' => TYPE_UINT
));

if ($vbulletin->GPC['photoplog_fileslower'])
{
	$condition .= " AND user.photoplog_filecount >= " . intval($vbulletin->GPC['photoplog_fileslower']);
	$urladd .= '&amp;photoplog_fileslower=' . urlencode($vbulletin->GPC['photoplog_fileslower']);
}
if ($vbulletin->GPC['photoplog_filesupper'])
{
	$condition .= " AND user.photoplog_filecount < " . intval($vbulletin->GPC['photoplog_filesupper']);
	$urladd .= '&amp;photoplog_filesupper=' . urlencode($vbulletin->GPC['photoplog_filesupper']);
}
if ($vbulletin->GPC['photoplog_commentslower'])
{
	$condition .= " AND user.photoplog_commentcount >= " . intval($vbulletin->GPC['photoplog_commentslower']);
	$urladd .= '&amp;photoplog_commentslower=' . urlencode($vbulletin->GPC['photoplog_commentslower']);
}
if ($vbulletin->GPC['photoplog_commentsupper'])
{
	$condition .= " AND user.photoplog_commentcount < " . intval($vbulletin->GPC['photoplog_commentsupper']);
	$urladd .= '&amp;photoplog_commentsupper=' . urlencode($vbulletin->GPC['photoplog_commentsupper']);
}

if ($_REQUEST['sortfield'] == 'photoplog_filessort')
{
	$sqlsort = 'user.photoplog_filecount';
	$sortfield = 'photoplog_filessort';
}
if ($_REQUEST['sortfield'] == 'photoplog_commentssort')
{
	$sqlsort = 'user.photoplog_commentcount';
	$sortfield = 'photoplog_commentssort';
}

$totalcols ++;
$photoplog_letter_sql = '';
$photoplog_ausername = $vbulletin->GPC['ausername'];

if (empty($ltr) && empty($photoplog_ausername))
{
	$photoplog_num_check = $db->query_first_slave("SELECT COUNT(*) AS cnt FROM " . TABLE_PREFIX . "user");
	$photoplog_allltr_cnt = intval($photoplog_num_check['cnt']);
	$db->free_result($photoplog_num_check);

	$photoplog_perpage = max($vbulletin->options['memberlistperpage'], min(100, $perpage));
	$photoplog_pagenumber = max(1, min($photoplog_allltr_cnt, $pagenumber));
	$photoplog_cutpoint = $photoplog_pagenumber * $photoplog_perpage * 4;

	if ($photoplog_allltr_cnt > $photoplog_cutpoint)
	{
		$photoplog_query_ltr1 = '[';

		for ($i=65; $i<=91; $i++)
		{
			$photoplog_query_ltr2 = chr($i);

			$photoplog_num_check = $db->query_first_slave("SELECT COUNT(*) AS cnt
				FROM " . TABLE_PREFIX . "user AS user
				LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield USING (userid)
				$hook_query_joins
				WHERE 1=1
				AND (username >= '" . $db->escape_string($photoplog_query_ltr1) . "'
				OR username < '" . $db->escape_string($photoplog_query_ltr2) . "')
				AND $condition
				AND usergroupid IN ($ids)
				$hook_query_where
			");

			if (intval($photoplog_num_check['cnt']) >= $photoplog_perpage * $photoplog_pagenumber)
			{
				$photoplog_letter_sql = "AND (username >= '" . $db->escape_string($photoplog_query_ltr1) . "'
					OR username < '" . $db->escape_string($photoplog_query_ltr2) . "')";
				break;
			}
		}
		$db->free_result($photoplog_num_check);
	}
}
else if ($ltr || $photoplog_ausername)
{
	$photoplog_query_ltr1 = max($ltr, $photoplog_ausername);

	if ($photoplog_query_ltr1 == '#')
	{
		$photoplog_query_ltr1 = '[';
		$photoplog_query_ltr2 = 'A';
		$photoplog_letter_sql = "AND (username >= '" . $db->escape_string($photoplog_query_ltr1) . "' OR username < '" . $db->escape_string($photoplog_query_ltr2) . "')";
	}
	else
	{
		$photoplog_query_ltr2 = chr(intval(ord($photoplog_query_ltr1)) + 1);
		$photoplog_letter_sql = "AND username >= '" . $db->escape_string($photoplog_query_ltr1) . "' AND username < '" . $db->escape_string($photoplog_query_ltr2) . "'";
	}
}

$photoplog_memberlist_infos = $db->query_read_slave("SELECT user.userid
	FROM " . TABLE_PREFIX . "user AS user
	LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield ON (userfield.userid = user.userid)
	$hook_query_joins
	WHERE 1=1
	$photoplog_letter_sql
	AND $condition
	AND usergroupid IN ($ids)
	$hook_query_where
");

$photoplog_memberlist_userids = array();

while ($photoplog_memberlist_info = $db->fetch_array($photoplog_memberlist_infos))
{
	$photoplog_memberlist_userids[] = intval($photoplog_memberlist_info['userid']);
}

$db->free_result($photoplog_memberlist_infos);

if ($ltr == '#' || $photoplog_ausername) // help vB queries use index !!!
{
	$condition .= ' '.$photoplog_letter_sql;
}

$photoplog_letter_sql = str_replace('username', 'f1.username', $photoplog_letter_sql);

$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], 0, 1).'link';
$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], -1, 1).'link';

$photoplog['do_highslide'] = 0;
if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
{
	$photoplog['do_highslide'] = 1;
}

$photoplog_memberlist_thumbs = array();

if (!empty($photoplog_memberlist_userids))
{
	$photoplog_memberlist_infos = $db->query_read_slave("
		SELECT f1.userid, f1.fileid, f1.filename, f1.title
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads AS f1
		LEFT JOIN " . PHOTOPLOG_PREFIX . "photoplog_fileuploads AS f2
		ON (f2.userid = f1.userid AND f2.fileid > f1.fileid)
		WHERE 1=1
		$photoplog_letter_sql
		AND f2.fileid IS NULL
		AND f1.userid IN (" . implode(',', array_unique($photoplog_memberlist_userids)) . ")
		$photoplog_catid_sql1a
		$photoplog_admin_sql1a
	");

	unset($photoplog_memberlist_userids);

	while ($photoplog_memberlist_info = $db->fetch_array($photoplog_memberlist_infos))
	{
		$photoplog['title'] = htmlspecialchars_uni($photoplog_memberlist_info['title']);

		$photoplog['hscnt'] = intval($photoplog['hscnt']) + 1;

		photoplog_file_link($photoplog_memberlist_info['userid'],$photoplog_memberlist_info['fileid'],$photoplog_memberlist_info['filename']);

		$photoplog_memberlist_thumb = '<a href="' . $photoplog['location'] . '/index.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $userinfo['userid'] . '"><img style="' . $vbulletin->options['photoplog_csssmall_thumbs'] . '" src="' . $photoplog['file_slink'] . '" alt="' . $photoplog['title'] . '" border="0" /></a>';
		if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
		{
			$photoplog_memberlist_thumb = '
				<script type="text/javascript">hs.registerOverlay({thumbnailId: \'hsjs' . $photoplog['hscnt'] . '\', overlayId: \'hscontrolbar\', position: \'top right\', hideOnMouseOut: true});</script>
				<a id="hsjs' . $photoplog['hscnt'] . '" href="' . $photoplog[$photoplog_hslink2] . '" class="highslide" onclick="return hs.expand(this, {slideshowGroup: \'searchinfo\'})"><img style="' . $vbulletin->options['photoplog_csssmall_thumbs'] . '" src="' . $photoplog[$photoplog_hslink1] . '" alt="' . $photoplog['title'] . '" border="0" /></a>
				<div class="highslide-caption" id="caption-for-hsjs' . $photoplog['hscnt'] . '"><a href="' . $photoplog['location'] . '/index.php?' . $vbulletin->session->vars['sessionurl'] . 'n=' . $photoplog_memberlist_info['fileid'] . '">' . $photoplog['title'] . '</a>&nbsp;</div>
			';
		}

		$photoplog_memberlist_thumbs[$photoplog_memberlist_info['userid']] = $photoplog_memberlist_thumb;
	}

	$db->free_result($photoplog_memberlist_infos);
}

?>