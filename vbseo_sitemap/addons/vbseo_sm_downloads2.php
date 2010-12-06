<?php

 /******************************************************************************************
 * vBSEO Google/Yahoo Sitemap Generator for vBulletin v3.x.x by Crawlability, Inc.         *
 *-----------------------------------------------------------------------------------------*
 *                                                                                         *
 * Copyright © 2005-2008, Crawlability, Inc. All rights reserved.                          *
 * You may not redistribute this file or its derivatives without written permission.       *
 *                                                                                         *
 * Sales Email: sales@crawlability.com                                                     *
 *                                                                                         *
 *-------------------------------------LICENSE AGREEMENT-----------------------------------*
 * 1. You are free to download and install this plugin on any vBulletin forum for which    *
 *    you hold a valid vB license.                                                         *
 * 2. You ARE NOT allowed to REMOVE or MODIFY the copyright text within the .php files     *
 *    themselves.                                                                          *
 * 3. You ARE NOT allowed to DISTRIBUTE the contents of any of the included files.         *
 * 4. You ARE NOT allowed to COPY ANY PARTS of the code and/or use it for distribution.    *
 ******************************************************************************************/

	$mods = $db->query("SELECT id,name FROM " . TABLE_PREFIX . "file_cats ORDER BY `order`");
	while ($mod = $db->fetch_array($mods))
	{	
		$url = $vbseo_vars['bburl'].'/downloads.php?do=cat&id='.$mod['id'].'&name='.urlencode($mod['name']);

		if(VBSEO_ON)
			$url = vbseo_any_url($url);

  		vbseo_add_url($url, 1.0, '', 'daily');
	}

	$mods = $db->query("SELECT ff.id as fid, ff.name as fname, fc.id as cid, fc.name as cname FROM " . TABLE_PREFIX . "file_files ff LEFT JOIN file_cats fc ON ff.catid=fc.id");
	while ($mod = $db->fetch_array($mods))
	{	
		$url = $vbseo_vars['bburl'].'/downloads.php?do=file&id='.$mod['fid'].'&name='.urlencode($mod['fname']).'&cat='.urlencode($mod['cname']);

		if(VBSEO_ON)
			$url = vbseo_any_url($url);
  		vbseo_add_url($url, 1.0, '', 'daily');
	}
?>