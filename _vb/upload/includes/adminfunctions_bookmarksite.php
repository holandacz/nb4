<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.7.3 Patch Level 1 - Licence Number VBFDF477A7
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

error_reporting(E_ALL & ~E_NOTICE);


// #############################################################################
/**
* Caches social bookmark site data to the datastore
*/
function build_bookmarksite_datastore()
{
	global $vbulletin;
	
	$vbulletin->bookmarksitecache = array();
	
	$bookmarksitelist = $vbulletin->db->query_read("
		SELECT *  
		FROM " . TABLE_PREFIX . "bookmarksite AS bookmarksite
		WHERE active = 1
		ORDER BY displayorder ASC, bookmarksiteid ASC
	");
	if ($bookmarksitelist)
	{
		while ($bookmarksite = $vbulletin->db->fetch_array($bookmarksitelist))
		{
			$vbulletin->bookmarksitecache["$bookmarksite[bookmarksiteid]"] = $bookmarksite;
		}
	}

	// store the cache array into the database
	build_datastore('bookmarksitecache', serialize($vbulletin->bookmarksitecache), 1);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 12:56, Sat Oct 11th 2008
|| # CVS: $RCSfile$ - $Revision: 25433 $
|| ####################################################################
\*======================================================================*/
?>