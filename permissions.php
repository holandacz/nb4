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

//$photoplog_categoryintpermissions = array(
//	'maxfilesize' => 512000,
//	'maxfilelimit' => 100,
//);

// ######################## SET HSJS COUNTER ##############################
$photoplog['hscnt'] = 0;

// ##################### GRAB REQUIRED FUNCTIONS ##########################
require_once(DIR . '/includes/adminfunctions.php');

if (is_file($vbulletin->options['photoplog_full_path'].'/functions.php'))
{
	require_once($vbulletin->options['photoplog_full_path'].'/functions.php');
}
else
{
	echo "<br /><br /><strong>
		Incorrect PhotoPlog setting! Go to 
		ACP -> PhotoPlog Pro -> General Settings and make the correction.
		</strong><br /><br />";
	exit();
}

// ################# SET USERGROUPS FOR PERMISSIONS #######################
$photoplog_perm_usergroupid = $vbulletin->userinfo['usergroupid'];

// fetch_membergroupids_array with true includes both primary and secondaries
$photoplog_perm_membergroups_array = fetch_membergroupids_array($vbulletin->userinfo, true);
if  (
	!($vbulletin->usergroupcache[$photoplog_perm_usergroupid]['genericoptions'] 
		& 
	$vbulletin->bf_ugp_genericoptions['allowmembergroups'])
)
{
	$photoplog_perm_membergroups_array = array($photoplog_perm_usergroupid);
}
	
foreach ($photoplog_perm_membergroups_array AS $photoplog_perm_key => $photoplog_perm_val)
{
	$photoplog_perm_membergroups_array[$photoplog_perm_key] = intval($photoplog_perm_val);
}
if (!$photoplog_perm_membergroups_array)
{
	$photoplog_perm_membergroups_array = array(0);
}

// #################### SET CATID FOR PERMISSIONS #########################
$vbulletin->input->clean_array_gpc('g', array(
	'n' => TYPE_UINT,
	'c' => TYPE_UINT,
	'm' => TYPE_UINT
));

$vbulletin->input->clean_array_gpc('p', array(
	'fileid' => TYPE_UINT,
	'catid' => TYPE_UINT,
	'commentid' => TYPE_UINT,
));

// get or post, not both, so it is max of zero and something else
$photoplog_perm_fileid = max($vbulletin->GPC['n'],$vbulletin->GPC['fileid']);
$photoplog_perm_catid = max($vbulletin->GPC['c'],$vbulletin->GPC['catid']);
$photoplog_perm_commentid = max($vbulletin->GPC['m'],$vbulletin->GPC['commentid']);

$photoplog_perm_catid = intval($photoplog_perm_catid);

if (!$photoplog_perm_catid)
{
	if ($photoplog_perm_fileid)
	{
		$photoplog_perm_info = $db->query_first("SELECT catid FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
									WHERE fileid = ".intval($photoplog_perm_fileid)."
		");
		$photoplog_perm_catid = intval($photoplog_perm_info['catid']);
	}
	else if ($photoplog_perm_commentid)
	{
		$photoplog_perm_info = $db->query_first("SELECT catid FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
									WHERE commentid = ".intval($photoplog_perm_commentid)."
		");
		$photoplog_perm_catid = intval($photoplog_perm_info['catid']);
	}
	$db->free_result($photoplog_perm_info);
}

// #################### GRAB RELATIVE INFORMATION #########################
$photoplog_list_children = array();
$photoplog_list_relatives = array();
$photoplog_list_categories = array();
photoplog_child_list_v2($photoplog_list_children, $photoplog_list_relatives, $photoplog_list_categories);

$photoplog_parent_list_by_catid = array();
$photoplog_list_relatives_copy = $photoplog_list_relatives;
foreach ($photoplog_list_relatives_copy AS $photoplog_list_relatives_copy_catid => $photoplog_list_relatives_copy_array)
{
	if ($photoplog_list_relatives_copy_catid != '-1')
	{
		$photoplog_parent_list_by_catid[$photoplog_list_relatives_copy_catid] = array(0 => $photoplog_list_relatives_copy_catid);
		foreach ($photoplog_list_relatives AS $photoplog_list_relatives_catid => $photoplog_list_relatives_array)
		{
			if (
				$photoplog_list_relatives_catid != '-1'
					&&
				in_array($photoplog_list_relatives_copy_catid,$photoplog_list_relatives_array)
			)
			{
				$photoplog_list_relatives_cnt = count($photoplog_list_relatives_array);
				$photoplog_parent_list_by_catid[$photoplog_list_relatives_copy_catid][$photoplog_list_relatives_cnt] = $photoplog_list_relatives_catid;
			}
		}
		ksort($photoplog_parent_list_by_catid[$photoplog_list_relatives_copy_catid]);
	}
}
unset($photoplog_list_relatives_copy);

// ################## START PERMISSION INFORMATION ########################
$photoplog_current_results = array();

$photoplog_permission_results_temp = array();
$photoplog_permission_results_temp['options'] = $vbulletin->userinfo['permissions']['photoplogpermissions'];
$photoplog_permission_results_temp['maxfilesize'] = $vbulletin->userinfo['permissions']['photoplogmaxfilesize'];
$photoplog_permission_results_temp['maxfilelimit'] = $vbulletin->userinfo['permissions']['photoplogmaxfilelimit'];

foreach ($photoplog_perm_membergroups_array AS $photoplog_perm_membergroup_groupid)
{
	foreach ($photoplog_list_relatives AS $photoplog_list_relatives_catid => $photoplog_list_relatives_array)
	{
		if ($photoplog_list_relatives_catid != '-1')
		{
			$photoplog_permission_results_temp_bycat = array();
			if ($vbulletin->usergroupcache[$photoplog_perm_membergroup_groupid])
			{
				$photoplog_permission_results_temp_bycat['options'] = $vbulletin->usergroupcache[$photoplog_perm_membergroup_groupid]['photoplogpermissions'];
				$photoplog_permission_results_temp_bycat['maxfilesize'] = $vbulletin->usergroupcache[$photoplog_perm_membergroup_groupid]['photoplogmaxfilesize'];
				$photoplog_permission_results_temp_bycat['maxfilelimit'] = $vbulletin->usergroupcache[$photoplog_perm_membergroup_groupid]['photoplogmaxfilelimit'];
			}
			else
			{
				$photoplog_permission_results_temp_bycat = $photoplog_permission_results_temp;
			}
			$photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_list_relatives_catid] =  $photoplog_permission_results_temp_bycat;
		}
	}
}
unset($photoplog_permission_results_temp);

$photoplog_permission_infos = $db->query_read("SELECT * FROM " . PHOTOPLOG_PREFIX . "photoplog_permissions
					WHERE usergroupid IN (".implode(",",$photoplog_perm_membergroups_array).")
");

$photoplog_permission_results = array();
while ($photoplog_permission_info = $db->fetch_array($photoplog_permission_infos))
{
	if ($photoplog_permission_info['catid'] > -1)
	{
		$photoplog_permission_result_catid = $photoplog_permission_info['catid'];
		$photoplog_permission_result_usergroupid = $photoplog_permission_info['usergroupid'];
		$photoplog_permission_results_temp = array();
		$photoplog_permission_results_temp['options'] = $photoplog_permission_info['options'];
		$photoplog_permission_results_temp['maxfilesize'] = $photoplog_permission_info['maxfilesize'];
		$photoplog_permission_results_temp['maxfilelimit'] = $photoplog_permission_info['maxfilelimit'];
		$photoplog_permission_results[$photoplog_permission_result_usergroupid][$photoplog_permission_result_catid] = $photoplog_permission_results_temp;
	}
}
unset($photoplog_permission_results_temp);
$db->free_result($photoplog_permission_infos);

foreach ($photoplog_perm_membergroups_array AS $photoplog_perm_membergroup_groupid)
{
	foreach ($photoplog_list_relatives AS $photoplog_list_relatives_catid => $photoplog_list_relatives_array)
	{
		if ($photoplog_list_relatives_catid != '-1')
		{
			foreach ($photoplog_parent_list_by_catid[$photoplog_list_relatives_catid] AS $photoplog_parent_list_by_catid_catid)
			{
				if ($photoplog_permission_results[$photoplog_perm_membergroup_groupid][$photoplog_parent_list_by_catid_catid])
				{
					$photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_list_relatives_catid] = $photoplog_permission_results[$photoplog_perm_membergroup_groupid][$photoplog_parent_list_by_catid_catid];
					break;
				}
			}
		}
	}
}
unset($photoplog_permission_results);
unset($photoplog_parent_list_by_catid);

$photoplog_perm_opt1 = $vbulletin->userinfo['permissions'][photoplogpermissions];
$photoplog_perm_siz1 = $vbulletin->userinfo['permissions'][photoplogmaxfilesize];
$photoplog_perm_lim1 = $vbulletin->userinfo['permissions'][photoplogmaxfilelimit];

if ($photoplog_perm_catid)
{
	$photoplog_perm_opt1 = intval($photoplog_current_results[$photoplog_perm_usergroupid][$photoplog_perm_catid]['options']);
	$photoplog_perm_siz1 = intval($photoplog_current_results[$photoplog_perm_usergroupid][$photoplog_perm_catid]['maxfilesize']);
	$photoplog_perm_lim1 = intval($photoplog_current_results[$photoplog_perm_usergroupid][$photoplog_perm_catid]['maxfilelimit']);

	foreach ($photoplog_perm_membergroups_array AS $photoplog_perm_membergroup_groupid)
	{
		if (
			$photoplog_perm_membergroup_groupid != $photoplog_perm_usergroupid
				&&
			$photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_perm_catid]
		)
		{
			$photoplog_perm_opt2 = intval($photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_perm_catid]['options']);
			$photoplog_perm_siz2 = intval($photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_perm_catid]['maxfilesize']);
			$photoplog_perm_lim2 = intval($photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_perm_catid]['maxfilelimit']);

			$photoplog_perm_opt1 = $photoplog_perm_opt1 | $photoplog_perm_opt2;
			$photoplog_perm_siz1 = ($photoplog_perm_siz1 && $photoplog_perm_siz2) ? max($photoplog_perm_siz1,$photoplog_perm_siz2) : 0;
			$photoplog_perm_lim1 = ($photoplog_perm_lim1 && $photoplog_perm_lim2) ? max($photoplog_perm_lim1,$photoplog_perm_lim2) : 0;
		}
	}
}

$photoplog_permissions = array();
$photoplog_permissions = convert_bits_to_array($photoplog_perm_opt1, $photoplog_categoryoptpermissions);
$photoplog_permissions['options'] = $photoplog_perm_opt1;
$photoplog_permissions['maxfilesize'] = $photoplog_perm_siz1;
$photoplog_permissions['maxfilelimit'] = $photoplog_perm_lim1;

unset($photoplog_perm_opt1, $photoplog_perm_siz1, $photoplog_perm_lim1);
unset($photoplog_perm_opt2, $photoplog_perm_siz2, $photoplog_perm_lim2);

$permissions['photoplogpermissions'] = $photoplog_permissions['options'];
$permissions['photoplogmaxfilesize'] = $photoplog_permissions['maxfilesize'];
$permissions['photoplogmaxfilelimit_group'] = $permissions['photoplogmaxfilelimit'];
$permissions['photoplogmaxfilelimit'] = $photoplog_permissions['maxfilelimit'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanviewfiles'] = $photoplog_permissions['canviewfiles'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadfiles'] = $photoplog_permissions['canuploadfiles'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcaneditownfiles'] = $photoplog_permissions['caneditownfiles'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcandeleteownfiles'] = $photoplog_permissions['candeleteownfiles'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcaneditotherfiles'] = $photoplog_permissions['caneditotherfiles'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcandeleteotherfiles'] = $photoplog_permissions['candeleteotherfiles'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanviewcomments'] = $photoplog_permissions['canviewcomments'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcancommentonfiles'] = $photoplog_permissions['cancommentonfiles'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcaneditowncomments'] = $photoplog_permissions['caneditowncomments'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcandeleteowncomments'] = $photoplog_permissions['candeleteowncomments'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcaneditothercomments'] = $photoplog_permissions['caneditothercomments'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcandeleteothercomments'] = $photoplog_permissions['candeleteothercomments'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanusesearchfeature'] = $photoplog_permissions['canusesearchfeature'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadunmoderatedfiles'] = $photoplog_permissions['canuploadunmoderatedfiles'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanpostunmoderatedcomments'] = $photoplog_permissions['canpostunmoderatedcomments'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanuseslideshowfeature'] = $photoplog_permissions['canuseslideshowfeature'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadasdifferentuser'] = $photoplog_permissions['canuploadasdifferentuser'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanusealbumfeature'] = $photoplog_permissions['canusealbumfeature'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcansuggestcategories'] = $photoplog_permissions['cansuggestcategories'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcanuseftpimport'] = $photoplog_permissions['canuseftpimport'];
$vbulletin->bf_ugp_photoplogpermissions['photoplogcancreateunmoderatedcategories'] = $photoplog_permissions['cancreateunmoderatedcategories'];

unset($photoplog_permissions);

$photoplog_perm_denied = array();
$photoplog_perm_allowed = array();
$photoplog_inline_bits = array();

foreach ($photoplog_perm_membergroups_array AS $photoplog_perm_membergroup_groupid)
{
	foreach ($photoplog_list_relatives AS $photoplog_list_relatives_catid => $photoplog_list_relatives_array)
	{
		if (!isset($photoplog_inline_bits[$photoplog_list_relatives_catid]))
		{
			$photoplog_inline_bits[$photoplog_list_relatives_catid] = 0;
		}
		if (
			$photoplog_list_relatives_catid != '-1'
				&&
			$photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_list_relatives_catid]
		)
		{
			$photoplog_cat_mod_opt = intval($photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_list_relatives_catid]['options']);
			$photoplog_cat_mod_val = $photoplog_cat_mod_opt % 2;
			if ($photoplog_cat_mod_val == 0)
			{
				$photoplog_perm_denied[] = intval($photoplog_list_relatives_catid);
			}
			else
			{
				$photoplog_cat_mod_arr = convert_bits_to_array($photoplog_cat_mod_opt, $photoplog_categoryoptpermissions);
				if (
					defined('PHOTOPLOG_THIS_SCRIPT') && PHOTOPLOG_THIS_SCRIPT == 'categories'
						&& 
					(
						($_REQUEST['do'] == 'suggest' && !$photoplog_cat_mod_arr['cansuggestcategories'])
							||
						($_REQUEST['do'] == 'create' && !$photoplog_cat_mod_arr['cancreateunmoderatedcategories'])
					)
				)
				{
					$photoplog_perm_denied[] = intval($photoplog_list_relatives_catid);
				}
				else if (
					defined('PHOTOPLOG_THIS_SCRIPT') && PHOTOPLOG_THIS_SCRIPT == 'edit'
					&& !$photoplog_cat_mod_arr['canuploadfiles']
				)
				{
					if ($photoplog_perm_catid != $photoplog_list_relatives_catid)
					{
						$photoplog_perm_denied[] = intval($photoplog_list_relatives_catid);
					}
					else // allow edits even if category now denied because file already uploaded
					{
						$photoplog_perm_allowed[] = intval($photoplog_list_relatives_catid);
						$photoplog_inline_temp = intval($photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_list_relatives_catid]['options']);
						$photoplog_inline_bits[$photoplog_list_relatives_catid] = $photoplog_inline_bits[$photoplog_list_relatives_catid] | $photoplog_inline_temp;
					}
				}
				else if (
					defined('PHOTOPLOG_THIS_SCRIPT') && PHOTOPLOG_THIS_SCRIPT == 'search'
					&& !$photoplog_cat_mod_arr['canusesearchfeature']
				)
				{
					$photoplog_perm_denied[] = intval($photoplog_list_relatives_catid);
				}
				else if (
					defined('PHOTOPLOG_THIS_SCRIPT') && PHOTOPLOG_THIS_SCRIPT == 'slideshow'
					&& !$photoplog_cat_mod_arr['canuseslideshowfeature']
				)
				{
					$photoplog_perm_denied[] = intval($photoplog_list_relatives_catid);
				}
				else if (
					defined('PHOTOPLOG_THIS_SCRIPT') && PHOTOPLOG_THIS_SCRIPT == 'upload'
					&& !$photoplog_cat_mod_arr['canuploadfiles']
				)
				{
					$photoplog_perm_denied[] = intval($photoplog_list_relatives_catid);
				}
				else
				{
					$photoplog_perm_allowed[] = intval($photoplog_list_relatives_catid);
					$photoplog_inline_temp = intval($photoplog_current_results[$photoplog_perm_membergroup_groupid][$photoplog_list_relatives_catid]['options']);
					$photoplog_inline_bits[$photoplog_list_relatives_catid] = $photoplog_inline_bits[$photoplog_list_relatives_catid] | $photoplog_inline_temp;
				}
			}
		}
	}
}

unset($photoplog_current_results, $photoplog_cat_mod_opt, $photoplog_cat_mod_val, $photoplog_cat_mod_arr);

$photoplog_perm_not_allowed_bits = array_diff(array_unique($photoplog_perm_denied),array_unique($photoplog_perm_allowed));
if (!is_array($photoplog_perm_not_allowed_bits))
{
	$photoplog_perm_not_allowed_bits = array();
}
if (!can_administer('canadminforums'))
{
	$photoplog_perm_not_allowed_bits[] = 0;  // ignore category 0 for everyone but admin
}
$photoplog_perm_not_allowed_list = implode(",",$photoplog_perm_not_allowed_bits);

unset($photoplog_perm_denied,$photoplog_perm_allowed);

$photoplog_catid_sql1 = '';
$photoplog_catid_sql1a = '';
$photoplog_catid_sql2 = '';
$photoplog_catid_sql2a = '';
$photoplog_catid_sql3 = '';
$photoplog_catid_sql4 = '';
$photoplog_catid_sql4a = '';

if (!empty($photoplog_perm_not_allowed_bits))
{
	$photoplog_catid_sql1 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.catid NOT IN ('.$photoplog_perm_not_allowed_list.')';
	$photoplog_catid_sql1a = 'AND f1.catid NOT IN ('.$photoplog_perm_not_allowed_list.')';
	$photoplog_catid_sql2 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_ratecomment.catid NOT IN ('.$photoplog_perm_not_allowed_list.')';
	$photoplog_catid_sql2a = 'AND f2.catid NOT IN ('.$photoplog_perm_not_allowed_list.')';
	$photoplog_catid_sql3 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_catcounts.catid NOT IN ('.$photoplog_perm_not_allowed_list.')';
	$photoplog_catid_sql4 = 'AND IFNULL(' . PHOTOPLOG_PREFIX . 'photoplog_ratecomment.catid, -1) NOT IN ('.$photoplog_perm_not_allowed_list.')';
	$photoplog_catid_sql4a = 'AND IFNULL(f2.catid, -1) NOT IN ('.$photoplog_perm_not_allowed_list.')';
}

?>