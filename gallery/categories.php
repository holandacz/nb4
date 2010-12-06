<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','categories');
define('PHOTOPLOG_LEVEL','categories');
require_once('./settings.php');

// ######################### Start Categories Page ########################
($hook = vBulletinHook::fetch_hook('photoplog_categories_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'suggest';
}

$photoplog['suggestcat'] = 0;
$photoplog['createcat'] = 0;
$photoplog['dowhat'] = '';
$photoplog['dophrase'] = '';

if ($_REQUEST['do'] == 'suggest' || $_REQUEST['do'] == 'create')
{
	if ($_REQUEST['do'] == 'suggest' && defined('PHOTOPLOG_USER16') && $vbulletin->userinfo['userid'])
	{
		$photoplog['suggestcat'] = 1;
		$photoplog['dowhat'] = 'dosuggest';
		$photoplog['dophrase'] = $vbphrase[photoplog_suggest_category];
	}
	if ($_REQUEST['do'] == 'create' && defined('PHOTOPLOG_USER17') && $vbulletin->userinfo['userid'])
	{
		$photoplog['createcat'] = 1;
		$photoplog['dowhat'] = 'docreate';
		$photoplog['dophrase'] = $vbphrase[photoplog_create_category];
	}

	if (!$photoplog['suggestcat'] && !$photoplog['createcat'])
	{
		photoplog_index_bounce();
	}

	require_once(DIR . '/includes/functions_editor.php');
	$photoplog['textareacols'] = fetch_textarea_width();
	$photoplog_list_categories_row = $photoplog_list_categories;

	if ($_REQUEST['do'] == 'suggest')
	{
		$photoplog_list_categories_row[-1] = $vbphrase['photoplog_no_one'];
	}
	if ($_REQUEST['do'] == 'create')
	{
		$photoplog_list_categories_row[-1] = $vbphrase['photoplog_select_one'];

		foreach ($photoplog_ds_catopts AS $photoplog_ds_catid => $photoplog_ds_value)
		{
			$photoplog_cat_opts = convert_bits_to_array($photoplog_ds_value['options'], $photoplog_categoryoptions);
			if (!$photoplog_cat_opts['openforsubcats'])
			{
				$photoplog_perm_not_allowed_bits[] = $photoplog_ds_catid;
			}
		}
		$photoplog_perm_not_allowed_bits = array_unique($photoplog_perm_not_allowed_bits);
	}

	if (!empty($photoplog_perm_not_allowed_bits))
	{
		array_walk($photoplog_list_categories_row, 'photoplog_append_key', '');
		$photoplog_list_categories_row = array_flip(array_diff(array_flip($photoplog_list_categories_row),$photoplog_perm_not_allowed_bits));
		array_walk($photoplog_list_categories_row, 'photoplog_remove_key', '');
	}

	if (count($photoplog_list_categories_row) == 1)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_categories']);
	}

	$photoplog['select_row'] = "<select name=\"catid\" id=\"sel_catid\" tabindex=\"1\">\n";
	$photoplog['select_row'] .= photoplog_select_options($photoplog_list_categories_row, -1);
	$photoplog['select_row'] .= "</select>\n";

	($hook = vBulletinHook::fetch_hook('photoplog_categories_form')) ? eval($hook) : false;

	photoplog_output_page('photoplog_category_form', $vbphrase['photoplog_suggest_category']);
}

if ($_POST['do'] == 'dosuggest' || $_POST['do'] == 'docreate')
{
	if ($_POST['do'] == 'dosuggest' && (!defined('PHOTOPLOG_USER16') || !$vbulletin->userinfo['userid']))
	{
		photoplog_index_bounce();
	}

	if ($_POST['do'] == 'docreate' && (!defined('PHOTOPLOG_USER17') || !$vbulletin->userinfo['userid']))
	{
		photoplog_index_bounce();
	}

	if ($photoplog_perm_catid && in_array($photoplog_perm_catid, $photoplog_perm_not_allowed_bits))
	{
		photoplog_index_bounce();
	}

	$vbulletin->input->clean_array_gpc('p', array(
		'title' => TYPE_STR,
		'description' => TYPE_STR,
		'catid' => TYPE_UINT
	));

	($hook = vBulletinHook::fetch_hook('photoplog_categories_do_start')) ? eval($hook) : false;

	$photoplog['title'] = $vbulletin->GPC['title'];
	$photoplog['description'] = $vbulletin->GPC['description'];
	$photoplog_parentid = ($vbulletin->GPC['catid']) ? $vbulletin->GPC['catid'] : -1;

	// allowhtml: 0, allowsmilies: 1, allowbbcode: 1, allowimgcode: 0, allowparseurl: 0,
	// allowcomments: 1, issearchable: 1, ismembersfolder: 0, actasdivider: 0, allowdeschtml: 0,
	// openforsubcats: 0 => 102
	$photoplog_options = 102;
	$photoplog_displayorder = 1;
	$photoplog['userid'] = $vbulletin->userinfo['userid'];

	if (vbstrlen($photoplog['title']) == 0)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_title_too_short']);
	}

	if ($_POST['do'] == 'dosuggest' && $photoplog_parentid != -1 && !in_array($photoplog_parentid,array_keys($photoplog_ds_catopts)))
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_category']);
	}

	if ($_POST['do'] == 'docreate')
	{
		if ($photoplog_parentid == -1)
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_category']);
		}
	
		$photoplog_cat_perm = array();
		$photoplog_cat_perm['cancreateunmoderatedcategories'] = 0;
		$photoplog_cat_opts = array();
		$photoplog_cat_opts['openforsubcats'] = 0;

		if (isset($photoplog_inline_bits[$photoplog_parentid]))
		{
			$photoplog_cat_perm = convert_bits_to_array($photoplog_inline_bits[$photoplog_parentid], $photoplog_categoryoptpermissions);
		}
		if (!$photoplog_cat_perm['cancreateunmoderatedcategories'])
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_permission']);
		}
		unset($photoplog_cat_perm);

		if (isset($photoplog_ds_catopts[$photoplog_parentid]['options']))
		{
			$photoplog_cat_opts = convert_bits_to_array($photoplog_ds_catopts[$photoplog_parentid]['options'], $photoplog_categoryoptions);
		}
		if (!$photoplog_cat_opts['openforsubcats'])
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_bad_category']);
		}
		unset($photoplog_cat_opts);
	}

	if ($_POST['do'] == 'dosuggest')
	{
		if (
			$db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
				(userid,title,description,displayorder,parentid,options,dateline)
				VALUES
				(
					".intval($photoplog['userid']).",
					'".$db->escape_string($photoplog['title'])."',
					'".$db->escape_string($photoplog['description'])."',
					".intval($photoplog_displayorder).",
					".intval($photoplog_parentid).",
					".intval($photoplog_options).",
					".intval(TIMENOW)."
				)
			")
		)
		{
			if ($vbulletin->options['photoplog_admin_email'])
			{
				$photoplog_subject = $photoplog_message = '';
				eval(fetch_email_phrases('photoplog_mod_category', -1, '', 'photoplog_'));
				vbmail($vbulletin->options['webmasteremail'], $photoplog_subject, $photoplog_message, true);
			}
		}
	}

	if ($_POST['do'] == 'docreate')
	{
		if (
			$db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_categories
				(title,description,displayorder,parentid,options)
				VALUES
				(
					'".$db->escape_string($photoplog['title'])."',
					'".$db->escape_string($photoplog['description'])."',
					".intval($photoplog_displayorder).",
					".intval($photoplog_parentid).",
					".intval($photoplog_options)."
				)
			")
		)
		{
			$photoplog_catid = $db->insert_id();

			$db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_categories
				ORDER BY parentid, displayorder, catid
			");

			$photoplog_dscatopts = $photoplog_ds_catopts;
			$photoplog_dscatopts[$photoplog_catid] = array(
				'title' => $photoplog['title'],
				'description' => $photoplog['description'],
				'displayorder' => $photoplog_displayorder,
				'parentid' => $photoplog_parentid,
				'options' => $photoplog_options
			);
			build_datastore('photoplog_dscat', serialize($photoplog_dscatopts));
			unset($photoplog_dscatopts);

			$photoplog_parent_fields = $db->query_read("SELECT groupid,
					displayorder, hidden, active, parentid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
				WHERE catid = " . intval($photoplog_parentid) . "
				AND inherited != 0
			");

			$photoplog_parent_values = array();
			while ($photoplog_parent_field = $db->fetch_array($photoplog_parent_fields))
			{
				$photoplog_parent_values[] = "
					(
						" . intval($photoplog_catid) . ",
						" . intval($photoplog_parent_field['groupid']) . ",
						" . intval($photoplog_parent_field['displayorder']) . ",
						" . intval($photoplog_parent_field['hidden']) . ",
						" . intval($photoplog_parent_field['active']) . ",
						1,
						1,
						" . intval($photoplog_parent_field['parentid']) . ",
						''
					)
				";
			}
			$db->free_result($photoplog_parent_fields);

			if (count($photoplog_parent_values) > 0)
			{
				$photoplog_parent_values = implode(", ", $photoplog_parent_values);

				$db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_customfields
					(catid, groupid, displayorder, hidden, active, protected, inherited, parentid, info)
					VALUES " . $photoplog_parent_values . "
				");
			}
			unset($photoplog_parent_values);

			if ($vbulletin->options['photoplog_admin_email'])
			{
				$photoplog_subject = $photoplog_message = '';
				eval(fetch_email_phrases('photoplog_made_category', -1, '', 'photoplog_'));
				vbmail($vbulletin->options['webmasteremail'], $photoplog_subject, $photoplog_message, true);
			}
		}
	}

	$photoplog_return_url = $photoplog['location'].'/index.php?'.$vbulletin->session->vars['sessionurl'].'c='.$photoplog_parentid;
	if ($photoplog_parentid < 0)
	{
		$photoplog_return_url = $photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q'];
	}

	($hook = vBulletinHook::fetch_hook('photoplog_categories_do_complete')) ? eval($hook) : false;

	$vbulletin->url = $photoplog_return_url;
	eval(print_standard_redirect('redirect_photoplog_submissionthanks'));
}

($hook = vBulletinHook::fetch_hook('photoplog_categories_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'suggest' && $_POST['do'] != 'dosuggest' && $_REQUEST['do'] != 'create' && $_POST['do'] != 'docreate')
{
	photoplog_index_bounce();
}

?>