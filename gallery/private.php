<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','private');
define('PHOTOPLOG_LEVEL','bypass');
require_once('./settings.php');

// ###################### REDIRECT TO FULL PATH ###########################
define('PHOTOPLOG_REDIRECT', '18164'); // define as whatever to indicate redirect
if ($_REQUEST['do'] == 'showpm' && PHOTOPLOG_REDIRECT)
{
	$vbulletin->input->clean_array_gpc('g', array(
		'pmid' => TYPE_UINT
	));

	$photoplog_pmid = $vbulletin->GPC['pmid'];
	$photoplog_popup_link = $vbulletin->options['bburl']."/private.php?".$vbulletin->session->vars['sessionurl']."do=showpm&amp;pmid=".$photoplog_pmid;

	exec_header_redirect($photoplog_popup_link);
	exit();
}
else if (PHOTOPLOG_REDIRECT)
{
	$photoplog_popup_link = $vbulletin->options['bburl']."/private.php".$vbulletin->session->vars['sessionurl_q'];

	exec_header_redirect($photoplog_popup_link);
	exit();
}

?>