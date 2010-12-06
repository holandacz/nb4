<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ##################### photoplog_upgradesetphp ##########################
function photoplog_upgradesetphp()
{
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
}

// ##################### photoplog_uninstallplog ##########################
function photoplog_uninstallplog()
{
	global $vbulletin;

//	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "phrasetype WHERE fieldname = 'photoplog'");
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "language DROP phrasegroup_photoplog");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "datastore WHERE title = 'photoplog_dscat'");
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "usergroup DROP photoplogpermissions");
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "usergroup DROP photoplogmaxfilesize");
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "usergroup DROP photoplogmaxfilelimit");
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "user DROP photoplog_filecount");
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "user DROP photoplog_commentcount");

	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . PHOTOPLOG_PREFIX . "photoplog_fileuploads");
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . PHOTOPLOG_PREFIX . "photoplog_categories");
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . PHOTOPLOG_PREFIX . "photoplog_ratecomment");
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . PHOTOPLOG_PREFIX . "photoplog_permissions");
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . PHOTOPLOG_PREFIX . "photoplog_customfields");
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . PHOTOPLOG_PREFIX . "photoplog_customgroups");
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . PHOTOPLOG_PREFIX . "photoplog_catcounts");
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . PHOTOPLOG_PREFIX . "photoplog_useralbums");
	$vbulletin->db->query_write("DROP TABLE IF EXISTS " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats");
}

// ##################### photoplog_upgradeto100 ###########################
function photoplog_upgradeto100()
{
	global $vbulletin;	

	$photoplog_check = $vbulletin->db->query_first("SELECT 1 FROM " . TABLE_PREFIX . "phrasetype WHERE fieldname = 'photoplog'");
	if (!$photoplog_check)
	{
		$photoplog_vbversion = explode(".", FILE_VERSION);
		if ($photoplog_vbversion[0] == 3 && $photoplog_vbversion[1] == 5)
		{
			$photoplog_max_rows = $vbulletin->db->query_first("SELECT MAX(phrasetypeid) + 1 AS max FROM " . TABLE_PREFIX . "phrasetype WHERE phrasetypeid < 1000");
			$photoplog_phrasetypeid = intval($photoplog_max_rows['max']);
			if ($photoplog_phrasetypeid)
			{
				$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "phrasetype (phrasetypeid, fieldname, title, editrows, product) VALUES ($photoplog_phrasetypeid, 'photoplog', 'PhotoPlog', 3, 'photoplog')");
				$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "language ADD phrasegroup_photoplog MEDIUMTEXT NOT NULL");
			}
			else
			{
				echo "<br /><br /><strong>No select phrasetypeid!</strong><br /><br />";
				exit();
			}
		}
		else if ($photoplog_vbversion[0] == 3 && $photoplog_vbversion[1] >= 6)
		{
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "phrasetype (fieldname, title, editrows, product) VALUES ('photoplog', 'PhotoPlog', 3, 'photoplog')");
			$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "language ADD phrasegroup_photoplog MEDIUMTEXT NOT NULL");
		}
		else
		{
			echo "<br /><br /><strong>No insert phrasetype!</strong><br /><br />";
			exit();
		}
	}

	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		(
			fileid INT UNSIGNED NOT NULL AUTO_INCREMENT,
			userid INT UNSIGNED NOT NULL DEFAULT '0',
			username VARCHAR(100) NOT NULL DEFAULT '',
			title VARCHAR(255) NOT NULL DEFAULT '',
			description MEDIUMTEXT NOT NULL,
			filename VARCHAR(255) NOT NULL DEFAULT '',
			filesize INT UNSIGNED NOT NULL DEFAULT '0',
			dateline INT UNSIGNED NOT NULL DEFAULT '0',
			views INT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (fileid)
		)
	");
}

// ##################### photoplog_upgradeto101 ###########################
function photoplog_upgradeto101()
{
	global $vbulletin;	

//	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "productcode WHERE productid = 'photoplog'");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads ADD catid INT UNSIGNED NOT NULL DEFAULT '0'");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads ADD moderate SMALLINT UNSIGNED NOT NULL DEFAULT '0'");

	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . PHOTOPLOG_PREFIX . "photoplog_categories
		(
			catid INT UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL DEFAULT '',
			description MEDIUMTEXT NOT NULL,
			displayorder SMALLINT NOT NULL DEFAULT '0',
			parentid SMALLINT NOT NULL DEFAULT '0',
			options INT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (catid)
		)
	");

	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "usergroup ADD photoplogpermissions INT(10) UNSIGNED DEFAULT '0' NOT NULL");
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "usergroup ADD photoplogmaxfilesize INT(10) DEFAULT '512000' NOT NULL");
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "usergroup ADD photoplogmaxfilelimit INT(10) DEFAULT '100' NOT NULL");
}

// ##################### photoplog_upgradeto102 ###########################
function photoplog_upgradeto102()
{
	global $vbulletin;

	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		(
			commentid INT UNSIGNED NOT NULL AUTO_INCREMENT,
			fileid INT UNSIGNED NOT NULL DEFAULT '0',
			catid INT UNSIGNED NOT NULL DEFAULT '0',
			userid INT UNSIGNED NOT NULL DEFAULT '0',
			username VARCHAR(100) NOT NULL DEFAULT '',
			rating INT UNSIGNED NOT NULL DEFAULT '0',
			title VARCHAR(255) NOT NULL DEFAULT '',
			comment MEDIUMTEXT NOT NULL,
			dateline INT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (commentid)
		)
	");
}

// ##################### photoplog_upgradeto103 ###########################
function photoplog_upgradeto103()
{
	global $vbulletin;

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment ADD moderate SMALLINT UNSIGNED NOT NULL DEFAULT '0'");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment ADD lastedit VARCHAR(255) NOT NULL DEFAULT ''");
}

// ##################### photoplog_upgradeto209 ###########################
function photoplog_upgradeto209()
{
	global $vbulletin;

	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "datastore WHERE title = 'photoplog_dsind'");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads ADD dimensions VARCHAR(255) NOT NULL DEFAULT '975313579 x 135797531'");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads ADD setid INT UNSIGNED NOT NULL DEFAULT '0'");
	$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads SET setid = fileid");
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . PHOTOPLOG_PREFIX . "photoplog_permissions
		(
			permid INT UNSIGNED NOT NULL AUTO_INCREMENT,
			catid INT UNSIGNED NOT NULL DEFAULT '0',
			usergroupid INT UNSIGNED NOT NULL DEFAULT '0',
			options INT UNSIGNED NOT NULL DEFAULT '0',
			maxfilesize INT UNSIGNED NOT NULL DEFAULT '512000',
			maxfilelimit INT UNSIGNED NOT NULL DEFAULT '100',
			PRIMARY KEY (permid)
		)
	");
}

// ##################### photoplog_upgradeto210 ###########################
function photoplog_upgradeto210()
{
	global $vbulletin;

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads ADD fielddata MEDIUMTEXT NOT NULL");
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . PHOTOPLOG_PREFIX . "photoplog_customfields
		(
			fieldid INT UNSIGNED NOT NULL AUTO_INCREMENT,
			catid INT NOT NULL DEFAULT '0',
			groupid INT NOT NULL DEFAULT '0',
			displayorder SMALLINT NOT NULL DEFAULT '0',
			hidden TINYINT NOT NULL DEFAULT '0',
			active TINYINT NOT NULL DEFAULT '0',
			protected TINYINT NOT NULL DEFAULT '0',
			inherited TINYINT NOT NULL DEFAULT '0',
			parentid INT NOT NULL DEFAULT '0',
			info MEDIUMTEXT NOT NULL,
			PRIMARY KEY (fieldid),
			INDEX (catid, displayorder)
		)
	");
	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . PHOTOPLOG_PREFIX . "photoplog_customgroups
		(
			groupid INT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL DEFAULT '',
			PRIMARY KEY (groupid)
		)
	");

	$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_customgroups (name) VALUES ('Title')");
	$photoplog_groupid_title = $vbulletin->db->insert_id();
	$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_customgroups (name) VALUES ('Description')");
	$photoplog_groupid_description = $vbulletin->db->insert_id();

	$photoplog_title_info = array(
		'type' => 0,
		'title' => 'photoplog_title',
		'description' => 'photoplog_title_description',
		'maxlength' => 85,
		'default' => '',
		'size' => 50,
		'required' => 1
	);
	$photoplog_description_info = array(
		'type' => 1,
		'title' => 'photoplog_description',
		'description' => 'photoplog_description_description',
		'maxlength' => 300,
		'default' => '',
		'size' => 50,
		'height' => 4,
		'required' => 1
	);
	$photoplog_title_info = $vbulletin->db->escape_string(serialize($photoplog_title_info));
	$photoplog_description_info = $vbulletin->db->escape_string(serialize($photoplog_description_info));

	$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_customfields
		(catid, groupid, displayorder, hidden, active, protected, inherited, parentid, info)
		VALUES (-2, " . intval($photoplog_groupid_title) . ", 10, 0, 1, 1, -1, 0, '" . $photoplog_title_info . "')
	");
	$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_customfields
		(catid, groupid, displayorder, hidden, active, protected, inherited, parentid, info)
		VALUES (-2, " . intval($photoplog_groupid_description) . ", 20, 0, 1, 1, -1, 0, '" . $photoplog_description_info . "')
	");

	$photoplog_catid_list = array(-1);
	$photoplog_catids = $vbulletin->db->query_read("SELECT catid FROM " . PHOTOPLOG_PREFIX . "photoplog_categories");
	while ($photoplog_catid = $vbulletin->db->fetch_array($photoplog_catids))
	{
		$photoplog_catid_list[] = $photoplog_catid['catid'];
	}

	$photoplog_sql_bits1 = array();
	$photoplog_sql_bits2 = array();
	foreach ($photoplog_catid_list AS $photoplog_catid)
	{
		$photoplog_sql_bits1[] = "(
			".intval($photoplog_catid).", ".intval($photoplog_groupid_title).", 10, 0, 1, 1, 1, -2, ''
		)";

		$photoplog_sql_bits2[] = "(
			".intval($photoplog_catid).", ".intval($photoplog_groupid_description).", 20, 0, 1, 1, 1, -2, ''
		)";
	}

	if ($photoplog_sql_bits1)
	{
		$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_customfields
			(catid, groupid, displayorder, hidden, active, protected, inherited, parentid, info)
			VALUES ".implode(",",$photoplog_sql_bits1)."
		");
	}
	if ($photoplog_sql_bits2)
	{
		$vbulletin->db->query_write("INSERT INTO " . PHOTOPLOG_PREFIX . "photoplog_customfields
			(catid, groupid, displayorder, hidden, active, protected, inherited, parentid, info)
			VALUES ".implode(",",$photoplog_sql_bits2)."
		");
	}
}

// ##################### photoplog_upgradeto211 ###########################
function photoplog_upgradeto211()
{
	global $vbulletin;	

	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "user ADD photoplog_filecount INT UNSIGNED NOT NULL DEFAULT '0'");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		ADD num_comments0 INT UNSIGNED NOT NULL DEFAULT '0',
		ADD num_comments1 INT UNSIGNED NOT NULL DEFAULT '0',
		ADD num_ratings0 INT UNSIGNED NOT NULL DEFAULT '0',
		ADD num_ratings1 INT UNSIGNED NOT NULL DEFAULT '0',
		ADD sum_ratings0 INT UNSIGNED NOT NULL DEFAULT '0',
		ADD sum_ratings1 INT UNSIGNED NOT NULL DEFAULT '0',
		ADD last_comment_dateline0 INT UNSIGNED NOT NULL DEFAULT '0',
		ADD last_comment_dateline1 INT UNSIGNED NOT NULL DEFAULT '0',
		ADD last_comment_id0 INT UNSIGNED NOT NULL DEFAULT '0',
		ADD last_comment_id1 INT UNSIGNED NOT NULL DEFAULT '0'
	");

	// remove any futuristic uploads/comments that may have been created via massmove in previous versions
	$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		SET dateline = ".intval(TIMENOW)."
		WHERE dateline > ".intval(TIMENOW)."
	");
	$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		SET dateline = ".intval(TIMENOW)."
		WHERE dateline > ".intval(TIMENOW)."
	");

	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . PHOTOPLOG_PREFIX . "photoplog_catcounts
		(
			catid INT NOT NULL DEFAULT '0',
			moderate SMALLINT NOT NULL DEFAULT '0',
			num_uploads INT UNSIGNED NOT NULL DEFAULT '0',
			num_comments INT UNSIGNED NOT NULL DEFAULT '0',
			num_ratings INT UNSIGNED NOT NULL DEFAULT '0',
			sum_ratings INT UNSIGNED NOT NULL DEFAULT '0',
			last_upload_dateline INT UNSIGNED NOT NULL DEFAULT '0',
			last_upload_id INT UNSIGNED NOT NULL DEFAULT '0',
			last_comment_dateline INT UNSIGNED NOT NULL DEFAULT '0',
			last_comment_id INT UNSIGNED NOT NULL DEFAULT '0',
			num_views INT UNSIGNED NOT NULL DEFAULT '0',
			sum_filesize INT UNSIGNED NOT NULL DEFAULT '0',
			INDEX (catid)
		)
	");

	$catids = array();
	$catsquery = $vbulletin->db->query_read("SELECT catid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_categories
	");
	while ($catrow = $vbulletin->db->fetch_array($catsquery))
	{
		$catids[] = intval($catrow['catid']);
	}

	$catids[] = 0;
	$count_inserts = array();
	foreach ($catids AS $catid)
	{
		$count_inserts["$catid"] = array();
		$count_inserts["$catid"][0] = array();
		$count_inserts["$catid"][1] = array();
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
			if (!isset($count_inserts["$catid"][0][$key]))
			{
				$count_inserts["$catid"][0][$key] = 0;
			}
			if (!isset($count_inserts["$catid"][1][$key]))
			{
				$count_inserts["$catid"][1][$key] = 0;
			}
			$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
			$count_inserts["$catid"][1][$key] = intval($count_inserts["$catid"][1][$key]) + intval($count_inserts["$catid"][0][$key]);
		}
		foreach (array('last_upload_dateline','last_upload_id','last_comment_dateline','last_comment_id') AS $key)
		{
			if (!isset($count_inserts["$catid"][0][$key]))
			{
				$count_inserts["$catid"][0][$key] = 0;
			}
			if (!isset($count_inserts["$catid"][1][$key]))
			{
				$count_inserts["$catid"][1][$key] = 0;
			}
			$count_inserts["$catid"][0][$key] = intval($count_inserts["$catid"][0][$key]);
			$count_inserts["$catid"][1][$key] = max(intval($count_inserts["$catid"][1][$key]), intval($count_inserts["$catid"][0][$key]));
			$upload_datelines[] = intval($count_inserts["$catid"][0]['last_upload_dateline']);
			$upload_datelines[] = intval($count_inserts["$catid"][1]['last_upload_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][0]['last_comment_dateline']);
			$comment_datelines[] = intval($count_inserts["$catid"][1]['last_comment_dateline']);
		}
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

// ##################### photoplog_upgradeto212 ###########################
function photoplog_upgradeto212()
{
	global $vbulletin;

	$vbulletin->db->query_write("RENAME TABLE " . PHOTOPLOG_PREFIX . "photoplog_customfield_groups TO " . PHOTOPLOG_PREFIX . "photoplog_customgroups");
	$vbulletin->db->query_write("ALTER TABLE " . TABLE_PREFIX . "user ADD photoplog_commentcount INT UNSIGNED NOT NULL DEFAULT '0'");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads ADD albumids MEDIUMTEXT NOT NULL");
	$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads SET albumids = '".$vbulletin->db->escape_string(serialize(array()))."'");

	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . PHOTOPLOG_PREFIX . "photoplog_useralbums
		(
			albumid INT UNSIGNED NOT NULL AUTO_INCREMENT,
			userid INT UNSIGNED NOT NULL DEFAULT '0',
			username VARCHAR(100) NOT NULL DEFAULT '',
			title VARCHAR(255) NOT NULL DEFAULT '',
			description MEDIUMTEXT NOT NULL,
			fileids MEDIUMTEXT NOT NULL,
			dateline INT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (albumid),
			KEY userid (userid)
		)
	");

	$vbulletin->db->query_write("CREATE TABLE IF NOT EXISTS " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
		(
			suggestid INT UNSIGNED NOT NULL AUTO_INCREMENT,
			catid INT NOT NULL DEFAULT '0',
			approve SMALLINT UNSIGNED NOT NULL DEFAULT '0',
			userid INT UNSIGNED NOT NULL DEFAULT '0',
			title VARCHAR(255) NOT NULL DEFAULT '',
			description MEDIUMTEXT NOT NULL,
			displayorder SMALLINT NOT NULL DEFAULT '0',
			parentid SMALLINT NOT NULL DEFAULT '0',
			options INT UNSIGNED NOT NULL DEFAULT '0',
			dateline INT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (suggestid),
			KEY approve (approve)
		)
	");

	$fields = $vbulletin->db->query_read("SELECT fieldid, catid, groupid, parentid, inherited
				FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
	");
	$fielddata = array();
	$fieldids = array();
	while ($field = $vbulletin->db->fetch_array($fields))
	{
		$fid = $field['fieldid'];
		$fieldids[] = $fid;
		$fielddata[$fid] = $field;
	}
	$vbulletin->db->free_result($fields);

	$delfieldids = array();
	foreach ($fieldids AS $fieldid)
	{
		$groupid = $fielddata[$fieldid]['groupid'];
		$parentid = $fielddata[$fieldid]['parentid'];
		$inherited = $fielddata[$fieldid]['inherited'];
		if ($inherited == 1)
		{
			$parfound = false;
			foreach ($fielddata AS $field)
			{
				$parent_groupid = $field['groupid'];
				$parent_catid = $field['catid'];
				$parent_inherited = $field['inherited'];
				if (($parent_catid == $parentid) && ($parent_inherited == -1) && ($parent_groupid == $groupid))
				{
					$parfound = true;
					break;
				}
			}
			if (!$parfound)
			{
				$delfieldids[] = intval($fieldid);
			}
		}
	}

	if (!empty($delfieldids))
	{
		$vbulletin->db->query_write("DELETE FROM " . PHOTOPLOG_PREFIX . "photoplog_customfields
			WHERE fieldid IN (" . implode(",",$delfieldids) . ") AND inherited = 1
		");
	}
}

// ##################### photoplog_upgradeto214 ###########################
function photoplog_upgradeto214()
{
	global $vbulletin;

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads ADD exifinfo TEXT NOT NULL");
	$vbulletin->db->query_write("UPDATE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads SET exifinfo = 'a:0:{}'");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_useralbums ADD visible SMALLINT UNSIGNED NOT NULL DEFAULT '1'");
}

// ##################### photoplog_upgradeto2145 ##########################
function photoplog_upgradeto2145()
{
	global $vbulletin;

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads DROP INDEX catid");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads DROP INDEX dateline");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads DROP INDEX moderate");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads DROP INDEX setid");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads DROP INDEX title");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads DROP INDEX userid");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads DROP INDEX username");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		ADD INDEX catid (catid,moderate),
		ADD INDEX dateline (dateline,fileid),
		ADD INDEX moderate (moderate),
		ADD INDEX setid (setid),
		ADD FULLTEXT KEY title (title, description, fielddata),
		ADD INDEX userid (userid),
		ADD INDEX username (username)
	");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment DROP INDEX catid");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment DROP INDEX dateline");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment DROP INDEX fileid");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment DROP INDEX moderate");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment DROP INDEX title");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment DROP INDEX userid");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		ADD INDEX catid (catid,moderate),
		ADD INDEX dateline (dateline),
		ADD INDEX fileid (fileid,moderate),
		ADD INDEX moderate (moderate),
		ADD FULLTEXT KEY title (title,comment),
		ADD INDEX userid (userid)
	");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_categories DROP INDEX displayorder");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_categories DROP INDEX parentid");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_categories
		ADD INDEX displayorder (displayorder),
		ADD INDEX parentid (parentid)
	");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_permissions DROP INDEX catid");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_permissions DROP INDEX usergroupid");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_permissions
		ADD INDEX catid (catid),
		ADD INDEX usergroupid (usergroupid,catid)
	");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats DROP INDEX approve");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats DROP INDEX catid");
	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats DROP INDEX userid");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats
		ADD INDEX catid (catid),
		ADD INDEX userid (userid)
	");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_customgroups DROP INDEX name");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_customgroups
		ADD INDEX name (name,groupid)
	");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_catcounts DROP INDEX moderate");

	$vbulletin->db->query_write("ALTER TABLE " . PHOTOPLOG_PREFIX . "photoplog_catcounts
		ADD INDEX moderate (moderate,num_uploads)
	");

	$vbulletin->db->query_write("ANALYZE TABLE " . PHOTOPLOG_PREFIX . "photoplog_catcounts");
	$vbulletin->db->query_write("ANALYZE TABLE " . PHOTOPLOG_PREFIX . "photoplog_categories");
	$vbulletin->db->query_write("ANALYZE TABLE " . PHOTOPLOG_PREFIX . "photoplog_customfields");
	$vbulletin->db->query_write("ANALYZE TABLE " . PHOTOPLOG_PREFIX . "photoplog_customgroups");
	$vbulletin->db->query_write("ANALYZE TABLE " . PHOTOPLOG_PREFIX . "photoplog_fileuploads");
	$vbulletin->db->query_write("ANALYZE TABLE " . PHOTOPLOG_PREFIX . "photoplog_permissions");
	$vbulletin->db->query_write("ANALYZE TABLE " . PHOTOPLOG_PREFIX . "photoplog_ratecomment");
	$vbulletin->db->query_write("ANALYZE TABLE " . PHOTOPLOG_PREFIX . "photoplog_suggestedcats");
	$vbulletin->db->query_write("ANALYZE TABLE " . PHOTOPLOG_PREFIX . "photoplog_useralbums");
}

?>