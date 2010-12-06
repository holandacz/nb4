<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','report');
define('PHOTOPLOG_LEVEL','view');
require_once('./settings.php');

// ######################### Start Report Page ############################
($hook = vBulletinHook::fetch_hook('photoplog_report_start')) ? eval($hook) : false;

if (
	!$vbulletin->userinfo['userid'] || !defined('PHOTOPLOG_USER7')
		||
	in_array($photoplog_perm_catid,$photoplog_perm_not_allowed_bits)
)
{
	photoplog_index_bounce();
}

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'report';
}

if ($_REQUEST['do'] == 'report')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'm' => TYPE_UINT,
		'n' => TYPE_UINT,
		'p' => TYPE_UINT
	));

	$photoplog['commentid'] = $vbulletin->GPC['m'];
	$photoplog['fileid'] = $vbulletin->GPC['n'];
	$photoplog['pagenum'] = $vbulletin->GPC['p'];

	require_once(DIR . '/includes/functions_editor.php');

	$photoplog['textareacols'] = fetch_textarea_width();

	($hook = vBulletinHook::fetch_hook('photoplog_report_form')) ? eval($hook) : false;

	photoplog_output_page('photoplog_report_form', $vbphrase['photoplog_report_item']);
}

if ($_POST['do'] == 'doreport')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'commentid' => TYPE_UINT,
		'fileid' => TYPE_UINT,
		'pagenum' => TYPE_UINT,
		'message' => TYPE_STR
	));

	($hook = vBulletinHook::fetch_hook('photoplog_report_doreport_start')) ? eval($hook) : false;

	$photoplog['commentid'] = $vbulletin->GPC['commentid'];
	$photoplog['fileid'] = $vbulletin->GPC['fileid'];
	$photoplog['pagenum'] = $vbulletin->GPC['pagenum'];
	$photoplog_reason = $vbulletin->GPC['message'];

	$photoplog_return_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog['fileid'];

	if ($vbulletin->GPC['message'] == '')
	{
		exec_header_redirect($photoplog_return_url);
		exit();
	}

	$photoplog_item_count = $db->query_first_slave("SELECT catid, $photoplog_admin_sql4
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid = ".intval($photoplog['fileid'])."
		$photoplog_catid_sql1
		$photoplog_admin_sql1
	");

	$photoplog_comment_page = '';
	if ($photoplog['commentid'] && $photoplog['fileid'] && $photoplog['pagenum'])
	{
		if (!$photoplog_item_count['num_comments'] || $photoplog_item_count['catid'] != $photoplog_perm_catid)
		{
			exec_header_redirect($photoplog_return_url);
			exit();
		}
		$photoplog_comment_page = '&amp;page='.$photoplog['pagenum'].'#comment'.$photoplog['commentid'];
	}
	else if ($photoplog['fileid'])
	{
		if (!$photoplog_item_count['catid'] || $photoplog_item_count['catid'] != $photoplog_perm_catid)
		{
			exec_header_redirect($photoplog_return_url);
			exit();
		}
	}
	else
	{
		exec_header_redirect($photoplog_return_url);
		exit();
	}

	$photoplog_item_url = $vbulletin->options['photoplog_bbcode_link'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog['fileid'].$photoplog_comment_page;
	$photoplog_page_url = $vbulletin->options['photoplog_bbcode_link'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog['fileid'];

	($hook = vBulletinHook::fetch_hook('photoplog_report_doreport_complete')) ? eval($hook) : false;

	$photoplog_subject = $photoplog_message = '';
	eval(fetch_email_phrases('photoplog_reported_item', -1, '', 'photoplog_'));
	vbmail($vbulletin->options['webmasteremail'], $photoplog_subject, $photoplog_message, true);

	$vbulletin->url = $photoplog_return_url;
	eval(print_standard_redirect('redirect_reportthanks'));
}

($hook = vBulletinHook::fetch_hook('photoplog_report_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'report' && $_POST['do'] != 'doreport')
{
	photoplog_index_bounce();
}

?>