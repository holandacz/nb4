<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','index');
define('PHOTOPLOG_LEVEL','view');
define('GET_EDIT_TEMPLATES', true);
define('PHOTOPLOG_RANDOM','bits');
require_once('./settings.php');

// ########################### Start Index Page ###########################
($hook = vBulletinHook::fetch_hook('photoplog_index_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'view';
}

if ($_REQUEST['do'] == 'view')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'c' => TYPE_UINT,
		'n' => TYPE_UINT,
		'u' => TYPE_UINT,
		'page' => TYPE_UINT,
		'pp' => TYPE_UINT,
		'q' => TYPE_NOHTML,
		'v' => TYPE_UINT
	));

	$photoplog['cat_id'] = $vbulletin->GPC['c'];
	$photoplog['cat_id2'] = $photoplog['cat_id'];
	$photoplog_file_id = $vbulletin->GPC['n'];
	$photoplog['user_id'] = $vbulletin->GPC['u'];
	$photoplog['user_id2'] = $photoplog['user_id'];
	$photoplog_page_num = $vbulletin->GPC['page'];
	$photoplog_per_page = $vbulletin->GPC['pp'];
	$photoplog['letter_id'] = $vbulletin->GPC['q'];
	$photoplog['letter_id2'] = $photoplog['letter_id'];
	$photoplog_order_id = $vbulletin->GPC['v'];

	if ($photoplog_file_id)
	{
		$photoplog['letter_id'] = $photoplog['cat_id'] = $photoplog['user_id'] = $photoplog_order_id = '';
	}
	if ($photoplog_order_id)
	{
		$photoplog['cat_id'] = $photoplog['user_id'] = $photoplog['letter_id'] = '';
	}
	if ($photoplog['cat_id'])
	{
		$photoplog['letter_id'] = $photoplog['user_id'] = $photoplog_order_id = '';
	}
	if ($photoplog['letter_id'])
	{
		$photoplog['cat_id'] = $photoplog['user_id'] = $photoplog_order_id = '';
	}
	if ($photoplog['user_id'])
	{
		$photoplog['cat_id'] = $photoplog['letter_id'] = $photoplog_order_id = '';
	}

	$photoplog_file_info = '';
	if (!$photoplog_file_info_links && $photoplog_file_id)
	{
		$photoplog_file_info = $db->query_first("SELECT userid, fileid, filename,
			catid, title, description, fielddata, moderate, username, dimensions,
			filesize, dateline, views, exifinfo, setid,
			$photoplog_admin_sql4
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid = ".intval($photoplog_file_id)."
			$photoplog_catid_sql1
			$photoplog_admin_sql1
		");
	}
	else if ($photoplog_file_info_links && $photoplog_file_id)
	{
		$photoplog_file_info = $photoplog_file_info_links;
	}
	unset($photoplog_file_info_links);

	$photoplog['catid'] = intval($photoplog_file_info['catid']);

	$photoplog_do_html = 0;
	$photoplog_do_smilies = 0;
	$photoplog_do_bbcode = 0;
	$photoplog['do_comments'] = 0;
	$photoplog_do_imgcode = 0;

	if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
	{
		$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
		$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

		$photoplog_do_html = ($photoplog_catoptions['allowhtml']) ? 1 : 0;
		$photoplog_do_smilies = ($photoplog_catoptions['allowsmilies']) ? 1 : 0;
		$photoplog_do_bbcode = ($photoplog_catoptions['allowbbcode']) ? 1 : 0;
		$photoplog['do_comments'] = ($photoplog_catoptions['allowcomments']) ? 1 : 0;

		// this is to show the little image toolbar icon
		$photoplog_do_imgcode = ($photoplog_catoptions['allowimgcode']) ? 1 : 0;
		$vbulletin->options['allowbbimagecode'] = $photoplog_do_imgcode;
	}

	$photoplog_parent_list = array();
	$photoplog_navbits = array();

	if ($photoplog_file_info || $photoplog['cat_id'])
	{
		$photoplog_catid_temp = ($photoplog['cat_id']) ? $photoplog['cat_id'] : $photoplog['catid'];

		foreach ($photoplog_list_relatives AS $photoplog_list_relatives_key => $photoplog_list_relatives_array)
		{
			if ($photoplog_list_relatives_key != '-1' && in_array($photoplog_catid_temp,$photoplog_list_relatives_array))
			{
				$photoplog_list_relatives_cnt = count($photoplog_list_relatives_array);
				$photoplog_parent_list[$photoplog_list_relatives_cnt] = $photoplog_list_relatives_key;
			}
		}

		krsort($photoplog_parent_list);


		$photoplog_navbits[$photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q']] = htmlspecialchars_uni($vbphrase['photoplog_photoplog']);

		foreach ($photoplog_parent_list AS $photoplog_parent_catid)
		{
			if (!in_array($photoplog_parent_catid,array_keys($photoplog_ds_catopts)))
			{
				$photoplog_parent_title = $vbphrase['photoplog_not_available'];
			}
			else
			{
				$photoplog_parent_title = $photoplog_ds_catopts[$photoplog_parent_catid]['title'];
			}

			$photoplog_navbits_link = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'c='.$photoplog_parent_catid;

			$photoplog_navbits[$photoplog_navbits_link] = htmlspecialchars_uni($photoplog_parent_title);
		}

		if (!in_array($photoplog_catid_temp,array_keys($photoplog_ds_catopts)))
		{
			$photoplog_child_title = $vbphrase['photoplog_not_available'];
		}
		else
		{
			$photoplog_child_title = $photoplog_ds_catopts[$photoplog_catid_temp]['title'];
		}

		if ($photoplog_file_info)
		{
			$photoplog_navbits_link = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'c='.$photoplog_file_info['catid'];
			$photoplog_navbits[$photoplog_navbits_link] = htmlspecialchars_uni($photoplog_child_title);
			$photoplog_navbits[''] = htmlspecialchars_uni($photoplog_file_info['title']);
		}
		else
		{
			$photoplog_navbits[''] = htmlspecialchars_uni($photoplog_child_title);
		}
	}

	if ($photoplog_file_info)
	{
		$photoplog_addon = 0;
		if (!$photoplog_page_num)
		{
			$photoplog_addon = 1;
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
						SET views = views + 1
						WHERE fileid = ".intval($photoplog_file_id)."
			");
			$photoplog_moderate = $photoplog_file_info['moderate'];
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
						SET num_views = num_views + 1
						WHERE catid = ".intval($photoplog['catid'])."
						AND moderate >= ".intval($photoplog_moderate)."
			");
		}

		$photoplog['fileid'] = $photoplog_file_info['fileid'];
		$photoplog['userid'] = $photoplog_file_info['userid'];
		$photoplog['username'] = $photoplog_file_info['username'];
		$photoplog['title'] = $photoplog_file_info['title'];
		$photoplog['description'] = $photoplog_file_info['description'];
		$photoplog['filename'] = $photoplog_file_info['filename'];
		$photoplog['dimensions'] = $photoplog_file_info['dimensions'];
		$photoplog['filesize'] = vb_number_format($photoplog_file_info['filesize'],1,true);
		$photoplog['date'] = vbdate($vbulletin->options['dateformat'],$photoplog_file_info['dateline'],true);
		$photoplog['time'] = vbdate($vbulletin->options['timeformat'],$photoplog_file_info['dateline']);
		$photoplog['views'] = $photoplog_file_info['views'] + $photoplog_addon;
		$photoplog['default_size'] = $vbulletin->options['photoplog_default_size'];
		$photoplog_fielddata = $photoplog_file_info['fielddata'];
		$photoplog_fielddata = ($photoplog_fielddata == '') ? array() : unserialize($photoplog_fielddata);

		$photoplog['exifinfo'] = '';
		if ($vbulletin->options['photoplog_exifinfo_active'])
		{
			$photoplog_exifinfo = unserialize($photoplog_file_info['exifinfo']);
			if (is_array($photoplog_exifinfo) && count($photoplog_exifinfo))
			{
				$photoplog_exifinfo_cnt = 0;

				$photoplog['exifinfo'] .= '
					<table class="tborder" style="margin-bottom: 10px;" border="0" cellpadding="'.$stylevar['cellpadding'].'" cellspacing="'.$stylevar['cellspacing'].'" align="center" width="100%">
					<tr>
				';

				foreach ($photoplog_exifinfo AS $photoplog_exifinfo_key => $photoplog_exifinfo_val)
				{
					$photoplog_exifinfo_cnt ++;

					$photoplog_exifinfo_key = htmlspecialchars_uni($photoplog_exifinfo_key);
					$photoplog_exifinfo_val = htmlspecialchars_uni($photoplog_exifinfo_val);

					$photoplog['exifinfo'] .= '
						<td width="25%" class="alt2" style="font-size: 11px;">'.$photoplog_exifinfo_key.'</td>
						<td width="25%" class="alt2" style="font-size: 11px;">'.$photoplog_exifinfo_val.'</td>
					';

					if ($photoplog_exifinfo_cnt % 2 == 0)
					{
						$photoplog['exifinfo'] .= '</tr><tr>';
					}
				}

				$photoplog['exifinfo'] = eregi_replace(preg_quote("</tr><tr>")."$","",$photoplog['exifinfo']);

				while ($photoplog_exifinfo_cnt % 2 != 0)
				{
					$photoplog['exifinfo'] .= '
						<td class="alt2" style="font-size: 11px;">&nbsp;</td>
						<td class="alt2" style="font-size: 11px;">&nbsp;</td>
					';
					$photoplog_exifinfo_cnt ++;
				}

				$photoplog['exifinfo'] .= '
					</tr>
					</table>
				';
			}
			unset($photoplog_exifinfo,$photoplog_exifinfo_key,$photoplog_exifinfo_val,$photoplog_exifinfo_cnt);
		}

		$photoplog['padright'] = intval(2 * ((2 * $stylevar['cellpadding']) + $stylevar['cellspacing']));

		if (!is_array($photoplog_fielddata))
		{
			$photoplog_fielddata = array();
		}
		if (!in_array($photoplog['default_size'], array('small','medium','large')))
		{
			$photoplog['default_size'] = '';
		}

		$photoplog['set_thumbs'] = '';
		$photoplog_setid = $photoplog_file_info['setid'];
		$photoplog_useset_infos = $db->query_read_slave("SELECT userid,fileid,filename,title
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE setid = ".intval($photoplog_setid)."
			AND setid > 0
			$photoplog_catid_sql1
			$photoplog_admin_sql1
		");
		if ($db->num_rows($photoplog_useset_infos) > 1)
		{
			$photoplog['set_thumbs'] .= "<br /><hr size=\"1\" style=\"color: ".$stylevar['tborder_bgcolor'].";\" />";

			$photoplog['set_thumbs'] .= "
				<script type=\"text/javascript\">
				<!--
				function photoplog_changeimage(file_olink, file_llink)
				{
					document.getElementById('photoplog_currentimage').innerHTML =  '<a href=\"' + file_olink + '\"><img style=\"".addslashes_js($vbulletin->options['photoplog_csslarge_thumbs'])."\" src=\"' + file_llink + '\" alt=\"".addslashes_js($vbphrase['photoplog_click_to_view_original'])."\" border=\"0\" /></a>';
				}
				// -->
				</script>
			";

			$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], 0, 1).'link';
			$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], -1, 1).'link';

			$photoplog['do_highslide'] = 0;
			if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
			{
				$photoplog['do_highslide'] = 1;
			}

			while ($photoplog_useset_info = $db->fetch_array($photoplog_useset_infos))
			{
				$photoplog_set_userid = $photoplog_useset_info['userid'];
				$photoplog_set_fileid = $photoplog_useset_info['fileid'];
				$photoplog_set_filename = $photoplog_useset_info['filename'];
				$photoplog_set_title = htmlspecialchars_uni($photoplog_useset_info['title']);

				$photoplog['hscnt'] = intval($photoplog['hscnt']) + 1;

				photoplog_file_link($photoplog_set_userid, $photoplog_set_fileid, $photoplog_set_filename);

//				if ($photoplog_set_fileid != $photoplog['fileid'])
//				{
					if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
					{
						$photoplog['set_thumbs'] .= '
							<script type="text/javascript">hs.registerOverlay({thumbnailId: \'hsjs' . $photoplog['hscnt'] . '\', overlayId: \'hscontrolbar\', position: \'top right\', hideOnMouseOut: true});</script>
							<a id="hsjs' . $photoplog['hscnt'] . '" href="' . $photoplog[$photoplog_hslink2] . '" class="highslide" onclick="return hs.expand(this, {slideshowGroup: \'viewfile\'})"><img style="' . $vbulletin->options['photoplog_csssmall_thumbs'] . '" src="' . $photoplog[$photoplog_hslink1] . '" alt="' . $vbphrase['photoplog_image_in_set'] . '" border="0" /></a>
							<div class="highslide-caption" id="caption-for-hsjs' . $photoplog['hscnt'] . '"><a href="' . $photoplog['location'] . '/index.php?' . $vbulletin->session->vars['sessionurl'] . 'n=' . $photoplog_set_fileid . '">' . $photoplog_set_title . '</a>&nbsp;</div>
						';
					}
					else if ($vbulletin->options['photoplog_lightbox_orig'])
					{
						$photoplog['set_thumbs'] .= "
							<a href=\"".$photoplog['location']."/index.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_set_fileid."\"><img style=\"".$vbulletin->options['photoplog_csssmall_thumbs']."\" src=\"".$photoplog['file_slink']."\" alt=\"".$vbphrase['photoplog_image_in_set']."\" border=\"0\" /></a>
						";
					}
					else
					{
						$photoplog['set_thumbs'] .= "
							<a href=\"".$photoplog['location']."/index.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_set_fileid."\" onclick=\"photoplog_changeimage('".addslashes_js($photoplog['file_olink'])."','".addslashes_js($photoplog['file_llink'])."'); return false;\"><img style=\"".$vbulletin->options['photoplog_csssmall_thumbs']."\" src=\"".$photoplog['file_slink']."\" alt=\"".$vbphrase['photoplog_image_in_set']."\" border=\"0\" /></a>
						";
					}
//				}
			}
		}
		$db->free_result($photoplog_useset_infos);

		($hook = vBulletinHook::fetch_hook('photoplog_index_setthumbs')) ? eval($hook) : false;

		$photoplog_file_location = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_info['userid']."/".$photoplog_file_info['filename'];
		$photoplog_file_check = @getimagesize($photoplog_file_location);
		$photoplog_size_arr = explode(",",str_replace(" ","",$vbulletin->options['photoplog_large_size']));
		$photoplog_frame_count = 0;

		if ($photoplog_file_check[2] == '1' && count($photoplog_size_arr) == 2)
		{
			$photoplog_thumb_w = intval($photoplog_size_arr[0]);
			$photoplog_thumb_h = intval($photoplog_size_arr[1]);

			if (
				$photoplog_file_check[0] <= $photoplog_thumb_w
					&&
				$photoplog_file_check[1] <= $photoplog_thumb_h
			)
			{
				$photoplog_file_contents = @file_get_contents($photoplog_file_location);
				$photoplog_frame_count = count(preg_split('#\x00[\x00-\xFF]\x00\x2C#',$photoplog_file_contents));
				unset($photoplog_file_contents);
			}

			unset($photoplog_thumb_w, $photoplog_thumb_h);
		}

		if ($photoplog_frame_count > 2)
		{
			$photoplog['default_size'] = '';
		}
		unset($photoplog_file_location, $photoplog_file_check, $photoplog_size_arr, $photoplog_frame_count);

		if (!empty($photoplog['default_size']))
		{
			$photoplog['default_size'] = '/'.$photoplog['default_size'];
		}

		$photoplog['catid'] = $photoplog_file_info['catid'];
		if (!in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
		{
			$photoplog['category_title'] = htmlspecialchars_uni($vbphrase['photoplog_not_available']);
			$photoplog['category_description'] = $vbphrase['photoplog_not_available'];
		}
		else
		{
			$photoplog['category_title'] = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog['catid']]['title']);
			$photoplog['category_description'] = $photoplog_ds_catopts[$photoplog['catid']]['description'];
		}

		$photoplog_allow_desc_html = 0;
		if ($photoplog_ds_catopts[$photoplog['catid']]['options'])
		{
			$photoplog_allowhtml = convert_bits_to_array($photoplog_ds_catopts[$photoplog['catid']]['options'], $photoplog_categoryoptions);
			$photoplog_allow_desc_html = $photoplog_allowhtml['allowdeschtml'];
			unset($photoplog_allowhtml);
		}

		$photoplog['category_description_tag'] = htmlspecialchars_uni($photoplog['category_description']);
		if (!$photoplog_allow_desc_html)
		{
			$photoplog['category_description'] = htmlspecialchars_uni($photoplog['category_description']);
		}

		$photoplog['title'] = photoplog_process_text($photoplog['title'], $photoplog['catid'], true, false);
		$photoplog['description'] = photoplog_process_text($photoplog['description'], $photoplog['catid'], false, false);

		$photoplog_custom_field = '';
		$photoplog_is_admin = (can_administer('canadminforums')) ? 'admin' : '';
		$photoplog_custom_field = photoplog_make_customfield_rows($photoplog_fielddata,$photoplog['title'],
			$photoplog['description'],$photoplog['catid'],$photoplog_is_admin);

		$photoplog['filmstrip'] = '';
		if ($vbulletin->options['photoplog_filmstrip_active'])
		{
			$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], 0, 1).'link';
			$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], -1, 1).'link';

			$photoplog['do_highslide'] = 0;
			if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
			{
				$photoplog['do_highslide'] = 1;
			}

			$photoplog_film_sql = '';
			if (!$vbulletin->options['photoplog_film_thumbs'])
			{
				$photoplog_film_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.catid = ".intval($photoplog['catid']);
				if ($vbulletin->options['photoplog_nest_thumbs'])
				{
					$photoplog_child_list = array();
					if (isset($photoplog_list_relatives[$photoplog['catid']]))
					{
						$photoplog_child_list = $photoplog_list_relatives[$photoplog['catid']];
					}
					if (!empty($photoplog_child_list))
					{
						$photoplog_film_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.catid IN (" . intval($photoplog['catid']) . "," . implode(",",$photoplog_child_list) . ")";
					}
				}
			}

			($hook = vBulletinHook::fetch_hook('photoplog_index_filmsql')) ? eval($hook) : false;

			$photoplog_filmstrip_number = intval($vbulletin->options['photoplog_numfilm_thumbs']);
			if (!in_array($photoplog_filmstrip_number, array(3,5,7)))
			{
				$photoplog_filmstrip_number = 5;
			}

			$photoplog_filmstrip_limit = $photoplog_filmstrip_number - 1;
			$photoplog_nump_limit = $photoplog_filmstrip_limit / 2; // even

			$photoplog_nexts = $db->query_read_slave("SELECT fileid, title, userid, filename
				FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				WHERE fileid > ".intval($photoplog['fileid'])."
				AND fileid < ".intval($photoplog['fileid'])." + 100
				$photoplog_film_sql
				$photoplog_catid_sql1
				$photoplog_admin_sql1
				ORDER BY fileid ASC LIMIT ".intval($photoplog_filmstrip_limit)."
			");

			$photoplog_num_n = $db->num_rows($photoplog_nexts);
			// $photoplog_num_p = ($photoplog_num_n < 2) ? 4 - $photoplog_num_n : 2; // 5
			$photoplog_num_p = ($photoplog_num_n < $photoplog_nump_limit) ? $photoplog_filmstrip_limit - $photoplog_num_n : $photoplog_nump_limit;

			$photoplog_prevs = $db->query_read_slave("SELECT fileid, title, userid, filename
				FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				WHERE fileid < ".intval($photoplog['fileid'])."
				AND fileid > ".intval($photoplog['fileid'])." - 100
				$photoplog_film_sql
				$photoplog_catid_sql1
				$photoplog_admin_sql1
				ORDER BY fileid DESC LIMIT ".intval($photoplog_num_p)."
			");

			$photoplog_film = array();
			$photoplog_film_title = array();
			$photoplog_film_userid = array();
			$photoplog_film_filename = array();

			while ($photoplog_prev = $db->fetch_array($photoplog_prevs))
			{
				$photoplog_film[] = intval($photoplog_prev['fileid']);
				$photoplog_film_title[] = photoplog_process_text($photoplog_prev['title'], $photoplog['catid'], true, false);
				$photoplog_film_userid[] = $photoplog_prev['userid'];
				$photoplog_film_filename[] = $photoplog_prev['filename'];
			}

			$photoplog_film = array_reverse($photoplog_film);
			$photoplog_film_title = array_reverse($photoplog_film_title);
			$photoplog_film_userid = array_reverse($photoplog_film_userid);
			$photoplog_film_filename = array_reverse($photoplog_film_filename);

			$photoplog_film[] = intval($photoplog['fileid']);
			$photoplog_film_title[] = strval($photoplog['title']);
			$photoplog_film_userid[] = $photoplog['userid'];
			$photoplog_film_filename[] = $photoplog['filename'];

			while ($photoplog_next = $db->fetch_array($photoplog_nexts))
			{
				$photoplog_film[] = intval($photoplog_next['fileid']);
				$photoplog_film_title[] = photoplog_process_text($photoplog_next['title'], $photoplog['catid'], true, false);
				$photoplog_film_userid[] = $photoplog_next['userid'];
				$photoplog_film_filename[] = $photoplog_next['filename'];
			}

			$photoplog_film = array_values(array_slice($photoplog_film, 0, $photoplog_filmstrip_number));
			$photoplog_film_title = array_values(array_slice($photoplog_film_title, 0, $photoplog_filmstrip_number));
			$photoplog_film_userid = array_values(array_slice($photoplog_film_userid, 0, $photoplog_filmstrip_number));
			$photoplog_film_filename = array_values(array_slice($photoplog_film_filename, 0, $photoplog_filmstrip_number));

			$db->free_result($photoplog_prevs);
			$db->free_result($photoplog_nexts);

			$photoplog_film_frame = '';
			$photoplog_filmtitle = '';
			$photoplog_film_current = 0;
			$photoplog_film_count = count($photoplog_film);
			$photoplog_film_prevnext = '';

			foreach ($photoplog_film AS $photoplog_filmkey => $photoplog_filmid)
			{
				$photoplog_current = 0;
				if ($photoplog_filmid == $photoplog['fileid'])
				{
					$photoplog_current = 1;
					$photoplog_film_current = $photoplog_filmkey + 1;
				}

				$photoplog_filmtitle = $photoplog_film_title[$photoplog_filmkey];
				$photoplog_filmuserid = $photoplog_film_userid[$photoplog_filmkey];
				$photoplog_filmfilename = $photoplog_film_filename[$photoplog_filmkey];

				$photoplog['hscnt'] = intval($photoplog['hscnt']) + 1;

				photoplog_file_link($photoplog_filmuserid, $photoplog_filmid, $photoplog_filmfilename);

				($hook = vBulletinHook::fetch_hook('photoplog_index_filmframe')) ? eval($hook) : false;

				eval('$photoplog_film_frame .= "' . fetch_template('photoplog_film_frame') . '";');
			}

			$photoplog_film_next_pos = $photoplog_film_count - $photoplog_film_current;
			$photoplog_film_prev_pos = $photoplog_film_count - $photoplog_film_next_pos - 1;
			$photoplog_film_next_num = $photoplog_film_current - 1 + 1;
			$photoplog_film_prev_num = $photoplog_film_current - 1 - 1;

			$photoplog_film_prevpos = 0;
			if ($photoplog_film_prev_pos > 0)
			{
				$photoplog_film_prevpos = 1;
			}

			$photoplog_film_nextpos = 0;
			if ($photoplog_film_next_pos > 0)
			{
				$photoplog_film_nextpos = 1;
			}

			if ($photoplog_film_frame)
			{
				($hook = vBulletinHook::fetch_hook('photoplog_index_filmstrip')) ? eval($hook) : false;

				eval('$photoplog_film_prevnext = "' . fetch_template('photoplog_film_prevnext') . '";');
				eval('$photoplog[\'filmstrip\'] = "' . fetch_template('photoplog_film_strip') . '";');
			}
		}

		$photoplog_rate_bits = '';
		$photoplog['rate_list'] = '';
		$photoplog_cnt_bits = 0;
		$photoplog['rate_pagenav'] = '';
		$photoplog_rate_count = 0;
		$photoplog['comment_raw_average'] = $vbphrase['photoplog_none'];
		$photoplog['comment_img_average'] = 0;
		$photoplog['comments'] = 0;

		$photoplog['inlineform'] = 0;
		$photoplog['inlinecanedit'] = 0;
		$photoplog['inlinecandelete'] = 0;

		$photoplog['starbox'] = 0;
		if ($photoplog['do_comments'] && defined('PHOTOPLOG_USER8'))
		{
			$photoplog['starbox'] = 1;
			if ($vbulletin->options['photoplog_rate_once'])
			{
				$photoplog_starbox_info = $db->query_first_slave("SELECT SUM(IF(rating > 0, 1, 0)) AS cnt1
					FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
					WHERE fileid = ".intval($photoplog['fileid'])."
					AND userid = ".intval($vbulletin->userinfo['userid'])."
				");
				if ($photoplog_starbox_info['cnt1'] > 0)
				{
					$photoplog['starbox'] = 0;
				}
				$db->free_result($photoplog_starbox_info);
			}
		}

		$photoplog['albumit'] = 0;
		if (defined('PHOTOPLOG_USER15'))
		{
			$photoplog['albumit'] = 1;
		}

		if ($photoplog['do_comments'] && defined('PHOTOPLOG_USER7'))
		{
			$photoplog_comment_raw_average_float = floatval($photoplog_file_info['ave_ratings']);
			$photoplog_rate_file_tot = intval($photoplog_file_info['num_comments']);
			$photoplog['comment_raw_average'] = sprintf("%.2f",round($photoplog_comment_raw_average_float,2));
			$photoplog['comment_img_average'] = intval(round($photoplog_comment_raw_average_float,0));

			$photoplog['comments'] = $photoplog_rate_file_tot;

			sanitize_pageresults($photoplog_rate_file_tot, $photoplog_page_num, $photoplog_per_page, 5, 5);
			$photoplog_rate_limit_lower = ($photoplog_page_num - 1) * $photoplog_per_page;

			if ($photoplog_rate_limit_lower < 0)
			{
				$photoplog_rate_limit_lower = 0;
			}

			$photoplog_rate_limit_lower = intval($photoplog_rate_limit_lower);
			$photoplog_per_page = intval($photoplog_per_page);

			$photoplog_rate_infos = $db->query_read("SELECT *
				FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE fileid = ".intval($photoplog_file_id)."
				$photoplog_catid_sql2
				$photoplog_admin_sql2
				AND comment != ''
				ORDER BY dateline ASC
				LIMIT $photoplog_rate_limit_lower,$photoplog_per_page
			");

			$photoplog_rate_sort_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog_file_id;
			$photoplog['rate_pagenav'] = construct_page_nav($photoplog_page_num, $photoplog_per_page, $photoplog_rate_file_tot, $photoplog_rate_sort_url, '#comment');

			$photoplog_rate_username = '';
			$photoplog_rate_rating = '';
			$photoplog_rate_title = '';
			$photoplog_rate_comment = '';
			$photoplog_rate_date = '';
			$photoplog_rate_time = '';
			$photoplog_rate_moderate = 0;

			if ($photoplog_rate_infos)
			{
				require_once(DIR . '/includes/functions_bigthree.php');
				require_once(DIR . '/includes/class_bbcode.php');

				$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

				$photoplog_userid_list = 0;
				while ($photoplog_rate_info = $db->fetch_array($photoplog_rate_infos))
				{
					$photoplog_userid_list .= ', '.intval($photoplog_rate_info['userid']);
				}
				$photoplog_userid_list = implode(", ", array_unique(explode(", ", $photoplog_userid_list)));

				$photoplog_hook_query_fields = '';
				$photoplog_hook_query_joins = '';

				($hook = vBulletinHook::fetch_hook('photoplog_index_userinfo')) ? eval($hook) : false;

				$photoplog_vbuser_infos = $db->query_read("SELECT user.userid, user.username,
					user.usertitle, user.joindate, user.posts,
					user.avatarid, user.avatarrevision, user.photoplog_filecount, user.photoplog_commentcount,
					user.usergroupid, user.membergroupids, user.infractiongroupids, user.lastactivity,
					IF(user.displaygroupid = 0, user.usergroupid, user.displaygroupid) AS displaygroupid,
					IF(user.options & ".intval($vbulletin->bf_misc_useroptions['invisible']).", 1, 0) AS invisible,
					avatar.avatarpath AS avpath, customavatar.userid AS avuserid, customavatar.dateline AS avdateline,
					usertextfield.signature, sigparsed.signatureparsed, sigparsed.hasimages AS sighasimages,
					sigpic.userid AS sigpic, sigpic.dateline AS sigpicdateline,
					sigpic.width AS sigpicwidth, sigpic.height AS sigpicheight
					$photoplog_hook_query_fields
					FROM " . TABLE_PREFIX . "user AS user
					LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON (avatar.avatarid = user.avatarid)
					LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON (customavatar.userid = user.userid)
					LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON (usertextfield.userid = user.userid)
					LEFT JOIN " . TABLE_PREFIX . "sigparsed AS sigparsed ON
					(
						sigparsed.userid = user.userid
							AND
						sigparsed.styleid = ".intval(STYLEID)."
							AND
						sigparsed.languageid = ".intval(LANGUAGEID)."
					)
					LEFT JOIN " . TABLE_PREFIX . "sigpic AS sigpic ON (sigpic.userid = user.userid)
					$photoplog_hook_query_joins
					WHERE user.userid IN (".$photoplog_userid_list.")
				");

				$photoplog_rates_array = array();
				while ($photoplog_vbuser_info = $db->fetch_array($photoplog_vbuser_infos))
				{
					$photoplog_rates_userid = $photoplog_vbuser_info['userid'];
					if (!isset($photoplog_rates_array[$photoplog_rates_userid]))
					{
						fetch_online_status($photoplog_vbuser_info, true);

						if (!$vbulletin->userinfo['userid'] || $vbulletin->userinfo['showsignatures'])
						{
							$photoplog_vbuser_perms = cache_permissions($photoplog_vbuser_info, false, false);
							$bbcode_parser->set_parse_userinfo($photoplog_vbuser_info, $photoplog_vbuser_perms);
							$photoplog_vbuser_info['signature'] = $bbcode_parser->parse(
								$photoplog_vbuser_info['signature'],
								'signature',
								true,
								false,
								$photoplog_vbuser_info['signatureparsed'],
								$photoplog_vbuser_info['sighasimages'],
								true
							);
							$bbcode_parser->set_parse_userinfo(array());
							unset($photoplog_vbuser_perms);
						}
						else
						{
							$photoplog_vbuser_info['signature'] = '';
						}

						$photoplog_rates_array[$photoplog_rates_userid] = array(
							'photoplog_rate_usertitle' => $photoplog_vbuser_info['usertitle'],
							'photoplog_rate_joindate' => $photoplog_vbuser_info['joindate'],
							'photoplog_rate_numposts' => $photoplog_vbuser_info['posts'],
							'photoplog_rate_username' => $photoplog_vbuser_info['username'],
							'photoplog_rate_musername' => $photoplog_vbuser_info,
							'photoplog_rate_avatarid' => $photoplog_vbuser_info['avatarid'],
							'photoplog_rate_avatarrevision' => $photoplog_vbuser_info['avatarrevision'],
							'photoplog_rate_filecount' => $photoplog_vbuser_info['photoplog_filecount'],
							'photoplog_rate_commentcount' => $photoplog_vbuser_info['photoplog_commentcount'],
							'photoplog_rate_onlinestatus' => $photoplog_vbuser_info['onlinestatus'],
							'photoplog_rate_avpath' => $photoplog_vbuser_info['avpath'],
							'photoplog_rate_avuserid' => $photoplog_vbuser_info['avuserid'],
							'photoplog_rate_avdateline' => $photoplog_vbuser_info['avdateline'],
							'photoplog_rate_signature' => $photoplog_vbuser_info['signature']
						);

						($hook = vBulletinHook::fetch_hook('photoplog_index_ratesarray')) ? eval($hook) : false;
					}
				}

				$db->free_result($photoplog_vbuser_infos);

				$db->data_seek($photoplog_rate_infos, 0);

				$photoplog_rate_avatar = '';
				$photoplog_rate_avatar_link = array();

				while ($photoplog_rate_info = $db->fetch_array($photoplog_rate_infos))
				{
					$photoplog_cnt_bits++;
					$photoplog_rate_count = (($photoplog_page_num - 1) * 5) + $photoplog_cnt_bits;

					$photoplog['commentid'] = $photoplog_rate_info['commentid'];
					$photoplog_commentid_tag = 'comment'.$photoplog['commentid'];

					$photoplog_rate_userid = $photoplog_rate_info['userid'];
					$photoplog['infraction'] = 0;
					if (
						$photoplog_rate_userid && $photoplog_rate_userid != $vbulletin->userinfo['userid']
							&&
						($vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['cangiveinfraction'])
					)
					{
						$photoplog['infraction'] = 1;
					}

					$photoplog['comment_owner'] = 0;
					if ($photoplog_rate_userid == $vbulletin->userinfo['userid'])
					{
						$photoplog['comment_owner'] = 1;
					}

					$photoplog_rate_username = $photoplog_rate_info['username'];
					$photoplog_rate_rating = $photoplog_rate_info['rating'];
					$photoplog_rate_title = $photoplog_rate_info['title'];
					$photoplog_rate_comment = $photoplog_rate_info['comment'];
					$photoplog_rate_date = vbdate($vbulletin->options['dateformat'],$photoplog_rate_info['dateline'],true);
					$photoplog_rate_time = vbdate($vbulletin->options['timeformat'],$photoplog_rate_info['dateline']);
					$photoplog_rate_moderate = $photoplog_rate_info['moderate'];

					$photoplog_rate_lastedit = '';
					if ($photoplog_rate_info['lastedit'])
					{
						$photoplog_rate_lastedit = $photoplog_rate_info['lastedit'];
					}

					$photoplog_rate_title = photoplog_process_text($photoplog_rate_title, $photoplog['catid'], true, false);
					$photoplog_rate_comment = photoplog_process_text($photoplog_rate_comment, $photoplog['catid'], false, false);

					$photoplog_newold_statusicon = 'old';
					if ($photoplog_rate_info['dateline'] > $vbulletin->userinfo['lastvisit'])
					{
						$photoplog_newold_statusicon = 'new';
					}

					$photoplog_rate_usertitle = '';
					$photoplog_rate_joindate = '--';
					$photoplog_rate_numposts = '--';
					$photoplog_rate_username = '';
					$photoplog_rate_musername = $vbphrase['guest'];
					$photoplog_rate_avatarid = '';
					$photoplog_rate_avatarrevision = '';
					$photoplog_rate_filecount = 0;
					$photoplog_rate_commentcount = 0;
					$photoplog_rate_onlinestatus = '&nbsp;';
					$photoplog_rate_avpath = '';
					$photoplog_rate_avuserid = '';
					$photoplog_rate_avdateline = '';
					$photoplog_rate_signature = '';
					$photoplog_rate_avatar = '';

					if (isset($photoplog_rates_array[$photoplog_rate_userid]))
					{
						$photoplog_rate_usertitle = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_usertitle'];
						$photoplog_rate_joindate = vbdate($vbulletin->options['registereddateformat'],$photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_joindate']);
						$photoplog_rate_numposts = vb_number_format($photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_numposts']);
						$photoplog_rate_username = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_username'];
						$photoplog_rate_musername = fetch_musername($photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_musername']);
						$photoplog_rate_avatarid = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_avatarid'];
						$photoplog_rate_avatarrevision = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_avatarrevision'];
						$photoplog_rate_filecount = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_filecount'];
						$photoplog_rate_commentcount = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_commentcount'];
						$photoplog_rate_onlinestatus = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_onlinestatus'];
						$photoplog_rate_avpath = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_avpath'];
						$photoplog_rate_avuserid = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_avuserid'];
						$photoplog_rate_avdateline = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_avdateline'];
						$photoplog_rate_signature = $photoplog_rates_array[$photoplog_rate_userid]['photoplog_rate_signature'];

						if ($vbulletin->userinfo['showavatars'] && $vbulletin->options['avatarenabled'] && !isset($photoplog_rate_avatar_link[$photoplog_rate_userid]))
						{
							$photoplog_rate_avatar_link[$photoplog_rate_userid] = '';

							if ($photoplog_rate_avatarid)
							{
								if ($photoplog_rate_avpath)
								{
									$photoplog_rate_avatar_link[$photoplog_rate_userid] = "<img src=\"".$photoplog_rate_avpath."\" alt=\"".$vbphrase['photoplog_image']."\" border=\"0\" />";
								}
							}
							else
							{
								if ($photoplog_rate_avuserid)
								{
									if ($vbulletin->options['usefileavatar'])
									{
										$photoplog_rate_avatar_link[$photoplog_rate_userid] = "<img src=\"".$vbulletin->options['avatarurl']."/avatar".$photoplog_rate_userid."_".$photoplog_rate_avatarrevision.".gif\" alt=\"".$vbphrase['photoplog_image']."\" border=\"0\" />";
									}
									else
									{
										$photoplog_rate_avatar_link[$photoplog_rate_userid] = "<img src=\"image.php?".$vbulletin->session->vars['sessionurl']."u=".$photoplog_rate_userid."&amp;dateline=".$photoplog_rate_avdateline."\" alt=\"".$vbphrase['photoplog_image']."\" border=\"0\" />";
									}
								}
							}

							$photoplog_rate_avatar = $photoplog_rate_avatar_link[$photoplog_rate_userid];
						}
						else if ($vbulletin->userinfo['showavatars'] && $vbulletin->options['avatarenabled'] && isset($photoplog_rate_avatar_link[$photoplog_rate_userid]))
						{
							$photoplog_rate_avatar = $photoplog_rate_avatar_link[$photoplog_rate_userid];
						}
						else
						{
							$photoplog_rate_avatar = '';
						}
					}

					$photoplog_inline_perm = array();
					$photoplog_inline_perm['caneditowncomments'] = 0;
					$photoplog_inline_perm['candeleteowncomments'] = 0;
					$photoplog_inline_perm['caneditothercomments'] = 0;
					$photoplog_inline_perm['candeleteothercomments'] = 0;

					if (isset($photoplog_inline_bits[$photoplog['catid']]))
					{
						$photoplog_inline_perm = convert_bits_to_array($photoplog_inline_bits[$photoplog['catid']], $photoplog_categoryoptpermissions);
					}

					$photoplog['inlinebox'] = 0;
					if (
						(($photoplog_inline_perm['caneditowncomments'] || $photoplog_inline_perm['candeleteowncomments']) && $vbulletin->userinfo['userid'] == $photoplog_rate_userid)
							||
						(($photoplog_inline_perm['caneditothercomments'] || $photoplog_inline_perm['candeleteothercomments']) && $vbulletin->userinfo['userid'] != $photoplog_rate_userid)
					)
					{
						$photoplog['inlinebox'] = 1;
						$photoplog['inlineform'] = 1;
						if (
							($photoplog_inline_perm['caneditowncomments'] && $vbulletin->userinfo['userid'] == $photoplog_rate_userid)
								||
							($photoplog_inline_perm['caneditothercomments'] && $vbulletin->userinfo['userid'] != $photoplog_rate_userid)
						)
						{
							$photoplog['inlinecanedit'] = 1;
						}
						if (
							($photoplog_inline_perm['candeleteowncomments'] && $vbulletin->userinfo['userid'] == $photoplog_rate_userid)
								||
							($photoplog_inline_perm['candeleteothercomments'] && $vbulletin->userinfo['userid'] != $photoplog_rate_userid)
						)
						{
							$photoplog['inlinecandelete'] = 1;
						}
					}
					unset($photoplog_inline_perm);

					($hook = vBulletinHook::fetch_hook('photoplog_index_ratebit')) ? eval($hook) : false;

					eval('$photoplog_rate_bits .= "' . fetch_template('photoplog_rate_bit') . '";');
				}
			}

			$db->free_result($photoplog_rate_infos);

			if (!$photoplog_cnt_bits)
			{
				$photoplog_rate_bits = '
					<table class="tborder" border="0" cellpadding="'.$stylevar['cellpadding'].'" cellspacing="0" align="center" width="100%" style="margin-bottom: 3px;">
					<tr><td class="alt2">'.$vbphrase['photoplog_not_available'].'</td></tr></table>
				';
			}

			($hook = vBulletinHook::fetch_hook('photoplog_index_ratelist')) ? eval($hook) : false;

			eval('$photoplog[\'rate_list\'] .= "' . fetch_template('photoplog_rate_list') . '";');
		}

		$db->free_result($photoplog_file_infos);

		if (!$photoplog['do_comments'])
		{
			$photoplog['quickreply'] = 0;
			$photoplog['quickreply_form'] = '';
		}
		else if ($photoplog['do_comments'] && defined('PHOTOPLOG_USER8'))
		{
			$photoplog['quickreply'] = 1;

			$vbulletin->bbcodecache = array();

			require_once(DIR . '/includes/functions_editor.php');

			$vBeditTemplate['clientscript'] = '';
			$show['wysiwyg'] = ($photoplog_do_bbcode ? is_wysiwyg_compatible() : 0);
			$istyles_js = construct_editor_styles_js();
			$showsig = 0;
			$qrpostid = 'who cares';
			$show['qr_require_click'] = 0;

			// yep this is how fileid is passed in
			$editorid = construct_edit_toolbar('', $photoplog_do_html, 'nonforum', $photoplog_do_smilies, $photoplog['fileid'], false, 'qr');

			$messagearea = '
				<script type="text/javascript">
				<!--
					var threaded_mode = 0;
					var require_click = 0;
					var is_last_page = 0;
					var allow_ajax_qr = 0;
					var ajax_last_post = 0;
				// -->
				</script>
				'.$messagearea;

			if ($vbulletin->options['photoplog_lightbox_orig'] || $vbulletin->options['photoplog_lightbox_film'])
			{
				// vB JavaScript and Lightbox Javascript not compatible
				$photoplog_switchmode = '<td><div class="imagebutton" id="'.$editorid.'_cmd_switchmode"><img src="'.$stylevar['imgdir_editor'].'/switchmode.gif" width="21" height="20" alt="'.$vbphrase['switch_editor_mode'].'" /></div></td>';
				$messagearea = str_replace($photoplog_switchmode, '', $messagearea);
			}

			$show['quickreply_collapse'] = true;
			if (is_browser('mozilla') && $show['wysiwyg'] == 2)
			{
				$show['quickreply_collapse'] = false;
				unset($vbcollapse['collapseobj_quickreply'],$vbcollapse['collapseimg_quickreply'],$vbcollapse['collapsecel_quickreply']);
			}

			($hook = vBulletinHook::fetch_hook('photoplog_index_quickreplyform')) ? eval($hook) : false;

			eval('$photoplog[\'quickreply_form\'] = "' . fetch_template('photoplog_quickreply_form') . '";');
			$photoplog['quickreply_form'] = $vBeditTemplate['clientscript']."\n".$photoplog['quickreply_form'];
		}

		photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);

		$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_large_thumb'], 0, 1).'link';
		$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_large_thumb'], -1, 1).'link';

		$photoplog['do_highslide'] = 0;
		if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
		{
			$photoplog['do_highslide'] = 1;
		}

		$photoplog['hslink1'] = $photoplog['file_llink'];
		$photoplog['hslink2'] = $photoplog['file_olink'];
		if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
		{
			$photoplog['hslink1'] = $photoplog[$photoplog_hslink1];
			$photoplog['hslink2'] = $photoplog[$photoplog_hslink2];
		}

		($hook = vBulletinHook::fetch_hook('photoplog_index_viewfile')) ? eval($hook) : false;

		photoplog_output_page('photoplog_view_file', $vbphrase['photoplog_view_file'], '', $photoplog_navbits);
	}
	else if (!$photoplog_file_info && $photoplog_file_id)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_mod_queue']);
	}
	else
	{
		$photoplog['file_bits'] = '';
		$photoplog['block_bits'] = '';
		$photoplog['user_bits'] = '';
		$photoplog['pagenav'] = '';
		$photoplog['index_page'] = 0;
		$photoplog['inlineform'] = 0;
		$photoplog['inlinecanedit'] = 0;
		$photoplog['inlinecandelete'] = 0;
		$photoplog['member_folder'] = 0;

		$photoplog_numcat_thumbs = intval($vbulletin->options['photoplog_numcat_thumbs']);

		if ($photoplog_numcat_thumbs < 1)
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_results']);
		}

		if (
			!($photoplog_order_id && isset($_REQUEST['v']))
				&&
			(
				($photoplog['user_id'] >= 0 && isset($_REQUEST['u']))
					||
				($photoplog['cat_id'] >= 0 && isset($_REQUEST['c']))
					||
				($photoplog['letter_id'] && isset($_REQUEST['q']))
			)
		)
		{
			if (!$photoplog['user_id'] && !$photoplog['cat_id'] && $photoplog['letter_id'])
			{
				if ($photoplog['letter_id'] == '1')
				{
//					$photoplog_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.username NOT REGEXP '^[A-Za-z]'";
					$photoplog_sql = "AND (" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.username > 'Z' OR " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.username < 'A')";
				}
				else
				{
					$photoplog_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.username LIKE '".$db->escape_string($photoplog['letter_id'])."%'";
				}
				$photoplog_link = "q=".urlencode($photoplog['letter_id']);
			}
			else if (!$photoplog['user_id'] && $photoplog['cat_id'] >= 0 && !$photoplog['letter_id'])
			{
				$photoplog_sql = '';
				$photoplog_link = '';
				$photoplog_ismembersfolder = 0;
				$photoplog_actasdivider = 0;

				if ($photoplog_ds_catopts[$photoplog['cat_id']]['options'])
				{
					$photoplog_actaswhat = convert_bits_to_array($photoplog_ds_catopts[$photoplog['cat_id']]['options'], $photoplog_categoryoptions);
					$photoplog_ismembersfolder = $photoplog_actaswhat['ismembersfolder'];
					$photoplog_actasdivider = $photoplog_actaswhat['actasdivider'];
				}
				if ($photoplog_actasdivider)
				{
					$photoplog['index_page'] = 1;
				}
				else if ($photoplog_ismembersfolder && !$photoplog['user_id2'])
				{
					$photoplog['index_page'] = 1;
					$photoplog['member_folder'] = 1;
				}
				else if ($photoplog_ismembersfolder && $photoplog['user_id2'])
				{
					$photoplog['user_id'] = $photoplog['user_id2'];
					$photoplog_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.catid = ".intval($photoplog['cat_id']);
					$photoplog_sql .= " AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.userid = ".intval($photoplog['user_id']);
					$photoplog_link = "c=".$photoplog['cat_id']."&amp;u=".$photoplog['user_id'];
				}
				else
				{
					$photoplog_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.catid = ".intval($photoplog['cat_id']);
					if ($vbulletin->options['photoplog_nest_thumbs'])
					{
						$photoplog_child_list = array();
						if (isset($photoplog_list_relatives[$photoplog['cat_id']]))
						{
							$photoplog_child_list = $photoplog_list_relatives[$photoplog['cat_id']];
						}
						if (!empty($photoplog_child_list))
						{
							$photoplog_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.catid IN (" . intval($photoplog['cat_id']) . "," . implode(",",$photoplog_child_list) . ")";
						}
					}
					$photoplog_link = "c=".$photoplog['cat_id'];
				}
			}
			else if ($photoplog['user_id'] >=0 && !$photoplog['cat_id'] && !$photoplog['letter_id'])
			{
				$photoplog_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.userid = ".intval($photoplog['user_id']);
				$photoplog_link = "u=".$photoplog['user_id'];
			}
			else
			{
				photoplog_index_bounce();
			}

			if (!$photoplog['index_page'])
			{
				$photoplog_file_count = $db->query_first("SELECT COUNT(*) AS num
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE 1=1
					$photoplog_sql
					$photoplog_catid_sql1
					$photoplog_admin_sql1
				");

				$photoplog_file_tot = intval($photoplog_file_count['num']);

				sanitize_pageresults($photoplog_file_tot, $photoplog_page_num, $photoplog_per_page, $photoplog_numcat_thumbs, $photoplog_numcat_thumbs);
				$photoplog_limit_lower = ($photoplog_page_num - 1) * $photoplog_per_page;

				if ($photoplog_limit_lower < 0)
				{
					$photoplog_limit_lower = 0;
				}

				$photoplog_limit_lower = intval($photoplog_limit_lower);
				$photoplog_per_page = intval($photoplog_per_page);

				$photoplog_file_infos = $db->query_read("SELECT catid, fileid, userid, username,
					title, description, filename, dimensions, filesize, dateline, views, moderate,
					$photoplog_admin_sql4
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE 1=1
					$photoplog_sql
					$photoplog_catid_sql1
					$photoplog_admin_sql1
					ORDER BY dateline DESC, fileid DESC
					LIMIT $photoplog_limit_lower,$photoplog_per_page
				");
				$photoplog_sort_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].$photoplog_link;
				$photoplog['pagenav'] = construct_page_nav($photoplog_page_num, $photoplog_per_page, $photoplog_file_tot, $photoplog_sort_url);
			}

			if ($photoplog['member_folder'])
			{
				$photoplog['letter_id'] = $photoplog['letter_id2'];

				$photoplog_sql = '';
				if ($photoplog['letter_id'] == '1')
				{
//					$photoplog_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.username NOT REGEXP '^[A-Za-z]'";
					$photoplog_sql = "AND (" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.username > 'Z' OR " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.username < 'A')";
				}
				else if ($photoplog['letter_id'])
				{
					$photoplog_sql = "AND " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.username LIKE '".$db->escape_string($photoplog['letter_id'])."%'";
				}

				$photoplog_link = "c=".$photoplog['cat_id'];
				if ($photoplog['letter_id'])
				{
					$photoplog_link .= "&amp;q=".urlencode($photoplog['letter_id']);
				}

				$photoplog_file_count = $db->query_first("SELECT COUNT(DISTINCT(username)) AS num
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE catid = ".intval($photoplog['cat_id'])."
					$photoplog_sql
					$photoplog_catid_sql1
					$photoplog_admin_sql1
				");

				$photoplog_file_tot = intval($photoplog_file_count['num']);

				sanitize_pageresults($photoplog_file_tot, $photoplog_page_num, $photoplog_per_page, 10, 10);
				$photoplog_limit_lower = ($photoplog_page_num - 1) * $photoplog_per_page;

				if ($photoplog_limit_lower < 0)
				{
					$photoplog_limit_lower = 0;
				}

				$photoplog_limit_lower = intval($photoplog_limit_lower);
				$photoplog_per_page = intval($photoplog_per_page);

				$photoplog_user_sql = 'SUM(num_comments0) AS total_comments, MAX(last_comment_id0) AS last_comment_id';
				if ($photoplog['canadminforums'])
				{
					$photoplog_user_sql = 'SUM(num_comments1) AS total_comments, MAX(last_comment_id1) AS last_comment_id';
				}

				$photoplog_file_infos = $db->query_read("SELECT userid, username,
					COUNT(*) as total_files, MAX(fileid) AS last_file_id,
					$photoplog_user_sql
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE catid = ".intval($photoplog['cat_id'])."
					$photoplog_sql
					$photoplog_catid_sql1
					$photoplog_admin_sql1
					GROUP BY username
					ORDER BY username ASC
					LIMIT $photoplog_limit_lower,$photoplog_per_page
				");

				$photoplog_sort_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].$photoplog_link;
				$photoplog['pagenav'] = construct_page_nav($photoplog_page_num, $photoplog_per_page, $photoplog_file_tot, $photoplog_sort_url);
			}
		}
		else if ($photoplog_order_id && isset($_REQUEST['v']))
		{
			switch($photoplog_order_id)
			{
				case 1:
					// newest uploads
					$photoplog_order_sql = "ORDER BY " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dateline DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid DESC";
					break;
				case 2:
					// most viewings
					$photoplog_order_sql = "ORDER BY " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.views DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dateline DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid DESC";
					break;
				case 3:
					// highest ratings
					$photoplog_order_sql = "ORDER BY ave_ratings DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dateline DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid DESC";
					break;
				case 4:
					// most comments
					$photoplog_order_sql = "ORDER BY num_comments DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dateline DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid DESC";
					break;
				case 5:
					// newest comments
					$photoplog_order_sql = "ORDER BY last_comment_dateline DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dateline DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid DESC";
					break;
				default:
					$photoplog_handled = false;

					($hook = vBulletinHook::fetch_hook('photoplog_index_sortsql')) ? eval($hook) : false;

					if (!$photoplog_handled)
					{
						// newest uploads
						$photoplog_order_id = 1;
						$photoplog_order_sql = "ORDER BY " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dateline DESC, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid DESC";
					}
			}

			if (!defined('PHOTOPLOG_USER7') && ($photoplog_order_id == 3 || $photoplog_order_id == 4))
			{
				$photoplog_order_id = 1;
				$photoplog_order_sql = "ORDER BY " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dateline DESC";
			}

			$photoplog_thirty_days_ago = TIMENOW - (86400 * 30);
			$photoplog_sql = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.dateline > ' . intval($photoplog_thirty_days_ago);
			if ($photoplog['cat_id2'])
			{
				$photoplog_sql = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.catid = ' . intval($photoplog['cat_id2']);
			}

			if ($photoplog_order_id == 5 && !$photoplog['cat_id2'])
			{
				$photoplog_file_count = $db->query_first("SELECT COUNT(DISTINCT(" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.fileid)) AS num
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads, " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
					WHERE 1=1
					AND " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.dateline > " . intval($photoplog_thirty_days_ago) . "
					AND " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.fileid = " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid
					AND " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.comment != ''
					$photoplog_catid_sql1
					$photoplog_catid_sql2
					$photoplog_admin_sql1
					$photoplog_admin_sql2
				");
			}
			else
			{
				$photoplog_file_count = $db->query_first("SELECT COUNT(*) AS num
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE 1=1
					$photoplog_sql
					$photoplog_catid_sql1
					$photoplog_admin_sql1
				");
			}

			$photoplog_file_tot = intval($photoplog_file_count['num']);
			sanitize_pageresults($photoplog_file_tot, $photoplog_page_num, $photoplog_per_page, $photoplog_numcat_thumbs, $photoplog_numcat_thumbs);
			$photoplog_limit_lower = ($photoplog_page_num - 1) * $photoplog_per_page;

			if ($photoplog_limit_lower < 0)
			{
				$photoplog_limit_lower = 0;
			}

			$photoplog_limit_lower = intval($photoplog_limit_lower);
			$photoplog_per_page = intval($photoplog_per_page);

			if ($photoplog_order_id == 5 && !$photoplog['cat_id2'])
			{
				$photoplog_file_infos = $db->query_read("SELECT DISTINCT(" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.fileid),
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.catid,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.userid,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.username,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.title,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.description,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.filename,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dimensions,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.filesize,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dateline,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.views,
					" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.moderate,
					$photoplog_admin_sql4
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads, " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
					WHERE 1=1
					AND " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.dateline > " . intval($photoplog_thirty_days_ago) . "
					AND " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.fileid = " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid
					AND " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.comment != ''
					$photoplog_catid_sql1
					$photoplog_catid_sql2
					$photoplog_admin_sql1
					$photoplog_admin_sql2
					$photoplog_order_sql
					LIMIT $photoplog_limit_lower,$photoplog_per_page
				");
			}
			else
			{
				$photoplog_file_infos = $db->query_read("SELECT catid, fileid, userid, username,
					title, description, filename, dimensions, filesize, dateline, views, moderate,
					$photoplog_admin_sql4
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE 1=1
					$photoplog_sql
					$photoplog_catid_sql1
					$photoplog_admin_sql1
					$photoplog_order_sql
					LIMIT $photoplog_limit_lower,$photoplog_per_page
				");
			}

			$photoplog_sort_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'v='.$photoplog_order_id;
			if ($photoplog['cat_id2'])
			{
				$photoplog_sort_url .= '&amp;c='.$photoplog['cat_id2'];
			}
			$photoplog['pagenav'] = construct_page_nav($photoplog_page_num, $photoplog_per_page, $photoplog_file_tot, $photoplog_sort_url);
		}
		else
		{
			$photoplog['index_page'] = 1;
		}

		if (!$photoplog['index_page'])
		{
			$photoplog['block_cols'] = intval($vbulletin->options['photoplog_block_cols']);
			$photoplog['block_width'] = '';
			if ($photoplog['block_cols'] < 1 && $vbulletin->options['photoplog_display_type'] == 1)
			{
				photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_results']);
			}
			$photoplog['block_width'] = intval(100 / max(1, $photoplog['block_cols']));

			$photoplog_last_comment_ids_list = 0;
			while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
			{
				$photoplog_last_comment_ids_list .= ', '.intval($photoplog_file_info['last_comment_id']);
			}
			$photoplog_last_comment_infos = $db->query_read("SELECT fileid, dateline,
				userid, username, title, commentid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE commentid IN (".$photoplog_last_comment_ids_list.")
				$photoplog_catid_sql2
				$photoplog_admin_sql2
				AND comment != ''
				ORDER BY dateline DESC
			");

			$photoplog_comments_array = array();

			while ($photoplog_last_comment_info = $db->fetch_array($photoplog_last_comment_infos))
			{
				$photoplog_comments_fileid = $photoplog_last_comment_info['fileid'];
				$photoplog_comments_dateline = $photoplog_last_comment_info['dateline'];

				if (!isset($photoplog_comments_array[$photoplog_comments_fileid]))
				{
					$photoplog_comments_array[$photoplog_comments_fileid] = array(
						'photoplog_comment_fileid' => $photoplog_last_comment_info['fileid'],
						'photoplog_comment_userid' => $photoplog_last_comment_info['userid'],
						'photoplog_comment_username' => $photoplog_last_comment_info['username'],
						'photoplog_comment_title' => $photoplog_last_comment_info['title'],
						'photoplog_comment_dateline' => $photoplog_last_comment_info['dateline'],
						'photoplog_comment_commentid' => $photoplog_last_comment_info['commentid']
					);
				}
			}
			$db->free_result($photoplog_last_comment_infos);

			$db->data_seek($photoplog_file_infos, 0);

			$photoplog_cnt_bits = 0;

			$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], 0, 1).'link';
			$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], -1, 1).'link';

			$photoplog['do_highslide'] = 0;
			if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
			{
				$photoplog['do_highslide'] = 1;
			}

			while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
			{
				$photoplog_hidden_bit = 0;

				$photoplog['catid'] = intval($photoplog_file_info['catid']);
				if (!in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
				{
					$photoplog['category_title'] = htmlspecialchars_uni($vbphrase['photoplog_not_available']);
					$photoplog['category_description'] = $vbphrase['photoplog_not_available'];
					$photoplog_hidden_bit = 1;
				}
				else
				{
					$photoplog['category_title'] = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog['catid']]['title']);
					$photoplog['category_description'] = $photoplog_ds_catopts[$photoplog['catid']]['description'];
					if ($photoplog_ds_catopts[$photoplog['catid']]['displayorder'] == 0)
					{
						if ($photoplog['cat_id'] != $photoplog['catid'])
						{
							$photoplog_hidden_bit = 1;
						}
					}
				}

				$photoplog_allow_desc_html = 0;
				if ($photoplog_ds_catopts[$photoplog['catid']]['options'])
				{
					$photoplog_allowhtml = convert_bits_to_array($photoplog_ds_catopts[$photoplog['catid']]['options'], $photoplog_categoryoptions);
					$photoplog_allow_desc_html = $photoplog_allowhtml['allowdeschtml'];
					unset($photoplog_allowhtml);
				}

				$photoplog['category_description_tag'] = htmlspecialchars_uni($photoplog['category_description']);
				if (!$photoplog_allow_desc_html)
				{
					$photoplog['category_description'] = htmlspecialchars_uni($photoplog['category_description']);
				}

				if (!$photoplog_hidden_bit || !$photoplog['catid'])
				{
					$photoplog_cnt_bits++;

					$photoplog['do_comments'] = 0;
					if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
					{
						$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
						$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

						$photoplog['do_comments'] = ($photoplog_catoptions['allowcomments']) ? 1 : 0;
					}

					$photoplog['fileid'] = $photoplog_file_info['fileid'];
					$photoplog['userid'] = $photoplog_file_info['userid'];
					$photoplog['username'] = $photoplog_file_info['username'];
					$photoplog['title'] = $photoplog_file_info['title'];
					$photoplog['description'] = $photoplog_file_info['description'];
					$photoplog['filename'] = $photoplog_file_info['filename'];
					$photoplog['dimensions'] = $photoplog_file_info['dimensions'];
					$photoplog['filesize'] = vb_number_format($photoplog_file_info['filesize'],1,true);
					$photoplog['date'] = vbdate($vbulletin->options['dateformat'],$photoplog_file_info['dateline'],true);
					$photoplog['time'] = vbdate($vbulletin->options['timeformat'],$photoplog_file_info['dateline']);
					$photoplog['views'] = $photoplog_file_info['views'];
					$photoplog['moderate'] = $photoplog_file_info['moderate'];

					if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog['title']) > $vbulletin->options['lastthreadchars'])
					{
						$photoplog['title'] = fetch_trimmed_title($photoplog['title'], $vbulletin->options['lastthreadchars']);
						$photoplog['title']  = photoplog_regexp_text($photoplog['title']);
					}
					$photoplog['title'] = photoplog_process_text($photoplog['title'], $photoplog['catid'], true, false);

					$photoplog_add_dots = false;
					if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog['description']) > $vbulletin->options['lastthreadchars'] * 2)
					{
						$photoplog_add_dots = true;
						$photoplog['description'] = fetch_trimmed_title($photoplog['description'], $vbulletin->options['lastthreadchars'] * 2);
						$photoplog['description']  = photoplog_regexp_text($photoplog['description']);
					}
					$photoplog['description'] = photoplog_process_text($photoplog['description'], $photoplog['catid'], false, $photoplog_add_dots);

					$photoplog_comment_count = intval($photoplog_file_info['num_comments']);
					$photoplog_comment_pagenum = '&amp;page='.ceil($photoplog_file_info['num_comments'] / 5);
					$photoplog['comment_raw_average'] = sprintf("%.2f",round($photoplog_file_info['ave_ratings'],2));
					if ($photoplog['comment_raw_average'] == '0.00')
					{
						$photoplog['comment_raw_average'] = $vbphrase['photoplog_none'];
					}
					$photoplog['comment_img_average'] = intval(round($photoplog_file_info['ave_ratings'],0));

					$photoplog_comment_userid = '';
					$photoplog_comment_username = '';
					$photoplog_comment_title = '';
					$photoplog_comment_date = '';
					$photoplog_comment_time = '';
					$photoplog_comment_commentid = '';
					$photoplog_comment_page = '';

					if (isset($photoplog_comments_array[$photoplog['fileid']]))
					{
						$photoplog_comment_userid = $photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_userid'];
						$photoplog_comment_username = $photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_username'];
						$photoplog_comment_title = $photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_title'];
						$photoplog_comment_date = vbdate($vbulletin->options['dateformat'],$photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_dateline'],true);
						$photoplog_comment_time = vbdate($vbulletin->options['timeformat'],$photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_dateline']);
						$photoplog_comment_commentid = '#comment'.$photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_commentid'];
						$photoplog_comment_page = $photoplog_comment_pagenum.$photoplog_comment_commentid;
					}

					if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog_comment_title) > $vbulletin->options['lastthreadchars'])
					{
						$photoplog_comment_title = fetch_trimmed_title($photoplog_comment_title, $vbulletin->options['lastthreadchars']);
						$photoplog_comment_title = photoplog_regexp_text($photoplog_comment_title);
					}
					$photoplog_comment_title = photoplog_process_text($photoplog_comment_title, $photoplog['catid'], true, false);

					$photoplog_inline_perm = array();
					$photoplog_inline_perm['caneditownfiles'] = 0;
					$photoplog_inline_perm['candeleteownfiles'] = 0;
					$photoplog_inline_perm['caneditotherfiles'] = 0;
					$photoplog_inline_perm['candeleteotherfiles'] = 0;

					if (isset($photoplog_inline_bits[$photoplog['catid']]))
					{
						$photoplog_inline_perm = convert_bits_to_array($photoplog_inline_bits[$photoplog['catid']], $photoplog_categoryoptpermissions);
					}

					$photoplog['inlinebox'] = 0;
					if (
						(($photoplog_inline_perm['caneditownfiles'] || $photoplog_inline_perm['candeleteownfiles']) && $vbulletin->userinfo['userid'] == $photoplog['userid'])
							||
						(($photoplog_inline_perm['caneditotherfiles'] || $photoplog_inline_perm['candeleteotherfiles']) && $vbulletin->userinfo['userid'] != $photoplog['userid'])
					)
					{
						$photoplog['inlinebox'] = 1;
						$photoplog['inlineform'] = 1;
						if (
							($photoplog_inline_perm['caneditownfiles'] && $vbulletin->userinfo['userid'] == $photoplog['userid'])
								||
							($photoplog_inline_perm['caneditotherfiles'] && $vbulletin->userinfo['userid'] != $photoplog['userid'])
						)
						{
							$photoplog['inlinecanedit'] = 1;
							$photoplog['select_row'] = photoplog_inline_select_row();
						}
						if (
							($photoplog_inline_perm['candeleteownfiles'] && $vbulletin->userinfo['userid'] == $photoplog['userid'])
								||
							($photoplog_inline_perm['candeleteotherfiles'] && $vbulletin->userinfo['userid'] != $photoplog['userid'])
						)
						{
							$photoplog['inlinecandelete'] = 1;
						}
					}
					unset($photoplog_inline_perm);

					$photoplog['hscnt'] = intval($photoplog['hscnt']) + 1;

					photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);

					if ($vbulletin->options['photoplog_display_type'] == 1)
					{
						($hook = vBulletinHook::fetch_hook('photoplog_index_blockbit')) ? eval($hook) : false;

						eval('$photoplog[\'block_bits\'] .= "' . fetch_template('photoplog_block_bit') . '";');
						if ($photoplog['block_cols'] && ($photoplog_cnt_bits % $photoplog['block_cols'] == 0))
						{
							$photoplog['block_bits'] .= '</tr><tr>';
						}
					}
					else
					{
						($hook = vBulletinHook::fetch_hook('photoplog_index_filebit')) ? eval($hook) : false;

						eval('$photoplog[\'file_bits\'] .= "' . fetch_template('photoplog_file_bit') . '";');
					}
				}
			}

			$db->free_result($photoplog_file_infos);

			$photoplog['block_bits'] = eregi_replace(preg_quote("</tr><tr>")."$","",$photoplog['block_bits']);

			if ($photoplog_cnt_bits && $photoplog['block_cols'] && $vbulletin->options['photoplog_display_type'] == 1)
			{
				$photoplog_cnt_bits_temp = $photoplog_cnt_bits;
				while ($photoplog_cnt_bits_temp % $photoplog['block_cols'] != 0)
				{
					$photoplog['block_bits'] .= "<td class=\"alt1\" align=\"left\" valign=\"bottom\" width=\"".$photoplog['block_width']."%\">&nbsp;</td>";
					$photoplog_cnt_bits_temp++;
				}
				unset($photoplog_cnt_bits_temp);
			}

			if (!$photoplog_cnt_bits)
			{
				if ($vbulletin->options['photoplog_display_type'] == 1)
				{
					$photoplog['block_bits'] = '<td colspan="'.$photoplog['block_cols'].'" class="alt2">'.$vbphrase['photoplog_not_available'].'</td>';
				}
				else
				{
					$photoplog['file_bits'] = '<tr><td colspan="6" class="alt2">'.$vbphrase['photoplog_not_available'].'</td></tr>';
				}
			}
		}

		if ($photoplog['member_folder'])
		{
			$photoplog_members_array = array();
			$photoplog_files_array = array();
			$photoplog_comments_array = array();

			$photoplog_last_file_ids_list = 0;
			$photoplog_last_comment_ids_list = 0;

			$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_category_thumb'], 0, 1).'link';
			$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_category_thumb'], -1, 1).'link';

			$photoplog['do_highslide'] = 0;
			if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
			{
				$photoplog['do_highslide'] = 1;
			}

			while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
			{
				$photoplog_members_userid = $photoplog_file_info['userid'];

				if (!isset($photoplog_members_array[$photoplog_members_userid]))
				{
					$photoplog_members_array[$photoplog_members_userid] = array(
						'photoplog_member_username' => $photoplog_file_info['username'],
						'photoplog_member_total_files' => $photoplog_file_info['total_files'],
						'photoplog_member_last_file_id' => $photoplog_file_info['last_file_id'],
						'photoplog_member_total_comments' => $photoplog_file_info['total_comments'],
						'photoplog_member_last_comment_id' => $photoplog_file_info['last_comment_id']
					);
				}

				$photoplog_last_file_ids_list .= ', '.intval($photoplog_file_info['last_file_id']);
				$photoplog_last_comment_ids_list .= ', '.intval($photoplog_file_info['last_comment_id']);
			}
			$db->free_result($photoplog_file_infos);

			$photoplog_last_file_infos = $db->query_read("SELECT f1.fileid, f1.title,
					f1.filename, f1.catid, f1.dateline
				FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads AS f1
				WHERE f1.fileid IN (".$photoplog_last_file_ids_list.")
				$photoplog_catid_sql1a
				$photoplog_admin_sql1a
			");

			while ($photoplog_last_file_info = $db->fetch_array($photoplog_last_file_infos))
			{
				$photoplog_files_fileid = $photoplog_last_file_info['fileid'];

				if (!isset($photoplog_files_array[$photoplog_files_fileid]))
				{
					$photoplog_files_array[$photoplog_files_fileid] = array(
						'photoplog_file_title' => $photoplog_last_file_info['title'],
						'photoplog_file_filename' => $photoplog_last_file_info['filename'],
						'photoplog_file_catid' => $photoplog_last_file_info['catid'],
						'photoplog_file_dateline' => $photoplog_last_file_info['dateline']
					);
				}
			}
			$db->free_result($photoplog_last_file_infos);

			$photoplog_last_comment_infos = $db->query_read("SELECT f2.commentid, f2.fileid,
					f2.userid, f2.username, f2.title, f2.dateline
				FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment AS f2
				WHERE f2.commentid IN (".$photoplog_last_comment_ids_list.")
				$photoplog_catid_sql2a
				$photoplog_admin_sql2a
				AND f2.comment != ''
			");

			while ($photoplog_last_comment_info = $db->fetch_array($photoplog_last_comment_infos))
			{
				$photoplog_comments_commentid = $photoplog_last_comment_info['commentid'];

				if (!isset($photoplog_comments_array[$photoplog_comments_commentid]))
				{
					$photoplog_comments_array[$photoplog_comments_commentid] = array(
						'photoplog_comment_fileid' => $photoplog_last_comment_info['fileid'],
						'photoplog_comment_userid' => $photoplog_last_comment_info['userid'],
						'photoplog_comment_username' => $photoplog_last_comment_info['username'],
						'photoplog_comment_title' => $photoplog_last_comment_info['title'],
						'photoplog_comment_dateline' => $photoplog_last_comment_info['dateline']
					);
				}
			}
			$db->free_result($photoplog_last_comment_infos);

			// photoplog_members_array[userid] => username, total_files, last_file_id, total_comments, last_comment_id
			// photoplog_files_array[fileid] => title, filename, catid, dateline
			// photoplog_comments_array[commentid] => fileid, userid, username, title, dateline

			$photoplog_cnt_bits = 0;

			foreach ($photoplog_members_array AS $photoplog_members_array_userid => $photoplog_members_array_info)
			{
				$photoplog_hidden_bit = 0;

				$photoplog_user_userid1 = $photoplog_members_array_userid;
				$photoplog_user_fileid1 = $photoplog_members_array_info['photoplog_member_last_file_id'];
				$photoplog_user_commentid = $photoplog_members_array_info['photoplog_member_last_comment_id'];

				$photoplog['catid'] = intval($photoplog_files_array[$photoplog_user_fileid1]['photoplog_file_catid']);
				if (!in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
				{
					$photoplog_hidden_bit = 1;
				}
				else
				{
					if ($photoplog_ds_catopts[$photoplog['catid']]['displayorder'] == 0)
					{
						if ($photoplog['cat_id'] != $photoplog['catid'])
						{
							$photoplog_hidden_bit = 1;
						}
					}
				}

				if (!$photoplog_hidden_bit)
				{
					$photoplog_cnt_bits++;

					// already htmlspecialchars_uni on username
					$photoplog_user_username1 = $photoplog_members_array_info['photoplog_member_username'];
					$photoplog_user_catid = $photoplog['catid'];
					$photoplog_user_description = '';
					$photoplog_user_subcats = '';

					$photoplog['do_comments'] = 0;
					if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
					{
						$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
						$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

						$photoplog['do_comments'] = ($photoplog_catoptions['allowcomments']) ? 1 : 0;
					}

					$photoplog_user_cnt1 = intval($photoplog_members_array_info['photoplog_member_total_files']);
					$photoplog_user_cnt2 = intval($photoplog_members_array_info['photoplog_member_total_comments']);

					$photoplog_user_title1 = $photoplog_files_array[$photoplog_user_fileid1]['photoplog_file_title'];
					if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog_user_title1) > $vbulletin->options['lastthreadchars'])
					{
						$photoplog_user_title1 = fetch_trimmed_title($photoplog_user_title1, $vbulletin->options['lastthreadchars']);
						$photoplog_user_title1 = photoplog_regexp_text($photoplog_user_title1);
					}
					$photoplog_user_title1 = photoplog_process_text($photoplog_user_title1, $photoplog['catid'], true, false);

					$photoplog_user_filename = $photoplog_files_array[$photoplog_user_fileid1]['photoplog_file_filename'];
					$photoplog_user_page = '&amp;page=' . ceil($photoplog_user_cnt2 / 5) . '#comment' . $photoplog_user_commentid;

					$photoplog_user_fileid2 = $photoplog_comments_array[$photoplog_user_commentid]['photoplog_comment_fileid'];
					$photoplog_user_userid2 = $photoplog_comments_array[$photoplog_user_commentid]['photoplog_comment_userid'];
					$photoplog_user_username2 = $photoplog_comments_array[$photoplog_user_commentid]['photoplog_comment_username'];

					$photoplog_user_title2 = $photoplog_comments_array[$photoplog_user_commentid]['photoplog_comment_title'];
					if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog_user_title2) > $vbulletin->options['lastthreadchars'])
					{
						$photoplog_user_title2 = fetch_trimmed_title($photoplog_user_title2, $vbulletin->options['lastthreadchars']);
						$photoplog_user_title2 = photoplog_regexp_text($photoplog_user_title2);
					}
					$photoplog_user_title2 = photoplog_process_text($photoplog_user_title2, $photoplog['catid'], true, false);

					$photoplog_user_stamp1 = $photoplog_files_array[$photoplog_user_fileid1]['photoplog_file_dateline'];
					$photoplog_user_stamp2 = $photoplog_comments_array[$photoplog_user_commentid]['photoplog_comment_dateline'];

					$photoplog_user_date1 = vbdate($vbulletin->options['dateformat'],$photoplog_user_stamp1,true);
					$photoplog_user_time1 = vbdate($vbulletin->options['timeformat'],$photoplog_user_stamp1);
					$photoplog_user_date2 = vbdate($vbulletin->options['dateformat'],$photoplog_user_stamp2,true);
					$photoplog_user_time2 = vbdate($vbulletin->options['timeformat'],$photoplog_user_stamp2);

					if (
						$vbulletin->userinfo['lastvisitdate'] == -1
							||
						$vbulletin->userinfo['lastvisit'] < $photoplog_user_stamp1
							||
						$vbulletin->userinfo['lastvisit'] < $photoplog_user_stamp2
					)
					{
						$photoplog_newold_statusicon = 'new';
					}
					else
					{
						$photoplog_newold_statusicon = 'old';
					}

					if (
						!($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadfiles'])
							&&
						$vbulletin->options['showlocks']
					)
					{
						$photoplog_newold_statusicon .= '_lock';
					}

					$photoplog['hscnt'] = intval($photoplog['hscnt']) + 1;

					photoplog_file_link($photoplog_user_userid1, $photoplog_user_fileid1, $photoplog_user_filename);

					($hook = vBulletinHook::fetch_hook('photoplog_index_userbit')) ? eval($hook) : false;

					eval('$photoplog[\'user_bits\'] .= "' . fetch_template('photoplog_user_bit') . '";');
				}
			}

			unset($photoplog_members_array, $photoplog_files_array, $photoplog_comments_array);

			if (!$photoplog_cnt_bits)
			{
				$photoplog['user_bits'] = '<tr><td colspan="7" class="alt2">'.$vbphrase['photoplog_not_available'].'</td></tr>';
			}

			$photoplog['letter_bar'] = '';
			$photoplog['letter_id'] = strtolower($vbulletin->GPC['q']);

			$photoplog_letter_arr = array(
				'1' => '#', 'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F',
				'g' => 'G', 'h' => 'H', 'i' => 'I', 'j' => 'J', 'k' => 'K', 'l' => 'L', 'm' => 'M',
				'n' => 'N', 'o' => 'O', 'p' => 'P', 'q' => 'Q', 'r' => 'R', 's' => 'S', 't' => 'T',
				'u' => 'U', 'v' => 'V', 'w' => 'W', 'x' => 'X', 'y' => 'Y', 'z' => 'Z'
			);
			$photoplog_folder_flag = 1;
			$photoplog_letter_bar_bits = '';
			foreach ($photoplog_letter_arr AS $photoplog_letter_key => $photoplog_letter_val)
			{
				($hook = vBulletinHook::fetch_hook('photoplog_settings_letterbarbit')) ? eval($hook) : false;

				eval('$photoplog_letter_bar_bits .= "' . fetch_template('photoplog_letter_bar_bit') . '";');
			}

			($hook = vBulletinHook::fetch_hook('photoplog_settings_letterbar')) ? eval($hook) : false;

			eval('$photoplog[\'letter_bar\'] = "' . fetch_template('photoplog_letter_bar') . '";');

			$photoplog['cat_title'] = '';
			if ($photoplog_ds_catopts[$photoplog['cat_id']]['title'])
			{
				$photoplog['cat_title'] = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog['cat_id']]['title']);
			}
			$photoplog['cat_link'] = 'c='.$photoplog['cat_id'];

			($hook = vBulletinHook::fetch_hook('photoplog_index_userlist')) ? eval($hook) : false;

			eval('$photoplog[\'user_list\'] .= "' . fetch_template('photoplog_user_list') . '";');
		}

		$photoplog_catbit_info = array();
		$photoplog_catbit_subcats = '';
		$photoplog['cat_title'] = '';
		$photoplog['cat_description'] = '';
		$photoplog['cat_description_tag'] = '';
		$photoplog['cat_link'] = '';
		$photoplog['title'] = '';

		if ($photoplog['cat_id'] && $photoplog['letter_id'])
		{
			if ($photoplog_ds_catopts[$photoplog['cat_id']]['title'])
			{
				$photoplog['cat_title'] = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog['cat_id']]['title']);
			}
			$photoplog['cat_link'] = 'c='.$photoplog['cat_id'].'&amp;q='.urlencode($photoplog['letter_id']);
		}
		else if ($photoplog['cat_id'] && $photoplog['user_id'])
		{
			if ($photoplog_ds_catopts[$photoplog['cat_id']]['title'])
			{
				$photoplog['cat_title'] = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog['cat_id']]['title']);
			}
			$photoplog['cat_description'] = $vbphrase['photoplog_uploads'].' '.$vbphrase['photoplog_posted_by'].' '.$photoplog['username'];

			$photoplog_allow_desc_html = 0;
			if ($photoplog_ds_catopts[$photoplog['cat_id']]['options'])
			{
				$photoplog_allowhtml = convert_bits_to_array($photoplog_ds_catopts[$photoplog['cat_id']]['options'], $photoplog_categoryoptions);
				$photoplog_allow_desc_html = $photoplog_allowhtml['allowdeschtml'];
				unset($photoplog_allowhtml);
			}

			$photoplog['cat_description_tag'] = htmlspecialchars_uni($vbphrase['photoplog_uploads'].' '.$vbphrase['photoplog_posted_by'].' ').$photoplog['username'];
			if (!$photoplog_allow_desc_html)
			{
				$photoplog['cat_description'] = htmlspecialchars_uni($vbphrase['photoplog_uploads'].' '.$vbphrase['photoplog_posted_by'].' ').$photoplog['username'];
			}

			$photoplog['cat_link'] = 'c='.$photoplog['cat_id'];
		}
		else if ($photoplog['letter_id'])
		{
			$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_uploads_posted_by'].' '.strtoupper($photoplog['letter_id']).'...');
			if ($photoplog['letter_id'] == '1')
			{
				$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_uploads_posted_by'].' #...');
			}
			$photoplog['cat_link'] = 'q='.urlencode($photoplog['letter_id']);
		}
		else if ($photoplog['cat_id'] > 0)
		{
			if ($photoplog_ds_catopts[$photoplog['cat_id']]['title'])
			{
				$photoplog['cat_title'] = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog['cat_id']]['title']);
			}
			if ($photoplog_ds_catopts[$photoplog['cat_id']]['description'])
			{
				$photoplog['cat_description'] = $photoplog_ds_catopts[$photoplog['cat_id']]['description'];

				$photoplog_allow_desc_html = 0;
				if ($photoplog_ds_catopts[$photoplog['cat_id']]['options'])
				{
					$photoplog_allowhtml = convert_bits_to_array($photoplog_ds_catopts[$photoplog['cat_id']]['options'], $photoplog_categoryoptions);
					$photoplog_allow_desc_html = $photoplog_allowhtml['allowdeschtml'];
					unset($photoplog_allowhtml);
				}

				$photoplog['cat_description_tag'] = htmlspecialchars_uni($photoplog['cat_description']);
				if (!$photoplog_allow_desc_html)
				{
					$photoplog['cat_description'] = htmlspecialchars_uni($photoplog['cat_description']);
				}
			}

			$photoplog['cat_link'] = 'c='.$photoplog['cat_id'];

			$photoplog_child_catlist = array();
			if (!empty($photoplog_list_relatives[$photoplog['cat_id']]))
			{
				$photoplog_child_catlist = array_merge(array($photoplog['cat_id']), $photoplog_list_relatives[$photoplog['cat_id']]);
			}

			if (is_array($photoplog_child_catlist) && count($photoplog_child_catlist))
			{
				define('PHOTOPLOG_LISTING_FILE', true);
				require_once('./listing.php');
			}
		}
		else if ($photoplog['user_id'])
		{
			$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_uploads'].' '.$vbphrase['photoplog_posted_by'].' ').$photoplog['username'];
			$photoplog['cat_link'] = 'u='.intval($photoplog['user_id']);
		}
		else if ($photoplog_order_id > 0)
		{
			switch($photoplog_order_id)
			{
				case 1:
					// newest uploads
					$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_newest_uploads']);
					break;
				case 2:
					// most viewings
					$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_most_viewings']);
					break;
				case 3:
					// highest ratings
					$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_highest_ratings']);
					break;
				case 4:
					// most comments
					$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_most_comments']);
					break;
				case 5:
					// newest comments
					$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_newest_comments']);
					break;
				default:
					$photoplog_handled = false;

					($hook = vBulletinHook::fetch_hook('photoplog_index_sorttitle')) ? eval($hook) : false;

					if (!$photoplog_handled)
					{
						// newest uploads
						$photoplog_order_id = 1;
						$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_newest_uploads']);
					}
			}

			if (!defined('PHOTOPLOG_USER7') && ($photoplog_order_id == 3 || $photoplog_order_id == 4))
			{
				$photoplog_order_id = 1;
				$photoplog['cat_title'] = htmlspecialchars_uni($vbphrase['photoplog_newest_uploads']);
			}

			$photoplog['cat_link'] = 'v='.$photoplog_order_id;

			if ($photoplog['cat_id2'])
			{
				$photoplog['cat_link'] = 'v='.$photoplog_order_id.'&amp;c='.$photoplog['cat_id2'];
				if ($photoplog_ds_catopts[$photoplog['cat_id2']]['title'])
				{
					$photoplog['cat_title'] = $photoplog['cat_title'] . htmlspecialchars_uni(' - ' . $photoplog_ds_catopts[$photoplog['cat_id2']]['title']);
				}
			}
			else
			{
				$photoplog['cat_title'] = $photoplog['cat_title'] . htmlspecialchars_uni(' - ' . $vbphrase['photoplog_last_thirty_days']);
			}
		}

		if ($photoplog['cat_title'])
		{
			$photoplog_phrase = $photoplog['cat_title'];
		}
		else
		{
			$photoplog_phrase = htmlspecialchars_uni($vbphrase[photoplog_file_list]);
		}

		if ($vbulletin->options['photoplog_display_type'] == 1)
		{
			($hook = vBulletinHook::fetch_hook('photoplog_index_blocklist')) ? eval($hook) : false;

			photoplog_output_page('photoplog_block_list', $photoplog_phrase, '', $photoplog_navbits);
		}
		else
		{
			($hook = vBulletinHook::fetch_hook('photoplog_index_filelist')) ? eval($hook) : false;

			photoplog_output_page('photoplog_file_list', $photoplog_phrase, '', $photoplog_navbits);
		}

	}
}

($hook = vBulletinHook::fetch_hook('photoplog_index_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'view')
{
	photoplog_index_bounce();
}

?>