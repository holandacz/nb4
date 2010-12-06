<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

@ignore_user_abort(true);
if (@ini_get('safe_mode') != 1)
{
	@set_time_limit(0);
	@ini_set('max_execution_time',0);
}
@ini_set('memory_limit','128M');
@ini_set('post_max_size','30M');
@ini_set('upload_max_filesize','30M');
@ini_set('magic_quotes_runtime',false);
@ini_set('magic_quotes_sybase',false);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('photoplog');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR.'/includes/photoplog_prefix.php');
require_once(DIR.'/'.$vbulletin->config['Misc']['admincpdir'].'/photoplog_functions.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminforums'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

$photoplog_fullpath = $vbulletin->options['photoplog_full_path'].'/'.$vbulletin->options['photoplog_upload_dir'];

print_cp_header($vbphrase['photoplog_maintenance']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'update';
}

$vbulletin->input->clean_array_gpc('r', array(
	'perpage' => TYPE_UINT,
	'start' => TYPE_UINT,
	'size' => TYPE_UINT,
	'phase' => TYPE_UINT
));

$photoplog_thumb_sizes = array(
	1 => $vbphrase['photoplog_small'],
	2 => $vbphrase['photoplog_medium'],
	3 => $vbphrase['photoplog_large']
);

if ($_REQUEST['do'] == 'update')
{
	print_form_header('index', 'buildbitfields');
	print_table_header($vbphrase['photoplog_rebuild_bitfields']);
	print_description_row($vbphrase['photoplog_this_will_rebuild_bitfields']);
	print_submit_row($vbphrase['photoplog_update']);

	print_form_header('photoplog_maintain', 'dimensions');
	print_table_header($vbphrase['photoplog_update_dimensions']);
	print_description_row($vbphrase['photoplog_this_will_update_dimensions']);
	print_submit_row($vbphrase['photoplog_update']);

	print_form_header('photoplog_maintain', 'ratings');
	print_table_header($vbphrase['photoplog_update_ratings']);
	print_description_row($vbphrase['photoplog_this_will_update_ratings']);
	print_submit_row($vbphrase['photoplog_update']);

	print_form_header('photoplog_maintain', 'rebuildthumbs');
	construct_hidden_code('start','0');
	print_table_header($vbphrase['photoplog_rebuild_thumbnails'], 2, 0);
	print_select_row($vbphrase['photoplog_thumbnail_size'], 'size', $photoplog_thumb_sizes, 1, true, 0, false);
	print_input_row($vbphrase['photoplog_max_number_to_process_per_cycle'], 'perpage', 50);
	print_submit_row($vbphrase['photoplog_update']);

	print_form_header('photoplog_maintain', 'postbitcounts');
	construct_hidden_code('start','0');
	print_table_header($vbphrase['photoplog_update_postbit_file_and_comment_counts'], 2, 0);
	print_input_row($vbphrase['photoplog_max_number_to_process_per_cycle'], 'perpage', 50);
	print_submit_row($vbphrase['photoplog_update']);

	print_form_header('photoplog_maintain', 'counts');
	construct_hidden_code('start','0');
	print_table_header($vbphrase['photoplog_update_gallery_file_and_category_counts'], 2, 0);
	print_input_row($vbphrase['photoplog_max_number_to_process_per_cycle'], 'perpage', 50);
	print_submit_row($vbphrase['photoplog_update']);

	print_form_header('photoplog_maintain', 'sync');
	construct_hidden_code('start','0');
	print_table_header($vbphrase['photoplog_sync_files_and_albums'], 2, 0);
	print_input_row($vbphrase['photoplog_max_number_to_process_per_cycle'], 'perpage', 25);
	print_submit_row($vbphrase['photoplog_update']);

	print_form_header('photoplog_maintain', 'datastore');
	print_table_header($vbphrase['photoplog_rebuild_category_datastore']);
	print_description_row($vbphrase['photoplog_this_will_rebuild_category_datastore']);
	print_submit_row($vbphrase['photoplog_update']);

	print_form_header('photoplog_maintain', 'exif');
	construct_hidden_code('start','0');
	print_table_header($vbphrase['photoplog_update_exif_information'], 2, 0);
	print_input_row($vbphrase['photoplog_max_number_to_process_per_cycle'], 'perpage', 25);
	print_submit_row($vbphrase['photoplog_update']);
}

if ($_POST['do'] == 'dimensions')
{
	print_table_start();
	print_table_header($vbphrase['photoplog_maintenance'], 1);

	print_cells_row(array('<nobr>' . $vbphrase['photoplog_dimensions'] . '</nobr>'), 1, '', -1);

	$photoplog_file_dimensions = $db->query_read("SELECT fileid, userid, filename
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE dimensions = '975313579 x 135797531'
		ORDER BY fileid ASC
	");

	if ($photoplog_file_dimensions && $db->num_rows($photoplog_file_dimensions))
	{
		while ($photoplog_file_dimension = $db->fetch_array($photoplog_file_dimensions))
		{
			$photoplog_fileid = $photoplog_file_dimension['fileid'];
			$photoplog_userid = $photoplog_file_dimension['userid'];
			$photoplog_filename = $photoplog_file_dimension['filename'];
			$photoplog_file = $photoplog_fullpath.'/'.$photoplog_userid.'/'.$photoplog_filename;

			$photoplog_getimagesize = @getimagesize($photoplog_file);

			if (is_array($photoplog_getimagesize) && $photoplog_getimagesize[0] && $photoplog_getimagesize[1])
			{
				$photoplog_w = $photoplog_getimagesize[0];
				$photoplog_h = $photoplog_getimagesize[1];
				$photoplog_wxh = $photoplog_w . ' x ' . $photoplog_h;

				$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					SET dimensions = '".$db->escape_string($photoplog_wxh)."'
					WHERE fileid = ".intval($photoplog_fileid)."
				");

				$photoplog_msg = $vbphrase['photoplog_updating'].' - '.
							$vbphrase['photoplog_file'].' '.$vbphrase['photoplog_id'].' '.
							$photoplog_fileid.': '.$photoplog_wxh;
				print_description_row($photoplog_msg, 0, 1);

				@flush();
				@ob_flush();
			}
		}

		$db->free_result($photoplog_file_dimensions);
		print_description_row('<strong>'.$vbphrase['photoplog_done'].'</strong>', 0, 1);
		print_table_footer();
		print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=update", 1);
	}
	else
	{
		print_description_row($vbphrase['photoplog_nothing_to_maintain'], 0, 1);
		print_table_footer();
	}
}

if ($_POST['do'] == 'ratings')
{
	print_table_start();
	print_table_header($vbphrase['photoplog_maintenance'], 1);

	print_cells_row(array('<nobr>' . $vbphrase['photoplog_ratings'] . '</nobr>'), 1, '', -1);

	if ($vbulletin->options['photoplog_rate_once'])
	{
		$photoplog_rating_infos = $db->query_read("SELECT userid, fileid,
			SUM(IF(rating > 0, 1, 0)) AS cntr1
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			GROUP BY userid, fileid
		");

		$photoplog_rating_cntr = 0;
		while ($photoplog_rating_info = $db->fetch_array($photoplog_rating_infos))
		{
			if ($photoplog_rating_info['cntr1'] > 1)
			{
				$photoplog_rating_cntr++;
				break;
			}
		}

		if ($photoplog_rating_infos && $photoplog_rating_cntr)
		{
			$db->data_seek($photoplog_rating_infos, 0);

			while ($photoplog_rating_info = $db->fetch_array($photoplog_rating_infos))
			{
				if ($photoplog_rating_info['cntr1'] > 1)
				{
					$photoplog_first_comment = $db->query_first("SELECT commentid, catid
						FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
						WHERE fileid = ".intval($photoplog_rating_info['fileid'])."
						AND userid = ".intval($photoplog_rating_info['userid'])."
						AND rating > 0
						ORDER BY dateline ASC
						LIMIT 1
					");

					if ($photoplog_first_comment)
					{
						$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
							SET rating = 0
							WHERE fileid = ".intval($photoplog_rating_info['fileid'])."
							AND userid = ".intval($photoplog_rating_info['userid'])."
							AND commentid != ".intval($photoplog_first_comment['commentid'])."
						");
						photoplog_update_fileuploads_counts($photoplog_rating_info['fileid']);
						photoplog_update_counts_table($photoplog_first_comment['catid']);

						$photoplog_commentid = $photoplog_first_comment['commentid'];

						$photoplog_msg = $vbphrase['photoplog_updating'].' - '.
									$vbphrase['photoplog_comment'].' '.$vbphrase['photoplog_id'].' '.
									$photoplog_commentid.': '.$vbphrase['photoplog_rating'].' != 0 ';
						print_description_row($photoplog_msg, 0, 1);

						@flush();
						@ob_flush();

						$db->free_result($photoplog_first_comment);
					}
				}
			}

			$db->free_result($photoplog_rating_infos);
			print_description_row('<strong>'.$vbphrase['photoplog_done'].'</strong>', 0, 1);
			print_table_footer();
			print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=update", 1);
		}
		else
		{
			print_description_row($vbphrase['photoplog_nothing_to_maintain'], 0, 1);
			print_table_footer();
		}
	}
	else
	{
		print_description_row($vbphrase['photoplog_nothing_to_maintain'], 0, 1);
		print_table_footer();
	}
}

if ($_REQUEST['do'] == 'rebuildthumbs')
{
	$photoplog_start = intval($vbulletin->GPC['start']);
	$photoplog_perpage = intval($vbulletin->GPC['perpage']);
	if (!$photoplog_perpage)
	{
		$photoplog_perpage = 50;
	}
	$photoplog_stop = intval($photoplog_start + $photoplog_perpage);
	$photoplog_size = intval($vbulletin->GPC['size']);

	print_table_start();
	print_table_header($vbphrase['photoplog_maintenance'], 1);

	print_cells_row(array('<nobr>' . $vbphrase['photoplog_thumbnails'] . '</nobr>'), 1, '', -1);

	if (!in_array($photoplog_size, array(1,2,3)))
	{
		print_description_row($vbphrase['photoplog_nothing_to_maintain'], 0, 1);
		print_table_footer();
		print_cp_footer();
	}

	switch ($photoplog_size)
	{
		case 1:
			// small
			$photoplog_directory_size = 'small';
			$photoplog_directory_dims = $vbulletin->options['photoplog_small_size'];
			break;
		case 2:
			// medium
			$photoplog_directory_size = 'medium';
			$photoplog_directory_dims = $vbulletin->options['photoplog_medium_size'];
			break;
		case 3:
			// large
			$photoplog_directory_size = 'large';
			$photoplog_directory_dims = $vbulletin->options['photoplog_large_size'];
			break;
		default:
			// small
			$photoplog_directory_size = 'small';
			$photoplog_directory_dims = $vbulletin->options['photoplog_small_size'];
	}

	$photoplog_file_infos = $db->query_read("SELECT fileid, userid, filename
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid >= ".intval($photoplog_start)."
		AND fileid < ".intval($photoplog_stop)."
		ORDER BY fileid ASC
	");

	while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
	{
		$photoplog_fileid = $photoplog_file_info['fileid'];
		$photoplog_userid = $photoplog_file_info['userid'];
		$photoplog_file_name = $photoplog_file_info['filename'];
		$photoplog_file_location = $photoplog_fullpath.'/'.$photoplog_userid.'/'.$photoplog_file_name;

		$photoplog_file_check = @getimagesize($photoplog_file_location);
		$photoplog_directory_name = $photoplog_fullpath.'/'.$photoplog_userid;

		$photoplog_thumb_result = photoplog_rebuild_thumbs(
			$photoplog_file_check, $photoplog_directory_name, $photoplog_file_name, $photoplog_directory_dims,
			$vbulletin->options['photoplog_jpg_quality'], $photoplog_directory_size, true
		);

		if ($photoplog_thumb_result)
		{
			$photoplog_getimagesize = @getimagesize($photoplog_directory_name.'/'.$photoplog_directory_size.'/'.$photoplog_file_name);

			if (is_array($photoplog_getimagesize) && $photoplog_getimagesize[0] && $photoplog_getimagesize[1])
			{
				$photoplog_w = $photoplog_getimagesize[0];
				$photoplog_h = $photoplog_getimagesize[1];
				$photoplog_wxh = $photoplog_w . ' x ' . $photoplog_h;

				$photoplog_msg = $vbphrase['photoplog_updating'].' - '.
							$vbphrase['photoplog_file'].' '.$vbphrase['photoplog_id'].' '.
							$photoplog_fileid.': '.$photoplog_wxh;
				print_description_row($photoplog_msg, 0, 1);

				@flush();
				@ob_flush();
			}
		}
	}

	$db->free_result($photoplog_file_infos);

	if (
		$photoplog_morecheck = $db->query_first("SELECT fileid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid >= ".intval($photoplog_stop)."
			ORDER BY fileid ASC
			LIMIT 1")
	)
	{
		print_table_footer();
		$photoplog_stop = intval($photoplog_morecheck['fileid']);
		print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=rebuildthumbs&start=".$photoplog_stop."&perpage=".$photoplog_perpage."&size=".$photoplog_size, 1);
	}
	else
	{
		print_description_row('<strong>'.$vbphrase['photoplog_done'].'</strong>', 0, 1);
		print_table_footer();
		print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=update", 1);
	}
}

if ($_REQUEST['do'] == 'postbitcounts')
{
	$photoplog_start = intval($vbulletin->GPC['start']);
	$photoplog_perpage = intval($vbulletin->GPC['perpage']);
	$photoplog_phase = intval($vbulletin->GPC['phase']);
	if (!$photoplog_perpage)
	{
		$photoplog_perpage = 50;
	}
	photoplog_maintain_postbitcounts($photoplog_start, $photoplog_perpage, $photoplog_phase, $vbphrase['photoplog_maintenance'], 'photoplog_maintain.php');
	if ($_REQUEST['massmove'] == 1)
	{
		print_cp_redirect("photoplog_massmove.php?".$vbulletin->session->vars['sessionurl']."do=view", 1);
	}
	else
	{
		print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=update", 1);
	}
}

if ($_REQUEST['do'] == 'counts')
{
	$photoplog_start = intval($vbulletin->GPC['start']);
	$photoplog_perpage = intval($vbulletin->GPC['perpage']);
	$photoplog_phase = intval($vbulletin->GPC['phase']);
	if (!$photoplog_perpage)
	{
		$photoplog_perpage = 50;
	}
	photoplog_maintain_counts($photoplog_start, $photoplog_perpage, $photoplog_phase, $vbphrase['photoplog_maintenance'], 'photoplog_maintain.php');
	print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=update", 1);
}

if ($_REQUEST['do'] == 'sync')
{
	$photoplog_start = intval($vbulletin->GPC['start']);
	$photoplog_perpage = intval($vbulletin->GPC['perpage']);
	$photoplog_phase = intval($vbulletin->GPC['phase']);
	if (!$photoplog_perpage)
	{
		$photoplog_perpage = 25;
	}
	$photoplog_stop = intval($photoplog_start + $photoplog_perpage);

	print_table_start();
	print_table_header($vbphrase['photoplog_maintenance'], 1);

	print_cells_row(array('<nobr>' . $vbphrase['photoplog_sync_files_and_albums'] . '</nobr>'), 1, '', -1);

	if ($photoplog_phase == 0)
	{
		$photoplog_msg = $vbphrase['photoplog_updating'].' photoplog_useralbums: '.
								$photoplog_start.' - '.$photoplog_stop;

		print_description_row($photoplog_msg, 0, 1);

		@flush();
		@ob_flush();

		$photoplog_fileids_by_album = array();
		$photoplog_fileids_in_albums = array();
		$photoplog_fileids_in_albums[] = -999;

		$photoplog_album_infos = $db->query_read("SELECT albumid, fileids
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE albumid >= ".intval($photoplog_start)."
			AND albumid < ".intval($photoplog_stop)."
			ORDER BY albumid ASC
		");

		while ($photoplog_album_info = $db->fetch_array($photoplog_album_infos))
		{
			$photoplog_album_info_albumid = intval($photoplog_album_info['albumid']);
			$photoplog_album_info_fileids = unserialize($photoplog_album_info['fileids']);
			if (is_array($photoplog_album_info_fileids))
			{
				$photoplog_fileids_by_album[$photoplog_album_info_albumid] = $photoplog_album_info_fileids;
				foreach($photoplog_album_info_fileids AS $photoplog_album_info_fileids_fileid)
				{
					if (!in_array($photoplog_album_info_fileids_fileid,$photoplog_fileids_in_albums))
					{
						$photoplog_fileids_in_albums[] = intval($photoplog_album_info_fileids_fileid);
					}
				}
			}
			else
			{
				$photoplog_fileids_by_album[$photoplog_album_info_albumid] = array();
			}
		}

		$db->free_result($photoplog_album_infos);

		$photoplog_file_infos = $db->query_read("SELECT fileid, albumids
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid IN (".implode(',',$photoplog_fileids_in_albums).")
			ORDER BY fileid ASC
		");

		$photoplog_fileid_arr = array();
		$photoplog_filealbum_map = array();

		while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
		{
			$photoplog_fileid = intval($photoplog_file_info['fileid']);
			$photoplog_fileid_arr[] = $photoplog_fileid;

			$photoplog_filealbum_map[$photoplog_fileid] = unserialize($photoplog_file_info['albumids']);
			if (!is_array($photoplog_filealbum_map[$photoplog_fileid]))
			{
				$photoplog_filealbum_map[$photoplog_fileid] = array();
			}
		}

		$db->free_result($photoplog_file_infos);

		$photoplog_album_cnt = 0;
		$photoplog_album_case1 = '';
		$photoplog_album_case2 = array();
		$photoplog_file_albumids_changed = array();

		foreach($photoplog_fileids_by_album AS $photoplog_album_info_albumid => $photoplog_album_info_fileids)
		{
			if (is_array($photoplog_album_info_fileids) && count($photoplog_album_info_fileids) > 0)
			{
				$photoplog_album_fileids = array_intersect($photoplog_album_info_fileids, $photoplog_fileid_arr);

				if (count($photoplog_album_fileids) != count($photoplog_album_info_fileids))
				{
					$photoplog_album_cnt ++;

					$photoplog_album_case1 .= "WHEN ".intval($photoplog_album_info_albumid)." THEN '".$db->escape_string(serialize($photoplog_album_fileids))."' ";
					$photoplog_album_case2[] = intval($photoplog_album_info_albumid);
				}

				foreach ($photoplog_album_fileids AS $photoplog_fileid)
				{
					if (!in_array($photoplog_album_info_albumid, $photoplog_filealbum_map[$photoplog_fileid]))
					{
						$photoplog_filealbum_map[$photoplog_fileid][] = $photoplog_album_info_albumid;
						$photoplog_file_albumids_changed[] = $photoplog_fileid;
					}
				}

				unset($photoplog_album_fileids);

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

		/* update the albumids */
		$photoplog_file_cnt = 0;
		$photoplog_file_case1 = '';
		$photoplog_file_case2 = array();
		$photoplog_file_albumids_changed = array_unique($photoplog_file_albumids_changed);

		foreach($photoplog_file_albumids_changed AS $photoplog_fileid)
		{
			$photoplog_albumids_changed = $photoplog_filealbum_map[$photoplog_fileid];
			$photoplog_file_cnt ++;

			$photoplog_file_case1 .= "WHEN ".intval($photoplog_fileid)." THEN '".$db->escape_string(serialize($photoplog_filealbum_map[$photoplog_fileid]))."' ";
			$photoplog_file_case2[] = intval($photoplog_fileid);

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

		unset($photoplog_fileid_arr, $photoplog_filealbum_map, $photoplog_fileids_by_album);
		unset($photoplog_file_case1, $photoplog_file_case2, $photoplog_file_albumids_changed);
	}
	else
	{
		$photoplog_msg = $vbphrase['photoplog_updating'].' photoplog_fileuploads: '.
								$photoplog_start.' - '.$photoplog_stop;

		print_description_row($photoplog_msg, 0, 1);

		@flush();
		@ob_flush();

		$photoplog_albumids_by_file = array();
		$photoplog_albumids_in_files = array();
		$photoplog_albumids_in_files[] = -999;

		$photoplog_file_infos = $db->query_read("SELECT fileid, albumids
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid >= ".intval($photoplog_start)."
			AND fileid < ".intval($photoplog_stop)."
			ORDER BY fileid ASC
		");

		while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
		{
			$photoplog_file_info_fileid = intval($photoplog_file_info['fileid']);
			$photoplog_file_info_albumids = unserialize($photoplog_file_info['albumids']);
			if (is_array($photoplog_file_info_albumids))
			{
				$photoplog_albumids_by_file[$photoplog_file_info_fileid] = $photoplog_file_info_albumids;
				foreach($photoplog_file_info_albumids AS $photoplog_file_info_albumids_albumid)
				{
					if (!in_array($photoplog_file_info_albumids_albumid,$photoplog_albumids_in_files))
					{
						$photoplog_albumids_in_files[] = intval($photoplog_file_info_albumids_albumid);
					}
				}
			}
			else
			{
				$photoplog_albumids_by_file[$photoplog_file_info_fileid] = array();
			}
		}

		$db->free_result($photoplog_file_infos);

		$photoplog_album_infos = $db->query_read("SELECT albumid, fileids
			FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
			WHERE albumid IN (".implode(',',$photoplog_albumids_in_files).")
			ORDER BY albumid ASC
		");

		$photoplog_albumid_arr = array();
		$photoplog_albumfile_map = array();

		while ($photoplog_album_info = $db->fetch_array($photoplog_album_infos))
		{
			$photoplog_albumid = intval($photoplog_album_info['albumid']);
			$photoplog_albumid_arr[] = $photoplog_albumid;

			$photoplog_albumfile_map[$photoplog_albumid] = unserialize($photoplog_album_info['fileids']);
			if (!is_array($photoplog_albumfile_map[$photoplog_albumid]))
			{
				$photoplog_albumfile_map[$photoplog_albumid] = array();
			}
		}

		$db->free_result($photoplog_album_infos);

		$photoplog_file_infos = $db->query_read("SELECT fileid, albumids
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid >= ".intval($photoplog_start)."
			AND fileid < ".intval($photoplog_stop)."
			ORDER BY fileid ASC
		");

		$photoplog_file_cnt = 0;
		$photoplog_file_case1 = '';
		$photoplog_file_case2 = array();
		$photoplog_album_fileids_changed = array();

		foreach($photoplog_albumids_by_file AS $photoplog_file_info_fileid => $photoplog_file_info_albumids)
		{
			if (is_array($photoplog_file_info_albumids) && count($photoplog_file_info_albumids) > 0)
			{
				$photoplog_file_albumids = array_intersect($photoplog_file_info_albumids, $photoplog_albumid_arr);

				if (count($photoplog_file_albumids) != count($photoplog_file_info_albumids))
				{
					$photoplog_file_cnt ++;

					$photoplog_file_case1 .= "WHEN ".intval($photoplog_file_info_fileid)." THEN '".$vbulletin->db->escape_string(serialize($photoplog_file_albumids))."' ";
					$photoplog_file_case2[] = intval($photoplog_file_info_fileid);
				}

				foreach ($photoplog_file_albumids AS $photoplog_albumid)
				{
					if (!in_array($photoplog_file_info_fileid, $photoplog_albumfile_map[$photoplog_albumid]))
					{
						$photoplog_albumfile_map[$photoplog_albumid][] = $photoplog_file_info_fileid;
						$photoplog_album_fileids_changed[] = $photoplog_albumid;
					}
				}

				unset($photoplog_file_albumids);

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
		}

		if ($photoplog_file_case1)
		{
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				SET albumids = CASE fileid ".$photoplog_file_case1." ELSE albumids END
				WHERE fileid IN (".implode(',', $photoplog_file_case2).")
			");
		}

		$db->free_result($photoplog_file_infos);
		
		/* update the fileids */
		$photoplog_album_cnt = 0;
		$photoplog_album_case1 = '';
		$photoplog_album_case2 = array();
		$photoplog_album_fileids_changed = array_unique($photoplog_album_fileids_changed);

		foreach($photoplog_album_fileids_changed AS $photoplog_albumid)
		{
			$photoplog_fileids_changed = $photoplog_albumfile_map[$photoplog_albumid];
			$photoplog_album_cnt ++;

			$photoplog_album_case1 .= "WHEN ".intval($photoplog_albumid)." THEN '".$db->escape_string(serialize($photoplog_albumfile_map[$photoplog_albumid]))."' ";
			$photoplog_album_case2[] = intval($photoplog_albumid);

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
		
		unset($photoplog_albumid_arr, $photoplog_albumfile_map,$photoplog_albumids_by_file);
		unset($photoplog_file_case1, $photoplog_file_case2, $photoplog_album_fileids_changed, $photoplog_album_case1, $photoplog_album_case2);
	}

	if ($photoplog_phase == 0)
	{
		print_table_footer();

		if (
			$photoplog_morecheck = $db->query_first("SELECT albumid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
				WHERE albumid >= ".intval($photoplog_stop)."
				ORDER BY albumid ASC
				LIMIT 1")
		)
		{
			$photoplog_stop = intval($photoplog_morecheck['albumid']);
			print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=sync&phase=0&start=".$photoplog_stop."&perpage=".$photoplog_perpage, 1);
		}
		else
		{
			print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=sync&phase=1&start=0&perpage=".$photoplog_perpage, 1);
		}
	}
	else
	{
		if (
			$photoplog_morecheck = $db->query_first("SELECT fileid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				WHERE fileid >= ".intval($photoplog_stop)."
				ORDER BY fileid ASC
				LIMIT 1")
		)
		{
			print_table_footer();
			$photoplog_stop = intval($photoplog_morecheck['fileid']);
			print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=sync&phase=1&start=".$photoplog_stop."&perpage=".$photoplog_perpage, 1);
		}
		else
		{
			print_description_row('<strong>'.$vbphrase['photoplog_done'].'</strong>', 0, 1);
			print_table_footer();
			print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=update", 1);
		}
	}
}

if ($_POST['do'] == 'datastore')
{
	print_table_start();
	print_table_header($vbphrase['photoplog_maintenance'], 1);

	print_cells_row(array('<nobr>' . $vbphrase['photoplog_rebuild_category_datastore'] . '</nobr>'), 1, '', -1);

	$photoplog_cat_infos = $db->query_read("SELECT *
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
		ORDER BY catid ASC
	");

	$photoplog_dscatopts = array();

	while ($photoplog_cat_info = $db->fetch_array($photoplog_cat_infos))
	{
		$photoplog_catid = intval($photoplog_cat_info['catid']);

		$photoplog_dscatopts[$photoplog_catid]['title'] = strval($photoplog_cat_info['title']);
		$photoplog_dscatopts[$photoplog_catid]['description'] = strval($photoplog_cat_info['description']);
		$photoplog_dscatopts[$photoplog_catid]['displayorder'] = intval($photoplog_cat_info['displayorder']);
		$photoplog_dscatopts[$photoplog_catid]['parentid'] = intval($photoplog_cat_info['parentid']);
		$photoplog_dscatopts[$photoplog_catid]['options'] = intval($photoplog_cat_info['options']);

		$photoplog_msg = $vbphrase['photoplog_updating'].' - '.$vbphrase['photoplog_category'].': '.$photoplog_catid;

		print_description_row($photoplog_msg, 0, 1);

		@flush();
		@ob_flush();
	}

	$db->free_result($photoplog_cat_infos);

	build_datastore('photoplog_dscat', serialize($photoplog_dscatopts));

	unset($photoplog_dscatopts);

	print_description_row('<strong>'.$vbphrase['photoplog_done'].'</strong>', 0, 1);
	print_table_footer();
	print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=update", 1);
}

if ($_REQUEST['do'] == 'exif')
{
	$photoplog_start = intval($vbulletin->GPC['start']);
	$photoplog_perpage = intval($vbulletin->GPC['perpage']);
	if (!$photoplog_perpage)
	{
		$photoplog_perpage = 25;
	}
	$photoplog_stop = intval($photoplog_start + $photoplog_perpage);

	print_table_start();
	print_table_header($vbphrase['photoplog_maintenance'], 1);

	print_cells_row(array('<nobr>' . $vbphrase['photoplog_update_exif_information'] . '</nobr>'), 1, '', -1);

	$photoplog_exif_flag = 0;
	if ($vbulletin->options['photoplog_exifinfo_active'] && $vbulletin->options['photoplog_jhead_path'])
	{
		if (is_file($vbulletin->options['photoplog_jhead_path']))
		{
			$photoplog_exif_flag = 1;
		}
	}

	if (!$photoplog_exif_flag)
	{
		print_description_row($vbphrase['photoplog_nothing_to_maintain'], 0, 1);
		print_table_footer();
		print_cp_footer();
	}

	$photoplog_exif_labels1 = array(
		'1'=>'file_name','2'=>'file_size','4'=>'file_date','8'=>'camera_make',
		'16'=>'camera_model','32'=>'date_time','64'=>'resolution','128'=>'orientation',
		'256'=>'color_bw','512'=>'flash_used','1024'=>'focal_length',
		'2048'=>'digital_zoom','4096'=>'ccd_width','8192'=>'exposure_time'
	);

	$photoplog_exif_labels2 = array(
		'16384'=>'aperture','32768'=>'focus_dist','65536'=>'iso_equiv','131072'=>'exposure_bias',
		'262144'=>'whitebalance','524288'=>'light_source','1048576'=>'metering_mode','2097152'=>'exposure',
		'4194304'=>'exposure_mode','8388608'=>'jpeg_process','16777216'=>'gps_latitude',
		'33554432'=>'gps_longitude','67108864'=>'gps_altitude','134217728'=>'comment'
	);

	$photoplog_exif_desc1 = array(
		'bytes','Black and white','Strobe light not detected','Strobe light detected',
		'Manual','manual, return light not detected','manual, return light  detected',
		'35mm equivalent','Infinite','Auto','Daylight','Fluorescent','Incandescent','Flash',
		'Fine weather','Shade','center weight','spot','matrix','program (auto)',
		'aperture priority (semi-auto)','shutter priority (semi-auto)',
		'Creative Program (based towards depth of field)',
		'Action program (based towards fast shutter speed)',
		'Portrait Mode','LandscapeMode','Auto bracketing','Unknown','Yes','No'
	);

	$photoplog_exif_desc2 = array(
		$vbphrase['photoplog_bytes'],$vbphrase['photoplog_black_and_white'],$vbphrase['photoplog_strobe_light_not_detected'],$vbphrase['photoplog_strobe_light_detected'],
		$vbphrase['photoplog_manual'],$vbphrase['photoplog_manual_return_light_not_detected'],$vbphrase['photoplog_manual_return_light_detected'],
		$vbphrase['photoplog_35mm_equivalent'],$vbphrase['photoplog_infinite'],$vbphrase['photoplog_auto'],$vbphrase['photoplog_daylight'],$vbphrase['photoplog_fluorescent'],$vbphrase['photoplog_incandescent'],$vbphrase['photoplog_flash'],
		$vbphrase['photoplog_fine_weather'],$vbphrase['photoplog_shade'],$vbphrase['photoplog_center_weight'],$vbphrase['photoplog_spot'],$vbphrase['photoplog_matrix'],$vbphrase['photoplog_program_auto'],
		$vbphrase['photoplog_aperture_priority_semi_auto'],$vbphrase['photoplog_shutter_priority_semi_auto'],
		$vbphrase['photoplog_creative_program_based_towards_depth_of_field'],
		$vbphrase['photoplog_action_program_based_towards_fast_shutter_speed'],
		$vbphrase['photoplog_portrait_mode'],$vbphrase['photoplog_landscapemode'],$vbphrase['photoplog_auto_bracketing'],$vbphrase['photoplog_unknown'],$vbphrase['photoplog_yes'],$vbphrase['photoplog_no']
	);

	function photoplog_preg_quote2($item)
	{
		$item = '/(^|\b)' . preg_quote($item, '/') . '($|\b)/iU';
		return $item;
	}

	$photoplog_exif_desc1 = array_map('photoplog_preg_quote2', $photoplog_exif_desc1);

	$photoplog_file_infos = $db->query_read("SELECT fileid, userid, filename
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid >= ".intval($photoplog_start)."
		AND fileid < ".intval($photoplog_stop)."
		AND (filename LIKE '%.jpeg' OR filename LIKE '%.jpg')
		ORDER BY fileid ASC
	");

	while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
	{
		$photoplog_fileid = $photoplog_file_info['fileid'];
		$photoplog_userid = $photoplog_file_info['userid'];
		$photoplog_file_name = $photoplog_file_info['filename'];
		$photoplog_file_location = $photoplog_fullpath.'/'.$photoplog_userid.'/'.$photoplog_file_name;

		$photoplog_exifinfo = array();

		$photoplog_exif_command = $vbulletin->options['photoplog_jhead_path'] . ' ' . $photoplog_file_location;
		$photoplog_exif_result = array();
		$photoplog_exif_retval = 1; // means error here

		if ($vbulletin->options['photoplog_exec_function'] == 2)
		{
			$photoplog_shell_output = @shell_exec($photoplog_exif_command);
			$photoplog_exif_result = explode("\n", $photoplog_shell_output);
			$photoplog_exif_retval = 0;
		}
		else
		{
			@exec($photoplog_exif_command, $photoplog_exif_result, $photoplog_exif_retval);
		}

		if (!$photoplog_exif_retval && is_array($photoplog_exif_result) && count($photoplog_exif_result))
		{
			foreach ($photoplog_exif_result AS $photoplog_exif_value)
			{
				$photoplog_exif_temp = explode(':', $photoplog_exif_value, 2);

				if (count($photoplog_exif_temp) == 2)
				{
					$photoplog_exif_title = strtolower(trim(trim(eregi_replace('[_]+', '_', 
						eregi_replace('[^a-z0-9]', '_', $photoplog_exif_temp[0])), '_')));

					$photoplog_exif_flag = 0;

					$photoplog_exif_key = intval(array_search($photoplog_exif_title, $photoplog_exif_labels1));
					if ($photoplog_exif_key && ($vbulletin->options['photoplogexifinfo'] & $photoplog_exif_key))
					{
						$photoplog_exif_flag = 1;
					}
					else
					{
						$photoplog_exif_key = intval(array_search($photoplog_exif_title, $photoplog_exif_labels2));
						if ($photoplog_exif_key && ($vbulletin->options['photoplogexifinfo'] & $photoplog_exif_key))
						{
							$photoplog_exif_flag = 1;
						}
					}

					if ($photoplog_exif_flag)
					{
						$photoplog_exif_temp[0] = $vbphrase['photoplog_'.$photoplog_exif_title];

						if ($photoplog_exif_title == 'file_name')
						{
							$photoplog_exif_temp[1] = eregi_replace('^[0-9]+[_]', '', $photoplog_file_name);
						}
						else
						{
							$photoplog_exif_temp[1] = preg_replace($photoplog_exif_desc1, $photoplog_exif_desc2, $photoplog_exif_temp[1]);
						}

						$photoplog_exifinfo["$photoplog_exif_temp[0]"] = trim(preg_replace('/\s+/', ' ', $photoplog_exif_temp[1]));
					}
				}
			}
		}

		$photoplog_exifinfo = serialize($photoplog_exifinfo);

		$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			SET exifinfo = '".$db->escape_string($photoplog_exifinfo)."'
			WHERE fileid = ".intval($photoplog_fileid)."
		");

		$photoplog_msg = $vbphrase['photoplog_updating'].' - '.$vbphrase['photoplog_file'].': '.$photoplog_fileid;
		print_description_row($photoplog_msg, 0, 1);

		@flush();
		@ob_flush();
	}

	$db->free_result($photoplog_file_infos);

	if (
		$photoplog_morecheck = $db->query_first("SELECT fileid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid >= ".intval($photoplog_stop)."
			ORDER BY fileid ASC
			LIMIT 1")
	)
	{
		print_table_footer();
		$photoplog_stop = intval($photoplog_morecheck['fileid']);
		print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=exif&start=".$photoplog_stop."&perpage=".$photoplog_perpage, 1);
	}
	else
	{
		print_description_row('<strong>'.$vbphrase['photoplog_done'].'</strong>', 0, 1);
		print_table_footer();
		print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=update", 1);
	}
}

print_cp_footer();

?>