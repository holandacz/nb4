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

$photoplog_list_categories = array();
photoplog_list_categories($photoplog_list_categories,-1,$vbphrase['photoplog_no_one']);

$photoplog_categoryoptions = array(
	'allowhtml' => 1,
	'allowsmilies' => 2,
	'allowbbcode' => 4,
	'allowimgcode' => 8,
	'allowparseurl' => 16,
	'allowcomments' => 32,
	'issearchable' => 64,
	'ismembersfolder' => 128,
	'actasdivider' => 256,
	'allowdeschtml' => 512,
	'openforsubcats' => 1024
);

$photoplog_ds_catopts = photoplog_fetch_ds_cat();

print_cp_header($vbphrase['photoplog_category_manager']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'modify';
}

if ($_REQUEST['do'] == 'decline')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'suggestid' => TYPE_UINT
	));

	print_form_header('photoplog_category', 'dodecline');
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	construct_hidden_code('suggestid', $vbulletin->GPC['suggestid']);

	print_table_header($vbphrase['photoplog_confirm_decline']);
	print_description_row($vbphrase['photoplog_you_are_about_to_decline'].' '.$vbphrase['photoplog_are_you_sure']);
	print_submit_row($vbphrase['photoplog_yes'], '', 2, $vbphrase['photoplog_no']);
}

if ($_REQUEST['do'] == 'dodecline')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'suggestid' => TYPE_UINT
	));

	$photoplog_suggestid = $vbulletin->GPC['suggestid'];

	$photoplog_where_sql = 'WHERE catid = 0 AND approve = 0';
	if ($photoplog_suggestid)
	{
		$photoplog_where_sql = 'WHERE suggestid = '.intval($photoplog_suggestid);
	}

	if ($vbulletin->options['photoplog_user_email'])
	{
		$photoplog_moderate_cats = $db->query_read("SELECT userid, title
			FROM " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
			$photoplog_where_sql
			ORDER BY dateline DESC
		");

		while ($photoplog_moderate_cat = $db->fetch_array($photoplog_moderate_cats))
		{
			$photoplog_fetch_userinfo = $db->query_first("SELECT username,email
				FROM ".TABLE_PREFIX."user
				WHERE userid = ".intval($photoplog_moderate_cat['userid'])."
				AND (options & ".intval($vbulletin->bf_misc_useroptions['adminemail']).")
			");
			if ($photoplog_fetch_userinfo)
			{
				$photoplog_category = strval($photoplog_moderate_cat['title']);
				$photoplog_username = unhtmlspecialchars($photoplog_fetch_userinfo['username']);
				$photoplog_subject = $photoplog_message = '';
				eval(fetch_email_phrases('photoplog_declined_category', -1, '', 'photoplog_'));
				vbmail($photoplog_fetch_userinfo['email'], $photoplog_subject, $photoplog_message, true);
			}
			$db->free_result($photoplog_fetch_userinfo);
		}

		$db->free_result($photoplog_moderate_cats);
	}

	if ($photoplog_suggestid)
	{
		// not approved: 0, approved: 1, declined: 2, cat deleted: 3
		$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
			SET approve = 2
			WHERE suggestid = ".intval($photoplog_suggestid)."
		");
	}
	else
	{
		$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
			SET approve = 2
			WHERE catid = 0 AND approve = 0
		");
	}

	print_cp_redirect("photoplog_category.php?".$vbulletin->session->vars['sessionurl']."do=moderate", 1);
}

if ($_REQUEST['do'] == 'moderate')
{
	$photoplog_moderate_cats = $db->query_read("SELECT suggestid, userid, title
		FROM " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
		WHERE catid = 0 AND approve = 0
		ORDER BY dateline DESC
	");

	if ($photoplog_moderate_cats)
	{
		print_form_header('photoplog_category', 'decline');
		construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
		construct_hidden_code('suggestid', 0);
		print_table_header($vbphrase['photoplog_moderate_categories'], 3);

		print_cells_row(array($vbphrase['photoplog_title'],
					'<nobr>' . $vbphrase['photoplog_submitted_by'] . '</nobr>',
					$vbphrase['photoplog_controls']), 1, '', -1);

		$photoplog_cnt_bits = 0;

		while ($photoplog_moderate_cat = $db->fetch_array($photoplog_moderate_cats))
		{
			$photoplog_cnt_bits ++;

			$photoplog_suggestid = intval($photoplog_moderate_cat['suggestid']);
			$photoplog_title = htmlspecialchars_uni($photoplog_moderate_cat['title']);
			$photoplog_userinfo = fetch_userinfo(intval($photoplog_moderate_cat['userid']));

			$photoplog_username = $photoplog_userinfo['username'];
			$photoplog_userid = $photoplog_userinfo['userid'];
			$photoplog_suggested_by = "<a href=\"user.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&u=".$photoplog_userid."\">".$photoplog_username."</a>";

			$bgclass = fetch_row_bgclass();
			echo "
				<tr>
					<td class=\"$bgclass\" width=\"100%\"><a href=\"photoplog_category.php?" . $vbulletin->session->vars['sessionurl'] . "do=review&amp;suggestid=".$photoplog_suggestid."\">".$photoplog_title."</a></td>
					<td class=\"$bgclass\">$photoplog_suggested_by</td>
					<td class=\"$bgclass\"><nobr><a href=\"photoplog_category.php?" . $vbulletin->session->vars['sessionurl'] . "do=review&amp;suggestid=".$photoplog_suggestid."\">".$vbphrase['photoplog_review']."</a> <a href=\"photoplog_category.php?" . $vbulletin->session->vars['sessionurl'] . "do=decline&amp;suggestid=".$photoplog_suggestid."\">".$vbphrase['photoplog_decline']."</a></nobr></td>
				</tr>
			";
		}

		if ($photoplog_cnt_bits)
		{
			print_table_footer(3, "<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . $vbphrase['photoplog_decline_all'] . "\" accesskey=\"s\" />");
		}
		else
		{
			print_description_row($vbphrase['photoplog_nothing_to_moderate'], 0, 3);
			print_table_footer();
		}
	}
	else
	{
		print_form_header('', '');
		construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
		print_table_header($vbphrase['photoplog_moderate_categories'], 1);
		print_description_row($vbphrase['photoplog_bad_luck'], 0, 2);
		print_table_footer();
	}
}

if ($_REQUEST['do'] == 'add')
{
	$photoplog_category = array(
		'title' => '',
		'description' => '',
		'displayorder' => 1,
		'parentid' => -1,
		'allowhtml' => 0,
		'allowsmilies' => 1,
		'allowbbcode' => 1,
		'allowimgcode' => 0,
		'allowparseurl' => 0,
		'allowcomments' => 1,
		'issearchable' => 1,
		'ismembersfolder' => 0,
		'actasdivider' => 0,
		'allowdeschtml' => 0,
		'openforsubcats' => 0
	);

	print_form_header('photoplog_category', 'doadd');
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	print_table_header($vbphrase['photoplog_add_new_category']);
	print_input_row($vbphrase['photoplog_title'], 'photoplog_category[title]', $photoplog_category['title']);
	print_textarea_row($vbphrase['photoplog_description'], 'photoplog_category[description]', $photoplog_category['description']);
	print_input_row("$vbphrase[photoplog_display_order]<dfn>$vbphrase[photoplog_zero_equals_no_display]</dfn>", 'photoplog_category[displayorder]', $photoplog_category['displayorder']);
	print_select_row($vbphrase['photoplog_parent_category'], 'photoplog_category[parentid]', $photoplog_list_categories, $photoplog_category['parentid'], true, 0, false);
	print_table_header($vbphrase['photoplog_enable_disable_features']);
	print_yes_no_row($vbphrase['photoplog_allow_html'], 'photoplog_category[options][allowhtml]', $photoplog_category['allowhtml']);
	print_yes_no_row($vbphrase['photoplog_allow_smilies'], 'photoplog_category[options][allowsmilies]', $photoplog_category['allowsmilies']);
	print_yes_no_row($vbphrase['photoplog_allow_bbcode'], 'photoplog_category[options][allowbbcode]', $photoplog_category['allowbbcode']);
	print_yes_no_row($vbphrase['photoplog_allow_img_code'], 'photoplog_category[options][allowimgcode]', $photoplog_category['allowimgcode']);
	print_yes_no_row($vbphrase['photoplog_allow_parse_url'], 'photoplog_category[options][allowparseurl]', $photoplog_category['allowparseurl']);
	print_yes_no_row($vbphrase['photoplog_allow_comments'], 'photoplog_category[options][allowcomments]', $photoplog_category['allowcomments']);
	print_yes_no_row($vbphrase['photoplog_is_searchable'], 'photoplog_category[options][issearchable]', $photoplog_category['issearchable']);
	print_yes_no_row($vbphrase['photoplog_is_members_folder'], 'photoplog_category[options][ismembersfolder]', $photoplog_category['ismembersfolder']);
	print_yes_no_row($vbphrase['photoplog_act_as_divider'], 'photoplog_category[options][actasdivider]', $photoplog_category['actasdivider']);
	print_yes_no_row($vbphrase['photoplog_allow_desc_html'], 'photoplog_category[options][allowdeschtml]', $photoplog_category['allowdeschtml']);
	print_yes_no_row($vbphrase['photoplog_open_for_subcats'], 'photoplog_category[options][openforsubcats]', $photoplog_category['openforsubcats']);
	print_submit_row($vbphrase['photoplog_save']);
}

if ($_REQUEST['do'] == 'doadd')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'suggestid' => TYPE_UINT,
		'photoplog_category' => TYPE_ARRAY
	));

	$photoplog_suggestid = $vbulletin->GPC['suggestid'];
	$photoplog_category = $vbulletin->GPC['photoplog_category'];

	$photoplog_category_title = trim(strval($photoplog_category['title']));
	$photoplog_category_description = trim(strval($photoplog_category['description']));
	$photoplog_category_displayorder = intval(trim(strval($photoplog_category['displayorder'])));
	$photoplog_category_parentid = intval(trim(strval($photoplog_category['parentid'])));

	$photoplog_category_options = $photoplog_category['options'];
	foreach ($photoplog_category_options AS $photoplog_key => $photoplog_val)
	{
		$photoplog_category_options["$photoplog_key"] = intval(trim(strval($photoplog_val)));
	}

	require_once(DIR . '/includes/functions_misc.php');

	$photoplog_category_bitopts = convert_array_to_bits($photoplog_category_options, $photoplog_categoryoptions, 1);

	if (photoplog_insert_category($photoplog_category_title,$photoplog_category_description,
		$photoplog_category_displayorder, $photoplog_category_parentid, $photoplog_category_bitopts,
		$photoplog_ds_catopts,$photoplog_suggestid))
	{
		if ($photoplog_suggestid)
		{
			if ($vbulletin->options['photoplog_user_email'])
			{
				$photoplog_moderate_cat = $db->query_first("SELECT userid, title
					FROM " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
					WHERE suggestid = ".intval($photoplog_suggestid)."
				");
				if ($photoplog_moderate_cat)
				{
					$photoplog_fetch_userinfo = $db->query_first("SELECT username,email
						FROM ".TABLE_PREFIX."user
						WHERE userid = ".intval($photoplog_moderate_cat['userid'])."
						AND (options & ".intval($vbulletin->bf_misc_useroptions['adminemail']).")
					");
					if ($photoplog_fetch_userinfo)
					{
						$photoplog_category = strval($photoplog_moderate_cat['title']);
						$photoplog_username = unhtmlspecialchars($photoplog_fetch_userinfo['username']);
						$photoplog_subject = $photoplog_message = '';
						eval(fetch_email_phrases('photoplog_approved_category', -1, '', 'photoplog_'));
						vbmail($photoplog_fetch_userinfo['email'], $photoplog_subject, $photoplog_message, true);
					}
					$db->free_result($photoplog_fetch_userinfo);
				}
			}
		}
		print_cp_redirect("photoplog_category.php?".$vbulletin->session->vars['sessionurl']."do=modify", 1);
	}
	else
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_cat_insert']);
	}
}

if ($_REQUEST['do'] == 'edit' || $_REQUEST['do'] == 'review')
{
	if ($_REQUEST['do'] == 'edit')
	{
		$vbulletin->input->clean_array_gpc('g', array(
			'catid' => TYPE_UINT
		));

		$photoplog_catid = $vbulletin->GPC['catid'];

		$photoplog_category_info = $db->query_first("SELECT title,
				description, displayorder, parentid, options
			FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
			WHERE catid = ".intval($photoplog_catid)."
		");
	}
	else if ($_REQUEST['do'] == 'review')
	{
		$vbulletin->input->clean_array_gpc('g', array(
			'suggestid' => TYPE_UINT
		));

		$photoplog_suggestid = $vbulletin->GPC['suggestid'];

		$photoplog_category_info = $db->query_first("SELECT title,
				description, displayorder, parentid, options
			FROM " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
			WHERE suggestid = ".intval($photoplog_suggestid)."
		");
	}

	if (!$photoplog_category_info)
	{
		print_stop_message(no_results_matched_your_query);
	}
	else
	{
		$photoplog_category = $photoplog_category_info;
		$photoplog_category_options = convert_bits_to_array($photoplog_category_info['options'], $photoplog_categoryoptions);
		unset($photoplog_category_info);

		if ($_REQUEST['do'] == 'edit')
		{
			print_form_header('photoplog_category', 'doedit');
			construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
			construct_hidden_code('catid', $photoplog_catid);
			print_table_header($vbphrase['photoplog_edit_this_category']);
		}
		else if ($_REQUEST['do'] == 'review')
		{
			print_form_header('photoplog_category', 'doadd');
			construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
			construct_hidden_code('suggestid', $photoplog_suggestid);
			print_table_header($vbphrase['photoplog_add_new_category']);
		}

		print_input_row($vbphrase['photoplog_title'], 'photoplog_category[title]', $photoplog_category['title']);
		print_textarea_row($vbphrase['photoplog_description'], 'photoplog_category[description]', $photoplog_category['description']);
		print_input_row("$vbphrase[photoplog_display_order]<dfn>$vbphrase[photoplog_zero_equals_no_display]</dfn>", 'photoplog_category[displayorder]', $photoplog_category['displayorder']);
		print_select_row($vbphrase['photoplog_parent_category'], 'photoplog_category[parentid]', $photoplog_list_categories, $photoplog_category['parentid'], true, 0, false);
		print_table_header($vbphrase['photoplog_enable_disable_features']);
		print_yes_no_row($vbphrase['photoplog_allow_html'], 'photoplog_category[options][allowhtml]', $photoplog_category_options['allowhtml']);
		print_yes_no_row($vbphrase['photoplog_allow_smilies'], 'photoplog_category[options][allowsmilies]', $photoplog_category_options['allowsmilies']);
		print_yes_no_row($vbphrase['photoplog_allow_bbcode'], 'photoplog_category[options][allowbbcode]', $photoplog_category_options['allowbbcode']);
		print_yes_no_row($vbphrase['photoplog_allow_img_code'], 'photoplog_category[options][allowimgcode]', $photoplog_category_options['allowimgcode']);
		print_yes_no_row($vbphrase['photoplog_allow_parse_url'], 'photoplog_category[options][allowparseurl]', $photoplog_category_options['allowparseurl']);
		print_yes_no_row($vbphrase['photoplog_allow_comments'], 'photoplog_category[options][allowcomments]', $photoplog_category_options['allowcomments']);
		print_yes_no_row($vbphrase['photoplog_is_searchable'], 'photoplog_category[options][issearchable]', $photoplog_category_options['issearchable']);
		print_yes_no_row($vbphrase['photoplog_is_members_folder'], 'photoplog_category[options][ismembersfolder]', $photoplog_category_options['ismembersfolder']);
		print_yes_no_row($vbphrase['photoplog_act_as_divider'], 'photoplog_category[options][actasdivider]', $photoplog_category_options['actasdivider']);
		print_yes_no_row($vbphrase['photoplog_allow_desc_html'], 'photoplog_category[options][allowdeschtml]', $photoplog_category_options['allowdeschtml']);
		print_yes_no_row($vbphrase['photoplog_open_for_subcats'], 'photoplog_category[options][openforsubcats]', $photoplog_category_options['openforsubcats']);

		if ($_REQUEST['do'] == 'edit')
		{
			print_submit_row($vbphrase['photoplog_save']);
		}
		else if ($_REQUEST['do'] == 'review')
		{
			print_submit_row($vbphrase['photoplog_approve'],'',2,$vbphrase['photoplog_go_back']);
		}
	}
}

if ($_REQUEST['do'] == 'doedit')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'catid' => TYPE_UINT,
		'photoplog_category' => TYPE_ARRAY
	));

	$photoplog_catid = $vbulletin->GPC['catid'];
	$photoplog_category = $vbulletin->GPC['photoplog_category'];

	$photoplog_category_parentid = intval(trim(strval($photoplog_category['parentid'])));

	$photoplog_list_children = array();
	$photoplog_list_relatives = array();
	photoplog_relative_list($photoplog_list_children, $photoplog_list_relatives);
	$photoplog_list_relatives_array = $photoplog_list_relatives[$photoplog_catid];

	if (
		$photoplog_catid == $photoplog_category_parentid
			||
		in_array($photoplog_category_parentid, $photoplog_list_relatives_array)
	)
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_parent']);
	}

	$photoplog_category_info = $db->query_first("SELECT parentid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
		WHERE catid = ".intval($photoplog_catid)."
	");

	$photoplog_original_parentid = 0;
	if (!$photoplog_category_info)
	{
		print_stop_message(no_results_matched_your_query);
	}
	else
	{
		$photoplog_original_parentid = intval($photoplog_category_info['parentid']);
	}

	$photoplog_category_title = trim(strval($photoplog_category['title']));
	$photoplog_category_description = trim(strval($photoplog_category['description']));
	$photoplog_category_displayorder = intval(trim(strval($photoplog_category['displayorder'])));

	$photoplog_category_options = $photoplog_category['options'];
	foreach ($photoplog_category_options AS $photoplog_key => $photoplog_val)
	{
		$photoplog_category_options["$photoplog_key"] = intval(trim(strval($photoplog_val)));
	}

	require_once(DIR . '/includes/functions_misc.php');

	$photoplog_category_bitopts = convert_array_to_bits($photoplog_category_options, $photoplog_categoryoptions, 1);

	if (photoplog_replace_into_category($photoplog_catid, $photoplog_category_title,
		$photoplog_category_description, $photoplog_category_displayorder, $photoplog_category_parentid,
		$photoplog_category_bitopts, $photoplog_ds_catopts))
	{
		if ($photoplog_category_parentid != $photoplog_original_parentid)
		{
			$photoplog_catids_array = array($photoplog_catid, $photoplog_category_parentid, $photoplog_original_parentid);
//			photoplog_regenerate_counts_table_v2($photoplog_catids_array);
		}
		print_cp_redirect("photoplog_category.php?".$vbulletin->session->vars['sessionurl']."do=modify", 1);
	}
	else
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_cat_replace']);
	}
}

if ($_REQUEST['do'] == 'modify')
{
	print_form_header('photoplog_category', 'doorder');
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	construct_hidden_code('catid', $photoplog_catid);
	print_table_header($vbphrase['photoplog_category_manager'], 3);
	print_description_row($vbphrase['photoplog_if_you_change_display_order'], 0, 3);

	print_cells_row(array($vbphrase['photoplog_title'],
				'<nobr>' . $vbphrase['photoplog_display_order'] . '</nobr>',
				$vbphrase['photoplog_controls']), 1, '', -1);

	$photoplog_category = array();
	foreach ($photoplog_list_categories AS $photoplog_key => $photoplog_value)
	{
		if ($photoplog_key > 0)
		{
			$photoplog_catid = $photoplog_key;
			$photoplog_dashes = '';
			$photoplog_title = htmlspecialchars_uni(trim($photoplog_value));

			if (eregi("^([-]+[ ])(.*)",$photoplog_title,$photoplog_regs))
			{
				$photoplog_dashes = $photoplog_regs[1];
				$photoplog_title = $photoplog_regs[2];
			}

			$photoplog_category['displayorder'] = $photoplog_ds_catopts[$photoplog_catid]['displayorder'];

			$bgclass = fetch_row_bgclass();
			echo "
				<tr>
					<td class=\"$bgclass\" width=\"100%\">".$photoplog_dashes."<a href=\"photoplog_category.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&amp;catid=".$photoplog_catid."\">".$photoplog_title."</a></td>
					<td class=\"$bgclass\"><input type=\"text\" class=\"bginput\" name=\"photoplog_category[".$photoplog_catid."]\" value=\"".$photoplog_category['displayorder']."\" size=\"5\" /></td>
					<td class=\"$bgclass\"><nobr><a href=\"photoplog_category.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&amp;catid=".$photoplog_catid."\">".$vbphrase['photoplog_edit']."</a> <a href=\"photoplog_category.php?" . $vbulletin->session->vars['sessionurl'] . "do=delete&amp;catid=".$photoplog_catid."\">".$vbphrase['photoplog_delete']."</a></nobr></td>
				</tr>
			";
		}
	}

	print_table_footer(3, "<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . $vbphrase['photoplog_save_display_order'] . "\" accesskey=\"s\" />" . construct_button_code($vbphrase['photoplog_add_new_category'], "photoplog_category.php?" . $vbulletin->session->vars['sessionurl'] . "do=add"));
}

if ($_REQUEST['do'] == 'doorder')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'photoplog_category' => TYPE_ARRAY_UINT
	));

	if (!empty($vbulletin->GPC['photoplog_category']))
	{
		$photoplog_dscatopts = $photoplog_ds_catopts;

		$photoplog_sql = '';
		foreach ($vbulletin->GPC['photoplog_category'] AS $photoplog_catid => $photoplog_displayorder)
		{
			$photoplog_sql .= "WHEN " . intval($photoplog_catid) . " THEN " . intval($photoplog_displayorder) . "\n";
			$photoplog_dscatopts["$photoplog_catid"]['displayorder'] = intval($photoplog_displayorder);
		}

		if (
			$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_categories
				SET displayorder = CASE catid
				$photoplog_sql ELSE displayorder END
			")
		)
		{
			$db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_categories
				ORDER BY parentid, displayorder, catid
			");
			build_datastore('photoplog_dscat', serialize($photoplog_dscatopts));
			print_cp_redirect("photoplog_category.php?".$vbulletin->session->vars['sessionurl']."do=modify", 1);
		}
		else
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_bad_order_update']);
		}
	}
	else
	{
		print_stop_message('no_results_matched_your_query');
	}
}

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'catid' => TYPE_UINT
	));

	print_form_header('photoplog_category', 'dodelete');
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	construct_hidden_code('catid', $vbulletin->GPC['catid']);

	print_table_header($vbphrase['photoplog_confirm_deletion']);
	print_description_row($vbphrase['photoplog_confirm_delete_category']);
	print_submit_row($vbphrase['photoplog_yes'], '', 2, $vbphrase['photoplog_no']);
}

if ($_REQUEST['do'] == 'dodelete')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'catid' => TYPE_UINT
	));

	$photoplog_catid = $vbulletin->GPC['catid'];
	$photoplog_dscatopts = $photoplog_ds_catopts;

	$photoplog_child_list = array();
	$photoplog_parent_list = array();
	photoplog_child_list($photoplog_child_list, $photoplog_parent_list, $photoplog_catid);

	$photoplog_catids_array = array_merge(array($photoplog_catid), $photoplog_child_list, $photoplog_parent_list);

	$photoplog_sql = "WHERE catid = ". intval($photoplog_catid);
	unset($photoplog_dscatopts["$photoplog_catid"]);

	if (!empty($photoplog_child_list))
	{
		$photoplog_sql = "WHERE catid IN (" . intval($photoplog_catid) . "," . implode(",",$photoplog_child_list) . ")";
		foreach ($photoplog_child_list AS $photoplog_childid)
		{
			unset($photoplog_dscatopts["$photoplog_childid"]);
		}
	}

	if (
		($db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			SET catid = 0
			$photoplog_sql
		"))
			&&
		($db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			SET catid = 0
			$photoplog_sql
		"))
			&&
		($db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
			$photoplog_sql
		"))
			&&
		($db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_catcounts
			$photoplog_sql
		"))
	)
	{
		$db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_categories
			ORDER BY parentid, displayorder, catid
		");
		// not approved: 0, approved: 1, declined: 2, cat deleted: 3
		$db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
			SET approve = 3
			$photoplog_sql
		");
//		photoplog_regenerate_counts_table_v2($photoplog_catids_array);
		photoplog_custom_field_delete_category($photoplog_catid,$photoplog_child_list);
		build_datastore('photoplog_dscat', serialize($photoplog_dscatopts));
		print_cp_redirect("photoplog_category.php?".$vbulletin->session->vars['sessionurl']."do=modify", 1);
	}
	else
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_cat_delete']);
	}
}

print_cp_footer();

?>