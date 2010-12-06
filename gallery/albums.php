<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','albums');
define('PHOTOPLOG_LEVEL','albums');
require_once('./settings.php');

// ########################### Start Albums Page ##########################
($hook = vBulletinHook::fetch_hook('photoplog_albums_start')) ? eval($hook) : false;

$photoplog['album_control'] = 1;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'view';
}

if ($_REQUEST['do'] == 'show')
{
	$photoplog_cnt_bits = 0;
	$photoplog['memberalbums'] = '';
	$photoplog['album_control'] = 0;

	$vbulletin->input->clean_array_gpc('g', array(
		'aid' => TYPE_UINT,
		'page' => TYPE_UINT,
		'pp' => TYPE_UINT,
		'u' => TYPE_UINT
	));

	$photoplog['album_albumid'] = $vbulletin->GPC['aid'];
	$photoplog_page_num = $vbulletin->GPC['page'];
	$photoplog_per_page = $vbulletin->GPC['pp'];
	$photoplog_user_id = $vbulletin->GPC['u'];

	$photoplog_cnt = 0;
	$photoplog['album_bits'] = '';
	$photoplog['album_title'] = '';
	$photoplog['album_description'] = '';
	$photoplog['album_privacy'] = '';
	$photoplog['album_count'] = 0;
	$photoplog['album_pagenav'] = '';

	$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], 0, 1).'link';
	$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], -1, 1).'link';

	$photoplog['do_highslide'] = 0;
	if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
	{
		$photoplog['do_highslide'] = 1;
	}

	if ($photoplog['album_albumid'])
	{
		$photoplog_album_info = $db->query_first_slave("SELECT fileids,title
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE albumid = ".intval($photoplog['album_albumid'])."
			AND userid = ".intval($photoplog_user_id)."
			AND visible = 1
		");

		if (!$photoplog_album_info)
		{
			echo $vbphrase['photoplog_not_available'];
			exit();
		}

		$photoplog_album_fileids = unserialize($photoplog_album_info['fileids']);
		$photoplog['album_title'] = htmlspecialchars_uni($photoplog_album_info['title']);

		$db->free_result($photoplog_album_info);

		if (!is_array($photoplog_album_fileids))
		{
			$photoplog_album_fileids = array();
		}

		$photoplog_file_tot = count($photoplog_album_fileids);
		if (!$photoplog_file_tot)
		{
			echo $vbphrase['photoplog_not_available'];
			exit();
		}

		$photoplog_page_tot = 12;
		if (!$photoplog_page_num || $photoplog_page_num == 1)
		{
			$photoplog_page_start = 0;
		}
		else
		{
			$photoplog_page_start = $photoplog_page_tot * ($photoplog_page_num - 1);
		}
		$photoplog_album_fileids = array_slice($photoplog_album_fileids, $photoplog_page_start, $photoplog_page_tot);

		$photoplog_userids = array();
		$photoplog_filenames = array();
		$photoplog_titles = array();

		$photoplog_file_infos = $db->query_read_slave("SELECT fileid,userid,filename,title
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid IN (".implode(',', $photoplog_album_fileids).")
			$photoplog_catid_sql1
			$photoplog_admin_sql1
		");
		while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
		{
			$photoplog['fileid'] = intval($photoplog_file_info['fileid']);
			$photoplog_userids[$photoplog['fileid']] = $photoplog_file_info['userid'];
			$photoplog_filenames[$photoplog['fileid']] = $photoplog_file_info['filename'];
			$photoplog_titles[$photoplog['fileid']] = htmlspecialchars_uni($photoplog_file_info['title']);
		}
		$db->free_result($photoplog_file_infos);

		$photoplog_cnt_bits = 0;
		foreach ($photoplog_album_fileids AS $photoplog['fileid'])
		{
			$photoplog_cnt_bits ++;

			$photoplog['userid'] = $photoplog_userids[$photoplog['fileid']];
			$photoplog['filename'] = $photoplog_filenames[$photoplog['fileid']];
			$photoplog['title'] = $photoplog_titles[$photoplog['fileid']];

			$photoplog['hscnt'] = intval($photoplog['hscnt']) + 1;

			photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);

			($hook = vBulletinHook::fetch_hook('photoplog_albums_viewbit')) ? eval($hook) : false;

			eval('$photoplog[\'album_bits\'] .= "' . fetch_template('photoplog_album_view_bit') . '";');

			if ($photoplog_cnt_bits % 4 == 0)
			{
				$photoplog['album_bits'] .= '</tr><tr>';
			}
		}

		$photoplog['album_bits'] = eregi_replace(preg_quote("</tr><tr>")."$","",$photoplog['album_bits']);

		while ($photoplog_cnt_bits % 4 != 0)
		{
			$photoplog['album_bits'] .= "<td class=\"alt1\" align=\"left\" valign=\"bottom\" width=\"25%\">&nbsp;</td>";
			$photoplog_cnt_bits ++;
		}

		sanitize_pageresults($photoplog_file_tot, $photoplog_page_num, $photoplog_per_page, $photoplog_page_tot, $photoplog_page_tot);
		$photoplog_link = 'do=show&u='.$photoplog_user_id.'&aid='.intval($photoplog['album_albumid']);
		$photoplog_sort_url = $photoplog['location'].'/albums.php?'.$vbulletin->session->vars['sessionurl'].$photoplog_link;
		$photoplog['album_pagenav'] = construct_page_nav($photoplog_page_num, $photoplog_per_page, $photoplog_file_tot, $photoplog_sort_url);

		($hook = vBulletinHook::fetch_hook('photoplog_albums_viewlist')) ? eval($hook) : false;

		eval('$photoplog[\'memberalbums\'] .= "' . fetch_template('photoplog_album_view_list') . '";');

		$photoplog_base_href = "<base href=\"".$vbulletin->options['bburl']."/".$vbulletin->options['forumhome'].".php\" />\n";
		$headinclude = $photoplog_base_href.$headinclude;

		$photoplog_highslide_version = ($vbulletin->options['photoplog_highslide_version']) ? '<div id="hsjsscreen">&nbsp;</div>' : '';

		$photoplog_albumview_list = $stylevar['htmldoctype'].'
			<html dir="'.$stylevar['textdirection'].'" lang="'.$stylevar['languagecode'].'">
			<head>
			'.$headinclude.'
			<title>'.$vbphrase['photoplog_album_view'].'</title>
			</head>
			<body style="margin: 0px;" onload="self.focus();">
				' . $photoplog_highslide_version . '
				<div id="hscontrolbar" class="highslide-overlay hscontrolbar">
					<a href="#" class="previous" onclick="return hs.previous(this)" title="' . $vbphrase['photoplog_highslide_previous_left_arrow_key'] . '"></a>
					<a href="#" class="next" onclick="return hs.next(this)" title="' . $vbphrase['photoplog_highslide_next_right_arrow_key'] . '"></a>
					<a href="#" class="highslide-move" onclick="return false" title="' . $vbphrase['photoplog_highslide_click_and_drag_to_move'] . '"></a>
					<a href="#" class="close" onclick="return hs.close(this)" title="' . $vbphrase['photoplog_highslide_close'] . '"></a>
				</div>
				' . $photoplog['memberalbums'] . '
			</body>
			</html>';

		($hook = vBulletinHook::fetch_hook('photoplog_selector_list')) ? eval($hook) : false;

		echo $photoplog_albumview_list;
	}
	else
	{
		echo $vbphrase['photoplog_not_available'];
	}
	exit();
}

if ($_REQUEST['do'] == 'view')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'aid' => TYPE_UINT,
		'page' => TYPE_UINT,
		'pp' => TYPE_UINT
	));

	$photoplog['album_albumid'] = $vbulletin->GPC['aid'];
	$photoplog_page_num = $vbulletin->GPC['page'];
	$photoplog_per_page = $vbulletin->GPC['pp'];

	$photoplog_cnt = 0;
	$photoplog['album_bits'] = '';
	$photoplog['album_title'] = '';
	$photoplog['album_description'] = '';
	$photoplog['album_privacy'] = '';
	$photoplog['album_count'] = 0;
	$photoplog['album_pagenav'] = '';

	$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], 0, 1).'link';
	$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], -1, 1).'link';

	$photoplog['do_highslide'] = 0;
	if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
	{
		$photoplog['do_highslide'] = 1;
	}

	if ($photoplog['album_albumid'])
	{
		$photoplog_album_info = $db->query_first_slave("SELECT fileids,title
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE albumid = ".intval($photoplog['album_albumid'])."
			AND userid = ".intval($vbulletin->userinfo['userid'])."
		");
		if (!$photoplog_album_info)
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_mod_queue']);
		}

		$photoplog_album_fileids = unserialize($photoplog_album_info['fileids']);
		$photoplog['album_title'] = htmlspecialchars_uni($photoplog_album_info['title']);

		$db->free_result($photoplog_album_info);

		if (!is_array($photoplog_album_fileids))
		{
			$photoplog_album_fileids = array();
		}

		$photoplog_file_tot = count($photoplog_album_fileids);
		if (!$photoplog_file_tot)
		{
			$photoplog['album_bits'] = '<td colspan="4" class="alt2">'.$vbphrase['photoplog_not_available'].'</td>';

			($hook = vBulletinHook::fetch_hook('photoplog_albums_viewlist')) ? eval($hook) : false;

			photoplog_output_page('photoplog_album_view_list', $vbphrase['photoplog_album_view']);
		}

		$photoplog_page_tot = 12;
		if (!$photoplog_page_num || $photoplog_page_num == 1)
		{
			$photoplog_page_start = 0;
		}
		else
		{
			$photoplog_page_start = $photoplog_page_tot * ($photoplog_page_num - 1);
		}
		$photoplog_album_fileids = array_slice($photoplog_album_fileids, $photoplog_page_start, $photoplog_page_tot);

		$photoplog_userids = array();
		$photoplog_filenames = array();
		$photoplog_titles = array();

		$photoplog_file_infos = $db->query_read_slave("SELECT fileid,userid,filename,title
				FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				WHERE fileid IN (".implode(',', $photoplog_album_fileids).")
		");
		while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
		{
			$photoplog['fileid'] = intval($photoplog_file_info['fileid']);
			$photoplog_userids[$photoplog['fileid']] = $photoplog_file_info['userid'];
			$photoplog_filenames[$photoplog['fileid']] = $photoplog_file_info['filename'];
			$photoplog_titles[$photoplog['fileid']] = htmlspecialchars_uni($photoplog_file_info['title']);
		}
		$db->free_result($photoplog_file_infos);

		$photoplog_cnt_bits = 0;
		foreach ($photoplog_album_fileids AS $photoplog['fileid'])
		{
			$photoplog_cnt_bits ++;

			$photoplog['userid'] = $photoplog_userids[$photoplog['fileid']];
			$photoplog['filename'] = $photoplog_filenames[$photoplog['fileid']];
			$photoplog['title'] = $photoplog_titles[$photoplog['fileid']];

			$photoplog['hscnt'] = intval($photoplog['hscnt']) + 1;

			photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);

			($hook = vBulletinHook::fetch_hook('photoplog_albums_viewbit')) ? eval($hook) : false;

			eval('$photoplog[\'album_bits\'] .= "' . fetch_template('photoplog_album_view_bit') . '";');

			if ($photoplog_cnt_bits % 4 == 0)
			{
				$photoplog['album_bits'] .= '</tr><tr>';
			}
		}

		$photoplog['album_bits'] = eregi_replace(preg_quote("</tr><tr>")."$","",$photoplog['album_bits']);

		while ($photoplog_cnt_bits % 4 != 0)
		{
			$photoplog['album_bits'] .= "<td class=\"alt1\" align=\"left\" valign=\"bottom\" width=\"25%\">&nbsp;</td>";
			$photoplog_cnt_bits ++;
		}

		sanitize_pageresults($photoplog_file_tot, $photoplog_page_num, $photoplog_per_page, $photoplog_page_tot, $photoplog_page_tot);
		$photoplog_link = 'do=view&aid='.intval($photoplog['album_albumid']);
		$photoplog_sort_url = $photoplog['location'].'/albums.php?'.$vbulletin->session->vars['sessionurl'].$photoplog_link;
		$photoplog['album_pagenav'] = construct_page_nav($photoplog_page_num, $photoplog_per_page, $photoplog_file_tot, $photoplog_sort_url);

		($hook = vBulletinHook::fetch_hook('photoplog_albums_viewlist')) ? eval($hook) : false;

		photoplog_output_page('photoplog_album_view_list', $vbphrase['photoplog_album_view']);
	}
	else
	{
		$photoplog_album_info = $db->query_first_slave("SELECT COUNT(*) AS cnt1
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE userid = ".intval($vbulletin->userinfo['userid'])."
		");
		$photoplog_album_tot = intval($photoplog_album_info['cnt1']);
		$db->free_result($photoplog_album_info);

		$photoplog_page_tot = 10;
		sanitize_pageresults($photoplog_album_tot, $photoplog_page_num, $photoplog_per_page, $photoplog_page_tot, $photoplog_page_tot);

		$photoplog_limit_lower = ($photoplog_page_num - 1) * $photoplog_per_page;
		if ($photoplog_limit_lower < 0)
		{
			$photoplog_limit_lower = 0;
		}

		$photoplog_limit_lower = intval($photoplog_limit_lower);
		$photoplog_per_page = intval($photoplog_per_page);

		$photoplog_link = 'do=view';
		$photoplog_sort_url = $photoplog['location'].'/albums.php?'.$vbulletin->session->vars['sessionurl'].$photoplog_link;
		$photoplog['album_pagenav'] = construct_page_nav($photoplog_page_num, $photoplog_per_page, $photoplog_album_tot, $photoplog_sort_url);

		$photoplog_album_infos = $db->query_read_slave("SELECT albumid,title,description,fileids,visible
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE userid = ".intval($vbulletin->userinfo['userid'])."
			ORDER BY dateline DESC
			LIMIT $photoplog_limit_lower,$photoplog_per_page
		");
		while ($photoplog_album_info = $db->fetch_array($photoplog_album_infos))
		{
			$photoplog_cnt++;

			$photoplog['album_albumid'] = intval($photoplog_album_info['albumid']);
			$photoplog['album_title'] = htmlspecialchars_uni($photoplog_album_info['title']);
			$photoplog['album_description'] = nl2br(htmlspecialchars_uni($photoplog_album_info['description']));
			$photoplog['album_count'] = vb_number_format(intval(count(unserialize($photoplog_album_info['fileids']))));
			$photoplog['album_privacy'] = (intval($photoplog_album_info['visible']) == 1) ? $vbphrase['photoplog_public'] : $vbphrase['photoplog_private'];

			($hook = vBulletinHook::fetch_hook('photoplog_albums_bit')) ? eval($hook) : false;

			eval('$photoplog[\'album_bits\'] .= "' . fetch_template('photoplog_album_bit') . '";');
		}
		$db->free_result($photoplog_album_infos);

		if (!$photoplog_cnt)
		{
			$photoplog['album_bits'] = '<tr><td class="alt2" colspan="4">'.$vbphrase['photoplog_not_available'].'</td></tr>';

			($hook = vBulletinHook::fetch_hook('photoplog_albums_list')) ? eval($hook) : false;

			photoplog_output_page('photoplog_album_list', $vbphrase['photoplog_album_list']);
		}

		($hook = vBulletinHook::fetch_hook('photoplog_albums_list')) ? eval($hook) : false;

		photoplog_output_page('photoplog_album_list', $vbphrase['photoplog_album_list']);
	}
}

if ($_REQUEST['do'] == 'add' || $_REQUEST['do'] == 'edit')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'aid' => TYPE_UINT
	));

	$photoplog['album_albumid'] = $vbulletin->GPC['aid'];
	$photoplog['album_title'] = '';
	$photoplog['album_description'] = '';
	$photoplog['album_checked0'] = '';
	$photoplog['album_checked1'] = 'checked="checked"';

	if ($_REQUEST['do'] == 'edit' && $photoplog['album_albumid'])
	{
		$photoplog_album_info = $db->query_first_slave("SELECT albumid,title,description,visible
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE albumid = ".intval($photoplog['album_albumid'])."
			AND userid = ".intval($vbulletin->userinfo['userid'])."
		");

		if ($photoplog_album_info)
		{
			$photoplog['album_albumid'] = intval($photoplog_album_info['albumid']);
			$photoplog['album_title'] = htmlspecialchars_uni($photoplog_album_info['title']);
			$photoplog['album_description'] = htmlspecialchars_uni($photoplog_album_info['description']);
			$photoplog['album_checked0'] = (intval($photoplog_album_info['visible']) == 0) ? 'checked="checked"' : '';
			$photoplog['album_checked1'] = (intval($photoplog_album_info['visible']) == 1) ? 'checked="checked"' : '';
		}

		$db->free_result($photoplog_album_info);
	}

	require_once(DIR . '/includes/functions_editor.php');

	$photoplog['textareacols'] = fetch_textarea_width();

	($hook = vBulletinHook::fetch_hook('photoplog_albums_form')) ? eval($hook) : false;

	photoplog_output_page('photoplog_album_form', $vbphrase['photoplog_manage_album']);
}

if ($_REQUEST['do'] == 'doalbum')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'albumid' => TYPE_UINT,
		'title' => TYPE_STR,
		'description' => TYPE_STR,
		'privacy' => TYPE_UINT
	));

	($hook = vBulletinHook::fetch_hook('photoplog_albums_doalbum_start')) ? eval($hook) : false;

	$photoplog['album_albumid'] = $vbulletin->GPC['albumid'];
	$photoplog['album_title'] = $vbulletin->GPC['title'];
	$photoplog['album_description'] = $vbulletin->GPC['description'];
	$photoplog_album_userid = $vbulletin->userinfo['userid'];
	$photoplog_album_username = $vbulletin->userinfo['username'];
	$photoplog_album_fileids = serialize(array());
	$photoplog_album_visible = $vbulletin->GPC['privacy'];

	if (vbstrlen($photoplog['album_title']) == 0)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_title_too_short']);
	}

	if ($photoplog['album_albumid'])
	{
		$photoplog_album_info = $db->query_first_slave("SELECT albumid,fileids,dateline
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE albumid = ".intval($photoplog['album_albumid'])."
			AND userid = ".intval($vbulletin->userinfo['userid'])."
		");

		if ($photoplog_album_info)
		{
			$photoplog['album_albumid'] = $photoplog_album_info['albumid'];
			$photoplog_album_fileids = $photoplog_album_info['fileids'];
			$photoplog_album_dateline = $photoplog_album_info['dateline'];

			$db->free_result($photoplog_album_info);

			$db->query_write("REPLACE INTO " . PHOTOPLOG_PREFIX . "photoplog_useralbums
				(albumid, userid, username, title, description, fileids, dateline, visible)
				VALUES
				(
					".intval($photoplog['album_albumid']).",
					".intval($photoplog_album_userid).",
					'".$db->escape_string($photoplog_album_username)."',
					'".$db->escape_string($photoplog['album_title'])."',
					'".$db->escape_string($photoplog['album_description'])."',
					'".$db->escape_string($photoplog_album_fileids)."',
					".intval($photoplog_album_dateline).",
					".intval($photoplog_album_visible)."
				)
			");

			($hook = vBulletinHook::fetch_hook('photoplog_albums_doalbum_complete')) ? eval($hook) : false;

			$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
			exec_header_redirect($photoplog_url);
			exit();
		}
		else
		{
			$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
			exec_header_redirect($photoplog_url);
			exit();
		}
	}
	else
	{
		$db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			(userid, username, title, description, fileids, dateline, visible)
			VALUES
			(
				".intval($photoplog_album_userid).",
				'".$db->escape_string($photoplog_album_username)."',
				'".$db->escape_string($photoplog['album_title'])."',
				'".$db->escape_string($photoplog['album_description'])."',
				'".$db->escape_string($photoplog_album_fileids)."',
				".intval(TIMENOW).",
				".intval($photoplog_album_visible)."
			)
		");

		($hook = vBulletinHook::fetch_hook('photoplog_albums_doalbum_complete')) ? eval($hook) : false;

		$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
		exec_header_redirect($photoplog_url);
		exit();
	}
}

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'aid' => TYPE_UINT
	));

	$photoplog['album_albumid'] = $vbulletin->GPC['aid'];

	if ($photoplog['album_albumid'])
	{
		$photoplog_album_info = $db->query_first_slave("SELECT albumid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE albumid = ".intval($photoplog['album_albumid'])."
			AND userid = ".intval($vbulletin->userinfo['userid'])."
		");

		if ($photoplog_album_info)
		{
			$photoplog['album_albumid'] = intval($photoplog_album_info['albumid']);

			$db->free_result($photoplog_album_info);

			($hook = vBulletinHook::fetch_hook('photoplog_albums_expungeform')) ? eval($hook) : false;

			photoplog_output_page('photoplog_expunge_form', $vbphrase['photoplog_manage_album']);
		}
		else
		{
			$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
			exec_header_redirect($photoplog_url);
			exit();
		}
	}
	else
	{
		$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
		exec_header_redirect($photoplog_url);
		exit();
	}
}

if ($_REQUEST['do'] == 'dodelete')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'albumid' => TYPE_UINT
	));

	($hook = vBulletinHook::fetch_hook('photoplog_albums_dodelete_start')) ? eval($hook) : false;

	$photoplog['album_albumid'] = $vbulletin->GPC['albumid'];

	if ($photoplog['album_albumid'])
	{
		$photoplog_album_info = $db->query_first_slave("SELECT albumid,fileids
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE albumid = ".intval($photoplog['album_albumid'])."
			AND userid = ".intval($vbulletin->userinfo['userid'])."
		");

		if ($photoplog_album_info)
		{
			$photoplog['album_albumid'] = intval($photoplog_album_info['albumid']);
			$photoplog_album_fileids = unserialize($photoplog_album_info['fileids']);

			$db->free_result($photoplog_album_info);

			if (is_array($photoplog_album_fileids) && count($photoplog_album_fileids) > 0)
			{
				$photoplog_file_infos = $db->query_read_slave("SELECT fileid, albumids
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE fileid IN (".implode(',', $photoplog_album_fileids).")
				");
				unset($photoplog_album_fileids);

				$photoplog_file_cnt = 0;
				$photoplog_file_case1 = '';
				$photoplog_file_case2 = array();

				while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
				{
					$photoplog_file_cnt ++;

					$photoplog_file_fileid = intval($photoplog_file_info['fileid']);
					$photoplog_file_albumids = unserialize($photoplog_file_info['albumids']);

					if (is_array($photoplog_file_albumids) && count($photoplog_file_albumids) > 0)
					{
						if (in_array($photoplog['album_albumid'], $photoplog_file_albumids))
						{
							$photoplog_album_albumid_key = array_search($photoplog['album_albumid'], $photoplog_file_albumids);
							unset($photoplog_file_albumids[$photoplog_album_albumid_key]);

							$photoplog_file_case1 .= "WHEN ".intval($photoplog_file_fileid)." THEN '".$db->escape_string(serialize($photoplog_file_albumids))."' ";
							$photoplog_file_case2[] = intval($photoplog_file_fileid);
						}
						unset($photoplog_file_albumids);
					}

					if (($photoplog_file_cnt % 20 == 0) && $photoplog_file_case1)
					{
						$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
							SET albumids = CASE fileid ".$photoplog_file_case1." ELSE albumids END
							WHERE fileid IN (".implode(',', $photoplog_file_case2).")
						");

						$photoplog_file_cnt = 0;
						$photoplog_file_case1 = '';
						$photoplog_file_case2 = array();
					}
				}

				if ($photoplog_file_case1)
				{
					$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
						SET albumids = CASE fileid ".$photoplog_file_case1." ELSE albumids END
						WHERE fileid IN (".implode(',', $photoplog_file_case2).")
					");
				}

				$db->free_result($photoplog_file_infos);
				unset($photoplog_file_case1, $photoplog_file_case2);
			}

			$db->query("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
				WHERE albumid = ".intval($photoplog['album_albumid'])."
				AND userid = ".intval($vbulletin->userinfo['userid'])."
			");

			($hook = vBulletinHook::fetch_hook('photoplog_albums_dodelete_complete')) ? eval($hook) : false;

			$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
			exec_header_redirect($photoplog_url);
			exit();
		}
		else
		{
			$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
			exec_header_redirect($photoplog_url);
			exit();
		}
	}
	else
	{
		$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
		exec_header_redirect($photoplog_url);
		exit();
	}
}

if ($_REQUEST['do'] == 'insert')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'n' => TYPE_UINT
	));

	$photoplog['fileid'] = intval($vbulletin->GPC['n']);

	$photoplog_file_info = $db->query_first_slave("SELECT albumids
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid = ".intval($photoplog['fileid'])."
		$photoplog_catid_sql1
		$photoplog_admin_sql1
	");
	if (!$photoplog_file_info)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_mod_queue']);
	}

	$photoplog_file_albumids_arr = unserialize($photoplog_file_info['albumids']);
	$db->free_result($photoplog_file_info);

	$photoplog_album_infos = $db->query_read_slave("SELECT albumid, title
		FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
		WHERE userid = ".intval($vbulletin->userinfo['userid'])."
		ORDER BY title ASC
	");

	if ($db->num_rows($photoplog_album_infos))
	{
		$photoplog_list_albums_row = array();
		while ($photoplog_album_info = $db->fetch_array($photoplog_album_infos))
		{
			$photoplog_albumid = intval($photoplog_album_info['albumid']);
			$photoplog_list_albums_row[$photoplog_albumid] = $photoplog_album_info['title'];
		}
		$db->free_result($photoplog_album_infos);

		$photoplog['select_row'] = "<select name=\"albumid\" id=\"sel_albumid\" tabindex=\"1\">\n";
		$photoplog['select_row'] .= photoplog_select_options($photoplog_list_albums_row, '');
		$photoplog['select_row'] .= "</select>\n";

		($hook = vBulletinHook::fetch_hook('photoplog_albums_select')) ? eval($hook) : false;

		photoplog_output_page('photoplog_album_select', $vbphrase['photoplog_album_list']);
	}
	else
	{
		$photoplog_album_userid = $vbulletin->userinfo['userid'];
		$photoplog_album_username = $vbulletin->userinfo['username'];
		$photoplog['album_title'] = $vbphrase['photoplog_title'];
		$photoplog['album_description'] = $vbphrase['photoplog_description'];
		$photoplog_album_fileids = serialize(array(intval($photoplog['fileid'])));

		$db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			(userid, username, title, description, fileids, dateline, visible)
			VALUES
			(
				".intval($photoplog_album_userid).",
				'".$db->escape_string($photoplog_album_username)."',
				'".$db->escape_string($photoplog['album_title'])."',
				'".$db->escape_string($photoplog['album_description'])."',
				'".$db->escape_string($photoplog_album_fileids)."',
				".intval(TIMENOW).",
				1
			)
		");

		$photoplog_albumid = intval($db->insert_id());

		if (!is_array($photoplog_file_albumids_arr))
		{
			$photoplog_file_albumids_arr = array();
		}
		if (!in_array($photoplog_albumid, $photoplog_file_albumids_arr))
		{
			$photoplog_file_albumids_arr[] = intval($photoplog_albumid);
		}
		$photoplog_file_albumids = serialize($photoplog_file_albumids_arr);

		$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			SET albumids = '".$db->escape_string($photoplog_file_albumids)."'
			WHERE fileid = ".intval($photoplog['fileid'])."
		");

		($hook = vBulletinHook::fetch_hook('photoplog_albums_autocreate')) ? eval($hook) : false;

		$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
		exec_header_redirect($photoplog_url);
		exit();
	}
}

if ($_REQUEST['do'] == 'doinsert')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'fileid' => TYPE_UINT,
		'albumid' => TYPE_UINT
	));

	($hook = vBulletinHook::fetch_hook('photoplog_albums_doinsert_start')) ? eval($hook) : false;

	$photoplog['fileid'] = intval($vbulletin->GPC['fileid']);
	$photoplog_albumid = intval($vbulletin->GPC['albumid']);

	$photoplog_file_info = $db->query_first_slave("SELECT albumids
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid = ".intval($photoplog['fileid'])."
		$photoplog_catid_sql1
		$photoplog_admin_sql1
	");
	if (!$photoplog_file_info)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_mod_queue']);
	}

	$photoplog_file_albumids_arr = unserialize($photoplog_file_info['albumids']);
	$db->free_result($photoplog_file_info);

	$photoplog_album_info = $db->query_first_slave("SELECT fileids
		FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
		WHERE albumid = ".intval($photoplog_albumid)."
		AND userid = ".intval($vbulletin->userinfo['userid'])."
	");
	if (!$photoplog_album_info)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_mod_queue']);
	}

	$photoplog_album_fileids_arr = unserialize($photoplog_album_info['fileids']);
	$db->free_result($photoplog_album_info);

	if (!is_array($photoplog_file_albumids_arr))
	{
		$photoplog_file_albumids_arr = array();
	}
	if (!in_array($photoplog_albumid, $photoplog_file_albumids_arr))
	{
		$photoplog_file_albumids_arr[] = intval($photoplog_albumid);
	}
	$photoplog_file_albumids = serialize($photoplog_file_albumids_arr);

	$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		SET albumids = '".$db->escape_string($photoplog_file_albumids)."'
		WHERE fileid = ".intval($photoplog['fileid'])."
	");

	if (!is_array($photoplog_album_fileids_arr))
	{
		$photoplog_album_fileids_arr = array();
	}
	if (!in_array($photoplog['fileid'], $photoplog_album_fileids_arr))
	{
		$photoplog_album_fileids_arr[] = intval($photoplog['fileid']);
	}
	$photoplog_album_fileids = serialize($photoplog_album_fileids_arr);

	$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_useralbums
		SET fileids = '".$db->escape_string($photoplog_album_fileids)."'
		WHERE albumid = ".intval($photoplog_albumid)."
		AND userid = ".intval($vbulletin->userinfo['userid'])."
	");

	($hook = vBulletinHook::fetch_hook('photoplog_albums_doinsert_complete')) ? eval($hook) : false;

	$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
	exec_header_redirect($photoplog_url);
	exit();
}

if ($_REQUEST['do'] == 'remove')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'aid' => TYPE_UINT,
		'fid' => TYPE_UINT
	));

	($hook = vBulletinHook::fetch_hook('photoplog_albums_remove_start')) ? eval($hook) : false;

	$photoplog_albumid = $vbulletin->GPC['aid'];
	$photoplog['fileid'] = $vbulletin->GPC['fid'];

	$photoplog_album_info = $db->query_first_slave("SELECT fileids
		FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
		WHERE albumid = ".intval($photoplog_albumid)."
		AND userid = ".intval($vbulletin->userinfo['userid'])."
	");

	if ($photoplog_album_info)
	{
		$photoplog_album_fileids_arr = unserialize($photoplog_album_info['fileids']);
		$db->free_result($photoplog_album_info);

		if (!is_array($photoplog_album_fileids_arr))
		{
			$photoplog_album_fileids_arr = array();
		}

		if (in_array($photoplog['fileid'], $photoplog_album_fileids_arr))
		{
			$photoplog_album_fileids_arr_key = array_search($photoplog['fileid'], $photoplog_album_fileids_arr);
			unset($photoplog_album_fileids_arr[$photoplog_album_fileids_arr_key]);
		}
		$photoplog_album_fileids = serialize($photoplog_album_fileids_arr);

		$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			SET fileids = '".$db->escape_string($photoplog_album_fileids)."'
			WHERE albumid = ".intval($photoplog_albumid)."
			AND userid = ".intval($vbulletin->userinfo['userid'])."
		");

		$photoplog_file_info = $db->query_first_slave("SELECT albumids
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid = ".intval($photoplog['fileid'])."
		");

		if ($photoplog_file_info)
		{
			$photoplog_file_albumids_arr = unserialize($photoplog_file_info['albumids']);
			$db->free_result($photoplog_file_info);

			if (!is_array($photoplog_file_albumids_arr))
			{
				$photoplog_file_albumids_arr = array();
			}

			if (in_array($photoplog_albumid, $photoplog_file_albumids_arr))
			{
				$photoplog_file_albumids_arr_key = array_search($photoplog_albumid, $photoplog_file_albumids_arr);
				unset($photoplog_file_albumids_arr[$photoplog_file_albumids_arr_key]);
			}
			$photoplog_file_albumids = serialize($photoplog_file_albumids_arr);

			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				SET albumids = '".$db->escape_string($photoplog_file_albumids)."'
				WHERE fileid = ".intval($photoplog['fileid'])."
			");
		}
	}

	($hook = vBulletinHook::fetch_hook('photoplog_albums_remove_complete')) ? eval($hook) : false;

	$photoplog_url = $photoplog['location'].'/albums.php'.$vbulletin->session->vars['sessionurl_q'];
	exec_header_redirect($photoplog_url);
	exit();
}

($hook = vBulletinHook::fetch_hook('photoplog_albums_complete')) ? eval($hook) : false;

if (!in_array($_REQUEST['do'], array('view','add','edit','doalbum','delete','dodelete','insert','remove','doinsert')))
{
	photoplog_index_bounce();
}

?>