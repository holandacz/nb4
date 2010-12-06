<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ##################### Start photoplog_list_worker #####################
// Used by photoplog_list_categories
function photoplog_list_worker(&$list_cats, $parentid, $root_title, $spacer, $spacer_append,
	&$catids, &$titles, &$parentids)
{
	if ($parentid < 0)
	{
		$list_cats = array("-1" => $root_title);
	}

	$n = count($catids);
	for ($i=0; $i<$n; $i++)
	{
		if ($parentid == $parentids[$i])
		{
			$list_cats["$catids[$i]"] = $spacer." ".$titles[$i]." ";
			if ($catids[$i] >= 0)
			{
				photoplog_list_worker($list_cats, $catids[$i], $root_title, $spacer.$spacer_append,
					$spacer_append, $catids, $titles, $parentids);
			}
		}
	}
}

// ################### Start photoplog_list_categories ###################
// Constructs an array of hierarchical category titles indexed by the catid.  It is appropriate for use
// in a select dropdown.
// - list_cats: the array, passed in by reference.  Typically, an empty array should be passed in.
// - parentid: the catid for the top of the hierarchy
// - root_title: the title for the top of the hierarchy if parentid < 0
// - include_not_available: indicates whether to include the not_available category
// - spacer: the prefix for each of the titles
// - spacer_append: the text to add for each level of the hierarchy
function photoplog_list_categories(&$list_cats, $parentid = -1, $root_title = '', $include_not_available = 0,
	$spacer = '', $spacer_append = '--')
{
	global $vbulletin,$vbphrase;

	// ORDER BY parentid,displayorder,catid
	// now done by ALTER TABLE after change
	$categories = $vbulletin->db->query_read("SELECT catid, title, parentid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
	");

	$catids = array();
	$titles = array();
	$parentids = array();

	while ($category = $vbulletin->db->fetch_array($categories))
	{
		if ($include_not_available || ($category['catid'] > 0))
		{
			$catids[] = $category['catid'];
			$titles[] = $category['title'];
			$parentids[] = $category['parentid'];
		}
	}
	$vbulletin->db->free_result($categories);
	if ($include_not_available && (!in_array(0,$catids)))
	{
		$catids[] = 0;
		$titles[] = $vbphrase['photoplog_not_available'];
		$parentids[] = -1;
	}

	photoplog_list_worker($list_cats, $parentid, $root_title, $spacer,
		$spacer_append, $catids, $titles, $parentids);
}

// ##################### Start photoplog_child_worker ####################
// Used by photoplog_child_list
function photoplog_child_worker(&$list_children, &$list_parents, $catid, &$catids, &$parids, $levels)
{
	$n = count($catids);
	for ($i=0; $i<$n; $i++)
	{
		if ($catid == $parids[$i])
		{
			$list_children[] = $catids[$i];
			$list_parents[] = $catid;
			if (($catids[$i] >= 0) && ($levels != 0))
			{
				photoplog_child_worker($list_children, $list_parents, $catids[$i], $catids, $parids, $levels - 1);
			}
		}
	}
}

// ##################### Start photoplog_child_list ######################
// Constructs paired arrays of children and parents for some or all descendents of a category.
// The array indices are numeric and without meaning, but the categories are added hierarchically.
// - list_children: the children array
// - list_parents: the parents array
// - catid: the category id for the root.
// - levels: controls which descendents are included.  The default level is -1, which
//   indicates that all descendents are included.  Level 0 indicates that only immediate
//   children are included,  level 1 indicates that children and grandchildren are included,
//   and so forth.
function photoplog_child_list(&$list_children, &$list_parents, $catid = -1, $levels = -1)
{
	global $vbulletin;

	$categories = $vbulletin->db->query_read("SELECT catid, parentid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
	");

	$catids = array();
	$parids = array();

	while ($category = $vbulletin->db->fetch_array($categories))
	{
		$catids[] = $category['catid'];
		$parids[] = $category['parentid'];
	}
	$vbulletin->db->free_result($categories);
	if (!in_array(0,$catids))
	{
		$catids[] = 0;
		$parids[] = -1;
	}

	photoplog_child_worker($list_children, $list_parents, $catid, $catids, $parids, $levels);
}

// ################## Start photoplog_relative_worker ####################
// Used by photoplog_relative_list
function photoplog_relative_worker(&$list_imm, &$list_all, $parid = -1)
{
	$parid = intval($parid);
	$list_all[$parid] = array();

	if (!is_array($list_imm[$parid]))
	{
		$list_imm[$parid] = array();
	}

	foreach ($list_imm[$parid] as $catid)
	{
		$list_all[$parid][] = intval($catid);

		if (!isset($list_all[$catid]))
		{
			photoplog_relative_worker($list_imm, $list_all, $catid);
		}

		$list_all[$parid] = array_merge($list_all[$parid], $list_all[$catid]);
	}
}

// ################### Start photoplog_relative_list #####################
// Constructs paired arrays of immediate children and all descendents of each category.
function photoplog_relative_list(&$list_imm, &$list_all)
{
	global $vbulletin;

	$categories = $vbulletin->db->query_read("SELECT catid, parentid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
	");

	while ($category = $vbulletin->db->fetch_array($categories))
	{
		$catid = intval($category['catid']);
		$parid = intval($category['parentid']);

		$list_imm[$parid][] = $catid;

		if (!isset($list_imm[$catid]))
		{
			$list_imm[$catid] = array();
		}
	}
	$vbulletin->db->free_result($categories);
	photoplog_relative_worker($list_imm, $list_all);
}

// ########### Start photoplog_make_source_key_of_parent_list ############
// This function takes as inputs, arrays of categories and their parents
// (such as constructed by photoplog_child_list) and returns an array that maps
// the parent_list to the category_list.  For example, if the category structure is
// -1 => (1,4,0), 1 => (2), 2 => (3), 3 => (), 4 => (5), 5 => (), 0 => ()
// then the source_list_categories is array(-1, 1,2,3, 4,5, 0),
// and the parent_list_categories is  array(-1,-1,1,2,-1,4,-1),
// and the function returns		array( 0, 0,1,2, 0,4, 0).
function photoplog_make_source_key_of_parent_list(&$source_list_categories,&$parent_list_categories)
{
	$parent_keys = array();
	$source_dupe = $source_list_categories;
	foreach ($parent_list_categories as $parent_key => $parent_cat)
	{
		$parent_keys[$parent_key] = 0;
		foreach ($source_list_categories as $source_key => $source_cat)
		{
			if ($parent_cat == $source_cat)
			{
				$parent_keys[$parent_key] = $source_key;
				break;
			}
		}
	}
	return $parent_keys;
}

// ################### Start photoplog_fetch_ds_cat ######################
// Returns the category datastore
function photoplog_fetch_ds_cat()
{
	global $vbulletin;

	$ds_catopts = array();
	if (defined('TABLE_PREFIX'))
	{
		if ($ds_catquery = $vbulletin->db->query_first("SELECT data
			FROM " . TABLE_PREFIX . "datastore WHERE title = 'photoplog_dscat'")
		)
		{
			$ds_catopts = unserialize($ds_catquery['data']);

			if (!is_array($ds_catopts))
			{
				$ds_catopts = array();
			}
		}
	}
	return $ds_catopts;
}

// ################### Start photoplog_insert_category ###################
// Inserts a category into the database and updates the datastore.  The current category datastore
// must be passed in as the last argument.
function photoplog_insert_category($title,$description,$displayorder,$parentid,$options,&$ds_catopts,$suggest_id=0)
{
	global $vbulletin;

	$cat_id = 0;
	$insert_sql = "INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_categories
		(title, description, displayorder, parentid, options)
		VALUES (
			'".$vbulletin->db->escape_string($title)."',
			'".$vbulletin->db->escape_string($description)."',
			".intval($displayorder).",
			".intval($parentid).",
			".intval($options)."
		)
	";

	if ($vbulletin->db->query_write($insert_sql))
	{
		$cat_id = $vbulletin->db->insert_id();
		$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_categories
			ORDER BY parentid, displayorder, catid
		");
	}
	if ($cat_id > 0)
	{
		$cat_datastore = array(
				'title' => $title,
				'description' => $description,
				'displayorder' => $displayorder,
				'parentid' => $parentid,
				'options' => $options
		);

		$ds_catopts[$cat_id] = $cat_datastore;
		build_datastore('photoplog_dscat', serialize($ds_catopts));
		$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_catcounts
			(catid,moderate)
			VALUES (".intval($cat_id).",0), (".intval($cat_id).",1)
		");
	}

	photoplog_import_fields_from_parent($cat_id, $parentid);

	if ($cat_id && $suggest_id)
	{
		$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
			SET catid = ".intval($cat_id).", approve = 1
			WHERE suggestid = ".intval($suggest_id)."
		");
	}

	return $cat_id;
}

// ################ Start photoplog_replace_into_category ################
// Replace a category into the database and updates the datastore.  The current category datastore
// must be passed in as the last argument.
function photoplog_replace_into_category($catid,$title,$description,$displayorder,$parentid,$options,&$ds_catopts)
{
	global $vbulletin;

	$old_catid = intval($catid);
	$old_parentid = $ds_catopts["$old_catid"]['parentid'];

	$cat_id = 0;
	$replace_sql = "REPLACE INTO " . PHOTOPLOG_PREFIX . "photoplog_categories
		(catid, title, description, displayorder, parentid, options)
		VALUES (
			".intval($catid).",
			'".$vbulletin->db->escape_string($title)."',
			'".$vbulletin->db->escape_string($description)."',
			".intval($displayorder).",
			".intval($parentid).",
			".intval($options)."
		)
	";

	if ($vbulletin->db->query_write($replace_sql))
	{
		$cat_id = intval($catid);
		$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_categories
			ORDER BY parentid, displayorder, catid
		");
	}
	if ($cat_id > 0)
	{
		$cat_datastore = array(
				'title' => $title,
				'description' => $description,
				'displayorder' => $displayorder,
				'parentid' => $parentid,
				'options' => $options
		);

		$ds_catopts["$cat_id"] = $cat_datastore;
		build_datastore('photoplog_dscat', serialize($ds_catopts));

	}
	if ($parentid != $old_parentid)
	{
		photoplog_move_custom_fields($catid,$old_parentid,$parentid);
	}

	return $cat_id;
}

// ##################### Start photoplog_set_abort #######################
// A small utility function for handling multiple abort conditions and messages
function photoplog_set_abort($abort_message)
{
	global $photoplog_abort, $photoplog_abort_message;

	if (!$photoplog_abort)
	{
		$photoplog_abort = true;
		$photoplog_abort_message = $abort_message;
	}
}

// ################### Start photoplog_fetch_username ####################
// Returns a username for a given id
function photoplog_fetch_username($id)
{
	$user = fetch_userinfo(intval($id));
	$uname = (isset($user['username'])) ? $user['username'] : '';
	return $uname;
}

// ################# Start photoplog_get_category_title ##################
// Returns category title
function photoplog_get_category_title($catid)
{
	global $vbulletin;

	$result = '';

	$categories = $vbulletin->db->query_first("SELECT title
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
		WHERE catid = " . intval($catid) ."
	");
	if ($categories && isset($categories['title']))
	{
		$result = $categories['title'];
	}
	$vbulletin->db->free_result($categories);

	return $result;
}

// ################### Start photoplog_check_category ####################
// Returns true if and only if the given category id is valid
function photoplog_check_category($catid)
{
	global $vbulletin;

	$categories = $vbulletin->db->query_first("SELECT COUNT(*) as num
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
		WHERE catid = " . intval($catid)
	);
	$result = ($categories && ($categories['num'] > 0));
	$vbulletin->db->free_result($categories);

	return $result;
}

// ################ Start photoplog_count_category_files #################
// Returns the number of fileuploads for the given category
function photoplog_count_category_files($catid)
{
	global $vbulletin;

	$categories = $vbulletin->db->query_first("SELECT COUNT(*) as num
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE catid = " . intval($catid)
	);
	$num = ($categories) ? $categories['num'] : 0;
	$vbulletin->db->free_result($categories);

	return $num;
}

// ##################### Start photoplog_checkdate #######################
// Calls the php function checkdate to see whether the given datearray date is valid.
function photoplog_checkdate($datearray)
{
	return checkdate($datearray['month'], $datearray['day'], $datearray['year']);
}

// #################### Start photoplog_partial_date #####################
// Returns true if an invalid partial date is provided.
function photoplog_partial_date($datearray)
{
	return (!photoplog_checkdate($datearray) &&
		($datearray['month'] + $datearray['day'] + $datearray['year'] + $datearray['minute'] + $datearray['hour'] > 0));
}

// ################### Start photoplog_get_dateline ######################
// Returns a dateline for the provided datearray.
function photoplog_get_dateline($datearray)
{
	global $vbulletin;

	if ($datearray['hour'] < 0)
	{
		$datearray['hour'] = 0;
	}
	if ($datearray['minute'] < 0)
	{
		$datearray['minute'] = 0;
	}

	$mkdatetime = mktime($datearray['hour'], $datearray['minute'], 0,
		$datearray['month'], $datearray['day'], $datearray['year']);

	// adjust so that the date input matches what the admin will see on their screen.

	return $mkdatetime + $vbulletin->options['hourdiff'];
}

// ################ Start photoplog_add_interval_condition ###############
// Used to construct an array of sql conditions.  This function adds an interval
// condition to the provided conditions array
function photoplog_add_interval_condition(&$conditions,$at_least,$at_most,$column)
{
	if ($at_least >= 0)
	{
		if ($at_most >= 0)
		{
			$conditions[] = $column . " BETWEEN " . intval($at_least) . " AND " . intval($at_most);
		}
		else
		{
			$conditions[] = $column . " >= " . intval($at_least);
		}
	}
	else
	{
		if ($at_most >= 0)
		{
			$conditions[] = $column . " <= " . intval($at_most);
		}
	}
}

// ################# Start photoplog_sql_in_list_implode #################
// Returns a sql condition for a column in an array
function photoplog_sql_in_list_implode ($col,$list_array)
{
	$list_copy = $list_array;

	foreach ($list_array AS $key => $value)
	{
		$list_copy[$key] = intval($value);
	}
	$output = $col . " IN (" . implode(",", $list_copy) . ")";

	return $output;
}

// ################## Start photoplog_ensure_directory ###################
// Checks if the directory exists.  If not, attempts to create it.
function photoplog_ensure_directory($dir)
{
	$okay = true;
	if (!is_dir($dir))
	{
		@mkdir($dir,0777);
		@chmod($dir,0777);

		if ($handle = @fopen($dir."/index.html","w"))
		{
			@fwrite($handle,'');
			@fclose($handle);
		}
		else
		{
			$okay = false;
		}
	}

	return $okay;
}

// ##################### Start photoplog_physical_move ###################
// moves a physical file.  Note that the directories should not have a trailing forward slash.
function photoplog_physical_move($source_directory,$source_file_name,$destination_directory,$destination_file_name)
{
	if (photoplog_ensure_directory($destination_directory))
	{
		copy($source_directory."/".$source_file_name,
			$destination_directory."/".$destination_file_name);
		@unlink($source_directory."/".$source_file_name);
	}
}

// #################### Start photoplog_rebuild_thumbs ###################
// rebuilds thumbnails
function photoplog_rebuild_thumbs($file_info, $file_dir, $file_name, $new_size, $jpg_qual, $sub_dir, $rebuild = false)
{
	$file_loc = $file_dir."/".$file_name;

	$size_arr = explode(",",str_replace(" ","",$new_size));

	if (count($size_arr) == 2)
	{
		$new_w = intval($size_arr[0]);
		$new_h = intval($size_arr[1]);
	}
	else
	{
		return false;
	}

	if ($file_info[2] == '1')
	{
		if (imagetypes() & IMG_GIF)
		{
			$old_img = @imagecreatefromgif($file_loc);
		}
	}

	if ($file_info[2] == '2')
	{
		if (imagetypes() & IMG_JPG)
		{
			$old_img = @imagecreatefromjpeg($file_loc);
		}
	}

	if ($file_info[2] == '3')
	{
		if (imagetypes() & IMG_PNG)
		{
			$old_img = @imagecreatefrompng($file_loc);
		}
	}

	if (!$old_img)
	{
		return false;
	}
	else
	{
		$orig_w = max(1,$file_info[0]);
		$orig_h = max(1,$file_info[1]);
		$new_w = max(1,$new_w);
		$new_h = max(1,$new_h);

		$ratio_w = $new_w / $orig_w;
		$ratio_h = $new_h / $orig_h;
		$ratio_m = min($ratio_w,$ratio_h);

		if ($ratio_m >= 1)
		{
			$use_w = $orig_w;
			$use_h = $orig_h;
		}
		else
		{
			$use_w = round($orig_w * $ratio_m, 0);
			$use_h = round($orig_h * $ratio_m, 0);
		}
/*
		if ($file_info[2] == '2')
		{
			$new_img = @imagecreatetruecolor($use_w,$use_h);
		}
		else
		{
			$new_img = @imagecreate($use_w,$use_h);
			$black = @imagecolorallocate($new_img,0,0,0);
			@imagecolortransparent($new_img,$black);
		}
*/
		if ($file_info[2] == '2' || $file_info[2] == '3')
		{
			$new_img = @imagecreatetruecolor($use_w,$use_h);

			if ($file_info[2] == '3')
			{
				@imagealphablending($new_img, false);
				@imagesavealpha($new_img, true);
				$black = @imagecolorallocate($new_img,0,0,0);
				@imagecolortransparent($new_img,$black);
			}
		}
		else
		{
			$new_img = @imagecreate($use_w,$use_h);
			$black = @imagecolorallocate($new_img,0,0,0);
			@imagecolortransparent($new_img,$black);
		}

		if (!$new_img)
		{
			return false;
		}

		$out_img = @imagecopyresampled($new_img,$old_img,0,0,0,0,$use_w,$use_h,$orig_w,$orig_h);

		if (!$out_img)
		{
			return false;
		}

		$new_dir = $file_dir."/".$sub_dir;

		if (!is_dir($new_dir))
		{
			@mkdir($new_dir,0777);
			@chmod($new_dir,0777);

			if ($photoplog_handle = @fopen($new_dir."/index.html","w"))
			{
				$photoplog_blank = '';
				@fwrite($photoplog_handle,$photoplog_blank);
				@fclose($photoplog_handle);
			}
			else
			{
				return false;
			}
		}

		$file_loc = $new_dir."/".$file_name;

		if ($rebuild)
		{
			@unlink($file_loc);
		}

		$response = false;

		if ($file_info[2] == '1')
		{
			$response = imagegif($new_img,$file_loc);
		}

		if ($file_info[2] == '2')
		{
			$jpg_qual = intval($jpg_qual);
			if ($jpg_qual < 0 || $jpg_qual > 100)
			{
				$jpg_qual = 75;
			}
			$response = imagejpeg($new_img,$file_loc,$jpg_qual);
		}

		if ($file_info[2] == '3')
		{
			$response = imagepng($new_img,$file_loc);
		}

		@imagedestroy($old_img);
		@imagedestroy($new_img);

		return $response;
	}
}

// ################# Start photoplog_delete_custom_field #################
// delete one upload field based on fieldid and catid.
function photoplog_delete_custom_field($fieldid, $catid)
{
	global $vbulletin;

	$vbulletin->db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
		WHERE fieldid = " . intval($fieldid) . " AND catid = " . intval($catid) . "
	");
}

// ############ Start photoplog_delete_custom_field_recursive ############
// delete an upload field based on fieldid and catid, and any field offspring.
function photoplog_delete_custom_field_recursive($child_list,$fieldid, $catid, $groupid)
{
	global $vbulletin;

	photoplog_delete_custom_field($fieldid, $catid);
	photoplog_delete_children_that_inherit($child_list, $catid, $groupid);
}

// ############ Start photoplog_delete_children_that_inherit #############
function photoplog_delete_children_that_inherit($child_list, $catid, $groupid)
{
	global $vbulletin;

	if (!empty($child_list))
	{
		$vbulletin->db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid IN (" . implode(",",$child_list) . ") AND inherited = 1
			AND parentid = " . intval($catid) . " AND groupid = " . intval($groupid) . "
		");
	}
}

// ######### Start photoplog_delete_children_fields_with_groupid #########
function photoplog_delete_children_fields_with_groupid($child_list, $catid, $groupid)
{
	global $vbulletin;

	if (!empty($child_list))
	{
		$vbulletin->db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid IN (" . implode(",",$child_list) . ")
			AND catid != " . intval($catid) . " AND groupid = " . intval($groupid) . "
		");
	}
}

// ################# Start photoplog_update_custom_field #################
// updates the hidden, active, inherited, parentid and info columns
function photoplog_update_custom_field($fieldid, $catid, $groupid, $hidden, $active,
	$inherited, $parentid, $info)
{
	global $vbulletin;

	$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_customfields
		SET hidden = " . intval($hidden) . ", active = " . intval($active) . ",
			inherited = " . intval($inherited) . ", parentid = " . intval($parentid) . ",
			info = '" . $vbulletin->db->escape_string($info) . "'
		WHERE fieldid = " . intval($fieldid) . " AND catid = " . intval($catid) . "
		AND groupid = " . intval($groupid) . "
	");
}

// ################# Start photoplog_insert_custom_field #################
function photoplog_insert_custom_field($catid, $groupid, $displayorder, $hidden, $active,
	$protected, $inherited, $parentid, $info)
{
	global $vbulletin;

	$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_customfields
		(catid, groupid, displayorder, hidden, active, protected, inherited, parentid, info)
		VALUES (
			" . intval($catid) . ",
			" . intval($groupid) . ",
			" . intval($displayorder) . ",
			" . intval($hidden) . ",
			" . intval($active) . ",
			" . intval($protected) . ",
			" . intval($inherited) . ",
			" . intval($parentid) . ",
			'" . $vbulletin->db->escape_string($info) . "'
		)
	");
}

// ############# Start photoplog_custom_field_delete_category ############
function photoplog_custom_field_delete_category($catid,$child_list)
{
	global $vbulletin;

	$child_list[] = intval($catid);

	$vbulletin->db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid IN (" . implode(",",$child_list) . ")
	");
}

// ############ Start photoplog_update_custom_field_recursive ############
// updates the hidden, active, inherited, parentid and info columns
// then, if it is passed on to its children, updates or inserts the field appropriately.
// if it is not passed on to its children, then it deletes any child offspring.
function photoplog_update_custom_field_recursive($child_list,$fieldid,$catid, $groupid,
	$displayorder, $hidden, $active, $inherited, $parentid, $info)
{
	global $vbulletin;

	photoplog_update_custom_field($fieldid, $catid, $groupid, $hidden, $active,
		$inherited, $parentid, $info);

	if (($inherited == -1) && !empty($child_list))
	{
		$child_field_ids = array();
		$child_cat_ids = array();

		$fields = $vbulletin->db->query_read("SELECT fieldid, catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid IN (" . implode(",",$child_list) . ")
			AND parentid = " . intval($catid) . "
			AND inherited = 1
			AND groupid = " . intval($groupid) . "
		");
		while ($field = $vbulletin->db->fetch_array($fields))
		{
			$child_field_ids[] = intval($field['fieldid']);
			$child_cat_ids[] = intval($field['catid']);
		}
		$vbulletin->db->free_result($fields);

		if (!empty($child_field_ids) && !empty($child_cat_ids))
		{
			$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_customfields
				SET hidden = " . intval($hidden) . ",
					active = " . intval($active) . ",
					info = ''
				WHERE fieldid IN (" . implode(",",$child_field_ids) . ")
				AND catid IN (" . implode(",",$child_cat_ids) . ")
				AND groupid = " . intval($groupid) . "
			");
		}

		$child_displayorders = array();
		$fields_displayorders = $vbulletin->db->query_read("SELECT catid,displayorder
			FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid IN (" . implode(",",$child_list) . ")
			ORDER BY displayorder ASC
		");
		while ($field = $vbulletin->db->fetch_array($fields_displayorders))
		{
			$field_catid = intval($field['catid']);
			$child_displayorders[$field_catid][] = intval($field['displayorder']);
		}
		$vbulletin->db->free_result($fields_displayorders);

		$insert_displayorders = array();
		foreach ($child_list AS $child_id)
		{
			$child_displayorder = $displayorder;
			while (in_array($child_displayorder,$child_displayorders[$child_id]))
			{
				$child_displayorder++;
			}
			$insert_displayorders[$child_id] = $child_displayorder;
		}

		foreach ($child_list AS $child_id)
		{
			if (!in_array($child_id,$child_cat_ids))
			{
				photoplog_insert_custom_field($child_id, $groupid, $insert_displayorder[$child_id],
					$hidden, $active, 1, 1, $catid, '');
			}
		}
	}

	if ($inherited == 0)
	{
		// not passed on to children
		photoplog_delete_children_that_inherit($child_list, $catid, $groupid);
	}
}

// ############ Start photoplog_insert_custom_field_recursive ############
// inserts a new field.  if it is passed on, then it passes it on.  if not, stray children are eliminated.
function photoplog_insert_custom_field_recursive($child_list, $catid, $groupid, $displayorder, $hidden,
	$active, $protected, $inherited, $parentid, $info)
{
	global $vbulletin;

	photoplog_insert_custom_field($catid, $groupid, $displayorder, $hidden,
		$active, $protected, $inherited, $parentid, $info);

	if (($inherited == -1) && !empty($child_list))
	{
		// passed on to children

		// liberal cleanup operation
		photoplog_delete_children_fields_with_groupid($child_list, $catid, $groupid);

		$child_displayorders = array();
		foreach ($child_list AS $child_id)
		{
			$child_displayorders[$child_id] = array();
		}

		$fields = $vbulletin->db->query_read("SELECT catid,displayorder
			FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid IN (" . implode(",",$child_list) . ")
			ORDER BY displayorder ASC
		");
		while ($field = $vbulletin->db->fetch_array($fields))
		{
			$field_catid = intval($field['catid']);
			$child_displayorders[$field_catid][] = intval($field['displayorder']);
		}
		$vbulletin->db->free_result($fields);

		foreach ($child_list AS $child_id)
		{
			$child_displayorder = $displayorder;
			while (in_array($child_displayorder,$child_displayorders[$child_id]))
			{
				$child_displayorder++;
			}
			photoplog_insert_custom_field($child_id, $groupid, $child_displayorder,
				$hidden, $active, 1, 1, $catid, '');
		}
	}

	if ($inherited == 0)
	{
		// not passed on to children
		// just a cleanup operation
		photoplog_delete_children_that_inherit($child_list, $catid, $groupid);
	}
}

// ################## Start photoplog_move_custom_fields #################
function photoplog_move_custom_fields($catid,$old_parentid,$new_parentid)
{
	global $vbulletin;

	$list_immediate_children = array();
	$list_all_children = array();
	photoplog_relative_list($list_immediate_children, $list_all_children);
	$child_list = $list_all_children[$catid];
	$catid = intval($catid);
	$family_list = array_merge(array($catid),$child_list);

	foreach ($family_list AS $family_catid)
	{
		$fields = $vbulletin->db->query_read("SELECT parentid,groupid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid = " . intval($family_catid) . "
			AND inherited = 1
			AND parentid > -1
		");
		while ($field = $vbulletin->db->fetch_array($fields))
		{
			$field_parentid = intval($field['parentid']);
			if (!in_array($family_catid,$list_all_children[$field_parentid]))
			{
				$family_child_list = array($family_catid);
				if (isset($list_all_children[$family_catid]))
				{
					$family_child_list = array_merge($family_child_list,$list_all_children[$family_catid]);
				}
				$group_id = intval($field['groupid']);
				$vbulletin->db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
					WHERE catid IN (" . implode(",",$family_child_list) . ")
					AND inherited = 1
					AND parentid > -1
					AND groupid = " . intval($group_id) . "
				");
			}
		}
		$vbulletin->db->free_result($fields);
	}

	$substitute_displayorders = array();
	$displayorder_query = $vbulletin->db->query_read("SELECT catid, MAX(displayorder) AS mdo
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
		WHERE catid IN (" . implode(",",$family_list) . ")
		GROUP BY catid
	");
	while ($displayorder_read = $vbulletin->db->fetch_array($displayorder_query))
	{
		$displayorder_read['catid'] = intval($displayorder_read['catid']);
		$substitute_displayorders[$displayorder_read['catid']] = intval($displayorder_read['mdo']);
	}
	$vbulletin->db->free_result($displayorder_query);

	$parents = $vbulletin->db->query_read("SELECT groupid,
			parentid, displayorder, hidden, active
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
		WHERE catid = " . intval($new_parentid) . "
		AND inherited != 0
		AND parentid > -1
	");
	while ($parent = $vbulletin->db->fetch_array($parents))
	{
		$parent_group_id = intval($parent['groupid']);
		$parent_parentid = intval($parent['parentid']);

		$vbulletin->db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid IN (" . implode(",",$family_list) . ")
			AND inherited < 1
			AND parentid > -1
			AND groupid = " . intval($parent_group_id) . "
		");

		$check_cats = $vbulletin->db->query_read("SELECT catid, COUNT(*) as cnt
			FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE catid IN (" . implode(",",$family_list) . ")
			AND inherited = 1
			AND parentid > -1
			AND groupid = " . intval($parent_group_id) . "
			GROUP BY catid
		");
		while ($check_one = $vbulletin->db->fetch_array($check_cats))
		{
			if ($check_one['cnt'] == 0)
			{
				$vbulletin->db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
					WHERE catid = " . intval($check_one['catid']) . "
					AND groupid = " . intval($parent_group_id) . "
				");
				$new_displayorder = (isset($substitute_displayorders[$check_one['catid']])) ?
					$substitute_displayorders[$check_one['catid']] + 10 : $parent['displayorder'];
				photoplog_insert_custom_field($check_one['catid'], $parent_group_id, $new_displayorder,
					$parent['hidden'], $parent['active'], 1, 1, $parent_parentid, '');
			}
		}
		$vbulletin->db->free_result($check_cats);
	}
	$vbulletin->db->free_result($parents);
}

// ############## Start photoplog_import_fields_from_parent ##############
// assumes that $catid has a clean slate. No fields and no child fields.
// that is, $catid was just created!
function photoplog_import_fields_from_parent($catid, $parentid)
{
	global $vbulletin;

	$parent_fields = $vbulletin->db->query_read("SELECT groupid,
		displayorder, hidden, active, protected, inherited, parentid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
		WHERE catid = " . intval($parentid) . "
		AND inherited != 0
	");
	while ($parent_field = $vbulletin->db->fetch_array($parent_fields))
	{
		photoplog_insert_custom_field($catid, $parent_field['groupid'], $parent_field['displayorder'],
			$parent_field['hidden'], $parent_field['active'], 1, 1, $parent_field['parentid'], '');
	}
	$vbulletin->db->free_result($parent_fields);
}

// ############## Start photoplog_update_fields_displayorder #############
function photoplog_update_fields_displayorder($displayorder, $catid)
{
	global $vbulletin;

	foreach ($displayorder AS $displayorder_key => $displayorder_value)
	{
		$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_customfields
			SET displayorder = " . intval($displayorder_value) . "
			WHERE fieldid = " . intval($displayorder_key) . "
			AND catid = " . intval($catid) . "
		");
	}
}

// ################### Start photoplog_get_field_data ####################
function photoplog_get_field_data($catid, $fieldid)
{
	global $vbulletin;

	$result = '';

	$photoplog_field = $vbulletin->db->query_first("SELECT t1.*, t2.name
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields AS t1,
			" . PHOTOPLOG_PREFIX . "photoplog_customgroups AS t2
		WHERE t1.fieldid = " . intval($fieldid) . "
		AND t2.groupid = t1.groupid
		AND t1.catid = " . intval($catid) . "
	");
	if ($photoplog_field)
	{
		$result = $photoplog_field;
	}
	$vbulletin->db->free_result($photoplog_field);

	return $result;
}

// ################### Start photoplog_get_all_groups ####################
function photoplog_get_all_groups()
{
	global $vbulletin;

	$result = array();

	$groups = $vbulletin->db->query_read("SELECT *
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customgroups
		ORDER BY name ASC
	");
	while ($group = $vbulletin->db->fetch_array($groups))
	{
		$result[$group['groupid']] = $group['name'];
	}
	$vbulletin->db->free_result($groups);

	return $result;
}

// ################# Start photoplog_get_groups_by_catid #################
function photoplog_get_groups_by_catid($catid)
{
	global $vbulletin;

	$result = array();

	$fields = $vbulletin->db->query_read("SELECT groupid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
		WHERE catid = " . intval($catid) . "
	");
	while ($field = $vbulletin->db->fetch_array($fields))
	{
		$result[] = intval($field['groupid']);
	}
	$vbulletin->db->free_result($fields);

	return $result;
}

// ################# Start photoplog_get_groups_and_cats #################
function photoplog_get_groups_and_cats()
{
	global $vbulletin;

	$result = array();

	$fields = $vbulletin->db->query_read("SELECT t1.groupid, t2.title
		FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields AS t1,
			" . PHOTOPLOG_PREFIX . "photoplog_categories AS t2
		WHERE t1.catid = t2.catid
	");
	while ($field = $vbulletin->db->fetch_array($fields))
	{
		$field['groupid'] = intval($field['groupid']);
		$result[$field['groupid']][] = strval($field['title']);
	}
	$vbulletin->db->free_result($fields);

	return $result;
}

// ############### Start photoplog_make_available_groups #################
function photoplog_make_available_groups($catid)
{
	global $vbphrase;

	$catid = intval($catid);
	$all_groups = photoplog_get_all_groups();
	$groups_by_catid = photoplog_get_groups_by_catid($catid);
	$groups_and_cats = photoplog_get_groups_and_cats();

	$result = array();

	foreach ($all_groups AS $id => $name)
	{
		if (!in_array($id,$groups_by_catid))
		{
			$catlist = $vbphrase['photoplog_none'];
			if (isset($groups_and_cats[$id]) && is_array($groups_and_cats[$id]))
			{
				$catlist = implode(", ",$groups_and_cats[$id]);
			}
			$result[$id] = "<strong>" . $name . "</strong> (" . $vbphrase['photoplog_used_by_categories'] . " " . $catlist . ")";
		}
	}

	return $result;
}

// ############## Start photoplog_insert_customfield_group ###############
function photoplog_insert_customfield_group($name)
{
	global $vbulletin;

	$group_query = $vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_customgroups (name)
		VALUES ('" . $vbulletin->db->escape_string($name) . "')
	");
	$groupid = $vbulletin->db->insert_id();
	$vbulletin->db->free_result($group_query);

	return $groupid;
}

// ################# Start photoplog_update_counts_table #################
// updates the counts in photoplog_catcounts for a single catid
function photoplog_update_counts_table($catid)
{
	global $vbulletin;

	$count_updates = array();
	$count_updates[0] = array();
	$count_updates[1] = array();
	$catid_where = "catid = ".intval($catid);

	$filequery = $vbulletin->db->query_read("SELECT moderate,
		COUNT(fileid) AS num_uploads,
		MAX(dateline) AS last_upload_dateline,
		MAX(fileid) AS last_upload_id,
		SUM(views) AS num_views,
		SUM(filesize) AS sum_filesize
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE $catid_where
		GROUP BY moderate
	");
	while($file = $vbulletin->db->fetch_array($filequery))
	{
		$moderate = intval($file['moderate']);
		$count_updates[$moderate] = $file;
	}
	$vbulletin->db->free_result($filequery);

	$ratequery1 = $vbulletin->db->query_read("SELECT moderate,
		SUM(IF(rating > 0,1,0)) AS num_ratings,
		SUM(rating) AS sum_ratings
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE $catid_where
		GROUP BY moderate
	");
	while($rate1 = $vbulletin->db->fetch_array($ratequery1))
	{
		$moderate = intval($rate1['moderate']);
		$count_updates[$moderate] = array_merge($count_updates[$moderate],$rate1);
	}
	$vbulletin->db->free_result($ratequery1);

	$ratequery2 = $vbulletin->db->query_read("SELECT moderate,
		COUNT(commentid) AS num_comments,
		MAX(dateline) AS last_comment_dateline,
		MAX(commentid) AS last_comment_id
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE $catid_where
		AND comment != ''
		GROUP BY moderate
	");
	while($rate2 = $vbulletin->db->fetch_array($ratequery2))
	{
		$moderate = intval($rate2['moderate']);
		$count_updates[$moderate] = array_merge($count_updates[$moderate],$rate2);
	}
	$vbulletin->db->free_result($ratequery2);

	foreach(array('num_uploads','num_views','sum_filesize','num_comments','num_ratings','sum_ratings') AS $key)
	{
		if (!isset($count_updates[0][$key])) $count_updates[0][$key] = 0;
		if (!isset($count_updates[1][$key])) $count_updates[1][$key] = 0;

		$count_updates[0][$key] = intval($count_updates[0][$key]);
		$count_updates[1][$key] = intval($count_updates[1][$key]) + intval($count_updates[0][$key]);
	}
	foreach(array('last_upload_dateline','last_upload_id','last_comment_dateline','last_comment_id') AS $key)
	{
		if (!isset($count_updates[0][$key])) $count_updates[0][$key] = 0;
		if (!isset($count_updates[1][$key])) $count_updates[1][$key] = 0;

		$count_updates[0][$key] = intval($count_updates[0][$key]);
		$count_updates[1][$key] = max(intval($count_updates[1][$key]), intval($count_updates[0][$key]));
	}

	$lastids = $vbulletin->db->query_read("SELECT fileid, dateline, moderate
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE $catid_where
		AND dateline IN (".intval($count_updates[0]['last_upload_dateline']).",".intval($count_updates[1]['last_upload_dateline']).")
	");
	while($lastid = $vbulletin->db->fetch_array($lastids))
	{
		if (
			($lastid['dateline'] == $count_updates[0]['last_upload_dateline']) &&
			($lastid['fileid'] != $count_updates[0]['last_upload_id']) &&
			($lastid['moderate'] == 0)
		)
		{
			$count_updates[0]['last_upload_id'] = intval($lastid['fileid']);
		}
		if (
			($lastid['dateline'] == $count_updates[1]['last_upload_dateline']) &&
			($lastid['fileid'] != $count_updates[1]['last_upload_id'])
		)
		{
			$count_updates[1]['last_upload_id'] = intval($lastid['fileid']);
		}
	}
	$vbulletin->db->free_result($lastids);

	$lastids = $vbulletin->db->query_read("SELECT commentid, dateline, moderate
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE $catid_where
		AND dateline IN (".intval($count_updates[0]['last_comment_dateline']).",".intval($count_updates[1]['last_comment_dateline']).")
		AND comment != ''
	");
	while($lastid = $vbulletin->db->fetch_array($lastids))
	{
		if (
			($lastid['dateline'] == $count_updates[0]['last_comment_dateline']) &&
			($lastid['commentid'] != $count_updates[0]['last_comment_id']) &&
			($lastid['moderate'] == 0)
		)
		{
			$count_updates[0]['last_comment_id'] = intval($lastid['commentid']);
		}
		if (
			($lastid['dateline'] == $count_updates[1]['last_comment_dateline']) &&
			($lastid['commentid'] != $count_updates[1]['last_comment_id'])
		)
		{
			$count_updates[1]['last_comment_id'] = intval($lastid['commentid']);
		}
	}
	$vbulletin->db->free_result($lastids);

	foreach($count_updates AS $mod => $info)
	{
		$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
			SET num_uploads = ".intval($info['num_uploads']).",
			num_comments = ".intval($info['num_comments']).",
			num_ratings = ".intval($info['num_ratings']).",
			sum_ratings = ".intval($info['sum_ratings']).",
			last_upload_dateline = ".intval($info['last_upload_dateline']).",
			last_upload_id = ".intval($info['last_upload_id']).",
			last_comment_dateline = ".intval($info['last_comment_dateline']).",
			last_comment_id = ".intval($info['last_comment_id']).",
			num_views = ".intval($info['num_views']).",
			sum_filesize = ".intval($info['sum_filesize'])."
			WHERE $catid_where
			AND moderate = ".intval($mod)."
		");
	}
}

// ################# Start photoplog_maintain_counts #################
// function used to maintain counts, complete with javascript redirect
function photoplog_maintain_counts($photoplog_start,$photoplog_perpage,$photoplog_phase,$header_phrase,$redirect)
{
	global $vbulletin,$vbphrase;

	$photoplog_stop = intval($photoplog_start + $photoplog_perpage);

	print_table_start();
	print_table_header($header_phrase, 1);
	print_cells_row(array('<nobr>' . $vbphrase['photoplog_gallery_file_and_category_counts'] . '</nobr>'), 1, '', -1);

	$photoplog_table_name = ($photoplog_phase) ? 'photoplog_fileuploads' : 'photoplog_categories';

	$photoplog_msg = $vbphrase['photoplog_updating'].' - '.
				$vbphrase['photoplog_table'].' '.$photoplog_table_name.': '.
				$photoplog_start.' - '.$photoplog_stop;

	if ($photoplog_phase == 0)
	{
		photoplog_regenerate_counts_table($photoplog_start, $photoplog_stop);

		print_description_row($photoplog_msg, 0, 1);

		@flush();
		@ob_flush();

		print_table_footer();

		if (
			$photoplog_morecheck = $vbulletin->db->query_first("SELECT catid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
				WHERE catid >= ".intval($photoplog_stop)."
				ORDER BY catid ASC
				LIMIT 1")
		)
		{
			$photoplog_stop = intval($photoplog_morecheck['catid']);
			print_cp_redirect($redirect."?".$vbulletin->session->vars['sessionurl']."do=counts&phase=0&start=".$photoplog_stop."&perpage=".$photoplog_perpage, 1);
		}
		else
		{
			print_cp_redirect($redirect."?".$vbulletin->session->vars['sessionurl']."do=counts&phase=1&start=0&perpage=".$photoplog_perpage, 1);
		}
	}
	else
	{
		photoplog_update_fileuploads_counts_interval($photoplog_start,$photoplog_stop);

		print_description_row($photoplog_msg, 0, 1);

		@flush();
		@ob_flush();

		if (
			$photoplog_morecheck = $vbulletin->db->query_first("SELECT fileid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				WHERE fileid >= ".intval($photoplog_stop)."
				ORDER BY fileid ASC
				LIMIT 1")
		)
		{
			print_table_footer();
			$photoplog_stop = intval($photoplog_morecheck['fileid']);
			print_cp_redirect($redirect."?".$vbulletin->session->vars['sessionurl']."do=counts&phase=1&start=".$photoplog_stop."&perpage=".$photoplog_perpage, 1);
		}
		else
		{
			print_description_row('<strong>'.$vbphrase['photoplog_done'].'</strong>', 0, 1);
			print_table_footer();
			if ($redirect == 'photoplog_massmove.php')
			{
				print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=postbitcounts&massmove=1", 1);
			}
			else
			{
				print_cp_redirect("photoplog_maintain.php?".$vbulletin->session->vars['sessionurl']."do=update", 1);
			}
		}
	}
}

// ################# Start photoplog_regenerate_counts_table #################
// regenerates (inserts!) the photoplog_catcounts table for catids between start (inclusive) and stop (exclusive).
// if start = 0, then the table is created if not exists, and wiped clean
// if stop < start, then there is no upper bound for catid.
function photoplog_regenerate_counts_table($start,$stop)
{
	global $vbulletin;

	$catids = array();

	$stop_sql = (intval($stop) < intval($start)) ? '' : 'AND catid < '.intval($stop);

	$catsquery = $vbulletin->db->query_read("SELECT catid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
		WHERE catid >= ".intval($start)."
		".$stop_sql."
	");
	while ($catrow = $vbulletin->db->fetch_array($catsquery))
	{
		$catids[] = intval($catrow['catid']);
	}
	$vbulletin->db->free_result($catsquery);
	if ($start == 0)
	{
		$catids[] = 0;
	}
	$count_inserts = array();
	foreach($catids AS $catid)
	{
		$count_inserts["$catid"] = array();
		$count_inserts["$catid"][0] = array();
		$count_inserts["$catid"][1] = array();
	}
	if (!empty($catids))
	{
		$catwhere = "catid IN (".implode(",",$catids).")";

		$filequery = $vbulletin->db->query_read("SELECT moderate, catid,
			COUNT(fileid) AS num_uploads,
			MAX(dateline) AS last_upload_dateline,
			MAX(fileid) AS last_upload_id,
			SUM(views) AS num_views,
			SUM(filesize) AS sum_filesize
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE $catwhere
			GROUP BY moderate, catid
		");
		while ($file = $vbulletin->db->fetch_array($filequery))
		{
			$moderate = intval($file['moderate']);
			$catid = intval($file['catid']);
			$count_inserts["$catid"][$moderate] = $file;
		}
		$vbulletin->db->free_result($filequery);

		$ratequery1 = $vbulletin->db->query_read("SELECT moderate, catid,
			SUM(IF(rating > 0,1,0)) AS num_ratings,
			SUM(rating) AS sum_ratings
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			GROUP BY moderate, catid
		");
		while ($rate1 = $vbulletin->db->fetch_array($ratequery1))
		{
			$moderate = intval($rate1['moderate']);
			$catid = intval($rate1['catid']);
			$count_inserts["$catid"][$moderate] = array_merge($count_inserts["$catid"][$moderate],$rate1);
		}
		$vbulletin->db->free_result($ratequery1);

		$ratequery2 = $vbulletin->db->query_read("SELECT moderate, catid,
			COUNT(commentid) AS num_comments,
			MAX(dateline) AS last_comment_dateline,
			MAX(commentid) AS last_comment_id
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			AND comment != ''
			GROUP BY moderate, catid
		");
		while ($rate2 = $vbulletin->db->fetch_array($ratequery2))
		{
			$moderate = intval($rate2['moderate']);
			$catid = intval($rate2['catid']);
			$count_inserts["$catid"][$moderate] = array_merge($count_inserts["$catid"][$moderate],$rate2);
		}
		$vbulletin->db->free_result($ratequery2);

		$upload_datelines = array(1);
		$comment_datelines = array(1);
		foreach ($catids AS $catid)
		{
			foreach (array('num_uploads','num_views','sum_filesize','num_comments','num_ratings','sum_ratings') AS $key)
			{
				if (!isset($count_inserts["$catid"][0][$key])) $count_inserts["$catid"][0][$key] = 0;
				if (!isset($count_inserts["$catid"][1][$key])) $count_inserts["$catid"][1][$key] = 0;

				$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
				$count_inserts["$catid"][1][$key] = intval($count_inserts["$catid"][1][$key]) + intval($count_inserts["$catid"][0][$key]);
			}
			foreach (array('last_upload_dateline','last_upload_id','last_comment_dateline','last_comment_id') AS $key)
			{
				if (!isset($count_inserts["$catid"][0][$key])) $count_inserts["$catid"][0][$key] = 0;
				if (!isset($count_inserts["$catid"][1][$key])) $count_inserts["$catid"][1][$key] = 0;

				$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
				$count_inserts["$catid"][1][$key] = max(intval($count_inserts["$catid"][1][$key]), intval($count_inserts["$catid"][0][$key]));
			}
			$upload_datelines[] = intval($count_inserts["$catid"][0]['last_upload_dateline']);
			$upload_datelines[] = intval($count_inserts["$catid"][1]['last_upload_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][0]['last_comment_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][1]['last_comment_dateline']);
		}

		$lastids = $vbulletin->db->query_read("SELECT fileid, dateline, moderate, catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE $catwhere
			AND dateline IN (".implode(",",array_unique($upload_datelines)).")
		");
		while ($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$catid = intval($lastid['catid']);
			if (
				($lastid['dateline'] == $count_inserts["$catid"][0]['last_upload_dateline']) &&
				($lastid['fileid'] != $count_inserts["$catid"][0]['last_upload_id']) &&
				($lastid['moderate'] == 0)
			)
			{
				$count_inserts["$catid"][0]['last_upload_id'] = intval($lastid['fileid']);
			}
			if (
				($lastid['dateline'] == $count_inserts["$catid"][1]['last_upload_dateline']) &&
				($lastid['fileid'] != $count_inserts["$catid"][1]['last_upload_id'])
			)
			{
				$count_inserts["$catid"][1]['last_upload_id'] = intval($lastid['fileid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		$lastids = $vbulletin->db->query_read("SELECT commentid, dateline, moderate, catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			AND dateline IN (".implode(",",array_unique($comment_datelines)).")
			AND comment != ''
		");
		while ($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$catid = intval($lastid['catid']);
			if (
				($lastid['dateline'] == $count_inserts["$catid"][0]['last_comment_dateline']) &&
				($lastid['commentid'] != $count_inserts["$catid"][0]['last_comment_id']) &&
				($lastid['moderate'] == 0)
			)
			{
				$count_inserts["$catid"][0]['last_comment_id'] = intval($lastid['commentid']);
			}
			if (
				($lastid['dateline'] == $count_inserts["$catid"][1]['last_comment_dateline']) &&
				($lastid['commentid'] != $count_inserts["$catid"][1]['last_comment_id'])
			)
			{
				$count_inserts["$catid"][1]['last_comment_id'] = intval($lastid['commentid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		if ($start == 0)
		{
			$vbulletin->db->query_write("TRUNCATE TABLE " . PHOTOPLOG_PREFIX . "photoplog_catcounts");
		}

		$limit = 100;
		$values = array();
		foreach ($count_inserts AS $catid => $mod_array)
		{
			if (!isset($mod_array) || !is_array($mod_array))
			{
				$mod_array = array();
				$mod_array[0] = array();
				$mod_array[1] = array();
			}
			if (!isset($mod_array[0]) || !is_array($mod_array[0]))
			{
				$mod_array[0] = array();
			}
			if (!isset($mod_array[1]) || !is_array($mod_array[1]))
			{
				$mod_array[1] = array();
			}
			foreach ($mod_array AS $mod => $info)
			{
				$values[] = "(" . intval($catid) . ", " . intval($mod) . ", " . intval($info['num_uploads']) . ", " .
					intval($info['num_comments']) . ", " . intval($info['num_ratings']) . ", " .
					intval($info['sum_ratings']) . ", " . intval($info['last_upload_dateline']) . ", " .
					intval($info['last_upload_id']) . ", " . intval($info['last_comment_dateline']) . ", " .
					intval($info['last_comment_id']) . ", " . intval($info['num_views']) . ", " .
					intval($info['sum_filesize']) . ")";
			}
			if (count($values) >= $limit)
			{
				$valstr = implode(", ", $values);
				$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_catcounts
					(catid, moderate, num_uploads, num_comments, num_ratings, sum_ratings, last_upload_dateline,
					last_upload_id, last_comment_dateline, last_comment_id, num_views, sum_filesize)
					VALUES " . $valstr);
				$values = array();
			}
		}
		if (count($values) > 0)
		{
			$valstr = implode(", ", $values);
			$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_catcounts
				(catid, moderate, num_uploads, num_comments, num_ratings, sum_ratings, last_upload_dateline,
				last_upload_id, last_comment_dateline, last_comment_id, num_views, sum_filesize)
				VALUES " . $valstr);
		}
	}
}

// ############## Start photoplog_regenerate_counts_table_v2 #############
// regenerates (updates!) the photoplog_catcounts table for given catids
function photoplog_regenerate_counts_table_v2($ids)
{
	global $vbulletin;

	if (!is_array($ids))
	{
		$ids = array($ids);
	}
	$ids = array_unique($ids);
	$ids = array_map('intval', $ids);

	$catsquery = $vbulletin->db->query_read("SELECT catid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
		WHERE catid IN (".implode(',', $ids).")
	");

	$catids = array();
	while ($catrow = $vbulletin->db->fetch_array($catsquery))
	{
		$catids[] = intval($catrow['catid']);
	}
	$vbulletin->db->free_result($catsquery);

	if (!empty($catids))
	{
		$count_inserts = array();
		foreach ($catids AS $catid)
		{
			$count_inserts[$catid] = array();
			$count_inserts[$catid][0] = array();
			$count_inserts[$catid][1] = array();
		}

		$catwhere = "catid IN (".implode(",",$catids).")";

		$filequery = $vbulletin->db->query_read("SELECT moderate, catid,
			COUNT(fileid) AS num_uploads,
			MAX(dateline) AS last_upload_dateline,
			MAX(fileid) AS last_upload_id,
			SUM(views) AS num_views,
			SUM(filesize) AS sum_filesize
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE $catwhere
			GROUP BY moderate, catid
		");
		while ($file = $vbulletin->db->fetch_array($filequery))
		{
			$moderate = intval($file['moderate']);
			$catid = intval($file['catid']);
			$count_inserts["$catid"][$moderate] = $file;
		}
		$vbulletin->db->free_result($filequery);

		$ratequery1 = $vbulletin->db->query_read("SELECT moderate, catid,
			SUM(IF(rating > 0,1,0)) AS num_ratings,
			SUM(rating) AS sum_ratings
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			GROUP BY moderate, catid
		");
		while ($rate1 = $vbulletin->db->fetch_array($ratequery1))
		{
			$moderate = intval($rate1['moderate']);
			$catid = intval($rate1['catid']);
			$count_inserts["$catid"][$moderate] = array_merge($count_inserts["$catid"][$moderate],$rate1);
		}
		$vbulletin->db->free_result($ratequery1);

		$ratequery2 = $vbulletin->db->query_read("SELECT moderate, catid,
			COUNT(commentid) AS num_comments,
			MAX(dateline) AS last_comment_dateline,
			MAX(commentid) AS last_comment_id
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			AND comment != ''
			GROUP BY moderate, catid
		");
		while ($rate2 = $vbulletin->db->fetch_array($ratequery2))
		{
			$moderate = intval($rate2['moderate']);
			$catid = intval($rate2['catid']);
			$count_inserts["$catid"][$moderate] = array_merge($count_inserts["$catid"][$moderate],$rate2);
		}
		$vbulletin->db->free_result($ratequery2);

		$upload_datelines = array(1);
		$comment_datelines = array(1);
		foreach ($catids AS $catid)
		{
			foreach (array('num_uploads','num_views','sum_filesize','num_comments','num_ratings','sum_ratings') AS $key)
			{
				if (!isset($count_inserts["$catid"][0][$key])) $count_inserts["$catid"][0][$key] = 0;
				if (!isset($count_inserts["$catid"][1][$key])) $count_inserts["$catid"][1][$key] = 0;

				$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
				$count_inserts["$catid"][1][$key] = intval($count_inserts["$catid"][1][$key]) + intval($count_inserts["$catid"][0][$key]);
			}
			foreach (array('last_upload_dateline','last_upload_id','last_comment_dateline','last_comment_id') AS $key)
			{
				if (!isset($count_inserts["$catid"][0][$key])) $count_inserts["$catid"][0][$key] = 0;
				if (!isset($count_inserts["$catid"][1][$key])) $count_inserts["$catid"][1][$key] = 0;

				$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
				$count_inserts["$catid"][1][$key] = max(intval($count_inserts["$catid"][1][$key]), intval($count_inserts["$catid"][0][$key]));
			}
			$upload_datelines[] = intval($count_inserts["$catid"][0]['last_upload_dateline']);
			$upload_datelines[] = intval($count_inserts["$catid"][1]['last_upload_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][0]['last_comment_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][1]['last_comment_dateline']);
		}

		$lastids = $vbulletin->db->query_read("SELECT fileid, dateline, moderate, catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			WHERE $catwhere
			AND dateline IN (".implode(",",array_unique($upload_datelines)).")
		");
		while ($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$catid = intval($lastid['catid']);
			if (
				($lastid['dateline'] == $count_inserts["$catid"][0]['last_upload_dateline']) &&
				($lastid['fileid'] != $count_inserts["$catid"][0]['last_upload_id']) &&
				($lastid['moderate'] == 0)
			)
			{
				$count_inserts["$catid"][0]['last_upload_id'] = intval($lastid['fileid']);
			}
			if (
				($lastid['dateline'] == $count_inserts["$catid"][1]['last_upload_dateline']) &&
				($lastid['fileid'] != $count_inserts["$catid"][1]['last_upload_id'])
			)
			{
				$count_inserts["$catid"][1]['last_upload_id'] = intval($lastid['fileid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		$lastids = $vbulletin->db->query_read("SELECT commentid, dateline, moderate, catid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $catwhere
			AND dateline IN (".implode(",",array_unique($comment_datelines)).")
			AND comment != ''
		");
		while ($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$catid = intval($lastid['catid']);
			if (
				($lastid['dateline'] == $count_inserts["$catid"][0]['last_comment_dateline']) &&
				($lastid['commentid'] != $count_inserts["$catid"][0]['last_comment_id']) &&
				($lastid['moderate'] == 0)
			)
			{
				$count_inserts["$catid"][0]['last_comment_id'] = intval($lastid['commentid']);
			}
			if (
				($lastid['dateline'] == $count_inserts["$catid"][1]['last_comment_dateline']) &&
				($lastid['commentid'] != $count_inserts["$catid"][1]['last_comment_id'])
			)
			{
				$count_inserts["$catid"][1]['last_comment_id'] = intval($lastid['commentid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		foreach ($count_inserts AS $catid => $mod_array)
		{
			if (!isset($mod_array) || !is_array($mod_array))
			{
				$mod_array = array();
				$mod_array[0] = array();
				$mod_array[1] = array();
			}
			if (!isset($mod_array[0]) || !is_array($mod_array[0]))
			{
				$mod_array[0] = array();
			}
			if (!isset($mod_array[1]) || !is_array($mod_array[1]))
			{
				$mod_array[1] = array();
			}
			foreach ($mod_array AS $mod => $info) // two queries
			{
				$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
					SET num_uploads = ".intval($info['num_uploads']).",
						num_comments = ".intval($info['num_comments']).",
						num_ratings = ".intval($info['num_ratings']).",
						sum_ratings = ".intval($info['sum_ratings']).",
						last_upload_dateline = ".intval($info['last_upload_dateline']).",
						last_upload_id = ".intval($info['last_upload_id']).",
						last_comment_dateline = ".intval($info['last_comment_dateline']).",
						last_comment_id = ".intval($info['last_comment_id']).",
						num_views = ".intval($info['num_views']).",
						sum_filesize = ".intval($info['sum_filesize'])."
					WHERE catid = ".intval($catid)." AND moderate = ".intval($mod)."
				");
			}
		}
	}
}

// ################# Start photoplog_update_fileuploads_counts #################
// updates the counts in fileuploads for a single fileid
function photoplog_update_fileuploads_counts($fileid)
{
	$sql = "fileid = ".intval($fileid);
	photoplog_update_fileuploads_counts_sql($sql);
}

// ################# Start photoplog_update_fileuploads_counts_array #################
// updates the counts in fileuploads for a fileid array
function photoplog_update_fileuploads_counts_array($fileid_array)
{
	if (!empty($fileid_array) && is_array($fileid_array))
	{
		$fileid_array = array_map('intval', $fileid_array);
		$sql = "fileid IN (".implode(",",$fileid_array).")";
		photoplog_update_fileuploads_counts_sql($sql);
	}
}

// ################# Start photoplog_update_fileuploads_counts_interval #################
// updates the counts in fileuploads for fileids between start (inclusive) and stop (exclusive)
function photoplog_update_fileuploads_counts_interval($start,$stop)
{
	$sql = "fileid >= ".intval($start)." AND fileid < ".intval($stop);
	photoplog_update_fileuploads_counts_sql($sql);
}

// ################# Start photoplog_update_fileuploads_counts_sql #################
// updates the counts in fileuploads for fileids that satisfy the sql condition
function photoplog_update_fileuploads_counts_sql($fileid_sql)
{
	global $vbulletin;

	$file_updates = array();
	$filerows = array();

	$fileids = $vbulletin->db->query_read("SELECT fileid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE $fileid_sql
	");
	while ($filerow = $vbulletin->db->fetch_array($fileids))
	{
		$fileid = intval($filerow['fileid']);
		$file_updates[$fileid] = array();
		$filerows[$fileid] = array();
		$filerows[$fileid][0] = array();
		$filerows[$fileid][1] = array();
	}
	$vbulletin->db->free_result($fileids);

	if (!empty($filerows))
	{
		$files1 = $vbulletin->db->query_read("SELECT fileid, moderate,
			SUM(IF(rating > 0, 1, 0)) AS num_ratings,
			SUM(rating) AS sum_ratings
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $fileid_sql
			GROUP BY fileid, moderate
		");
		while ($filerow1 = $vbulletin->db->fetch_array($files1))
		{
			$fileid = intval($filerow1['fileid']);
			$moderate = intval($filerow1['moderate']);
			if (isset($filerows[$fileid]))
			{
				$filerows[$fileid][$moderate] = $filerow1;
			}
		}
		$vbulletin->db->free_result($files1);

		$files2 = $vbulletin->db->query_read("SELECT fileid, moderate,
			COUNT(commentid) AS num_comments,
			MAX(dateline) AS last_comment_dateline,
			MAX(commentid) AS last_comment_id
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $fileid_sql
			AND comment != ''
			GROUP BY fileid, moderate
		");
		$comment_datelines = array(1);
		while ($filerow2 = $vbulletin->db->fetch_array($files2))
		{
			$fileid = intval($filerow2['fileid']);
			$moderate = intval($filerow2['moderate']);
			if (isset($filerows[$fileid]))
			{
				$filerows[$fileid][$moderate] = array_merge($filerows[$fileid][$moderate], $filerow2);
				$comment_datelines[] = intval($filerow2['last_comment_id']);
			}
		}
		$vbulletin->db->free_result($files2);

		foreach ($filerows AS $fileid => $mod_array)
		{
			foreach (array('num_comments','num_ratings','sum_ratings') AS $key)
			{
				if (!isset($mod_array[0][$key])) $mod_array[0][$key] = 0;
				if (!isset($mod_array[1][$key])) $mod_array[1][$key] = 0;

				$file_updates[$fileid][$key."0"] = intval($mod_array[0][$key]);
				$file_updates[$fileid][$key."1"] = intval($mod_array[0][$key]) + intval($mod_array[1][$key]);
			}
			foreach (array('last_comment_dateline','last_comment_id') AS $key)
			{
				if (!isset($mod_array[0][$key])) $mod_array[0][$key] = 0;
				if (!isset($mod_array[1][$key])) $mod_array[1][$key] = 0;

				$file_updates[$fileid][$key."0"] = intval($mod_array[0][$key]);
				$file_updates[$fileid][$key."1"] = max(intval($mod_array[0][$key]),intval($mod_array[1][$key]));
			}
		}

		$lastids = $vbulletin->db->query_read("SELECT commentid, dateline, moderate, fileid
			FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
			WHERE $fileid_sql
			AND dateline IN (".implode(",",array_unique($comment_datelines)).")
			AND comment != ''
		");
		while($lastid = $vbulletin->db->fetch_array($lastids))
		{
			$fileid = intval($lastid['fileid']);
			if (
				($lastid['dateline'] == $file_updates[$fileid]['last_comment_dateline0']) &&
				($lastid['commentid'] != $file_updates[$fileid]['last_comment_id0']) &&
				($lastid['moderate'] == 0)
			)
			{
				$file_updates[$fileid]['last_comment_id0'] = intval($lastid['commentid']);
			}
			if (
				($lastid['dateline'] == $file_updates[$fileid]['last_comment_dateline1']) &&
				($lastid['commentid'] != $file_updates[$fileid]['last_comment_id1'])
			)
			{
				$file_updates[$fileid]['last_comment_id1'] = intval($lastid['commentid']);
			}
		}
		$vbulletin->db->free_result($lastids);

		foreach ($file_updates AS $fileid => $filerow)
		{
			$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				SET num_comments0 = " . intval($filerow['num_comments0']) . ",
				num_comments1 = " . intval($filerow['num_comments1']) . ",
				num_ratings0 = " . intval($filerow['num_ratings0']) . ",
				num_ratings1 = " . intval($filerow['num_ratings1']) . ",
				sum_ratings0 = " . intval($filerow['sum_ratings0']) . ",
				sum_ratings1 = " . intval($filerow['sum_ratings1']) . ",
				last_comment_dateline0 = " . intval($filerow['last_comment_dateline0']) . ",
				last_comment_dateline1 = " . intval($filerow['last_comment_dateline1']) . ",
				last_comment_id0 = " . intval($filerow['last_comment_id0']) . ",
				last_comment_id1 = " . intval($filerow['last_comment_id1']) . "
				WHERE fileid = " . intval($fileid)
			);
		}
	}
}

// ################# Start photoplog_maintain_postbitcounts #################
// function used to maintain postbit counts, complete with javascript redirect
function photoplog_maintain_postbitcounts($photoplog_start,$photoplog_perpage,$photoplog_phase,$header_phrase,$redirect)
{
	global $vbulletin,$vbphrase;

	$photoplog_stop = intval($photoplog_start + $photoplog_perpage);

	print_table_start();
	print_table_header($header_phrase, 1);
	print_cells_row(array('<nobr>' . $vbphrase['photoplog_postbit_file_and_comment_counts'] . '</nobr>'), 1, '', -1);

	$photoplog_table_name = ($photoplog_phase) ? 'photoplog_ratecomment' : 'photoplog_fileuploads';

	$photoplog_msg = $vbphrase['photoplog_updating'].' '.$photoplog_table_name.' - '.
				$vbphrase['photoplog_table'].' '.TABLE_PREFIX.'user: '.
				$photoplog_start.' - '.$photoplog_stop;

	photoplog_update_postbit_counts_interval($photoplog_start,$photoplog_stop,$photoplog_phase);

	print_description_row($photoplog_msg, 0, 1);

	@flush();
	@ob_flush();

	if ($photoplog_phase == 0)
	{
		print_table_footer();

		if (
			$photoplog_morecheck = $vbulletin->db->query_first("SELECT userid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
				WHERE userid >= ".intval($photoplog_stop)."
				ORDER BY userid ASC
				LIMIT 1")
		)
		{
			$photoplog_stop = intval($photoplog_morecheck['userid']);
			print_cp_redirect($redirect."?".$vbulletin->session->vars['sessionurl']."do=postbitcounts&phase=0&start=".$photoplog_stop."&perpage=".$photoplog_perpage, 1);
		}
		else
		{
			print_cp_redirect($redirect."?".$vbulletin->session->vars['sessionurl']."do=postbitcounts&phase=1&start=0&perpage=".$photoplog_perpage, 1);
		}
	}
	else
	{
		if (
			$photoplog_morecheck = $vbulletin->db->query_first("SELECT userid
				FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
				WHERE userid >= ".intval($photoplog_stop)."
				ORDER BY userid ASC
				LIMIT 1")
		)
		{
			print_table_footer();
			$photoplog_stop = intval($photoplog_morecheck['userid']);
			print_cp_redirect($redirect."?".$vbulletin->session->vars['sessionurl']."do=postbitcounts&phase=1&start=".$photoplog_stop."&perpage=".$photoplog_perpage, 1);
		}
		else
		{
			print_description_row('<strong>'.$vbphrase['photoplog_done'].'</strong>', 0, 1);
			print_table_footer();
		}
	}
}

// ################# Start photoplog_update_postbit_counts_interval #################
// updates filecount in user table for userids between start (inclusive) and stop (exclusive)
function photoplog_update_postbit_counts_interval($start,$stop,$phase)
{
	$sql = "userid >= ".intval($start)." AND userid < ".intval($stop);
	photoplog_update_postbit_counts_sql($sql,$phase);
}

// ################# Start photoplog_update_postbit_counts_sql #################
// updates filecount in user table for userids that satisfy the sql condition
function photoplog_update_postbit_counts_sql($userid_sql,$phase)
{
	global $vbulletin;

	$photoplog_table_name = ($phase) ? PHOTOPLOG_PREFIX . 'photoplog_ratecomment' : PHOTOPLOG_PREFIX . 'photoplog_fileuploads';
	$photoplog_table_where = ($phase) ? 'AND comment != \'\'' : '';
	$photoplog_field_name = ($phase) ? 'photoplog_commentcount' : 'photoplog_filecount';

	$photoplog_user_infos = $vbulletin->db->query_read("SELECT userid, COUNT(*) AS cnt1
		FROM ".$photoplog_table_name."
		WHERE $userid_sql
		$photoplog_table_where
		GROUP BY userid
		ORDER BY userid ASC
	");

	while ($photoplog_user_info = $vbulletin->db->fetch_array($photoplog_user_infos))
	{
		$photoplog_userid = $photoplog_user_info['userid'];
		$photoplog_count1 = $photoplog_user_info['cnt1'];

		if ($photoplog_userid && $photoplog_count1)
		{
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user
				SET ".$photoplog_field_name." = ".intval($photoplog_count1)."
				WHERE userid = ".intval($photoplog_userid)."
			");
		}
	}

	$vbulletin->db->free_result($photoplog_user_infos);
}

?>