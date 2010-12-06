<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','comment');
define('PHOTOPLOG_LEVEL','comment');
define('GET_EDIT_TEMPLATES', true);
require_once('./settings.php');

// ########################### Start Comment Page #########################
($hook = vBulletinHook::fetch_hook('photoplog_comment_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'comment';
}

if ($_REQUEST['do'] == 'comment')
{
	if (!defined('PHOTOPLOG_USER8'))
	{
		photoplog_index_bounce();
	}

	$vbulletin->input->clean_array_gpc('g', array(
		'n' => TYPE_UINT
	));

	$photoplog['fileid'] = $vbulletin->GPC['n'];
	$photoplog['commentid'] = 0;

	$photoplog['rating_flag'] = 0;
	if ($vbulletin->options['photoplog_rate_once'])
	{
		$photoplog_rating_info = $db->query_first("SELECT SUM(IF(rating > 0, 1, 0)) AS cnt1
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE fileid = ".intval($photoplog['fileid'])."
			AND userid = ".intval($vbulletin->userinfo['userid'])."
		");
		if ($photoplog_rating_info['cnt1'] > 0)
		{
			$photoplog['rating_flag'] = 1;
		}
		$db->free_result($photoplog_rating_info);
	}

	$photoplog_file_info = '';
	if (!$photoplog_file_info_links && $photoplog_file_id)
	{
		$photoplog_file_info = $db->query_first("SELECT fileid,
			userid,filename,catid,title
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
		$vbulletin->bbcodecache = array();

		require_once(DIR . '/includes/functions_editor.php');

		$photoplog_checked = array();

		$photoplog['fileid'] = $photoplog_file_info['fileid'];
		$photoplog['userid'] = $photoplog_file_info['userid'];
		$photoplog['filename'] = $photoplog_file_info['filename'];
		$photoplog['catid'] = $photoplog_file_info['catid'];
		$photoplog['title'] = $photoplog_file_info['title'];
		$photoplog['title'] = photoplog_process_text($photoplog['title'], $photoplog['catid'], true, false);
		if ($photoplog['title'] == $vbphrase['photoplog_untitled'])
		{
			$photoplog['title'] = '';
		}

		$do_html = 0;
		$do_smilies = 0;
		$do_comments = 0;
		$do_imgcode = 0;

		if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
		{
			$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
			$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

			$do_html = ($photoplog_catoptions['allowhtml']) ? 1 : 0;
			$do_smilies = ($photoplog_catoptions['allowsmilies']) ? 1 : 0;
			$do_comments = ($photoplog_catoptions['allowcomments']) ? 1 : 0;

			// this is to show the little image toolbar icon
			$do_imgcode = ($photoplog_catoptions['allowimgcode']) ? 1 : 0;
			$vbulletin->options['allowbbimagecode'] = $do_imgcode;
		}

		if (!$do_comments)
		{
			photoplog_index_bounce();
		}

		// yep this is how fileid is passed in
		$editorid = construct_edit_toolbar('', $do_html, 'nonforum', $do_smilies, $photoplog['fileid']);

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

		($hook = vBulletinHook::fetch_hook('photoplog_comment_form')) ? eval($hook) : false;

		photoplog_output_page('photoplog_comment_form', $vbphrase['photoplog_comment']);
	}
	else
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_mod_queue']);
	}
}

if ($_POST['do'] == 'docomment')
{
	if (!defined('PHOTOPLOG_USER8') && !defined('PHOTOPLOG_USER9') && !defined('PHOTOPLOG_USER11'))
	{
		photoplog_index_bounce();
	}

	$vbulletin->input->clean_array_gpc('p', array(
		'fileid' => TYPE_UINT,
		'commentid' => TYPE_UINT,
		'rating' => TYPE_UINT,
		'title' => TYPE_STR,
		'message' => TYPE_STR,
		'wysiwyg' => TYPE_BOOL,
		'stars' => TYPE_BOOL
	));

	($hook = vBulletinHook::fetch_hook('photoplog_comment_docomment_start')) ? eval($hook) : false;

	$photoplog['fileid'] = $vbulletin->GPC['fileid'];
	$photoplog['commentid'] = $vbulletin->GPC['commentid'];
	$photoplog['rating'] = $vbulletin->GPC['rating'];
	$photoplog['title'] = $vbulletin->GPC['title'];
	$photoplog['comment'] = $vbulletin->GPC['message'];
	$photoplog_wysiwyg = $vbulletin->GPC['wysiwyg'];
	$photoplog_stars = $vbulletin->GPC['stars'];

	if ($vbulletin->options['photoplog_rate_once'])
	{
		if ($photoplog['commentid']) // edit comment
		{
			$photoplog_rating_userinfo = $db->query_first("SELECT userid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE commentid = ".intval($photoplog['commentid'])."
			");
			$photoplog_rating_userid = $photoplog_rating_userinfo['userid'];
			$db->free_result($photoplog_rating_userinfo);
		}
		else // new comment
		{
			$photoplog_rating_userid = $vbulletin->userinfo['userid'];
		}

		$photoplog_rating_info = $db->query_first("SELECT SUM(IF(rating > 0, 1, 0)) AS cnt1
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE fileid = ".intval($photoplog['fileid'])."
			AND userid = ".intval($photoplog_rating_userid)."
		");
		if ($photoplog_rating_info['cnt1'] > 0)
		{
			$photoplog['rating'] = 0;
		}
		$db->free_result($photoplog_rating_info);
	}

	if (($photoplog_stars && !$photoplog['rating']) || ($photoplog['comment'] == '' && !$photoplog['rating']))
	{
		$photoplog_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog['fileid'];
		exec_header_redirect($photoplog_url);
		exit();
	}

	$photoplog_file_info = $db->query_first("SELECT userid,catid,
		last_comment_id0,last_comment_dateline0,last_comment_id1,last_comment_dateline1
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid = ".intval($photoplog['fileid'])."
		$photoplog_catid_sql1
		$photoplog_admin_sql1
	");

	$do_html = false;
	$do_comments = false;

	if ($photoplog_file_info)
	{
		$photoplog_file_userid = $photoplog_file_info['userid'];

		$photoplog['catid'] = $photoplog_file_info['catid'];
		if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
		{
			$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
			$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

			$do_html = ($photoplog_catoptions['allowhtml']) ? true : false;
			$do_comments = ($photoplog_catoptions['allowcomments']) ? true : false;
		}

		if (!$do_comments)
		{
			photoplog_index_bounce();
		}

/*
		if ($photoplog['comment'] == '' || vbstrlen($photoplog['comment']) < intval($vbulletin->options['postminchars']))
		{
			$photoplog_msg_too_short = construct_phrase($vbphrase['message_too_short'],$vbulletin->options['postminchars']);
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$photoplog_msg_too_short);
		}
*/

		if ($photoplog_wysiwyg)
		{
			require_once(DIR . '/includes/functions_wysiwyg.php');

			$photoplog['comment'] = str_replace($vbulletin->options['bburl']."/images/smilies/","images/smilies/",$photoplog['comment']);
			$photoplog['comment'] = convert_wysiwyg_html_to_bbcode($photoplog['comment'], $do_html);
		}

		if (!$photoplog['commentid']) // new comment
		{
			if (defined('PHOTOPLOG_USER8'))
			{
				($hook = vBulletinHook::fetch_hook('photoplog_comment_docomment_add')) ? eval($hook) : false;

				$photoplog_have_comment = ($photoplog['comment'] != '') ? 1 : 0;

				$photoplog_sql = 1;
				if (!$photoplog_have_comment || ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanpostunmoderatedcomments']))
				{
					$photoplog_sql = 0;
				}

				$photoplog_current_last_comment_id0 = intval($photoplog_file_info['last_comment_id0']);
				$photoplog_current_last_comment_dateline0 = intval($photoplog_file_info['last_comment_dateline0']);
				$photoplog_current_last_comment_id1 = intval($photoplog_file_info['last_comment_id1']);
				$photoplog_current_last_comment_dateline1 = intval($photoplog_file_info['last_comment_dateline1']);

				$db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
					(fileid, catid, userid, username, rating, title, comment, dateline, moderate, lastedit)
					VALUES (
						".intval($photoplog['fileid']).",
						".intval($photoplog['catid']).",
						".intval($vbulletin->userinfo['userid']).",
						'".$db->escape_string($vbulletin->userinfo['username'])."',
						".intval($photoplog['rating']).",
						'".$db->escape_string($photoplog['title'])."',
						'".$db->escape_string($photoplog['comment'])."',
						".intval(TIMENOW).",
						".intval($photoplog_sql).",
						''
					)
				");

				$photoplog_pound_place = $db->insert_id();

				$photoplog_rating_plus = (intval($photoplog['rating']) > 0) ? 1 : 0;

				if ($photoplog_have_comment)
				{
					$photoplog_fileuploads_update0 = '';
					if ($photoplog_sql == 0)
					{
						$photoplog_fileuploads_update0 = ",num_comments0 = num_comments0 + 1,
							num_ratings0 = num_ratings0 + ".intval($photoplog_rating_plus).",
							sum_ratings0 = sum_ratings0 + ".intval($photoplog['rating']).",
							last_comment_dateline0 = ".intval(TIMENOW).",
							last_comment_id0 = ".intval($photoplog_pound_place)."
						";
					}
					$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
						SET num_comments1 = num_comments1 + 1,
						num_ratings1 = num_ratings1 + ".intval($photoplog_rating_plus).",
						sum_ratings1 = sum_ratings1 + ".intval($photoplog['rating']).",
						last_comment_dateline1 = ".intval(TIMENOW).",
						last_comment_id1 = ".intval($photoplog_pound_place)."
						$photoplog_fileuploads_update0
						WHERE fileid = ".intval($photoplog['fileid'])."
					");

					$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
						SET num_comments = num_comments + 1,
						num_ratings = num_ratings + ".intval($photoplog_rating_plus).",
						sum_ratings = sum_ratings + ".intval($photoplog['rating']).",
						last_comment_dateline = ".intval(TIMENOW).",
						last_comment_id = ".intval($photoplog_pound_place)."
						WHERE catid = ".intval($photoplog['catid'])."
						AND moderate >= ".intval($photoplog_sql)."
					");

					$db->query_write("UPDATE " . TABLE_PREFIX . "user
						SET photoplog_commentcount = photoplog_commentcount + 1
						WHERE userid = ".intval($vbulletin->userinfo['userid'])."
					");

					if ($photoplog_sql == 0 && $vbulletin->options['photoplog_user_email'])
					{
						if ($photoplog_file_userid != $vbulletin->userinfo['userid'])
						{
							$photoplog_fetch_userinfo = $db->query_first("SELECT username,email
								FROM ".TABLE_PREFIX."user
								WHERE userid = ".intval($photoplog_file_userid)."
								AND (options & ".intval($vbulletin->bf_misc_useroptions['adminemail']).")
							");
							if ($photoplog_fetch_userinfo)
							{
								$photoplog_username = unhtmlspecialchars($photoplog_fetch_userinfo['username']);
								$photoplog_fileid = $photoplog['fileid'];
								$photoplog_subject = $photoplog_message = '';
								eval(fetch_email_phrases('photoplog_new_comment', -1, '', 'photoplog_'));
								vbmail($photoplog_fetch_userinfo['email'], $photoplog_subject, $photoplog_message, true);
							}
							$db->free_result($photoplog_fetch_userinfo);
						}
					}
					else if ($photoplog_sql == 1 && $vbulletin->options['photoplog_admin_email'])
					{
						$photoplog_subject = $photoplog_message = '';
						eval(fetch_email_phrases('photoplog_mod_comment', -1, '', 'photoplog_'));
						vbmail($vbulletin->options['webmasteremail'], $photoplog_subject, $photoplog_message, true);
					}
				}
				else // no comment provided
				{
					$photoplog_fileuploads_update0 = '';
					if ($photoplog_sql == 0)
					{
						$photoplog_fileuploads_update0 = ",
							num_ratings0 = num_ratings0 + ".intval($photoplog_rating_plus).",
							sum_ratings0 = sum_ratings0 + ".intval($photoplog['rating'])."
						";
					}
					$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
						SET num_ratings1 = num_ratings1 + ".intval($photoplog_rating_plus).",
						sum_ratings1 = sum_ratings1 + ".intval($photoplog['rating'])."
						$photoplog_fileuploads_update0
						WHERE fileid = ".intval($photoplog['fileid'])."
					");

					$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
						SET num_ratings = num_ratings + ".intval($photoplog_rating_plus).",
						sum_ratings = sum_ratings + ".intval($photoplog['rating'])."
						WHERE catid = ".intval($photoplog['catid'])."
						AND moderate >= ".intval($photoplog_sql)."
					");

					if ($photoplog_sql == 1 && $vbulletin->options['photoplog_admin_email'])
					{
						$photoplog_subject = $photoplog_message = '';
						eval(fetch_email_phrases('photoplog_mod_comment', -1, '', 'photoplog_'));
						vbmail($vbulletin->options['webmasteremail'], $photoplog_subject, $photoplog_message, true);
					}
				}
			}
			else
			{
				photoplog_index_bounce();
			}
		}
		else // edited comment
		{
			$photoplog_comment_info = $db->query_first("SELECT userid, catid,
				username, dateline, comment, moderate, rating
				FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE commentid = ".intval($photoplog['commentid'])."
				AND fileid = ".intval($photoplog['fileid'])."
				$photoplog_catid_sql2
				$photoplog_admin_sql2
			");

			if ($photoplog_comment_info)
			{
				if (
					(
						($photoplog_comment_info['userid'] == $vbulletin->userinfo['userid'])
							&&
						defined('PHOTOPLOG_USER9')
					)
						||
					defined('PHOTOPLOG_USER11')
				)
				{
					($hook = vBulletinHook::fetch_hook('photoplog_comment_docomment_edit')) ? eval($hook) : false;

					$photoplog['catid'] = $photoplog_comment_info['catid'];
					$photoplog['userid'] = $photoplog_comment_info['userid'];
					$photoplog['username'] = $photoplog_comment_info['username'];
					$photoplog_dateline = $photoplog_comment_info['dateline'];

					$photoplog_have_old_comment = ($photoplog_comment_info['comment'] != '') ? 1 : 0;

					$photoplog['date'] = vbdate($vbulletin->options['dateformat'],TIMENOW);
					$photoplog['time'] = vbdate($vbulletin->options['timeformat'],TIMENOW);

					$photoplog_have_new_comment = ($photoplog['comment'] != '') ? 1 : 0;

					$photoplog_moderate = 1;
					if (!$photoplog_have_new_comment || ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanpostunmoderatedcomments']))
					{
						$photoplog_moderate = 0;
					}

					$photoplog_lastedit = '';
					if ($permissions['genericoptions'] & $vbulletin->bf_ugp_genericoptions['showeditedby'])
					{
						if ($photoplog_dateline < (TIMENOW - ($vbulletin->options['noeditedbytime'] * 60)))
						{
							$photoplog_lastedit = construct_phrase($vbphrase['photoplog_last_edited_by'],$vbulletin->userinfo['username'],$photoplog['date'],$photoplog['time']);
						}
					}

					$db->query_write("REPLACE INTO " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
						(commentid, fileid, catid, userid, username, rating, title, comment, dateline, moderate, lastedit)
						VALUES (
							".intval($photoplog['commentid']).",
							".intval($photoplog['fileid']).",
							".intval($photoplog['catid']).",
							".intval($photoplog['userid']).",
							'".$db->escape_string($photoplog['username'])."',
							".intval($photoplog['rating']).",
							'".$db->escape_string($photoplog['title'])."',
							'".$db->escape_string($photoplog['comment'])."',
							".intval($photoplog_dateline).",
							".intval($photoplog_moderate).",
							'".$db->escape_string($photoplog_lastedit)."'
						)
					");

					$photoplog_pound_place = $photoplog['commentid'];

					if (!$photoplog_have_old_comment && $photoplog_have_new_comment)
					{
						$db->query_write("UPDATE " . TABLE_PREFIX . "user
							SET photoplog_commentcount = photoplog_commentcount + 1
							WHERE userid = ".intval($photoplog_comment_info['userid'])."
						");
					}
					if ($photoplog_have_old_comment && !$photoplog_have_new_comment)
					{
						$db->query_write("UPDATE " . TABLE_PREFIX . "user
							SET photoplog_commentcount = photoplog_commentcount - 1
							WHERE userid = ".intval($photoplog_comment_info['userid'])."
						");
					}

					$photoplog_have_comment_change = ($photoplog_have_old_comment != $photoplog_have_new_comment) ? 1 : 0;

					if ($photoplog_have_comment_change || $photoplog_moderate != $photoplog_comment_info['moderate'])
					{
						// moderate or comment changed so recompute to be sure
						photoplog_update_fileuploads_counts($photoplog['fileid']);
						photoplog_update_counts_table($photoplog['catid']);
					}
					else
					{
						// moderate and comment have not changed so do this directly
						$photoplog_rating_plus = (intval($photoplog['rating']) > 0) ? 1 : 0;
						if ($photoplog_comment_info['rating'] > 0) { $photoplog_rating_plus--; }
						$photoplog_rating_delta = intval($photoplog['rating']) - intval($photoplog_comment_info['rating']);

						$photoplog_fileuploads_update0 = '';
						if ($photoplog_moderate == 0)
						{
							$photoplog_fileuploads_update0 = ",
								num_ratings0 = num_ratings0 + ".intval($photoplog_rating_plus).",
								sum_ratings0 = sum_ratings0 + ".intval($photoplog_rating_delta)."
							";
						}
						$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
							SET num_ratings1 = num_ratings1 + ".intval($photoplog_rating_plus).",
							sum_ratings1 = sum_ratings1 + ".intval($photoplog_rating_delta)."
							$photoplog_fileuploads_update0
							WHERE fileid = ".intval($photoplog['fileid'])."
						");

						$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
							SET num_ratings = num_ratings + ".intval($photoplog_rating_plus).",
							sum_ratings = sum_ratings + ".intval($photoplog_rating_delta)."
							WHERE catid = ".intval($photoplog['catid'])."
							AND moderate >= ".intval($photoplog_moderate)."
						");
					}

					if ($photoplog_moderate == 1 && $vbulletin->options['photoplog_admin_email'])
					{
						$photoplog_subject = $photoplog_message = '';
						eval(fetch_email_phrases('photoplog_mod_comment', -1, '', 'photoplog_'));
						vbmail($vbulletin->options['webmasteremail'], $photoplog_subject, $photoplog_message, true);
					}
				}
				else
				{
					photoplog_index_bounce();
				}
			}
			else
			{
				photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_mod_queue']);
			}
		}

		if ($photoplog_sql == 0 || $photoplog_admin_sql2 == '')
		{
			if ($photoplog_stars)
			{
				($hook = vBulletinHook::fetch_hook('photoplog_comment_docomment_complete')) ? eval($hook) : false;

				$photoplog_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog['fileid'];
				exec_header_redirect($photoplog_url);
				exit();
			}
			else
			{
				$photoplog_comment_count = $db->query_first("SELECT COUNT(*) AS cnt1
					FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
					WHERE fileid = ".intval($photoplog['fileid'])."
					AND commentid <= ".intval($photoplog_pound_place)."
				");
				if (!$photoplog_comment_count['cnt1'])
				{
					$photoplog_comment_count['cnt1'] = 1;
				}
				$photoplog_comment_page = ceil($photoplog_comment_count['cnt1'] / 5);
				$photoplog_comment_page = '&amp;page='.$photoplog_comment_page.'#comment'.$photoplog_pound_place;

				($hook = vBulletinHook::fetch_hook('photoplog_comment_docomment_complete')) ? eval($hook) : false;

				$photoplog_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog['fileid'].$photoplog_comment_page;
				exec_header_redirect($photoplog_url);
				exit();
			}
		}
		else
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_mod_queue']);
		}
	}
	else
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_mod_queue']);
	}
}

if ($_GET['do'] == 'edit')
{
	if (!defined('PHOTOPLOG_USER9') && !defined('PHOTOPLOG_USER11'))
	{
		photoplog_index_bounce();
	}

	$vbulletin->input->clean_array_gpc('g', array(
		'm' => TYPE_UINT
	));

	$photoplog['commentid'] = $vbulletin->GPC['m'];

	$photoplog_comment_info = $db->query_first("SELECT " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.userid,
		" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.fileid,
		" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.catid,
		" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.rating,
		" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.title,
		" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.comment,
		" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.userid AS fu_userid,
		" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.filename AS fu_filename
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment, " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.commentid = ".intval($photoplog['commentid'])."
		AND " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.fileid = " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid
		$photoplog_catid_sql1
		$photoplog_catid_sql2
		$photoplog_admin_sql1
		$photoplog_admin_sql2
	");

	if ($photoplog_comment_info)
	{
		if (
			(
				($photoplog_comment_info['userid'] == $vbulletin->userinfo['userid'])
					&&
				defined('PHOTOPLOG_USER9')
			)
				||
			defined('PHOTOPLOG_USER11')
		)
		{
			$photoplog['rating_flag'] = 0;
			if ($vbulletin->options['photoplog_rate_once'])
			{
				$photoplog_rating_info = $db->query_first("SELECT SUM(IF(rating > 0, 1, 0)) AS cnt1
					FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
					WHERE fileid = ".intval($photoplog_comment_info['fileid'])."
					AND userid = ".intval($photoplog_comment_info['userid'])."
				");
				if ($photoplog_rating_info['cnt1'] > 0)
				{
					$photoplog['rating_flag'] = 1;
				}
				$db->free_result($photoplog_rating_info);
			}

			$vbulletin->bbcodecache = array();

			require_once(DIR . '/includes/functions_editor.php');

			$photoplog['catid'] = intval($photoplog_comment_info['catid']);

			$do_html = 0;
			$do_smilies = 0;
			$do_bbcode = 0;
			$do_comments = 0;
			$do_imgcode = 0;

			if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
			{
				$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
				$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

				$do_html = ($photoplog_catoptions['allowhtml']) ? 1 : 0;
				$do_smilies = ($photoplog_catoptions['allowsmilies']) ? 1 : 0;
				$do_bbcode = ($photoplog_catoptions['allowbbcode']) ? 1 : 0;
				$do_comments = ($photoplog_catoptions['allowcomments']) ? 1 : 0;

				// this is to show the little image toolbar icon
				$do_imgcode = ($photoplog_catoptions['allowimgcode']) ? 1 : 0;
				$vbulletin->options['allowbbimagecode'] = $do_imgcode;
			}

			if (!$do_comments)
			{
				photoplog_index_bounce();
			}

			$photoplog_checked = array();
			$photoplog['rating'] = $photoplog_comment_info['rating'];

			for ($i=0; $i<=5; $i++)
			{
				$photoplog_checked[$i] = '';
				if ($i == $photoplog['rating'])
				{
					$photoplog_checked[$i] = "checked=\"checked\"";
				}
			}

			$photoplog['fileid'] = $photoplog_comment_info['fileid'];
			$photoplog['userid'] = $photoplog_comment_info['fu_userid'];
			$photoplog['filename'] = $photoplog_comment_info['fu_filename'];

			$photoplog['title'] = $photoplog_comment_info['title'];
			$photoplog['title'] = photoplog_process_text($photoplog['title'], $photoplog['catid'], true, false);
			if ($photoplog['title'] == $vbphrase['photoplog_untitled'])
			{
				$photoplog['title'] = '';
			}

			$photoplog['comment'] = $photoplog_comment_info['comment'];
			$photoplog['comment'] = photoplog_process_text($photoplog['comment'], $photoplog['catid'], false, false);

			// yep this is how fileid is passed in
			$editorid = construct_edit_toolbar('', $do_html, 'nonforum', $do_smilies, $photoplog['fileid']);

			$photoplog_toolbartype = $do_bbcode ? is_wysiwyg_compatible(-1, 'fe') : 0;
			if ($photoplog_toolbartype != 2)
			{
				$photoplog['comment'] = $photoplog_comment_info['comment'];
			}

			// special character and link stuff for the editors
			$photoplog['comment'] = str_replace('src="images/smilies/', 'src="'.$vbulletin->options['bburl'].'/images/smilies/', $photoplog['comment']);
			$photoplog['comment'] = htmlspecialchars_uni($photoplog['comment']);

			// yep this is how to get the comment in
			$messagearea = str_replace("</textarea>",$photoplog['comment']."</textarea>",$messagearea);

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

			($hook = vBulletinHook::fetch_hook('photoplog_comment_form')) ? eval($hook) : false;

			photoplog_output_page('photoplog_comment_form', $vbphrase['photoplog_comment']);
		}
		else
		{
			photoplog_index_bounce();
		}
	}
	else
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_edit']);
	}
}

if ($_GET['do'] == 'delete')
{
	if (!defined('PHOTOPLOG_USER10') && !defined('PHOTOPLOG_USER12'))
	{
		photoplog_index_bounce();
	}

	$vbulletin->input->clean_array_gpc('g', array(
		'm' => TYPE_UINT
	));

	$photoplog['commentid'] = $vbulletin->GPC['m'];

	$photoplog_comment_info = $db->query_first("SELECT catid, userid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE commentid = ".intval($photoplog['commentid'])."
		$photoplog_catid_sql2
		$photoplog_admin_sql2
	");

	$photoplog['catid'] = $photoplog_comment_info['catid'];

	$do_comments = 0;

	if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
	{
		$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
		$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

		$do_comments = ($photoplog_catoptions['allowcomments']) ? 1 : 0;
	}

	if (!$do_comments)
	{
//		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_tank']);
		photoplog_index_bounce();
	}

	if ($photoplog_comment_info)
	{
		if (
			(
				($photoplog_comment_info['userid'] == $vbulletin->userinfo['userid'])
					&&
				defined('PHOTOPLOG_USER10')
			)
				||
			defined('PHOTOPLOG_USER12')
		)
		{
			($hook = vBulletinHook::fetch_hook('photoplog_comment_removeform')) ? eval($hook) : false;

			photoplog_output_page('photoplog_remove_form', $vbphrase['photoplog_delete_comment']);
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
	if (!defined('PHOTOPLOG_USER10') && !defined('PHOTOPLOG_USER12'))
	{
		photoplog_index_bounce();
	}

	$vbulletin->input->clean_array_gpc('p', array(
		'commentid' => TYPE_UINT
	));

	($hook = vBulletinHook::fetch_hook('photoplog_comment_dodelete_start')) ? eval($hook) : false;

	$photoplog['commentid'] = $vbulletin->GPC['commentid'];

	$photoplog_comment_info = $db->query_first("SELECT catid,
			userid, fileid, comment
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE commentid = ".intval($photoplog['commentid'])."
		$photoplog_catid_sql2
		$photoplog_admin_sql2
	");

	$photoplog['catid'] = $photoplog_comment_info['catid'];

	$do_comments = 0;

	if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
	{
		$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
		$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

		$do_comments = ($photoplog_catoptions['allowcomments']) ? 1 : 0;
	}

	if (!$do_comments)
	{
		photoplog_index_bounce();
	}

	if ($photoplog_comment_info)
	{
		if (
			(
				($photoplog_comment_info['userid'] == $vbulletin->userinfo['userid'])
					&&
				defined('PHOTOPLOG_USER10')
			)
				||
			defined('PHOTOPLOG_USER12')
		)
		{
			$photoplog['fileid'] = $photoplog_comment_info['fileid'];
			$photoplog_have_comment = ($photoplog_comment_info['comment'] != '') ? 1 : 0;

			$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE commentid = ".intval($photoplog['commentid'])."
				$photoplog_catid_sql2
				$photoplog_admin_sql2
			");

			if ($photoplog_have_comment)
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "user
					SET photoplog_commentcount = photoplog_commentcount - 1
					WHERE userid = ".intval($photoplog_comment_info['userid'])."
				");
			}

			photoplog_update_fileuploads_counts($photoplog['fileid']);
			photoplog_update_counts_table($photoplog['catid']);

			($hook = vBulletinHook::fetch_hook('photoplog_comment_dodelete_complete')) ? eval($hook) : false;

			$photoplog_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog['fileid'];
			exec_header_redirect($photoplog_url);
			exit();
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

($hook = vBulletinHook::fetch_hook('photoplog_comment_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'comment' && $_POST['do'] != 'docomment' && $_GET['do'] != 'edit' && $_GET['do'] != 'delete' && $_POST['do'] != 'dodelete')
{
	photoplog_index_bounce();
}

?>