<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ################## Start photoplog_create_thumbs ######################
function photoplog_create_thumbs($file_info, $file_dir, $file_name, $new_size, $jpg_qual, $sub_dir)
{
	$file_loc = $file_dir."/".$file_name;
	$old_img = '';
	$size_arr = explode(",",str_replace(" ","",$new_size));

	if (count($size_arr) == 2)
	{
		$new_w = intval($size_arr[0]);
		$new_h = intval($size_arr[1]);
	}
	else
	{
		return false;
	}

	if ($file_info[2] == '1')
	{
		if (imagetypes() & IMG_GIF)
		{
			$old_img = @imagecreatefromgif($file_loc);
		}
	}

	if ($file_info[2] == '2')
	{
		if (imagetypes() & IMG_JPG)
		{
			$old_img = @imagecreatefromjpeg($file_loc);
		}
	}

	if ($file_info[2] == '3')
	{
		if (imagetypes() & IMG_PNG)
		{
			$old_img = @imagecreatefrompng($file_loc);
		}
	}

	if (!$old_img)
	{
		return false;
	}
	else
	{
		$orig_w = max(1,$file_info[0]);
		$orig_h = max(1,$file_info[1]);
		$new_w = max(1,$new_w);
		$new_h = max(1,$new_h);

		$ratio_w = $new_w / $orig_w;
		$ratio_h = $new_h / $orig_h;
		$ratio_m = min($ratio_w,$ratio_h);

		if ($ratio_m >= 1)
		{
			$use_w = $orig_w;
			$use_h = $orig_h;
		}
		else
		{
			$use_w = round($orig_w * $ratio_m, 0);
			$use_h = round($orig_h * $ratio_m, 0);
		}

		if ($file_info[2] == '2' || $file_info[2] == '3')
		{
			$new_img = @imagecreatetruecolor($use_w,$use_h);

			if ($file_info[2] == '3')
			{
				@imagealphablending($new_img, false);
				@imagesavealpha($new_img, true);
				$black = @imagecolorallocate($new_img,0,0,0);
				@imagecolortransparent($new_img,$black);
			}
		}
		else
		{
			$new_img = @imagecreate($use_w,$use_h);
			$black = @imagecolorallocate($new_img,0,0,0);
			@imagecolortransparent($new_img,$black);
		}

		if (!$new_img)
		{
			return false;
		}

		$out_img = @imagecopyresampled($new_img,$old_img,0,0,0,0,$use_w,$use_h,$orig_w,$orig_h);

		if (!$out_img)
		{
			return false;
		}

		$new_dir = $file_dir."/".$sub_dir;

		if (!is_dir($new_dir))
		{
			@mkdir($new_dir,0777);
			@chmod($new_dir,0777);

			if ($photoplog_handle = @fopen($new_dir."/index.html","w"))
			{
				$photoplog_blank = '';
				@fwrite($photoplog_handle,$photoplog_blank);
				@fclose($photoplog_handle);
			}
			else
			{
				return false;
			}
		}

		$file_loc = $new_dir."/".$file_name;

		$response = false;

		if ($file_info[2] == '1')
		{
			$response = imagegif($new_img,$file_loc);
		}

		if ($file_info[2] == '2')
		{
			$jpg_qual = intval($jpg_qual);
			if ($jpg_qual < 0 || $jpg_qual > 100)
			{
				$jpg_qual = 75;
			}
			$response = imagejpeg($new_img,$file_loc,$jpg_qual);
		}

		if ($file_info[2] == '3')
		{
			$response = imagepng($new_img,$file_loc);
		}

		@imagedestroy($old_img);
		@imagedestroy($new_img);

		return $response;
	}
}

// #################### Start photoplog_preg_quote #######################
function photoplog_preg_quote(&$item, $key, $prefix)
{
	$item = '/(' . preg_quote(trim($item), '/') . ')/siU';
}

// #################### Start photoplog_trim_text ########################
function photoplog_trim_text(&$item, $key, $prefix)
{
	$item = trim($item);
}

// #################### Start photoplog_process_text #####################
function photoplog_process_text($text, $catid, $is_title = false, $add_dots = false)
{
	global $vbulletin, $vbphrase, $photoplog_categoryoptions, $photoplog_ds_catopts;
	static $photoplog_parser = false;

	$do_html = false;
	$do_smilies = false;
	$do_bbcode = false;
	$do_imgcode = false;
	$do_parseurl = false;

	$catid = intval($catid);
	if (!is_array($photoplog_ds_catopts))
	{
		$photoplog_ds_catopts = array();
	}
	if (in_array($catid,array_keys($photoplog_ds_catopts)))
	{
		$photoplog_categorybit = $photoplog_ds_catopts[$catid]['options'];
		$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

		$do_html = ($photoplog_catoptions['allowhtml']) ? true : false;
		$do_smilies = ($photoplog_catoptions['allowsmilies']) ? true : false;
		$do_bbcode = ($photoplog_catoptions['allowbbcode']) ? true : false;
		$do_imgcode = ($photoplog_catoptions['allowimgcode']) ? true : false;
		$do_parseurl = ($photoplog_catoptions['allowparseurl']) ? true : false;
	}

	$text = fetch_censored_text($text);
	$text = fetch_word_wrapped_string($text);

	require_once(DIR . '/includes/functions_newpost.php');

	if ($is_title)
	{
		$text = fetch_no_shouting_text($text);

		$max_len = 255;
		if (vbstrlen($text) > $max_len)
		{
			$text = fetch_trimmed_title($text, $max_len);
			$text = photoplog_regexp_text($text);
		}

		if (empty($text))
		{
			$text = $vbphrase['photoplog_untitled'];
		}

		$text = htmlspecialchars_uni($text);

		return $text;
	}

	if ($add_dots)
	{
		$max_len = 100;
		if ($vbulletin->options['lastthreadchars'] != 0)
		{
			$max_len = $vbulletin->options['lastthreadchars'] * 2;
		}
	}
	else
	{
		$max_len = min(vbstrlen($text),15360000);
		if ($vbulletin->options['postmaxchars'] != 0)
		{
			$max_len = $vbulletin->options['postmaxchars'];
		}
	}

	if (vbstrlen($text) > $max_len)
	{
		$text = fetch_trimmed_title($text, $max_len);
		$text = photoplog_regexp_text($text);
	}

	if ($do_parseurl)
	{
		$text = convert_url_to_bbcode($text);
	}

	if (empty($text))
	{
		$text = $vbphrase['photoplog_not_available'];
	}

	$text = fetch_no_shouting_text($text);

	if (!$photoplog_parser)
	{
		require_once(DIR . '/includes/class_bbcode.php');
		$photoplog_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());
	}

	$text = $photoplog_parser->do_parse($text, $do_html, $do_smilies, $do_bbcode, $do_imgcode, true, false);

	return $text;
}

// ##################### Start photoplog_regexp_text #####################
function photoplog_regexp_text($text)
{
	$text = preg_replace("/\[.*\]/isU", " ", $text);
	$text = preg_replace("/<.*>/isU", " ", $text);
	$text = eregi_replace("(<[^>]*)$", "", $text);
	$text = eregi_replace("&[^[:space:]]*[^;]$", "", $text);
	$text = eregi_replace("\[([^]]*)$", "", $text);
	return $text;
}

// ##################### Start photoplog_strip_text ######################
function photoplog_strip_text($text)
{
	($hook = vBulletinHook::fetch_hook('photoplog_functions_striptext')) ? eval($hook) : false;
	$text = eregi_replace("[^a-z0-9._-]","_",$text);
	return $text;
}

// ##################### Start photoplog_list_worker #####################
function photoplog_list_worker(&$list_cats, $parentid, $spacer, &$catids, &$titles, &$parentids)
{
	global $vbphrase;

	if ($parentid < 0)
	{
		$list_cats = array("-1" => $vbphrase['photoplog_no_one']);
	}

	$n = count($catids);
	for ($i=0; $i<$n; $i++)
	{
		if ($parentid == $parentids[$i])
		{
			$list_cats["$catids[$i]"] = $spacer." ".$titles[$i]." ";
			if ($catids[$i] > 0)
			{
				photoplog_list_worker($list_cats, $catids[$i], $spacer.'--', $catids, $titles, $parentids);
			}
		}
	}
}

// ################### Start photoplog_list_categories ###################
function photoplog_list_categories(&$list_cats, $parentid = -1, $spacer = '')
{
	global $vbulletin;

	// ORDER BY parentid,displayorder,catid
	// now done by ALTER TABLE after change
	$categories = $vbulletin->db->query_read("SELECT catid, title, parentid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
	");

	$catids = array();
	$titles = array();
	$parentids = array();

	while ($category = $vbulletin->db->fetch_array($categories))
	{
		$catids[] = intval($category['catid']);
		$titles[] = strval($category['title']);
		$parentids[] = intval($category['parentid']);
	}
	$vbulletin->db->free_result($categories);

	photoplog_list_worker($list_cats, $parentid, $spacer, $catids, $titles, $parentids);
}

// #################### Start photoplog_child_worker #####################
function photoplog_child_worker(&$list_imm, &$list_all, $parid = -1)
{
	$parid = intval($parid);
	$list_all[$parid] = array();

	if (!is_array($list_imm[$parid]))
	{
		$list_imm[$parid] = array();
	}

	foreach ($list_imm[$parid] AS $catid)
	{
		$list_all[$parid][] = intval($catid);

		if (!isset($list_all[$catid]))
		{
			photoplog_child_worker($list_imm, $list_all, $catid);
		}

		$list_all[$parid] = array_merge($list_all[$parid], $list_all[$catid]);
	}
}

// ##################### Start photoplog_child_list ######################
function photoplog_child_list(&$list_imm, &$list_all)
{
	global $vbulletin;

	$categories = $vbulletin->db->query_read("SELECT catid, parentid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
	");

	while ($category = $vbulletin->db->fetch_array($categories))
	{
		$catid = intval($category['catid']);
		$parid = intval($category['parentid']);

		$list_imm[$parid][] = $catid;

		if (!isset($list_imm[$catid]))
		{
			$list_imm[$catid] = array();
		}
	}
	$vbulletin->db->free_result($categories);

	photoplog_child_worker($list_imm, $list_all);
}

// ##################### Start photoplog_child_list_v2 ######################
function photoplog_child_list_v2(&$list_imm, &$list_all, &$list_cats)
{
	global $vbulletin;

	// ORDER BY parentid,displayorder,catid
	// now done by ALTER TABLE after change
	$categories = $vbulletin->db->query_read("SELECT catid, parentid, title
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
	");

	$catids = array();
	$titles = array();
	$parentids = array();
	while ($category = $vbulletin->db->fetch_array($categories))
	{
		$catid = intval($category['catid']);
		$parid = intval($category['parentid']);

		$list_imm[$parid][] = $catid;

		if (!isset($list_imm[$catid]))
		{
			$list_imm[$catid] = array();
		}

		$catids[] = $catid;
		$titles[] = strval($category['title']);
		$parentids[] = $parid;
	}
	$vbulletin->db->free_result($categories);

	photoplog_child_worker($list_imm, $list_all);
	photoplog_list_worker($list_cats, -1, '', $catids, $titles, $parentids);
}

// #################### Start photoplog_index_bounce #####################
function photoplog_index_bounce()
{
	global $photoplog, $vbulletin;

	($hook = vBulletinHook::fetch_hook('photoplog_functions_indexbounce')) ? eval($hook) : false;

	$photoplog_url = $photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q'];
	exec_header_redirect($photoplog_url);
	exit();
}

// ##################### Start photoplog_file_link #######################
function photoplog_file_link($photoplog_userid, $photoplog_fileid, $photoplog_filename)
{
	global $vbulletin, $photoplog;

	$photoplog_userid = intval($photoplog_userid);
	$photoplog_fileid = intval($photoplog_fileid);
	$photoplog_filename = strval($photoplog_filename);

	if ($vbulletin->options['photoplog_dynamic_link'])
	{
		$photoplog['file_olink'] = $vbulletin->options['photoplog_script_dir']."/file.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."&amp;w=o";
		$photoplog['file_llink'] = $vbulletin->options['photoplog_script_dir']."/file.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."&amp;w=l";
		$photoplog['file_mlink'] = $vbulletin->options['photoplog_script_dir']."/file.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."&amp;w=m";
		$photoplog['file_slink'] = $vbulletin->options['photoplog_script_dir']."/file.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."&amp;w=s";
	}
	else
	{
		if ($vbulletin->options['photoplog_watermark_img'])
		{
			$photoplog_getimagesize_wm = @getimagesize($vbulletin->options['photoplog_watermark_img']);
			$photoplog_getimagesize_wmx = $photoplog_getimagesize_wm[0];
			$photoplog_getimagesize_wmy = $photoplog_getimagesize_wm[1];
			unset($photoplog_getimagesize_wm);

			$photoplog_file_lloc = $vbulletin->options['photoplog_full_path']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/large/".$photoplog_filename;
			$photoplog_getimagesize_ll = @getimagesize($photoplog_file_lloc);
			$photoplog_getimagesize_llx = $photoplog_getimagesize_ll[0];
			$photoplog_getimagesize_lly = $photoplog_getimagesize_ll[1];
			unset($photoplog_file_lloc, $photoplog_getimagesize_ll);

			if (
				$photoplog_getimagesize_wmx < $photoplog_getimagesize_llx
					&&
				$photoplog_getimagesize_wmy * 3 < $photoplog_getimagesize_lly
			)
			{
				unset($photoplog_getimagesize_wmx, $photoplog_getimagesize_wmy, $photoplog_getimagesize_llx, $photoplog_getimagesize_lly);
				$photoplog['file_olink'] = $vbulletin->options['photoplog_script_dir']."/file.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."&amp;w=o";
				$photoplog['file_llink'] = $vbulletin->options['photoplog_script_dir']."/file.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."&amp;w=l";
			}
			else
			{
				$photoplog_file_oloc = $vbulletin->options['photoplog_full_path']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/".$photoplog_filename;
				$photoplog_getimagesize_ol = @getimagesize($photoplog_file_oloc);
				$photoplog_getimagesize_olx = $photoplog_getimagesize_ol[0];
				$photoplog_getimagesize_oly = $photoplog_getimagesize_ol[1];
				unset($photoplog_file_oloc, $photoplog_getimagesize_ol);

				if (
					$photoplog_getimagesize_wmx < $photoplog_getimagesize_olx
						&&
					$photoplog_getimagesize_wmy * 3 < $photoplog_getimagesize_oly
				)
				{
					unset($photoplog_getimagesize_wmx, $photoplog_getimagesize_wmy, $photoplog_getimagesize_olx, $photoplog_getimagesize_oly);
					$photoplog['file_olink'] = $vbulletin->options['photoplog_script_dir']."/file.php?".$vbulletin->session->vars['sessionurl']."n=".$photoplog_fileid."&amp;w=o";
					$photoplog['file_llink'] = $vbulletin->options['photoplog_script_dir']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/large/".$photoplog_filename;
				}
				else
				{
					$photoplog['file_olink'] = $vbulletin->options['photoplog_script_dir']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/".$photoplog_filename;
					$photoplog['file_llink'] = $vbulletin->options['photoplog_script_dir']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/large/".$photoplog_filename;
				}
			}
		}
		else
		{
			$photoplog['file_olink'] = $vbulletin->options['photoplog_script_dir']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/".$photoplog_filename;
			$photoplog['file_llink'] = $vbulletin->options['photoplog_script_dir']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/large/".$photoplog_filename;
		}
		$photoplog['file_mlink'] = $vbulletin->options['photoplog_script_dir']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/medium/".$photoplog_filename;
		$photoplog['file_slink'] = $vbulletin->options['photoplog_script_dir']."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_userid."/small/".$photoplog_filename;
	}

	if (defined('PHOTOPLOG_LEVEL') && PHOTOPLOG_LEVEL == 'slideshow')
	{
		$photoplog['file_olink'] = str_replace('&amp;', '&', $photoplog['file_olink']);
		$photoplog['file_llink'] = str_replace('&amp;', '&', $photoplog['file_llink']);
		$photoplog['file_mlink'] = str_replace('&amp;', '&', $photoplog['file_mlink']);
		$photoplog['file_slink'] = str_replace('&amp;', '&', $photoplog['file_slink']);
	}

	if (defined('VBA_PORTAL'))
	{
		$photoplog['location'] = $vbulletin->options['photoplog_script_dir'];
	}
	$photoplog_newest_display = intval($vbulletin->options['photoplog_newest_display']);
	$photoplog_vba_adjust = (defined('VBA_PORTAL')) ? 5 : 6;
	$photoplog['do_vert'] = (
		($photoplog_newest_display == 2) || (intval($photoplog_newest_display + $photoplog_vba_adjust) % 9 == 0)
	) ? 1 : 0;

	($hook = vBulletinHook::fetch_hook('photoplog_functions_filelink')) ? eval($hook) : false;
}

// ################## Start photoplog_editor_switch ######################
function photoplog_editor_switch(&$photoplog_html_output)
{
	global $vbulletin, $photoplog;

	$photoplog_file_location = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/vbulletin_textedit.js";
	$photoplog_vbversion = explode(".", FILE_VERSION);

	$photoplog_search_for = '';
	if ($photoplog_vbversion[0] == 3 && $photoplog_vbversion[1] == 5)
	{
		$photoplog_search_for = "<script type=\"text/javascript\" src=\"clientscript/vbulletin_textedit.js\"></script>";
	}
	else if ($photoplog_vbversion[0] == 3 && $photoplog_vbversion[1] >= 6)
	{
		$photoplog_search_for = "<script type=\"text/javascript\" src=\"clientscript/vbulletin_textedit.js?v=".$vbulletin->options['simpleversion']."\"></script>";
	}

	if (
		defined('GET_EDIT_TEMPLATES') && defined('PHOTOPLOG_LEVEL')
			&&
		in_array(PHOTOPLOG_LEVEL, array('comment','edit','upload','view'))
			&&
		$photoplog_search_for
	)
	{
		if (!file_exists($photoplog_file_location))
		{
			$photoplog_replace_with = '';
			if (in_array(PHOTOPLOG_LEVEL, array('comment','edit','upload')) || (PHOTOPLOG_LEVEL == 'view' && $photoplog['quickreply']))
			{
				$photoplog_replace_with = @file_get_contents(PHOTOPLOG_FWD . '/clientscript/vbulletin_textedit.js');
			}

			if ($photoplog_replace_with)
			{
				if ($photoplog_vbversion[1] > 6)
				{
					$photoplog_replace_with = str_replace("'ajax.php?do=editorswitch'", "'".$vbulletin->options['photoplog_script_dir']."/ajax.php?do=editorswitch'", $photoplog_replace_with);
				}
				else
				{
					$photoplog_replace_with = str_replace("ajax.send('ajax.php", "ajax.send('".$vbulletin->options['photoplog_script_dir']."/ajax.php", $photoplog_replace_with);
				}
				if ($photoplog_handle = @fopen($photoplog_file_location,"w"))
				{
					@fwrite($photoplog_handle,$photoplog_replace_with);
					@fclose($photoplog_handle);
					@chmod($photoplog_file_location,0644);
				}
				unset($photoplog_replace_with);
			}
		}

		if (file_exists($photoplog_file_location))
		{
			$photoplog_replace_with = '';
			if ($photoplog_vbversion[0] == 3 && $photoplog_vbversion[1] == 5)
			{
				$photoplog_replace_with = "<script type=\"text/javascript\" src=\"".$vbulletin->options['photoplog_script_dir']."/".$vbulletin->options['photoplog_upload_dir']."/vbulletin_textedit.js\"></script>";
			}
			else if ($photoplog_vbversion[0] == 3 && $photoplog_vbversion[1] >= 6)
			{
				$photoplog_replace_with = "<script type=\"text/javascript\" src=\"".$vbulletin->options['photoplog_script_dir']."/".$vbulletin->options['photoplog_upload_dir']."/vbulletin_textedit.js?v=".$vbulletin->options['simpleversion']."\"></script>";
			}

			if ($photoplog_replace_with)
			{
				$photoplog_html_output = str_replace($photoplog_search_for, $photoplog_replace_with, $photoplog_html_output);
			}
		}
	}
}

// #################### Start photoplog_output_page ######################
function photoplog_output_page($template_name, $photoplog_phrase, $error_message = '', $photoplog_navbits = array())
{
	global $photoplog, $photoplog_checked, $photoplog_custom_field, $messagearea, $editorid, $vbulletin;

	if ($vbulletin->options['photoplog_global_vars'])
	{
		$photoplog_global_vars = array();
		$photoplog_global_string = '';

		$photoplog_global_vars = explode("\n", trim($vbulletin->options['photoplog_global_vars']));
		array_walk($photoplog_global_vars, 'photoplog_trim_text', '');

		foreach ($photoplog_global_vars AS $photoplog_global_var)
		{
			global $$photoplog_global_var;
		}
	}

	if (defined('SIMPLE_VERSION') && SIMPLE_VERSION >= 370)
	{
		global $notices, $notifications_total, $notifications_menubits;
	}
	if (defined('SIMPLE_VERSION') && SIMPLE_VERSION >= 366)
	{
		global $template_hook;
	}
	global $vbphrase, $stylevar, $vbcollapse, $spacer_open, $_phpinclude_output, $scriptpath, $show, $pmbox;
	global $foruminfo, $gobutton, $spacer_close, $quickchooserbits, $languagechooserbits, $admincpdir, $modcpdir;
	global $cronimage, $threadinfo, $pagenumber, $style, $headinclude, $header, $footer;

	if (!eregi("^(/|http://)", $stylevar['imgdir_misc']))
	{
		$photoplog_search_for = "var IMGDIR_MISC = \"".$stylevar['imgdir_misc']."\"";
		$photoplog_replace_with = "var IMGDIR_MISC = \"".$vbulletin->options['bburl']."/".$stylevar['imgdir_misc']."\"";
		$headinclude = str_replace($photoplog_search_for, $photoplog_replace_with, $headinclude);
	}

	$photoplog_base_href = "<base href=\"".$vbulletin->options['bburl']."/".$vbulletin->options['forumhome'].".php\" />\n";
	$headinclude = $photoplog_base_href.$headinclude.'<!-- +23C3P0ac02d80ed402ce672816a0b60e70bf491b79d5+ -->';

	$photoplog_jstest = "
		<script type=\"text/javascript\">
		<!--
			photoplog_expiry = new Date();
			photoplog_expiry.setTime(photoplog_expiry.getTime() + 31536000000);
			set_cookie('".addslashes_js(COOKIE_PREFIX)."photoplogjs', '1', photoplog_expiry);
		// -->
		</script>
	";
	$headinclude = $headinclude . $photoplog_jstest;

	$footer = $photoplog['powered_by'] . $footer;
	$photoplog['powered_by'] = '';

	($hook = vBulletinHook::fetch_hook('photoplog_functions_outputpage')) ? eval($hook) : false;

	if ($photoplog_phrase == $vbphrase['photoplog_file_list'])
	{
		$photoplog_phrase = $vbphrase['photoplog_gallery'];
	}
	if ($error_message == $vbphrase['photoplog_mod_queue'] || $error_message == $vbphrase['photoplog_no_results'])
	{
		$photoplog_phrase = $vbphrase['photoplog_message'];
		$vbphrase['photoplog_error'] = $vbphrase['photoplog_message'];
	}
	if ($photoplog['jsactive'] && $template_name == 'photoplog_slideshow_page')
	{
		$photoplog['sub_navbar'] = eregi_replace('<br />$','',trim($photoplog['sub_navbar']));
	}

	$navbits = array();
	$pagetitle = $vbphrase['photoplog_photoplog'];
	if (count($photoplog_navbits) > 0)
	{
		$navbits = construct_navbits($photoplog_navbits);
		if ($vbphrase['photoplog_photoplog'] != $photoplog_navbits[''])
		{
			$pagetitle = $vbphrase['photoplog_photoplog'].' - '.$photoplog_navbits[''];
		}
	}
	else
	{
		$navbits[$photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q']] = htmlspecialchars_uni($vbphrase['photoplog_photoplog']);
		$navbits[''] = $photoplog_phrase;
		$navbits = construct_navbits($navbits);
		if ($vbphrase['photoplog_photoplog'] != $photoplog_phrase)
		{
			$pagetitle = $vbphrase['photoplog_photoplog'].' - '.$photoplog_phrase;
		}
	}

	if ($vbulletin->options['hometitle'] && $vbulletin->options['hometitle'] != $vbphrase['photoplog_photoplog'])
	{
		$pagetitle = $vbulletin->options['hometitle'].' - '.$pagetitle;
	}
	eval('$navbar = "' . fetch_template('navbar') . '";');

	$photoplog_error_message = '';
	if ($error_message)
	{
		$photoplog_error_message = $vbphrase['photoplog_sorry'].", ".$vbulletin->userinfo['username'].": ".$error_message;
	}

	$photoplog_dolightbox = 0;
	if (
		($vbulletin->options['photoplog_lightbox_orig'] || $vbulletin->options['photoplog_lightbox_film'])
			&&
		in_array($template_name, array('photoplog_view_file','photoplog_slideshow_page'))
	)
	{
		$photoplog_dolightbox = 1;
	}

	eval('$html = "' . fetch_template($template_name) . '";');
	eval('$photoplog_html_output = "' . fetch_template('shell_blank') . '";');

	photoplog_editor_switch($photoplog_html_output);

	($hook = vBulletinHook::fetch_hook('photoplog_global_complete')) ? eval($hook) : false;

	eval('print_output($photoplog_html_output);');
	exit();
}

// ################## Start photoplog_get_all_groups #####################
function photoplog_get_all_groups()
{
	global $vbulletin;

	$result = array();

	$groups = $vbulletin->db->query_read("SELECT groupid, name
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customgroups
		ORDER BY name
	");
	while ($group = $vbulletin->db->fetch_array($groups))
	{
		$result[$group['groupid']] = $group['name'];
	}
	$vbulletin->db->free_result($groups);

	return $result;
}

// ################## Start photoplog_select_options #####################
function photoplog_select_options($catlist, $catid=0, $disable=0, $skip=0, $group=0)
{
	global $photoplog_perm_not_allowed_bits;
	if ($disable)
	{
		global $photoplog_ds_catopts, $photoplog_categoryoptions;
	}

	$output = '';
	if (!is_array($catlist))
	{
		$catlist = array($catlist);
	}

	foreach ($catlist AS $catlist_key => $catlist_val)
	{
		$match = array();
		if (is_array($catlist_val))
		{
			$count = preg_match('/^[-]+/', trim($catlist_key), $match);
			$indent = '';
			if ($count)
			{
				$count = vbstrlen($match[0]) - 1;
				if ($skip)
				{
					$count --;
				}
				if ($count > 0)
				{
					$indent = str_repeat('&nbsp; &nbsp;', $count);
				}
			}

			$output .= '
				<optgroup label="' . $indent . eregi_replace('^[-]+', '', htmlspecialchars_uni($catlist_key)) . '">
				' . photoplog_select_options($catlist_val, $catid, $disable, $skip, 1) . '
				</optgroup>
			';
		}
		else
		{
			$selected = ($catlist_key == $catid) ? ' class="fjsel" selected="selected"' : '';
			if (is_array($catid))
			{
				$selected = (in_array($catlist_key, $catid)) ? ' class="fjsel" selected="selected"' : '';
			}

			if ($disable)
			{
				$bits = convert_bits_to_array($photoplog_ds_catopts[$catlist_key]['options'], $photoplog_categoryoptions);
				if ($bits['actasdivider'])
				{
					$selected .= ' disabled="disabled" style="font-weight: bold; font-style: italic;"';
				}
			}

			$count = preg_match('/^[-]+/', trim($catlist_val), $match);
			$indent = '';
			if ($count)
			{
				$count = vbstrlen($match[0]) - 1;
				if ($skip && $group)
				{
					$count --;
				}
				if ($count > 0)
				{
					$indent = str_repeat('&nbsp; &nbsp;', $count);
				}
			}

			if (!$skip && !$indent && !$selected)
			{
				$selected = ' class="fjdpth0"';
			}

			if (!in_array($catlist_key, $photoplog_perm_not_allowed_bits))
			{
				$output .= '
					<option value="' . $catlist_key . '"' . $selected . '>
					' . $indent . eregi_replace('^[-]+', '', htmlspecialchars_uni($catlist_val)) . '
					</option>
				';
			}
		}
	}

	return $output;
}

// #################### Start photoplog_append_key #######################
function photoplog_append_key(&$item, $key, $prefix)
{
	$item = $item . '_' . $key;
}

// #################### Start photoplog_remove_key #######################
function photoplog_remove_key(&$item, $key, $prefix)
{
	$item = eregi_replace(preg_quote('_' . $key) . '$', '', $item);
}

// ################# Start photoplog_inline_select_row ###################
function photoplog_inline_select_row()
{
	global $vbphrase, $photoplog, $photoplog_list_relatives, $photoplog_list_categories, $photoplog_perm_not_allowed_bits, $photoplog_inline_bits, $photoplog_ds_catopts, $photoplog_categoryoptions;

	$photoplog_categoryoptpermissions = array(
		'canviewfiles' => 1,
		'canuploadfiles' => 2,
		'caneditownfiles' => 4,
		'candeleteownfiles' => 8,
		'caneditotherfiles' => 16,
		'candeleteotherfiles' => 32,
		'canviewcomments' => 64,
		'cancommentonfiles' => 128,
		'caneditowncomments' => 256,
		'candeleteowncomments' => 512,
		'caneditothercomments' => 1024,
		'candeleteothercomments' => 2048,
		'canusesearchfeature' => 4096,
		'canuploadunmoderatedfiles' => 8192,
		'canpostunmoderatedcomments' => 16384,
		'canuseslideshowfeature' => 32768,
		'canuploadasdifferentuser' => 65536,
		'canusealbumfeature' => 131072,
		'cansuggestcategories' => 262144,
		'canuseftpimport' => 524288,
		'cancreateunmoderatedcategories' => 1048576
	);

	$photoplog_list_categories_row = $photoplog_list_categories;
	$photoplog_list_categories_row[-1] = $vbphrase['photoplog_select_one'];

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

	if (!empty($photoplog_perm_not_allowed_bits) || !empty($photoplog_inline_not_allowed_bits))
	{
		array_walk($photoplog_list_categories_row, 'photoplog_append_key', '');
		$photoplog_list_categories_row = array_flip(array_diff(array_flip($photoplog_list_categories_row),
			array_unique(array_merge($photoplog_inline_not_allowed_bits, $photoplog_perm_not_allowed_bits))
		));
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
					if (!isset($photoplog_list_categories_row3[$photoplog_relative_catid]) && !in_array($photoplog_relative_catid, $photoplog_inline_not_allowed_bits))
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
					if (!isset($photoplog_list_categories_row3[$photoplog_relative_catid]) && !in_array($photoplog_relative_catid, $photoplog_inline_not_allowed_bits))
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

	if (count($photoplog_list_categories_row) == 1)
	{
		$photoplog['inlinecanedit'] = 0;
	}

	$photoplog_select_row = "<select name=\"catid\" id=\"sel_catid\" tabindex=\"1\">\n";
	$photoplog_select_row .= photoplog_select_options($photoplog_list_categories_row, 0, true, true);
	$photoplog_select_row .= "</select>\n";

	return $photoplog_select_row;
}

// ################ Start photoplog_make_custom_fields ###################
function photoplog_make_custom_fields($catid, $fielddata=array())
{
	global $photoplog_field_title, $photoplog_field_description, $photoplog_custom_field, $vbulletin, $vbphrase;

	$custom_fields = '';
	$all_groups = photoplog_get_all_groups();
	$html_checked = 'checked="checked"';
	$html_selected = 'selected="selected"';

	$photoplog_fields = $vbulletin->db->query_read("SELECT
		f1.inherited AS inherited1, f1.fieldid AS fieldid1, f1.groupid AS groupid1,
		f1.info AS info1 ,f2.info AS info2
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields AS f1
		LEFT JOIN " . PHOTOPLOG_PREFIX . "photoplog_customfields AS f2
		ON (f1.parentid = f2.catid AND f1.groupid = f2.groupid)
		WHERE f1.catid = ".intval($catid)."
		AND f1.active = 1
		ORDER BY f1.displayorder ASC
	");

	$field_template_names = array('photoplog_field_input','photoplog_field_textarea',
		'photoplog_field_select','photoplog_field_radio','photoplog_field_select_multiple','photoplog_field_radio');

	while ($photoplog_field = $vbulletin->db->fetch_array($photoplog_fields))
	{
		$photoplog_field['info'] = ($photoplog_field['inherited1'] < 1) ? $photoplog_field['info1'] :
			$photoplog_field['info2'];

		// note: photoplog_field['info'] may be NULL
		$photoplog_field['info'] = empty($photoplog_field['info']) ? '' : unserialize($photoplog_field['info']);

		if (is_array($photoplog_field['info']))
		{
			$photoplog_field['name1'] = (isset($all_groups[$photoplog_field['groupid1']])) ?
				$all_groups[$photoplog_field['groupid1']] : '';

			if ($photoplog_field['name1'] == 'Title')
			{
				$custom_fields .= $photoplog_field_title;
			}
			else if ($photoplog_field['name1'] == 'Description')
			{
				$custom_fields .= $photoplog_field_description;
			}
			else
			{
				$current_value_exists = isset($fielddata[$photoplog_field['name1']]);
				$photoplog_field['info']['current_value_exists'] = $current_value_exists;
				$current_value = ($current_value_exists) ? $fielddata[$photoplog_field['name1']] : '';
				$photoplog_field['info']['current_value'] = $current_value;

				$photoplog_field['info']['idname'] = 'id_'.$photoplog_field['fieldid1'];
				$photoplog_type = intval($photoplog_field['info']['type']);

				if (($photoplog_type < 0) || ($photoplog_type > 5))
				{
					$photoplog_type = 0;
				}
				if ($photoplog_type >= 2)
				{
					$photoplog_options = $photoplog_field['info']['options'];
					if (!is_array($photoplog_options))
					{
						$photoplog_field['info']['options'] = array();
						$photoplog_options = array();
					}
					if (!is_array($current_value))
					{
						$current_value = array($current_value);
					}
					$selected = intval($photoplog_field['info']['default']);
					if (($photoplog_type == 2) || ($photoplog_type == 4))
					{
						$photoplog_field_selectoptions = '';

						$photoplog_custom_field = array();
						$photoplog_custom_field['selected'] = ((!$current_value_exists && ($selected == 0)) ||
																($current_value_exists && empty($current_value))) ?
																$html_selected : '';
						$photoplog_custom_field['key'] = 0;
						$photoplog_custom_field['value'] = $vbphrase['photoplog_select_one'];
						eval('$photoplog_field_selectoptions .= "' . fetch_template('photoplog_field_select_option') . '";');

						$i = 1;
						foreach ($photoplog_options AS $option_text)
						{
							$photoplog_custom_field = array();
							$photoplog_custom_field['selected'] = ((!$current_value_exists && ($selected == $i)) ||
																	($current_value_exists && in_array($option_text,$current_value)))
																	? $html_selected : '';
							$photoplog_custom_field['key'] = $i++;
							$photoplog_custom_field['value'] = htmlspecialchars_uni($option_text);

							eval('$photoplog_field_selectoptions .= "' . fetch_template('photoplog_field_select_option') . '";');
						}
						$photoplog_field['info']['selectoptions'] = $photoplog_field_selectoptions;
					}
					if (($photoplog_type == 3) || ($photoplog_type == 5))
					{
						$photoplog_field_radiooptions = '';
						$photoplog_custom_field_name = htmlspecialchars_uni($photoplog_field['info']['idname']);

						$i = 1;
						$photoplog_option_template = ($photoplog_type == 3) ? 'photoplog_field_radio_option' :
							'photoplog_field_checkbox_option';
						$photoplog_perline = intval($photoplog_field['info']['perline']);

						$j = 1;
						if ($photoplog_perline > 0)
						{
							$photoplog_field_radiooptions = '<table cellpadding="0" cellspacing="0" border="0">';
						}

						foreach ($photoplog_options AS $option_text)
						{
							$photoplog_custom_field = array();
							$photoplog_custom_field['checked'] = ((!$current_value_exists && ($selected == $i)) ||
																	($current_value_exists && in_array($option_text,$current_value)))
																	? $html_checked : '';
							$photoplog_custom_field['name'] = $photoplog_custom_field_name;
							$photoplog_custom_field['key'] = $i++;
							$photoplog_custom_field['value'] = htmlspecialchars_uni($option_text);

							$photoplog_field_radiooption = '';
							eval('$photoplog_field_radiooption = "' . fetch_template($photoplog_option_template) . '";');
							if ($photoplog_perline > 0)
							{
								if ($j == 1)
								{
									$photoplog_field_radiooptions .= '<tr align="left">';
								}
								$photoplog_field_radiooptions .= '<td>' . $photoplog_field_radiooption . '</td>';
								if ($j == $photoplog_perline)
								{
									$photoplog_field_radiooptions .= '</tr>';
									$j = 0;
								}
								$j++;
							}
							else
							{
								$photoplog_field_radiooptions .= $photoplog_field_radiooption;
							}
						}

						if ($photoplog_perline > 0)
						{
							$photoplog_field_radiooptions .= '</table>';
						}
						$photoplog_field['info']['radiooptions'] = $photoplog_field_radiooptions;
					}
				}
				$photoplog_custom_field = photoplog_make_custom_field_generic($photoplog_field['info'], $photoplog_type);
				eval('$custom_fields .= "' . fetch_template($field_template_names[$photoplog_type]) . '";');
			}
		}
	}

	$vbulletin->db->free_result($photoplog_fields);
	$photoplog_custom_field = $custom_fields;
}

// ############# Start photoplog_make_custom_field_generic ###############
function photoplog_make_custom_field_generic($field_info,$field_type)
{
	$photoplog_custom_field = array();
	$photoplog_custom_field['title'] = htmlspecialchars_uni($field_info['title']);
	$photoplog_custom_field['description'] = "";
	$description = $field_info['description'];

	if ($description)
	{
		$photoplog_custom_field['description'] = "<span class=\"smallfont\">".
						htmlspecialchars_uni($description).
						"</span><br />";
	}

	$photoplog_custom_field['name'] = htmlspecialchars_uni($field_info['idname']);
	if ($field_type <= 1)
	{
		$photoplog_custom_field['size'] = max(1,intval($field_info['size']));
		if ($field_type == 1)
		{
			$photoplog_custom_field['height'] = max(1,intval($field_info['height']));
		}
		$photoplog_custom_field['maxlength'] = max(1,intval($field_info['maxlength']));
		$current_value = ($field_info['current_value_exists']) ? $field_info['current_value'] : $field_info['default'];
		$photoplog_custom_field['value'] = htmlspecialchars_uni(fetch_trimmed_title($current_value, $photoplog_custom_field['maxlength']));
	}
	if (($field_type == 2) || ($field_type == 4))
	{
		$photoplog_custom_field['selectoptions'] = $field_info['selectoptions'];
		$photoplog_custom_field['height'] = max(1,intval($field_info['height']));
		if (is_array($field_info['options']))
		{
			$photoplog_custom_field['height'] = min($photoplog_custom_field['height'], 1 + count($field_info['options']));
		}
	}
	if (($field_type == 3) || ($field_type == 5))
	{
		$photoplog_custom_field['radiooptions'] = $field_info['radiooptions'];
	}

	return $photoplog_custom_field;
}

// ########## Start photoplog_clean_and_validate_customfield #############
function photoplog_clean_and_validate_customfield (&$photoplog_customfield,$catid,$do_required,$current_fielddata=array())
{
	global $vbulletin,$vbphrase;

	$customfield = $current_fielddata;
	$error = '';
	$all_groups = photoplog_get_all_groups();

	$fields = $vbulletin->db->query_read("SELECT
		f1.inherited AS inherited1, f1.fieldid AS fieldid1, f1.groupid AS groupid1,
		f1.info AS info1 ,f2.info AS info2
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields AS f1
		LEFT JOIN " . PHOTOPLOG_PREFIX . "photoplog_customfields AS f2
		ON (f1.parentid = f2.catid AND f1.groupid = f2.groupid)
		WHERE f1.catid = ".intval($catid)."
		AND f1.active = 1
		ORDER BY f1.displayorder ASC
	");

	while ($field = $vbulletin->db->fetch_array($fields))
	{
		$info = ($field['inherited1'] < 1) ? $field['info1'] : $field['info2'];
		$info = empty($info) ? '' : unserialize($info);
		$name = (isset($all_groups[$field['groupid1']])) ? $all_groups[$field['groupid1']] : '';

		if (($name == 'Title') || ($name == 'Description'))
		{
			continue;
		}
		if (!is_array($info) || ($name == ''))
		{
			$error = $vbphrase['photoplog_bad_field_table_erroneous'];
			break;
		}

		$idname = 'id_'.$field['fieldid1'];
		$title = htmlspecialchars_uni(trim($info['title']));
		$required = intval($info['required']);
		$type = intval($info['type']);

		if (isset($photoplog_customfield[$idname]))
		{
			if (($type < 0) || ($type > 5))
			{
				$type = 0;
			}
			if ($type <= 1)
			{
				$input = trim($photoplog_customfield[$idname]);
				$maxlength = intval($info['maxlength']);
				if ($maxlength > 0)
				{
					$input = fetch_trimmed_title($input, $maxlength);
				}
				if ($required && $do_required && ($input == ''))
				{
					$error = construct_phrase($vbphrase['photoplog_error_missing_required_fields'],$title);
					break;
				}
				$customfield[$name] = $input;
			}
			$options = (isset($info['options']) && is_array($info['options'])) ? $info['options'] : array();
			if (($type == 2) || ($type == 3))
			{
				$input = intval(trim($photoplog_customfield[$idname]));
				$num_options = count($options);
				if (($input > $num_options) || ($input < 0))
				{
					$input = 0;
				}
				if ($required && $do_required && !$input)
				{
					$error = construct_phrase($vbphrase['photoplog_error_missing_required_fields'],$title);
					break;
				}
				$input--;
				$customfield[$name] = ($input >= 0) ? trim($options[$input]) : '';
			}
			if (($type == 4) || ($type == 5))
			{
				$input_array = $photoplog_customfield[$idname];
				$num_options = count($options);
				$customfield[$name] = array();
				$limit = intval($info['limit']);

				if ($limit == 0)
				{
					$limit = $num_options;
				}
				if (is_array($input_array))
				{
					foreach ($input_array AS $input_element)
					{
						$input = intval(trim($input_element));
						if (($input > $num_options) || ($input < 0))
						{
							$input = 0;
						}
						$input--;
						if ($input >= 0)
						{
							$customfield[$name][] = trim($options[$input]);
						}
					}
				}
				if ($required && $do_required && empty($customfield[$name]))
				{
					$error = construct_phrase($vbphrase['photoplog_error_missing_required_fields'],$title);
					break;
				}
				if (count($customfield[$name]) > $limit)
				{
					$error = construct_phrase($vbphrase['photoplog_maximum_number_of_selections_exceeded'],$title);
					break;
				}
			}
		}
		else if ($required && $do_required)
		{
			$error = construct_phrase($vbphrase['photoplog_error_missing_required_fields'],$title);
			break;
		}
	}
	$vbulletin->db->free_result($fields);

	$photoplog_customfield = ($error) ? array() : $customfield;
	return $error;
}

// ############### Start photoplog_make_customfield_rows #################
function photoplog_make_customfield_rows($fielddata,$title,$description,$catid,$admin='')
{
	global $vbulletin, $vbphrase, $photoplog;

	$photoplog['padright'] = intval($photoplog['padright']);
	$customfields = '';
	$all_groups = photoplog_get_all_groups();
	$hidden = '';
	if (!$admin)
	{
		$hidden = "AND f2.hidden = 0";
	}

	$fields = $vbulletin->db->query_read("SELECT
		f1.inherited AS inherited1, f1.fieldid AS fieldid1, f1.groupid AS groupid1,
		f1.info AS info1 ,f2.info AS info2
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields AS f1
		LEFT JOIN " . PHOTOPLOG_PREFIX . "photoplog_customfields AS f2
		ON (f1.parentid = f2.catid AND f1.groupid = f2.groupid)
		WHERE f1.catid = ".intval($catid)."
		AND f1.active = 1
		$hidden
		ORDER BY f1.displayorder ASC
	");

	while ($field = $vbulletin->db->fetch_array($fields))
	{
		$name = (isset($all_groups[$field['groupid1']])) ? $all_groups[$field['groupid1']] : '';
		$fieldtitle = '';
		$fieldtext = '';

		if ($name == 'Title')
		{
			$fieldtitle = $vbphrase['photoplog_title'];
			$fieldtext = $title;
		}
		else if ($name == 'Description')
		{
			$fieldtitle = $vbphrase['photoplog_description'];
			$fieldtext = $description;
		}
		else
		{
			$info = ($field['inherited1'] < 1) ? $field['info1'] : $field['info2'];
			$info = empty($info) ? '' : unserialize($info);
			$fieldtitle = htmlspecialchars_uni(trim($info['title']));
			$type = intval($info['type']);

			if (($type < 0) || ($type > 5))
			{
				$type = 0;
			}
			if (isset($fielddata[$name]))
			{
				if ($type <= 3)
				{
					if (trim($fielddata[$name]) == '')
					{
						$fieldtext = $vbphrase['photoplog_not_available'];
					}
					else
					{
						$maxlength = intval($info['maxlength']);
						$fieldtext = trim($fielddata[$name]);
						if ($maxlength > 0)
						{
							$fieldtext = fetch_trimmed_title($fieldtext, $maxlength);
						}
						$fieldtext = nl2br(htmlspecialchars_uni(fetch_censored_text($fieldtext)));
					}
				}
				else
				{
					if (is_array($fielddata[$name]))
					{
						if (empty($fielddata[$name]))
						{
							$fieldtext = $vbphrase['photoplog_not_available'];
						}
						else
						{
							$field_options = $fielddata[$name];
							foreach ($field_options AS $option_key => $option_text)
							{
								$field_options[$option_key] = htmlspecialchars_uni(fetch_censored_text(trim($option_text)));
							}
							$fieldtext = implode("<br />",$field_options);
						}
					}
					else
					{
						$fieldtext = $vbphrase['photoplog_not_available'];
					}
				}
			}
			else
			{
				$fieldtext = $vbphrase['photoplog_not_available'];
			}
		}

		$photoplog_custom_field = array('key' => $fieldtitle, 'value' => $fieldtext);
		eval('$customfields .= "' . fetch_template('photoplog_view_file_field') . '";');
	}

	return $customfields;
}

// ################ Start photoplog_get_category_title ###################
function photoplog_get_category_title($catid)
{
	global $vbulletin;

	$result = '';

	$categories = $vbulletin->db->query_first("SELECT title
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
		WHERE catid = ".intval($catid)."
	");
	if ($categories && isset($categories['title']))
	{
		$result = $categories['title'];
	}
	$vbulletin->db->free_result($categories);

	return $result;
}

// ################# Start photoplog_update_fileuploads_counts #################
// updates the counts in fileuploads for a single fileid
function photoplog_update_fileuploads_counts($fileid)
{
	$sql = "fileid = ".intval($fileid);
	photoplog_update_fileuploads_counts_sql($sql);
}

// ############## Start photoplog_update_fileuploads_counts_array ##############
// updates the counts in fileuploads for a fileid array
function photoplog_update_fileuploads_counts_array($fileid_array)
{
	if (!empty($fileid_array) && is_array($fileid_array))
	{
		$fileid_array = array_map('intval', $fileid_array);
		$sql = "fileid IN (".implode(",",$fileid_array).")";
		photoplog_update_fileuploads_counts_sql($sql);
	}
}

// ############### Start photoplog_update_fileuploads_counts_sql ###############
// updates the counts in fileuploads for fileids that satisfy the sql condition
function photoplog_update_fileuploads_counts_sql($fileid_sql)
{
	global $vbulletin;

	$file_updates = array();
	$filerows = array();

	$fileids = $vbulletin->db->query_read("SELECT fileid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE $fileid_sql
	");
	while ($filerow = $vbulletin->db->fetch_array($fileids))
	{
		$fileid = intval($filerow['fileid']);
		$file_updates[$fileid] = array();
		$filerows[$fileid] = array();
		$filerows[$fileid][0] = array();
		$filerows[$fileid][1] = array();
	}
	$vbulletin->db->free_result($fileids);

	if (!empty($filerows))
	{
		$files1 = $vbulletin->db->query_read("SELECT fileid, moderate,
			SUM(IF(rating > 0, 1, 0)) AS num_ratings,
			SUM(rating) AS sum_ratings
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $fileid_sql
			GROUP BY fileid, moderate
		");
		while ($filerow1 = $vbulletin->db->fetch_array($files1))
		{
			$fileid = intval($filerow1['fileid']);
			$moderate = intval($filerow1['moderate']);
			if (isset($filerows[$fileid]))
			{
				$filerows[$fileid][$moderate] = $filerow1;
			}
		}
		$vbulletin->db->free_result($files1);

		$files2 = $vbulletin->db->query_read("SELECT fileid, moderate,
			COUNT(commentid) AS num_comments,
			MAX(dateline) AS last_comment_dateline,
			MAX(commentid) AS last_comment_id
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $fileid_sql
			AND comment != ''
			GROUP BY fileid, moderate
		");
		$comment_datelines = array(1);
		while ($filerow2 = $vbulletin->db->fetch_array($files2))
		{
			$fileid = intval($filerow2['fileid']);
			$moderate = intval($filerow2['moderate']);
			if (isset($filerows[$fileid]))
			{
				$filerows[$fileid][$moderate] = array_merge($filerows[$fileid][$moderate], $filerow2);
				$comment_datelines[] = intval($filerow2['last_comment_id']);
			}
		}
		$vbulletin->db->free_result($files2);

		foreach ($filerows AS $fileid => $mod_array)
		{
			foreach (array('num_comments','num_ratings','sum_ratings') AS $key)
			{
				if (!isset($mod_array[0][$key])) $mod_array[0][$key] = 0;
				if (!isset($mod_array[1][$key])) $mod_array[1][$key] = 0;

				$file_updates[$fileid][$key."0"] = intval($mod_array[0][$key]);
				$file_updates[$fileid][$key."1"] = intval($mod_array[0][$key]) + intval($mod_array[1][$key]);
			}
			foreach (array('last_comment_dateline','last_comment_id') AS $key)
			{
				if (!isset($mod_array[0][$key])) $mod_array[0][$key] = 0;
				if (!isset($mod_array[1][$key])) $mod_array[1][$key] = 0;

				$file_updates[$fileid][$key."0"] = intval($mod_array[0][$key]);
				$file_updates[$fileid][$key."1"] = max(intval($mod_array[0][$key]),intval($mod_array[1][$key]));
			}
		}

		$lastids = $vbulletin->db->query_read("SELECT commentid, dateline, moderate, fileid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $fileid_sql
			AND dateline IN (".implode(",",array_unique($comment_datelines)).")
			AND comment != ''
		");
		while($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$fileid = intval($lastid['fileid']);
			if (
				($lastid['dateline'] == $file_updates[$fileid]['last_comment_dateline0']) &&
				($lastid['commentid'] != $file_updates[$fileid]['last_comment_id0']) &&
				($lastid['moderate'] == 0)
			)
			{
				$file_updates[$fileid]['last_comment_id0'] = intval($lastid['commentid']);
			}
			if (
				($lastid['dateline'] == $file_updates[$fileid]['last_comment_dateline1']) &&
				($lastid['commentid'] != $file_updates[$fileid]['last_comment_id1'])
			)
			{
				$file_updates[$fileid]['last_comment_id1'] = intval($lastid['commentid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		foreach ($file_updates AS $fileid => $filerow)
		{
			$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				SET num_comments0 = " . intval($filerow['num_comments0']) . ",
				num_comments1 = " . intval($filerow['num_comments1']) . ",
				num_ratings0 = " . intval($filerow['num_ratings0']) . ",
				num_ratings1 = " . intval($filerow['num_ratings1']) . ",
				sum_ratings0 = " . intval($filerow['sum_ratings0']) . ",
				sum_ratings1 = " . intval($filerow['sum_ratings1']) . ",
				last_comment_dateline0 = " . intval($filerow['last_comment_dateline0']) . ",
				last_comment_dateline1 = " . intval($filerow['last_comment_dateline1']) . ",
				last_comment_id0 = " . intval($filerow['last_comment_id0']) . ",
				last_comment_id1 = " . intval($filerow['last_comment_id1']) . "
				WHERE fileid = " . intval($fileid)
			);
		}
	}
}

// ################# Start photoplog_update_counts_table #################
// updates the counts in photoplog_catcounts for a single catid
function photoplog_update_counts_table($catid)
{
	global $vbulletin;

	$count_updates = array();
	$count_updates[0] = array();
	$count_updates[1] = array();
	$catid_where = "catid = ".intval($catid);

	$filequery = $vbulletin->db->query_read("SELECT moderate,
		COUNT(fileid) AS num_uploads,
		MAX(dateline) AS last_upload_dateline,
		MAX(fileid) AS last_upload_id,
		SUM(views) AS num_views,
		SUM(filesize) AS sum_filesize
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE $catid_where
		GROUP BY moderate
	");
	while($file = $vbulletin->db->fetch_array($filequery))
	{
		$moderate = intval($file['moderate']);
		$count_updates[$moderate] = $file;
	}
	$vbulletin->db->free_result($filequery);

	$ratequery1 = $vbulletin->db->query_read("SELECT moderate,
		SUM(IF(rating > 0,1,0)) AS num_ratings,
		SUM(rating) AS sum_ratings
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE $catid_where
		GROUP BY moderate
	");
	while($rate1 = $vbulletin->db->fetch_array($ratequery1))
	{
		$moderate = intval($rate1['moderate']);
		$count_updates[$moderate] = array_merge($count_updates[$moderate],$rate1);
	}
	$vbulletin->db->free_result($ratequery1);

	$ratequery2 = $vbulletin->db->query_read("SELECT moderate,
		COUNT(commentid) AS num_comments,
		MAX(dateline) AS last_comment_dateline,
		MAX(commentid) AS last_comment_id
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE $catid_where
		AND comment != ''
		GROUP BY moderate
	");
	while($rate2 = $vbulletin->db->fetch_array($ratequery2))
	{
		$moderate = intval($rate2['moderate']);
		$count_updates[$moderate] = array_merge($count_updates[$moderate],$rate2);
	}
	$vbulletin->db->free_result($ratequery2);

	foreach(array('num_uploads','num_views','sum_filesize','num_comments','num_ratings','sum_ratings') AS $key)
	{
		if (!isset($count_updates[0][$key])) $count_updates[0][$key] = 0;
		if (!isset($count_updates[1][$key])) $count_updates[1][$key] = 0;

		$count_updates[0][$key] = intval($count_updates[0][$key]);
		$count_updates[1][$key] = intval($count_updates[1][$key]) + intval($count_updates[0][$key]);
	}
	foreach(array('last_upload_dateline','last_upload_id','last_comment_dateline','last_comment_id') AS $key)
	{
		if (!isset($count_updates[0][$key])) $count_updates[0][$key] = 0;
		if (!isset($count_updates[1][$key])) $count_updates[1][$key] = 0;

		$count_updates[0][$key] = intval($count_updates[0][$key]);
		$count_updates[1][$key] = max(intval($count_updates[1][$key]), intval($count_updates[0][$key]));
	}

	$lastids = $vbulletin->db->query_read("SELECT fileid, dateline, moderate
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE $catid_where
		AND dateline IN (".intval($count_updates[0]['last_upload_dateline']).",".intval($count_updates[1]['last_upload_dateline']).")
	");
	while($lastid = $vbulletin->db->fetch_array($lastids))
	{
		if (
			($lastid['dateline'] == $count_updates[0]['last_upload_dateline']) &&
			($lastid['fileid'] != $count_updates[0]['last_upload_id']) &&
			($lastid['moderate'] == 0)
		)
		{
			$count_updates[0]['last_upload_id'] = intval($lastid['fileid']);
		}
		if (
			($lastid['dateline'] == $count_updates[1]['last_upload_dateline']) &&
			($lastid['fileid'] != $count_updates[1]['last_upload_id'])
		)
		{
			$count_updates[1]['last_upload_id'] = intval($lastid['fileid']);
		}
	}
	$vbulletin->db->free_result($lastids);

	$lastids = $vbulletin->db->query_read("SELECT commentid, dateline, moderate
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE $catid_where
		AND dateline IN (".intval($count_updates[0]['last_comment_dateline']).",".intval($count_updates[1]['last_comment_dateline']).")
		AND comment != ''
	");
	while($lastid = $vbulletin->db->fetch_array($lastids))
	{
		if (
			($lastid['dateline'] == $count_updates[0]['last_comment_dateline']) &&
			($lastid['commentid'] != $count_updates[0]['last_comment_id']) &&
			($lastid['moderate'] == 0)
		)
		{
			$count_updates[0]['last_comment_id'] = intval($lastid['commentid']);
		}
		if (
			($lastid['dateline'] == $count_updates[1]['last_comment_dateline']) &&
			($lastid['commentid'] != $count_updates[1]['last_comment_id'])
		)
		{
			$count_updates[1]['last_comment_id'] = intval($lastid['commentid']);
		}
	}
	$vbulletin->db->free_result($lastids);

	foreach ($count_updates AS $mod => $info) // two queries
	{
		$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
			SET num_uploads = ".intval($info['num_uploads']).",
			num_comments = ".intval($info['num_comments']).",
			num_ratings = ".intval($info['num_ratings']).",
			sum_ratings = ".intval($info['sum_ratings']).",
			last_upload_dateline = ".intval($info['last_upload_dateline']).",
			last_upload_id = ".intval($info['last_upload_id']).",
			last_comment_dateline = ".intval($info['last_comment_dateline']).",
			last_comment_id = ".intval($info['last_comment_id']).",
			num_views = ".intval($info['num_views']).",
			sum_filesize = ".intval($info['sum_filesize'])."
			WHERE $catid_where
			AND moderate = ".intval($mod)."
		");
	}
}

// ############### Start photoplog_regenerate_counts_table ###############
// regenerates (inserts!) the photoplog_catcounts table for catids between start (inclusive) and stop (exclusive).
// if start = 0, then the table is created if not exists, and wiped clean
// if stop < start, then there is no upper bound for catid.
function photoplog_regenerate_counts_table($start,$stop)
{
	global $vbulletin;

	$catids = array();

	$stop_sql = (intval($stop) < intval($start)) ? '' : 'AND catid < '.intval($stop);

	$catsquery = $vbulletin->db->query_read("SELECT catid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
		WHERE catid >= ".intval($start)."
		".$stop_sql."
	");
	while($catrow = $vbulletin->db->fetch_array($catsquery))
	{
		$catids[] = intval($catrow['catid']);
	}
	$vbulletin->db->free_result($catsquery);
	if ($start == 0)
	{
		$catids[] = 0;
	}
	$count_inserts = array();
	foreach($catids AS $catid)
	{
		$count_inserts["$catid"] = array();
		$count_inserts["$catid"][0] = array();
		$count_inserts["$catid"][1] = array();
	}
	if (!empty($catids))
	{
		$catwhere = "catid IN (".implode(",",$catids).")";

		$filequery = $vbulletin->db->query_read("SELECT moderate, catid,
			COUNT(fileid) AS num_uploads,
			MAX(dateline) AS last_upload_dateline,
			MAX(fileid) AS last_upload_id,
			SUM(views) AS num_views,
			SUM(filesize) AS sum_filesize
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE $catwhere
			GROUP BY moderate, catid
		");
		while ($file = $vbulletin->db->fetch_array($filequery))
		{
			$moderate = intval($file['moderate']);
			$catid = intval($file['catid']);
			$count_inserts["$catid"][$moderate] = $file;
		}
		$vbulletin->db->free_result($filequery);

		$ratequery1 = $vbulletin->db->query_read("SELECT moderate, catid,
			SUM(IF(rating > 0,1,0)) AS num_ratings,
			SUM(rating) AS sum_ratings
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			GROUP BY moderate, catid
		");
		while ($rate1 = $vbulletin->db->fetch_array($ratequery1))
		{
			$moderate = intval($rate1['moderate']);
			$catid = intval($rate1['catid']);
			$count_inserts["$catid"][$moderate] = array_merge($count_inserts["$catid"][$moderate],$rate1);
		}
		$vbulletin->db->free_result($ratequery1);

		$ratequery2 = $vbulletin->db->query_read("SELECT moderate, catid,
			COUNT(commentid) AS num_comments,
			MAX(dateline) AS last_comment_dateline,
			MAX(commentid) AS last_comment_id
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			AND comment != ''
			GROUP BY moderate, catid
		");
		while ($rate2 = $vbulletin->db->fetch_array($ratequery2))
		{
			$moderate = intval($rate2['moderate']);
			$catid = intval($rate2['catid']);
			$count_inserts["$catid"][$moderate] = array_merge($count_inserts["$catid"][$moderate],$rate2);
		}
		$vbulletin->db->free_result($ratequery2);

		$upload_datelines = array(1);
		$comment_datelines = array(1);
		foreach ($catids AS $catid)
		{
			foreach (array('num_uploads','num_views','sum_filesize','num_comments','num_ratings','sum_ratings') AS $key)
			{
				if (!isset($count_inserts["$catid"][0][$key])) $count_inserts["$catid"][0][$key] = 0;
				if (!isset($count_inserts["$catid"][1][$key])) $count_inserts["$catid"][1][$key] = 0;

				$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
				$count_inserts["$catid"][1][$key] = intval($count_inserts["$catid"][1][$key]) + intval($count_inserts["$catid"][0][$key]);
			}
			foreach (array('last_upload_dateline','last_upload_id','last_comment_dateline','last_comment_id') AS $key)
			{
				if (!isset($count_inserts["$catid"][0][$key])) $count_inserts["$catid"][0][$key] = 0;
				if (!isset($count_inserts["$catid"][1][$key])) $count_inserts["$catid"][1][$key] = 0;

				$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
				$count_inserts["$catid"][1][$key] = max(intval($count_inserts["$catid"][1][$key]), intval($count_inserts["$catid"][0][$key]));
			}
			$upload_datelines[] = intval($count_inserts["$catid"][0]['last_upload_dateline']);
			$upload_datelines[] = intval($count_inserts["$catid"][1]['last_upload_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][0]['last_comment_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][1]['last_comment_dateline']);
		}

		$lastids = $vbulletin->db->query_read("SELECT fileid, dateline, moderate, catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE $catwhere
			AND dateline IN (".implode(",",array_unique($upload_datelines)).")
		");
		while ($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$catid = intval($lastid['catid']);
			if (
				($lastid['dateline'] == $count_inserts["$catid"][0]['last_upload_dateline']) &&
				($lastid['fileid'] != $count_inserts["$catid"][0]['last_upload_id']) &&
				($lastid['moderate'] == 0)
			)
			{
				$count_inserts["$catid"][0]['last_upload_id'] = intval($lastid['fileid']);
			}
			if (
				($lastid['dateline'] == $count_inserts["$catid"][1]['last_upload_dateline']) &&
				($lastid['fileid'] != $count_inserts["$catid"][1]['last_upload_id'])
			)
			{
				$count_inserts["$catid"][1]['last_upload_id'] = intval($lastid['fileid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		$lastids = $vbulletin->db->query_read("SELECT commentid, dateline, moderate, catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			AND dateline IN (".implode(",",array_unique($comment_datelines)).")
			AND comment != ''
		");
		while ($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$catid = intval($lastid['catid']);
			if (
				($lastid['dateline'] == $count_inserts["$catid"][0]['last_comment_dateline']) &&
				($lastid['commentid'] != $count_inserts["$catid"][0]['last_comment_id']) &&
				($lastid['moderate'] == 0)
			)
			{
				$count_inserts["$catid"][0]['last_comment_id'] = intval($lastid['commentid']);
			}
			if (
				($lastid['dateline'] == $count_inserts["$catid"][1]['last_comment_dateline']) &&
				($lastid['commentid'] != $count_inserts["$catid"][1]['last_comment_id'])
			)
			{
				$count_inserts["$catid"][1]['last_comment_id'] = intval($lastid['commentid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		if ($start == 0)
		{
			$vbulletin->db->query_write("TRUNCATE TABLE " . PHOTOPLOG_PREFIX . "photoplog_catcounts");
		}

		$limit = 100;
		$values = array();
		foreach ($count_inserts AS $catid => $mod_array)
		{
			if (!isset($mod_array) || !is_array($mod_array))
			{
				$mod_array = array();
				$mod_array[0] = array();
				$mod_array[1] = array();
			}
			if (!isset($mod_array[0]) || !is_array($mod_array[0]))
			{
				$mod_array[0] = array();
			}
			if (!isset($mod_array[1]) || !is_array($mod_array[1]))
			{
				$mod_array[1] = array();
			}
			foreach ($mod_array AS $mod => $info)
			{
				$values[] = "(" . intval($catid) . ", " . intval($mod) . ", " . intval($info['num_uploads']) . ", " .
					intval($info['num_comments']) . ", " . intval($info['num_ratings']) . ", " .
					intval($info['sum_ratings']) . ", " . intval($info['last_upload_dateline']) . ", " .
					intval($info['last_upload_id']) . ", " . intval($info['last_comment_dateline']) . ", " .
					intval($info['last_comment_id']) . ", " . intval($info['num_views']) . ", " .
					intval($info['sum_filesize']) . ")";
			}
			if (count($values) >= $limit)
			{
				$valstr = implode(", ", $values);
				$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_catcounts
					(catid, moderate, num_uploads, num_comments, num_ratings, sum_ratings, last_upload_dateline,
					last_upload_id, last_comment_dateline, last_comment_id, num_views, sum_filesize)
					VALUES " . $valstr);
				$values = array();
			}
		}
		if (count($values) > 0)
		{
			$valstr = implode(", ", $values);
			$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_catcounts
				(catid, moderate, num_uploads, num_comments, num_ratings, sum_ratings, last_upload_dateline,
				last_upload_id, last_comment_dateline, last_comment_id, num_views, sum_filesize)
				VALUES " . $valstr);
		}
	}
}

// ############## Start photoplog_regenerate_counts_table_v2 #############
// regenerates (updates!) the photoplog_catcounts table for given catids
function photoplog_regenerate_counts_table_v2($ids)
{
	global $vbulletin;

	if (!is_array($ids))
	{
		$ids = array($ids);
	}
	$ids = array_unique($ids);
	$ids = array_map('intval', $ids);

	$catsquery = $vbulletin->db->query_read("SELECT catid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
		WHERE catid IN (".implode(',', $ids).")
	");

	$catids = array();
	while ($catrow = $vbulletin->db->fetch_array($catsquery))
	{
		$catids[] = intval($catrow['catid']);
	}
	$vbulletin->db->free_result($catsquery);

	if (!empty($catids))
	{
		$count_inserts = array();
		foreach ($catids AS $catid)
		{
			$count_inserts[$catid] = array();
			$count_inserts[$catid][0] = array();
			$count_inserts[$catid][1] = array();
		}

		$catwhere = "catid IN (".implode(",",$catids).")";

		$filequery = $vbulletin->db->query_read("SELECT moderate, catid,
			COUNT(fileid) AS num_uploads,
			MAX(dateline) AS last_upload_dateline,
			MAX(fileid) AS last_upload_id,
			SUM(views) AS num_views,
			SUM(filesize) AS sum_filesize
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE $catwhere
			GROUP BY moderate, catid
		");
		while ($file = $vbulletin->db->fetch_array($filequery))
		{
			$moderate = intval($file['moderate']);
			$catid = intval($file['catid']);
			$count_inserts["$catid"][$moderate] = $file;
		}
		$vbulletin->db->free_result($filequery);

		$ratequery1 = $vbulletin->db->query_read("SELECT moderate, catid,
			SUM(IF(rating > 0,1,0)) AS num_ratings,
			SUM(rating) AS sum_ratings
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			GROUP BY moderate, catid
		");
		while ($rate1 = $vbulletin->db->fetch_array($ratequery1))
		{
			$moderate = intval($rate1['moderate']);
			$catid = intval($rate1['catid']);
			$count_inserts["$catid"][$moderate] = array_merge($count_inserts["$catid"][$moderate],$rate1);
		}
		$vbulletin->db->free_result($ratequery1);

		$ratequery2 = $vbulletin->db->query_read("SELECT moderate, catid,
			COUNT(commentid) AS num_comments,
			MAX(dateline) AS last_comment_dateline,
			MAX(commentid) AS last_comment_id
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			AND comment != ''
			GROUP BY moderate, catid
		");
		while ($rate2 = $vbulletin->db->fetch_array($ratequery2))
		{
			$moderate = intval($rate2['moderate']);
			$catid = intval($rate2['catid']);
			$count_inserts["$catid"][$moderate] = array_merge($count_inserts["$catid"][$moderate],$rate2);
		}
		$vbulletin->db->free_result($ratequery2);

		$upload_datelines = array(1);
		$comment_datelines = array(1);
		foreach ($catids AS $catid)
		{
			foreach (array('num_uploads','num_views','sum_filesize','num_comments','num_ratings','sum_ratings') AS $key)
			{
				if (!isset($count_inserts["$catid"][0][$key])) $count_inserts["$catid"][0][$key] = 0;
				if (!isset($count_inserts["$catid"][1][$key])) $count_inserts["$catid"][1][$key] = 0;

				$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
				$count_inserts["$catid"][1][$key] = intval($count_inserts["$catid"][1][$key]) + intval($count_inserts["$catid"][0][$key]);
			}
			foreach (array('last_upload_dateline','last_upload_id','last_comment_dateline','last_comment_id') AS $key)
			{
				if (!isset($count_inserts["$catid"][0][$key])) $count_inserts["$catid"][0][$key] = 0;
				if (!isset($count_inserts["$catid"][1][$key])) $count_inserts["$catid"][1][$key] = 0;

				$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
				$count_inserts["$catid"][1][$key] = max(intval($count_inserts["$catid"][1][$key]), intval($count_inserts["$catid"][0][$key]));
			}
			$upload_datelines[] = intval($count_inserts["$catid"][0]['last_upload_dateline']);
			$upload_datelines[] = intval($count_inserts["$catid"][1]['last_upload_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][0]['last_comment_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][1]['last_comment_dateline']);
		}

		$lastids = $vbulletin->db->query_read("SELECT fileid, dateline, moderate, catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE $catwhere
			AND dateline IN (".implode(",",array_unique($upload_datelines)).")
		");
		while ($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$catid = intval($lastid['catid']);
			if (
				($lastid['dateline'] == $count_inserts["$catid"][0]['last_upload_dateline']) &&
				($lastid['fileid'] != $count_inserts["$catid"][0]['last_upload_id']) &&
				($lastid['moderate'] == 0)
			)
			{
				$count_inserts["$catid"][0]['last_upload_id'] = intval($lastid['fileid']);
			}
			if (
				($lastid['dateline'] == $count_inserts["$catid"][1]['last_upload_dateline']) &&
				($lastid['fileid'] != $count_inserts["$catid"][1]['last_upload_id'])
			)
			{
				$count_inserts["$catid"][1]['last_upload_id'] = intval($lastid['fileid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		$lastids = $vbulletin->db->query_read("SELECT commentid, dateline, moderate, catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			AND dateline IN (".implode(",",array_unique($comment_datelines)).")
			AND comment != ''
		");
		while ($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$catid = intval($lastid['catid']);
			if (
				($lastid['dateline'] == $count_inserts["$catid"][0]['last_comment_dateline']) &&
				($lastid['commentid'] != $count_inserts["$catid"][0]['last_comment_id']) &&
				($lastid['moderate'] == 0)
			)
			{
				$count_inserts["$catid"][0]['last_comment_id'] = intval($lastid['commentid']);
			}
			if (
				($lastid['dateline'] == $count_inserts["$catid"][1]['last_comment_dateline']) &&
				($lastid['commentid'] != $count_inserts["$catid"][1]['last_comment_id'])
			)
			{
				$count_inserts["$catid"][1]['last_comment_id'] = intval($lastid['commentid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		foreach ($count_inserts AS $catid => $mod_array)
		{
			if (!isset($mod_array) || !is_array($mod_array))
			{
				$mod_array = array();
				$mod_array[0] = array();
				$mod_array[1] = array();
			}
			if (!isset($mod_array[0]) || !is_array($mod_array[0]))
			{
				$mod_array[0] = array();
			}
			if (!isset($mod_array[1]) || !is_array($mod_array[1]))
			{
				$mod_array[1] = array();
			}
			foreach ($mod_array AS $mod => $info) // two queries
			{
				$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
					SET num_uploads = ".intval($info['num_uploads']).",
						num_comments = ".intval($info['num_comments']).",
						num_ratings = ".intval($info['num_ratings']).",
						sum_ratings = ".intval($info['sum_ratings']).",
						last_upload_dateline = ".intval($info['last_upload_dateline']).",
						last_upload_id = ".intval($info['last_upload_id']).",
						last_comment_dateline = ".intval($info['last_comment_dateline']).",
						last_comment_id = ".intval($info['last_comment_id']).",
						num_views = ".intval($info['num_views']).",
						sum_filesize = ".intval($info['sum_filesize'])."
					WHERE catid = ".intval($catid)." AND moderate = ".intval($mod)."
				");
			}
		}
	}
}

?>