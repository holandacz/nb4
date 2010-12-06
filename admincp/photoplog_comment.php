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

print_cp_header($vbphrase['photoplog_moderate_comments']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'moderate';
}

if ($_REQUEST['do'] == 'moderate')
{
	$photoplog_moderate_comments = $db->query_read("SELECT f2.commentid, f2.fileid, f2.rating,
		f2.userid, f2.username, f2.dateline, f2.title, f2.comment, IFNULL(f1.num_comments1,0) AS num_comments,
		IFNULL(f1.title,'') AS file_title, IFNULL(f1.moderate,0) AS file_moderate, f1.userid AS fu_userid,
		f1.filename AS fu_filename
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment AS f2
		LEFT JOIN " . PHOTOPLOG_PREFIX . "photoplog_fileuploads AS f1
		ON (f1.fileid = f2.fileid)
		WHERE f2.moderate = 1
		ORDER BY f2.dateline DESC
	");

	if ($photoplog_moderate_comments)
	{
		print_form_header('photoplog_comment', 'domoderate');
		construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
		print_table_header($vbphrase['photoplog_moderate_comments'], 2);
		print_cells_row(array(
					'<nobr>' . $vbphrase[photoplog_username] . ' / ' . $vbphrase['photoplog_comment'] . '</nobr>',
					'<nobr><input type="checkbox" name="allbox" title="'.$vbphrase['photoplog_check_all'].'" checked="checked" onclick="js_check_all(this.form);" />' . $vbphrase['photoplog_check_all'] . '</nobr>'
				), 1, '', -1, 'bottom');

		$photoplog_cnt_bits = 0;

		while ($photoplog_moderate_comment = $vbulletin->db->fetch_array($photoplog_moderate_comments))
		{
			$photoplog_cnt_bits++;

			$photoplog_commentid = $photoplog_moderate_comment['commentid'];
			$photoplog_fileid = $photoplog_moderate_comment['fileid'];
			$photoplog_fu_userid = $photoplog_moderate_comment['fu_userid'];
			$photoplog_fu_filename = $photoplog_moderate_comment['fu_filename'];

			$photoplog_comment_count = intval($photoplog_moderate_comment['num_comments']);
			if (!$photoplog_comment_count)
			{
				$photoplog_comment_count = 1;
			}

			$photoplog_comment_page = ceil($photoplog_comment_count / 5);
			$photoplog_comment_page = '&amp;page='.$photoplog_comment_page.'#comment'.$photoplog_commentid;

			$photoplog_rating = $photoplog_moderate_comment['rating'];
			$photoplog_rating = "<img style=\"float: right;\" src=\"".$photoplog_location."/stars/rating_".$photoplog_rating.".gif\" border=\"0\" />";

			$photoplog_userid = $photoplog_moderate_comment['userid'];
			$photoplog_username = "<a href=\"user.php?".$vbulletin->session->vars['sessionurl']."do=edit&amp;u=".$photoplog_userid."\">".$photoplog_moderate_comment['username']."</a>";

			$photoplog_date = vbdate($vbulletin->options['dateformat'],$photoplog_moderate_comment['dateline'],true);
			$photoplog_time = vbdate($vbulletin->options['timeformat'],$photoplog_moderate_comment['dateline']);

			$photoplog_click = "<strong>[<a href=\"".$photoplog_location."/index.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid.$photoplog_comment_page."\" target=\"_blank\">". $vbphrase['photoplog_click_here_to_edit'] ."</a>]</strong>";
			$photoplog_click = "<strong>[<a href=\"".$photoplog_location."/comment.php?".$vbulletin->session->vars['sessionurl']."do=edit&amp;m=".$photoplog_commentid."\" target=\"_blank\">". $vbphrase['photoplog_click_here_to_edit'] ."</a>]</strong>";

			if ($vbulletin->options['photoplog_dynamic_link'])
			{
				$photoplog_thumb = "<img src=\"".$photoplog_location."/file.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."&amp;w=s\" border=\"0\" />";
			}
			else
			{
				$photoplog_file_slink = $photoplog_location."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_fu_userid."/small/".$photoplog_fu_filename;
				$photoplog_thumb = "<img src=\"".$photoplog_file_slink."\" border=\"0\" />";
			}

			$photoplog_thumb = "<div style=\"float: right;\">".$photoplog_rating."<br /><a style=\"float: right;\" href=\"".$photoplog_location."/index.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid.$photoplog_comment_page."\" target=\"_blank\">".$photoplog_thumb."</a></div>";

			$photoplog_title = htmlspecialchars_uni($photoplog_moderate_comment['title']);
			$photoplog_comment = htmlspecialchars_uni($photoplog_moderate_comment['comment']);

			if (empty($photoplog_title))
			{
				if ($photoplog_moderate_comment['file_title'])
				{
					$photoplog_title = htmlspecialchars_uni($photoplog_moderate_comment['file_title']);
				}
				else
				{
					$photoplog_title = $vbphrase['photoplog_untitled'];
				}
			}

			if (empty($photoplog_comment))
			{
				$photoplog_comment = $vbphrase['photoplog_not_available'];
			}
			$photoplog_comment = "<a href=\"".$photoplog_location."/index.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid.$photoplog_comment_page."\" target=\"_blank\">".$photoplog_title."</a><br /><br />".$photoplog_comment;

			echo "
				<tr><td colspan=\"2\" class=\"tcat\">".$vbphrase['photoplog_posted_by']." ".$photoplog_username." ".$photoplog_date." ".$photoplog_time." ".$photoplog_click."</td></tr>
			";

			$photoplog_file_moderate = $photoplog_moderate_comment['file_moderate'];

			if (!$photoplog_file_moderate)
			{
				print_checkbox_row($photoplog_comment, "photoplog_check[$photoplog_commentid]", true, 1, $photoplog_thumb, '');
			}
			else
			{
				$photoplog_moderate_msg = "<a href=\"photoplog_file.php?".$vbulletin->session->vars['sessionurl']."do=moderate\">".$vbphrase['photoplog_approve_file']."</a>";
				print_description_row($photoplog_moderate_msg.$photoplog_thumb, 0, 2);
			}
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
		print_table_header($vbphrase['photoplog_moderate_comments'], 1);
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
	$photoplog_checked_commentids = array();

	foreach ($photoplog_check AS $photoplog_key => $photoplog_value)
	{
		$photoplog_commentid = intval(trim($photoplog_key));
		$photoplog_checks = intval(trim($photoplog_value));

		if ($photoplog_checks)
		{
			$photoplog_checked_commentids[] = $photoplog_commentid;
		}
	}

	$photoplog_sql_in = implode(",",$photoplog_checked_commentids);

	if ($photoplog_doaction == 'approve')
	{
		if (!empty($photoplog_sql_in))
		{
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				SET moderate = 0
				WHERE commentid IN (".$photoplog_sql_in.")
			");

			$photoplog_fileids_array = array();
			$photoplog_userids_array = array();
			$photoplog_catids_array = array();

			$photoplog_fileids_query = $db->query_read("SELECT commentid,fileid,userid,catid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE commentid IN (".$photoplog_sql_in.")
			");

			while ($photoplog_fileids_row = $db->fetch_array($photoplog_fileids_query))
			{
				$photoplog_commentid = intval($photoplog_fileids_row['commentid']);
				$photoplog_fileids_array[$photoplog_commentid] = intval($photoplog_fileids_row['fileid']);
				$photoplog_userids_array[$photoplog_commentid] = intval($photoplog_fileids_row['userid']);
				$photoplog_catids_array[] = intval($photoplog_fileids_row['catid']);

				if ($vbulletin->options['photoplog_user_email'])
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
						eval(fetch_email_phrases('photoplog_approved_comment', -1, '', 'photoplog_'));
						vbmail($photoplog_fetch_userinfo['email'], $photoplog_subject, $photoplog_message, true);
					}
					$db->free_result($photoplog_fetch_userinfo);
				}
			}

			$db->free_result($photoplog_fileids_query);

			if (!empty($photoplog_fileids_array) && $vbulletin->options['photoplog_user_email'])
			{
				foreach ($photoplog_fileids_array AS $photoplog_commentid => $photoplog_fileid)
				{
					$photoplog_comment_maker = intval($photoplog_userids_array[$photoplog_commentid]);

					$photoplog_mail_infos = $db->query_read("SELECT f2.commentid AS commentid,
							f1.userid AS fu_userid, f2.userid AS rc_userid
						FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads AS f1,
							" . PHOTOPLOG_PREFIX . "photoplog_ratecomment AS f2
						WHERE f1.fileid = ".intval($photoplog_fileid)."
						AND f1.fileid = f2.fileid
					");

					$photoplog_mail_userids = array();
					$photoplog_mail_count = 0;

					while ($photoplog_mail_info = $db->fetch_array($photoplog_mail_infos))
					{
						if ($photoplog_mail_count == 0)
						{
							$photoplog_fu_userid = intval($photoplog_mail_info['fu_userid']);
							if ($photoplog_fu_userid != $photoplog_comment_maker)
							{
								$photoplog_mail_userids[] = $photoplog_fu_userid;
							}
						}

						$photoplog_rc_userid = intval($photoplog_mail_info['rc_userid']);
						if ($photoplog_rc_userid != $photoplog_comment_maker)
						{
							$photoplog_mail_userids[] = $photoplog_rc_userid;
						}

						$photoplog_mail_count++;
					}

					$db->free_result($photoplog_mail_infos);

					if (!empty($photoplog_mail_userids))
					{
						$photoplog_fetch_userinfos = $db->query_read("SELECT username, email
							FROM " . TABLE_PREFIX . "user
							WHERE userid IN (" . implode(',',array_unique($photoplog_mail_userids)) . ")
						");
						unset($photoplog_mail_userids);

						while($photoplog_fetch_userinfo = $db->fetch_array($photoplog_fetch_userinfos))
						{
							$photoplog_username = unhtmlspecialchars($photoplog_fetch_userinfo['username']);
							$photoplog_subject = $photoplog_message = '';
							eval(fetch_email_phrases('photoplog_new_comment', -1, '', 'photoplog_'));
							vbmail($photoplog_fetch_userinfo['email'], $photoplog_subject, $photoplog_message, true);
						}
						$db->free_result($photoplog_fetch_userinfos);
					}
				}
			}

			if (!empty($photoplog_fileids_array))
			{
				$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					SET moderate = 0
					WHERE fileid IN (" . implode(',', $photoplog_fileids_array) . ")
				");
			}

			photoplog_update_fileuploads_counts_array($photoplog_fileids_array);
			photoplog_regenerate_counts_table_v2($photoplog_catids_array);
		}
	}
	else if ($photoplog_doaction == 'delete')
	{
		if (!empty($photoplog_sql_in))
		{
			$photoplog_fileids_array = array();
			$photoplog_catids_array = array();

			$photoplog_fileids_query = $db->query_read("SELECT fileid,userid,title,catid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE commentid IN (".$photoplog_sql_in.")
			");

			while ($photoplog_fileids_row = $db->fetch_array($photoplog_fileids_query))
			{
				$photoplog_fileids_array[] = intval($photoplog_fileids_row['fileid']);
				$photoplog_catids_array[] = intval($photoplog_fileids_row['catid']);

				if ($vbulletin->options['photoplog_user_email'])
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
						eval(fetch_email_phrases('photoplog_declined_comment', -1, '', 'photoplog_'));
						vbmail($photoplog_fetch_userinfo['email'], $photoplog_subject, $photoplog_message, true);
					}
					$db->free_result($photoplog_fetch_userinfo);
				}
			}

			$db->free_result($photoplog_fileids_query);

			foreach ($photoplog_checked_commentids AS $photoplog_commentid)
			{
				$photoplog_comment_info = $db->query_first("SELECT userid,comment
					FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
					WHERE commentid = ".intval($photoplog_commentid)."
				");

				if ($photoplog_comment_info)
				{
					$photoplog_have_comment = ($photoplog_comment_info['comment'] != '') ? 1 : 0;

					if (
						$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
							WHERE commentid = ".intval($photoplog_commentid)."
						")
					)
					{
						if ($photoplog_have_comment)
						{
							$db->query_write("UPDATE " . TABLE_PREFIX . "user
								SET photoplog_commentcount = photoplog_commentcount - 1
								WHERE userid = ".intval($photoplog_comment_info['userid'])."
							");
						}
					}
				}
			}

			photoplog_update_fileuploads_counts_array($photoplog_fileids_array);
			photoplog_regenerate_counts_table_v2($photoplog_catids_array);
		}
	}

	print_cp_redirect("photoplog_comment.php?".$vbulletin->session->vars['sessionurl']."do=moderate", 1);
}

print_cp_footer();

?>