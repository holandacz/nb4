<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// #################### PREVENT UNAUTHORIZED USERS ########################
if (!defined('VB_AREA') || !defined('THIS_SCRIPT') || !defined('DIR'))
{
	exit(); // DO NOT REMOVE THIS!
}

if (is_file($vbulletin->options['photoplog_full_path'].'/thumbnails.php'))
{
	// ####################### REQUIRE PLOG BACK-END ##########################
	require_once($vbulletin->options['photoplog_full_path'].'/thumbnails.php');

	// ####################### EVALUATE VBA TEMPLATE ##########################
	eval('$home[$mods[\'modid\']][\'content\'] = "' . fetch_template('adv_portal_photoplog_thumbs') . '";');
}
else
{
	eval('$home[$mods[\'modid\']][\'content\'] = "";');
}

?>