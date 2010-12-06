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

// ##################### INITIALIZE CATEGORY BITS #########################
($hook = vBulletinHook::fetch_hook('photoplog_listing_start')) ? eval($hook) : false;

$photoplog_list_flag = 0;
$photoplog_list_sql1 = '';
$photoplog_list_sql2 = '';
$photoplog_list_sql3 = '';
if (defined('PHOTOPLOG_LISTING_FILE'))
{
	$photoplog_child_catlist = array_map('intval', $photoplog_child_catlist);
	$photoplog_list_catids = implode(',', $photoplog_child_catlist);
	$photoplog_list_flag = 1;
	$photoplog_list_sql1 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_catcounts.catid IN (' . $photoplog_list_catids . ')';
	$photoplog_list_sql2 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_ratecomment.catid IN (' . $photoplog_list_catids . ')';
	$photoplog_list_sql3 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.catid IN (' . $photoplog_list_catids . ')';
}
$photoplog_parent_catnum = ($photoplog_list_flag) ? $photoplog_child_catlist[0] : -1;

$photoplog_catcom_counts = $db->query_read("SELECT catid,
	num_uploads,num_comments,last_upload_dateline,last_comment_dateline,
	last_upload_id,last_comment_id
	FROM " . PHOTOPLOG_PREFIX . "photoplog_catcounts
	WHERE 1=1
	$photoplog_list_sql1
	$photoplog_catid_sql3
	$photoplog_admin_sql5
	AND num_uploads > 0
");

$photoplog_count_cats_coms = array();
while ($photoplog_catcom_count = $db->fetch_array($photoplog_catcom_counts))
{
	$photoplog_catcom_catid = intval($photoplog_catcom_count['catid']);
	$photoplog_catcom_num_uploads = intval($photoplog_catcom_count['num_uploads']);
	$photoplog_catcom_num_comments = intval($photoplog_catcom_count['num_comments']);
	$photoplog_catcom_last_upload_dateline = intval($photoplog_catcom_count['last_upload_dateline']);
	$photoplog_catcom_last_comment_dateline = intval($photoplog_catcom_count['last_comment_dateline']);
	$photoplog_catcom_last_upload_id = intval($photoplog_catcom_count['last_upload_id']);
	$photoplog_catcom_last_comment_id = intval($photoplog_catcom_count['last_comment_id']);
	$photoplog_count_cats_coms[$photoplog_catcom_catid] = array(
		'num_uploads' => $photoplog_catcom_num_uploads,
		'num_comments' => $photoplog_catcom_num_comments,
		'last_upload_dateline' => $photoplog_catcom_last_upload_dateline,
		'last_comment_dateline' => $photoplog_catcom_last_comment_dateline,
		'last_upload_id' => $photoplog_catcom_last_upload_id,
		'last_comment_id' => $photoplog_catcom_last_comment_id
	);
}
$db->free_result($photoplog_catcom_counts);

$photoplog_lastupload_list = array();
$photoplog_lastupload_list2 = array();
$photoplog_lastcomment_list = array();
$photoplog_numuploads_list = array();
$photoplog_numcomments_list = array();

$photoplog_divider_array = array();
$photoplog_toplevel_catids = array();
$photoplog_allow_desc_html = array();

foreach ($photoplog_ds_catopts AS $photoplog_ds_catid => $photoplog_ds_value)
{
	if (!$photoplog_ds_catopts[$photoplog_ds_catid]['displayorder'] && $photoplog_ds_catid != -1)
	{
		if (isset($photoplog_list_relatives[$photoplog_ds_catid]))
		{
			$photoplog_rel_list = $photoplog_list_relatives[$photoplog_ds_catid];
			foreach ($photoplog_rel_list AS $photoplog_rel_list_catid)
			{
				$photoplog_ds_catopts[$photoplog_rel_list_catid]['displayorder'] = 0;
			}
			unset($photoplog_rel_list);
		}
	}
}

foreach ($photoplog_ds_catopts AS $photoplog_ds_catid => $photoplog_ds_value)
{
	$photoplog_divider_opts = $photoplog_ds_catopts[$photoplog_ds_catid]['options'];
	$photoplog_divider_array[$photoplog_ds_catid] = convert_bits_to_array($photoplog_divider_opts, $photoplog_categoryoptions);
	$photoplog_allow_desc_html[$photoplog_ds_catid] = $photoplog_divider_array[$photoplog_ds_catid]['allowdeschtml'];

	if ($photoplog_list_flag && !in_array($photoplog_ds_catid, $photoplog_child_catlist))
	{
		$photoplog_ds_catopts[$photoplog_ds_catid]['displayorder'] = 0;
	}

	if (
		$photoplog_ds_catopts[$photoplog_ds_catid]['parentid'] == $photoplog_parent_catnum
			&&
		$photoplog_ds_catopts[$photoplog_ds_catid]['displayorder'] != 0
	)
	{
		$photoplog_temp_displayorder = $photoplog_ds_catopts[$photoplog_ds_catid]['displayorder'];
		while (in_array($photoplog_temp_displayorder,array_keys($photoplog_toplevel_catids)))
		{
			$photoplog_temp_displayorder++;
		}
		$photoplog_toplevel_catids[$photoplog_temp_displayorder] = $photoplog_ds_catid;
	}
}
unset($photoplog_divider_opts, $photoplog_temp_displayorder);

ksort($photoplog_toplevel_catids);
$photoplog_loop_catids = array();
foreach ($photoplog_toplevel_catids AS $photoplog_toplevel_displayorder => $photoplog_toplevel_catid)
{
	$photoplog_loop_catids[$photoplog_toplevel_catid] = $photoplog_toplevel_displayorder;
	$photoplog_child_array = array();
	if (isset($photoplog_list_children[$photoplog_toplevel_catid]))
	{
		$photoplog_child_array = $photoplog_list_children[$photoplog_toplevel_catid];
	}
	$photoplog_child_array_displayorders = array();

	foreach ($photoplog_child_array AS $photoplog_child_array_catid)
	{
		$photoplog_temp_displayorder = $photoplog_ds_catopts[$photoplog_child_array_catid]['displayorder'];
		while (in_array($photoplog_temp_displayorder,array_keys($photoplog_child_array_displayorders)))
		{
			$photoplog_temp_displayorder++;
		}
		$photoplog_child_array_displayorders[$photoplog_temp_displayorder] = $photoplog_child_array_catid;
	}

	ksort($photoplog_child_array_displayorders);
	foreach ($photoplog_child_array_displayorders AS $photoplog_child_array_displayorder => $photoplog_child_array_catid)
	{
		$photoplog_loop_catids[$photoplog_child_array_catid] = $photoplog_child_array_displayorder;
	}
}

$photoplog_loop_last_displayorder = 0;
foreach ($photoplog_loop_catids AS $photoplog_loop_catid => $photoplog_loop_displayorder)
{
	if ($photoplog_loop_displayorder <= $photoplog_loop_last_displayorder)
	{
		$photoplog_loop_last_displayorder++;
		$photoplog_loop_catids[$photoplog_loop_catid] = $photoplog_loop_last_displayorder;
	}
	else
	{
		$photoplog_loop_last_displayorder = $photoplog_loop_displayorder;
	}
	if ($photoplog_ds_catopts[$photoplog_loop_catid]['displayorder'] != 0)
	{
		$photoplog_ds_catopts[$photoplog_loop_catid]['displayorder'] = $photoplog_loop_catids[$photoplog_loop_catid];
	}
}

unset($photoplog_toplevel_catids,$photoplog_loop_catids,$photoplog_child_array,
$photoplog_child_array_displayorders,$photoplog_temp_displayorder,$photoplog_loop_last_displayorder);

foreach ($photoplog_ds_catopts AS $photoplog_ds_catid => $photoplog_ds_value)
{
	$photoplog_ds_catid = intval($photoplog_ds_catid);
	$photoplog_parent_catid = intval($photoplog_ds_catopts[$photoplog_ds_catid]['parentid']);

	if (
		(
			$photoplog_ds_catopts[$photoplog_ds_catid]['parentid'] == $photoplog_parent_catnum
				||
			($photoplog_divider_array[$photoplog_parent_catid]['actasdivider']
				&&
			$photoplog_ds_catopts[$photoplog_parent_catid]['parentid'] == $photoplog_parent_catnum)
		)
			&&
		$photoplog_ds_catopts[$photoplog_ds_catid]['displayorder'] != 0
	)
	{
		$photoplog_child_list = array();
		if (isset($photoplog_list_relatives[$photoplog_ds_catid]))
		{
			$photoplog_child_list = $photoplog_list_relatives[$photoplog_ds_catid];
		}
		$photoplog_memy_catids = array_merge(array($photoplog_ds_catid),$photoplog_child_list);

		$photoplog_lastupload_list[$photoplog_ds_catid] = 0;
		$photoplog_lastcomment_list[$photoplog_ds_catid] = 0;
		$photoplog_numuploads_list[$photoplog_ds_catid] = 0;
		$photoplog_numcomments_list[$photoplog_ds_catid] = 0;
		$photoplog_lastupload_dateline = 0;
		$photoplog_lastcomment_dateline = 0;
		foreach ($photoplog_memy_catids AS $photoplog_memy_catid)
		{
			if (isset($photoplog_count_cats_coms[$photoplog_memy_catid]) && $photoplog_ds_catopts[$photoplog_memy_catid]['displayorder'] != 0)
			{
				if (intval($photoplog_count_cats_coms[$photoplog_memy_catid]['last_upload_dateline']) > $photoplog_lastupload_dateline)
				{
					$photoplog_lastupload_dateline = intval($photoplog_count_cats_coms[$photoplog_memy_catid]['last_upload_dateline']);
					$photoplog_lastupload_list[$photoplog_ds_catid] = intval($photoplog_count_cats_coms[$photoplog_memy_catid]['last_upload_id']);
				}
				if (intval($photoplog_count_cats_coms[$photoplog_memy_catid]['last_comment_dateline']) > $photoplog_lastcomment_dateline)
				{
					$photoplog_lastcomment_dateline = intval($photoplog_count_cats_coms[$photoplog_memy_catid]['last_comment_dateline']);
					$photoplog_lastcomment_list[$photoplog_ds_catid] = intval($photoplog_count_cats_coms[$photoplog_memy_catid]['last_comment_id']);
				}
				$photoplog_numuploads_list[$photoplog_ds_catid] += intval($photoplog_count_cats_coms[$photoplog_memy_catid]['num_uploads']);
				$photoplog_numcomments_list[$photoplog_ds_catid] += intval($photoplog_count_cats_coms[$photoplog_memy_catid]['num_comments']);
			}
		}
		$photoplog_lastupload_list2[] = $photoplog_lastupload_list[$photoplog_ds_catid];
	}
}

$photoplog_lastcomment_list1 = '';
if (!empty($photoplog_lastcomment_list))
{
	$photoplog_lastcomment_list_temp = array_unique($photoplog_lastcomment_list);
	if (count($photoplog_lastcomment_list_temp) > 1 || (count($photoplog_lastcomment_list_temp) == 1 && implode('',$photoplog_lastcomment_list_temp) != 0))
	{
		$photoplog_lastcomment_list1 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_ratecomment.commentid IN (' . implode(",", $photoplog_lastcomment_list_temp) . ')';
	}
	unset($photoplog_lastcomment_list_temp);
}

$photoplog_last_comment = array();
if ($photoplog_lastcomment_list1)
{
	$photoplog_last_comment_rows = $db->query_read("SELECT commentid,
			catid, fileid, userid, username, title, dateline
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE 1=1
		$photoplog_lastcomment_list1
		$photoplog_list_sql2
		$photoplog_catid_sql2
		$photoplog_admin_sql2
		AND comment != ''
		ORDER BY dateline DESC
	");

	while ($photoplog_last_comment_row = $db->fetch_array($photoplog_last_comment_rows))
	{
		$photoplog_last_comment_row_commentid = intval($photoplog_last_comment_row['commentid']);
		$photoplog_last_comment_row_catid = intval($photoplog_last_comment_row['catid']);
		$photoplog_last_comment_row_fileid = intval($photoplog_last_comment_row['fileid']);
		$photoplog_lastupload_list2[] = $photoplog_last_comment_row_fileid;
		$photoplog_last_comment_row_userid = intval($photoplog_last_comment_row['userid']);
		$photoplog_last_comment_row_username = strval($photoplog_last_comment_row['username']);
		$photoplog_last_comment_row_title = strval($photoplog_last_comment_row['title']);
		$photoplog_last_comment_row_dateline = intval($photoplog_last_comment_row['dateline']);
		$photoplog_last_comment_row_info_array = array(
			'commentid' => $photoplog_last_comment_row_commentid,
			'catid' => $photoplog_last_comment_row_catid,
			'fileid' => $photoplog_last_comment_row_fileid,
			'userid' => $photoplog_last_comment_row_userid,
			'username' => $photoplog_last_comment_row_username,
			'title' => $photoplog_last_comment_row_title,
			'dateline' => $photoplog_last_comment_row_dateline
		);

		foreach ($photoplog_lastcomment_list AS $photoplog_lastcomment_list_catid => $photoplog_lastcomment_list_commentid)
		{
			if (
				($photoplog_last_comment_row_commentid == $photoplog_lastcomment_list_commentid)
					&&
				(!$photoplog_last_comment[$photoplog_lastcomment_list_catid])
			)
			{
				$photoplog_last_comment[$photoplog_lastcomment_list_catid] = $photoplog_last_comment_row_info_array;
			}
		}
	}
	$db->free_result($photoplog_last_comment_rows);
	unset($photoplog_last_comment_row_info_array);
}

$photoplog_lastupload_list1 = '';
if (!empty($photoplog_lastupload_list2))
{
	$photoplog_lastupload_list2_temp = array_unique($photoplog_lastupload_list2);
	if (count($photoplog_lastupload_list2_temp) > 1 || (count($photoplog_lastupload_list2_temp) == 1 && implode('',$photoplog_lastupload_list2_temp) != 0))
	{
		$photoplog_lastupload_list1 = 'AND ' . PHOTOPLOG_PREFIX . 'photoplog_fileuploads.fileid IN (' . implode(",", $photoplog_lastupload_list2_temp) . ')';
	}
	unset($photoplog_lastupload_list2_temp);
}

$photoplog_last_upload = array();
$photoplog_last_upload_byfile = array();
if ($photoplog_lastupload_list1)
{
	$photoplog_last_upload_rows = $db->query_read("SELECT catid,
			fileid, userid, username, title, dateline, filename,
			$photoplog_admin_sql4
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE 1=1
		$photoplog_lastupload_list1
		$photoplog_list_sql3
		$photoplog_catid_sql1
		$photoplog_admin_sql1
		ORDER BY dateline DESC
	");

	while ($photoplog_last_upload_row = $db->fetch_array($photoplog_last_upload_rows))
	{
		$photoplog_last_upload_row_catid = intval($photoplog_last_upload_row['catid']);
		$photoplog_last_upload_row_fileid = intval($photoplog_last_upload_row['fileid']);
		$photoplog_last_upload_row_userid = intval($photoplog_last_upload_row['userid']);
		$photoplog_last_upload_row_username = strval($photoplog_last_upload_row['username']);
		$photoplog_last_upload_row_title = strval($photoplog_last_upload_row['title']);
		$photoplog_last_upload_row_dateline = intval($photoplog_last_upload_row['dateline']);
		$photoplog_last_upload_row_num_comments = intval($photoplog_last_upload_row['num_comments']);
		$photoplog_last_upload_row_last_comment_dateline = intval($photoplog_last_upload_row['last_comment_dateline']);
		$photoplog_last_upload_row_last_comment_id = intval($photoplog_last_upload_row['last_comment_id']);
		$photoplog_last_upload_row_filename = strval($photoplog_last_upload_row['filename']);
		$photoplog_last_upload_row_info_array = array(
			'fileid' => $photoplog_last_upload_row_fileid,
			'userid' => $photoplog_last_upload_row_userid,
			'username' => $photoplog_last_upload_row_username,
			'title' => $photoplog_last_upload_row_title,
			'dateline' => $photoplog_last_upload_row_dateline,
			'num_comments' => $photoplog_last_upload_row_num_comments,
			'last_comment_dateline' => $photoplog_last_upload_row_last_comment_dateline,
			'last_comment_id' => $photoplog_last_upload_row_last_comment_id,
			'filename' => $photoplog_last_upload_row_filename
		);
		$photoplog_last_upload_byfile[$photoplog_last_upload_row_fileid] = $photoplog_last_upload_row_info_array;

		foreach ($photoplog_lastupload_list AS $photoplog_lastupload_list_catid => $photoplog_lastupload_list_fileid)
		{
			if (
				($photoplog_last_upload_row_fileid == $photoplog_lastupload_list_fileid)
					&&
				(!$photoplog_last_upload[$photoplog_lastupload_list_catid])
			)
			{
				$photoplog_last_upload[$photoplog_lastupload_list_catid] = $photoplog_last_upload_row_info_array;
			}
		}
	}
	$db->free_result($photoplog_last_upload_rows);
	unset($photoplog_last_upload_row_info_array);
}

$photoplog_catbit_info = array();
if (!is_array($photoplog_perm_not_allowed_bits))
{
	$photoplog_perm_not_allowed_bits = array();
}
foreach ($photoplog_ds_catopts AS $photoplog_ds_catid => $photoplog_ds_value)
{
	$photoplog_ds_catid = intval($photoplog_ds_catid);
	$photoplog_parent_catid = intval($photoplog_ds_catopts[$photoplog_ds_catid]['parentid']);

	if (
		(
			$photoplog_ds_catopts[$photoplog_ds_catid]['parentid'] == $photoplog_parent_catnum
				||
			($photoplog_divider_array[$photoplog_parent_catid]['actasdivider']
				&&
			$photoplog_ds_catopts[$photoplog_parent_catid]['parentid'] == $photoplog_parent_catnum)
		)
			&&
		$photoplog_ds_catopts[$photoplog_ds_catid]['displayorder'] != 0
	)
	{
		$photoplog_catbit_displayorder = $photoplog_ds_catopts[$photoplog_ds_catid]['displayorder'];
		while (in_array($photoplog_catbit_displayorder,array_keys($photoplog_catbit_info)))
		{
			$photoplog_catbit_displayorder++;
		}
		$photoplog_catbit_title = $photoplog_ds_catopts[$photoplog_ds_catid]['title'];
		$photoplog_catbit_description = $photoplog_ds_catopts[$photoplog_ds_catid]['description'];

		$photoplog_catbit_info[$photoplog_catbit_displayorder]['title'] = $photoplog_catbit_title;
		$photoplog_catbit_info[$photoplog_catbit_displayorder]['description'] = $photoplog_catbit_description;
		$photoplog_catbit_info[$photoplog_catbit_displayorder]['count1'] = intval($photoplog_numuploads_list[$photoplog_ds_catid]);
		$photoplog_catbit_info[$photoplog_catbit_displayorder]['count2'] = intval($photoplog_numcomments_list[$photoplog_ds_catid]);
		$photoplog_catbit_info[$photoplog_catbit_displayorder]['catid'] = $photoplog_ds_catid;

		$photoplog_catbit_info[$photoplog_catbit_displayorder]['subcats'] = '';
		$photoplog_catbit_info_temp = array();

		$photoplog_child_list = array();
		if (isset($photoplog_list_children[$photoplog_ds_catid]))
		{
			$photoplog_child_list = $photoplog_list_children[$photoplog_ds_catid];
		}

		foreach ($photoplog_child_list AS $photoplog_cl_value)
		{
			if (
				!in_array($photoplog_cl_value,$photoplog_perm_not_allowed_bits)
					&&
				$photoplog_ds_catopts[$photoplog_cl_value]['displayorder'] != 0
			)
			{
				$photoplog_catbit_substamp1 = 0;
				$photoplog_catbit_substamp2 = 0;
				if (isset($photoplog_count_cats_coms[$photoplog_cl_value]))
				{
					$photoplog_catbit_substamp1 = intval($photoplog_count_cats_coms[$photoplog_cl_value]['last_upload_dateline']);
					$photoplog_catbit_substamp2 = intval($photoplog_count_cats_coms[$photoplog_cl_value]['last_comment_dateline']);
				}

				if (
					$vbulletin->userinfo['lastvisitdate'] == -1
						||
					$vbulletin->userinfo['lastvisit'] < $photoplog_catbit_substamp1
						||
					$vbulletin->userinfo['lastvisit'] < $photoplog_catbit_substamp2
				)
				{
					$photoplog_newold_substatusicon = 'new';
				}
				else
				{
					$photoplog_newold_substatusicon = 'old';
				}

				$photoplog_catbit_displayorder_temp = $photoplog_ds_catopts[$photoplog_cl_value]['displayorder'];
				while (in_array($photoplog_catbit_displayorder_temp,array_keys($photoplog_catbit_info_temp)))
				{
					$photoplog_catbit_displayorder_temp++;
				}
				$photoplog_catbit_title_temp = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog_cl_value]['title']);
				$photoplog_catbit_description_temp = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog_cl_value]['description']);
				$photoplog_catbit_info_temp[$photoplog_catbit_displayorder_temp] = "<img src=\"".$stylevar['imgdir_statusicon']."/post_".$photoplog_newold_substatusicon.".gif\" alt=\"".$vbphrase['photoplog_image']."\" border=\"0\" />&nbsp;<a href=\"".$photoplog['location']."/index.php?".$vbulletin->session->vars['sessionurl']."c=".$photoplog_cl_value."\" title=\"".$photoplog_catbit_description_temp."\">".$photoplog_catbit_title_temp."</a>";
			}
		}

		if (!empty($photoplog_catbit_info_temp))
		{
			ksort($photoplog_catbit_info_temp);
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['subcats'] = ' '.implode(' ',$photoplog_catbit_info_temp);
		}

		if ($photoplog_catbit_info[$photoplog_catbit_displayorder]['count1'])
		{
			$photoplog_lu_fileid = '';
			$photoplog_lu_userid = '';
			$photoplog_lu_username = '';
			$photoplog_lu_title = '';
			$photoplog_lu_date = '';
			$photoplog_lu_time = '';
			$photoplog_lu_filename = '';

			if ($photoplog_last_upload[$photoplog_ds_catid])
			{
				$photoplog_lu_fileid = $photoplog_last_upload[$photoplog_ds_catid]['fileid'];
				$photoplog_lu_userid = $photoplog_last_upload[$photoplog_ds_catid]['userid'];
				$photoplog_lu_username = $photoplog_last_upload[$photoplog_ds_catid]['username'];
				$photoplog_lu_title = $photoplog_last_upload[$photoplog_ds_catid]['title'];
				$photoplog_lu_date = vbdate($vbulletin->options['dateformat'],$photoplog_last_upload[$photoplog_ds_catid]['dateline'],true);
				$photoplog_lu_time = vbdate($vbulletin->options['timeformat'],$photoplog_last_upload[$photoplog_ds_catid]['dateline']);
				$photoplog_lu_stamp = $photoplog_last_upload[$photoplog_ds_catid]['dateline'];
				$photoplog_lu_filename = $photoplog_last_upload[$photoplog_ds_catid]['filename'];
			}

			$photoplog_catbit_info[$photoplog_catbit_displayorder]['fileid1'] = $photoplog_lu_fileid;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['userid1'] = $photoplog_lu_userid;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['username1'] = $photoplog_lu_username;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['lasttitle1'] = $photoplog_lu_title;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['date1'] = $photoplog_lu_date;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['time1'] = $photoplog_lu_time;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['stamp1'] = $photoplog_lu_stamp;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['filename1'] = $photoplog_lu_filename;
		}

		if ($photoplog_catbit_info[$photoplog_catbit_displayorder]['count2'])
		{
			$photoplog_lc_fileid = '';
			$photoplog_lc_userid = '';
			$photoplog_lc_username = '';
			$photoplog_lc_title = '';
			$photoplog_lc_date = '';
			$photoplog_lc_time = '';
			$photoplog_lc_pagenum = '';
			$photoplog_lc_commentid = '';
			$photoplog_lc_page = '';
			$photoplog_lc_docoms = 0;

			if ($photoplog_last_comment[$photoplog_ds_catid])
			{
				$photoplog_lc_fileid = intval($photoplog_last_comment[$photoplog_ds_catid]['fileid']);

				$photoplog_lc_catid = $photoplog_last_comment[$photoplog_ds_catid]['catid'];
				if (in_array($photoplog_lc_catid,array_keys($photoplog_ds_catopts)))
				{
					$photoplog_categorybit = $photoplog_ds_catopts["$photoplog_lc_catid"]['options'];
					$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

					$photoplog_lc_docoms = ($photoplog_catoptions['allowcomments']) ? 1 : 0;
				}

				if ($photoplog_last_upload_byfile[$photoplog_lc_fileid]['num_comments'] && $photoplog_last_upload_byfile[$photoplog_lc_fileid]['last_comment_id'])
				{
					$photoplog_lc_pagenum = '&amp;page='.ceil($photoplog_last_upload_byfile[$photoplog_lc_fileid]['num_comments'] / 5);
					$photoplog_lc_commentid = '#comment'.$photoplog_last_upload_byfile[$photoplog_lc_fileid]['last_comment_id'];
				}
				$photoplog_lc_page = $photoplog_lc_pagenum.$photoplog_lc_commentid;

				$photoplog_lc_userid = $photoplog_last_comment[$photoplog_ds_catid]['userid'];
				$photoplog_lc_username = $photoplog_last_comment[$photoplog_ds_catid]['username'];
				$photoplog_lc_title = $photoplog_last_comment[$photoplog_ds_catid]['title'];
				$photoplog_lc_date = vbdate($vbulletin->options['dateformat'],$photoplog_last_comment[$photoplog_ds_catid]['dateline'],true);
				$photoplog_lc_time = vbdate($vbulletin->options['timeformat'],$photoplog_last_comment[$photoplog_ds_catid]['dateline']);
				$photoplog_lc_stamp = $photoplog_last_comment[$photoplog_ds_catid]['dateline'];
			}

			$photoplog_catbit_info[$photoplog_catbit_displayorder]['fileid2'] = $photoplog_lc_fileid;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['userid2'] = $photoplog_lc_userid;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['username2'] = $photoplog_lc_username;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['lasttitle2'] = $photoplog_lc_title;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['date2'] = $photoplog_lc_date;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['time2'] = $photoplog_lc_time;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['stamp2'] = $photoplog_lc_stamp;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['page2'] = $photoplog_lc_page;
			$photoplog_catbit_info[$photoplog_catbit_displayorder]['docoms2'] = $photoplog_lc_docoms;
		}
	}
}
unset($photoplog_count_cats_coms, $photoplog_last_upload, $photoplog_last_upload_byfile, $photoplog_last_comment,
	$photoplog_lastupload_list, $photoplog_lastupload_list2, $photoplog_lastcomment_list, $photoplog_numuploads_list,
	$photoplog_numcomments_list);

$photoplog['cat_bits'] = '';
$photoplog_divider_collapse = '';
$photoplog_divider_bar = 0;
$photoplog_divider_flag = 0;
if (!empty($photoplog_catbit_info))
{
	ksort($photoplog_catbit_info);

	$photoplog_catbit_title = '';
	$photoplog_catbit_description = '';
	$photoplog_upload_count = '';
	$photoplog_comment_count = '';
	$photoplog_catbit_catid = '';
	$photoplog_catbit_subcats = '';

	$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_category_thumb'], 0, 1).'link';
	$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_category_thumb'], -1, 1).'link';

	$photoplog['do_highslide'] = 0;
	if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
	{
		$photoplog['do_highslide'] = 1;
	}

	foreach ($photoplog_catbit_info AS $photoplog_key => $photoplog_value)
	{
		$photoplog_catbit_catid = $photoplog_catbit_info[$photoplog_key]['catid'];
		$photoplog_catbit_title = htmlspecialchars_uni($photoplog_catbit_info[$photoplog_key]['title']);
		$photoplog_catbit_description = $photoplog_catbit_info[$photoplog_key]['description'];
		if (!$photoplog_allow_desc_html[$photoplog_catbit_catid])
		{
			$photoplog_catbit_description = htmlspecialchars_uni($photoplog_catbit_description);
		}
		$photoplog_upload_count = $photoplog_catbit_info[$photoplog_key]['count1'];
		$photoplog_comment_count = $photoplog_catbit_info[$photoplog_key]['count2'];
		$photoplog_catbit_subcats = $photoplog_catbit_info[$photoplog_key]['subcats'];

		$photoplog_catbit_fileid1 = '';
		$photoplog_catbit_userid1 = '';
		$photoplog_catbit_username1 = '';
		$photoplog_catbit_lasttitle1 = '';
		$photoplog_catbit_date1 = '';
		$photoplog_catbit_time1 = '';
		$photoplog_catbit_stamp1 = '';
		$photoplog_catbit_filename1 = '';

		if ($photoplog_upload_count && !empty($photoplog_catbit_info[$photoplog_key]['fileid1']))
		{
			$photoplog_catbit_fileid1 = $photoplog_catbit_info[$photoplog_key]['fileid1'];
			$photoplog_catbit_userid1 = $photoplog_catbit_info[$photoplog_key]['userid1'];
			$photoplog_catbit_username1 = $photoplog_catbit_info[$photoplog_key]['username1'];
			$photoplog_catbit_lasttitle1 = $photoplog_catbit_info[$photoplog_key]['lasttitle1'];
			$photoplog_catbit_date1 = $photoplog_catbit_info[$photoplog_key]['date1'];
			$photoplog_catbit_time1 = $photoplog_catbit_info[$photoplog_key]['time1'];
			$photoplog_catbit_stamp1 = $photoplog_catbit_info[$photoplog_key]['stamp1'];
			$photoplog_catbit_filename1 = $photoplog_catbit_info[$photoplog_key]['filename1'];

			if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog_catbit_lasttitle1) > $vbulletin->options['lastthreadchars'])
			{
				$photoplog_catbit_lasttitle1 = fetch_trimmed_title($photoplog_catbit_lasttitle1, $vbulletin->options['lastthreadchars']);
				$photoplog_catbit_lasttitle1 = photoplog_regexp_text($photoplog_catbit_lasttitle1);
			}
			$photoplog_catbit_lasttitle1 = photoplog_process_text($photoplog_catbit_lasttitle1, $photoplog_catbit_catid, true, false);
		}

		$photoplog_catbit_fileid2 = '';
		$photoplog_catbit_userid2 = '';
		$photoplog_catbit_username2 = '';
		$photoplog_catbit_lasttitle2 = '';
		$photoplog_catbit_date2 = '';
		$photoplog_catbit_time2 = '';
		$photoplog_catbit_stamp2 = '';
		$photoplog_catbit_page2 = '';
		$photoplog['do_comments'] = 0;

		if ($photoplog_comment_count && !empty($photoplog_catbit_info[$photoplog_key]['fileid2']))
		{
			$photoplog_catbit_fileid2 = $photoplog_catbit_info[$photoplog_key]['fileid2'];
			$photoplog_catbit_userid2 = $photoplog_catbit_info[$photoplog_key]['userid2'];
			$photoplog_catbit_username2 = $photoplog_catbit_info[$photoplog_key]['username2'];
			$photoplog_catbit_lasttitle2 = $photoplog_catbit_info[$photoplog_key]['lasttitle2'];
			$photoplog_catbit_date2 = $photoplog_catbit_info[$photoplog_key]['date2'];
			$photoplog_catbit_time2 = $photoplog_catbit_info[$photoplog_key]['time2'];
			$photoplog_catbit_stamp2 = $photoplog_catbit_info[$photoplog_key]['stamp2'];
			$photoplog_catbit_page2 = $photoplog_catbit_info[$photoplog_key]['page2'];
			$photoplog['do_comments'] = $photoplog_catbit_info[$photoplog_key]['docoms2'];

			if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog_catbit_lasttitle2) > $vbulletin->options['lastthreadchars'])
			{
				$photoplog_catbit_lasttitle2 = fetch_trimmed_title($photoplog_catbit_lasttitle2, $vbulletin->options['lastthreadchars']);
				$photoplog_catbit_lasttitle2 = photoplog_regexp_text($photoplog_catbit_lasttitle2);
			}
			$photoplog_catbit_lasttitle2 = photoplog_process_text($photoplog_catbit_lasttitle2, $photoplog_catbit_catid, true, false);
		}

		if (
			$vbulletin->userinfo['lastvisitdate'] == -1
				||
			$vbulletin->userinfo['lastvisit'] < $photoplog_catbit_stamp1
				||
			$vbulletin->userinfo['lastvisit'] < $photoplog_catbit_stamp2
		)
		{
			$photoplog_newold_statusicon = 'new';
		}
		else
		{
			$photoplog_newold_statusicon = 'old';
		}

		if (
			!($permissions['photoplogpermissions'] & $vbulletin->bf_ugp_photoplogpermissions['photoplogcanuploadfiles'])
				&&
			$vbulletin->options['showlocks']
		)
		{
			$photoplog_newold_statusicon .= '_lock';
		}

		if (!in_array($photoplog_catbit_catid,$photoplog_perm_not_allowed_bits))
		{
			$photoplog_divider_bar = 0;
			$photoplog_parent_catid = intval($photoplog_ds_catopts[$photoplog_catbit_catid]['parentid']);

			if (!$photoplog_list_flag)
			{
				if ($photoplog_divider_array[$photoplog_catbit_catid]['actasdivider'] && !$photoplog_divider_array[$photoplog_parent_catid]['actasdivider'])
				{
					$photoplog_divider_collapse = 'collapseimg_photoplog_catbit_'.$photoplog_catbit_catid;
					$photoplog_divider_collapse = $vbcollapse[$photoplog_divider_collapse];
					$photoplog_divider_bar = 1;
				}
				else if ($photoplog_divider_array[$photoplog_catbit_catid]['actasdivider'] && $photoplog_divider_array[$photoplog_parent_catid]['actasdivider'])
				{
					$photoplog_divider_bar = 2;
				}
			}
			else
			{
				if ($photoplog_divider_array[$photoplog_catbit_catid]['actasdivider'] && $photoplog_parent_catid == $photoplog_parent_catnum)
				{
					$photoplog_divider_collapse = 'collapseimg_photoplog_catbit_'.$photoplog_catbit_catid;
					$photoplog_divider_collapse = $vbcollapse[$photoplog_divider_collapse];
					$photoplog_divider_bar = 1;
				}
				else if ($photoplog_divider_array[$photoplog_catbit_catid]['actasdivider'] && $photoplog_parent_catid != $photoplog_parent_catnum)
				{
					$photoplog_divider_bar = 2;
				}
			}

			if (($photoplog_divider_bar == 1 || $photoplog_parent_catid == $photoplog_parent_catnum) && $photoplog_divider_flag == 1)
			{
				$photoplog_divider_flag = 0;
				$photoplog['cat_bits'] .= '
					</tbody>
				';
			}
			if (($photoplog_divider_bar == 1 || $photoplog_parent_catid == $photoplog_parent_catnum) && $photoplog_divider_flag == 0)
			{
				$photoplog_divider_flag = 1;
				$photoplog['cat_bits'] .= '
					<tbody>
				';
			}
			else if ($photoplog_divider_flag == 0)
			{
				$photoplog_divider_collapse = 'collapseobj_photoplog_catbit_'.$photoplog_parent_catid;
				$photoplog_divider_collapse = $vbcollapse[$photoplog_divider_collapse];
				$photoplog_divider_flag = 1;
				$photoplog['cat_bits'] .= '
					<tbody id="collapseobj_photoplog_catbit_'.$photoplog_parent_catid.'" style="'.$photoplog_divider_collapse.'">
				';
			}

			$photoplog['hscnt'] = intval($photoplog['hscnt']) + 1;

			photoplog_file_link($photoplog_catbit_userid1, $photoplog_catbit_fileid1, $photoplog_catbit_filename1);

			($hook = vBulletinHook::fetch_hook('photoplog_listing_catbit')) ? eval($hook) : false;

			eval('$photoplog[\'cat_bits\'] .= "' . fetch_template('photoplog_cat_bit') . '";');

			if (($photoplog_divider_bar == 1 || $photoplog_parent_catid == $photoplog_parent_catnum) && $photoplog_divider_flag == 1)
			{
				$photoplog_divider_flag = 0;
				$photoplog['cat_bits'] .= '
					</tbody>
				';
			}
		}
	}

	if ($photoplog_divider_flag == 1)
	{
		$photoplog_divider_flag = 0;
		$photoplog['cat_bits'] .= '
			</tbody>
		';
	}
}
else
{
	$photoplog['cat_bits'] = '<tr><td colspan="7" class="alt2">'.$vbphrase['photoplog_not_available'].'</td></tr>';
	if ($photoplog_list_flag)
	{
		$photoplog['cat_bits'] = '';
	}
}

($hook = vBulletinHook::fetch_hook('photoplog_listing_complete')) ? eval($hook) : false;

$photoplog['cat_list'] = $photoplog['status_key'] = '';
if ($photoplog['cat_bits'])
{
	eval('$photoplog[\'status_key\'] = "' . fetch_template('photoplog_status_key') . '";');
	if ($photoplog_list_flag && !$photoplog['index_page'])
	{
		$photoplog['status_key'] = '<br />'.$photoplog['status_key'];
	}
	eval('$photoplog[\'cat_list\'] = "' . fetch_template('photoplog_cat_list') . '";');
}

?>