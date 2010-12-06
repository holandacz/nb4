<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','delete');
define('PHOTOPLOG_LEVEL','delete');
require_once('./settings.php');

// ########################### Start Delete Page ##########################
($hook = vBulletinHook::fetch_hook('photoplog_delete_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'delete';
}

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'n' => TYPE_UINT
	));

	$photoplog_file_id = $vbulletin->GPC['n'];

	$photoplog_file_info = '';
	if (!$photoplog_file_info_links && $photoplog_file_id)
	{
		$photoplog_file_info = $db->query_first("SELECT userid,fileid,filename,title
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

	if ($photoplog_file_info)
	{
		if (($photoplog_file_info['userid'] == $vbulletin->userinfo['userid']) || defined('PHOTOPLOG_USER6'))
		{
			$photoplog['fileid'] = $photoplog_file_info['fileid'];
			$photoplog['userid'] = $photoplog_file_info['userid'];
			$photoplog['filename'] = $photoplog_file_info['filename'];
			$photoplog['title'] = htmlspecialchars_uni($photoplog_file_info['title']);

			photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);

			$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], 0, 1).'link';
			$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], -1, 1).'link';

			$photoplog['do_highslide'] = 0;
			if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
			{
				$photoplog['do_highslide'] = 1;
			}

			$photoplog['hslink1'] = $photoplog['file_slink'];
			$photoplog['hslink2'] = $photoplog['file_llink'];
			if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
			{
				$photoplog['hslink1'] = $photoplog[$photoplog_hslink1];
				$photoplog['hslink2'] = $photoplog[$photoplog_hslink2];
			}

			($hook = vBulletinHook::fetch_hook('photoplog_delete_form')) ? eval($hook) : false;

			photoplog_output_page('photoplog_delete_form', $vbphrase['photoplog_delete_file']);
		}
		else
		{
			photoplog_index_bounce();
		}
	}
	else
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_tank']);
	}
}

if ($_POST['do'] == 'dodelete')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'fileid' => TYPE_UINT
	));

	($hook = vBulletinHook::fetch_hook('photoplog_delete_dodelete_start')) ? eval($hook) : false;

	$photoplog_file_id = $vbulletin->GPC['fileid'];

	$photoplog_file_info = $db->query_first("SELECT userid,filename,catid,albumids,fileid
						FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
						WHERE fileid = ".intval($photoplog_file_id)."
						$photoplog_catid_sql1
						$photoplog_admin_sql1
	");

	if ($photoplog_file_info)
	{
		if (($photoplog_file_info['userid'] != $vbulletin->userinfo['userid']) && !defined('PHOTOPLOG_USER6'))
		{
			photoplog_index_bounce();
		}
		else
		{
			$photoplog_file_userid = $photoplog_file_info['userid'];
			$photoplog_directory_name = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_userid;
			$photoplog_file_old = $photoplog_file_info['filename'];
			$photoplog_file_info_catid = intval($photoplog_file_info['catid']);

			$photoplog_file_albumids = unserialize($photoplog_file_info['albumids']);
			$photoplog_file_fileid = intval($photoplog_file_info['fileid']);

			if (
				$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
							WHERE fileid = ".intval($photoplog_file_id)."
							$photoplog_catid_sql1
							$photoplog_admin_sql1
				")
			)
			{
				if (is_array($photoplog_file_albumids) && count($photoplog_file_albumids) > 0)
				{
					$photoplog_album_infos = $db->query_read("SELECT albumid, fileids
						FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
						WHERE albumid IN (".implode(',', $photoplog_file_albumids).")
					");
					unset($photoplog_file_albumids);

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

				$photoplog_user_case1 = '';
				$photoplog_user_case2 = array();

				$photoplog_commcnt_querys = $db->query_read("SELECT userid, COUNT(userid) AS cnt1
					FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
					WHERE fileid = ".intval($photoplog_file_id)."
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
							WHERE fileid = ".intval($photoplog_file_id)."
				");
				photoplog_update_counts_table($photoplog_file_info_catid);
				$db->query_write("UPDATE " . TABLE_PREFIX . "user
							SET photoplog_filecount = photoplog_filecount - 1
							WHERE userid = ".intval($photoplog_file_userid)."
				");

				@unlink($photoplog_directory_name."/".$photoplog_file_old);
				@unlink($photoplog_directory_name."/large/".$photoplog_file_old);
				@unlink($photoplog_directory_name."/medium/".$photoplog_file_old);
				@unlink($photoplog_directory_name."/small/".$photoplog_file_old);
			}

			($hook = vBulletinHook::fetch_hook('photoplog_delete_dodelete_complete')) ? eval($hook) : false;

			photoplog_index_bounce();
		}
	}
	else
	{
		photoplog_index_bounce();
	}
}

($hook = vBulletinHook::fetch_hook('photoplog_delete_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'delete' && $_POST['do'] != 'dodelete')
{
	photoplog_index_bounce();
}

?>