<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('photoplog','cppermission');
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

$photoplog_list_categories = array();
photoplog_list_categories($photoplog_list_categories,-1,$vbphrase['photoplog_no_one']);
$photoplog_ds_catopts = photoplog_fetch_ds_cat();
if (!is_array($vbulletin->usergroupcache))
{
	$vbulletin->usergroupcache = array();
}

// ###################### SET BIT OPTION VALUES ###########################
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

$photoplog_categoryintpermissions = array(
	'maxfilesize' => 512000,
	'maxfilelimit' => 100,
);

print_cp_header($vbphrase['photoplog_category_permissions']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'modify';
}

if ($_REQUEST['do'] == 'modify')
{
	$photoplog_list_children = array();
	$photoplog_list_relatives = array();
	photoplog_relative_list($photoplog_list_children, $photoplog_list_relatives);

	$photoplog_permission_infos = $db->query_read("SELECT catid, usergroupid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_permissions
	");

	$photoplog_list_style = array();
	while ($photoplog_permission_info = $db->fetch_array($photoplog_permission_infos))
	{
		$photoplog_cat_id = intval($photoplog_permission_info['catid']);
		$photoplog_usergroup_id = intval($photoplog_permission_info['usergroupid']);
		$photoplog_list_style[$photoplog_cat_id][$photoplog_usergroup_id] = 'col-c';
	}

	$db->data_seek($photoplog_permission_infos, 0);

	while ($photoplog_permission_info = $db->fetch_array($photoplog_permission_infos))
	{
		$photoplog_cat_id = intval($photoplog_permission_info['catid']);
		$photoplog_usergroup_id = intval($photoplog_permission_info['usergroupid']);
		$photoplog_relatives_array = $photoplog_list_relatives[$photoplog_cat_id];
		if (!is_array($photoplog_relatives_array))
		{
			$photoplog_relatives_array = array();
		}
		foreach ($photoplog_relatives_array AS $photoplog_relatives_catid)
		{
			if (!$photoplog_list_style[$photoplog_relatives_catid][$photoplog_usergroup_id])
			{
				$photoplog_list_style[$photoplog_relatives_catid][$photoplog_usergroup_id] = 'col-i';
			}
		}
	}

	$db->free_result($photoplog_permission_infos);

	print_form_header('', '');
	print_table_header($vbphrase['photoplog_category_permissions']);
	print_description_row('
		<div class="darkbg" style="border: 2px inset"><ul class="darkbg">
		<li><b>' . $vbphrase['photoplog_color_key'] . '</b></li>
		<li class="col-g">' . $vbphrase['photoplog_standard_permissions'] . '</li>
		<li class="col-c">' . $vbphrase['photoplog_customized_permissions'] . '</li>
		<li class="col-i">' . $vbphrase['photoplog_inherited_permissions'] . '</li>
		</ul></div>
	');
	print_table_footer();

	echo "
		<center>
		<div class=\"tborder\" style=\"width: 89%\">
		<div class=\"alt1\" style=\"padding: 8px\">
		<div class=\"darkbg\" style=\"padding: 4px; border: 2px inset; text-align: ".$stylevar['left'].";\">
	";
	echo "\n<ul class=\"lsq\">\n";

	$photoplog_dash_length1 = 0;
	$photoplog_group_flag = 0;
	$photoplog_group_bits = array();

	foreach($photoplog_list_categories AS $photoplog_catid => $photoplog_value)
	{
		if ($photoplog_catid > 0)
		{
			$photoplog_dashes = '';
			$photoplog_title = htmlspecialchars_uni(trim($photoplog_value));

			if (eregi("^([-]+[ ])(.*)",$photoplog_title,$photoplog_regs))
			{
				$photoplog_dashes = $photoplog_regs[1];
				$photoplog_title = $photoplog_regs[2];
			}

			// the >> right shift is to force integer value
			// strlen and mb_strlen both return an integer
			// that divided by two can cause rounding error
			// doing the right shift forces an integer value
			$photoplog_dash_length2 = vbstrlen($photoplog_dashes) >> 1;

			while ($photoplog_dash_length2 != $photoplog_dash_length1)
			{
				if ($photoplog_dash_length2 > $photoplog_dash_length1)
				{
					echo "\n<ul class=\"lsq\">\n";
					$photoplog_dash_length1++;
				}
				else
				{
					echo "\n</ul>\n";
					$photoplog_dash_length1--;
				}
			}

			echo "\n<li><strong><a href=\"photoplog_category.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&amp;catid=".$photoplog_catid."\">".$photoplog_title."</a></strong>\n";
			echo "
				<span class=\"smallfont\">
				(".
					construct_link_code($vbphrase['photoplog_reset'],    "photoplog_permission.php?" . $vbulletin->session->vars['sessionurl'] . "do=reset&amp;catid=".$photoplog_catid).
				" ".
					construct_link_code($vbphrase['photoplog_deny_all'], "photoplog_permission.php?" . $vbulletin->session->vars['sessionurl'] . "do=deny&amp;catid=".$photoplog_catid).
				")</span>";
			echo "</li>";

			$photoplog_usergroups = "<ul class=\"usergroups\">";
			foreach ($vbulletin->usergroupcache AS $photoplog_usergroupid => $photoplog_cachearray)
			{
				$photoplog_list_class = 'col-g';
				if ($photoplog_list_style[$photoplog_catid][$photoplog_usergroupid])
				{
					$photoplog_list_class = $photoplog_list_style[$photoplog_catid][$photoplog_usergroupid];
				}

				$photoplog_editlink = 'catid='.$photoplog_catid.'&amp;group='.$photoplog_cachearray['usergroupid'];
				$photoplog_usergroups .= "\n<li class=\"".$photoplog_list_class."\">" . construct_link_code($vbphrase['photoplog_edit'], "photoplog_permission.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&amp;$photoplog_editlink") . $photoplog_cachearray['title'] . "</li>\n";

				if (!$photoplog_group_flag)
				{
					$photoplog_group_bits[] = intval($photoplog_cachearray['usergroupid']);
				}
			}
			$photoplog_group_flag++;
			$photoplog_usergroups .= "</ul><br />";
			echo $photoplog_usergroups;
		}
	}

	if ($photoplog_group_bits)
	{
		$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_permissions
			WHERE usergroupid NOT IN (".implode(",",$photoplog_group_bits).")
		");
	}

	echo "\n</ul>\n";
	echo "
		</div>
		</div>
		</div>
		</center>
	";
}

if ($_REQUEST['do'] == 'edit')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'catid' => TYPE_UINT,
		'group' => TYPE_UINT
	));

	$photoplog_catid = $vbulletin->GPC['catid'];
	$photoplog_usergroupid = $vbulletin->GPC['group'];

	$photoplog_list_children = array();
	$photoplog_list_relatives = array();
	$photoplog_parent_list = array();
	photoplog_relative_list($photoplog_list_children, $photoplog_list_relatives);

	foreach ($photoplog_list_relatives AS $photoplog_list_relatives_key => $photoplog_list_relatives_array)
	{
		if ($photoplog_list_relatives_key != '-1' && in_array(intval($photoplog_catid),$photoplog_list_relatives_array))
		{
			$photoplog_list_relatives_cnt = count($photoplog_list_relatives_array);
			$photoplog_parent_list[$photoplog_list_relatives_cnt] = $photoplog_list_relatives_key;
		}
	}
	ksort($photoplog_parent_list);

	$photoplog_permission_info = $db->query_first("SELECT options, maxfilesize, maxfilelimit
		FROM " . PHOTOPLOG_PREFIX . "photoplog_permissions
		WHERE usergroupid = ".intval($photoplog_usergroupid)."
		AND catid = ".intval($photoplog_catid)."
	");

	// whites
	$photoplog_custom_permissions = 0;
	$photoplog_category_options = convert_bits_to_array($vbulletin->usergroupcache[$photoplog_usergroupid]['photoplogpermissions'], $photoplog_categoryoptpermissions);
	$photoplog_category_filesize = $vbulletin->usergroupcache[$photoplog_usergroupid]['photoplogmaxfilesize'];
	$photoplog_category_filelimit = $vbulletin->usergroupcache[$photoplog_usergroupid]['photoplogmaxfilelimit'];

	if ($photoplog_permission_info) // reds
	{
		$photoplog_custom_permissions = 1;
		$photoplog_category_options = convert_bits_to_array($photoplog_permission_info['options'], $photoplog_categoryoptpermissions);
		$photoplog_category_filesize = $photoplog_permission_info['maxfilesize'];
		$photoplog_category_filelimit = $photoplog_permission_info['maxfilelimit'];
	}
	else // yellows
	{
		$photoplog_parent_list_sql = $photoplog_parent_list;
		if (empty($photoplog_parent_list_sql))
		{
			$photoplog_parent_list_sql = array(-999);
		}
		$photoplog_permission_parent_infos = $db->query_read("SELECT catid, options, maxfilesize, maxfilelimit
			FROM " . PHOTOPLOG_PREFIX . "photoplog_permissions
			WHERE usergroupid = ".intval($photoplog_usergroupid)."
			AND catid IN (".implode(",",$photoplog_parent_list_sql).")
		");
		unset($photoplog_parent_list_sql);
		$photoplog_permission_parent_list = array();
		foreach ($photoplog_parent_list AS $photoplog_parent_list_key => $photoplog_parent_list_val)
		{
			$photoplog_permission_parent_list[$photoplog_parent_list_key] = array();
		}
		while ($photoplog_permission_parent_info = $db->fetch_array($photoplog_permission_parent_infos))
		{
			if ($photoplog_permission_parent_info['catid'] > -1)
			{
				foreach ($photoplog_parent_list AS $photoplog_parent_list_key => $photoplog_parent_catid)
				{
					if ($photoplog_permission_parent_info['catid'] == $photoplog_parent_catid)
					{
						$photoplog_permission_parent_list[$photoplog_parent_list_key] = $photoplog_permission_parent_info;
					}
				}
			}
		}
		$db->free_result($photoplog_permission_parent_infos);
		foreach ($photoplog_parent_list AS $photoplog_parent_list_key => $photoplog_parent_catid)
		{
			if ($photoplog_permission_parent_list[$photoplog_parent_list_key])
			{
				$photoplog_custom_permissions = 0;
				$photoplog_category_options = convert_bits_to_array($photoplog_permission_parent_list[$photoplog_parent_list_key]['options'], $photoplog_categoryoptpermissions);
				$photoplog_category_filesize = $photoplog_permission_parent_list[$photoplog_parent_list_key]['maxfilesize'];
				$photoplog_category_filelimit = $photoplog_permission_parent_list[$photoplog_parent_list_key]['maxfilelimit'];
				break;
			}
		}
	}

	$db->free_result($photoplog_permission_info);

	print_form_header('photoplog_permission', 'doedit');
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	construct_hidden_code('catid', $photoplog_catid);
	construct_hidden_code('group', $photoplog_usergroupid);

	$photoplog_category_title = htmlspecialchars_uni(trim($photoplog_ds_catopts[$photoplog_catid]['title']));
	$photoplog_usergroup_title = $vbulletin->usergroupcache[$photoplog_usergroupid]['title'];

	print_table_header(construct_phrase($vbphrase['photoplog_edit_category_permissions_for_usergroup_x_in_category_y'], $photoplog_usergroup_title, $photoplog_category_title));
	print_description_row('
		<label for="uug_0"><input type="radio" name="photoplogcustomperm" value="0" id="uug_0"' . iif(empty($photoplog_custom_permissions), ' checked="checked"') . ' />' . $vbphrase['photoplog_use_default_permissions'] . '</label>
		<br />
		<label for="uug_1"><input type="radio" name="photoplogcustomperm" value="1" id="uug_1"' . iif(!empty($photoplog_custom_permissions), ' checked="checked"') . ' />' . $vbphrase['photoplog_use_custom_permissions'] . '</label>
	', 0, 2, 'tfoot', '' , 'mode');
	print_table_break();

//	print_table_header($vbphrase['photoplog_permissions']);
	print_label_row('<strong>'.$vbphrase['photoplog_permissions'].'</strong>',
		'<input type="button" class="button" value="'.$vbphrase['photoplog_all_yes'].'" onclick="js_check_all_option(this.form, 1);" />
		<input type="button" class="button" value="'.$vbphrase['photoplog_all_no'].'" onclick="js_check_all_option(this.form, 0);" />',
		'tcat', 'middle'
	);
	print_yes_no_row($vbphrase['photoplog_can_view_files'], 'photoplog_category[options][canviewfiles]', $photoplog_category_options['canviewfiles']);
	print_yes_no_row($vbphrase['photoplog_can_upload_files'], 'photoplog_category[options][canuploadfiles]', $photoplog_category_options['canuploadfiles']);
	print_yes_no_row($vbphrase['photoplog_can_edit_own_files'], 'photoplog_category[options][caneditownfiles]', $photoplog_category_options['caneditownfiles']);
	print_yes_no_row($vbphrase['photoplog_can_delete_own_files'], 'photoplog_category[options][candeleteownfiles]', $photoplog_category_options['candeleteownfiles']);
	print_yes_no_row($vbphrase['photoplog_can_edit_other_files'], 'photoplog_category[options][caneditotherfiles]', $photoplog_category_options['caneditotherfiles']);
	print_yes_no_row($vbphrase['photoplog_can_delete_other_files'], 'photoplog_category[options][candeleteotherfiles]', $photoplog_category_options['candeleteotherfiles']);
	print_yes_no_row($vbphrase['photoplog_can_view_comments'], 'photoplog_category[options][canviewcomments]', $photoplog_category_options['canviewcomments']);
	print_yes_no_row($vbphrase['photoplog_can_comment_on_files'], 'photoplog_category[options][cancommentonfiles]', $photoplog_category_options['cancommentonfiles']);
	print_yes_no_row($vbphrase['photoplog_can_edit_own_comments'], 'photoplog_category[options][caneditowncomments]', $photoplog_category_options['caneditowncomments']);
	print_yes_no_row($vbphrase['photoplog_can_delete_own_comments'], 'photoplog_category[options][candeleteowncomments]', $photoplog_category_options['candeleteowncomments']);
	print_yes_no_row($vbphrase['photoplog_can_edit_other_comments'], 'photoplog_category[options][caneditothercomments]', $photoplog_category_options['caneditothercomments']);
	print_yes_no_row($vbphrase['photoplog_can_delete_other_comments'], 'photoplog_category[options][candeleteothercomments]', $photoplog_category_options['candeleteothercomments']);
	print_input_row($vbphrase['photoplog_max_file_size'], 'photoplogmaxfilesize', $photoplog_category_filesize, true, 20);
	print_input_row($vbphrase['photoplog_max_file_limit'], 'photoplogmaxfilelimit', $photoplog_category_filelimit, true, 20);
	print_yes_no_row($vbphrase['photoplog_can_use_search_feature'], 'photoplog_category[options][canusesearchfeature]', $photoplog_category_options['canusesearchfeature']);
	print_yes_no_row($vbphrase['photoplog_can_upload_unmoderated_files'], 'photoplog_category[options][canuploadunmoderatedfiles]', $photoplog_category_options['canuploadunmoderatedfiles']);
	print_yes_no_row($vbphrase['photoplog_can_post_unmoderated_comments'], 'photoplog_category[options][canpostunmoderatedcomments]', $photoplog_category_options['canpostunmoderatedcomments']);
	print_yes_no_row($vbphrase['photoplog_can_use_slideshow_feature'], 'photoplog_category[options][canuseslideshowfeature]', $photoplog_category_options['canuseslideshowfeature']);
	print_yes_no_row($vbphrase['photoplog_can_upload_as_different_user'], 'photoplog_category[options][canuploadasdifferentuser]', $photoplog_category_options['canuploadasdifferentuser']);
	print_yes_no_row($vbphrase['photoplog_can_use_album_feature'], 'photoplog_category[options][canusealbumfeature]', $photoplog_category_options['canusealbumfeature']);
	print_yes_no_row($vbphrase['photoplog_can_suggest_categories'], 'photoplog_category[options][cansuggestcategories]', $photoplog_category_options['cansuggestcategories']);
	print_yes_no_row($vbphrase['photoplog_can_use_ftp_import'], 'photoplog_category[options][canuseftpimport]', $photoplog_category_options['canuseftpimport']);
	print_yes_no_row($vbphrase['photoplog_can_create_unmoderated_categories'], 'photoplog_category[options][cancreateunmoderatedcategories]', $photoplog_category_options['cancreateunmoderatedcategories']);
	print_submit_row($vbphrase['photoplog_save']);
}

if ($_REQUEST['do'] == 'doedit')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'catid' => TYPE_UINT,
		'group' => TYPE_UINT,
		'photoplogcustomperm' => TYPE_UINT,
		'photoplog_category' => TYPE_ARRAY,
		'photoplogmaxfilesize' => TYPE_UINT,
		'photoplogmaxfilelimit' => TYPE_UINT
	));

	$photoplog_catid = $vbulletin->GPC['catid'];
	$photoplog_usergroupid = $vbulletin->GPC['group'];
	$photoplog_custom_permissions = $vbulletin->GPC['photoplogcustomperm'];
	$photoplog_category = $vbulletin->GPC['photoplog_category'];
	$photoplog_category_filesize = $vbulletin->GPC['photoplogmaxfilesize'];
	$photoplog_category_filelimit = $vbulletin->GPC['photoplogmaxfilelimit'];

	if (!$photoplog_custom_permissions)
	{
		$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_permissions
			WHERE usergroupid = ".intval($photoplog_usergroupid)."
			AND catid = ".intval($photoplog_catid)."
		");
	}
	else
	{
		$photoplog_category_options = $photoplog_category['options'];
		foreach ($photoplog_category_options AS $photoplog_key => $photoplog_val)
		{
			$photoplog_category_options["$photoplog_key"] = intval(trim(strval($photoplog_val)));
		}

		require_once(DIR . '/includes/functions_misc.php');

		$photoplog_category_bitopts = convert_array_to_bits($photoplog_category_options, $photoplog_categoryoptpermissions, 1);

		$photoplog_row_check = $db->query_first("SELECT permid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_permissions
			WHERE usergroupid = ".intval($photoplog_usergroupid)."
			AND catid = ".intval($photoplog_catid)."
		");

		if ($photoplog_row_check && $photoplog_row_check['permid'])
		{
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_permissions
				SET options = ".intval($photoplog_category_bitopts).",
					maxfilesize = ".intval($photoplog_category_filesize).",
					maxfilelimit = ".intval($photoplog_category_filelimit)."
				WHERE usergroupid = ".intval($photoplog_usergroupid)."
				AND catid = ".intval($photoplog_catid)."
			");
		}
		else
		{
			$db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_permissions
				(options, maxfilesize, maxfilelimit, catid, usergroupid)
				VALUES (
					".intval($photoplog_category_bitopts).",
					".intval($photoplog_category_filesize).",
					".intval($photoplog_category_filelimit).",
					".intval($photoplog_catid).",
					".intval($photoplog_usergroupid)."
				)
			");
		}
	}

	print_cp_redirect("photoplog_permission.php?".$vbulletin->session->vars['sessionurl']."do=modify", 1);
}

if ($_REQUEST['do'] == 'reset')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'catid' => TYPE_UINT
	));

	$photoplog_catid = $vbulletin->GPC['catid'];

	$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_permissions
		WHERE catid = ".intval($photoplog_catid)."
	");

	print_cp_redirect("photoplog_permission.php?".$vbulletin->session->vars['sessionurl']."do=modify", 1);
}

if ($_REQUEST['do'] == 'deny')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'catid' => TYPE_UINT
	));

	$photoplog_catid = $vbulletin->GPC['catid'];
	$photoplog_sql_bits = array();

	$db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_permissions
		WHERE catid = ".intval($photoplog_catid)."
	");

	foreach ($vbulletin->usergroupcache AS $photoplog_usergroupid => $photoplog_cachearray)
	{
		$photoplog_usergroup_id = $photoplog_cachearray['usergroupid'];
		$photoplog_sql_bits[] = "(
						0,
						0,
						0,
						".intval($photoplog_catid).",
						".intval($photoplog_usergroup_id)."
		)";
	}

	if ($photoplog_sql_bits)
	{
		$db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_permissions
					(options, maxfilesize, maxfilelimit, catid, usergroupid)
					VALUES 
					".implode(",",$photoplog_sql_bits)."
		");
	}

	print_cp_redirect("photoplog_permission.php?".$vbulletin->session->vars['sessionurl']."do=modify", 1);
}

print_cp_footer();

?>