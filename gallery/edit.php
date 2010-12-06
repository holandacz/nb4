<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','edit');
define('PHOTOPLOG_LEVEL','edit');
define('GET_EDIT_TEMPLATES', true);
require_once('./settings.php');

// ############################ Start Edit Page ###########################
($hook = vBulletinHook::fetch_hook('photoplog_edit_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'edit';
}

$photoplog_block_category = 0;
if ($photoplog_perm_catid && $permissions['photoplogmaxfilelimit'])
{

	$photoplog_cnt_checks = $db->query_first("SELECT COUNT(*) as cnt
				FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				WHERE userid = ".intval($vbulletin->userinfo['userid'])."
				AND catid = ".intval($photoplog_perm_catid)."
	");
	if 	(
			$photoplog_cnt_checks
				&&
			(intval($photoplog_cnt_checks['cnt']) >= $permissions['photoplogmaxfilelimit'])
		)
	{
		$photoplog_block_category = 1;
	}
}

if ($_REQUEST['do'] == 'edit')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'n' => TYPE_UINT
	));
	$vbulletin->input->clean_array_gpc('p', array(
		'catid' => TYPE_UINT,
		'fileid' => TYPE_UINT
	));

	$photoplog_file_id = max($vbulletin->GPC['n'],$vbulletin->GPC['fileid']);
	$photoplog_file_catid = $vbulletin->GPC['catid'];

	if (in_array($photoplog_file_catid,$photoplog_perm_not_allowed_bits))
	{
		$photoplog_file_catid = -1;
	}

	if (!in_array($photoplog_file_catid,array_keys($photoplog_ds_catopts)))
	{
		$photoplog_file_catid = -1;
	}

	$photoplog_file_info = '';
	if (!$photoplog_file_info_links && $photoplog_file_id)
	{
		$photoplog_file_info = $db->query_first("SELECT userid,fileid,
				catid,filename,title,description,fielddata
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
		if (($photoplog_file_info['userid'] == $vbulletin->userinfo['userid']) || defined('PHOTOPLOG_USER5') || defined('PHOTOPLOG_USER6'))
		{
			$photoplog['fileid'] = intval($photoplog_file_info['fileid']);
			$photoplog_file_catid_default = intval($photoplog_file_info['catid']);
			$photoplog['userid'] = intval($photoplog_file_info['userid']);
			$photoplog['filename'] = strval($photoplog_file_info['filename']);

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
				$photoplog_list_categories_row = $photoplog_list_categories;
				$photoplog_list_categories_row[-1] = $vbphrase['photoplog_select_one'];
				if (!empty($photoplog_perm_not_allowed_bits))
				{
					array_walk($photoplog_list_categories_row, 'photoplog_append_key', '');
					$photoplog_list_categories_row = array_flip(array_diff(array_flip($photoplog_list_categories_row),$photoplog_perm_not_allowed_bits));
					array_walk($photoplog_list_categories_row, 'photoplog_remove_key', '');
				}

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

				($hook = vBulletinHook::fetch_hook('photoplog_edit_selectcategory')) ? eval($hook) : false;

				photoplog_output_page('photoplog_edit_select_category', $vbphrase['photoplog_edit_file']);
			}

			if ($photoplog_block_category && ($photoplog_file_catid != $photoplog_file_catid_default))
			{
				photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_max']);
			}

			$photoplog['select_row'] = htmlspecialchars_uni(photoplog_get_category_title($photoplog_file_catid));

			$photoplog['title'] = $photoplog_file_info['title'];
			$photoplog['title'] = photoplog_process_text($photoplog['title'], $photoplog_file_catid, true, false);
			if ($photoplog['title'] == $vbphrase['photoplog_untitled'])
			{
				$photoplog['title'] = '';
			}

			$photoplog['description'] = $photoplog_file_info['description'];
			$photoplog['description'] = photoplog_process_text($photoplog['description'], $photoplog_file_catid, false, false);

			$photoplog_fielddata = $photoplog_file_info['fielddata'];
			$photoplog_fielddata = ($photoplog_fielddata == '') ? array() : unserialize($photoplog_fielddata);
			if (!is_array($photoplog_fielddata))
			{
				$photoplog_fielddata = array();
			}

			$photoplog['maxfilesize'] = intval($permissions['photoplogmaxfilesize']);
			$photoplog['maxfilesize'] = vb_number_format($photoplog['maxfilesize'],1,true);

			$do_html = 0;
			$do_smilies = 0;
			$do_bbcode = 0;
			$do_imgcode = 0;

			if ($photoplog_file_catid > 0 && in_array($photoplog_file_catid,array_keys($photoplog_ds_catopts)))
			{
				$photoplog_categorybit = $photoplog_ds_catopts["$photoplog_file_catid"]['options'];
				$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

				$do_html = ($photoplog_catoptions['allowhtml']) ? 1 : 0;
				$do_smilies = ($photoplog_catoptions['allowsmilies']) ? 1 : 0;
				$do_bbcode = ($photoplog_catoptions['allowbbcode']) ? 1 : 0;

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

			// yep this is how fileid is passed in
			$editorid = construct_edit_toolbar('', $do_html, 'nonforum', $do_smilies, $photoplog['fileid']);

			$photoplog['upload_box'] = $vbphrase['photoplog_not_available'];
			$photoplog_box_count = intval($vbulletin->options['photoplog_box_count']);
			if ($photoplog_box_count > 0)
			{
				$photoplog['upload_box'] = "<span class=\"smallfont\">".
						$vbphrase['photoplog_upload_file_from_computer'].
						"</span>";
				$photoplog['upload_box'] .= "<br /><input class=\"bginput\" type=\"file\" name=\"userfile[]\" size=\"30\" />";
			}

			$photoplog_url_count = intval($vbulletin->options['photoplog_url_count']);
			if ($photoplog_url_count > 0 && $photoplog_box_count > 0)
			{
				$photoplog['upload_box'] .= "<br /><br /><span class=\"smallfont\"><strong>".
						$vbphrase['photoplog_or']."</strong> ".
						$vbphrase['photoplog_upload_file_from_url_link'].
						"</span>";
				$photoplog['upload_box'] .= "<br /><input class=\"bginput\" type=\"text\" name=\"userlink\" size=\"40\" />";
			}
			else if ($photoplog_url_count > 0)
			{
				$photoplog['upload_box'] = "<span class=\"smallfont\">".
						$vbphrase['photoplog_upload_file_from_url_link'].
						"</span>";
				$photoplog['upload_box'] .= "<br /><input class=\"bginput\" type=\"text\" name=\"userlink\" size=\"40\" />";
			}

			$photoplog_toolbartype = $do_bbcode ? is_wysiwyg_compatible(-1, 'fe') : 0;
			if ($photoplog_toolbartype != 2)
			{
				$photoplog['description'] = $photoplog_file_info['description'];
			}

			// special character and link stuff for the editors
			$photoplog['description'] = str_replace('src="images/smilies/', 'src="'.$vbulletin->options['bburl'].'/images/smilies/', $photoplog['description']);
			$photoplog['description'] = htmlspecialchars_uni($photoplog['description']);

			// yep this is how to get the description in
			$messagearea = str_replace("</textarea>",$photoplog['description']."</textarea>",$messagearea);

			($hook = vBulletinHook::fetch_hook('photoplog_edit_fields')) ? eval($hook) : false;

			eval('$photoplog_field_title = "' . fetch_template('photoplog_field_title') . '";');
			eval('$photoplog_field_description = "' . fetch_template('photoplog_field_description') . '";');

			$photoplog_custom_field = '';
			photoplog_make_custom_fields($photoplog_file_catid,$photoplog_fielddata);
			$photoplog['catid'] = $photoplog_file_catid;

			photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);

			$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_medium_thumb'], 0, 1).'link';
			$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_medium_thumb'], -1, 1).'link';

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

			($hook = vBulletinHook::fetch_hook('photoplog_edit_form')) ? eval($hook) : false;

			photoplog_output_page('photoplog_edit_form', $vbphrase['photoplog_edit_file']);
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

if ($_POST['do'] == 'doedit')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'fileid' => TYPE_UINT,
		'catid' => TYPE_INT,
		'title' => TYPE_STR,
		'message' => TYPE_STR,
		'customfield' => TYPE_ARRAY,
		'wysiwyg' => TYPE_BOOL,
		'userlink' => TYPE_STR
	));

	$vbulletin->input->clean_array_gpc('f', array(
		'userfile' => TYPE_FILE
	));

	($hook = vBulletinHook::fetch_hook('photoplog_edit_doedit_start')) ? eval($hook) : false;

	$photoplog_file_id = $vbulletin->GPC['fileid'];
	$photoplog_file_catid = $vbulletin->GPC['catid'];
	$photoplog_file_catid_default = -101;
	$photoplog_file_title = $vbulletin->GPC['title'];
	$photoplog_file_description = $vbulletin->GPC['message'];
	$photoplog_customfield = $vbulletin->GPC['customfield'];
	$photoplog_wysiwyg = $vbulletin->GPC['wysiwyg'];
	$photoplog_userfile = $vbulletin->GPC['userfile'];
	$photoplog_userlink = $vbulletin->GPC['userlink'];

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

	if ($photoplog_wysiwyg)
	{
		require_once(DIR . '/includes/functions_wysiwyg.php');

		$photoplog_file_description = str_replace($vbulletin->options['bburl']."/images/smilies/","images/smilies/",$photoplog_file_description);
		$photoplog_file_description = convert_wysiwyg_html_to_bbcode($photoplog_file_description, $do_html);
	}

	if (is_array($photoplog_userfile['name']))
	{
		$photoplog_userfile['name'] = $photoplog_userfile['name'][0];
		$photoplog_userfile['type'] = $photoplog_userfile['type'][0];
		$photoplog_userfile['tmp_name'] = $photoplog_userfile['tmp_name'][0];
		$photoplog_userfile['error'] = $photoplog_userfile['error'][0];
		$photoplog_userfile['size'] = $photoplog_userfile['size'][0];
	}

	$photoplog_urlflag = 0;
	$photoplog_file_error = 1;

	if (vbstrlen($photoplog_userlink) > 0)
	{
		@ini_set('user_agent','PHP');

		$photoplog_urlflag = 0;
		$photoplog_file_error = 1;
		$photoplog_urllink = str_replace(array(' ','..'), array('+',''), $photoplog_userlink);

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
					$photoplog_urlflag = 1;
					$photoplog_file_error = 0;
				}
			}
		}

		if ($photoplog_urlflag == 1)
		{
			$photoplog_userfile['name'] = trim(strval($photoplog_file_name));
			$photoplog_userfile['type'] = trim(strval($photoplog_file_type));
			$photoplog_userfile['tmp_name'] = trim(strval($photoplog_file_tmp_name));
			$photoplog_userfile['error'] = intval($photoplog_file_error);
			$photoplog_userfile['size'] = intval($photoplog_file_size);

			$photoplog_userfile['urlflag'] = intval($photoplog_urlflag);
			$photoplog_userfile['dimmensions'] = strval($photoplog['dimensions']);
		}
		else
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_file']);
		}
	}

	$photoplog_file_name = photoplog_strip_text(trim($photoplog_userfile['name']));
	$photoplog_file_type = htmlspecialchars_uni($photoplog_userfile['type']);
	$photoplog_file_tmp_name = htmlspecialchars_uni($photoplog_userfile['tmp_name']);
	$photoplog_file_error = intval($photoplog_userfile['error']);
	$photoplog_file_size = intval($photoplog_userfile['size']);

	$photoplog_urlflag = isset($photoplog_userfile['urlflag']) ? $photoplog_userfile['urlflag'] : 0;

	$photoplog['dimensions'] = '975313579 x 135797531';
	if (isset($photoplog_userfile['dimmensions']))
	{
		$photoplog['dimensions'] = $photoplog_userfile['dimmensions'];
	}
	else
	{
		$photoplog_dim_array = @getimagesize($photoplog_userfile['tmp_name']);
		if (!empty($photoplog_dim_array) && $photoplog_dim_array[0] && $photoplog_dim_array[1])
		{
			$photoplog['dimensions'] = $photoplog_dim_array[0].' x '.$photoplog_dim_array[1]; // w x h
		}
	}

	$photoplog_file_edit = 1;

	if (
		!$photoplog_file_name
			||
		!$photoplog_file_type
			||
		!$photoplog_file_tmp_name
			||
		$photoplog_file_error
			||
		!$photoplog_file_size
	)
	{
		$photoplog_file_edit = 0;
	}

	$photoplog_file_old = '';

	$photoplog_file_info = $db->query_first("SELECT *
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid = ".intval($photoplog_file_id)."
		$photoplog_catid_sql1
		$photoplog_admin_sql1
	");

	if ($photoplog_file_info)
	{
		if (($photoplog_file_info['userid'] != $vbulletin->userinfo['userid']) && !defined('PHOTOPLOG_USER5') && !defined('PHOTOPLOG_USER6'))
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_view']);
		}
		else
		{
			$photoplog_file_catid_default = intval($photoplog_file_info['catid']);
			if ($photoplog_block_category && ($photoplog_file_catid != $photoplog_file_catid_default))
			{
				photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_max']);
			}

			$photoplog_file_old = $photoplog_file_info['filename'];
			$photoplog_file_userid = $photoplog_file_info['userid'];
			$photoplog_file_username = $photoplog_file_info['username'];
			$photoplog_file_dateline = $photoplog_file_info['dateline'];

			$photoplog_file_views = $photoplog_file_info['views'];
			$photoplog_file_nc0 = $photoplog_file_info['num_comments0'];
			$photoplog_file_nc1 = $photoplog_file_info['num_comments1'];
			$photoplog_file_nr0 = $photoplog_file_info['num_ratings0'];
			$photoplog_file_nr1 = $photoplog_file_info['num_ratings1'];
			$photoplog_file_sr0 = $photoplog_file_info['sum_ratings0'];
			$photoplog_file_sr1 = $photoplog_file_info['sum_ratings1'];
			$photoplog_file_lcd0 = $photoplog_file_info['last_comment_dateline0'];
			$photoplog_file_lcd1 = $photoplog_file_info['last_comment_dateline1'];
			$photoplog_file_lci0 = $photoplog_file_info['last_comment_id0'];
			$photoplog_file_lci1 = $photoplog_file_info['last_comment_id1'];

			$photoplog_albumids = $photoplog_file_info['albumids'];
			$photoplog_exifinfo = $photoplog_file_info['exifinfo'];

			if (!$photoplog_file_edit || !$photoplog_file_size)
			{
				$photoplog_file_size = $photoplog_file_info['filesize'];
				$photoplog['dimensions'] = $photoplog_file_info['dimensions'];
			}
			$photoplog_file_moderate = 1;
			if ($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadunmoderatedfiles'])
			{
				$photoplog_file_moderate = 0;
			}
			$photoplog_file_setid = $photoplog_file_info['setid'];

			$photoplog_file_fielddata = $photoplog_file_info['fielddata'];
			$photoplog_file_fielddata = ($photoplog_file_fielddata == '') ? array() : unserialize($photoplog_file_fielddata);
			if (!is_array($photoplog_file_fielddata))
			{
				$photoplog_file_fielddata = array();
			}

			$photoplog_error_phrase = photoplog_clean_and_validate_customfield($photoplog_customfield,$photoplog_file_catid,true,$photoplog_file_fielddata);
			if ($photoplog_error_phrase)
			{
				photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$photoplog_error_phrase);
			}
			$photoplog_fielddata = serialize($photoplog_customfield);
		}
	}
	else
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_view']);
	}

	if ($photoplog_file_edit)
	{
		if (
			(($photoplog_file_userid != $vbulletin->userinfo['userid']) && !defined('PHOTOPLOG_USER5') && !defined('PHOTOPLOG_USER6'))
				||
			!eregi("\.(gif|jpeg|jpg|png)$",$photoplog_file_name)
				||
			(!is_uploaded_file($photoplog_file_tmp_name) && $photoplog_urlflag == 0)
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
			if ($photoplog_urlflag == 0)
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

				if ($photoplog_urlflag == 1)
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
		}
	}

	if (!$photoplog_file_edit && empty($photoplog_file_title) && empty($photoplog_file_description))
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_text']);
	}

	$photoplog_replace_name = $photoplog_file_old;
	if ($photoplog_file_edit)
	{
		$photoplog_replace_name = $photoplog_file_name;
	}

	($hook = vBulletinHook::fetch_hook('photoplog_edit_sqlreplace')) ? eval($hook) : false;

	if (
		$db->query_write("REPLACE INTO " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			(fileid, userid, username, title, description, filename, filesize, dateline, views, catid, moderate, dimensions, setid, 
			fielddata, num_comments0, num_comments1, num_ratings0, num_ratings1, sum_ratings0, sum_ratings1,
			last_comment_dateline0, last_comment_dateline1, last_comment_id0, last_comment_id1, albumids, exifinfo)
			VALUES (
				".intval($photoplog_file_id).",
				".intval($photoplog_file_userid).",
				'".$db->escape_string($photoplog_file_username)."',
				'".$db->escape_string($photoplog_file_title)."',
				'".$db->escape_string($photoplog_file_description)."',
				'".$db->escape_string($photoplog_replace_name)."',
				".intval($photoplog_file_size).",
				".intval($photoplog_file_dateline).",
				".intval($photoplog_file_views).",
				".intval($photoplog_file_catid).",
				".intval($photoplog_file_moderate).",
				'".$db->escape_string($photoplog['dimensions'])."',
				".intval($photoplog_file_setid).",
				'".$db->escape_string($photoplog_fielddata)."',
				".intval($photoplog_file_nc0).",
				".intval($photoplog_file_nc1).",
				".intval($photoplog_file_nr0).",
				".intval($photoplog_file_nr1).",
				".intval($photoplog_file_sr0).",
				".intval($photoplog_file_sr1).",
				".intval($photoplog_file_lcd0).",
				".intval($photoplog_file_lcd1).",
				".intval($photoplog_file_lci0).",
				".intval($photoplog_file_lci1).",
				'".$db->escape_string($photoplog_albumids)."',
				'".$db->escape_string($photoplog_exifinfo)."'
			)
		")
	)
	{
		if ($photoplog_file_catid_default >= 0 && ($photoplog_file_catid != $photoplog_file_catid_default))
		{
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
						SET catid = ".intval($photoplog_file_catid)."
						WHERE fileid = ".intval($photoplog_file_id)."
			");
			photoplog_update_counts_table($photoplog_file_catid_default);
		}
		photoplog_update_counts_table($photoplog_file_catid);

		if ($photoplog_file_moderate == 1 && $vbulletin->options['photoplog_admin_email'])
		{
			$photoplog_subject = $photoplog_message = '';
			eval(fetch_email_phrases('photoplog_mod_file', -1, '', 'photoplog_'));
			vbmail($vbulletin->options['webmasteremail'], $photoplog_subject, $photoplog_message, true);
		}

		if ($photoplog_file_old && $photoplog_file_edit)
		{
			@unlink($photoplog_directory_name."/".$photoplog_file_old);
			@unlink($photoplog_directory_name."/large/".$photoplog_file_old);
			@unlink($photoplog_directory_name."/medium/".$photoplog_file_old);
			@unlink($photoplog_directory_name."/small/".$photoplog_file_old);
		}

		($hook = vBulletinHook::fetch_hook('photoplog_edit_doedit_complete')) ? eval($hook) : false;

		$photoplog_id = intval($photoplog_file_id);
		$photoplog_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'n='.$photoplog_id;
		exec_header_redirect($photoplog_url);
		exit();
	}
	else
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_luck']);
	}
}

($hook = vBulletinHook::fetch_hook('photoplog_edit_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'edit' && $_POST['do'] != 'doedit')
{
	photoplog_index_bounce();
}

?>