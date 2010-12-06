<?php

/* DownloadsII 5.0.8 by CyberRanger & Jelle
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
 
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'downloads');
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array(
	'ecdownloads',
	'posting'
);

// get special data templates from the datastore
$specialtemplates = array(
	'smiliecache',
	'bbcodecache',
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'DOWNLOADS',
	'downloads_main',
	'downloads_main_catbit',
	'downloads_panel_bit',
	'downloads_panel_side',
	'downloads_panel_top',
	'downloads_wrapper_top',
	'downloads_wrapper_side',
	'downloads_wrapper_none',
	'downloads_warning'
);

// pre-cache templates used by specific actions
$actiontemplates = array(
	'add' => array(
		'downloads_file_addit',
		'editor_clientscript',
		'editor_jsoptions_font',
		'editor_jsoptions_size',
		'editor_toolbar_off',
		'editor_toolbar_on',
		'editor_smilie',
		'editor_smiliebox',
		'editor_smiliebox_row',
		'editor_smiliebox_straggler',
		'newpost_disablesmiliesoption'
	),
	'assignuser' => array(
		'downloadsbuddy_assign_user'
	),
	'cat' => array(
		'downloads_cat',
		'downloads_cat_filebit',
		'downloads_cat_files',
		'downloads_cat_subbit',
		'downloads_cat_subs'
	),
	'edit' => array(
		'downloads_file_addit',
		'editor_clientscript',
		'editor_jsoptions_font',
		'editor_jsoptions_size',
		'editor_toolbar_off',
		'editor_toolbar_on',
		'editor_smilie',
		'editor_smiliebox',
		'editor_smiliebox_row',
		'editor_smiliebox_straggler',
		'newpost_disablesmiliesoption'
	),
	'file' => array(
		'downloads_file',
		'downloads_file_addit',
		'editor_clientscript',
		'editor_jsoptions_font',
		'editor_jsoptions_size',
		'editor_toolbar_off',
		'editor_toolbar_on',
		'editor_smilie',
		'editor_smiliebox',
		'editor_smiliebox_row',
		'editor_smiliebox_straggler',
		'newpost_disablesmiliesoption',
		'downloads_file_comment'
	),
	'manfiles' => array(
		'downloads_man',
		'downloads_man_bit'
	),
	'my' => array(
		'downloads_my',
		'downloads_my_bit'
	),
	'search' => array(
		'downloads_search',
		'downloads_search_result',
		'downloads_search_result_bit'
	),
	'stats' => array(
		'downloads_stats',
		'downloads_stats_bit'
	),
	'tree' => array(
		'downloads_tree'
	)		

);

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once('./includes/class_downloads.php');
require_once('./includes/class_bbcode.php');
require_once('./includes/functions_editor.php'); /* included to build editor for description */
require_once('./includes/functions_wysiwyg.php'); /* included to build editor for description */
require_once('./includes/functions_newpost.php'); /* included to build editor for description */
require_once('./includes/functions.php'); /* included to strip bbcode */

$parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
$dl = new vB_Downloads();
$navbits = array('downloads.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['ecdownloads_downloads']);
$forceredirect = false;
$vbulletin->url = 'downloads.php' . $vbulletin->session->vars['sessionurl_q'];

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
		$limitjoin .= " group delay: " .$permissions['ecdownloaddelaygrp'];
if ($dl->disabled)
{
	if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canviewdisabled']))
	{
		$dwarning = $vbphrase['ecdownloads_disabled'].': '.$dl->reason;
		eval('$dwarning = "' . fetch_template('downloads_warning') . '";');
		$header = $dwarning . "<br />" . $header;
		$footer .= "<br />" . $dwarning;
	}
	else
	{
		$errormessage = $vbphrase['ecdownloads_disabled'].': '.$dl->reason;
		eval('print_output("' . fetch_template('STANDARD_ERROR') . '");');
		exit();
	}
}

if (!is_dir($dl->url))
{
	$dwarning = $vbphrase['ecdownloads_dir_doesnt_exist'];
	eval('$dwarning = "' . fetch_template('downloads_warning') . '";');
	$header = $dwarning . "<br />" . $header;
	$footer .= "<br />" . $dwarning;
}
else if (!is_writable($dl->url))
{
	$dwarning = $vbphrase['ecdownloads_dir_not_writeable'];
	eval('$dwarning = "' . fetch_template('downloads_warning') . '";');
	$header = $dwarning . "<br />" . $header;
	$footer .= "<br />" . $dwarning;
}
else if (!file_exists($dl->url."index.html") AND !file_exists($dl->url."index.php"))
{
	$dwarning = $vbphrase['ecdownloads_no_index_in_dir'];
	eval('$dwarning = "' . fetch_template('downloads_warning') . '";');
	$header = $dwarning . "<br />" . $header;
	$footer .= "<br />" . $dwarning;
}

// Check for safe mode
if (ini_get('safe_mode') AND !is_dir($dl->url."/ec_tmp/"))
{
	$errormessage = $vbphrase['ecdownloads_safe_mode'].': '.$dl->reason;
	eval('print_output("' . fetch_template('STANDARD_ERROR') . '");');
	exit();
}

if (!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canviewfiles']))
{
	print_no_permission();
}

$dlinks = '<a href="./downloads.php?">'.$vbphrase['ecdownloads_main'].'</a> | <a href="./downloads.php?do=tree">'.$vbphrase['ecdownloads_category_tree'].'</a> | <a href="./downloads.php?do=stats">'.$vbphrase['ecdownloads_stats'].'</a> | <a href="./downloads.php?do=search">'.$vbphrase['ecdownloads_search'].'</a>';

if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canuploadfiles']) OR ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canlinktofiles']))
{
	if ($_GET['do'] == 'cat')
	{
		$catid = $vbulletin->input->clean_gpc('r', 'id', TYPE_UINT);
	}
	else
	{
		$catid = '';
	}

	$dlinks .= ' | <a href="./downloads.php?do=add&amp;cat='.$catid.'">'.$vbphrase['ecdownloads_add'].'</a> | <a href="./downloads.php?do=my">'.$vbphrase['ecdownloads_my_files'].'</a>';
}
if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanagepurgatory']) OR ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles']))
{
	$dlinks .= ' | <a href="./downloads.php?do=manfiles">'.$vbphrase['ecdownloads_manage_files'].'</a> | <a href="./downloads.php?do=manfiles&amp;act=updatecounters">'.$vbphrase['ecdownloads_update_counters'].'</a>';
}

// get the main page statistics
$dpanel_latest_bits = $dl->stats['latestall'];
$dpanel_popular_bits = $dl->stats['popularall'];
$dpanel_contrib_bits = $dl->stats['contriball'];

if ($_GET['do'] == 'cat')
{
	$cleancatid = $vbulletin->input->clean_gpc('r', 'id', TYPE_UINT);

	$catexclude = $dl->exclude_cat();

	$cat = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE ".$catexclude." `id` = ".$cleancatid);
	if ($cat['id'] == 0)
	{
		eval(print_standard_redirect('ecdownloads_msg_invalid_cat', true, true));
	}
	$dlcustomtitle = $cat['name'];
	
	$navbits += $dl->build_cat_nav($cleancatid);

	$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE ".$catexclude." `parent` = ".$cleancatid." ORDER BY ".$dl->order);
	if ($db->num_rows($result) > 0)
	{
		while ($sub = $db->fetch_array($result))
		{
			if ($dl->hidesubcatssub == 0)
			{
				$subcats = $dl->grab_subcats_by_name_client($sub['id']);
			}
			else
			{
				$subcats = '';
			}
			
			$files = vb_number_format($sub['files']);
			exec_switch_bg();
			eval('$dsubbits .= "' . fetch_template('downloads_cat_subbit') . '";');
		}
		eval('$dsubcats .= "' . fetch_template('downloads_cat_subs') . '";');
	}
	$filesexclude = $dl->exclude_files();
	
	$temp = $db->query_first("SELECT COUNT(*) as files FROM " . TABLE_PREFIX . "dl_files WHERE ".$filesexclude." `category` = ".$cleancatid);
	
	if ($temp['files'] == 0 AND $db->num_rows($result) == 0)
	{
		eval(print_standard_redirect('ecdownloads_msg_no_files_in_cat', true, true));
	}
	
	$db->free_result($result);

	$sortfield = $vbulletin->input->clean_gpc('r', 'sortfield', TYPE_STR);
	$sortfields = array(
		'name',
		'date',
		'downloads',
		'last',
		'rating',
		'comments'
	);
	if (!in_array($sortfield, $sortfields))
	{
		$sortfield = 'date';
	}
	$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

	sanitize_pageresults($temp['files'], $pagenumber, $dl->perpage, $dl->perpage, $dl->perpage);

	$limit = (($pagenumber -1)*$dl->perpage);
	$navigation = construct_page_nav($pagenumber, $dl->perpage, $temp['files'], "downloads.php?" . $vbulletin->session->vars['sessionurl'] . "do=cat&amp;id=$cleancatid", ""
		. (!empty($sortfield) ? "&amp;sort=$sortfield" : "")
	);

	if ($sortfield == 'name')
	{
		$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE `purgatory` = '0' AND `category` = ".$cleancatid." ORDER BY `pin` DESC, `".$sortfield."` ASC LIMIT ".$limit.",".$dl->perpage);
	}
	else
	{
		$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE `purgatory` = '0' AND `category` = ".$cleancatid." ORDER BY `pin` DESC, `".$sortfield."` DESC LIMIT ".$limit.",".$dl->perpage);
	}
	
	if ($db->num_rows($result) > 0)
	{
		while ($file = $db->fetch_array($result))
		{
			$date = vbdate($vbulletin->options['dateformat'], $file['date'], true);
			$downloads = vb_number_format($file['downloads']);

			if ($file['link'] == 1)
			{
				$target = 'target="_blank"';
			}
			else
			{
				$target = 'target="_self"';
			}
			
			if ($dl->smalldesclen > 0)
			{
				$smalldesc = strip_bbcode($file['description'], false, false, false);
				$smalldesc = substr($smalldesc, 0, $dl->smalldesclen);
				$smalldesc = $vbulletin->input->clean($smalldesc, TYPE_NOHTML);
				$smalldesc = ": ".$smalldesc;
			}
			
			if (!$dl->includethumb)
			{
				$thumb_q = $db->query_read("SELECT ".trim(thumb)." FROM " . TABLE_PREFIX . "dl_images WHERE file = ".$file['id']." ORDER BY `id` ASC LIMIT 0,1");
				if ($db->num_rows($thumb_q) > 0)
				{
					while ($thumb_x = $db->fetch_array($thumb_q))
					{
						if ($dl->smalldesclen > 0)
						{
							$filethumb = '<a href="./downloads.php?do=file&amp;id='.$file['id'].'&amp;act=down" '.$target.'><img src="'.$dl->url.$thumb_x['thumb'].'" align="middle" title="'.$vbphrase['ecdownloads_download'].'&nbsp;'.$file['name'].'" alt="'.$vbphrase['ecdownloads_downloads'].'" border="0" /></a>&nbsp;';
						}
						else
						{
							$filethumb = '<a href="./downloads.php?do=file&amp;id='.$file['id'].'" '.$target.'><img src="'.$dl->url.$thumb_x['thumb'].'" align="middle" title="'.$vbphrase['ecdownloads_download'].'&nbsp;'.$file['name'].'" alt="'.$vbphrase['ecdownloads_downloads'].'" border="0" /></a>&nbsp;';
						}
					}
				}
				else
				{
					$filethumb = '';
				}
			}
			else
			{
				$filethumb = '';
			}

			if ($file['size'] == 0)
			{
				$size = $vbphrase['ecdownloads_unknown_size'];
			}
			else
			{
				$size = vb_number_format($file['size'], 0, true);
			}
			
			if (strlen($file['description']) > $dl->smalldesclen AND $dl->smalldesclen > 0)
			{
				$smalldesc .= '&nbsp;... [<a href="downloads.php?do=file&amp;id='.$file['id'].'">'.$vbphrase['ecdownloads_more'].'</a>]';
			}

			if ($dl->smalldesclen > 0)
			{
				$download = '<a href="./downloads.php?do=file&amp;id='.$file['id'].'&amp;act=down" '.$target.'><img src="'.$stylevar[imgdir_button].'/'.$vbphrase['ecdownloads_download_pic'].'" width="15" height="15" align="middle" title="'.$vbphrase['ecdownloads_download'].'&nbsp;'.$file['name'].'" alt="'.$vbphrase['ecdownloads_downloads'].'" border="0" /></a>&nbsp;';
			}
			else
			{
				$download = '<a href="./downloads.php?do=file&amp;id='.$file['id'].'" '.$target.'><img src="'.$stylevar[imgdir_button].'/'.$vbphrase['ecdownloads_download_pic'].'" width="15" height="15" align="middle" title="'.$vbphrase['ecdownloads_download'].'&nbsp;'.$file['name'].'" alt="'.$vbphrase['ecdownloads_downloads'].'" border="0" /></a>&nbsp;';
			}

			exec_switch_bg();
			eval('$dfilebits .= "' . fetch_template('downloads_cat_filebit') . '";');
		}
		eval('$dfiles .= "' . fetch_template('downloads_cat_files') . '";');
	}
	$db->free_result($result);

	$category_array = $dl->construct_select_array(0, array('#' => $vbphrase['ecdownloads_category_jump']), '');
	foreach ($category_array AS $cat_key => $cat_value)
	{
		$category_jump .= '<option value="./downloads.php?do=cat&amp;id='.$cat_key.'">'.$cat_value.'</option>';
	}

	eval('$dmain_jr = "' . fetch_template('downloads_cat') . '";');
	eval('$dpanel = "' . fetch_template('downloads_panel_side') . '";');
	eval('$dmain = "' . fetch_template('downloads_wrapper_side') . '";');
}
else if ($_GET['do'] == 'tree')
{
	$navbits['downloads.php?do=tree'] = $vbphrase['ecdownloads_category_tree'];
	$dlcustomtitle = $vbphrase['ecdownloads_category_tree'];

	$category_array = $dl->construct_select_array(0, array(), '');
	foreach ($category_array AS $cat_key => $cat_value)
	{
		$temp = $db->query_first("SELECT COUNT(*) as events 
							FROM " . TABLE_PREFIX . "dl_files 
							WHERE 	category =  ".$cat_key."");

		$catidinfo = '';
		if ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles'])
		{
			$catidinfo = "&nbsp;&nbsp;&nbsp;[".$vbphrase['ecdownloads_category_id_is']." ".$cat_key."]";
		}

		exec_switch_bg();
		if ($temp['events'] > 0)
		{
			$category_tree .= '<tr><td class="'.$bgclass.'"><a href="./downloads.php?do=cat&amp;id='.$cat_key.'">'.$cat_value.'</a> ('.$temp['events'].' '.$vbphrase['ecdownloads_files'].')'.$catidinfo.'</td></tr>';
		}
		else
		{
			$category_tree .= '<tr><td class="'.$bgclass.'">'.$cat_value.$catidinfo.'</td></tr>';
		}

		$db->free_result($temp);
	}

	eval('$dmain_jr .= "' . fetch_template('downloads_tree') . '";');
	if ($vbulletin->options['ecdownloads_tops'])
	{
		eval('$dpanel = "' . fetch_template('downloads_panel_side') . '";');
		eval('$dmain .= "' . fetch_template('downloads_wrapper_side') . '";');
	}
	else
	{
		eval('$dmain .= "' . fetch_template('downloads_wrapper_top') . '";');
	}
}
else if ($_GET['do'] == 'file')
{
	if (!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canviewfiles']))
	{
		print_no_permission();
	}

	$cleanfileid = $vbulletin->input->clean_gpc('r', 'id', TYPE_UINT);

	$filesexclude = $dl->exclude_files();

	$file = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE ".$filesexclude." `id` = ".$cleanfileid);
	if ($file['id'] == 0)
	{
		eval(print_standard_redirect('ecdownloads_msg_invalid_file', true, true));
	}

	$dlcustomtitle = $file['name'];

	if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles']) OR
	   (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditownfiles']) AND
	   ($file['uploaderid'] == $vbulletin->userinfo['userid'])))
	{
		$dlinks .= ' | <a href="./downloads.php?do=assignuser&amp;id='.$file['id'].'">'.$vbphrase['ecdownloads_assign_uploader'].'</a>' ;
	}

	$vbulletin->url = './downloads.php?do=file&id='.$file['id'];

	$navbits += $dl->build_cat_nav($file['category']);
	$navbits['downloads.php?do=file&id='.$file['id']] = $file['name'];

	if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles']) OR
	   (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditownfiles']) AND
	   ($file['uploaderid'] == $vbulletin->userinfo['userid'])))
	{
		$showedit = true;
	}

	if ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanagepurgatory'])
	{
		$showapprove = true;
	}

	if (($_GET['rating'] > 0) AND ($vbulletin->userinfo['userid'] > 0))
	{
		$result = $db->query_first("SELECT COUNT(*) AS voteamount FROM " . TABLE_PREFIX . "dl_votes WHERE `user` = ".$vbulletin->userinfo['userid']." AND `file` = ".$file['id']);
		if ($result['voteamount'] == 0)
		{
			$result = $db->query_write("INSERT INTO " . TABLE_PREFIX . "dl_votes (user, file, value) VALUES(".$vbulletin->userinfo['userid'].", ".$file['id'].", ".$db->sql_prepare($_GET['rating']).")");
			if ($result)
			{
				$voteinfo = $db->query_first("SELECT COUNT(*) AS votes, SUM(`value`) AS total FROM " . TABLE_PREFIX . "dl_votes WHERE `file` = ".$file['id']);
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `rating`=".$db->sql_prepare($voteinfo['total']/$voteinfo['votes'])." WHERE `id` = ".$file['id']);
				eval(print_standard_redirect('ecdownloads_msg_vote_success', true, true));
			}
			else
			{
				eval(print_standard_redirect('ecdownloads_msg_failure', true, true));
			}
		}
		else
		{
			eval(print_standard_redirect('ecdownloads_msg_already_voted', true, true));
		}
		$db->free_result($result);
	}

	if ($_GET['act'] == 'down')
	{
		if (!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['candownloadfiles']) OR
		   (($file['purgatory'] == 1) AND !($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanagepurgatory'])))
		{
			print_no_permission();
		}
		else
		{
			// Check if strict daily limits are in effect, then set conditional for testing limits
			if (!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['dailylimits']))
			{
				$limitjoin = "AND";// strict limits is set to NO
				// check if the users has exceeded the max daily download amount
				if (($permissions['ecdownloadsmaxdailydl'] >= 0) AND ($permissions['ecdownloadsmaxdailyfiles'] >= 0))
				{
					// check if max is set to zero 
					if (($permissions['ecdownloadsmaxdailydl'] == 0) AND ($permissions['ecdownloadsmaxdailyfiles'] == 0))
					{
						eval(print_standard_redirect('ecdownloads_daily_download_amount_exceeded', true, true));
					}
	
					// check amount downloaded against maxdaily
					if ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['ecdownloadsmaxdailydl'] > 0)
					{
						$tempmax1 = $db->query_first("SELECT SUM(filesize) AS dlamount FROM " . TABLE_PREFIX . "dl_downloads WHERE `userid` = ".$vbulletin->userinfo['userid']." AND `time` >= ".$db->sql_prepare((int) (TIMENOW - 86400)));
	
						$tempnew = $db->query_first("SELECT size FROM " . TABLE_PREFIX . "dl_files WHERE `id`=".$db->sql_prepare($file['id']));
						$dlremaining = ($permissions['ecdownloadsmaxdailydl'] * 1048576) - ($tempmax1['dlamount'] + $tempnew['size']);
	
						if ($dlremaining < 0)
						{
							eval(print_standard_redirect('ecdownloads_daily_download_amount_exceeded', true, true));
						}
					}
					// check amount downloaded against max daily number of files
					if ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['ecdownloadsmaxdailyfiles'] > 0)
					{
						$tempmax1 = $db->query_first("SELECT COUNT(*) AS dlcount FROM " . TABLE_PREFIX . "dl_downloads WHERE `userid` = ".$vbulletin->userinfo['userid']." AND `time` >= ".$db->sql_prepare((int) (TIMENOW - 86400)));
						$dlremaining = ($permissions['ecdownloadsmaxdailyfiles']) - ($tempmax1['dlcount']);
	
						if ($dlremaining <= 0)
						{
							eval(print_standard_redirect('ecdownloads_daily_download_amount_exceeded', true, true));
						}
					}
				}

			}
			else
			{
				$limitjoin = "OR"; // strict limits is set to YES
				// check if the users has exceeded the max daily download amount
				if (($permissions['ecdownloadsmaxdailydl'] >= 0) OR ($permissions['ecdownloadsmaxdailyfiles'] >= 0))
				{
					// check if max is set to zero 
					if (($permissions['ecdownloadsmaxdailydl'] == 0) OR ($permissions['ecdownloadsmaxdailyfiles'] == 0))
					{
						eval(print_standard_redirect('ecdownloads_daily_download_amount_exceeded', true, true));
					}
	
					// check amount downloaded against maxdaily
					if ($permissions['ecdownloadsmaxdailydl'] > 0)
					{
						$tempmax1 = $db->query_first("SELECT SUM(filesize) AS dlamount 
													  FROM " . TABLE_PREFIX . "dl_downloads 
													  WHERE `userid`=".$vbulletin->userinfo['userid']." 
														AND `clientip`=".$db->sql_prepare($_SERVER['REMOTE_ADDR'])." 
													  	AND `time` >= ".$db->sql_prepare((int) (TIMENOW - 86400)));
	
						$tempnew = $db->query_first("SELECT size FROM " . TABLE_PREFIX . "dl_files WHERE `id`=".$db->sql_prepare($file['id']));
						$dlremaining = ($permissions['ecdownloadsmaxdailydl'] * 1048576) - ($tempmax1['dlamount'] + $tempnew['size']);
	
						if ($dlremaining < 0)
						{
							eval(print_standard_redirect('ecdownloads_daily_download_amount_exceeded', true, true));
						}
					}
					// check amount downloaded against max daily number of files
					if ($permissions['ecdownloadsmaxdailyfiles'] > 0)
					{
						$tempmax1 = $db->query_first("SELECT COUNT(*) AS dlcount 
													  FROM " . TABLE_PREFIX . "dl_downloads 
													  WHERE `userid`=".$vbulletin->userinfo['userid']." 
														AND `clientip`=".$db->sql_prepare($_SERVER['REMOTE_ADDR'])." 
													  	AND `time` >= ".$db->sql_prepare((int) (TIMENOW - 86400)));
						$dlremaining = ($permissions['ecdownloadsmaxdailyfiles']) - ($tempmax1['dlcount']);
	
						if ($dlremaining <= 0)
						{
							eval(print_standard_redirect('ecdownloads_daily_download_amount_exceeded', true, true));
						}
					}
				}
			}
			
			if (($permissions['ecdownloaddelaygrp'] > 0) OR ($vbulletin->options['ecdownloaddelay']) > 0)
			{
					// check for possible Denial of service attack
					$temptime = $db->query_first("
						SELECT time 
						FROM " . TABLE_PREFIX . "dl_downloads 
						WHERE `userid`=".$vbulletin->userinfo['userid']." 
							AND `clientip`=".$db->sql_prepare($_SERVER['REMOTE_ADDR'])."
						ORDER BY `time` DESC LIMIT 0,1
					");
					
					if ($permissions['ecdownloaddelaygrp'] > 0)
					{
						$timedelay = $permissions['ecdownloaddelaygrp'];
					}
					else
					{
						$timedelay = $vbulletin->options['ecdownloaddelay'];
					}
					
		
					if (TIMENOW - $temptime['time'] < $timedelay)
					{
						$timedelay = round($temptime['time'] + $timedelay - TIMENOW, 0);
		
						eval(standard_error(fetch_error('ecdownloads_download_too_quickly', $timedelay)));
						exit();
					}
		
					$db->free_result($temptime);
			}
			// hook for pre-download checks
			($hook = vBulletinHook::fetch_hook('dl_pre_download')) ? eval($hook) : false;

			$db->query_write("INSERT INTO " . TABLE_PREFIX . "dl_downloads (userid, fileid, user, file, time, filesize, clientip) 
							VALUES(".$vbulletin->userinfo['userid'].",".$file['id'].",".$db->sql_prepare($vbulletin->userinfo['username']).",".$db->sql_prepare($file['name']).",".TIMENOW.",".$db->sql_prepare($file['size']).",".$db->sql_prepare($_SERVER['REMOTE_ADDR']).")");
			$db->query_write("UPDATE " . TABLE_PREFIX . "user SET `downloads`=`downloads`+1 WHERE `userid`=".$vbulletin->userinfo['userid']);
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `downloads`=`downloads`+1, `last`=".TIMENOW." WHERE `id`=".$file['id']);
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `downloads`=`downloads`+1");
			$dl->update_popular_files();
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_stats SET `downloads`=`downloads`+1, `bandwidth`=`bandwidth`+".$db->sql_prepare($file['size'])." WHERE `day`=".$db->sql_prepare((int) (TIMENOW/86400)));
			if ($db->affected_rows() == 0)
			{
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "dl_stats (day, downloads, bandwidth) VALUES (".$db->sql_prepare((int) (TIMENOW/86400)).",1,".$db->sql_prepare($file['size']).")");
			}
			
			

			if ($file['link'] == 0)
			{
				if (is_dir($dl->url."/ec_tmp/"))
				{
					if ($dh = opendir($dl->url."/ec_tmp/"))
					{
						// iterate over file list
						while (($filename = readdir($dh)) !== false)
						{
							//if (preg_match("/dlver-/",$filename))
							//{
							@unlink($dl->url."/ec_tmp/".$filename); // secure download, delete the files
							//}
						}
						// close directory
						closedir($dh);
					}
				}

			// hook for post-download checks
			($hook = vBulletinHook::fetch_hook('dl_post_download')) ? eval($hook) : false;

				$ext = strtolower(substr($dl->url.$file['url'], strrpos($dl->url.$file['url'], '.')+1));
				if ($dl->renamefiles)
				{
					$newfilename = preg_replace('/[^\sA-Za-z0-9\&\(\)\-\_\+\{\[\}\]\,\.]+/','',$file['name']).'.'.$ext;
				}
				else
				{
					$newfilename = preg_replace('/[0-9]+-/','',$file['url']);
				}


				/* $random = "ec_";
				for ($i = 1; $i <= 3; $i++)
				{
					switch(rand(1,3))
					{
						case 1: $random.=chr(mt_rand(48,57)); break;  // 0-9
						case 2: $random.=chr(mt_rand(65,90)); break;  // A-Z
						case 3: $random.=chr(mt_rand(97,122)); break; // a-z
					}
				} */

				// for sites without safe mode on, create the ec_tmp folder
				if (!is_dir($dl->url."/ec_tmp/"))
				{
					mkdir($dl->url."/ec_tmp");
				}

				$dlfilename = $dl->url."/ec_tmp/".$newfilename;
				copy($dl->url.$file['url'],$dlfilename);

						// test download code

						/* $filename = $_GET['file']; */
						$filename = $dlfilename;
						// required for IE, otherwise Content-disposition is ignored
						if (ini_get('zlib.output_compression'))
						{
							ini_set('zlib.output_compression', 'Off');
						}
						
						// addition by Jorg Weske
						$file_extension = strtolower(substr(strrchr($filename,"."),1));

						if ($filename == '')
						{
							echo "<html><head><title>DownloadsII</title></head><body>ERROR: download file NOT SPECIFIED.</body></html>";
							exit;
						}
						else if (!file_exists($filename))
						{
							echo "<html><head><title>DownloadsII</title></head><body>ERROR: File not found.</body></html>";
							exit;
						}
						switch ($file_extension)
						{
							case "asf": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="video/x-ms-asf"; break;
							case "avi": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="video/avi"; break;
							case "doc": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="application/msword"; break;
							case "exe": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="application/octet-stream"; break;
							case "gif": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="image/gif"; break;
							case "html": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="text/html"; break;
							case "htm": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="text/html"; break;
							case "jpeg": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="image/jpg"; break;
							case "jpg": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="image/jpg"; break;
							case "mp3": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="audio/mpeg3"; break;
							case "pdf": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="application/pdf"; break;
							case "ppt": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="application/vnd.ms-powerpoint"; break;
							case "png": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="image/png"; break;
							case "wav": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="audio/wav"; break;
							case "xls": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="application/vnd.ms-excel"; break;
							case "zip": if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="application/zip"; break;
							default: if (strstr("|".str_replace(" ","|",$vbulletin->options['ecextopen'])."|",$ext)){header("location: $dlfilename"); break;}$ctype="application/force-download";
						}
						header("Pragma: public"); // required
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Cache-Control: private",false); // required for certain browsers
						header("Content-Type: $ctype; name=\"".basename($filename)."\";");
						// change, added quotes to allow spaces in filenames, by Rajkumar Singh
						header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
						header("Content-Transfer-Encoding: binary");
						header("Content-Length: ".filesize($filename));
						readfile("$filename");
						exit();
				
				// end test download code
			}
			else
			{
				header("Location: $file[url]");
				exit();
			}
		}
	}

	if ($dl->allowcomments)
	{
		if (($_POST['message'] != '') AND 
			($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['cancomment']))
		{
			if ($_POST['wysiwyg'] == 1)
			{
				$_POST['message'] = convert_wysiwyg_html_to_bbcode($_POST['message'], 0);
			}
			
			$_POST['message'] = convert_url_to_bbcode($_POST['message']);
			$db->query_write("INSERT INTO " . TABLE_PREFIX . "dl_comments (`fileid`,`author`,`authorid`,`date`,`message`) VALUES(".$file['id'].",".$db->sql_prepare($vbulletin->userinfo['username']).",".$vbulletin->userinfo['userid'].",".$db->sql_prepare(TIMENOW).",".$db->sql_prepare($_POST['message']).")");
			$db->query_write("UPDATE " . TABLE_PREFIX . "user SET `comments`=`comments`+1 WHERE `userid` = ".$vbulletin->userinfo['userid']);
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `comments`=`comments`+1");
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `comments`=`comments`+1 WHERE `id` = ".$file['id']);
			eval(print_standard_redirect('ecdownloads_msg_comment_added', true, true));
		}

		if ($_GET['act'] == 'delcomment')
		{
			$comment = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_comments WHERE `id`=".$db->sql_prepare($_GET['com']));

			if (($comment['fileid'] == $file['id']) AND
				($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanageallcomments']) OR 
				(($comment['authorid'] == $userinfo['userid']) AND ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanageowncomments'])))
			{
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_comments WHERE `id`=".$db->sql_prepare($comment['id']));
				$db->query_write("UPDATE " . TABLE_PREFIX . "user SET `comments`=`comments`-1 WHERE `userid` = ".$vbulletin->userinfo['userid']);
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `comments`=`comments`-1");
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `comments`=`comments`-1 WHERE `id` = ".$file['id']);
				eval(print_standard_redirect('ecdownloads_msg_comment_deleted', true, true));
			}
		}
	}

	if ($dl->allowimages)
	{
		if (($_FILES['image']['name'] != '') AND 
			(($permissions[ecdownloadpermissions] & $vbulletin->bf_ugp[ecdownloadpermissions][canuploadimages] AND ($file['uploaderid'] == $vbulletin->userinfo['userid'])) OR
			($permissions[ecdownloadpermissions] & $vbulletin->bf_ugp[ecdownloadpermissions][canuploadimages] AND $permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles'])))
		{
			for ($i = 1; $i <= 3; $i++)
			{
				switch(rand(1,3))
				{
					case 1: $random.=chr(mt_rand(48,57)); break;  // 0-9
					case 2: $random.=chr(mt_rand(65,90)); break;  // A-Z
					case 3: $random.=chr(mt_rand(97,122)); break; // a-z
				}
			}
			$dot = strrpos($_FILES['image']['name'], '.');
			$name = strtolower(substr($_FILES['image']['name'], 0, $dot));
			$ext = strtolower(substr($_FILES['image']['name'], $dot+1));
			if (strpos($dl->ixt, $ext) === false)
			{
				$forceredirect = true;
				eval(print_standard_redirect('ecdownloads_msg_ext_invalid', true, true));
			}
			else
			{
				$forceredirect = true;
				$newfilename = $name.'_'.$random.'.'.$ext;
				move_uploaded_file($_FILES['image']['tmp_name'], $dl->url.$newfilename);
				chmod($dl->url.$newfilename, 0666);
				$thumb = $name.'_'.$random.'_thumb.'.$ext;
				if (($ext == 'jpg') OR ($ext == 'jpeg'))
				{
					$orig_image = imagecreatefromjpeg($dl->url.$newfilename);
				}
				else if ($ext == 'png')
				{
					$orig_image = imagecreatefrompng($dl->url.$newfilename);
				}
				else if ($ext == 'gif')
				{
					$orig_image = imagecreatefromgif($dl->url.$newfilename);
				}
				else if ($ext == 'bmp')
				{
					$orig_image = imagecreatefrombmp($dl->url.$newfilename);
				}
				
				list($width, $height, $type, $attr) = getimagesize($dl->url.$newfilename);
				if ($width > 100)
				{
					$ratio = 100 / $width;
					$newheight = $ratio * $height;
				}
				else
				{
					$newheight = $height;
				}
				$destimg = @imagecreatetruecolor(100,$newheight);
				imagecopyresampled($destimg,$orig_image,0,0,0,0,100,$newheight,imagesx($orig_image),imagesy($orig_image));

				if (($ext == 'jpg') OR ($ext == 'jpeg'))
				{
					@imagejpeg($destimg,$dl->url.$thumb);
				}
				else if ($ext == 'png')
				{
					@imagepng($destimg,$dl->url.$thumb);
				}
				else if ($ext == 'gif')
				{
					@imagegif($destimg,$dl->url.$thumb);
				}
				else if ($ext == 'bmp')
				{
					@imagebmp($destimg,$dl->url.$thumb);
				}
				@imagedestroy($destimg);

				$db->query_write("INSERT INTO " . TABLE_PREFIX . "dl_images (`file`,`name`,`thumb`,`uploader`,`uploaderid`,`date`) VALUES(".$file['id'].",".$db->sql_prepare($newfilename).",".$db->sql_prepare($thumb).",".$db->sql_prepare($vbulletin->userinfo['username']).",".$vbulletin->userinfo['userid'].",".$db->sql_prepare(TIMENOW).")");
				eval(print_standard_redirect('ecdownloads_msg_image_added', true, true));
			}
		}


		if ($_GET['act'] == 'delimg')
		{
			$image = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_images WHERE `id` = ".$db->sql_prepare($_GET['img']));
			if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles']) OR
			   (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditownfiles']) AND
				 (($image['uploaderid'] == $vbulletin->userinfo['userid']) AND ($file['uploaderid'] == $vbulletin->userinfo['userid']))))
			{
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_images WHERE `id` = ".$db->sql_prepare($image['id']));
				@unlink($dl->url.$image['name']);
				@unlink($dl->url.$image['thumb']);
				eval(print_standard_redirect('ecdownloads_msg_image_deleted', true, true));
			}
		}
	}

	$date = vbdate($vbulletin->options['dateformat'], $file['date'], true);
	if ($file['rating'] <= 0)	$grade = $vbphrase['ecdownloads_not_rated'];
		else if ($file['rating'] > 9.6) $grade = "A+"; else if ($file['rating'] > 9.3) $grade = "A"; else if ($file['rating'] > 8.9) $grade = "A-";
		else if ($file['rating'] > 8.6) $grade = "B+"; else if ($file['rating'] > 8.3) $grade = "B"; else if ($file['rating'] > 7.9) $grade = "B-";
		else if ($file['rating'] > 7.6) $grade = "C+"; else if ($file['rating'] > 7.3) $grade = "C"; else if ($file['rating'] > 6.9) $grade = "C-";
		else if ($file['rating'] > 6.6) $grade = "D+"; else if ($file['rating'] > 6.3) $grade = "D"; else if ($file['rating'] > 5.9) $grade = "D-";
		else $grade = "F";
		
	$temp = $db->query_first("SELECT `value` FROM " . TABLE_PREFIX . "dl_votes WHERE `file` = ".$file['id']." AND `user` = ".$vbulletin->userinfo['userid']);
	$urating = $temp['value'];
	if ($urating > 0)
	{
		if ($urating > 9.6) $ugrade = "A+"; else if ($urating > 9.3) $ugrade = "A"; else if ($urating > 8.9) $ugrade = "A-";
			else if ($urating > 8.6) $ugrade = "B+"; else if ($urating > 8.3) $ugrade = "B"; else if ($urating > 7.9) $ugrade = "B-";
			else if ($urating > 7.6) $ugrade = "C+"; else if ($urating > 7.3) $ugrade = "C"; else if ($urating > 6.9) $ugrade = "C-";
			else if ($urating > 6.6) $ugrade = "D+"; else if ($urating > 6.3) $ugrade = "D"; else if ($urating > 5.9) $ugrade = "D-";
			else $ugrade = "F";
		$userscore = $vbphrase['ecdownloads_your_grade'].': '.$ugrade;
	} 
	else 
	{
		if ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canratefiles'])
		{
			$gradeArray = array(
									"10" => "A+", "9.5" => "A", "9.3" => "A-",
									"8.7" => "B+", "8.5" => "B", "8.3" => "B-",
									"7.7" => "C+", "7.5" => "C", "7.3" => "C-",
									"6.7" => "D+", "6.5" => "D", "6.3" => "D-",
									"5.0" => "F"
								);
								
			foreach ($gradeArray AS $key => $value)
			{
				$optionString .= '<option value="./downloads.php?do=file&amp;id='.$file['id'].'&amp;rating='.$key.'">'.$value.'</option>';
			}
			$userscore = '<form name="form" enctype="multipart/form-data" action="./downloads.php?" method="post">
							<select name="grade" onchange="MM_jumpMenu(\'parent\',this,1)">
								<option value="">'.$vbphrase['ecdownloads_rate_file'].'</option>
								'.$optionString.'
							</select>
						  </form>';
		}
		else
		{
			$userscore = $vbdownloads['ecdownloads_cant_rate'];
		}
	}

	if ($file['size'] == 0)
	{
		$size = $vbphrase['ecdownloads_unknown_size'];
	}
	else
	{
		$size = vb_number_format($file['size'], 0, true);
	}
	
	$downloads = vb_number_format($file['downloads']);
	
	if (($file['purgatory'] == 0) OR ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanagepurgatory']))
	{
		if ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['candownloadfiles'])
		{
			if ($file['link'] == 1)
			{
				$target = 'target="_blank"';
			}
			else
			{
				$target = 'target="_self"';
			}

				$download = '<a href="./downloads.php?do=file&amp;id='.$file['id'].'&amp;act=down" '.$target.'><img src="'.$stylevar[imgdir_button].'/'.$vbphrase['ecdownloads_download_pic'].'" width="15" height="15" align="middle" title="'.$vbphrase['ecdownloads_download'].'&nbsp;'.$file['name'].'" alt="'.$vbphrase['ecdownloads_downloads'].'" border="0" /></a>&nbsp;';

				$download .= '[<a href="./downloads.php?do=file&amp;id='.$file['id'].'&amp;act=down" '.$target.' title="'.$vbphrase['ecdownloads_download'].'&nbsp;'.$file['name'].'">'.$vbphrase['ecdownloads_download'].'&nbsp;'.$file['name'].'</a>]';

			if (($file['purgatory'] == 1) AND ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanagepurgatory']))
			{
				$download = $vbphrase['ecdownloads_file_in_purgatory'].': '.$download;
			}
		}
		else
		{
			$download = $vbphrase['ecdownloads_no_permission_download'];
		}
	}
	else
	{
		$download = $vbphrase['ecdownloads_file_in_purgatory'];
	}

	$parsed_text = $parser->do_parse($file['description'], false, true, true, true, true, $cachable);
	$pagedata = $parsed_text;
	$description = $pagedata;

	if ($dl->allowimages)
	{
		$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_images WHERE `file` = ".$file['id']);
		if ($db->num_rows($result) > 0)
		{
			while ($image = $db->fetch_array($result))
			{
				if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles']) OR
				   (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditownfiles']) AND
					(($image['uploaderid'] == $vbulletin->userinfo['userid']) AND ($file['uploaderid'] == $vbulletin->userinfo['userid']))))
					$edit = '[<a href="./downloads.php?do=file&amp;id='.$file['id'].'&amp;act=delimg&amp;img='.$image['id'].'" onclick="return delete_it()">'.$vbphrase['delete'].'</a>]';
				{
					if (file_exists($dl->url.$image['thumb']))
					{
						$dimages .= '<a href="'.$dl->url.$image['name'].'"><img src="'.$dl->url.$image['thumb'].'" alt="'.$file['name'].'" title="'.$file['name'].'" border="0" /></a> by <a href="./member.php?u='.$image['uploaderid'].'">'.$image['uploader'].'</a> on '.vbdate($vbulletin->options['dateformat'], $image['date'], true).' '.$edit.'<br />';
					}
					else
					{
						$dimages .= '<a href="'.$dl->url.$image['name'].'">'.$image['name'].'</a> by <a href="./member.php?u='.$image['uploaderid'].'">'.$image['uploader'].'</a> on '.vbdate($vbulletin->options['dateformat'], $image['date'], true).' '.$edit.'<br />';
					}
				}
			}
		}
		else
		{
			$dimages = $vbphrase['ecdownloads_none'];
		}
	}

	if ($file['_author'] != '')
	{
		$_author = $file['_author'];
	}
	else if (($file['_author'] == '') AND ($file['author'] != ''))
	{
		$_author = $file['author'];
	}
	else
	{
		$_author = $vbphrase['ecdownloads_unknown'];
	}

	$category_array = $dl->construct_select_array(0, array('#' => $vbphrase['ecdownloads_category_jump']), '');
	foreach ($category_array AS $cat_key => $cat_value)
	{
		$category_jump .= '<option value="./downloads.php?do=cat&amp;id='.$cat_key.'">'.$cat_value.'</option>';
	}

	if ($dl->allowcomments)
	{
		$textareacols = fetch_textarea_width();
		$editorid = construct_edit_toolbar('', 0, 'nonforum', iif($vbulletin->options['privallowsmilies'], 1, 0));

		$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

		sanitize_pageresults($file['comments'], $pagenumber, $dl->perpage, $dl->perpage, $dl->perpage);

		$limit = (($pagenumber -1)*$dl->perpage);
		$navigation = construct_page_nav($pagenumber, $dl->perpage, $file['comments'], "downloads.php?" . $vbulletin->session->vars['sessionurl'] . "do=file&amp;id=$cleanfileid");

		$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_comments WHERE `fileid` = ".$file['id']." LIMIT ".$limit.",".$dl->perpage);
		while ($comment = $db->fetch_array($result))
		{
			$comment['date'] = vbdate($vbulletin->options['dateformat'], $comment['date'], true)." at ".vbdate($vbulletin->options['timeformat'], $comment['date'], true);
			$comment['message'] = $parser->do_parse($comment['message'], false, true, true, true, true, $cachable);
			if ((($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanageowncomments']) AND ($comment['authorid'] == $vbulletin->userinfo['userid'])) OR
				($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanageallcomments']))
			{
				$canedit = true;
			}
			else
			{
				$canedit = false;
			}
			eval('$comments .= "' . fetch_template('downloads_file_comment') . '";');
		}
		$db->free_result($result);
	}

	eval('$dmain_jr = "' . fetch_template('downloads_file') . '";');
	if ($vbulletin->options['ecdownloads_tops'])
	{
		eval('$dpanel = "' . fetch_template('downloads_panel_side') . '";');
		eval('$dmain = "' . fetch_template('downloads_wrapper_side') . '";');
	}
	else
	{
		eval('$dmain .= "' . fetch_template('downloads_wrapper_top') . '";');
	}
}
else if ($_GET['do'] == 'assignuser')
{
	$navbits['downloads.php?do=assignuser'] = $vbphrase['ecdownloads_assign_new_uploader'];
	$dlcustomtitle = $vbphrase['ecdownloads_assign_new_uploader'];

	$cleanfileid = $vbulletin->input->clean_gpc('r', 'id', TYPE_UINT);

	$filesexclude = $dl->exclude_files();

	$file = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE ".$filesexclude." `id` = ".$cleanfileid);

	if (!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles']) AND
	   (!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditownfiles']) OR
	   ($file['uploaderid'] != $vbulletin->userinfo['userid'])))
	{
		print_no_permission();
	}

	if ($file['id'] == 0)
	{
		eval(print_standard_redirect('ecdownloads_msg_invalid_file', true, true));
	}

	if ($_GET['act'] == 'update')
	{
		$currentuser = $db->query_first("SELECT uploaderid
									   FROM " . TABLE_PREFIX . "dl_files
									   WHERE id = ".$file['id']);

		$temp = $db->query_first("SELECT username, userid 
									   FROM " . TABLE_PREFIX . "user
									   WHERE username = ".$db->sql_prepare($_POST['author']));

		if ($temp['username'] == '')
		{
			$vbulletin->url = './downloads.php?do=assignuser&id='.$file['id'];
			eval(print_standard_redirect('ecdownloads_no_such_user', true, true));
		}
		else
		{
			// Found user, update dl_files record
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files 
							SET `uploader`=".$db->sql_prepare($temp['username']).", uploaderid = ".$temp['userid']."
							WHERE id = ".$file['id']);
			$dl->modify_filecount_user($temp['userid']);
			$dl->modify_filecount_user($currentuser['uploaderid']);
			$dl->update_counters();
		}

		$db->free_result($temp);
		$vbulletin->url = './downloads.php?do=file&id='.$file['id'];
		eval(print_standard_redirect('ecdownloads_uploader_updated', true, true));
	}

	eval('$dmain_jr .= "' . fetch_template('downloadsbuddy_assign_user') . '";');
	// eval('$dpanel .= "' . fetch_template('downloads_panel_top') . '";');
	eval('$dmain .= "' . fetch_template('downloads_wrapper_top') . '";');
}
else if ($_GET['do'] == 'add' OR $_GET['do'] == 'edit')
{
	$textareacols = fetch_textarea_width();

	if ($_GET['do'] == 'add')
	{
		$navbits['downloads.php?do=add'] = $vbphrase['ecdownloads_addit_file'];
		$dlcustomtitle = $vbphrase['ecdownloads_addit_file'];
		if (!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canuploadfiles']) AND
			!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canlinktofiles']))
		{
			print_no_permission();
		}
	}
	else if ($_GET['do'] == 'edit')
	{
		$cleanfileid = $vbulletin->input->clean_gpc('r', 'id', TYPE_UINT);
		$file = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE `id`=".$cleanfileid);
		if ($file['id'] == 0)
		{
			eval(print_standard_redirect('ecdownloads_msg_invalid_file', true, true));
		}
			
		$navbits['downloads.php?do=edit'] = $vbphrase['ecdownloads_edit_file'];
		$dlcustomtitle = $vbphrase['ecdownloads_edit_file'];
		if (!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles']) AND
		   (!($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditownfiles']) OR
		   ($file['uploaderid'] != $vbulletin->userinfo['userid'])))
		{
			print_no_permission();
		}
	}

	if ($_POST['submit'] != '')
	{
			// hook for pre-upload checks
			($hook = vBulletinHook::fetch_hook('dl_pre_upload')) ? eval($hook) : false;
		
		
		// check if the users has exceeded the max daily download amount
		if ($permissions['ecdownloadsmaxuploadtotal'] >= 0 AND @filesize($_FILES['upload']['tmp_name']) > 0)
		{
			$vbulletin->url = './downloads.php?do=null';

			// max is set to zero so ... no downloads unless override is set for usergroup
			if (($permissions['ecdownloadsmaxuploadtotal'] == 0))
			{
				eval(print_standard_redirect('ecdownloads_upload_amount_exceeded', true, true));
			}

			// check amount downloaded against maxdaily
			if ($permissions['ecdownloadsmaxuploadtotal'] > 0)
			{
				$tempnew = $db->query_first("SELECT SUM(size) as uploadedsize FROM " . TABLE_PREFIX . "dl_files WHERE `uploaderid` = ".$vbulletin->userinfo['userid']);

				$size = @filesize($_FILES['upload']['tmp_name']);
				$dlremaining = $permissions['ecdownloadsmaxuploadtotal'] * 1048576 - ($tempnew['uploadedsize'] + $size);
				$db->free_result($tempnew);

				if ($dlremaining < 0)
				{
					eval(print_standard_redirect('ecdownloads_upload_amount_will_be_exceeded', true, true));
				}
			}
		}

		$_POST['dname'] = strip_tags($_POST['dname']);
		$_POST['author'] = strip_tags($_POST['author']);

		if ($_POST['dname'] == '')
		{
			$errors['name'] = 'color="#FF0000"';
		}
		if ($_POST['author'] == '')
            ;//$_POST['author'] = '';
		else
		{
			$authors = explode(";",$_POST['author']);
			foreach ($authors AS $key => $value)
			{
				$author = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE `username`=".$db->sql_prepare(trim($value)));
				if ($author['userid'] > 0)
				{
					$authors[$key] = '<a href="member.php?u='.$author['userid'].'">'.trim($value).'</a>';
				}
				else
				{
					$authors[$key] = trim($value);
					if ($authors[$key] == '')
					{
						unset($authors[$key]);
					}
				}
				$_author = implode(", ",$authors);
			}
		}
		//if ($_POST['message'] == '')
		//{
			//$errors['desc'] = 'color="#FF0000"';
		//}
		if ($_POST['category'] == '')
		{
			$errors['category'] = 'color="#FF0000"';
		}

		if (isset($errors))
		{
			$errors['message'] .= '<center>'.$vbphrase['ecdownloads_fill_in_fields'].'</center><br />';
		}
		
		$ext = '';
		if ($_FILES['upload']['name'] != '' AND ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canuploadfiles']))
		{
			$_POST['link'] = '';
			$link = false;
			$upload = true;
			$ext = strtolower(substr($_FILES['upload']['name'], strrpos($_FILES['upload']['name'], '.')+1));
		}
		else if ($_POST['link'] != '' AND ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canlinktofiles']))
		{
			$link = true;
			$upload = false;
			$ext = strtolower(substr($_POST['link'], strrpos($_POST['link'], '.')+1));
		}
		else if ($_GET['do'] != 'edit')
		{
			$errors['upload'] = 'color=#FF0000"';
			//$errors['link'] = 'color=#FF0000"';
			$errors['message'] .= '<center>'.$vbphrase['ecdownloads_must_submit_file'].'</center><br />';
		}

		if ($upload == true)
		{
			if (!strstr("|".str_replace(" ","|",$dl->ext)."|",$ext))
			{
				$errors['message'] .= '<center>'.$vbphrase['ecdownloads_invalid_extension'].': '.$dl->ext.'</center><br />';
			}
		}

		if (!isset($errors))
		{
			$_POST['desc'] = $_POST['message'];

			if ($_POST['wysiwyg'] == 1)
			{
				$_POST['desc'] = convert_wysiwyg_html_to_bbcode($_POST['message'], 0);
			}
			else
			{
			 	$_POST['desc'] = &$_POST['message'];
		 	}

			$_POST['desc'] = convert_url_to_bbcode($_POST['desc']);

			if ($upload)
			{
				$newfilename = (TIMENOW%100000).'-'.$_FILES['upload']['name'];
				if (move_uploaded_file($_FILES['upload']['tmp_name'], $dl->url.$newfilename))
				{
					chmod($dl->url.$newfilename, 0666);
					$size = @filesize($dl->url.$newfilename);
				} 
				else 
				{
					$errors['message'] .= '<center><span style="color: red;">The upload failed!  Upload error.</span></center><br />';
				}
			}
			else if ($link)
			{
				$newfilename = $_POST['link'];

				if ($_POST['size'] == '')
				{
					$size = @filesize($newfilename);
				} 
				else 
				{
					if (is_numeric($_POST['size']))
					{
						$size = $_POST['size'];
					}
					else
					{
						$size = 0;
					}
				}

				// check for http on beginning of link or d/l won't work
				if (strpos($newfilename, "http://") === false AND strpos($newfilename, "ftp://") === false)
				{
					$newfilename = "http://".$newfilename;
				}
				// end of http check
			}
			else if ($_GET['do'] == 'edit')
			{
				$newfilename = $file['url'];
				$size = $file['size'];
				$link = $file['link'];
			}

			if ($_GET['do'] == 'add' AND !isset($errors))
			{
				$result = $db->query_write("INSERT INTO " . TABLE_PREFIX . "dl_files (`name`, `description`, `author`, `_author`, `uploader`, `uploaderid`, `url`, `date`, `category`, `size`, `pin`, `purgatory`, `link`)
											VALUES(".
												$db->sql_prepare($_POST['dname']).", ".
												$db->sql_prepare($_POST['desc']).", ".
												$db->sql_prepare($_POST['author']).", ".
												$db->sql_prepare($_author).", ".
												$db->sql_prepare($vbulletin->userinfo['username']).", ".
												$db->sql_prepare($vbulletin->userinfo['userid']).", ".
												$db->sql_prepare($newfilename).", ".
												$db->sql_prepare(TIMENOW).", ".
												$db->sql_prepare($_POST['category']).", ".
												$db->sql_prepare($size).", ".
												$db->sql_prepare($_POST['pin']).", ".
												$db->sql_prepare($_POST['purgatory']).", ".
												$db->sql_prepare($link).
											")"
										);
			}
			else if ($_GET['do'] == 'edit')
			{
				$result = $db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `name`=".$db->sql_prepare($_POST['dname']).
												",`description`=".$db->sql_prepare($_POST['desc']).
												",`author`=".$db->sql_prepare($_POST['author']).
												",`_author`=".$db->sql_prepare($_author).
												",`url`=".$db->sql_prepare($newfilename).
												",`category`=".$db->sql_prepare($_POST['category']).
												",`size`=".$db->sql_prepare($size).
												",`pin`=".$db->sql_prepare($_POST['pin']).
												",`purgatory`=".$db->sql_prepare($_POST['purgatory']).
												",`link`=".$db->sql_prepare($link)
												." WHERE `id`=".$db->sql_prepare($_GET['id']));
			}
			
			

			if ($_GET['do'] == 'add' AND !isset($errors))
			{
				$id = $db->insert_id();
				$temp = $db->query_first("SELECT COUNT(*) AS `uploads` FROM " . TABLE_PREFIX . "dl_files WHERE `uploaderid` = ".$vbulletin->userinfo['userid']);
				$db->query_write("UPDATE " . TABLE_PREFIX . "user SET `uploads`=".$db->sql_prepare($temp['uploads'])." WHERE `userid` = ".$vbulletin->userinfo['userid']);
				$temp = $db->query_first("SELECT COUNT(*) AS `files` FROM " . TABLE_PREFIX . "dl_files");
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `files`=".$db->sql_prepare($temp['files']));
				$dl->modify_filecount($_POST['category'], 1);
				$dl->update_counters();
				$vbulletin->url = './downloads.php?do=file&id='.$id;
				// hook for post-upload checks
				($hook = vBulletinHook::fetch_hook('dl_post_upload_add')) ? eval($hook) : false;
				eval(print_standard_redirect('ecdownloads_msg_file_added', true, true));
			}
			else
			{
				if (!isset($errors))
				{
					if (!isset($upload))
					{
						rename($dl->url.$file['url'], $dl->url.$newfilename);
					}
					if ($file['category'] != $_POST['category'])
					{
						$dl->modify_filecount($_POST['category'], 1);
						$dl->modify_filecount_delete($file['category'], -1);
					}
					$dl->update_counters();
					$vbulletin->url = 'downloads.php?do=file&id='.$_GET['id'];
					// hook for post-upload checks
					($hook = vBulletinHook::fetch_hook('dl_post_upload_edit')) ? eval($hook) : false;
					eval(print_standard_redirect('ecdownloads_msg_file_edited', true, true));
				}
			}

		}
	}

	if (($_GET['do'] == 'edit') AND isset($file))
	{
		$_POST = $file;
		$_POST['dname'] = $file['name'];
		$_POST['url'] = $vbulletin->input->clean($file['url'], TYPE_NOHTML);
	}

	if ($_POST['pin'] == 1)
	{
		$pinned = 'selected="selected"';
		$unpinned = '';
	}
	else
	{
		$pinned = '';
		$unpinned = 'selected="selected"';
	}
	// Get the message editor for the description
	$editorid = construct_edit_toolbar(htmlspecialchars_uni($file['description']), 0, 'nonforum', iif($vbulletin->options['privallowsmilies'], 1, 0));

	$category_array = $dl->construct_select_array(0, array('' => '----------'), '');
	foreach ($category_array AS $cat_key => $cat_value)
	{
		if (($_POST['category'] == $cat_key) OR ($_GET['cat'] == $cat_key))
		{
			$selected = 'selected="selected"';
		}
		else
		{
			$selected = '';
		}
		$category_select .= '<option value="'.$cat_key.'" '.$selected.'>'.$cat_value.'</option>';
	}

	eval('$dmain_jr = "' . fetch_template('downloads_file_addit') . '";');
	eval('$dmain .= "' . fetch_template('downloads_wrapper_top') . '";');
}
else if ($_GET['do'] == 'manfiles')
{
	$navbits['downloads.php?do=manfiles'] = $vbphrase['ecdownloads_manage_files'];
	$dlcustomtitle = $vbphrase['ecdownloads_manage_files'];

	if ($_GET['act'] == 'updatecounters')
	{
		$dl->update_counters_all();
		$vbulletin->url = './downloads.php?do=manfiles';
		eval(print_standard_redirect('ecdownloads_msg_counters_updated', true, true));
	}

	// check for category permissions
	$filesexclude = $dl->exclude_files();

	$file = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE ".$filesexclude." `id`=".$db->sql_prepare($_GET['id']));
	if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditallfiles']) OR
	   (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['caneditownfiles']) AND
	   ($file['uploaderid'] == $vbulletin->userinfo['userid'])))
	{
		$showedit = true;
	}
	if ($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['canmanagepurgatory'])
	{
		$showapprove = true;
	}
	if (!$showedit AND !$showapprove)
	{
		print_no_permission();
	}

	if ($_GET['redir'] == 'manfiles')
	{
		if ($_GET['category'] != '')
		{
			$_GET['category'] = $vbulletin->input->clean_gpc('r', 'category', TYPE_UINT);
		}
		if ($_GET['pin'] != '')
		{
			$_GET['pin'] = $vbulletin->input->clean_gpc('r', 'pin', TYPE_UINT);
		}
		if ($_GET['approval'] != '')
		{
			$_GET['approval'] = $vbulletin->input->clean_gpc('r', 'approval', TYPE_UINT);
		}
		$_GET['page'] = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);
		$vbulletin->url = './downloads.php?do=manfiles&category='.$_GET['category'].'&pin='.$_GET['pin'].'&approval='.$_GET['approval'].'&page='.$_GET['page'];
	}
	else if ($_GET['redir'] == 'file')
	{
		$cleanfileid = $vbulletin->input->clean_gpc('r', 'id', TYPE_UINT);
		$vbulletin->url = './downloads.php?do=file&id='.$cleanfileid;
	}
	else
	{
		$vbulletin->url = './downloads.php?';
	}

	if ($_GET['act'] == '')
	{
	}
	else if ($_GET['act'] == 'approve' AND $showapprove)
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `purgatory`='0' WHERE `id`=".$db->sql_prepare($_GET['id']));
		$dl->update_counters();
		eval(print_standard_redirect('ecdownloads_msg_file_approved', true, true));
	}
	else if ($_GET['act'] == 'unapprove' AND $showapprove)
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `purgatory`='1' WHERE `id`=".$db->sql_prepare($_GET['id']));
		$dl->update_counters();
		eval(print_standard_redirect('ecdownloads_msg_file_unapproved', true, true));
	}
	else if ($_GET['act'] == 'pin' AND $showapprove)
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `pin`='1' WHERE `id`=".$db->sql_prepare($_GET['id']));
		eval(print_standard_redirect('ecdownloads_msg_file_pinned', true, true));
	}
	else if ($_GET['act'] == 'unpin' AND $showapprove)
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `pin`='0' WHERE `id`=".$db->sql_prepare($_GET['id']));
		eval(print_standard_redirect('ecdownloads_msg_file_unpinned', true, true));
	}
	else if ($_GET['act'] == 'delete' AND $showedit)
	{
		$cleanfileid = $vbulletin->input->clean_gpc('r', 'id', TYPE_UINT);

		$file = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE `id` = ".$cleanfileid);
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_files WHERE `id` = ".$cleanfileid);
		if (!$file['link'])
		{
			@unlink($dl->url.$file['url']);
		}

		$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_images WHERE `file` = ".$cleanfileid);
		while ($image = $db->fetch_array($result))
		{
			@unlink($dl->url.$image['name']);
			@unlink($dl->url.$image['thumb']);
		}
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_images WHERE `file` = ".$cleanfileid);
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_comments WHERE `fileid` = ".$cleanfileid);
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_votes WHERE `file` = ".$cleanfileid);

		$dl->modify_filecount_user($file['uploaderid']);
		$dl->update_counters();
		$dl->modify_filecount_delete($file['category'], -1);

		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `files`=`files`-1");
		eval(print_standard_redirect('ecdownloads_msg_file_deleted', true, true));
	}
	else if ($_GET['act'] == 'mass' AND ($showedit OR $showapprove) AND $_POST['id'])
	{
		if ($_POST['task'] == 'approve' AND $showapprove)
		{
			foreach ($_POST['id'] AS $id => $value)
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `purgatory`='0' WHERE `id` = ".$db->sql_prepare($id));
			}
			$dl->update_counters();
			eval(print_standard_redirect('ecdownloads_msg_file_approved', true, true));
		}
		else if ($_POST['task'] == 'unapprove' AND $showapprove)
		{
			foreach ($_POST['id'] AS $id => $value)
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `purgatory`='1' WHERE `id` = ".$db->sql_prepare($id));
			}
			$dl->update_counters();
			eval(print_standard_redirect('ecdownloads_msg_file_unapproved', true, true));
		}
		else if ($_POST['task'] == 'pin' AND $showapprove)
		{
			foreach ($_POST['id'] AS $id => $value)
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `pin`='1' WHERE `id` = ".$db->sql_prepare($id));
			}
			eval(print_standard_redirect('ecdownloads_msg_file_pinned', true, true));
		}
		else if ($_POST['task'] == 'unpin' AND $showapprove)
		{
			foreach ($_POST['id'] AS $id => $value)
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `pin`='0' WHERE `id` = ".$db->sql_prepare($id));
			}
			eval(print_standard_redirect('ecdownloads_msg_file_unpinned', true, true));
		}
		else if ($_POST['task'] == 'delete' AND $showedit)
		{
			foreach ($_POST['id'] AS $id => $value)
			{
				$file = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE `id`=".$db->sql_prepare($id));
				if ($file['id'] > 0)
				{
					$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_files WHERE `id` = ".$db->sql_prepare($id));
					if (!$file['link'])
					{
						@unlink($dl->url.$file['url']);
					}

					$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_images WHERE `file` = ".$db->sql_prepare($_GET['id']));
					while ($image = $db->fetch_array($result))
					{
						@unlink($dl->url.$image['name']);
						@unlink($dl->url.$image['name']);
					}
					$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_images WHERE `file` = ".$db->sql_prepare($_GET['id']));
					$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_comments WHERE `fileid` = ".$db->sql_prepare($_GET['id']));
					$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_votes WHERE `file` = ".$db->sql_prepare($_GET['id']));

					$dl->modify_filecount_user($file['uploaderid']);
					$dl->modify_filecount($file['category'],-1);
				}
				else
				{
					unset($_POST[$id]);
				}
			}
			$dl->update_counters();
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `downloads`=`downloads`-".$db->sql_prepare(sizeof($_POST['id'])));
			eval(print_standard_redirect('ecdownloads_msg_file_deleted', true, true));
		}
		else if ($_POST['task'] == 'move' AND $showedit)
		{
			$cat = $db->query_first("SELECT `abbr` FROM " . TABLE_PREFIX . "dl_cats WHERE `id`=".$db->sql_prepare($_POST['category']));
			foreach ($_POST['id'] AS $id => $value)
			{
				$file = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE `id`=".$db->sql_prepare($id));
				if ($file['id'] > 0)
				{
					$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `category`=".$db->sql_prepare($_POST['category'])." WHERE `id` = ".$db->sql_prepare($id));
					$dl->modify_filecount($file['category'],-1);
				}
				else
				{
					unset($_POST[$id]);
				}
			}
			$dl->modify_filecount($_POST['category'],sizeof($_POST['id']));
			eval(print_standard_redirect('ecdownloads_msg_file_moved', true, true));
		}
	}

	$category_array = $dl->construct_select_array(0, array('' => '['.$vbphrase['ecdownloads_category'].']'), '');
	foreach ($category_array AS $cat_key => $cat_value)
	{
		$category_select .= '<option value="'.$cat_key.'">'.$cat_value.'</option>';
	}

	$params = '&amp;redir=manfiles';

	if ($_GET['category'] != '' AND $_GET['category'] != 0)
	{
		$cleancatid = $vbulletin->input->clean_gpc('r', 'category', TYPE_UINT);
		$category = 'category = '.$cleancatid;
		$params .= '&amp;category='.$cleancatid;
	}
	else
	{
		$category = 'category != '.$db->sql_prepare(-1);
	}

	if ($_GET['pin'] == '0')
	{
		$pin = ' AND pin = '.$db->sql_prepare(0);
		$params .= '&amp;pin=0';
	}
	else if ($_GET['pin'] == '1')
	{
		$pin = ' AND pin = '.$db->sql_prepare(1);
		$params .= '&amp;pin=1';
	}
	else
	{
		$pin = '';
	}

	if ($_GET['approval'] == '0')
	{
		$approval = ' AND purgatory = '.$db->sql_prepare(1);
		$params .= '&amp;approval=0';
		$cleanapprove = 0;
	}
	else if ($_GET['approval'] == '1')
	{
		$approval = ' AND purgatory = '.$db->sql_prepare(0);
		$params .= '&amp;approval=1';
		$cleanapprove = 1;
	}
	else
	{
		$approval = '';
		$cleanapprove = 1;
	}
		
	$temp = $db->query_first("SELECT COUNT(*) AS files FROM " . TABLE_PREFIX . "dl_files WHERE ".$filesexclude." ".$category.$pin.$approval);

	$cleanpin = $vbulletin->input->clean_gpc('r', 'pin', TYPE_UINT);
	$cleancatid = $vbulletin->input->clean_gpc('r', 'category', TYPE_UINT);
	// $cleanapprove = $vbulletin->input->clean_gpc('r', 'approval', TYPE_UINT);
	$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

	sanitize_pageresults($temp['files'], $pagenumber, $dl->perpage, $dl->perpage, $dl->perpage);

	$limit = (($pagenumber -1)*$dl->perpage);
	$navigation = construct_page_nav($pagenumber, $dl->perpage, $temp['files'], "downloads.php?" . $vbulletin->session->vars['sessionurl'] . "do=manfiles&amp;pin=$cleanpin&amp;approval=$cleanapprove", ""
		. (!empty($cleancatid) ? "&amp;category=$cleancatid" : "")
	);

	$params .= '&amp;page='.$pagenumber;
	
	$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE ".$filesexclude." ".$category.$pin.$approval." ORDER BY `id` DESC LIMIT ".$db->sql_prepare($limit).",".$dl->perpage);
	if ($db->num_rows($result) > 0)
	{
		while ($file = $db->fetch_array($result))
		{
			exec_switch_bg();
			if ($file['purgatory'] == 0)
			{
				$info = ' <span style="color: blue;">'.$vbphrase['ecdownloads_approved'].'</span>';
			}
			else
			{
				$info = ' <span style="color: red;">'.$vbphrase['ecdownloads_unapproved'].'</span>';
			}
			if ($file['pin'] == 1)
			{
				$info .= ', '.$vbphrase['ecdownloads_pinned'].'';
			}

			if (($_GET['act'] != 'nolink') OR !file_exists($dl->url.$file['url']))
			{
				eval('$dfilebits .= "' . fetch_template('downloads_man_bit') . '";');
			}
		}
	}

	$db->free_result($result);

	eval('$dmain_jr .= "' . fetch_template('downloads_man') . '";');
	if ($vbulletin->options['ecdownloads_tops'])
	{
		eval('$dpanel = "' . fetch_template('downloads_panel_side') . '";');
		eval('$dmain .= "' . fetch_template('downloads_wrapper_side') . '";');
	}
	else
	{
		eval('$dmain .= "' . fetch_template('downloads_wrapper_top') . '";'); 
	}
}
else if ($_GET['do'] == 'stats')
{
	$navbits['downloads.php?do=stats'] = $vbphrase['ecdownloads_stats'];
	$dlcustomtitle = $vbphrase['ecdownloads_stats'];

	$temp = $db->query_first("SELECT COUNT(*) AS days FROM " . TABLE_PREFIX . "dl_stats");

	$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

	sanitize_pageresults($temp['days'], $pagenumber, $dl->perpage, $dl->perpage, $dl->perpage);

	$limit = (($pagenumber -1)*$dl->perpage);
	$navigation = construct_page_nav($pagenumber, $dl->perpage, $temp['days'], 'downloads.php?' . $vbulletin->session->vars['sessionurl'] . 'do=stats');

	$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_stats ORDER BY `day` DESC LIMIT ".$limit.",".$dl->perpage);
	while ($stat = $db->fetch_array($result))
	{
		$date = vbdate($vbulletin->options['dateformat'], $stat['day']*86400, true);
		$bandwidth = (int) ($stat['bandwidth']/1000);
		if ($bandwidth == 0)
		{
			$bandwidth = $vbphrase['ecdownloads_unknown'];
		}
		else
		{
			$bandwidth .= ' KB';
		}
		exec_switch_bg();
		eval('$dstatbits .= "' . fetch_template('downloads_stats_bit') . '";');
	}

	eval('$dmain_jr .= "' . fetch_template('downloads_stats') . '";');
	if ($vbulletin->options['ecdownloads_tops'])
	{
	 	eval('$dpanel = "' . fetch_template('downloads_panel_side') . '";');
		eval('$dmain .= "' . fetch_template('downloads_wrapper_side') . '";'); 
	}
	else
	{
		eval('$dmain .= "' . fetch_template('downloads_wrapper_top') . '";');
	}
}
else if ($_GET['do'] == 'search')
{
	$navbits['downloads.php?do=search'] = $vbphrase['ecdownloads_search'];
	$dlcustomtitle = $vbphrase['ecdownloads_search'];

	if ($_POST['query'] != '')
	{
		$keyword = explode(",",$_POST['query']);
		foreach ($keyword AS $text)
		{
			$text = trim($text);
			if (strlen($text) >= 4)
			{
				$query .= " OR " . TABLE_PREFIX . "dl_files.author LIKE ".$dl->like($text).
						  " OR " . TABLE_PREFIX . "dl_files.name LIKE ".$dl->like($text).
						  " OR " . TABLE_PREFIX . "dl_files.description LIKE ".$dl->like($text);
			}
		}

		if (strlen($query) > 0)
		{
			$query = substr($query,4);
		}
		else
		{
			$query = TABLE_PREFIX . "dl_files.id=".$db->sql_prepare(-1);
		}

		// check for category permissions
		$filesexclude = $dl->exclude_files();

		$result = $db->query_read("SELECT " . TABLE_PREFIX . "dl_files.*,  " . TABLE_PREFIX . "dl_cats.name catname FROM " . TABLE_PREFIX . "dl_files, " . TABLE_PREFIX . "dl_cats WHERE ".$filesexclude." (".$query.") 
									AND " . TABLE_PREFIX . "dl_files.category = " . TABLE_PREFIX . "dl_cats.id
									ORDER BY catname, " . TABLE_PREFIX . "dl_files.name");
		if ($db->num_rows($result) > 0)
		{
			while ($file = $db->fetch_array($result))
			{
				$date = vbdate($vbulletin->options['dateformat'], $file['date'], true);

				if ($file['rating'] <= 0)	$grade = $vbphrase['ecdownloads_not_rated'];
					else if ($file['rating'] > 9.6) $grade = "A+"; else if ($file['rating'] > 9.3) $grade = "A"; else if ($file['rating'] > 8.9) $grade = "A-";
					else if ($file['rating'] > 8.6) $grade = "B+"; else if ($file['rating'] > 8.3) $grade = "B"; else if ($file['rating'] > 7.9) $grade = "B-";
					else if ($file['rating'] > 7.6) $grade = "C+"; else if ($file['rating'] > 7.3) $grade = "C"; else if ($file['rating'] > 6.9) $grade = "C-";
					else if ($file['rating'] > 6.6) $grade = "D+"; else if ($file['rating'] > 6.3) $grade = "D"; else if ($file['rating'] > 5.9) $grade = "D-";
					else $grade = "F";
					
			if ($dl->smalldesclen > 0)
			{
				$smalldesc = strip_bbcode($file['description'], false, false, false);
				$smalldesc = substr($smalldesc, 0, $dl->smalldesclen);
				$smalldesc = $vbulletin->input->clean($smalldesc, TYPE_NOHTML);
				$smalldesc = ": ".$smalldesc;
			}

				exec_switch_bg();
				eval('$dresultbits .= "' . fetch_template('downloads_search_result_bit') . '";');
			}
		}
		else
		{
			$dresultbits .= '<tr><td class="alt2" colspan="5" align="center">'.$vbphrase['ecdownloads_no_match'].'</td></tr>';
		}
		eval('$dresult .= "' . fetch_template('downloads_search_result') . '";');
	}
	
	eval('$dmain_jr .= "' . fetch_template('downloads_search') . '";');
	if ($vbulletin->options['ecdownloads_tops'])
	{
	 	eval('$dpanel = "' . fetch_template('downloads_panel_side') . '";');
		eval('$dmain .= "' . fetch_template('downloads_wrapper_side') . '";');
	}
	else
	{
		eval('$dmain .= "' . fetch_template('downloads_wrapper_top') . '";');
	}
}
else if ($_GET['do'] == 'my')
{
	$navbits['downloads.php?do=my'] = $vbphrase['ecdownloads_my_files'];
	$dlcustomtitle = $vbphrase['ecdownloads_my_files'];

	// check for category permissions
	$filesexclude = $dl->exclude_files();

	$temp = $db->query_first("SELECT COUNT(*) AS files FROM " . TABLE_PREFIX . "dl_files WHERE ".$filesexclude." `uploaderid` = ".$vbulletin->userinfo['userid']);

	if ($temp['files'] > 0)
	{
		$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

		sanitize_pageresults($temp['files'], $pagenumber, $dl->perpage, $dl->perpage, $dl->perpage);

		$limit = (($pagenumber -1)*$dl->perpage);
		$navigation = construct_page_nav($pagenumber, $dl->perpage, $temp['files'], 'downloads.php?' . $vbulletin->session->vars['sessionurl'] . 'do=my');

		$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_files WHERE ".$filesexclude." `uploaderid` = ".$vbulletin->userinfo['userid']." LIMIT ".$limit.",".$dl->perpage);
		while ($file = $db->fetch_array($result))
		{
			$date = vbdate($vbulletin->options['dateformat'], $file['date'], true);

			if ($file['rating'] <= 0)	$grade = $vbphrase['ecdownloads_not_rated'];
				else if ($file['rating'] > 9.6) $grade = "A+"; else if ($file['rating'] > 9.3) $grade = "A"; else if ($file['rating'] > 8.9) $grade = "A-";
				else if ($file['rating'] > 8.6) $grade = "B+"; else if ($file['rating'] > 8.3) $grade = "B"; else if ($file['rating'] > 7.9) $grade = "B-";
				else if ($file['rating'] > 7.6) $grade = "C+"; else if ($file['rating'] > 7.3) $grade = "C"; else if ($file['rating'] > 6.9) $grade = "C-";
				else if ($file['rating'] > 6.6) $grade = "D+"; else if ($file['rating'] > 6.3) $grade = "D"; else if ($file['rating'] > 5.9) $grade = "D-";
				else $grade = "F";

			if ($file['purgatory'] == 1)
			{
				$status = $vbphrase['ecdownloads_unapproved'];
			}
			else
			{
				$status = $vbphrase['ecdownloads_approved'];
			}

			exec_switch_bg();
			eval('$dmyfilebits .= "' . fetch_template('downloads_my_bit') . '";');
		}
	}
	else
	{
		$myfilebits .= '<tr><td class="alt2" colspan="6" align="center">'.$vbphrase['ecdownloads_you_have_no_uploads'].'</td></tr>';
	}

	eval('$dmain_jr .= "' . fetch_template('downloads_my') . '";');
	if ($vbulletin->options['ecdownloads_tops'])
	{
 	 	eval('$dpanel = "' . fetch_template('downloads_panel_side') . '";');
		eval('$dmain .= "' . fetch_template('downloads_wrapper_side') . '";'); 
	}
	else
	{
		eval('$dmain .= "' . fetch_template('downloads_wrapper_top') . '";');
	}
}
else
{
	// check for category permissions
	$catexclude = $dl->exclude_cat();

	$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE ".$catexclude." parent = '0' ORDER BY ".$dl->order);
	while ($cat = $db->fetch_array($result))
	{
		if ($dl->hidesubcats == 0)
		{
			$subcats = $dl->grab_subcats_by_name_client($cat['id']);
		}
		else
		{
			$subcats = '';
		}

		exec_switch_bg();
		eval('$dcatbits .= "' . fetch_template('downloads_main_catbit') . '";');
	}

	$db->free_result($result);

/* 	if ($handle1 = opendir($dl->url))
	{
		while (false !== ($filedir1 = readdir($handle1)))
		{
			if (is_dir($dl->url.$filedir1) AND strpos($filedir1, "ec_") === 0)
			{
				if ($handle2 = opendir($dl->url.$filedir1))
				{
					while (false !== ($filedir2 = readdir($handle2)))
					{
						if ($filedir2 != "." AND $filedir2 != "..")
						{
							unlink($dl->url.$filedir1."/".$filedir2);
						}
					}
				}
				rmdir($dl->url.$filedir1);
				closedir($handle2);
			}
		}
		closedir($handle1);
	} */

		if (is_dir($dl->url."/ec_tmp/"))
		{
			if ($dh = opendir($dl->url."/ec_tmp/"))
			{
				// iterate over file list
				while (($filename = readdir($dh)) !== false)
				{
					//if (preg_match("/dlver-/",$filename))
					//{
					@unlink($dl->url."/ec_tmp/".$filename); // secure download, delete the files
					//}
				}
				// close directory
				closedir($dh);
			}
		}

	eval('$dmain_jr .= "' . fetch_template('downloads_main') . '";');

	if ($dl->statslatestfiles > 0 OR $dl->statstopcontributers > 0 OR $dl->statsmostpopularfiles > 0)
	{
		eval('$dpanel = "' . fetch_template('downloads_panel_side') . '";');
		eval('$dmain .= "' . fetch_template('downloads_wrapper_side') . '";');
	}
	else
	{
		eval('$dmain .= "' . fetch_template('downloads_wrapper_none') . '";');
	}
}

$navbits = construct_navbits($navbits);
eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('DOWNLOADS') . '");');
?>