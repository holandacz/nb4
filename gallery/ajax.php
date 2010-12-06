<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','ajax');
define('PHOTOPLOG_LEVEL','editor');
define('LOCATION_BYPASS',1);
define('NOPMPOPUP',1);
define('NOSHUTDOWNFUNC',1);
require_once('./settings.php');

// ####################### DETERMINE VB VERSION ###########################
$photoplog_fileversion = 0;
$photoplog_vbversion = explode(".", FILE_VERSION);

if ($photoplog_vbversion[0] == 3 && $photoplog_vbversion[1] == 5)
{
	$photoplog_fileversion = 5;
}
else if ($photoplog_vbversion[0] == 3 && $photoplog_vbversion[1] >= 6)
{
	$photoplog_fileversion = 6;
}
else
{
	exit();
}

if ($photoplog_fileversion == 6)
{
	require_once(DIR . '/includes/class_xml.php');
}

// ################## FULL PATH THE AJAX SWITCH STUFF #####################
$photoplog_ajax_flag = 0;

if ($_POST['do'] == 'editorswitch')
{
	$photoplog_ajax_flag = 1;

	$vbulletin->input->clean_array_gpc('p', array(
		'towysiwyg' => TYPE_BOOL,
		'message' => TYPE_STR,
		'parsetype' => TYPE_STR,
		'allowsmilie' => TYPE_UINT
	));

	// yep this is how fileid and catid is passed in
	$photoplog['fileid'] = $vbulletin->GPC['allowsmilie'];

	$photoplog_file_info = '';
	if ($photoplog['fileid'] < 10864246810)
	{
		$photoplog_file_info = $db->query_first_slave("SELECT catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid = ".intval($photoplog['fileid'])."
			$photoplog_catid_sql1
			$photoplog_admin_sql1
		");
	}
	else
	{
		$photoplog_file_info['catid'] = $photoplog['fileid'] - 10864246810; // catid
	}

	$do_html = 0;
	$do_imgcode = 0;

	if ($photoplog_file_info)
	{
		$photoplog['catid'] = intval($photoplog_file_info['catid']);
		if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
		{
			$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
			$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

			$do_html = ($photoplog_catoptions['allowhtml']) ? 1 : 0;
			$do_imgcode = ($photoplog_catoptions['allowimgcode']) ? 1 : 0;
		}
	}

	$vbulletin->GPC['message'] = convert_urlencoded_unicode($vbulletin->GPC['message']);

	if ($photoplog_fileversion == 6)
	{
		$xml = new vB_AJAX_XML_Builder($vbulletin, 'text/xml');
	}

	require_once(DIR . '/includes/functions_wysiwyg.php');

	if ($vbulletin->GPC['towysiwyg'])
	{
		if ($do_imgcode)
		{
			$vbulletin->GPC['message'] = preg_replace("/\[img\]/i","photoplog_front_vb_bb_img_tag",$vbulletin->GPC['message']);
			$vbulletin->GPC['message'] = preg_replace("/\[\/img\]/i","photoplog_back_vb_bb_img_tag",$vbulletin->GPC['message']);
		}

		ob_start();
		echo parse_wysiwyg_html(htmlspecialchars_uni($vbulletin->GPC['message']), false, $vbulletin->GPC['parsetype'], $vbulletin->GPC['allowsmilie']);
		$photoplog_html_output = ob_get_contents();
		ob_end_clean();

		if ($do_imgcode)
		{
			$photoplog_html_output = str_replace(
				array('photoplog_front_vb_bb_img_tag','photoplog_back_vb_bb_img_tag'),
				array('<img src="','">'),
				$photoplog_html_output
			);
		}

		$photoplog_html_output = str_replace("src=\"images/smilies/", "src=\"".$vbulletin->options['bburl']."/images/smilies/", $photoplog_html_output);

		if ($photoplog_fileversion == 6)
		{
			$xml->add_tag('message', $photoplog_html_output);
		}
		else
		{
			echo $photoplog_html_output;
		}
	}
	else
	{
		switch ($vbulletin->GPC['parsetype'])
		{
			case 'nonforum':
				$dohtml = $do_html;
				break;
			default:
				$dohtml = 0;
		}

		$vbulletin->GPC['message'] = str_replace("src=\"".$vbulletin->options['bburl']."/images/smilies/", "src=\"images/smilies/", $vbulletin->GPC['message']);

		if ($photoplog_fileversion == 6)
		{
			$xml->add_tag('message', convert_wysiwyg_html_to_bbcode($vbulletin->GPC['message'], $dohtml));
		}
		else
		{
			echo convert_wysiwyg_html_to_bbcode($vbulletin->GPC['message'], $dohtml);
		}
	}

	if ($photoplog_fileversion == 6)
	{
		$xml->print_xml();
	}
}

// ##################### REQUIRE VB AJAX IF NEEDED ########################
if (!$photoplog_ajax_flag && 1 == 2)
{
	chdir(PHOTOPLOG_FWD);
	require_once(DIR . '/ajax.php');
	chdir(PHOTOPLOG_BWD);
}

?>