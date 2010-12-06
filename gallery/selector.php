<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','selector');
define('PHOTOPLOG_LEVEL','bypass');
require_once('./settings.php');

// ########################### Start Index Page ###########################
($hook = vBulletinHook::fetch_hook('photoplog_selector_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'view';
}

if ($_REQUEST['do'] == 'view')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'e' => TYPE_NOHTML,
		'page' => TYPE_UINT,
		'pp' => TYPE_UINT,
		'aid' => TYPE_UINT,
		'albumid' => TYPE_UINT,
		'cid' => TYPE_UINT,
		'catid' => TYPE_UINT
	));

	$photoplog['userid'] = intval($vbulletin->userinfo['userid']);

	$photoplog_editorid = $vbulletin->GPC['e'];
	$photoplog_page_num = $vbulletin->GPC['page'];
	$photoplog_per_page = $vbulletin->GPC['pp'];

	$photoplog_albumid_get = $vbulletin->GPC['aid'];
	$photoplog_albumid_post = $vbulletin->GPC['albumid'];
	$photoplog_albumid_default = max($photoplog_albumid_get, $photoplog_albumid_post);

	$photoplog_catid_get = $vbulletin->GPC['cid'];
	$photoplog_catid_post = $vbulletin->GPC['catid'];
	$photoplog_catid_default = max($photoplog_catid_get, $photoplog_catid_post);

	$photoplog_albumid_link = '';
	$photoplog_catid_link = '';

	$photoplog_list_categories_row = $photoplog_list_categories;
	$photoplog_list_categories_row[-1] = $vbphrase['photoplog_select_one'];

	if (!empty($photoplog_perm_not_allowed_bits))
	{
		array_walk($photoplog_list_categories_row, 'photoplog_append_key', '');
		$photoplog_list_categories_row = array_flip(array_diff(array_flip($photoplog_list_categories_row),$photoplog_perm_not_allowed_bits));
		array_walk($photoplog_list_categories_row, 'photoplog_remove_key', '');
	}

	$photoplog['select_category'] = "<select name=\"catid\" id=\"sel_catid\" tabindex=\"1\" onchange=\"document.getElementById('photoplog_selector_form').submit();\">\n";
	$photoplog['select_category'] .= photoplog_select_options($photoplog_list_categories_row, $photoplog_catid_default);
	$photoplog['select_category'] .= "</select>\n";

	$photoplog_list_albums_row = array();
	$photoplog_list_albums_row[-1] = $vbphrase['photoplog_select_one'];

	$photoplog_album_infos = $db->query_read_slave("SELECT albumid, title
		FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
		WHERE userid = ".intval($photoplog['userid'])."
		ORDER BY dateline DESC
	");

	while ($photoplog_album_info = $db->fetch_array($photoplog_album_infos))
	{
		$photoplog_album_id = intval($photoplog_album_info['albumid']);
		$photoplog_list_albums_row[$photoplog_album_id] = $photoplog_album_info['title'];
	}

	$db->free_result($photoplog_album_infos);

	$photoplog['select_album'] = "<select name=\"albumid\" id=\"sel_albumid\" tabindex=\"1\" onchange=\"document.getElementById('photoplog_selector_form').submit();\">\n";
	$photoplog['select_album'] .= photoplog_select_options($photoplog_list_albums_row, $photoplog_albumid_default);
	$photoplog['select_album'] .= "</select>\n";

	$photoplog_where_sql = 'WHERE userid = '.intval($photoplog['userid']);

	if ($photoplog_albumid_default)
	{
		$photoplog_albumid_link = '&amp;aid='.intval($photoplog_albumid_default);
		$photoplog_albumid_sql = 'AND albumid = '.intval($photoplog_albumid_default);

		$photoplog_album_info = $db->query_first_slave("SELECT fileids
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE userid = ".intval($photoplog['userid'])."
			$photoplog_albumid_sql
			LIMIT 1
		");

		$photoplog_album_fileids = unserialize($photoplog_album_info['fileids']);

		$photoplog_where_sql = 'WHERE 1=0';
		if (!empty($photoplog_album_fileids))
		{
			$photoplog_where_sql = 'WHERE fileid IN ('.implode(',', $photoplog_album_fileids).')';
		}

		$db->free_result($photoplog_album_info);
	}

	$photoplog_catid_sql = '';

	if ($photoplog_catid_default && !in_array($photoplog_catid_default, $photoplog_perm_not_allowed_bits))
	{
		$photoplog_catid_link = '&amp;cid='.intval($photoplog_catid_default);
		$photoplog_catid_sql = 'AND catid = '.intval($photoplog_catid_default);
	}

	($hook = vBulletinHook::fetch_hook('photoplog_selector_sql')) ? eval($hook) : false;

	$photoplog_file_info = $db->query_first_slave("SELECT COUNT(*) AS cnt1
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		$photoplog_where_sql
		$photoplog_catid_sql
	");

	$photoplog_file_tot = intval($photoplog_file_info['cnt1']);

	$db->free_result($photoplog_file_info);

	sanitize_pageresults($photoplog_file_tot, $photoplog_page_num, $photoplog_per_page, 5, 5);
	$photoplog_limit_lower = ($photoplog_page_num - 1) * $photoplog_per_page;

	if ($photoplog_limit_lower < 0)
	{
		$photoplog_limit_lower = 0;
	}

	$photoplog_limit_lower = intval($photoplog_limit_lower);
	$photoplog_per_page = intval($photoplog_per_page);

	$photoplog_file_infos = $db->query_read_slave("SELECT fileid,userid,filename
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			$photoplog_where_sql
			$photoplog_catid_sql
			ORDER BY dateline DESC
			LIMIT $photoplog_limit_lower,$photoplog_per_page
	");

	$photoplog_sort_url = $photoplog['location'].'/selector.php?'.$vbulletin->session->vars['sessionurl'].'do=view'.$photoplog_catid_link.$photoplog_albumid_link.'&amp;e='.urlencode($photoplog_editorid);
	$photoplog['pagenav'] = construct_page_nav($photoplog_page_num, $photoplog_per_page, $photoplog_file_tot, $photoplog_sort_url);

	$photoplog_cnt = 0;
	$photoplog_selector_bits = '';
	$photoplog_bbcode_link = $vbulletin->options['photoplog_bbcode_link'];

	while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
	{
		$photoplog_cnt ++;

		$photoplog['fileid'] = intval($photoplog_file_info['fileid']);
		$photoplog['userid'] = intval($photoplog_file_info['userid']);
		$photoplog['filename'] = strval($photoplog_file_info['filename']);

		$photoplog_urllink = $photoplog_bbcode_link.'/index.php?n='.$photoplog['fileid'];

		$photoplog_default_size = $vbulletin->options['photoplog_default_size'];
		if (!in_array($photoplog_default_size, array('small','medium','large')))
		{
			$photoplog_default_size = '';
		}

		if (
			$vbulletin->options['photoplog_dynamic_link']
				||
			($vbulletin->options['photoplog_watermark_img'] && ($photoplog_default_size == '' || $photoplog_default_size == 'large'))
		)
		{
			$photoplog_imglink = $photoplog_bbcode_link.'/file.php?n='.$photoplog['fileid'];

			if ($vbulletin->options['photoplog_htaccess_link'])
			{
				$photoplog_imglink = $photoplog_bbcode_link.'/file_'.$photoplog['fileid'].'.jpg';
			}
		}
		else
		{
			if (!empty($photoplog_default_size))
			{
				$photoplog_default_size = '/'.$photoplog_default_size;
			}
			$photoplog_imglink = $photoplog_bbcode_link.'/'.$vbulletin->options['photoplog_upload_dir'].'/'.$photoplog['userid'].$photoplog_default_size.'/'.$photoplog['filename'];
		}

		photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);

		($hook = vBulletinHook::fetch_hook('photoplog_selector_bit')) ? eval($hook) : false;

		if ($vbulletin->options['photoplog_highslide_active'])
		{
			$photoplog_thumblink = $photoplog['file_slink'];
			if (!eregi('^http', $photoplog_thumblink))
			{
				$photoplog_thumblink = $vbulletin->options['photoplog_bbcode_link'] . str_replace($vbulletin->options['photoplog_script_dir'], '', $photoplog_thumblink);
			}
			$photoplog_selector_bits .= '
				<a href="'.$photoplog_imglink.'" onmousedown="photoplog_blockdragdrop1(event);" ondragstart="photoplog_blockdragdrop2(event);" onclick="photoplog_selectimage(\''.addslashes_js($photoplog_editorid).'\', \''.addslashes_js($photoplog_imglink).'\', \''.addslashes_js($photoplog_thumblink).'\'); window.focus(); return false;"><img src="'.$photoplog['file_slink'].'" alt="'.$vbphrase['photoplog_image'].'" border="0" /></a>
			';
		}
		else
		{
			$photoplog_selector_bits .= '
				<a href="'.$photoplog_imglink.'" onmousedown="photoplog_blockdragdrop1(event);" ondragstart="photoplog_blockdragdrop2(event);" onclick="photoplog_selectimage(\''.addslashes_js($photoplog_editorid).'\', \''.addslashes_js($photoplog_urllink).'\', \''.addslashes_js($photoplog_imglink).'\'); window.focus(); return false;"><img src="'.$photoplog['file_slink'].'" alt="'.$vbphrase['photoplog_image'].'" border="0" /></a>
			';
		}
	}

	$db->free_result($photoplog_file_infos);

	if (!$photoplog_cnt)
	{
		$photoplog_selector_bits = $vbphrase['photoplog_not_available'];
	}

	$photoplog_base_href = "<base href=\"".$vbulletin->options['bburl']."/".$vbulletin->options['forumhome'].".php\" />\n";
	$headinclude = $photoplog_base_href.$headinclude;

	$photoplog_selector_list = $stylevar['htmldoctype'].'
		<html dir="'.$stylevar['textdirection'].'" lang="'.$stylevar['languagecode'].'">
		<head>
		'.$headinclude.'
		<title>'.$vbphrase['photoplog_images'].'</title>
		<script type="text/javascript">
		<!--
		function photoplog_suppresserror()
		{
			return true;
		}
		window.onerror = photoplog_suppresserror;

		function photoplog_blockdragdrop1(theevent)
		{
			if (!is_ie)
			{
				theevent.stopPropagation();
				theevent.preventDefault();
				return true;
			}
		}

		function photoplog_blockdragdrop2(theevent)
		{
			if (is_ie)
			{
				window.event.cancelBubble = true;
				window.event.returnValue = false;
				window.event.dataTransfer.effectAllowed = "none";
				window.event.dataTransfer.dropEffect = "none";
				return window.event;
			}
		}

		function photoplog_selectimage(theeid, theurl, theimg)
		{
			if (window.opener && !window.opener.closed)
			{
				if (window.opener.vB_Editor[theeid].wysiwyg_mode)
				{
					var theurlimg = \'<a href="\' + theurl + \'"><img src="\' + theimg + \'" border="0" alt="" /></a> \';
					window.opener.vB_Editor[theeid].insert_text(theurlimg, false);
				}
				else
				{
					var theurlimg = \'[URL=\' + theurl + \'][IMG]\' + theimg + \'[/IMG][/URL] \';
					window.opener.vB_Editor[theeid].insert_text(theurlimg, false);
				}

				document.getElementById(\'photoplog_selector_form\').submit();
			}
		}
		//-->
		</script>
		</head>
		<body style="margin: 0px;" onload="self.focus();">
		<table class="tborder" cellpadding="'.$stylevar['cellpadding'].'" cellspacing="'.$stylevar['cellspacing'].'" border="0" width="100%" id="selectortable">
		<tr>
			<td class="tcat">
				'.$vbphrase['photoplog_images'].'
				<div class="smallfont">'.$vbphrase['photoplog_click_an_image_to_insert_it_into_your_message'].'</div>
			</td>
		</tr>
		<tr>
			<td class="alt2">
				<form id="photoplog_selector_form" action="'.$photoplog['location'].'/selector.php" method="post">
				<input type="hidden" name="securitytoken" value="'.$vbulletin->userinfo['securitytoken'].'" />
				<input type="hidden" name="s" value="'.$vbulletin->session->vars['sessionhash'].'" />
				<input type="hidden" name="e" value="'.$photoplog_editorid.'" />
				'.$vbphrase['photoplog_category'].' '.$photoplog['select_category'].'
				'.$vbphrase['photoplog_album'].' '.$photoplog['select_album'].'
				<input type="submit" class="button" value="'.$vbphrase['photoplog_go'].'" />
				</form>
			</td>
		</tr>
		<tr>
			<td class="alt2">
				'.$photoplog_selector_bits.'
			</td>
		</tr>
		<tr>
			<td class="tfoot" align="center"><input type="button" class="button" value="'.$vbphrase['photoplog_close_window'].'" onclick="self.close()" /></td>
		</tr>
		</table>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 0px;">
		<tr valign="top">
			<td align="'.$stylevar['right'].'">'.$photoplog['pagenav'].'</td>
		</tr>
		</table>
		</body>
		</html>';

	($hook = vBulletinHook::fetch_hook('photoplog_selector_list')) ? eval($hook) : false;

	echo $photoplog_selector_list;
}

($hook = vBulletinHook::fetch_hook('photoplog_selector_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'view')
{
	photoplog_index_bounce();
}

?>