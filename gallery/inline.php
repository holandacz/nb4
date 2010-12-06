<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','inline');
define('PHOTOPLOG_LEVEL','bypass');
require_once('./settings.php');

// ########################### Start Inline Page ##########################
if (!in_array($_REQUEST['do'], array('edit','delete','approve','unapprove')))
{
	photoplog_index_bounce();
}

if (($_REQUEST['do'] == 'approve' || $_REQUEST['do'] == 'unapprove') && !$photoplog['canadminforums'])
{
	photoplog_index_bounce();
}

$vbulletin->input->clean_array_gpc('p', array(
	'action' => TYPE_NOHTML,
	'inlinefileids' => TYPE_ARRAY_KEYS_INT,
	'catid' => TYPE_UINT,
	'inlinecommentids' => TYPE_ARRAY_KEYS_INT,
	'fileid' => TYPE_UINT
));

$photoplog_inline_action = $vbulletin->GPC['action'];
if ($photoplog_inline_action != 'files' && $photoplog_inline_action != 'comments')
{
	photoplog_index_bounce();
}

// changing this can increase process time and queries so best not to change it
$photoplog_inline_limit = 20;

if ($photoplog_inline_action == 'files')
{
	// post when block format, thread when list format
	if ($vbulletin->options['photoplog_display_type'] == 1)
	{
		$vbulletin->input->clean_array_gpc('c', array(
			'photoplog_inlinepost' => TYPE_NOHTML
		));
		$photoplog_inline_cookie = $vbulletin->GPC['photoplog_inlinepost'];
	}
	else
	{
		$vbulletin->input->clean_array_gpc('c', array(
			'photoplog_inlinethread' => TYPE_NOHTML
		));
		$photoplog_inline_cookie = $vbulletin->GPC['photoplog_inlinethread'];
	}

	if ($photoplog_inline_cookie)
	{
		$photoplog_inline_cookie = explode('-', eregi_replace('[^0-9-]', '', $photoplog_inline_cookie));
		$photoplog_inline_cookie = $vbulletin->input->clean($photoplog_inline_cookie, TYPE_ARRAY_UINT);
		$vbulletin->GPC['inlinefileids'] = array_unique(array_merge($vbulletin->GPC['inlinefileids'], $photoplog_inline_cookie));
	}

	$photoplog_file_ids = $vbulletin->GPC['inlinefileids'];
	$photoplog_cat_id = $vbulletin->GPC['catid'];

	$photoplog_inline_not_allowed_bits = array();
	if (!empty($photoplog_inline_bits))
	{
		foreach ($photoplog_inline_bits AS $photoplog_inline_bits_catid => $photoplog_inline_bits_opt)
		{
			$photoplog_inline_perm = convert_bits_to_array($photoplog_inline_bits_opt, $photoplog_categoryoptpermissions);
			if (!$photoplog_inline_perm['canuploadfiles'])
			{
				$photoplog_inline_not_allowed_bits[] = intval($photoplog_inline_bits_catid);
			}
		}
	}

	if ($_REQUEST['do'] == 'edit' && (!$photoplog_cat_id || in_array($photoplog_cat_id, array_unique(array_merge($photoplog_inline_not_allowed_bits, $photoplog_perm_not_allowed_bits)))))
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_category']);
	}
	if (empty($photoplog_file_ids) || count($photoplog_file_ids) > $photoplog_inline_limit)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_inline']);
	}

	$photoplog_file_infos = $db->query_read_slave("SELECT fileid, catid, userid, albumids, filename
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid IN (" . implode(',', $photoplog_file_ids) . ")
		$photoplog_catid_sql1
		$photoplog_admin_sql1
	");

	$photoplog_inline_perm = array();
	while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
	{
		$photoplog_inline_fileid = $photoplog_file_info['fileid'];
		$photoplog_inline_catid = $photoplog_file_info['catid'];

		$photoplog_inline_perm[$photoplog_inline_fileid]['caneditownfiles'] = 0;
		$photoplog_inline_perm[$photoplog_inline_fileid]['candeleteownfiles'] = 0;
		$photoplog_inline_perm[$photoplog_inline_fileid]['caneditotherfiles'] = 0;
		$photoplog_inline_perm[$photoplog_inline_fileid]['candeleteotherfiles'] = 0;

		$photoplog_inline_perm[$photoplog_inline_fileid]['catid'] = 0;
		$photoplog_inline_perm[$photoplog_inline_fileid]['userid'] = 0;
		$photoplog_inline_perm[$photoplog_inline_fileid]['albumids'] = 'a:0:{}';
		$photoplog_inline_perm[$photoplog_inline_fileid]['filename'] = '';

		if (isset($photoplog_inline_bits[$photoplog_inline_catid]))
		{
			$photoplog_inline_perm[$photoplog_inline_fileid] = convert_bits_to_array($photoplog_inline_bits[$photoplog_inline_catid], $photoplog_categoryoptpermissions);

			$photoplog_inline_perm[$photoplog_inline_fileid]['catid'] = $photoplog_inline_catid;
			$photoplog_inline_perm[$photoplog_inline_fileid]['userid'] = $photoplog_file_info['userid'];
			$photoplog_inline_perm[$photoplog_inline_fileid]['albumids'] = $photoplog_file_info['albumids'];
			$photoplog_inline_perm[$photoplog_inline_fileid]['filename'] = $photoplog_file_info['filename'];
		}
	}
	$db->free_result($photoplog_file_infos);

	if (empty($photoplog_inline_perm))
	{
		photoplog_index_bounce();
	}

	$photoplog_file_sql = array();
	$photoplog_cat_ids = array();
	$photoplog_album_ids = array();
	$photoplog_user_ids = array();
	$photoplog_file_names = array();
	foreach ($photoplog_inline_perm AS $photoplog_inline_perm_fileid => $photoplog_inline_perm_array)
	{
		if ($_REQUEST['do'] == 'edit' && $photoplog_cat_id != $photoplog_inline_perm_array['catid'])
		{
			if (
				($photoplog_inline_perm_array['caneditownfiles'] && $vbulletin->userinfo['userid'] == $photoplog_inline_perm_array['userid'])
					||
				($photoplog_inline_perm_array['caneditotherfiles'] && $vbulletin->userinfo['userid'] != $photoplog_inline_perm_array['userid'])
			)
			{
				$photoplog_file_sql[] = intval($photoplog_inline_perm_fileid);
				$photoplog_cat_ids[] = intval($photoplog_inline_perm_array['catid']);
			}
		}

		if ($_REQUEST['do'] == 'delete')
		{
			if (
				($photoplog_inline_perm_array['candeleteownfiles'] && $vbulletin->userinfo['userid'] == $photoplog_inline_perm_array['userid'])
					||
				($photoplog_inline_perm_array['candeleteotherfiles'] && $vbulletin->userinfo['userid'] != $photoplog_inline_perm_array['userid'])
			)
			{
				$photoplog_file_sql[] = intval($photoplog_inline_perm_fileid);
				$photoplog_cat_ids[] = intval($photoplog_inline_perm_array['catid']);
				$photoplog_album_ids[$photoplog_inline_perm_fileid] = unserialize($photoplog_inline_perm_array['albumids']);
				$photoplog_file_names[$photoplog_inline_perm_array['filename']] = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_inline_perm_array['userid'];

				if (isset($photoplog_user_ids[$photoplog_inline_perm_array['userid']]))
				{
					$photoplog_user_ids[$photoplog_inline_perm_array['userid']] ++;
				}
				else
				{
					$photoplog_user_ids[$photoplog_inline_perm_array['userid']] = 1;
				}
			}
		}

		if (
			($_REQUEST['do'] == 'approve' || $_REQUEST['do'] == 'unapprove') && $photoplog['canadminforums']
				&&
			(
				(
					($photoplog_inline_perm_array['caneditownfiles'] || $photoplog_inline_perm_array['candeleteownfiles'])
						&&
					$vbulletin->userinfo['userid'] == $photoplog_inline_perm_array['userid']
				)
					||
				$photoplog_inline_perm_array['caneditotherfiles'] || $photoplog_inline_perm_array['candeleteotherfiles']
			)
		)
		{
			$photoplog_file_sql[] = intval($photoplog_inline_perm_fileid);
			$photoplog_cat_ids[] = intval($photoplog_inline_perm_array['catid']);
		}
	}

	$photoplog_file_sql = array_unique($photoplog_file_sql);
	if (empty($photoplog_file_sql))
	{
		// if no ids then invalid request !!!
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_invalid_request']);
	}

	$photoplog_file_sql = implode(',', $photoplog_file_sql);
	if ($_REQUEST['do'] == 'edit')
	{
		if (
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				SET catid = " . intval($photoplog_cat_id) . "
				WHERE fileid IN (" . $photoplog_file_sql . ")
			")
		)
		{
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				SET catid = " . intval($photoplog_cat_id) . "
				WHERE fileid IN (" . $photoplog_file_sql . ")
			");

			$photoplog_cat_ids[] = intval($photoplog_cat_id);
			photoplog_regenerate_counts_table_v2($photoplog_cat_ids);

			$_REQUEST['do'] = 'wipe';
		}
	}

	if ($_REQUEST['do'] == 'delete')
	{
		if (
			$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				WHERE fileid IN (" . $photoplog_file_sql . ")
			")
		)
		{
			foreach ($photoplog_album_ids AS $photoplog_file_fileid => $photoplog_file_albumids)
			{
				if (is_array($photoplog_file_albumids) && count($photoplog_file_albumids) > 0)
				{
					$photoplog_album_infos = $db->query_read_slave("SELECT albumid, fileids
						FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
						WHERE albumid IN (".implode(',', $photoplog_file_albumids).")
					");

					$photoplog_album_cnt = 0;
					$photoplog_album_case1 = '';
					$photoplog_album_case2 = array();

					while ($photoplog_album_info = $db->fetch_array($photoplog_album_infos))
					{
						$photoplog_album_cnt ++;

						$photoplog_album_albumid = intval($photoplog_album_info['albumid']);
						$photoplog_album_fileids = unserialize($photoplog_album_info['fileids']);

						if (is_array($photoplog_album_fileids) && count($photoplog_album_fileids) > 0)
						{
							if (in_array($photoplog_file_fileid, $photoplog_album_fileids))
							{
								$photoplog_file_fileid_key = array_search($photoplog_file_fileid, $photoplog_album_fileids);
								unset($photoplog_album_fileids[$photoplog_file_fileid_key]);

								$photoplog_album_case1 .= "WHEN ".intval($photoplog_album_albumid)." THEN '".$db->escape_string(serialize($photoplog_album_fileids))."' ";
								$photoplog_album_case2[] = intval($photoplog_album_albumid);
							}
							unset($photoplog_album_fileids);
						}

						if (($photoplog_album_cnt % 20 == 0) && $photoplog_album_case1)
						{
							$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_useralbums
								SET fileids = CASE albumid ".$photoplog_album_case1." ELSE fileids END
								WHERE albumid IN (".implode(',', $photoplog_album_case2).")
							");

							$photoplog_album_cnt = 0;
							$photoplog_album_case1 = '';
							$photoplog_album_case2 = array();
						}
					}

					if ($photoplog_album_case1)
					{
						$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_useralbums
							SET fileids = CASE albumid ".$photoplog_album_case1." ELSE fileids END
							WHERE albumid IN (".implode(',', $photoplog_album_case2).")
						");
					}

					$db->free_result($photoplog_album_infos);
					unset($photoplog_album_case1, $photoplog_album_case2);
				}
			}
			unset($photoplog_file_albumids);

			$photoplog_user_case1 = '';
			$photoplog_user_case2 = array();

			$photoplog_commcnt_querys = $db->query_read_slave("SELECT userid, COUNT(userid) AS cnt1
				FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE fileid IN (" . $photoplog_file_sql . ")
				AND comment != ''
				GROUP BY userid
			");

			while ($photoplog_commcnt_query = $db->fetch_array($photoplog_commcnt_querys))
			{
				$photoplog_commuid = intval($photoplog_commcnt_query['userid']);
				$photoplog_commcnt = intval($photoplog_commcnt_query['cnt1']);
				if ($photoplog_commcnt > 0)
				{
					$photoplog_user_case1 .= "WHEN ".$photoplog_commuid.
						" THEN photoplog_commentcount - ".$photoplog_commcnt." ";
					$photoplog_user_case2[] = $photoplog_commuid;
				}
			}
			$db->free_result($photoplog_commcnt_querys);

			if ($photoplog_user_case1)
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "user
					SET photoplog_commentcount = CASE userid ".$photoplog_user_case1." ELSE photoplog_commentcount END
					WHERE userid IN (".implode(',', $photoplog_user_case2).")
				");
			}

			$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE fileid IN (" . $photoplog_file_sql . ")
			");

			photoplog_regenerate_counts_table_v2($photoplog_cat_ids);

			$photoplog_user_case1 = '';
			$photoplog_user_case2 = array();

			foreach ($photoplog_user_ids AS $photoplog_user_ids_userid => $photoplog_user_ids_cnt)
			{
				$photoplog_user_case1 .= "WHEN ".intval($photoplog_user_ids_userid).
					" THEN photoplog_filecount - ".intval($photoplog_user_ids_cnt)." ";
				$photoplog_user_case2[] = intval($photoplog_user_ids_userid);
			}

			if ($photoplog_user_case1)
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "user
					SET photoplog_filecount = CASE userid ".$photoplog_user_case1." ELSE photoplog_filecount END
					WHERE userid IN (".implode(',', $photoplog_user_case2).")
				");
			}

			foreach ($photoplog_file_names AS $photoplog_file_old => $photoplog_directory_name)
			{
				@unlink($photoplog_directory_name."/".$photoplog_file_old);
				@unlink($photoplog_directory_name."/large/".$photoplog_file_old);
				@unlink($photoplog_directory_name."/medium/".$photoplog_file_old);
				@unlink($photoplog_directory_name."/small/".$photoplog_file_old);
			}

			$_REQUEST['do'] = 'wipe';
		}
	}

	if ($_REQUEST['do'] == 'approve' || $_REQUEST['do'] == 'unapprove')
	{
		$photoplog_moderate = 1;
		if ($_REQUEST['do'] == 'approve')
		{
			$photoplog_moderate = 0;
		}

		if (
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				SET moderate = ".intval($photoplog_moderate)."
				WHERE fileid IN (" . $photoplog_file_sql . ")
			")
		)
		{
			photoplog_regenerate_counts_table_v2($photoplog_cat_ids);

			$_REQUEST['do'] = 'wipe';
		}
	}
}

if ($photoplog_inline_action == 'comments')
{
	$vbulletin->input->clean_array_gpc('c', array(
		'photoplog_inlinepost' => TYPE_NOHTML
	));
	$photoplog_inline_cookie = $vbulletin->GPC['photoplog_inlinepost'];

	if ($photoplog_inline_cookie)
	{
		$photoplog_inline_cookie = explode('-', eregi_replace('[^0-9-]', '', $photoplog_inline_cookie));
		$photoplog_inline_cookie = $vbulletin->input->clean($photoplog_inline_cookie, TYPE_ARRAY_UINT);
		$vbulletin->GPC['inlinecommentids'] = array_unique(array_merge($vbulletin->GPC['inlinecommentids'], $photoplog_inline_cookie));
	}

	$photoplog_comment_ids = $vbulletin->GPC['inlinecommentids'];
	$photoplog_file_id = $vbulletin->GPC['fileid'];

	$photoplog_cat_id = 0;
	$photoplog_file_check = $db->query_first_slave("SELECT catid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid = ".intval($photoplog_file_id)."
	");
	$photoplog_cat_id = intval($photoplog_file_check['catid']);
	$db->free_result($photoplog_file_check);

	$photoplog_inline_not_allowed_bits = array();
	if (!empty($photoplog_inline_bits))
	{
		foreach ($photoplog_inline_bits AS $photoplog_inline_bits_catid => $photoplog_inline_bits_opt)
		{
			$photoplog_inline_perm = convert_bits_to_array($photoplog_inline_bits_opt, $photoplog_categoryoptpermissions);
			if (!$photoplog_inline_perm['cancommentonfiles'])
			{
				$photoplog_inline_not_allowed_bits[] = intval($photoplog_inline_bits_catid);
			}
		}
	}

	if ($_REQUEST['do'] == 'edit' && (!$photoplog_cat_id || in_array($photoplog_cat_id, array_unique(array_merge($photoplog_inline_not_allowed_bits, $photoplog_perm_not_allowed_bits)))))
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_category']);
	}
	if (empty($photoplog_comment_ids) || count($photoplog_comment_ids) > $photoplog_inline_limit)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_inline']);
	}

	$photoplog_comment_infos = $db->query_read_slave("SELECT commentid, catid, fileid, userid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE commentid IN (" . implode(',', $photoplog_comment_ids) . ")
		$photoplog_catid_sql2
		$photoplog_admin_sql2
		AND comment != ''
	");

	$photoplog_inline_perm = array();
	while ($photoplog_comment_info = $db->fetch_array($photoplog_comment_infos))
	{
		$photoplog_inline_commentid = $photoplog_comment_info['commentid'];
		$photoplog_inline_catid = $photoplog_comment_info['catid'];

		$photoplog_inline_perm[$photoplog_inline_commentid]['caneditowncomments'] = 0;
		$photoplog_inline_perm[$photoplog_inline_commentid]['candeleteowncomments'] = 0;
		$photoplog_inline_perm[$photoplog_inline_commentid]['caneditothercomments'] = 0;
		$photoplog_inline_perm[$photoplog_inline_commentid]['candeleteothercomments'] = 0;

		$photoplog_inline_perm[$photoplog_inline_commentid]['catid'] = 0;
		$photoplog_inline_perm[$photoplog_inline_commentid]['fileid'] = 0;
		$photoplog_inline_perm[$photoplog_inline_commentid]['userid'] = 0;

		if (isset($photoplog_inline_bits[$photoplog_inline_catid]))
		{
			$photoplog_inline_perm[$photoplog_inline_commentid] = convert_bits_to_array($photoplog_inline_bits[$photoplog_inline_catid], $photoplog_categoryoptpermissions);

			$photoplog_inline_perm[$photoplog_inline_commentid]['catid'] = $photoplog_inline_catid;
			$photoplog_inline_perm[$photoplog_inline_commentid]['fileid'] = $photoplog_comment_info['fileid'];
			$photoplog_inline_perm[$photoplog_inline_commentid]['userid'] = $photoplog_comment_info['userid'];
		}
	}
	$db->free_result($photoplog_comment_infos);

	if (empty($photoplog_inline_perm))
	{
		photoplog_index_bounce();
	}

	$photoplog_comment_sql = array();
	$photoplog_cat_ids = array();
	$photoplog_file_ids = array();
	$photoplog_user_ids = array();
	foreach ($photoplog_inline_perm AS $photoplog_inline_perm_commentid => $photoplog_inline_perm_array)
	{
		if ($_REQUEST['do'] == 'edit' && $photoplog_file_id != $photoplog_inline_perm_array['fileid'])
		{
			if (
				($photoplog_inline_perm_array['caneditowncomments'] && $vbulletin->userinfo['userid'] == $photoplog_inline_perm_array['userid'])
					||
				($photoplog_inline_perm_array['caneditothercomments'] && $vbulletin->userinfo['userid'] != $photoplog_inline_perm_array['userid'])
			)
			{
				$photoplog_comment_sql[] = intval($photoplog_inline_perm_commentid);
				$photoplog_cat_ids[] = intval($photoplog_inline_perm_array['catid']);
				$photoplog_file_ids[] = intval($photoplog_inline_perm_array['fileid']);
			}
		}

		if ($_REQUEST['do'] == 'delete')
		{
			if (
				($photoplog_inline_perm_array['candeleteowncomments'] && $vbulletin->userinfo['userid'] == $photoplog_inline_perm_array['userid'])
					||
				($photoplog_inline_perm_array['candeleteothercomments'] && $vbulletin->userinfo['userid'] != $photoplog_inline_perm_array['userid'])
			)
			{
				$photoplog_comment_sql[] = intval($photoplog_inline_perm_commentid);
				$photoplog_cat_ids[] = intval($photoplog_inline_perm_array['catid']);
				$photoplog_file_ids[] = intval($photoplog_inline_perm_array['fileid']);

				if (isset($photoplog_user_ids[$photoplog_inline_perm_array['userid']]))
				{
					$photoplog_user_ids[$photoplog_inline_perm_array['userid']] ++;
				}
				else
				{
					$photoplog_user_ids[$photoplog_inline_perm_array['userid']] = 1;
				}
			}
		}

		if (
			($_REQUEST['do'] == 'approve' || $_REQUEST['do'] == 'unapprove') && $photoplog['canadminforums']
				&&
			(
				(
					($photoplog_inline_perm_array['caneditowncomments'] || $photoplog_inline_perm_array['candeleteowncomments'])
						&&
					$vbulletin->userinfo['userid'] == $photoplog_inline_perm_array['userid']
				)
					||
				$photoplog_inline_perm_array['caneditothercomments'] || $photoplog_inline_perm_array['candeleteothercomments']
			)
		)
		{
			$photoplog_comment_sql[] = intval($photoplog_inline_perm_commentid);
			$photoplog_cat_ids[] = intval($photoplog_inline_perm_array['catid']);
			$photoplog_file_ids[] = intval($photoplog_inline_perm_array['fileid']);
		}
	}

	$photoplog_comment_sql = array_unique($photoplog_comment_sql);
	if (empty($photoplog_comment_sql))
	{
		// if no ids then invalid request !!!
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_invalid_request']);
	}

	$photoplog_comment_sql = implode(',', $photoplog_comment_sql);
	if ($_REQUEST['do'] == 'edit')
	{
		if (
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				SET fileid = ".intval($photoplog_file_id)."
				WHERE commentid IN (" . $photoplog_comment_sql . ")
			")
		)
		{
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				SET catid = ".intval($photoplog_cat_id)."
				WHERE commentid IN (" . $photoplog_comment_sql . ")
			");

			$photoplog_cat_ids[] = intval($photoplog_cat_id);
			$photoplog_file_ids[] = intval($photoplog_file_id);

			photoplog_update_fileuploads_counts_array($photoplog_file_ids);
			photoplog_regenerate_counts_table_v2($photoplog_cat_ids);

			$_REQUEST['do'] = 'wipe';
		}
	}

	if ($_REQUEST['do'] == 'delete')
	{
		if (
			$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE commentid IN (" . $photoplog_comment_sql . ")
			")
		)
		{
			$photoplog_user_case1 = '';
			$photoplog_user_case2 = array();

			foreach ($photoplog_user_ids AS $photoplog_user_ids_userid => $photoplog_user_ids_cnt)
			{
				$photoplog_user_case1 .= "WHEN ".intval($photoplog_user_ids_userid).
					" THEN photoplog_commentcount - ".intval($photoplog_user_ids_cnt)." ";
				$photoplog_user_case2[] = intval($photoplog_user_ids_userid);
			}

			if ($photoplog_user_case1)
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "user
					SET photoplog_commentcount = CASE userid ".$photoplog_user_case1." ELSE photoplog_commentcount END
					WHERE userid IN (".implode(',', $photoplog_user_case2).")
				");
			}

			photoplog_update_fileuploads_counts_array($photoplog_file_ids);
			photoplog_regenerate_counts_table_v2($photoplog_cat_ids);

			$_REQUEST['do'] = 'wipe';
		}
	}

	if ($_REQUEST['do'] == 'approve' || $_REQUEST['do'] == 'unapprove')
	{
		$photoplog_moderate = 1;
		if ($_REQUEST['do'] == 'approve')
		{
			$photoplog_moderate = 0;
		}

		if (
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				SET moderate = ".intval($photoplog_moderate)."
				WHERE commentid IN (" . $photoplog_comment_sql . ")
			")
		)
		{
			if ($_REQUEST['do'] == 'approve')
			{
				$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					SET moderate = 0
					WHERE fileid IN (" . implode(',', $photoplog_file_ids) . ")
				");
			}

			photoplog_update_fileuploads_counts_array($photoplog_file_ids);
			photoplog_regenerate_counts_table_v2($photoplog_cat_ids);

			$_REQUEST['do'] = 'wipe';
		}
	}
}

if ($_REQUEST['do'] == 'wipe')
{
	if ($photoplog_inline_action == 'files')
	{
		if ($vbulletin->options['photoplog_display_type'] == 1)
		{
			setcookie('photoplog_inlinepost', '', TIMENOW - 3600, '/');
		}
		else
		{
			setcookie('photoplog_inlinethread', '', TIMENOW - 3600, '/');
		}
	}
	if ($photoplog_inline_action == 'comments')
	{
		setcookie('photoplog_inlinepost', '', TIMENOW - 3600, '/');
	}
}

if ($vbulletin->url && !eregi('inline\.php', $vbulletin->url))
{
	exec_header_redirect($vbulletin->url);
}
else
{
	$photoplog_url = $photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q'];
	exec_header_redirect($photoplog_url);
}

?>