<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ######################## SET PHP ENVIRONMENT ###########################
// report all errors except notice level errors
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
$phrasegroups = array('forum', 'cpuser', 'photoplog');
$specialtemplates = array();

// ######################## REQUIRE BACK-END ##############################
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

$photoplog_images_directory = $vbulletin->options['photoplog_full_path'].'/'.$vbulletin->options['photoplog_upload_dir'];

$photoplog_header = $vbphrase['photoplog_mass'] . " " . $vbphrase['photoplog_md'];
print_cp_header($photoplog_header);

$photoplog_ratecomment_condition_holds_if_none = 0;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'view';
}

if ($_REQUEST['do'] == 'counts')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_UINT,
		'start' => TYPE_UINT,
		'phase' => TYPE_UINT
	));

	$photoplog_start = intval($vbulletin->GPC['start']);
	$photoplog_perpage = intval($vbulletin->GPC['perpage']);
	$photoplog_phase = intval($vbulletin->GPC['phase']);
	if (!$photoplog_perpage)
	{
		$photoplog_perpage = 50;
	}
	photoplog_maintain_counts($photoplog_start,$photoplog_perpage,$photoplog_phase,$photoplog_header,'photoplog_massmove.php');
	// print_cp_redirect("photoplog_massmove.php?".$vbulletin->session->vars['sessionurl']."do=view", 1);
}

if ($_REQUEST['do'] == 'view')
{
	print_form_header('photoplog_massmove', 'preview');
	construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
	print_table_header($photoplog_header, 3);
	print_radio_row($vbphrase['photoplog_md'],'photoplog_md',
		array("1" => $vbphrase['photoplog_move'],
		"2" => $vbphrase['photoplog_delete']),"1");
	print_table_header($vbphrase['photoplog_source'] . " " . $vbphrase['photoplog_blank_ignored'], 3);
	$photoplog_source_list_categories = array();
	$photoplog_not_available_nonempty = photoplog_count_category_files(0);
	photoplog_list_categories($photoplog_source_list_categories,-1,$vbphrase['photoplog_all_categories'],
		$photoplog_not_available_nonempty);
	print_select_row($vbphrase['photoplog_category'], 'photoplog_source_category',
		$photoplog_source_list_categories, "-1", true, 0, false);
	print_yes_no_row($vbphrase['photoplog_include_subcategories'],'photoplog_include_subcategories',"0");
	print_input_row($vbphrase['photoplog_posted_by_ids'],'photoplog_posted_by');
	print_input_row($vbphrase['photoplog_title_contains'],'photoplog_title');
	print_input_row($vbphrase['photoplog_description_contains'],'photoplog_description');
	print_time_row($vbphrase['photoplog_posted_after'],'photoplog_posted_after');
	print_time_row($vbphrase['photoplog_posted_before'],'photoplog_posted_before');
	print_input_row($vbphrase['photoplog_filesize_at_least'],'photoplog_filesize_at_least');
	print_input_row($vbphrase['photoplog_filesize_at_most'],'photoplog_filesize_at_most');
	print_input_row($vbphrase['photoplog_width_at_least'],'photoplog_width_at_least');
	print_input_row($vbphrase['photoplog_width_at_most'],'photoplog_width_at_most');
	print_input_row($vbphrase['photoplog_height_at_least'],'photoplog_height_at_least');
	print_input_row($vbphrase['photoplog_height_at_most'],'photoplog_height_at_most');
	print_input_row($vbphrase['photoplog_number_views_at_least'],'photoplog_number_views_at_least');
	print_input_row($vbphrase['photoplog_number_views_at_most'],'photoplog_number_views_at_most');
	print_time_row($vbphrase['photoplog_last_comment_after'],'photoplog_last_comment_after');
	print_time_row($vbphrase['photoplog_last_comment_before'],'photoplog_last_comment_before');
	print_input_row($vbphrase['photoplog_number_comments_at_least'],
		'photoplog_number_comments_at_least');
	print_input_row($vbphrase['photoplog_number_comments_at_most'],'photoplog_number_comments_at_most');
	print_input_row($vbphrase['photoplog_number_ratings_at_least'],'photoplog_number_ratings_at_least');
	print_input_row($vbphrase['photoplog_number_ratings_at_most'],'photoplog_number_ratings_at_most');
	$photoplog_rating_array = array("-1" => $vbphrase['photoplog_ignore'],
		"1" => $vbphrase['photoplog_terrible'],
		"2" => $vbphrase['photoplog_bad'], "3" => $vbphrase['photoplog_average'],
		"4" => $vbphrase['photoplog_good'], "5" => $vbphrase['photoplog_excellent']);
	print_select_row($vbphrase['photoplog_average_rating_at_least'],'photoplog_average_rating_ge',
		$photoplog_rating_array,"-1");
	print_select_row($vbphrase['photoplog_average_rating_at_most'],'photoplog_average_rating_le',
		$photoplog_rating_array,"-1");

	print_table_header($vbphrase['photoplog_move_destination']);
	$photoplog_destination_list_categories = array();
	photoplog_list_categories($photoplog_destination_list_categories);

	print_select_row($vbphrase['photoplog_category'], 'photoplog_destination_category',
		$photoplog_destination_list_categories, "-1", true, 0, false);
	print_yes_no_row($vbphrase['photoplog_make_subcategories'],'photoplog_make_subcategories',"0");
	print_input_row($vbphrase['photoplog_destination_posted_by_id'],'photoplog_destination_posted_by');
	print_time_row($vbphrase['photoplog_destination_posted_on'],'photoplog_destination_posted_on');
	print_submit_row($vbphrase['photoplog_preview']);
}

if ($_REQUEST['do'] == 'domassmove' || $_REQUEST['do'] == 'preview')
{
	$photoplog_input_names = array(
		'photoplog_md' => TYPE_INT,
		'photoplog_source_category' => TYPE_INT,
		'photoplog_include_subcategories' => TYPE_UINT,
		'photoplog_posted_by' => TYPE_STR,
		'photoplog_title' => TYPE_STR,
		'photoplog_description' => TYPE_STR,
		'photoplog_posted_after' => TYPE_ARRAY_UINT,
		'photoplog_posted_before' => TYPE_ARRAY_UINT,
		'photoplog_filesize_at_least' => TYPE_UINT,
		'photoplog_filesize_at_most' => TYPE_UINT,
		'photoplog_width_at_least' => TYPE_UINT,
		'photoplog_width_at_most' => TYPE_UINT,
		'photoplog_height_at_least' => TYPE_UINT,
		'photoplog_height_at_most' => TYPE_UINT,
		'photoplog_number_views_at_least' => TYPE_UINT,
		'photoplog_number_views_at_most' => TYPE_UINT,
		'photoplog_last_comment_after' => TYPE_ARRAY_UINT,
		'photoplog_last_comment_before' => TYPE_ARRAY_UINT,
		'photoplog_number_comments_at_least' => TYPE_UINT,
		'photoplog_number_comments_at_most' => TYPE_UINT,
		'photoplog_number_ratings_at_least' => TYPE_UINT,
		'photoplog_number_ratings_at_most' => TYPE_UINT,
		'photoplog_average_rating_ge' => TYPE_INT,
		'photoplog_average_rating_le' => TYPE_INT,
		'photoplog_destination_category' => TYPE_INT,
		'photoplog_make_subcategories' => TYPE_UINT,
		'photoplog_destination_posted_by' => TYPE_UINT,
		'photoplog_destination_posted_on' => TYPE_ARRAY_UINT
	);

	$photoplog_input_okay_info = array(
		'photoplog_md' => array('photoplog_bad_input',1,2),
		'photoplog_source_category' => array('photoplog_bad_source_category',1,2),
		'photoplog_include_subcategories' => array('photoplog_bad_input',1,2),
		'photoplog_posted_by' => array('photoplog_bad_posted_by_id',1,2),
		'photoplog_title' => array('photoplog_bad_input',1,2),
		'photoplog_description' => array('photoplog_bad_input',1,2),
		'photoplog_posted_after' => array('photoplog_bad_date',1,2),
		'photoplog_posted_before' => array('photoplog_bad_date',1,2),
		'photoplog_filesize_at_least' => array('photoplog_bad_filesize',1,2),
		'photoplog_filesize_at_most' => array('photoplog_bad_filesize',1,2),
		'photoplog_width_at_least' => array('photoplog_bad_width',1,2),
		'photoplog_width_at_most' => array('photoplog_bad_width',1,2),
		'photoplog_height_at_least' => array('photoplog_bad_height',1,2),
		'photoplog_height_at_most' => array('photoplog_bad_height',1,2),
		'photoplog_number_views_at_least' => array('photoplog_bad_number_views',1,2),
		'photoplog_number_views_at_most' => array('photoplog_bad_number_views',1,2),
		'photoplog_last_comment_after' => array('photoplog_bad_date',1,2),
		'photoplog_last_comment_before' => array('photoplog_bad_date',1,2),
		'photoplog_number_comments_at_least' => array('photoplog_bad_number_comments',1,2),
		'photoplog_number_comments_at_most' => array('photoplog_bad_number_comments',1,2),
		'photoplog_number_ratings_at_least' => array('photoplog_bad_number_ratings',1,2),
		'photoplog_number_ratings_at_most' => array('photoplog_bad_number_ratings',1,2),
		'photoplog_average_rating_ge' => array('photoplog_bad_average_rating',1,2),
		'photoplog_average_rating_le' => array('photoplog_bad_average_rating',1,2),
		'photoplog_destination_category' => array('photoplog_bad_destination_category',1),
		'photoplog_make_subcategories' => array('photoplog_bad_input',1),
		'photoplog_destination_posted_by' => array('photoplog_bad_posted_by_id',1),
		'photoplog_destination_posted_on' => array('photoplog_bad_date',1)
	);

	$vbulletin->input->clean_array_gpc('p', $photoplog_input_names);

	$photoplog_blank_to_minus_one = array(
		'photoplog_filesize_at_most','photoplog_filesize_at_least',
		'photoplog_width_at_most','photoplog_width_at_least',
		'photoplog_height_at_most','photoplog_height_at_least',
		'photoplog_number_views_at_most','photoplog_number_views_at_least',
		'photoplog_number_comments_at_most','photoplog_number_comments_at_least',
		'photoplog_number_ratings_at_most','photoplog_number_ratings_at_least',
		'photoplog_destination_posted_by'
	);

	$photoplog_input_okay = array();

	foreach ($photoplog_input_names AS $photoplog_key => $photoplog_value)
	{
		$$photoplog_key = $vbulletin->GPC[$photoplog_key];
		$photoplog_input_okay[$photoplog_key] = true;

		if (
			(($photoplog_value == TYPE_INT) || ($photoplog_value == TYPE_UINT))
				&&
			isset($_REQUEST[$photoplog_key])
				&&
			(trim($_REQUEST[$photoplog_key]) != '')
				&&
			(strval($$photoplog_key) != trim($_REQUEST[$photoplog_key]))
		)
		{
			$photoplog_input_okay[$photoplog_key] = false;
		}

		if (($photoplog_value == TYPE_ARRAY_INT) || ($photoplog_value == TYPE_ARRAY_UINT))
		{
			foreach ($$photoplog_key AS $photoplog_key_three => $photoplog_value_three)
			{
				if (
					isset($_REQUEST[$photoplog_key][$photoplog_key_three])
						&&
					(trim($_REQUEST[$photoplog_key][$photoplog_key_three]) != '')
						&&
					(strval($photoplog_value_three) != trim($_REQUEST[$photoplog_key][$photoplog_key_three]))
				)
				{
					$photoplog_input_okay[$photoplog_key] = false;
					break;
				}
			}
		}

		if (
			in_array($photoplog_key,$photoplog_blank_to_minus_one)
				&&
			isset($_REQUEST[$photoplog_key])
				&&
			(trim($_REQUEST[$photoplog_key]) == '')
		)
		{
			$$photoplog_key = -1;
		}
	}

	if (!in_array($photoplog_md,array(1,2)))
	{
		print_stop_message('generic_error_x',$vbphrase['photoplog_bad_input']);
	}

	foreach ($photoplog_input_okay AS $photoplog_key => $photoplog_value)
	{
		if (!$photoplog_value && in_array($photoplog_md,$photoplog_input_okay_info[$photoplog_key]))
		{
			print_stop_message('generic_error_x', $vbphrase[$photoplog_input_okay_info[$photoplog_key][0]]);
		}
	}

	$photoplog_move_action = ($photoplog_md == 1);

	if (!$photoplog_move_action)
	{
		// THESE SETTINGS ARE CRITICAL!!!
		$photoplog_destination_category = -1;
		$photoplog_destination_posted_by = -1;
	}

	if (intval($photoplog_source_category) < 0 )
	{
		$photoplog_source_category = -1;
		$photoplog_include_subcategories = 1;
	}

	if (intval($photoplog_destination_category) < 0)
	{
		$photoplog_destination_category = -1;
	}
	if (intval($photoplog_average_rating_ge) <= 0)
	{
		$photoplog_average_rating_ge = -1;
	}
	if (intval($photoplog_average_rating_le) <= 0)
	{
		$photoplog_average_rating_le = -1;
	}

	$photoplog_ds_catopts = photoplog_fetch_ds_cat();

	$photoplog_phrase_action = '';
	$photoplog_phrase_number = '';

	if ($photoplog_move_action)
	{
		$photoplog_phrase_action = $vbphrase['photoplog_move_files'];
		$photoplog_phrase_number = $vbphrase['photoplog_number_to_be_moved'];
	}
	else
	{
		$photoplog_phrase_action = $vbphrase['photoplog_delete_files'];
		$photoplog_phrase_number = $vbphrase['photoplog_number_to_be_deleted'];
	}

	$photoplog_abort = false;
	$photoplog_abort_message = '';

	$photoplog_check_category_source = ($photoplog_source_category > 0) ?
		photoplog_check_category($photoplog_source_category) : true;
	if (!$photoplog_check_category_source)
	{
		photoplog_set_abort($vbphrase['photoplog_bad_source_category']);
	}

	// check destination category:  no selection == -1 ==> no change
	$photoplog_check_destination = ($photoplog_destination_category >= 0) ?
		photoplog_check_category($photoplog_destination_category) : true;
	if (!$photoplog_check_destination && $photoplog_move_action)
	{
		photoplog_set_abort($vbphrase['photoplog_bad_destination_category']);
	}

	// select where conditions on fileuploads LEFT JOIN ratecomments
	$photoplog_where_conditions = array();
	$photoplog_multi_wheres = array();

	// add categories to the where condition
	$photoplog_source_list_categories = array();
	$photoplog_destination_list_categories = array();
	$photoplog_parent_list_categories = array();

	$photoplog_move_categories = true;
	if ($photoplog_destination_category < 0)
	{
		$photoplog_destination_category = $photoplog_source_category;
		$photoplog_move_categories = false;
		$photoplog_make_subcategories = 0;  // improves efficiency
	}

	$photoplog_source_list_categories[] = $photoplog_source_category;
	$photoplog_destination_list_categories[] = $photoplog_destination_category;
	$photoplog_parent_list_categories[] = $photoplog_source_category;

	if ($photoplog_include_subcategories)
	{
		photoplog_child_list($photoplog_source_list_categories,$photoplog_parent_list_categories,
			$photoplog_source_category);
		foreach ($photoplog_source_list_categories AS $photoplog_list_key => $photoplog_list_value)
		{
			$photoplog_destination_list_categories[$photoplog_list_key] = $photoplog_destination_category;
		}
	}

	if ($photoplog_make_subcategories || !$photoplog_include_subcategories)
	{
		// case 2, or case 1
		foreach ($photoplog_source_list_categories AS
			$photoplog_list_category_key => $photoplog_list_category_value)
		{
			$photoplog_multi_wheres[$photoplog_list_category_key] = array();
			$photoplog_multi_wheres[$photoplog_list_category_key][] =
				PHOTOPLOG_PREFIX . "photoplog_fileuploads.catid = " . intval($photoplog_list_category_value);
		}
	}
	else
	{
		// case 3
		$photoplog_multi_wheres[0] = array();
		$photoplog_multi_wheres[0][] = photoplog_sql_in_list_implode(PHOTOPLOG_PREFIX . 'photoplog_fileuploads.catid',
			$photoplog_source_list_categories);
	}

	// add userids to the where condition
	$photoplog_posted_by_ids = explode(',', $photoplog_posted_by);
	if ($photoplog_posted_by != '')
	{
		$photoplog_posted_by_ints = array();
		foreach ($photoplog_posted_by_ids AS $photoplog_posted_by_id)
		{
			$photoplog_posted_id_trim = trim($photoplog_posted_by_id);
			$photoplog_posted_id_intval = strval(intval($photoplog_posted_id_trim));
			if (
				($photoplog_posted_id_trim != '')
					&&
				($photoplog_posted_id_trim === $photoplog_posted_id_intval)
			)
			{
				$photoplog_posted_by_ints[] = intval($photoplog_posted_id_trim);
			}
			else
			{
				photoplog_set_abort($vbphrase['photoplog_bad_posted_by_id']);
			}
		}
		if (count($photoplog_posted_by_ints) > 0)
		{
			$photoplog_where_conditions[] = photoplog_sql_in_list_implode(PHOTOPLOG_PREFIX . 'photoplog_fileuploads.userid',
				$photoplog_posted_by_ints);
		}
	}

	// photoplog_title
	if ($photoplog_title != '')
	{
		$photoplog_where_conditions[] = PHOTOPLOG_PREFIX . "photoplog_fileuploads.title LIKE '".
			$vbulletin->db->escape_string($photoplog_title)."%'";
	}

	// photoplog_description
	if ($photoplog_description != '')
	{
		$photoplog_where_conditions[] = PHOTOPLOG_PREFIX . "photoplog_fileuploads.description LIKE '".
			$vbulletin->db->escape_string($photoplog_description)."%'";
	}

	$photoplog_posted_after_dateline = (photoplog_checkdate($photoplog_posted_after)) ?
		photoplog_get_dateline($photoplog_posted_after) : -1;
	$photoplog_posted_before_dateline = (photoplog_checkdate($photoplog_posted_before)) ?
		photoplog_get_dateline($photoplog_posted_before) : -1;

	if (
		(
			($photoplog_posted_after_dateline < 0)
				&&
			(photoplog_partial_date($photoplog_posted_after))
		)
			||
		(
			($photoplog_posted_before_dateline < 0)
				&&
			(photoplog_partial_date($photoplog_posted_before))
		)
	)
	{
		photoplog_set_abort($vbphrase['photoplog_bad_date']);
	}
	else
	{
		photoplog_add_interval_condition($photoplog_where_conditions,$photoplog_posted_after_dateline,
			$photoplog_posted_before_dateline,PHOTOPLOG_PREFIX . 'photoplog_fileuploads.dateline');
	}

	photoplog_add_interval_condition($photoplog_where_conditions,$photoplog_filesize_at_least,
		$photoplog_filesize_at_most,PHOTOPLOG_PREFIX . 'photoplog_fileuploads.filesize');

	photoplog_add_interval_condition($photoplog_where_conditions,
		$photoplog_width_at_least, $photoplog_width_at_most,
		"SUBSTRING_INDEX(" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dimensions,' x ',1)");

	photoplog_add_interval_condition($photoplog_where_conditions,
		$photoplog_height_at_least, $photoplog_height_at_most,
		"SUBSTRING_INDEX(" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.dimensions,' x ',-1)");

	photoplog_add_interval_condition($photoplog_where_conditions,
		$photoplog_number_views_at_least, $photoplog_number_views_at_most,
		PHOTOPLOG_PREFIX . 'photoplog_fileuploads.views');

	// photoplog_last_comment_after
	if (photoplog_checkdate($photoplog_last_comment_after))
	{
		$photoplog_last_comment_after_dateline = photoplog_get_dateline($photoplog_last_comment_after);
		$photoplog_last_comment_null = ($photoplog_ratecomment_condition_holds_if_none) ?
			$photoplog_last_comment_after_dateline + 1 : $photoplog_last_comment_after_dateline - 1;
		$photoplog_having_conditions[] = "IFNULL(max_ratecomment_dateline, " .
			intval($photoplog_last_comment_null) . ") > " .
			intval($photoplog_last_comment_after_dateline);
	}
	else if (photoplog_partial_date($photoplog_last_comment_after))
	{
		photoplog_set_abort($vbphrase['photoplog_bad_date']);
	}

	// photoplog_last_comment_before
	if (photoplog_checkdate($photoplog_last_comment_before)){
		$photoplog_last_comment_before_dateline = photoplog_get_dateline($photoplog_last_comment_before);
		$photoplog_last_comment_null = ($photoplog_ratecomment_condition_holds_if_none) ?
			$photoplog_last_comment_before_dateline - 1: $photoplog_last_comment_before_dateline + 1;
		$photoplog_having_conditions[] = "IFNULL(max_ratecomment_dateline, " .
			intval($photoplog_last_comment_null) . ") < " .
			intval($photoplog_last_comment_before_dateline);
	}
	else if (photoplog_partial_date($photoplog_last_comment_before))
	{
		photoplog_set_abort($vbphrase['photoplog_bad_date']);
	}

	photoplog_add_interval_condition($photoplog_having_conditions,
		$photoplog_number_comments_at_least,
		$photoplog_number_comments_at_most,'count_ratecomment');

	photoplog_add_interval_condition($photoplog_having_conditions,
		$photoplog_number_ratings_at_least, $photoplog_number_ratings_at_most,
		'number_ratecomment_rating');

	if ($photoplog_ratecomment_condition_holds_if_none)
	{
		$photoplog_fake_array = array();
		photoplog_add_interval_condition($photoplog_fake_array,
			$photoplog_average_rating_ge, $photoplog_average_rating_le,
			'average_ratecomment_rating');
		foreach ($photoplog_fake_array AS $photoplog_fake_value)
		{
			$photoplog_having_conditions[] = "((" . $photoplog_fake_value .
				") OR (average_ratecomment_rating = 0))";
		}
	}
	else
	{
		$photoplog_ar_ge_modified =
			(($photoplog_average_rating_le > 0) && ($photoplog_average_rating_ge <= 0)) ? 1 :
			$photoplog_average_rating_ge;
		photoplog_add_interval_condition($photoplog_having_conditions,
			$photoplog_ar_ge_modified, $photoplog_average_rating_le,
			'average_ratecomment_rating');
	}

	$photoplog_movefiles = false;
	if ($photoplog_destination_posted_by > 0)
	{
		$photoplog_new_username = photoplog_fetch_username($photoplog_destination_posted_by);
		if ($photoplog_new_username == '')
		{
			photoplog_set_abort($vbphrase['photoplog_bad_posted_by_id']);
		}
		else
		{
			$photoplog_movefiles = true;
		}
	}

	if ($photoplog_abort)
	{
		print_stop_message('generic_error_x', $photoplog_abort_message);
	}

	// finalize where and having conditions
	$photoplog_where_condition = (count($photoplog_where_conditions) > 0) ?
		' WHERE ' . implode(' AND ',$photoplog_where_conditions) : '';
	$photoplog_multi_where = array();

	foreach ($photoplog_multi_wheres AS $photoplog_multi_key => $photoplog_multi_value)
	{
		$photoplog_multi_where_condition = (count($photoplog_multi_value) > 0) ?
			implode(' AND ',$photoplog_multi_value) : '';
		$photoplog_multi_glue = '';
		if (count($photoplog_multi_value) > 0)
		{
			$photoplog_multi_glue = ($photoplog_where_condition) ? " AND " : " WHERE ";
		}
		$photoplog_multi_where[$photoplog_multi_key] = $photoplog_where_condition .
			$photoplog_multi_glue . $photoplog_multi_where_condition;
	}

	$photoplog_having_condition = (count($photoplog_having_conditions) > 0) ?
		' HAVING ' . implode(' AND ',$photoplog_having_conditions) : '';

	// select the fileids
	// note: max_ratecomment_dateline CAN be null, but it is only used in the having conditions,
	// and the value assigned when null may depend on the condition!
	$photoplog_fileid_counts = array();
	$photoplog_fileids = array();
	$photoplog_fileinfo = array();

	photoplog_massmove_make_select_fileids($photoplog_fileids, $photoplog_fileid_counts,
		$photoplog_fileinfo, $photoplog_multi_where, $photoplog_having_condition);

	$photoplog_fileid_totalcount = array_sum($photoplog_fileid_counts);

	if ($_REQUEST['do'] == 'preview')
	{
		$photoplog_new_go = ($photoplog_fileid_totalcount > 0) ? 'domassmove' : 'view';
		print_form_header('photoplog_massmove', $photoplog_new_go);
		construct_hidden_code('s', $vbulletin->session->vars['sessionhash']);
		if ($photoplog_fileid_totalcount > 0)
		{
			foreach ($photoplog_input_names AS $photoplog_input_key => $photoplog_input_value)
			{
				if (is_array($_REQUEST[$photoplog_input_key]))
				{
					foreach ($_REQUEST[$photoplog_input_key] AS $photoplog_two_key => $photoplog_two_val)
					{
						$photoplog_new_key = $photoplog_input_key . '[' . $photoplog_two_key . ']';
						construct_hidden_code($photoplog_new_key,$photoplog_two_val);
					}
				}
				else
				{
					construct_hidden_code($photoplog_input_key,$_REQUEST[$photoplog_input_key]);
				}
			}

			print_table_header($vbphrase['photoplog_warning_changes_permanent'],2);
			print_label_row($photoplog_phrase_number . $photoplog_fileid_totalcount);
			print_submit_row($photoplog_phrase_action,'',2,$vbphrase['photoplog_goback']);
		}
		else
		{
			print_table_header($vbphrase['photoplog_no_files_matched'],2);
			print_submit_row($vbphrase['photoplog_reset'],'',2,$vbphrase['photoplog_goback']);
		}
	}
	else
	{
		if ($photoplog_include_subcategories && $photoplog_make_subcategories && $photoplog_move_categories)
		{
			photoplog_massmove_make_subcats_as_necessary($photoplog_fileids,
				$photoplog_destination_list_categories, $photoplog_parent_list_categories,
				$photoplog_source_list_categories,$photoplog_ds_catopts);
		}

		$photoplog_destination_posted_on_dateline = ($photoplog_move_action &&
			photoplog_checkdate($photoplog_destination_posted_on)) ?
			photoplog_get_dateline($photoplog_destination_posted_on) : -1;

		if ($photoplog_move_action)
		{
			// make update set strings
			$photoplog_update_set_fileuploads_string =
				photoplog_massmove_make_update_set_fileuploads($photoplog_multi_wheres,
					$photoplog_move_categories, $photoplog_destination_list_categories,
					$photoplog_destination_posted_by,$photoplog_new_username,
					$photoplog_destination_posted_on_dateline);
			$photoplog_update_set_ratecomment_string =
				photoplog_massmove_make_update_set_ratecomment($photoplog_multi_wheres,
					$photoplog_move_categories, $photoplog_destination_list_categories);

			// update the tables
			photoplog_massmove_do_move_update($photoplog_update_set_fileuploads_string,
				$photoplog_update_set_ratecomment_string,$photoplog_fileids,$photoplog_fileinfo,
				$photoplog_movefiles,$photoplog_destination_posted_by);
		}
		else
		{
			photoplog_massmove_do_delete_update($photoplog_fileids,$photoplog_fileinfo);
		}
		photoplog_maintain_counts(0,50,0,$photoplog_header,'photoplog_massmove.php');
		// print_cp_redirect("photoplog_massmove.php?".$vbulletin->session->vars['sessionurl']."do=view", 1);
	}
}

print_cp_footer();

// ################### PHOTOPLOG MASS MOVE FUNCTIONS ######################
function photoplog_massmove_make_select_sql($where_condition, $having_condition)
{
	$select_sql = "SELECT " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.*,
			MAX(" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.dateline) AS max_ratecomment_dateline,
			COUNT(" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.commentid) AS count_ratecomment,
			SUM(IF(IFNULL(" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.rating,0) > 0, 1, 0)) AS number_ratecomment_rating,
			IFNULL(AVG(NULLIF(" . PHOTOPLOG_PREFIX . "photoplog_ratecomment.rating,0)),0) AS average_ratecomment_rating
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads LEFT JOIN " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			ON (" . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid = " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.fileid)
			$where_condition GROUP BY " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid
			$having_condition";

	return $select_sql;
}

function photoplog_massmove_make_update_set_fileuploads(&$multi_wheres, $move_categories, &$cat_map,
	$destination_posted_by, $destination_posted_by_username, $destination_posted_on_dateline)
{
	global $vbulletin;

	$update_set_array = array();
	foreach ($multi_wheres AS $multi_key => $multi_value)
	{
		$update_set_array[$multi_key] = array();
	}
	if ($move_categories)
	{
		foreach ($multi_wheres AS $multi_key => $multi_value)
		{
			$update_set_array[$multi_key][] =
				PHOTOPLOG_PREFIX . "photoplog_fileuploads.catid = " . intval($cat_map[$multi_key]);
		}
	}
	if ((intval($destination_posted_by) > 0) && ($destination_posted_by_username != ''))
	{
		foreach ($multi_wheres AS $multi_key => $multi_value)
		{
			$update_set_array[$multi_key][] =
				PHOTOPLOG_PREFIX . "photoplog_fileuploads.userid = " . intval($destination_posted_by);
			$update_set_array[$multi_key][] =
				PHOTOPLOG_PREFIX . "photoplog_fileuploads.username = '" .
				$vbulletin->db->escape_string($destination_posted_by_username) . "'";
		}
	}
	if (intval($destination_posted_on_dateline) > 0)
	{
		$destination_posted_on_dateline = min(intval($destination_posted_on_dateline),intval(TIMENOW));
		foreach ($multi_wheres AS $multi_key => $multi_value)
		{
			$update_set_array[$multi_key][] =
				PHOTOPLOG_PREFIX . "photoplog_fileuploads.dateline = " .intval($destination_posted_on_dateline);
		}
	}

	$update_set_string = array();
	foreach ($update_set_array AS $update_set_key => $update_set_value)
	{
		$update_set_string[$update_set_key] =
			(count($update_set_value) > 0) ? implode(', ', $update_set_value) : '';
	}

	return $update_set_string;
}

function photoplog_massmove_make_update_set_ratecomment(&$multi_wheres, $move_categories, &$cat_map)
{
	$update_set_array = array();
	foreach ($multi_wheres AS $multi_key => $multi_value)
	{
		$update_set_array[$multi_key] = array();
	}
	if ($move_categories)
	{
		foreach ($multi_wheres AS $multi_key => $multi_value)
		{
			$update_set_array[$multi_key][] =
				PHOTOPLOG_PREFIX . "photoplog_ratecomment.catid = " . intval($cat_map[$multi_key]);
		}
	}
	$update_set_string = array();
	foreach ($update_set_array AS $update_set_key => $update_set_value)
	{
		$update_set_string[$update_set_key] =
			(count($update_set_value) > 0) ? implode(', ', $update_set_value) : '';
	}

	return $update_set_string;
}

function photoplog_massmove_make_select_fileids(&$fileids,&$fileid_counts,&$fileinfo,&$multi_where,$having_condition)
{
	global $vbulletin;

	foreach ($multi_where AS $multi_key => $multi_value)
	{
		$fileid_array = array();
		$select_sql = photoplog_massmove_make_select_sql($multi_value, $having_condition);

		$select_query = $vbulletin->db->query_read($select_sql);

		while ($select_row = $vbulletin->db->fetch_array($select_query))
		{
			$fileid_array[] = $select_row['fileid'];
			$onefile = array();
			$onefile['fileid'] = $select_row['fileid'];
			$onefile['userid'] = $select_row['userid'];
			$onefile['username'] = $select_row['username'];
			$onefile['filename'] = $select_row['filename'];
			$fileinfo[$multi_key][] = $onefile;
		}
		$vbulletin->db->free_result($select_query);
		$fileid_counts[$multi_key] = count($fileid_array);
		$fileids[$multi_key] = (count($fileid_array) > 0) ? implode(', ', $fileid_array) : '';
	}
}

function photoplog_massmove_make_subcats_as_necessary(&$fileids,&$destination_list_categories,
	&$parent_list_categories,&$source_list_categories,&$ds_catopts)
{
	global $vbulletin;

	$source_key_of_parent_list = photoplog_make_source_key_of_parent_list($source_list_categories,
		$parent_list_categories);
	$clone_category_list = array();
	$clone_category_list[] = false;
	foreach ($source_list_categories AS $source_key => $source_cat)
	{
		if (($source_key != 0) && ($fileids[$source_key] != ''))
		{
			$clone_category_list[$source_key] = true;
			$parent_key = $source_key_of_parent_list[$source_key];
			while ($parent_key != 0)
			{
				$clone_category_list[$parent_key] = true;
				$parent_key = $source_key_of_parent_list[$parent_key];
			}
		}
		else
		{
			$clone_category_list[$source_key] = false;
		}
	}
	foreach ($clone_category_list AS $clone_key => $clone_cat)
	{
		if ($clone_key != 0)
		{
			if ($clone_cat)
			{
				$clone_parent_key = $source_key_of_parent_list[$clone_key];
				$clone_parent_cat = $destination_list_categories[$clone_parent_key];
				$orig_child_cat = $source_list_categories[$clone_key];

				$child_info_query = '';
				$child_title = '';
				$child_description = '';
				$child_displayorder = 0;
				$child_options = '';
				$child_info_query = $vbulletin->db->query_first("SELECT title,
						description, displayorder, options
					FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
					WHERE catid = " . intval($orig_child_cat));

				if ($child_info_query)
				{
					$child_title = $child_info_query['title'];
					$child_description = $child_info_query['description'];
					$child_displayorder = $child_info_query['displayorder'];
					$child_options = $child_info_query['options'];
				}

				$child_search_query = $vbulletin->db->query_first("SELECT catid
					FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
					WHERE parentid = " . intval($clone_parent_cat) . "
					AND title = '" . $vbulletin->db->escape_string($child_title) . "'
				");

				if ($child_search_query)
				{
					// child already exists
					$destination_list_categories[$clone_key] = $child_search_query['catid'];
				}
				else
				{
					// child does not exist, create clone
					// attempt to grab options from parent category
					$parent_query = $vbulletin->db->query_first("SELECT options
						FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
						WHERE catid = " . intval($clone_parent_cat) . "
					");
					if ($parent_query)
					{
						$child_options = $parent_query['options'];
					}
					$destination_list_categories[$clone_key] = photoplog_insert_category($child_title,
						$child_description,$child_displayorder,
						$clone_parent_cat,$child_options,$ds_catopts);
				}
			}
			else
			{
				// really should be no files to move, but in case something just came in, don't move it.
				$destination_list_categories[$clone_key] = $source_list_categories[$clone_key];
			}
		}
	}
}

// move file with $fileinfo from current user to $destination_userid.
function photoplog_massmove_movefile($fileinfo,$destination_userid)
{
	global $vbulletin,$photoplog_images_directory;

	if (isset($fileinfo) && is_array($fileinfo) && isset($fileinfo['userid']) && isset($fileinfo['filename']))
	{
		$source_directory = $photoplog_images_directory . "/" . $fileinfo['userid'];
		$destination_directory = $photoplog_images_directory . "/" . intval($destination_userid);
		$destination_file_name = '';

		if (is_dir($source_directory) && photoplog_ensure_directory($destination_directory))
		{
			$source_file_name = $fileinfo['filename'];
			$source_original_name = eregi_replace("^[0-9]+[_]","",$source_file_name);
			$source_file_location = $source_directory."/".$source_file_name;

			$counter = 1;
			$destination_file_name = $counter."_".$source_original_name;
			$destination_file_location = $destination_directory."/".$destination_file_name;

			while (file_exists($destination_file_location) && ($counter <= 500))
			{
				$counter++;
				$destination_file_name = $counter."_".$source_original_name;
				$destination_file_location = $destination_directory."/".$destination_file_name;
			}

			if ($counter <= 500)
			{
				copy($source_file_location,$destination_file_location);
				$destination_file_check = @getimagesize($destination_file_location);

				if (
					$destination_file_check === false
						||
					!is_array($destination_file_check)
						||
					empty($destination_file_check)
						||
					!in_array($destination_file_check[2],array(1,2,3))
				)
				{
					@unlink($destination_file_location);
				}
				else
				{
					@unlink($source_file_location);
					photoplog_physical_move($source_directory."/large",$source_file_name,
						$destination_directory."/large",$destination_file_name);
					photoplog_physical_move($source_directory."/medium",$source_file_name,
						$destination_directory."/medium",$destination_file_name);
					photoplog_physical_move($source_directory."/small",$source_file_name,
						$destination_directory."/small",$destination_file_name);
					$movefile_sql = "UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads SET filename = '" .
						$vbulletin->db->escape_string($destination_file_name) . "' WHERE fileid = " .
						intval($fileinfo['fileid']);
					$movefile_query = $vbulletin->db->query_write($movefile_sql);
				}
			}
		}
	}
}

function photoplog_massmove_do_delete_update(&$fileids,&$fileinfo)
{
	global $vbulletin,$photoplog_images_directory;

	$photoplog_filelist_arr = array();
	foreach ($fileids AS $key => $filelist)
	{
		$filelist2 = explode(',', $filelist);
		if (is_array($filelist2) && count($filelist2) > 0)
		{
			$photoplog_filelist_arr = array_unique(array_merge($photoplog_filelist_arr, $filelist2));
		}
	}
	unset($filelist2, $key, $filelist);

	if (!empty($photoplog_filelist_arr))
	{
		$photoplog_file_infos = $vbulletin->db->query_read("SELECT albumids
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE fileid IN (".implode(',', $photoplog_filelist_arr).")
		");

		$photoplog_file_albumids_arr = array();
		while ($photoplog_file_info = $vbulletin->db->fetch_array($photoplog_file_infos))
		{
			$photoplog_file_albumids = unserialize($photoplog_file_info['albumids']);
			if (is_array($photoplog_file_albumids) && count($photoplog_file_albumids) > 0)
			{
				$photoplog_file_albumids_arr = array_unique(array_merge($photoplog_file_albumids_arr, $photoplog_file_albumids));
			}
		}
		$vbulletin->db->free_result($photoplog_file_infos);
		unset($photoplog_file_albumids);

		if (!empty($photoplog_file_albumids_arr))
		{
			$photoplog_album_infos = $vbulletin->db->query_read("SELECT albumid, fileids
				FROM " . PHOTOPLOG_PREFIX . "photoplog_useralbums
				WHERE albumid IN (".implode(',', $photoplog_file_albumids_arr).")
			");

			$photoplog_album_cnt = 0;
			$photoplog_album_case1 = '';
			$photoplog_album_case2 = array();

			while ($photoplog_album_info = $vbulletin->db->fetch_array($photoplog_album_infos))
			{
				$photoplog_album_cnt ++;

				$photoplog_album_info_albumid = intval($photoplog_album_info['albumid']);
				$photoplog_album_info_fileids = unserialize($photoplog_album_info['fileids']);

				if (is_array($photoplog_album_info_fileids) && count($photoplog_album_info_fileids) > 0)
				{
					$photoplog_album_fileids = array_diff($photoplog_album_info_fileids, $photoplog_filelist_arr);

					$photoplog_album_case1 .= "WHEN ".intval($photoplog_album_info_albumid)." THEN '".$vbulletin->db->escape_string(serialize($photoplog_album_fileids))."' ";
					$photoplog_album_case2[] = intval($photoplog_album_info_albumid);

					unset($photoplog_album_info_fileids, $photoplog_album_fileids);
				}

				if (($photoplog_album_cnt % 20 == 0) && $photoplog_album_case1)
				{
					$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_useralbums
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
				$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_useralbums
					SET fileids = CASE albumid ".$photoplog_album_case1." ELSE fileids END
					WHERE albumid IN (".implode(',', $photoplog_album_case2).")
				");
			}

			$vbulletin->db->free_result($photoplog_album_infos);
			unset($photoplog_album_case1, $photoplog_album_case2);
		}
	}
	unset($photoplog_filelist_arr);

	foreach ($fileids AS $key => $filelist)
	{
		$update_fileuploads_sql = "DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads WHERE fileid IN (" . $filelist . ")";
		$update_ratecomment_sql = "DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment WHERE fileid IN (" . $filelist . ")";
		$update_fileuploads_query = $vbulletin->db->query_write($update_fileuploads_sql);

		if ($update_fileuploads_query)
		{
			if (isset($fileinfo[$key]) && is_array($fileinfo[$key]))
			{
				foreach ($fileinfo[$key] AS $onefileinfo)
				{
					$source_directory = $photoplog_images_directory . "/" . $onefileinfo['userid'];
					$source_dirs = array();
					$source_dirs[] = $source_directory;
					$source_dirs[] = $source_directory . "/large";
					$source_dirs[] = $source_directory . "/medium";
					$source_dirs[] = $source_directory . "/small";
					$source_file_name = $onefileinfo['filename'];
					foreach ($source_dirs AS $sdir)
					{
						if (is_dir($sdir))
						{
							@unlink($sdir . "/" . $source_file_name);
						}
					}
				}
			}
			$update_ratecomment_query = $vbulletin->db->query_write($update_ratecomment_sql);
		}
	}
}

function photoplog_massmove_do_move_update(&$update_set_fileuploads_string,&$update_set_ratecomment_string,
	&$fileids,&$fileinfo,$movefiles,$destination_userid)
{
	// maybe add later: exit and return empty array if error and attempt an undo
	global $vbulletin;

	foreach ($update_set_fileuploads_string AS $update_set_key => $update_set_fileuploads_value)
	{
		$update_set_ratecomment_value = $update_set_ratecomment_string[$update_set_key];

		if ($fileids[$update_set_key] != '')
		{
			if ($update_set_fileuploads_value)
			{
				$update_fileuploads_sql = "UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
					SET " . $update_set_fileuploads_value . "
					WHERE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads.fileid IN (" . $fileids[$update_set_key] . ")";

				$update_fileuploads_query = $vbulletin->db->query_write($update_fileuploads_sql);

				if ($update_fileuploads_query)
				{
					if (
						$movefiles
							&&
						isset($fileinfo[$update_set_key])
							&&
						is_array($fileinfo[$update_set_key])
					)
					{
						foreach ($fileinfo[$update_set_key] AS $onefileinfo)
						{
							photoplog_massmove_movefile($onefileinfo,$destination_userid);
						}
					}
				}
			}
			if ($update_set_ratecomment_value)
			{
				$update_ratecomment_sql = "UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
					SET " . $update_set_ratecomment_value . "
					WHERE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment.fileid IN (" . $fileids[$update_set_key] . ")";

				$update_ratecomment_query = $vbulletin->db->query_write($update_ratecomment_sql);
			}
		}
	}
}

?>