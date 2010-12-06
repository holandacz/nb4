<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','upload');
define('PHOTOPLOG_LEVEL','upload');
define('GET_EDIT_TEMPLATES', true);
require_once('./settings.php');

// ########################### Start Upload Page ##########################
($hook = vBulletinHook::fetch_hook('photoplog_upload_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'upload';
}

$photoplog_cnt_checks = $db->query_read("SELECT catid, COUNT(*) AS cnt
	FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
	WHERE userid = ".intval($vbulletin->userinfo['userid'])."
	GROUP BY catid
");

$photoplog_cat_uploads = array();
$photoplog_tot_uploads = 0;
while ($photoplog_cnt_check = $db->fetch_array($photoplog_cnt_checks))
{
	$photoplog_cnt_check['catid'] = intval($photoplog_cnt_check['catid']);
	$photoplog_cnt_check['cnt'] = intval($photoplog_cnt_check['cnt']);
	$photoplog_cat_uploads[$photoplog_cnt_check['catid']] = $photoplog_cnt_check['cnt'];
	$photoplog_tot_uploads += $photoplog_cnt_check['cnt'];
}
$db->free_result($photoplog_cnt_checks);

if (
	(
		$photoplog_perm_catid && $permissions['photoplogmaxfilelimit']
			&&
		$photoplog_cat_uploads[$photoplog_perm_catid] >= $permissions['photoplogmaxfilelimit']
	)
		||
	(
		!$photoplog_perm_catid && $permissions['photoplogmaxfilelimit_group']
			&&
		$photoplog_tot_uploads >= $permissions['photoplogmaxfilelimit_group']
	)
)
{
	photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_max']);
}

$photoplog_ftp_import_flag = 0;
if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanuseftpimport'])
{
	$photoplog_ftp_import_flag = 1;
}

if ($_REQUEST['do'] == 'upload')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'c' => TYPE_UINT
	));
	$vbulletin->input->clean_array_gpc('p', array(
		'catid' => TYPE_UINT
	));

	$photoplog_file_catid_default = $vbulletin->GPC['c'];
	$photoplog_file_catid = $vbulletin->GPC['catid'];

	if (in_array($photoplog_file_catid,$photoplog_perm_not_allowed_bits))
	{
		$photoplog_file_catid = -1;
	}

	if (!in_array($photoplog_file_catid,array_keys($photoplog_ds_catopts)))
	{
		$photoplog_file_catid = -1;
	}

	$photoplog_list_categories_row = $photoplog_list_categories;
	$photoplog_list_categories_row[-1] = $vbphrase['photoplog_select_one'];
	if (!empty($photoplog_perm_not_allowed_bits))
	{
		array_walk($photoplog_list_categories_row, 'photoplog_append_key', '');
		$photoplog_list_categories_row = array_flip(array_diff(array_flip($photoplog_list_categories_row),$photoplog_perm_not_allowed_bits));
		array_walk($photoplog_list_categories_row, 'photoplog_remove_key', '');
	}

	$photoplog['suggestcat'] = 0;
	if (defined('PHOTOPLOG_USER16'))
	{
		$photoplog['suggestcat'] = 1;
	}

	$photoplog['createcat'] = 0;
	if (defined('PHOTOPLOG_USER17'))
	{
		$photoplog['createcat'] = 1;
	}

	if ($photoplog_file_catid < 0)
	{
		$photoplog_divider_array = array();
		$photoplog_list_categories_row2 = array();
		$photoplog_list_categories_row2[-1] = $vbphrase['photoplog_select_one'];
		$photoplog_list_categories_row3 = array();

		foreach ($photoplog_list_categories_row AS $photoplog_list_categories_row_catid => $photoplog_list_categories_row_title)
		{
			if ($photoplog_list_categories_row_catid != '-1') // && $photoplog_ds_catopts[$photoplog_list_categories_row_catid]['parentid'] < 0)
			{
				$photoplog_divider_array[$photoplog_list_categories_row_catid] = convert_bits_to_array($photoplog_ds_catopts[$photoplog_list_categories_row_catid]['options'], $photoplog_categoryoptions);
				if ($photoplog_divider_array[$photoplog_list_categories_row_catid]['actasdivider'])
				{
					if (!isset($photoplog_list_categories_row3[$photoplog_list_categories_row_catid]))
					{
						$photoplog_list_categories_row3[$photoplog_list_categories_row_catid] = 1;
						$photoplog_list_categories_row2[$photoplog_list_categories_row_title] = array();
					}
					$photoplog_list_categories_row_relatives = $photoplog_list_relatives[$photoplog_list_categories_row_catid];
					foreach ($photoplog_list_categories_row_relatives AS $photoplog_relative_catid)
					{
						if (!isset($photoplog_list_categories_row3[$photoplog_relative_catid]))
						{
							$photoplog_list_categories_row3[$photoplog_relative_catid] = 1;
							$photoplog_list_categories_row2[$photoplog_list_categories_row_title][$photoplog_relative_catid] = $photoplog_list_categories_row[$photoplog_relative_catid];
						}
					}
				}
				else
				{
					if (!isset($photoplog_list_categories_row3[$photoplog_list_categories_row_catid]))
					{
						$photoplog_list_categories_row3[$photoplog_list_categories_row_catid] = 1;
						$photoplog_list_categories_row2[$photoplog_list_categories_row_catid] = $photoplog_list_categories_row_title;
					}
					$photoplog_list_categories_row_relatives = $photoplog_list_relatives[$photoplog_list_categories_row_catid];
					foreach ($photoplog_list_categories_row_relatives AS $photoplog_relative_catid)
					{
						if (!isset($photoplog_list_categories_row3[$photoplog_relative_catid]))
						{
							$photoplog_list_categories_row3[$photoplog_relative_catid] = 1;
							$photoplog_list_categories_row2[$photoplog_relative_catid] = $photoplog_list_categories_row[$photoplog_relative_catid];
						}
					}
				}
			}
		}

		$photoplog_list_categories_row = $photoplog_list_categories_row2;
		unset($photoplog_divider_array, $photoplog_list_categories_row2, $photoplog_list_categories_row3);

		$photoplog['select_row'] = "<select name=\"catid\" id=\"sel_catid\" tabindex=\"1\">\n";
		$photoplog['select_row'] .= photoplog_select_options($photoplog_list_categories_row, $photoplog_file_catid_default, true, true);
		$photoplog['select_row'] .= "</select>\n";

		($hook = vBulletinHook::fetch_hook('photoplog_upload_selectcategory')) ? eval($hook) : false;

		photoplog_output_page('photoplog_upload_select_category', $vbphrase['photoplog_upload_file']);
	}
	else
	{
		$photoplog['select_row'] = htmlspecialchars_uni(photoplog_get_category_title($photoplog_file_catid));

		$photoplog['useset_row'] = '';
		if ($vbulletin->options['photoplog_box_count'] > 1 || $vbulletin->options['photoplog_url_count'] > 1)
		{
			$photoplog['useset_row'] = "
				<span class=\"smallfont\" style=\"white-space: nowrap;\">
				<label for=\"rb_1_useset\"><input type=\"radio\" name=\"useset\" id=\"rb_1_useset\" value=\"1\" tabindex=\"1\" />".$vbphrase['photoplog_yes']."</label>
				<label for=\"rb_0_useset\"><input type=\"radio\" name=\"useset\" id=\"rb_0_useset\" value=\"0\" tabindex=\"1\" />".$vbphrase['photoplog_no']."</label>
				</span>
			";
		}

		$photoplog['maxfilesize'] = intval($permissions['photoplogmaxfilesize']);
		$photoplog['maxfilesize'] = vb_number_format($photoplog['maxfilesize'],1,true);

		$do_html = 0;
		$do_smilies = 0;
		$do_imgcode = 0;

		if ($photoplog_file_catid > 0 && in_array($photoplog_file_catid,array_keys($photoplog_ds_catopts)))
		{
			$photoplog_categorybit = $photoplog_ds_catopts["$photoplog_file_catid"]['options'];
			$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

			$do_html = ($photoplog_catoptions['allowhtml']) ? 1 : 0;
			$do_smilies = ($photoplog_catoptions['allowsmilies']) ? 1 : 0;

			// this is to show the little image toolbar icon
			$do_imgcode = ($photoplog_catoptions['allowimgcode']) ? 1 : 0;
			$vbulletin->options['allowbbimagecode'] = $do_imgcode;
		}
		else
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no'].' '.$vbphrase['photoplog_category']);
		}

		$vbulletin->bbcodecache = array();

		require_once(DIR . '/includes/functions_editor.php');

		$photoplog_catid_pass = 10864246810 + $photoplog_file_catid;
		if ($photoplog_file_catid < 0)
		{
			$photoplog_catid_pass = 10864246810;
		}

		// yep this is how catid is passed in
		$editorid = construct_edit_toolbar('', $do_html, 'nonforum', $do_smilies, $photoplog_catid_pass);

		$photoplog['upload_box'] = $vbphrase['photoplog_not_available'];
		$photoplog_box_count = intval($vbulletin->options['photoplog_box_count']);
		if ($photoplog_box_count > 0)
		{
			$photoplog_box_cnt = 0;
			if ($photoplog_box_cnt < $photoplog_box_count)
			{
				$photoplog['upload_box'] = "<span class=\"smallfont\">".
						$vbphrase['photoplog_upload_file_from_computer'].
						"</span>";
			}
			while ($photoplog_box_cnt < $photoplog_box_count)
			{
				$photoplog_box_cnt++;
				$photoplog['upload_box'] .= "<br /><input class=\"bginput\" type=\"file\" name=\"userfile[]\" size=\"30\" />";
			}
		}

		$photoplog_url_count = intval($vbulletin->options['photoplog_url_count']);
		if ($photoplog_url_count > 0)
		{
			$photoplog_url_cnt = 0;
			if ($photoplog_url_cnt < $photoplog_url_count)
			{
				if ($photoplog_box_count > 0)
				{
					$photoplog['upload_box'] .= "<br /><br /><span class=\"smallfont\">".
							$vbphrase['photoplog_upload_file_from_url_link'].
							"</span>";
				}
				else
				{
					$photoplog['upload_box'] = "<span class=\"smallfont\">".
							$vbphrase['photoplog_upload_file_from_url_link'].
							"</span>";
				}
			}
			while ($photoplog_url_cnt < $photoplog_url_count)
			{
				$photoplog_url_cnt++;
				$photoplog['upload_box'] .= "<br /><input class=\"bginput\" type=\"text\" name=\"userlink[]\" size=\"40\" />";
			}
		}

		($hook = vBulletinHook::fetch_hook('photoplog_upload_fields')) ? eval($hook) : false;

		$photoplog['title'] = '';
		eval('$photoplog_field_title = "' . fetch_template('photoplog_field_title') . '";');
		eval('$photoplog_field_description = "' . fetch_template('photoplog_field_description') . '";');

		$photoplog_custom_field = '';
		photoplog_make_custom_fields($photoplog_file_catid);
		$photoplog['catid'] = $photoplog_file_catid;

		$photoplog['diffuser_row'] = 0;
		if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadasdifferentuser'])
		{
			$photoplog['diffuser_row'] = 1;
		}

		$photoplog['ftp_import'] = '';
		if ($photoplog_ftp_import_flag == 1)
		{
			$photoplog_directory_name = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$vbulletin->userinfo['userid'];
			if (!is_dir($photoplog_directory_name))
			{
				@mkdir($photoplog_directory_name,0777);
				@chmod($photoplog_directory_name,0777);

				if ($photoplog_handle = @fopen($photoplog_directory_name."/index.html","w"))
				{
					$photoplog_blank = '';
					@fwrite($photoplog_handle,$photoplog_blank);
					@fclose($photoplog_handle);
				}
				else
				{
					photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_open']);
				}
			}

			$photoplog_directory_name = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$vbulletin->userinfo['userid']."/ftp";
			if (!is_dir($photoplog_directory_name))
			{
				@mkdir($photoplog_directory_name,0777);
				@chmod($photoplog_directory_name,0777);

				if ($photoplog_handle = @fopen($photoplog_directory_name."/index.html","w"))
				{
					$photoplog_blank = '';
					@fwrite($photoplog_handle,$photoplog_blank);
					@fclose($photoplog_handle);
				}
				else
				{
					photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_open']);
				}
			}

			$photoplog_ftp_directory = $vbulletin->options['photoplog_full_path']."/".$vbulletin->options['photoplog_upload_dir']."/".$vbulletin->userinfo['userid']."/ftp";
			$photoplog['ftp_import'] = "
				<span class=\"smallfont\">
					<div style=\"white-space: nowrap;\">
						".$vbphrase['photoplog_perform_ftp_import']."
						<label for=\"rb_1_ftpimport\"><input type=\"radio\" name=\"ftpimport\" id=\"rb_1_ftpimport\" value=\"1\" tabindex=\"1\" />".$vbphrase['photoplog_yes']."</label>
						<label for=\"rb_0_ftpimport\"><input type=\"radio\" name=\"ftpimport\" id=\"rb_0_ftpimport\" value=\"0\" tabindex=\"1\" />".$vbphrase['photoplog_no']."</label>
						<br /><br />
						".$vbphrase['photoplog_how_many_files_at_a_time']."
						<input type=\"text\" class=\"bginput\" name=\"ftpnumber\" value=\"1\" size=\"5\" maxlength=\"3\" />
						<br /><br />
					</div>
					<div style=\"padding-bottom: 12px;\">
						".$vbphrase['photoplog_files_must_be_in_the_following_directory']."<br />
						<strong>".$photoplog_ftp_directory."</strong><br />
					</div>
					<div class=\"highlight\">
						".$vbphrase['photoplog_make_sure_the_files_are_copies']."
					</div>
				</span>
			";
		}

		($hook = vBulletinHook::fetch_hook('photoplog_upload_form')) ? eval($hook) : false;

		photoplog_output_page('photoplog_upload_form', $vbphrase['photoplog_upload_file']);
	}
}

if ($_POST['do'] == 'doupload')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'userid' => TYPE_UINT,
		'useset' => TYPE_BOOL,
		'catid' => TYPE_INT,
		'title' => TYPE_STR,
		'message' => TYPE_STR,
		'customfield' => TYPE_ARRAY,
		'wysiwyg' => TYPE_BOOL,
		'userlink' => TYPE_ARRAY,
		'diffuser' => TYPE_STR,
		'ftpimport' => TYPE_BOOL,
		'ftpnumber' => TYPE_UINT
	));

	$vbulletin->input->clean_array_gpc('f', array(
		'userfile' => TYPE_FILE
	));

	($hook = vBulletinHook::fetch_hook('photoplog_upload_doupload_start')) ? eval($hook) : false;

	$photoplog_file_userid = $vbulletin->GPC['userid'];
	$photoplog_file_useset = $vbulletin->GPC['useset'];
	$photoplog_file_catid = $vbulletin->GPC['catid'];
	$photoplog_file_title = $vbulletin->GPC['title'];
	$photoplog_file_description = $vbulletin->GPC['message'];
	$photoplog_customfield = $vbulletin->GPC['customfield'];
	$photoplog_wysiwyg = $vbulletin->GPC['wysiwyg'];
	$photoplog_userlink = $vbulletin->GPC['userlink'];
	$photoplog_file_diffusername = htmlspecialchars_uni(trim($vbulletin->GPC['diffuser']));
	$photoplog_ftpimport = $vbulletin->GPC['ftpimport'];
	$photoplog_ftpnumber = $vbulletin->GPC['ftpnumber'];
	$photoplog_userfile = $vbulletin->GPC['userfile'];

	$photoplog_fielddata = '';

	$do_html = false;
	if (in_array($photoplog_file_catid,array_keys($photoplog_ds_catopts)))
	{
		$photoplog_categorybit = $photoplog_ds_catopts[$photoplog_file_catid]['options'];
		$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);
		$do_html = ($photoplog_catoptions['allowhtml']) ? true : false;
	}
	else
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no'].' '.$vbphrase['photoplog_category']);
	}

	if ($photoplog_catoptions['actasdivider'])
	{
		$photoplog_file_catid = -999;
	}

	if ($photoplog_file_catid < 0) // do not change this!
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no'].' '.$vbphrase['photoplog_category']);
	}

	$photoplog_ftpcount = 0;
	if ($photoplog_ftpimport && $photoplog_ftpnumber && $photoplog_ftp_import_flag == 1)
	{
		$photoplog_directory_name = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$vbulletin->userinfo['userid']."/ftp";

		if ($photoplog_handle = opendir($photoplog_directory_name))
		{
			while (false !== ($photoplog_ftpfile = readdir($photoplog_handle)))
			{
				if ($photoplog_ftpfile != "." && $photoplog_ftpfile != ".." && $photoplog_ftpfile != 'index.html')
				{
					$photoplog_ftpcount ++;
					break;
				}
			}
			closedir($photoplog_handle);
		}

		if ($photoplog_ftpcount)
		{
			$photoplog_userlink = array();
			$photoplog_userfile = array();

			$photoplog_customfield_hidden = '';
			if (is_array($photoplog_customfield) && count($photoplog_customfield) > 0)
			{
				foreach ($photoplog_customfield AS $photoplog_customfield_key => $photoplog_customfield_val)
				{
					$photoplog_customfield_key = htmlspecialchars_uni(trim($photoplog_customfield_key));
					$photoplog_customfield_val = htmlspecialchars_uni(trim($photoplog_customfield_val));
					$photoplog_customfield_hidden .= '
						<input type="hidden" name="customfield['.$photoplog_customfield_key.']" value="'.$photoplog_customfield_val.'" />
					';
				}
			}

			$photoplog_base_href = "<base href=\"".$vbulletin->options['bburl']."/".$vbulletin->options['forumhome'].".php\" />\n";
			$headinclude = $photoplog_base_href.$headinclude;

			$photoplog_ftpimport_form = $stylevar['htmldoctype'].'
				<html dir="'.$stylevar['textdirection'].'" lang="'.$stylevar['languagecode'].'">
				<head>
				'.$headinclude.'
				<title>'.$vbphrase['photoplog_redirecting'].'</title>
				</head>
				<body>
				<br /><br /><br /><br />
				<form name="ftpimportform" action="'.$photoplog['location'].'/upload.php" method="post">
				<input type="hidden" name="securitytoken" value="'.$vbulletin->userinfo['securitytoken'].'" />
				<input type="hidden" name="s" value="'.$vbulletin->session->vars['sessionhash'].'" />
				<input type="hidden" name="do" value="doupload" />
				<input type="hidden" name="userid" value="'.$photoplog_file_userid.'" />
				<input type="hidden" name="useset" value="'.$photoplog_file_useset.'" />
				<input type="hidden" name="catid" value="'.$photoplog_file_catid.'" />
				<input type="hidden" name="title" value="'.htmlspecialchars_uni($photoplog_file_title).'" />
				<input type="hidden" name="message" value="'.htmlspecialchars_uni($photoplog_file_description).'" />
				'.$photoplog_customfield_hidden.'
				<input type="hidden" name="wysiwyg" value="'.$photoplog_wysiwyg.'" />
				<input type="hidden" name="diffuser" value="'.$photoplog_file_diffusername.'" />
				<input type="hidden" name="ftpimport" value="'.$photoplog_ftpimport.'" />
				<input type="hidden" name="ftpnumber" value="'.$photoplog_ftpnumber.'" />
				<table class="tborder" cellpadding="'.$stylevar['cellpadding'].'" cellspacing="'.$stylevar['cellspacing'].'" border="0" width="70%" align="center">
				<tr>
					<td class="tcat">'.$vbphrase['photoplog_ftp_import'].'</td>
				</tr>
				<tr>
					<td class="panelsurround" align="center">
						<div class="panel">
						<blockquote>
						<strong>'.$vbphrase['photoplog_ftp_import'].': '.$vbphrase['photoplog_redirecting'].'</strong>
						<span id="click1" class="smallfont"><br /><br />'.$vbphrase['photoplog_click_the_continue_button'].'<br /><br /></span>
						<input id="click2" class="button" type="submit" value="'.$vbphrase['photoplog_continue'].'" />
						</blockquote>
						</div>
					</td>
				</tr>
				</table>
				</form>
				<script type="text/javascript">
				<!--
					fetch_object(\'click1\').style.display = \'none\';
					fetch_object(\'click2\').style.display = \'none\';
					function photoplog_submit_form()
					{
						document.forms.ftpimportform.submit();
					}
					photoplog_submit_form();
				//-->
				</script>
				</body>
				</html>';
		}
		else
		{
			$photoplog_url = $photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q'];
			exec_header_redirect($photoplog_url);
			exit();
		}
	}
	else if ($photoplog_ftpimport && !$photoplog_ftpnumber && $photoplog_ftp_import_flag == 1)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_ftp_number']);
	}

	$photoplog_diffuser_flag = 0;
	if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadasdifferentuser'])
	{
		$photoplog_diffuser_flag = 1;
	}

	$photoplog_file_diffuserid = 0;
	if ($photoplog_diffuser_flag && vbstrlen($photoplog_file_diffusername) > 0)
	{
		$photoplog_diffuser_query = $db->query_first("SELECT userid,username
			FROM ".TABLE_PREFIX."user
			WHERE username = '".$db->escape_string($photoplog_file_diffusername)."'
		");
		if ($photoplog_diffuser_query)
		{
			$photoplog_file_diffuserid = intval($photoplog_diffuser_query['userid']);
			$photoplog_file_diffusername = strval($photoplog_diffuser_query['username']);
		}
		else
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_user_by_that_name']);
		}
		$db->free_result($photoplog_diffuser_query);
	}

	// photoplog_file_userid set via hidden field and checked below
	$photoplog_file_username = $vbulletin->userinfo['username'];
	$photoplog_diffuser_check = 0;

	if ($photoplog_diffuser_flag && $photoplog_file_diffuserid)
	{
		$photoplog_file_userid = $photoplog_file_diffuserid;
		$photoplog_file_username = $photoplog_file_diffusername;
		$photoplog_diffuser_check = 1;
	}

	$photoplog_error_phrase = photoplog_clean_and_validate_customfield($photoplog_customfield,$photoplog_file_catid,true);
	if ($photoplog_error_phrase)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$photoplog_error_phrase);
	}
	$photoplog_fielddata = serialize($photoplog_customfield);

	if ($photoplog_wysiwyg)
	{
		require_once(DIR . '/includes/functions_wysiwyg.php');

		$photoplog_file_description = str_replace($vbulletin->options['bburl']."/images/smilies/","images/smilies/",$photoplog_file_description);
		$photoplog_file_description = convert_wysiwyg_html_to_bbcode($photoplog_file_description, $do_html);
	}

	$photoplog_id = 0;
	$photoplog_upload_cnt = 0;

	$photoplog_box_count = max(0, intval($vbulletin->options['photoplog_box_count']));
	$photoplog_url_count = max(0, intval($vbulletin->options['photoplog_url_count']));
	$photoplog_admin_count = $photoplog_box_count + $photoplog_url_count;

	$photoplog_first_setid = '';
	$photoplog_setid_array = array();

	@ini_set('user_agent','PHP');
	$photoplog_userlink_count = count($photoplog_userlink);

	$photoplog_urlftpflag = 0;
	$photoplog_file_error = 1;

	for ($i=0; $i<$photoplog_userlink_count; $i++)
	{
		$photoplog_urlftpflag = 0;
		$photoplog_file_error = 1;
		$photoplog_urllink = str_replace(array(' ','..'), array('+',''), $photoplog_userlink[$i]);

		if (eregi('^(http|ftp)s?://[^./]+\.[^.]+.*/.+(\.(gif|jpeg|jpg|png))$',$photoplog_urllink))
		{
			$photoplog_parse_url = @parse_url($photoplog_urllink);
			$photoplog_file_check = @getimagesize($photoplog_urllink);

			$photoplog_file_name = photoplog_strip_text(trim(basename($photoplog_parse_url['path'])));

			if (
				!empty($photoplog_file_check) && is_array($photoplog_file_check) 
					&& 
				!empty($photoplog_file_name) && eregi(".+\.(gif|jpeg|jpg|png)$",$photoplog_file_name)
			)
			{
				if (!in_array($photoplog_file_check[2],array(1,2,3)))
				{
					photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_file']);
				}

				$photoplog_file_type = htmlspecialchars_uni($photoplog_file_check['mime']);
				$photoplog_file_tmp_name = '';
				$photoplog_file_error = 1;

				require_once(DIR . '/includes/class_upload.php');

				$photoplog_class_upload = new vB_Upload_Abstract($vbulletin);
				$photoplog_file_size = intval($photoplog_class_upload->fetch_remote_filesize($photoplog_urllink));

				if (
					!$photoplog_file_size
						||
					$permissions['photoplogmaxfilesize']
						&&
					$photoplog_file_size > intval($permissions['photoplogmaxfilesize'])
				)
				{
					photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_file']);
				}

				$photoplog['dimensions'] = '975313579 x 135797531';
				if ($photoplog_file_check[0] && $photoplog_file_check[1])
				{
					$photoplog['dimensions'] = $photoplog_file_check[0].' x '.$photoplog_file_check[1];
				}

				$photoplog_directory_name = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_userid;
				if (!is_dir($photoplog_directory_name))
				{
					@mkdir($photoplog_directory_name,0777);
					@chmod($photoplog_directory_name,0777);

					if ($photoplog_handle = @fopen($photoplog_directory_name."/index.html","w"))
					{
						$photoplog_blank = '';
						@fwrite($photoplog_handle,$photoplog_blank);
						@fclose($photoplog_handle);
					}
					else
					{
						photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_open']);
					}
				}

				$photoplog_directory_name = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_userid."/url";
				if (!is_dir($photoplog_directory_name))
				{
					@mkdir($photoplog_directory_name,0777);
					@chmod($photoplog_directory_name,0777);

					if ($photoplog_handle = @fopen($photoplog_directory_name."/index.html","w"))
					{
						$photoplog_blank = '';
						@fwrite($photoplog_handle,$photoplog_blank);
						@fclose($photoplog_handle);
					}
					else
					{
						photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_open']);
					}
				}

				$photoplog_counter = 1;
				$photoplog_file_name = $photoplog_counter."_".$photoplog_file_name;
				$photoplog_file_location = $photoplog_directory_name."/".$photoplog_file_name;

				while (file_exists($photoplog_file_location) && $photoplog_counter <= 500)
				{
					$photoplog_counter++;
					$photoplog_file_name = $photoplog_counter."_".eregi_replace("^[0-9]+[_]","",$photoplog_file_name);
					$photoplog_file_location = $photoplog_directory_name."/".$photoplog_file_name;
				}

				if ($photoplog_counter > 500)
				{
					photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_name']);
				}

				$photoplog_urlstring = '';
				if (in_array($photoplog_file_check[2],array(1,2,3)) && eregi(".+\.(gif|jpeg|jpg|png)$",$photoplog_urllink))
				{
					$photoplog_urlstring = @file_get_contents($photoplog_urllink);
				}
				if (!$photoplog_urlstring)
				{
					photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_name']);
				}

				if ($photoplog_handle = @fopen($photoplog_file_location,"wb"))
				{
					@fwrite($photoplog_handle,$photoplog_urlstring);
					@fclose($photoplog_handle);
					unset($photoplog_urlstring);
					$photoplog_file_tmp_name = $photoplog_file_location;
				}
				else
				{
					photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_open']);
				}

				$photoplog_file_check = @getimagesize($photoplog_file_tmp_name);

				if (
					$photoplog_file_check === false
						||
					!is_array($photoplog_file_check)
						||
					empty($photoplog_file_check)
						||
					!in_array($photoplog_file_check[2],array(1,2,3))
						||
					!eregi("\.(gif|jpeg|jpg|png)$",$photoplog_file_tmp_name)
				)
				{
					@unlink($photoplog_file_tmp_name);

					photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_type']);
				}
				else
				{
					$photoplog_urlftpflag = 1;
					$photoplog_file_error = 0;
				}
			}
		}

		if ($photoplog_urlftpflag == 1)
		{
			$photoplog_current_count = count($photoplog_userfile['name']);

			$photoplog_userfile['name'][$photoplog_current_count] = trim(strval($photoplog_file_name));
			$photoplog_userfile['type'][$photoplog_current_count] = trim(strval($photoplog_file_type));
			$photoplog_userfile['tmp_name'][$photoplog_current_count] = trim(strval($photoplog_file_tmp_name));
			$photoplog_userfile['error'][$photoplog_current_count] = intval($photoplog_file_error);
			$photoplog_userfile['size'][$photoplog_current_count] = intval($photoplog_file_size);

			$photoplog_userfile['urlftpflag'][$photoplog_current_count] = intval($photoplog_urlftpflag);
			$photoplog_userfile['dimmensions'][$photoplog_current_count] = strval($photoplog['dimensions']);
		}
	}

	$photoplog_urlftpflag = 0;
	$photoplog_file_error = 1;

	if ($photoplog_ftpimport && $photoplog_ftpnumber && $photoplog_ftpcount && $photoplog_ftp_import_flag == 1)
	{
		$photoplog_directory_name = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$vbulletin->userinfo['userid']."/ftp";
		$photoplog_ftpcontinue = 0;

		if ($photoplog_handle = opendir($photoplog_directory_name))
		{
			while ((false !== ($photoplog_ftpfile = readdir($photoplog_handle))) && ($photoplog_ftpcontinue < $photoplog_ftpnumber))
			{
				$photoplog_urlftpflag = 0;
				$photoplog_file_error = 1;

				if ($photoplog_ftpfile != "." && $photoplog_ftpfile != ".." && $photoplog_ftpfile != 'index.html')
				{
					$photoplog_ftpcontinue ++;

					$photoplog_file_check = @getimagesize($photoplog_directory_name.'/'.$photoplog_ftpfile);
					$photoplog_file_size = @filesize($photoplog_directory_name.'/'.$photoplog_ftpfile);
					$photoplog_file_type = htmlspecialchars_uni($photoplog_file_check['mime']);

					if (
						$photoplog_file_check === false
							||
						!is_array($photoplog_file_check)
							||
						empty($photoplog_file_check)
							||
						!in_array($photoplog_file_check[2],array(1,2,3))
							||
						!eregi("\.(gif|jpeg|jpg|png)$",$photoplog_ftpfile)
							||
						!in_array($photoplog_file_type, array('image/gif','image/jpeg','image/jpg','image/pjpeg','image/png','image/x-png'))
							||
						!$photoplog_file_size
							||
						$permissions['photoplogmaxfilesize']
							&&
						$photoplog_file_size > intval($permissions['photoplogmaxfilesize'])
					)
					{
						@unlink($photoplog_directory_name.'/'.$photoplog_ftpfile);
					}
					else
					{
						$photoplog_urlftpflag = 1;
						$photoplog_file_error = 0;

						$photoplog_file_name = '1_'.photoplog_strip_text($photoplog_ftpfile);
						$photoplog_file_tmp_name = $photoplog_directory_name."/".$photoplog_ftpfile;
						$photoplog['dimensions'] = '975313579 x 135797531';
						if ($photoplog_file_check[0] && $photoplog_file_check[1])
						{
							$photoplog['dimensions'] = $photoplog_file_check[0].' x '.$photoplog_file_check[1];
						}
					}
				}

				if ($photoplog_urlftpflag == 1)
				{
					$photoplog_userfile['name'][] = trim(strval($photoplog_file_name));
					$photoplog_userfile['type'][] = trim(strval($photoplog_file_type));
					$photoplog_userfile['tmp_name'][] = trim(strval($photoplog_file_tmp_name));
					$photoplog_userfile['error'][] = intval($photoplog_file_error);
					$photoplog_userfile['size'][] = intval($photoplog_file_size);

					$photoplog_userfile['urlftpflag'][] = intval($photoplog_urlftpflag);
					$photoplog_userfile['dimmensions'][] = strval($photoplog['dimensions']);
				}
			}
			closedir($photoplog_handle);
		}
	}

	$photoplog_upload_count = count($photoplog_userfile['name']);
	$photoplog_min_count = min($photoplog_admin_count,$photoplog_upload_count);
	if ($photoplog_ftpimport && $photoplog_ftpnumber && $photoplog_ftpcount && $photoplog_ftp_import_flag == 1)
	{
		$photoplog_min_count = $photoplog_upload_count;
	}

	if ($photoplog_min_count)
	{
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

		for ($i=0; $i<$photoplog_min_count; $i++)
		{
			$photoplog_file_name = photoplog_strip_text(trim($photoplog_userfile['name'][$i]));
			$photoplog_file_type = htmlspecialchars_uni($photoplog_userfile['type'][$i]);
			$photoplog_file_tmp_name = htmlspecialchars_uni($photoplog_userfile['tmp_name'][$i]);
			$photoplog_file_error = intval($photoplog_userfile['error'][$i]);
			$photoplog_file_size = intval($photoplog_userfile['size'][$i]);

			$photoplog_urlftpflag = isset($photoplog_userfile['urlftpflag'][$i]) ? $photoplog_userfile['urlftpflag'][$i] : 0;

			if ($photoplog_file_name)
			{
				if (
					(($photoplog_file_userid != $vbulletin->userinfo['userid']) && !$photoplog_diffuser_check)
						||
					!eregi("\.(gif|jpeg|jpg|png)$",$photoplog_file_name)
						||
					(!is_uploaded_file($photoplog_file_tmp_name) && $photoplog_urlftpflag == 0)
						||
					$photoplog_file_error
						||
					!$photoplog_file_size
						||
					(
						$permissions['photoplogmaxfilesize']
							&&
						$photoplog_file_size > intval($permissions['photoplogmaxfilesize'])
					)
						||
					!in_array($photoplog_file_type, array('image/gif','image/jpeg','image/jpg','image/pjpeg','image/png','image/x-png'))
				)
				{
					@unlink($photoplog_file_tmp_name);

					photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_file']);
				}
				else
				{
					$photoplog['dimensions'] = '975313579 x 135797531';
					if (isset($photoplog_userfile['dimmensions'][$i]))
					{
						$photoplog['dimensions'] = $photoplog_userfile['dimmensions'][$i];
					}
					else
					{
						$photoplog_dim_array = @getimagesize($photoplog_userfile['tmp_name'][$i]);
						if (!empty($photoplog_dim_array) && $photoplog_dim_array[0] && $photoplog_dim_array[1])
						{
							$photoplog['dimensions'] = $photoplog_dim_array[0].' x '.$photoplog_dim_array[1]; // w x h
						}
					}

					$photoplog_directory_name = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_userid;

					if (!is_dir($photoplog_directory_name))
					{
						@mkdir($photoplog_directory_name,0777);
						@chmod($photoplog_directory_name,0777);

						if ($photoplog_handle = @fopen($photoplog_directory_name."/index.html","w"))
						{
							$photoplog_blank = '';
							@fwrite($photoplog_handle,$photoplog_blank);
							@fclose($photoplog_handle);
						}
						else
						{
							photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_open']);
						}
					}

					$photoplog_counter = 1;
					if ($photoplog_urlftpflag == 0)
					{
						$photoplog_file_name = $photoplog_counter."_".$photoplog_file_name;
					}
					$photoplog_file_location = $photoplog_directory_name."/".$photoplog_file_name;

					while (file_exists($photoplog_file_location) && $photoplog_counter <= 500)
					{
						$photoplog_counter++;
						$photoplog_file_name = $photoplog_counter."_".eregi_replace("^[0-9]+[_]","",$photoplog_file_name);
						$photoplog_file_location = $photoplog_directory_name."/".$photoplog_file_name;
					}

					if ($photoplog_counter <= 500)
					{
						$photoplog_file_check = @getimagesize($photoplog_file_tmp_name);

						if (
							$photoplog_file_check === false
								||
							!is_array($photoplog_file_check)
								||
							empty($photoplog_file_check)
								||
							!in_array($photoplog_file_check[2],array(1,2,3))
						)
						{
							@unlink($photoplog_file_tmp_name);

							photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_type']);
						}

						if ($photoplog_urlftpflag == 1)
						{
							rename($photoplog_file_tmp_name,$photoplog_file_location);
						}
						else
						{
							move_uploaded_file($photoplog_file_tmp_name,$photoplog_file_location);
							@chmod($photoplog_file_location,0644);
						}

						$photoplog_file_check = @getimagesize($photoplog_file_location);

						if (
							$photoplog_file_check === false
								||
							!is_array($photoplog_file_check)
								||
							empty($photoplog_file_check)
								||
							!in_array($photoplog_file_check[2],array(1,2,3))
						)
						{
							@unlink($photoplog_file_location);

							photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_type']);
						}
						else
						{
							$photoplog_large_thumb = photoplog_create_thumbs(
								$photoplog_file_check, $photoplog_directory_name, $photoplog_file_name,
								$vbulletin->options['photoplog_large_size'],
								$vbulletin->options['photoplog_jpg_quality'], 'large'
							);
							$photoplog_medium_thumb = photoplog_create_thumbs(
								$photoplog_file_check, $photoplog_directory_name, $photoplog_file_name,
								$vbulletin->options['photoplog_medium_size'],
								$vbulletin->options['photoplog_jpg_quality'], 'medium'
							);
							$photoplog_small_thumb = photoplog_create_thumbs(
								$photoplog_file_check, $photoplog_directory_name, $photoplog_file_name,
								$vbulletin->options['photoplog_small_size'],
								$vbulletin->options['photoplog_jpg_quality'], 'small'
							);

							if (
								$photoplog_large_thumb === false
									||
								$photoplog_medium_thumb === false
									||
								$photoplog_small_thumb === false
							)
							{
								@unlink($photoplog_file_location);
								@unlink($photoplog_directory_name."/large/".$photoplog_file_name);
								@unlink($photoplog_directory_name."/medium/".$photoplog_file_name);
								@unlink($photoplog_directory_name."/small/".$photoplog_file_name);

								photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_save']);
							}
							else
							{
								$photoplog_exifinfo = array();

								if ($vbulletin->options['photoplog_exifinfo_active'] && $vbulletin->options['photoplog_jhead_path'] && eregi('\.(jpeg|jpg)$',$photoplog_file_name))
								{
									if (is_file($vbulletin->options['photoplog_jhead_path']))
									{
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
									}
								}

								$photoplog_exifinfo = serialize($photoplog_exifinfo);
							}
						}
					}
					else
					{
						photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_name']);
					}

					$photoplog_file_moderate = 1;
					if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadunmoderatedfiles'])
					{
						$photoplog_file_moderate = 0;
					}

					if (vbstrlen($photoplog_file_title) == 0 && !defined('PHOTOPLOG_USEFILENAME'))
					{
						define('PHOTOPLOG_USEFILENAME', true);
					}

					if (defined('PHOTOPLOG_USEFILENAME'))
					{
						$photoplog_file_title = eregi_replace("^[0-9]+[_]","",$photoplog_file_name);
					}

					($hook = vBulletinHook::fetch_hook('photoplog_upload_sqlinsert')) ? eval($hook) : false;

					if (
						$db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
							(userid, username, title, description, filename, filesize, dateline, catid, moderate, dimensions, fielddata, exifinfo)
							VALUES (
								".intval($photoplog_file_userid).",
								'".$db->escape_string($photoplog_file_username)."',
								'".$db->escape_string($photoplog_file_title)."',
								'".$db->escape_string($photoplog_file_description)."',
								'".$db->escape_string($photoplog_file_name)."',
								".intval($photoplog_file_size).",
								".intval(TIMENOW).",
								".intval($photoplog_file_catid).",
								".intval($photoplog_file_moderate).",
								'".$db->escape_string($photoplog['dimensions'])."',
								'".$db->escape_string($photoplog_fielddata)."',
								'".$db->escape_string($photoplog_exifinfo)."'
							)
						")
					)
					{
						$photoplog_upload_cnt++;
						$photoplog_id = $db->insert_id();

						$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
							SET num_uploads = num_uploads + 1,
							last_upload_dateline = ".intval(TIMENOW).",
							last_upload_id = ".intval($photoplog_id).",
							sum_filesize = sum_filesize + ".intval($photoplog_file_size)."
							WHERE catid = ".intval($photoplog_file_catid)."
							AND moderate >= ".intval($photoplog_file_moderate)."
						");
						$db->query_write("UPDATE " . TABLE_PREFIX . "user
							SET photoplog_filecount = photoplog_filecount + 1
							WHERE userid = ".intval($photoplog_file_userid)."
						");

						if ($photoplog_file_moderate == 1 && $vbulletin->options['photoplog_admin_email'])
						{
							$photoplog_subject = $photoplog_message = '';
							eval(fetch_email_phrases('photoplog_mod_file', -1, '', 'photoplog_'));
							vbmail($vbulletin->options['webmasteremail'], $photoplog_subject, $photoplog_message, true);
						}

						if (!$photoplog_first_setid)
						{
							$photoplog_first_setid = intval($photoplog_id);
						}
						$photoplog_setid_array[] = intval($photoplog_id);
					}
					else
					{
						photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_luck']);
					}
				}
			}
		}

		if (!$photoplog_upload_cnt)
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_file']);
		}
		else
		{
			if ($photoplog_file_useset && $photoplog_upload_cnt > 1 && $photoplog_first_setid)
			{
				$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					SET setid = ".intval($photoplog_first_setid)."
					WHERE fileid IN (".implode(",",$photoplog_setid_array).")
				");
			}
			else
			{
				$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					SET setid = fileid
					WHERE fileid IN (".implode(",",$photoplog_setid_array).")
				");
			}

			if ($photoplog_ftpimport && $photoplog_ftpnumber && $photoplog_ftpcount && $photoplog_ftp_import_flag == 1)
			{
				($hook = vBulletinHook::fetch_hook('photoplog_upload_ftpimportform')) ? eval($hook) : false;

				echo $photoplog_ftpimport_form;
				exit();
			}
			else
			{
				($hook = vBulletinHook::fetch_hook('photoplog_upload_doupload_complete')) ? eval($hook) : false;

				$photoplog_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog_id;
				exec_header_redirect($photoplog_url);
				exit();
			}
		}
	}
	else
	{
		$photoplog_ftpcount = 0;
		if ($photoplog_ftpimport && $photoplog_ftpnumber && $photoplog_ftp_import_flag == 1)
		{
			$photoplog_directory_name = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$vbulletin->userinfo['userid']."/ftp";

			if ($photoplog_handle = opendir($photoplog_directory_name))
			{
				while (false !== ($photoplog_ftpfile = readdir($photoplog_handle)))
				{
					if ($photoplog_ftpfile != "." && $photoplog_ftpfile != ".." && $photoplog_ftpfile != 'index.html')
					{
						$photoplog_ftpcount ++;
						break;
					}
				}
				closedir($photoplog_handle);
			}
		}

		if ($photoplog_ftpimport && $photoplog_ftpnumber && $photoplog_ftpcount && $photoplog_ftp_import_flag == 1)
		{
			($hook = vBulletinHook::fetch_hook('photoplog_upload_ftpimportform')) ? eval($hook) : false;

			echo $photoplog_ftpimport_form;
			exit();
		}
		else
		{
			$photoplog_url = $photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q'];
			exec_header_redirect($photoplog_url);
			exit();
		}
	}
}

($hook = vBulletinHook::fetch_hook('photoplog_upload_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'upload' && $_POST['do'] != 'doupload')
{
	photoplog_index_bounce();
}

?>