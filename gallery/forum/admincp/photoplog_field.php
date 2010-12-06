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
$phrasegroups = array('forum', 'cpuser', 'photoplog', 'profilefield');
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

$photoplog_header = $vbphrase['photoplog_custom_fields'];
print_cp_header($photoplog_header);

$photoplog_fieldtypes = array(
	$vbphrase['single_line_text_box'], // 'input' <=> 0
	$vbphrase['multiple_line_text_box'], // 'textarea' <=> 1
	$vbphrase['single_selection_menu'], // 'select' <=> 2
	$vbphrase['single_selection_radio_buttons'], // 'radio' <=> 3
	$vbphrase['multiple_selection_menu'], // 'select_multiple' <=> 4
	$vbphrase['multiple_selection_checkbox'] // 'checkbox' <=> 5
);

$photoplog_input_names = array();
$photoplog_output_names = array();
$photoplog_blank_replace = array();
$photoplog_use_blank = array();

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'view';
}

if ($_REQUEST['do'] == 'view')
{
	$photoplog_input_names = array('catid' => TYPE_INT);
	$photoplog_output_names = array('catid' => 'photoplog_catid');
	$photoplog_blank_replace = array('catid' => -2);
}
else if ($_REQUEST['do'] == 'edit')
{
	$photoplog_input_names = array('catid' => TYPE_INT, 'fieldid' => TYPE_INT);
	$photoplog_output_names = array('catid' => 'photoplog_catid', 'fieldid' => 'photoplog_fieldid');
	$photoplog_blank_replace = array('catid' => -2, 'fieldid' => -1);
}
else if ($_REQUEST['do'] == 'delete')
{
	$photoplog_input_names = array('catid' => TYPE_INT, 'fieldid' => TYPE_INT);
	$photoplog_output_names = array('catid' => 'photoplog_catid', 'fieldid' => 'photoplog_fieldid');
	$photoplog_blank_replace = array('catid' => -2, 'fieldid' => -1);
}
else if ($_REQUEST['do'] == 'dodelete')
{
	$photoplog_input_names = array('catid' => TYPE_INT, 'fieldid' => TYPE_INT);
	$photoplog_output_names = array('catid' => 'photoplog_catid', 'fieldid' => 'photoplog_fieldid');
	$photoplog_blank_replace = array('catid' => -2, 'fieldid' => -1);
}
else if ($_REQUEST['do'] == 'add')
{
	$photoplog_input_names = array('catid' => TYPE_INT, 'fieldtype' => TYPE_INT);
	$photoplog_output_names = array(
		'catid' => 'photoplog_catid',
		'fieldtype' => 'photoplog_fieldtype'
	);
	$photoplog_blank_replace = array(
		'catid' => -2,
		'fieldtype' => -1
	);
}
else if ($_REQUEST['do'] == 'doadd')
{
	$photoplog_input_names = array('catid' => TYPE_INT, 'fieldtype' => TYPE_INT, 'fielddata' => TYPE_ARRAY);
	$photoplog_output_names = array(
		'catid' => 'photoplog_catid',
		'fieldtype' => 'photoplog_fieldtype',
		'fielddata' => 'photoplog_fielddata'
	);
	$photoplog_blank_replace = array(
		'catid' => -2,
		'fieldtype' => -1
	);
}
else if ($_REQUEST['do'] == 'doedit')
{
	$photoplog_input_names = array('catid' => TYPE_INT, 'fieldid' => TYPE_INT, 'fielddata' => TYPE_ARRAY);
	$photoplog_output_names = array(
		'catid' => 'photoplog_catid',
		'fieldid' => 'photoplog_fieldid',
		'fielddata' => 'photoplog_fielddata'
	);
	$photoplog_blank_replace = array(
		'catid' => -2,
		'fieldid' => -1
	);
}
else if ($_REQUEST['do'] == 'displayorder')
{
	$photoplog_input_names = array('catid' => TYPE_INT, 'displayorder' => TYPE_ARRAY_UINT);
	$photoplog_output_names = array('catid' => 'photoplog_catid',
		'displayorder' => 'photoplog_displayorder');
	$photoplog_blank_replace = array('catid' => -2);
}

if (!empty($photoplog_input_names))
{
	foreach ($photoplog_blank_replace AS $photoplog_key => $photoplog_value)
	{
		if (!isset($_REQUEST[$photoplog_key]) || (trim($_REQUEST[$photoplog_key]) == ''))
		{
			$photoplog_use_blank[$photoplog_key] = $photoplog_value;
		}
	}
	$vbulletin->input->clean_array_gpc('r', $photoplog_input_names);
	foreach ($photoplog_input_names AS $photoplog_key => $photoplog_value)
	{
		$photoplog_varname = (isset($photoplog_output_names[$photoplog_key])) ?
			$photoplog_output_names[$photoplog_key] : $photoplog_key;
		$$photoplog_varname = (isset($photoplog_use_blank[$photoplog_key])) ?
			$photoplog_use_blank[$photoplog_key] : $vbulletin->GPC[$photoplog_key];
	}
}

if ($_REQUEST['do'] == 'view')
{
	if ($photoplog_catid < -1)
	{
		photoplog_request_category('view');
	}
	else
	{
		photoplog_list_upload_fields($photoplog_catid);
	}
}

if ($_REQUEST['do'] == 'edit')
{
	if ($photoplog_catid < -1)
	{
		photoplog_request_category('view');
	}
	else if ($photoplog_fieldid < 0)
	{
		photoplog_list_upload_fields($photoplog_catid);
	}
	else
	{
		// do usual edit stuff
		// get contents of current field.
		// if none, return error.
		// else spit form.
		$photoplog_fielddata = photoplog_get_field_data($photoplog_catid, $photoplog_fieldid);
		if (!$photoplog_fielddata)
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_for_editing']);
		}
		if ($photoplog_fielddata['protected'] != 0)
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_field_is_protected']);
		}
		$photoplog_inputdata = array();

		$photoplog_inputdata['name'] = $photoplog_fielddata['name'];
		$photoplog_inputdata['hidden'] = strval($photoplog_fielddata['hidden']);
		$photoplog_inputdata['active'] = strval($photoplog_fielddata['active']);
		$photoplog_inputdata['inherited'] = ($photoplog_fielddata['inherited'] == -1) ? '1' : '0';

		$photoplog_info = ($photoplog_fielddata['info']) ? unserialize($photoplog_fielddata['info']) : array();
		foreach ($photoplog_info AS $photoplog_info_key => $photoplog_info_value)
		{
			$photoplog_inputdata[$photoplog_info_key] = $photoplog_info_value;
		}
		if (isset($photoplog_inputdata['options']) && is_array($photoplog_inputdata['options']))
		{
			$photoplog_inputdata['options'] = implode("\n",$photoplog_inputdata['options']);
		}
		photoplog_make_field_form($photoplog_inputdata,$photoplog_catid,$photoplog_info['type'],$photoplog_fieldid,'doedit');
	}
}

if ($_REQUEST['do'] == 'doedit')
{
	if ($photoplog_catid < -1)
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_category']);
	}
	if ($photoplog_fieldid < 0)
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_for_editing']);
	}
	$photoplog_current_fielddata = photoplog_get_field_data($photoplog_catid, $photoplog_fieldid);
	if (!$photoplog_current_fielddata)
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_for_editing']);
	}
	if ($photoplog_current_fielddata['protected'] != 0)
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_field_is_protected']);
	}
	$photoplog_current_type = -1;
	if (isset($photoplog_current_fielddata['info']))
	{
		$photoplog_current_info = unserialize($photoplog_current_fielddata['info']);
		if ((is_array($photoplog_current_info)) && isset($photoplog_current_info['type']))
		{
			$photoplog_current_type = intval($photoplog_current_info['type']);
		}
	}
	if ($photoplog_current_type < 0)
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_error']);
	}

	photoplog_doadd_doedit_worker($photoplog_fielddata,$photoplog_catid,$photoplog_current_type,$photoplog_current_fielddata);
	photoplog_list_upload_fields($photoplog_catid);
}

if ($_REQUEST['do'] == 'delete')
{
	if ($photoplog_catid < -1)
	{
		photoplog_request_category('view');
	}
	else if ($photoplog_fieldid < 0)
	{
		photoplog_list_upload_fields($photoplog_catid);
	}
	else
	{
		// do usual delete stuff
		$photoplog_current_fielddata = photoplog_get_field_data($photoplog_catid, $photoplog_fieldid);
		if (!$photoplog_current_fielddata)
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_for_deleting']);
		}
		if ($photoplog_current_fielddata['protected'] != 0)
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_field_is_protected']);
		}
		print_form_header('photoplog_field', 'dodelete');
		construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
		construct_hidden_code('catid', $photoplog_catid);
		construct_hidden_code('fieldid', $photoplog_fieldid);
		print_table_header($vbphrase['photoplog_delete_permanent'],2);
		print_submit_row($vbphrase['photoplog_delete_field'],'',2,$vbphrase['photoplog_goback']);
	}
}

if ($_REQUEST['do'] == 'dodelete')
{
	if ($photoplog_catid < -1)
	{
		photoplog_request_category('view');
	}
	else if ($photoplog_fieldid < 0)
	{
		photoplog_list_upload_fields($photoplog_catid);
	}
	else
	{
		// do usual delete stuff
		$photoplog_current_fielddata = photoplog_get_field_data($photoplog_catid, $photoplog_fieldid);
		if (!$photoplog_current_fielddata)
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_for_deleting']);
		}
		if ($photoplog_current_fielddata['protected'] != 0)
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_field_is_protected']);
		}

		$photoplog_child_list = array();
		$photoplog_parent_list = array();
		photoplog_child_list($photoplog_child_list,$photoplog_parent_list, $photoplog_catid);

		photoplog_delete_custom_field_recursive($photoplog_child_list,$photoplog_fieldid,
			$photoplog_catid,$photoplog_current_fielddata['groupid']);
		photoplog_list_upload_fields($photoplog_catid);
	}
}

if ($_REQUEST['do'] == 'add')
{
	if ($photoplog_catid < -1)
	{
		photoplog_request_category('add');
	}
	else
	{
		if (($photoplog_fieldtype < 0) || ($photoplog_fieldtype > 5))
		{
			photoplog_request_fieldtype($photoplog_catid);
		}
		else
		{
			$photoplog_fielddata = array();
			photoplog_make_field_form($photoplog_fielddata,$photoplog_catid,$photoplog_fieldtype,0,'doadd');
		}
	}
}

if ($_REQUEST['do'] == 'doadd')
{
	if ($photoplog_catid < -1)
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_category']);
	}
	if (($photoplog_fieldtype < 0) || ($photoplog_fieldtype > 5))
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_fieldtype']);
	}
	photoplog_doadd_doedit_worker($photoplog_fielddata,$photoplog_catid,$photoplog_fieldtype,'');
	photoplog_list_upload_fields($photoplog_catid);
}

if ($_REQUEST['do'] == 'displayorder')
{
	if ($photoplog_catid < 0)
	{
		photoplog_request_category('view');
	}
	else if (!is_array($photoplog_displayorder))
	{
		photoplog_list_upload_fields($photoplog_catid);
	}
	else
	{
		$photoplog_corrected_displayorder = photoplog_make_displayorder_unique($photoplog_displayorder);
		photoplog_update_fields_displayorder($photoplog_corrected_displayorder, $photoplog_catid);
		photoplog_list_upload_fields($photoplog_catid);
	}
}

print_cp_footer();

function photoplog_row_info($name,$description,$max=0)
{
	global $vbphrase;

	$max_text = ($max) ? $vbphrase['photoplog_maximum'] . " " . $max . ".  " : "";
	$desc_text = ($description && $vbphrase[$description]) ? $vbphrase[$description] : "";
	$desc_text = $max_text . $desc_text;
	$row_info = '';

	if ($name && $vbphrase[$name])
	{
		$row_info = $vbphrase[$name];
	}
	if ($desc_text)
	{
		$row_info .= "<dfn>" . $desc_text . "</dfn>";
	}

	return $row_info;
}

function photoplog_request_fieldtype($catid)
{
	global $vbulletin, $vbphrase, $photoplog_fieldtypes;

	print_form_header('photoplog_field', 'add');
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	construct_hidden_code('catid',$catid);
	print_table_header($vbphrase['photoplog_add_new_field'], 2);
	$catname = ($catid == -1) ? $vbphrase['photoplog_all_categories'] : photoplog_get_category_title($catid);
	print_label_row($vbphrase['photoplog_category'], htmlspecialchars_uni($catname));
	print_select_row(photoplog_row_info('photoplog_field_type','photoplog_not_editable'),
		'fieldtype', $photoplog_fieldtypes);
	print_submit_row($vbphrase['photoplog_continue'],'',2,$vbphrase['go_back']);
}

function photoplog_request_category($nextdo='view')
{
	global $vbulletin, $vbphrase, $photoplog_header;

	print_form_header('photoplog_field', $nextdo);
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	print_table_header($photoplog_header, 2);
	$photoplog_list_categories = array();
	photoplog_list_categories($photoplog_list_categories,-1,$vbphrase['photoplog_all_categories']);
	print_select_row(photoplog_row_info('photoplog_category','photoplog_not_editable'),
		'catid', $photoplog_list_categories, "-1", true, 0, false);
	print_submit_row($vbphrase['photoplog_continue'],'',2,$vbphrase['go_back']);
}

function photoplog_list_upload_fields($catid)
{
	global $vbulletin, $vbphrase;

	$catid = intval($catid);
	if (($catid != -1) && !photoplog_check_category($catid))
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_category']);
	}

	$photoplog_fields = $vbulletin->db->query_read("SELECT f1.inherited AS inherited1,
		f1.fieldid AS fieldid1, f1.groupid as groupid1, f1.displayorder AS displayorder1,
		f1.protected AS protected1, f1.parentid AS parentid1,
		f1.info AS info1 ,f2.info AS info2
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields AS f1
		LEFT JOIN " . PHOTOPLOG_PREFIX . "photoplog_customfields AS f2
		ON (f1.parentid = f2.catid AND f1.groupid = f2.groupid)
		WHERE f1.catid = ".intval($catid)."
		ORDER BY f1.displayorder ASC
	");

	print_form_header('photoplog_field', 'displayorder');
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	construct_hidden_code('catid', $catid);
	$catname = ($catid == -1) ? $vbphrase['photoplog_all_categories'] : photoplog_get_category_title($catid);
	print_table_header(htmlspecialchars_uni($catname), 3);

	if ($catid == -1)
	{
		print_description_row($vbphrase['photoplog_inherited_fields_cannot_be_modified_root'], 0, 3);
	}
	else
	{
		print_description_row($vbphrase['photoplog_inherited_fields_cannot_be_modified'], 0, 3);
	}

	print_cells_row(array($vbphrase['photoplog_field_title'],
				'<nobr>' . $vbphrase['photoplog_display_order'] . '</nobr>',
				$vbphrase['photoplog_controls']), 1, '', -1);

	while ($photoplog_field = $vbulletin->db->fetch_array($photoplog_fields))
	{
		$photoplog_field['info'] = ($photoplog_field['inherited1'] < 1) ? $photoplog_field['info1'] :
			$photoplog_field['info2'];

		// note: photoplog_field['info'] may be NULL
		$photoplog_field['info'] = empty($photoplog_field['info']) ? '' : unserialize($photoplog_field['info']);

		if (is_array($photoplog_field['info']))
		{
			$photoplog_fieldid1 = $photoplog_field['fieldid1'];
			$photoplog_title1 = $photoplog_field['info']['title'];
			$photoplog_parentid1 = intval($photoplog_field['parentid1']);
			if ($photoplog_parentid1 == -2)
			{
				$photoplog_title1 = $vbphrase[$photoplog_title1];
			}
			$photoplog_protected1 = $photoplog_field['protected1'];
			$photoplog_edit_html = '' . $vbphrase['edit'] . '';
			$photoplog_delete_html = '' . $vbphrase['delete'] . '';
			$photoplog_title_link = htmlspecialchars_uni($photoplog_title1);

			if ($photoplog_protected1 == 0)
			{
				$photoplog_pre_ahref1 = "[<a href=\"photoplog_field.php?" . $vbulletin->session->vars['sessionurl'];
				$photoplog_pre_ahref2 = "<a href=\"photoplog_field.php?" . $vbulletin->session->vars['sessionurl'];
				$photoplog_post_ahref = "&amp;catid=".intval($catid)."&amp;fieldid=".
					intval($photoplog_fieldid1) . "\">";
				$photoplog_edit_html = $photoplog_pre_ahref1 . "do=edit" . $photoplog_post_ahref .
					$photoplog_edit_html . "</a>]";
				$photoplog_delete_html = $photoplog_pre_ahref1 . "do=delete" . $photoplog_post_ahref .
					$photoplog_delete_html . "</a>]";
				$photoplog_title_link = $photoplog_pre_ahref2 . "do=edit" . $photoplog_post_ahref .
					$photoplog_title_link . "</a>";
			}
			else
			{
				$photoplog_edit_html = '[<del>' . $vbphrase['edit'] . '</del>]';
				$photoplog_delete_html = '[<del>' . $vbphrase['delete'] . '</del>]';
			}

			$bgclass = fetch_row_bgclass();
			if ($catid == -1)
			{
				echo "
					<tr>
					<td class=\"$bgclass\" width=\"100%\">" . $photoplog_title_link . "</td>
					<td class=\"$bgclass\"><span class=\"smallfont\">" . $vbphrase['photoplog_na'] . "</span></td>
					<td class=\"$bgclass\"><nobr>" . $photoplog_edit_html . " " . $photoplog_delete_html .
					"</nobr></td>
					</tr>
				";
			}
			else
			{
				echo "
					<tr>
					<td class=\"$bgclass\" width=\"100%\">" . $photoplog_title_link . "</td>
					<td class=\"$bgclass\"><input type=\"text\" class=\"bginput\" name=\"displayorder[".
						$photoplog_fieldid1."]\" value=\"".$photoplog_field['displayorder1']."\" size=\"5\" /></td>
					<td class=\"$bgclass\"><nobr>" . $photoplog_edit_html . " " . $photoplog_delete_html .
					"</nobr></td>
					</tr>
				";
			}
		}
	}

	$vbulletin->db->free_result($photoplog_fields);

	$photoplog_submit_button = ($catid != -1) ?
		"<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" .
			$vbphrase['save_display_order'] . "\" accesskey=\"s\" />" :
		"";

	print_table_footer(3, $photoplog_submit_button .
		construct_button_code($vbphrase['photoplog_add_new_field'], "photoplog_field.php?" .
		$vbulletin->session->vars['sessionurl'] . "do=add&amp;catid=".intval($catid)) .
		construct_button_code($vbphrase['go_back'], "photoplog_field.php?" .
		$vbulletin->session->vars['sessionurl'] . "do=view"));
}

function photoplog_make_displayorder_unique($displayorder)
{
	$corrected_displayorder = array();

	foreach ($displayorder AS $displayorder_key => $displayorder_value)
	{
		$displayorder_corrected_value = $displayorder_value;
		while (in_array($displayorder_corrected_value, $corrected_displayorder))
		{
			$displayorder_corrected_value++;
		}
		$corrected_displayorder[$displayorder_key] = $displayorder_corrected_value;
	}

	return $corrected_displayorder;
}

function photoplog_doadd_doedit_worker($new_fielddata,$catid,$fieldtype,$current_fielddata)
{
	global $vbulletin, $vbphrase;

	$doadd = empty($current_fielddata);
	$new_fielddata_name = ($doadd) ? trim($new_fielddata['name']) : $current_fielddata['name'];
	$new_fielddata_groupid = ($doadd) ? intval(trim($new_fielddata['groupid'])) : $current_fielddata['groupid'];
	$all_groups = photoplog_get_all_groups();

	if ($doadd)
	{
		if ($new_fielddata_groupid)
		{
			if($new_fielddata_name)
			{
				if ($all_groups[$new_fielddata_groupid] != $new_fielddata_name)
				{
					print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_name_inconsistent']);
				}
			}
			else
			{
				$new_fielddata_name = trim($all_groups[$new_fielddata_groupid]);
			}
		}
		else
		{
			if (in_array($new_fielddata_name,$all_groups))
			{
				foreach ($all_groups as $gid => $gname)
				{
					if ($new_fielddata_name == $gname)
					{
						$new_fielddata_groupid = intval($gid);
						break;
					}
				}
			}
		}
	}

	$new_fielddata_title = trim($new_fielddata['title']);
	$new_fielddata_description = trim($new_fielddata['description']);
	$new_fielddata_maxlength = intval(trim($new_fielddata['maxlength']));
	$new_fielddata_default = trim($new_fielddata['default']);
	$new_fielddata_size = intval(trim($new_fielddata['size']));
	$new_fielddata_height = intval(trim($new_fielddata['height']));

	$new_fielddata_options = trim($new_fielddata['options']);
	$new_fielddata_options = explode("\n",$new_fielddata_options);

	if (is_array($new_fielddata_options))
	{
		foreach ($new_fielddata_options AS $option_key => $option_value)
		{
			$new_fielddata_options[$option_key] = trim($option_value);
		}
	}
	else
	{
		$new_fielddata_options = array();
	}

	$new_fielddata_limit = intval(trim($new_fielddata['limit']));
	$new_fielddata_perline = intval(trim($new_fielddata['perline']));
	$new_fielddata_active = intval(trim($new_fielddata['active']));
	$new_fielddata_hidden = intval(trim($new_fielddata['hidden']));
	$new_fielddata_required = intval(trim($new_fielddata['required']));
	$new_fielddata_inherited = intval(trim($new_fielddata['inherited']));

	$info = array();
	$info['type'] = $fieldtype;

	if ((vbstrlen($new_fielddata_name) < 3) || (vbstrlen($new_fielddata_name) > 50))
	{
		print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_name'],3,50));
	}
	if (vbstrlen($new_fielddata_title) > 50)
	{
		print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_title'],50));
	}
	else
	{
		$info['title'] = $new_fielddata_title;
	}
	if (vbstrlen($new_fielddata_description) > 250)
	{
		print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_description'],250));
	}
	else
	{
		$info['description'] = $new_fielddata_description;
	}
	if ($fieldtype <= 1)
	{
		if (($new_fielddata_maxlength < 1) || ($new_fielddata_maxlength > 1024))
		{
			print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_maxlength'],1,1024));
		}
		else
		{
			$info['maxlength'] = $new_fielddata_maxlength;
		}
		if (vbstrlen($new_fielddata_default) > $new_fielddata_maxlength)
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_default_exceeds_maxlength']);
		}
		else
		{
			$info['default'] = $new_fielddata_default;
		}
		if (($new_fielddata_size < 1) || ($new_fielddata_size > 100))
		{
			print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_size'], 1, 100));
		}
		else
		{
			$info['size'] = $new_fielddata_size;
		}
	}
	if ($fieldtype >= 2)
	{
		if ((count($new_fielddata_options) < 1) || (count($new_fielddata_options) > 256))
		{
			print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_options'],1,256));
		}
		else
		{
			$info['options'] = $new_fielddata_options;
		}
		if (!(($new_fielddata_default == '0') || ($new_fielddata_default == '1')))
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_default']);
		}
		else
		{
			$info['default'] = $new_fielddata_default;
		}
	}
	if ($fieldtype == 1)
	{
		if (($new_fielddata_height < 2) || ($new_fielddata_height > 1000))
		{
			print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_height'], 2, 1000));
		}
		else
		{
			$info['height'] = $new_fielddata_height;
		}
	}
	if (($fieldtype == 4) || ($fieldtype == 2))
	{
		if (($new_fielddata_height < 1) || ($new_fielddata_height > 1000))
		{
			print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_height'], 0, 1000));
		}
		else
		{
			$info['height'] = $new_fielddata_height;
		}
	}
	if ($fieldtype >= 4)
	{
		if (($new_fielddata_limit < 0) || ($new_fielddata_limit > 1000))
		{
			print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_limit'], 0, 1000));
		}
		else
		{
			$info['limit'] = $new_fielddata_limit;
		}
	}
	if (($fieldtype == 5) || ($fieldtype == 3))
	{
		if (($new_fielddata_perline < 0) || ($new_fielddata_perline > 1000))
		{
			print_stop_message('generic_error_x', construct_phrase($vbphrase['photoplog_bad_field_perline'], 1000));
		}
		else
		{
			$info['perline'] = $new_fielddata_perline;
		}
	}
	if (!(($new_fielddata_active == 0) || ($new_fielddata_active == 1)))
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_active']);
	}
	if (!(($new_fielddata_hidden == 0) || ($new_fielddata_hidden == 1)))
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_hidden']);
	}
	if (!(($new_fielddata_required == 0) || ($new_fielddata_required == 1)))
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_required']);
	}
	else
	{
		$info['required'] = $new_fielddata_required;
	}
	if (!(($new_fielddata_inherited == 0) || ($new_fielddata_inherited == 1)))
	{
		print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_inherited']);
	}

	$child_list = array();
	$parent_list = array();
	photoplog_child_list($child_list,$parent_list,$catid);
	$displayorder = 0;

	if ($doadd)
	{
		$name_in_use = false;
		$fields = $vbulletin->db->query_read("SELECT displayorder, groupid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid = ".intval($catid)."
			ORDER BY displayorder ASC
		");
		while ($field = $vbulletin->db->fetch_array($fields))
		{
			$displayorder = $field['displayorder'];
			if ($field['groupid'] == $new_fielddata_groupid)
			{
				$name_in_use = true;
				break;
			}
		}
		$vbulletin->db->free_result($fields);

		if ($name_in_use)
		{
			print_stop_message('generic_error_x', $vbphrase['photoplog_bad_field_name_in_use']);
		}
		$displayorder += 10;

		if (($new_fielddata_inherited == 1) && !empty($child_list))
		{
			// check subcategories to see if there is a problem with the name!
			$check_subcats = $vbulletin->db->query_first("SELECT COUNT(*) AS cnt
				FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
				WHERE catid IN (".implode(",", $child_list).")
				AND groupid = ".intval($new_fielddata_groupid)."
			");
			if (!$check_subcats || !isset($check_subcats['cnt']) || ($check_subcats['cnt'] > 0))
			{
				print_stop_message('generic_error_x',$vbphrase['photoplog_bad_field_name_in_use_inherited']);
			}
		}
	}
	else
	{
		$displayorder = intval($current_fielddata['displayorder']);
		if (($new_fielddata_inherited == 1) && (intval($current_fielddata['inherited']) > -1) && !empty($child_list))
		{
			// check subcategories to see if there is a problem with the name!
			$check_subcats = $vbulletin->db->query_first("SELECT COUNT(*) AS cnt
				FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
				WHERE catid IN (".implode(",", $child_list).")
				AND groupid = ".intval($new_fielddata_groupid)."
				AND parentid != ".intval($catid)."
			");
			if (!$check_subcats || !isset($check_subcats['cnt']) || ($check_subcats > 0))
			{
				print_stop_message('generic_error_x',$vbphrase['photoplog_bad_field_name_in_use_inherited']);
			}
		}
	}

	// be careful to not move the below two lines up!!!
	$new_fielddata_inherited = -1 * $new_fielddata_inherited;
	$new_fielddata_protected = 0;

	$info = serialize($info);

	if ($doadd)
	{
		if (!$new_fielddata_groupid)
		{
			$new_fielddata_groupid = photoplog_insert_customfield_group($new_fielddata_name);
		}
		if ($new_fielddata_groupid)
		{
			photoplog_insert_custom_field_recursive($child_list,$catid, $new_fielddata_groupid,
				$displayorder, $new_fielddata_hidden, $new_fielddata_active,
				$new_fielddata_protected, $new_fielddata_inherited, $catid, $info);
		}
	}
	else
	{
		photoplog_update_custom_field_recursive($child_list,$current_fielddata['fieldid'],$catid,
			$current_fielddata['groupid'], $current_fielddata['displayorder'], $new_fielddata_hidden,
			$new_fielddata_active, $new_fielddata_inherited, $catid, $info);
	}
}

function photoplog_make_field_form($fielddata_inputs,$catid,$fieldtype,$fieldid,$donext)
{
	global $vbulletin, $vbphrase, $photoplog_fieldtypes;

	$doadd = ($donext == 'doadd');

	$fielddata = array(
		'name'=>'',
		'groupid'=>'0',
		'title'=>'',
		'description'=>'',
		'maxlength'=>'100',
		'default'=>'1',
		'size'=>'40',
		'height'=>'8',
		'options'=>'',
		'limit'=>'0',
		'perline'=>'2',
		'active'=>'1',
		'hidden'=>'0',
		'required'=>'0',
		'inherited'=>'1'
	);

	if ($fieldtype <= 1)
	{
		$fielddata['default'] = '';
	}
	if ($fieldtype == 1)
	{
		$fielddata['maxlength'] = '1000';
	}
	foreach ($fielddata_inputs AS $fielddata_name => $fielddata_value)
	{
		$fielddata[$fielddata_name] = $fielddata_value;
	}
	$catname = (intval($catid) == -1) ? $vbphrase['photoplog_all_categories'] : photoplog_get_category_title($catid);

	print_form_header('photoplog_field', $donext);
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	construct_hidden_code('catid',$catid);

	if ($doadd)
	{
		construct_hidden_code('fieldtype',$fieldtype);
		print_table_header($vbphrase['photoplog_add_new_field'], 2);
	}
	else
	{
		construct_hidden_code('fieldid',$fieldid);
		print_table_header($vbphrase['photoplog_edit_field'],2);
	}

	// NOTE THAT htmlspecialchars_uni is automatically done by the below functions unless turned off! EXCEPT label_row!
	print_label_row($vbphrase['photoplog_category'],htmlspecialchars_uni($catname));
	print_label_row($vbphrase['photoplog_field_type'],htmlspecialchars_uni($photoplog_fieldtypes[$fieldtype]));

	if ($doadd)
	{
		$photoplog_available_groups = photoplog_make_available_groups($catid);
		if (empty($photoplog_available_groups))
		{
			print_input_row(photoplog_row_info('photoplog_field_name','photoplog_field_name_description',50),
				'fielddata[name]',$fielddata['name']);
		}
		else
		{
			$photoplog_available_groups['0'] = $vbphrase['photoplog_select_name_or_enter_below'];
			ksort($photoplog_available_groups);
			print_radio_row(photoplog_row_info('photoplog_field_name','photoplog_field_name_select_description',50), 'fielddata[groupid]', $photoplog_available_groups,'0','smallfont');
			$bgclass = fetch_row_bgclass();
			print_input_row(photoplog_row_info('','photoplog_field_name_description'),'fielddata[name]',$fielddata['name']);
		}
	}
	else
	{
		print_label_row($vbphrase['photoplog_field_name'],htmlspecialchars_uni($fielddata['name']));
	}

	print_input_row(photoplog_row_info('photoplog_field_title','photoplog_field_title_description',50),
		'fielddata[title]',$fielddata['title']);
	print_input_row(photoplog_row_info('photoplog_field_description','photoplog_field_description_description',250),
		'fielddata[description]',$fielddata['description']);

	if ($fieldtype <= 1)
	{
		print_input_row(photoplog_row_info('photoplog_field_maxlength','photoplog_field_maxlength_description'),
			'fielddata[maxlength]',$fielddata['maxlength']);
		print_input_row(photoplog_row_info('photoplog_field_default','photoplog_field_default_description_text'),
			'fielddata[default]',$fielddata['default']);
		print_input_row(photoplog_row_info('photoplog_field_size','photoplog_field_size_description'),
			'fielddata[size]',$fielddata['size']);
	}
	if ($fieldtype == 1)
	{
		print_input_row(photoplog_row_info('photoplog_field_height','photoplog_field_height_description_text'),
			'fielddata[height]',$fielddata['height']);
	}
	if (($fieldtype >= 2) && ($fieldtype <= 5))
	{
		print_textarea_row(photoplog_row_info('photoplog_field_options','photoplog_field_options_description'),
			'fielddata[options]',$fielddata['options'],8,40);
		print_yes_no_row(photoplog_row_info('photoplog_field_default','photoplog_field_default_description_select'),
			'fielddata[default]',$fielddata['default']);
	}
	if (($fieldtype == 4) || ($fieldtype == 2))
	{
		print_input_row(photoplog_row_info('photoplog_field_height','photoplog_field_height_description_select'),
			'fielddata[height]',$fielddata['height']);
	}
	if ($fieldtype >= 4)
	{
		print_input_row(photoplog_row_info('photoplog_field_limit','photoplog_field_limit_description'),
			'fielddata[limit]',$fielddata['limit']);
	}
	if ($fieldtype == 3)
	{
		print_input_row(photoplog_row_info('photoplog_field_perline_radio','photoplog_field_perline_radio_description'),
			'fielddata[perline]',$fielddata['perline']);
	}
	if ($fieldtype == 5)
	{
		print_input_row(photoplog_row_info('photoplog_field_perline_checkbox','photoplog_field_perline_checkbox_description'),
			'fielddata[perline]',$fielddata['perline']);
	}
	print_yes_no_row(photoplog_row_info('photoplog_field_active','photoplog_field_active_description'),
		'fielddata[active]',$fielddata['active']);
	print_yes_no_row(photoplog_row_info('photoplog_field_hidden','photoplog_field_hidden_description'),
		'fielddata[hidden]',$fielddata['hidden']);
	print_yes_no_row(photoplog_row_info('photoplog_field_required','photoplog_field_required_description'),
		'fielddata[required]',$fielddata['required']);
	print_yes_no_row(photoplog_row_info('photoplog_field_inherited','photoplog_field_inherited_description'),
		'fielddata[inherited]',$fielddata['inherited']);
	print_submit_row($vbphrase['photoplog_save'],'_default_',2,$vbphrase['go_back']);
}

?>