<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','misc');
define('PHOTOPLOG_LEVEL','bypass');
require_once('./settings.php');

// ###################### REDIRECT TO FULL PATH ###########################
define('PHOTOPLOG_REDIRECT', '18164'); // define as whatever to indicate redirect
if ($_REQUEST['do'] == 'getsmilies' && PHOTOPLOG_REDIRECT)
{
	$vbulletin->input->clean_array_gpc('g', array(
		'editorid' => TYPE_NOHTML
	));

	$photoplog_editorid = $vbulletin->GPC['editorid'];
	$photoplog_popup_link = $vbulletin->options['bburl']."/misc.php?".$vbulletin->session->vars['sessionurl']."do=getsmilies&amp;editorid=".$photoplog_editorid;

	exec_header_redirect($photoplog_popup_link);
	exit();
}
else if ($_REQUEST['do'] == 'buddylist' && PHOTOPLOG_REDIRECT)
{
	$photoplog_popup_link = $vbulletin->options['bburl']."/misc.php?".$vbulletin->session->vars['sessionurl']."do=buddylist&amp;focus=1";

	exec_header_redirect($photoplog_popup_link);
	exit();
}

?>