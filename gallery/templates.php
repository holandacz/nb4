<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// #################### PREVENT UNAUTHORIZED USERS ########################
if (!defined('PHOTOPLOG_SCRIPT'))
{
	exit(); // DO NOT REMOVE THIS!
}

// ################### PRE-CACHE TEMPLATES AND DATA #######################
// get special phrase groups
$phrasegroups = array(
	'photoplog'
);

if (defined('GET_EDIT_TEMPLATES'))
{
	$phrasegroups = array(
		'photoplog',
		'posting'
	);
}

// get special data templates from the datastore
$specialtemplates = array(
	'smiliecache',
	'bbcodecache',
	'userstats',
	'photoplog_dscat'
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'shell_blank',
	'photoplog_error_page',
	'photoplog_sub_navbar'
);

// pre-cache templates used by all actions
if (defined('PHOTOPLOG_RANDOM') && !defined('PHOTOPLOG_HTTPD') && !isset($_REQUEST['c']) && !isset($_REQUEST['n']) && !isset($_REQUEST['u']) && !isset($_REQUEST['q']) && !isset($_REQUEST['v']))
{
	$globaltemplates = array_merge($globaltemplates, array(
		'photoplog_minithumb_pics',
		'photoplog_minithumb_nails',
		'photoplog_thumb_pics',
		'photoplog_thumb_info',
		'photoplog_thumb_nails'
	));
}

// pre-cache templates used by specific actions
$actiontemplates = array();
if (defined('PHOTOPLOG_THIS_SCRIPT'))
{
	if (PHOTOPLOG_THIS_SCRIPT == 'albums')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'view';
		}

		$actiontemplates = array(
			'add' => array(
				'photoplog_album_form'
			),
			'delete' => array(
				'photoplog_expunge_form'
			),
			'edit' => array(
				'photoplog_album_form'
			),
			'insert' => array(
				'photoplog_album_select'
			),
			'show' => array(
				'photoplog_album_view_bit',
				'photoplog_album_view_list'
			),
			'view' => array(
				'photoplog_album_bit',
				'photoplog_album_list',
				'photoplog_album_view_bit',
				'photoplog_album_view_list'
			)
		);
	}

	if (PHOTOPLOG_THIS_SCRIPT == 'categories')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'suggest';
		}

		$actiontemplates = array(
			'create' => array(
				'photoplog_category_form'
			),
			'suggest' => array(
				'photoplog_category_form'
			)
		);
	}

	if (PHOTOPLOG_THIS_SCRIPT == 'comment')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'comment';
		}

		$actiontemplates = array(
			'comment' => array(
				'photoplog_comment_form'
			),
			'delete' => array(
				'photoplog_delete_form'
			),
			'edit' => array(
				'photoplog_comment_form'
			)
		);
	}

	if (PHOTOPLOG_THIS_SCRIPT == 'delete')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'delete';
		}

		$actiontemplates = array(
			'delete' => array(
				'photoplog_delete_form'
			)
		);
	}

	if (PHOTOPLOG_THIS_SCRIPT == 'edit')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'edit';
		}

		$actiontemplates = array(
			'edit' => array(
				'photoplog_edit_form',
				'photoplog_edit_select_category',
				'photoplog_field_checkbox_option',
				'photoplog_field_description',
				'photoplog_field_input',
				'photoplog_field_radio',
				'photoplog_field_radio_option',
				'photoplog_field_select',
				'photoplog_field_select_multiple',
				'photoplog_field_select_option',
				'photoplog_field_textarea',
				'photoplog_field_title'
			)
		);
	}

	if (PHOTOPLOG_THIS_SCRIPT == 'index')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'view';
		}

		$actiontemplates = array(
			'view' => array(
				'photoplog_block_bit',
				'photoplog_block_list',
				'photoplog_cat_bit',
				'photoplog_cat_list',
				'photoplog_file_bit',
				'photoplog_file_list',
				'photoplog_film_frame',
				'photoplog_film_prevnext',
				'photoplog_film_strip',
				'photoplog_letter_bar',
				'photoplog_letter_bar_bit',
				'photoplog_quickreply_form',
				'photoplog_rate_bit',
				'photoplog_rate_list',
				'photoplog_stat_bar',
				'photoplog_status_key',
				'photoplog_user_bit',
				'photoplog_user_list',
				'photoplog_view_file',
				'photoplog_view_file_field',
				'postbit_onlinestatus',
				'showthread_quickreply'
			)
		);
	}

	if (PHOTOPLOG_THIS_SCRIPT == 'report')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'report';
		}

		$actiontemplates = array(
			'report' => array(
				'photoplog_report_form'
			)
		);
	}

	if (PHOTOPLOG_THIS_SCRIPT == 'search')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'query';
		}

		$actiontemplates = array(
			'query' => array(
				'photoplog_search_form'
			),
			'view' => array(
				'photoplog_block_bit',
				'photoplog_block_list',
				'photoplog_file_bit',
				'photoplog_file_list'
			)
		);
	}

	if (PHOTOPLOG_THIS_SCRIPT == 'slideshow')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'query';
		}

		$actiontemplates = array(
			'query' => array(
				'photoplog_slideshow_form'
			),
			'show' => array(
				'photoplog_slideshow_page'
			),
			'view' => array(
				'photoplog_slideshow_page'
			)
		);
	}

	if (PHOTOPLOG_THIS_SCRIPT == 'upload')
	{
		if (empty($_REQUEST['do']))
		{
			$_REQUEST['do'] = 'upload';
		}

		$actiontemplates = array(
			'upload' => array(
				'photoplog_field_checkbox_option',
				'photoplog_field_description',
				'photoplog_field_input',
				'photoplog_field_radio',
				'photoplog_field_radio_option',
				'photoplog_field_select',
				'photoplog_field_select_multiple',
				'photoplog_field_select_option',
				'photoplog_field_textarea',
				'photoplog_field_title',
				'photoplog_upload_form',
				'photoplog_upload_select_category'
			)
		);
	}
}

if (defined('PHOTOPLOG_HTTPD'))
{
	$phrasegroups = array();
	$specialtemplates = array();
	$globaltemplates = array();
	$actiontemplates = array();
}

?>