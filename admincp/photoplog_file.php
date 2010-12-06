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

$photoplog_location = $vbulletin->options['photoplog_script_dir'];

print_cp_header($vbphrase['photoplog_moderate_files']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'moderate';
}

if ($_REQUEST['do'] == 'moderate')
{
	$photoplog_moderate_files = $db->query_read("SELECT fileid, filename,
			userid, username, dateline, title
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE moderate = 1
		ORDER BY dateline DESC
	");

	if ($photoplog_moderate_files)
	{
		print_form_header('photoplog_file', 'domoderate');
		construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
		print_table_header($vbphrase['photoplog_moderate_files'], 2);
		print_cells_row(array(
					'<nobr>' . $vbphrase[photoplog_username] . ' / ' . $vbphrase['photoplog_title'] . '</nobr>',
					'<nobr><input type="checkbox" name="allbox" title="'.$vbphrase['photoplog_check_all'].'" checked="checked" onclick="js_check_all(this.form);" />' . $vbphrase['photoplog_check_all'] . '</nobr>'
				), 1, '', -1, 'bottom');

		$photoplog_cnt_bits = 0;

		while ($photoplog_moderate_file = $db->fetch_array($photoplog_moderate_files))
		{
			$photoplog_cnt_bits++;

			$photoplog_fileid = $photoplog_moderate_file['fileid'];
			$photoplog_filename = $photoplog_moderate_file['filename'];
			$photoplog_userid = $photoplog_moderate_file['userid'];
			$photoplog_username = "<a href=\"user.php?".$vbulletin->session->vars['sessionurl']."do=edit&amp;u=".$photoplog_userid."\">".$photoplog_moderate_file['username']."</a>";

			$photoplog_date = vbdate($vbulletin->options['dateformat'],$photoplog_moderate_file['dateline'],true);
			$photoplog_time = vbdate($vbulletin->options['timeformat'],$photoplog_moderate_file['dateline']);

			$photoplog_click = "<strong>[<a href=\"".$photoplog_location."/edit.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."\" target=\"_blank\">". $vbphrase['photoplog_click_here_to_edit'] ."</a>]</strong>";

			if ($vbulletin->options['photoplog_dynamic_link'])
			{
				$photoplog_thumb = "<img src=\"".$photoplog_location."/file.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."&amp;w=s\" border=\"0\" />";
			}
			else
			{
				$photoplog_file_slink = $photoplog_location."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/small/".$photoplog_filename;
				$photoplog_thumb = "<img src=\"".$photoplog_file_slink."\" border=\"0\" />";
			}

			$photoplog_thumb = "<a style=\"float: right;\" href=\"".$photoplog_location."/index.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."\" target=\"_blank\">".$photoplog_thumb."</a>";

			$photoplog_title = htmlspecialchars_uni($photoplog_moderate_file['title']);
			if (empty($photoplog_title))
			{
				$photoplog_title = $vbphrase['photoplog_untitled'];
			}
			$photoplog_title = "<a href=\"".$photoplog_location."/index.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."\" target=\"_blank\">".$photoplog_title."</a>";

			echo "
				<tr><td colspan=\"2\" class=\"tcat\">".$vbphrase['photoplog_posted_by']." ".$photoplog_username." ".$photoplog_date." ".$photoplog_time." ".$photoplog_click."</td></tr>
			";
			print_checkbox_row($photoplog_title, "photoplog_check[$photoplog_fileid]", true, 1, $photoplog_thumb, '');
		}

		if ($photoplog_cnt_bits)
		{
			echo "
				<tr><td colspan=\"2\" class=\"tcat\">".$vbphrase['photoplog_action_not_reversible']."</td></tr>
			";

			print_description_row('
				<div class="smallfont" align="center" style="font-weight: bold;">
					' . $vbphrase['photoplog_action'] . ':
					<label for="dw_delete"><input type="radio" name="doaction" value="delete" id="dw_delete" tabindex="1" />' . $vbphrase['photoplog_delete'] . '</label>
					<label for="dw_approve"><input type="radio" name="doaction" value="approve" id="dw_approve" tabindex="1" />' . $vbphrase['photoplog_approve'] . '</label>
				</div>', 0, 2);

			print_submit_row($vbphrase['photoplog_submit'], $vbphrase['photoplog_check_all'], 2);
		}
		else
		{
			print_description_row($vbphrase['photoplog_nothing_to_moderate'], 0, 2);
			print_table_footer();
		}
	}
	else
	{
		print_form_header('', '');
		construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
		print_table_header($vbphrase['photoplog_moderate_files'], 1);
		print_description_row($vbphrase['photoplog_bad_luck'], 0, 2);
		print_table_footer();
	}
}

if ($_REQUEST['do'] == 'domoderate')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'photoplog_check' => TYPE_ARRAY_UINT,
		'doaction' => TYPE_NOHTML
	));

	$photoplog_check = $vbulletin->GPC['photoplog_check'];
	$photoplog_doaction = $vbulletin->GPC['doaction'];
	$photoplog_checked_fileids = array();

	foreach ($photoplog_check AS $photoplog_key => $photoplog_value)
	{
		$photoplog_fileid = intval(trim($photoplog_key));
		$photoplog_checks = intval(trim($photoplog_value));

		if ($photoplog_checks)
		{
			$photoplog_checked_fileids[] = $photoplog_fileid;
		}
	}

	$photoplog_sql_in = implode(",",$photoplog_checked_fileids);

	if ($photoplog_doaction == 'approve')
	{
		if (!empty($photoplog_sql_in))
		{
			$photoplog_catids_array = array();
			$photoplog_catids_query = $db->query_read("SELECT catid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				WHERE fileid IN (".$photoplog_sql_in.")
			");
			while ($photoplog_catids_row = $db->fetch_array($photoplog_catids_query))
			{
				$photoplog_catids_array[] = $photoplog_catids_row['catid'];
			}
			$db->free_result($photoplog_catids_query);

			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				SET moderate = 0
				WHERE fileid IN (".$photoplog_sql_in.")
			");

			if ($vbulletin->options['photoplog_user_email'])
			{
				$photoplog_fileids_query = $db->query_read("SELECT fileid,userid
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE fileid IN (".$photoplog_sql_in.")
				");

				while ($photoplog_fileids_row = $db->fetch_array($photoplog_fileids_query))
				{
					$photoplog_fetch_userinfo = $db->query_first("SELECT username,email
						FROM ".TABLE_PREFIX."user
						WHERE userid = ".intval($photoplog_fileids_row['userid'])."
						AND (options & ".intval($vbulletin->bf_misc_useroptions['adminemail']).")
					");
					if ($photoplog_fetch_userinfo)
					{
						$photoplog_fileid = intval($photoplog_fileids_row['fileid']);
						$photoplog_username = unhtmlspecialchars($photoplog_fetch_userinfo['username']);
						$photoplog_subject = $photoplog_message = '';
						eval(fetch_email_phrases('photoplog_approved_file', -1, '', 'photoplog_'));
						vbmail($photoplog_fetch_userinfo['email'], $photoplog_subject, $photoplog_message, true);
					}
					$db->free_result($photoplog_fetch_userinfo);
				}

				$db->free_result($photoplog_fileids_query);
			}

			photoplog_regenerate_counts_table_v2($photoplog_catids_array);
		}
	}
	else if ($photoplog_doaction == 'delete')
	{
		if (!empty($photoplog_sql_in))
		{
			if ($vbulletin->options['photoplog_user_email'])
			{
				$photoplog_fileids_query = $db->query_read("SELECT userid,title
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE fileid IN (".$photoplog_sql_in.")
				");

				while ($photoplog_fileids_row = $db->fetch_array($photoplog_fileids_query))
				{
					$photoplog_fetch_userinfo = $db->query_first("SELECT username,email
						FROM ".TABLE_PREFIX."user
						WHERE userid = ".intval($photoplog_fileids_row['userid'])."
						AND (options & ".intval($vbulletin->bf_misc_useroptions['adminemail']).")
					");
					if ($photoplog_fetch_userinfo)
					{
						$photoplog_username = unhtmlspecialchars($photoplog_fetch_userinfo['username']);
						$photoplog_title = $photoplog_fileids_row['title'];
						$photoplog_subject = $photoplog_message = '';
						eval(fetch_email_phrases('photoplog_declined_file', -1, '', 'photoplog_'));
						vbmail($photoplog_fetch_userinfo['email'], $photoplog_subject, $photoplog_message, true);
					}
					$db->free_result($photoplog_fetch_userinfo);
				}

				$db->free_result($photoplog_fileids_query);
			}

			$photoplog_catids_array = array();
			foreach ($photoplog_checked_fileids AS $photoplog_file_id)
			{
				$photoplog_file_info = $db->query_first("SELECT userid,filename,albumids,fileid,catid
					FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					WHERE fileid = ".intval($photoplog_file_id)."
				");

				if ($photoplog_file_info)
				{
					$photoplog_file_userid = $photoplog_file_info['userid'];
					$photoplog_directory_name = $vbulletin->options['photoplog_full_path']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_userid;
					$photoplog_file_old = $photoplog_file_info['filename'];

					$photoplog_file_albumids = unserialize($photoplog_file_info['albumids']);
					$photoplog_file_fileid = intval($photoplog_file_info['fileid']);
					$photoplog_catids_array[] = $photoplog_file_info['catid'];

					$db->free_result($photoplog_file_info);

					if (
						$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
							WHERE fileid = ".intval($photoplog_file_id)."
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

						$photoplog_commcnt_querys = $db->query_read("SELECT userid, COUNT(userid) AS cnt1
							FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
							WHERE fileid = ".intval($photoplog_file_id)."
							AND comment != ''
							GROUP BY userid
						");
						while ($photoplog_commcnt_query = $db->fetch_array($photoplog_commcnt_querys))
						{
							$photoplog_commcnt = intval($photoplog_commcnt_query['cnt1']);
							if ($photoplog_commcnt > 0)
							{
								$db->query_write("UPDATE " . TABLE_PREFIX . "user
									SET photoplog_commentcount = photoplog_commentcount - $photoplog_commcnt
									WHERE userid = ".intval($photoplog_commcnt_query['userid'])."
								");
							}
						}
						$db->free_result($photoplog_commcnt_querys);

						$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
							WHERE fileid = ".intval($photoplog_file_id)."
						");

						$db->query_write("UPDATE " . TABLE_PREFIX . "user
							SET photoplog_filecount = photoplog_filecount - 1
							WHERE userid = ".intval($photoplog_file_userid)."
						");

						@unlink($photoplog_directory_name."/".$photoplog_file_old);
						@unlink($photoplog_directory_name."/large/".$photoplog_file_old);
						@unlink($photoplog_directory_name."/medium/".$photoplog_file_old);
						@unlink($photoplog_directory_name."/small/".$photoplog_file_old);
					}
				}
			}

			photoplog_regenerate_counts_table_v2($photoplog_catids_array);
		}
	}

	print_cp_redirect("photoplog_file.php?".$vbulletin->session->vars['sessionurl']."do=moderate", 1);
}

print_cp_footer();

?>