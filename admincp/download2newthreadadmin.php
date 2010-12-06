<?php

/* DownloadsII 5.0.4 by CyberRanger & Jelle
 *
 * ecDownloads 4.1 by Ronin of EliteCoders.org
 *
 *	Human License (non-binding, for the actual license please view licence.txt)
 *	Attribution-NonCommercial-ShareAlike 2.5
 *	You are free:
 *	   * to copy, distribute, display, and perform the work
 * 	   * to make derivative works
 *
 *	Under the following conditions:
 *		by: Attribution. You must attribute the work in the manner specified by the author or licensor.
 *		nc: Noncommercial. You may not use this work for commercial purposes.
 *		sa: Share Alike. If you alter, transform, or build upon this work, you may distribute the resulting work only under a license identical to this one.
 *
 *		* For any reuse or distribution, you must make clear to others the license terms of this work.
 *		* Any of these conditions can be waived if you get permission from the copyright holder.
 */

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once('./includes/class_downloads.php');
require_once('./includes/functions_newpost.php');
$dl = new vB_Downloads();
$categories = $dl->construct_select_array(0,array(0 => 'None'), false);

// ########################################################
// ################## Settings Functions ##################
// ########################################################
if ($_GET['do'] == 'settings')
{
	$cat = $db->query_first("SELECT downloadsIIforumid FROM " . TABLE_PREFIX . "dl_main WHERE 1=1");
	print_cp_header('Manage Global Forum Setting');
	print_form_header('download2newthreadadmin', 'doeditset');
	print_table_header('Edit Global Forum');
	print_forum_chooser('Select the default forum to use when creating threads. If left to \'Select Forum\', a thread will only be created if the category has a forum set.</dfn>', 'dl_forumid_cat', $cat['downloadsIIforumid'], null, true, false, '[%s]');
	print_submit_row('Save Setting', 0);
	
	print_cp_footer();
}

// ########################################################
// ################### Do Edit Settings ###################
// ########################################################
if ($_POST['do'] == 'doeditset')
{

	// Verifies that the specified forumid is valid


		if ($_POST['dl_forumid_cat'] != -1 AND $_POST['dl_forumid_cat'] != 0 AND empty($vbulletin->forumcache[$_POST['dl_forumid_cat']]))
		{
			print_stop_message('invalid_forum_specified');
		}
		else if (!($vbulletin->forumcache[$_POST['dl_forumid_cat']]['options'] & $vbulletin->bf_misc_forumoptions['cancontainthreads']) AND $_POST['dl_forumid_cat'] != 0)
		{
			print_stop_message('forum_is_a_category_allow_posting');
		}


		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET 
			`downloadsIIforumid`=".$db->sql_prepare($_POST['dl_forumid_cat'])."
			WHERE 1=1");
			
		if ($db->affected_rows() > 0)
		{
			define('CP_REDIRECT', "download2newthreadadmin.php?do=settings");
			print_stop_message('ecdownloads_category_edited');
		}
		else
		{
			print_stop_message('ecdownloads_category_not_edited');
		}
}

// ########################################################
// ################## category Functions ##################
// ########################################################
if ($_GET['do'] == 'category')
{
	print_cp_header('Manage Download Categories');
	
	print_form_header('download2newthreadadmin', 'editcat');
	print_table_header('Edit a Download category');
	print_select_row('Edit category<dfn>Select the category you wish to edit.</dfn>', 'edit', $categories);
	print_submit_row('Edit category', 0);
	
	print_cp_footer();
}


// ########################################################
// ################### Do Edit Cat Form ###################
// ########################################################
if ($_POST['do'] == 'editcat')
{
	$cat = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE `id`=".$db->sql_prepare($_POST['edit']));
	$dl->unset_subcats($cat['id']);

	print_cp_header('Manage Download Categories');
	print_form_header('download2newthreadadmin', 'doeditcat');
	print_table_header('Edit '.$cat['name'].'&#8217;s Thread Creation Information');

	print_label_row('<input type="hidden" name="cid" value="'.$cat['id'].'" />');
	print_input_row('Thread UserId<dfn>Set to the userid that should be used to create threads. LEAVE at -1 if you want the uploader\'s userid used. <strong>NOTE: You must also allow the usergroup to create new threads for the user uploading the file!!</strong></dfn>', 'dl_userid_cat', $cat['dl_userid_cat']);
	//print_input_row('Forum ID<dfn>Set to the forum id where the thread will be created.  LEAVE at -1 to use the global forum id.</dfn>', 'dl_forumid_cat', $cat['dl_forumid_cat']);
	print_forum_chooser('Forum<dfn>Set to the forum where the thread will be created.  LEAVE at \'Select Forum\' to use the global forum.</dfn>', 'dl_forumid_cat', $cat['dl_forumid_cat'], null, true, false, '[%s]');
	print_yes_no_row('Prevent thread creation?<dfn>If you want this category to NEVER have threads created, click "Yes".</dfn>', 'dl_no_threads',  $cat['dl_no_threads']);
	print_submit_row('Edit category', 0);

	print_cp_footer();
}

// ########################################################
// ################### Do Edit category ###################
// ########################################################
if ($_POST['do'] == 'doeditcat')
{

	// Verifies that the specified forumid is valid


		if ($_POST['dl_forumid_cat'] != -1 AND $_POST['dl_forumid_cat'] != 0 AND empty($vbulletin->forumcache[$_POST['dl_forumid_cat']]))
		{
			print_stop_message('invalid_forum_specified');
		}
		else if (!($vbulletin->forumcache[$_POST['dl_forumid_cat']]['options'] & $vbulletin->bf_misc_forumoptions['cancontainthreads']) AND $_POST['dl_forumid_cat'] != 0)
		{
			print_stop_message('forum_is_a_category_allow_posting');
		}

	if ($_POST['cid'] == '')
	{
		print_stop_message('ecdownloads_no_cat_to_edit');
	}
	else
	{

		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_cats SET 
			`dl_forumid_cat`=".$db->sql_prepare($_POST['dl_forumid_cat']).", 
			`dl_userid_cat`=".$db->sql_prepare($_POST['dl_userid_cat']).",
			`dl_no_threads`=".$db->sql_prepare($_POST['dl_no_threads'])."
			WHERE `id`=".$db->sql_prepare($_POST['cid']));
		if ($db->affected_rows() > 0)
		{
			define('CP_REDIRECT', "download2newthreadadmin.php?do=category");
			print_stop_message('ecdownloads_category_edited');
		}
		else
		{
			print_stop_message('ecdownloads_category_not_edited');
		}
	}
}


?>