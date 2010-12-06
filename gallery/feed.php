<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('NOSHUTDOWNFUNC', 1);
define('SKIP_SESSIONCREATE', 1);
define('DIE_QUIETLY', 1);
define('THIS_SCRIPT', 'feed');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################## GRAB THE FULL PATH ############################
require_once('./config.php');

// ######################### REQUIRE BACK-END ############################
define('PHOTOPLOG_BWD', (($getcwd = getcwd()) ? $getcwd : '.'));

chdir(PHOTOPLOG_FWD);
require_once('./global.php');
chdir(PHOTOPLOG_BWD);

$vbulletin->input->clean_array_gpc('g', array(
	'count' => TYPE_UINT,
	'cid' => TYPE_UINT
));

$vbulletin->GPC['c'] = intval($vbulletin->GPC['cid']);

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

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

($hook = vBulletinHook::fetch_hook('photoplog_feed_start')) ? eval($hook) : false;

$photoplog_link_limit = intval($vbulletin->GPC['count']);
$photoplog_link_catid = intval($vbulletin->GPC['cid']);
$photoplog_feed_limit = max(0, intval($vbulletin->options['photoplog_feed_limit']));

$photoplog_thirty_days_ago = TIMENOW - (86400 * 30);
$photoplog_feed_catid = 'AND f1.dateline > '.intval($photoplog_thirty_days_ago);
if ($photoplog_link_catid)
{
	$photoplog_feed_catid = 'AND f1.catid = '.intval($photoplog_link_catid).' '.$photoplog_feed_catid;
}

$photoplog_feed_active = ($vbulletin->options['photoplog_is_active'] && $vbulletin->options['photoplog_feed_active']) ? 1 : 0;

if ($photoplog_feed_active && $photoplog_feed_limit)
{
	if ($photoplog_link_limit > 0)
	{
		$photoplog_feed_limit = min($photoplog_link_limit, $photoplog_feed_limit);
	}

	$photoplog_feed_infos = $db->query_read_slave("
		SELECT f1.fileid AS fileid, f1.catid AS filecatid, f1.userid AS fileuserid,
			f1.username AS fileusername, f1.title AS filetitle, f1.description AS filedescription,
			f1.filename AS filename, f1.dateline AS filedateline, f2.commentid AS commentid,
			f2.userid AS commentuserid, f2.username AS commentusername, f2.title AS commenttitle,
			f2.comment AS commenttext, f2.dateline AS commentdateline, f3.title AS categorytitle
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads AS f1
		LEFT JOIN " . PHOTOPLOG_PREFIX . "photoplog_ratecomment AS f2 ON (f2.fileid = f1.fileid)
		LEFT JOIN " . PHOTOPLOG_PREFIX . "photoplog_categories AS f3 ON (f3.catid = f1.catid)
		WHERE 1=1
		$photoplog_feed_catid
		$photoplog_catid_sql1a
		$photoplog_catid_sql4a
		AND f1.moderate = 0
		AND IFNULL(f2.moderate, 0) = 0
		LIMIT ".intval($photoplog_feed_limit)."
	");
}

if ($photoplog_feed_active && $photoplog_feed_limit && $db->num_rows($photoplog_feed_infos))
{
	$photoplog_feed_subdir = '';
	if (in_array($vbulletin->options['photoplog_feed_subdir'], array('small','medium','large')))
	{
		$photoplog_feed_subdir = $vbulletin->options['photoplog_feed_subdir'] . '/';
	}

	if ($stylevar['charset'])
	{
		header('Content-Type: text/xml; charset=' . $stylevar['charset']);
	}
	else
	{
		header('Content-Type: text/xml');
	}
	header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo '<?xml version="1.0" encoding="' . $stylevar['charset'] . '"?>' . "\r\n\r\n";
	echo "<rss version=\"2.0\">\r\n";
	echo "<channel>\r\n";
	echo "\t<title>" . htmlspecialchars_uni($vbulletin->options['hometitle'] . " - " . $vbphrase['photoplog_gallery']) . "</title>\r\n";
	echo "\t<link>" . htmlspecialchars_uni($vbulletin->options['photoplog_bbcode_link']) . "/</link>\r\n";
	echo "\t<description><![CDATA[" . htmlspecialchars_uni($vbulletin->options['description']) . "]]></description>\r\n";
	echo "\t<language>" . $stylevar['languagecode'] . "</language>\r\n";
	echo "\t<pubDate>" . gmdate('D, d M Y H:i:s', TIMENOW) . " GMT</pubDate>\r\n";
	echo "\t<generator>PhotoPlog</generator>\r\n";
	echo "\t<ttl>60</ttl>\r\n";

	$photoplog_fileid = '';
	$photoplog_filecatid = '';
	$photoplog_fileuserid = '';
	$photoplog_fileusername = '';
	$photoplog_filetitle = '';
	$photoplog_filedescription = '';
	$photoplog_filename = '';
	$photoplog_filedateline = '';
	$photoplog_commentid = '';
	$photoplog_commentuserid = '';
	$photoplog_commentusername = '';
	$photoplog_commenttitle = '';
	$photoplog_commenttext = '';
	$photoplog_commentdateline = '';
	$photoplog_categorytitle = '';
	$photoplog_commentbits = '';
	$photoplog_imagelink = '';

	$photoplog_tempval = 0;

	while ($photoplog_feed_info = $db->fetch_array($photoplog_feed_infos))
	{
		$photoplog_fileid = intval($photoplog_feed_info['fileid']);

		if (($photoplog_tempval != $photoplog_fileid) && $photoplog_tempval > 0)
		{
			echo "\t\t<description><![CDATA[" . $photoplog_filedescription . $photoplog_imagelink . $photoplog_commentbits . "]]></description>\r\n";
			echo "\t\t<pubDate>" . gmdate('D, d M Y H:i:s', $photoplog_filedateline) . " GMT</pubDate>\r\n";
			echo "\t\t<author><![CDATA[nospam@domain.com (" . $photoplog_fileusername . ")]]></author>\r\n";
			echo "\t\t<category domain=\"" . htmlspecialchars_uni($vbulletin->options['photoplog_bbcode_link']) . "/index.php?c=" . $photoplog_filecatid . "\"><![CDATA[" . $photoplog_categorytitle . "]]></category>\r\n";
			echo "\t</item>\r\n";

			$photoplog_commentbits = '';
		}

		$photoplog_filecatid = intval($photoplog_feed_info['filecatid']);
		$photoplog_fileuserid = intval($photoplog_feed_info['fileuserid']);
		$photoplog_fileusername = htmlspecialchars_uni(unhtmlspecialchars($photoplog_feed_info['fileusername']));
		$photoplog_filetitle = htmlspecialchars_uni(strip_bbcode($photoplog_feed_info['filetitle'],false,true));
		$photoplog_filedescription = htmlspecialchars_uni(strip_bbcode($photoplog_feed_info['filedescription'],false,true));
		$photoplog_filename = htmlspecialchars_uni($photoplog_feed_info['filename']);
		$photoplog_filedateline = intval($photoplog_feed_info['filedateline']);
		$photoplog_commentid = intval($photoplog_feed_info['commentid']);
		$photoplog_commentuserid = intval($photoplog_feed_info['commentuserid']);
		$photoplog_commentusername = htmlspecialchars_uni(unhtmlspecialchars($photoplog_feed_info['commentusername']));
		$photoplog_commenttitle = htmlspecialchars_uni(strip_bbcode($photoplog_feed_info['commenttitle'],false,true));
		$photoplog_commenttext = htmlspecialchars_uni(strip_bbcode($photoplog_feed_info['commenttext'],false,true));
		$photoplog_commentdateline = intval($photoplog_feed_info['commentdateline']);
		$photoplog_categorytitle = htmlspecialchars_uni($photoplog_feed_info['categorytitle']);
		$photoplog_imagelink = "<br /><img src=\"" . $vbulletin->options['photoplog_bbcode_link'] . "/" . $vbulletin->options['photoplog_upload_dir'] . "/" . $photoplog_fileuserid . "/" . $photoplog_feed_subdir . $photoplog_filename . "\" alt=\"" . $photoplog_filetitle . "\" border=\"0\" />";

		if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanviewcomments'])
		{
			if ($photoplog_commentid && $photoplog_commenttext != '')
			{
				$photoplog_commentbits .= "<br />" . $vbphrase['photoplog_comment'] . " - " . $photoplog_commentusername . " - " . gmdate('D, d M Y H:i:s', $photoplog_commentdateline) . " GMT - " . $photoplog_commenttitle . " - " . $photoplog_commenttext;
			}
		}

		if ($photoplog_tempval != $photoplog_fileid)
		{
			echo "\t<item>\r\n";
			echo "\t\t<title>$photoplog_filetitle</title>\r\n";
			echo "\t\t<link>" . $vbulletin->options['photoplog_bbcode_link'] . "/index.php?n=" . $photoplog_fileid . "</link>\r\n";
			echo "\t\t<guid isPermaLink=\"false\">" . $vbulletin->options['photoplog_bbcode_link'] . "/" . $vbulletin->options['photoplog_upload_dir'] . "/" . $photoplog_fileuserid . "/" . $photoplog_feed_subdir . $photoplog_filename . "</guid>\r\n";
		}

		$photoplog_tempval = $photoplog_fileid;
	}

	if ($photoplog_tempval > 0)
	{
		echo "\t\t<description><![CDATA[" . $photoplog_filedescription . $photoplog_imagelink . $photoplog_commentbits . "]]></description>\r\n";
		echo "\t\t<pubDate>" . gmdate('D, d M Y H:i:s', $photoplog_filedateline) . " GMT</pubDate>\r\n";
		echo "\t\t<author><![CDATA[nospam@domain.com (" . $photoplog_fileusername . ")]]></author>\r\n";
		echo "\t\t<category domain=\"" . htmlspecialchars_uni($vbulletin->options['photoplog_bbcode_link']) . "/index.php?c=" . $photoplog_filecatid . "\"><![CDATA[" . $photoplog_categorytitle . "]]></category>\r\n";
		echo "\t</item>\r\n";
	}

	echo "</channel>\r\n";
	echo "</rss>";
}

if ($photoplog_feed_active && $photoplog_feed_limit)
{
	$db->free_result($photoplog_feed_infos);
}

($hook = vBulletinHook::fetch_hook('photoplog_feed_complete')) ? eval($hook) : false;

?>