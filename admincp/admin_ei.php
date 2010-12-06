<?php

/*
Original concept code by Colin F in this mod -
http://www.vbulletin.org/forum/showthread.php?t=68113

This code has been COMPLETELY rewritten from the ground up with the exception of including the free mimedecode class file.

Thanks to RedTyger, Bob Denny, Ed Kohwley and Chris McKeever for their additions and assistance with portions of this code.

Huge thanks also goes to the many individuals that have helped test various incarnations of this mod.

Most especially huge thanks to those that have donated!!

If you'd like to donate to the continued production of this modification please do so here -

https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=cyricx%40mmogcommunities%2ecom&item_name=Email%20Integration%20Donation&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8'

-=-=-=-=- Cyricx -=-=-=-=-=-=-

Version 2.3
*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style');
$specialtemplates = array('products');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');

 // ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminpermissions'))
{
    print_cp_no_permission();
}

print_cp_header($vbphrase['ei_header']);


if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'list';
}



// ###################### Start list #######################
if ($_REQUEST['do'] == 'list')
{

	$getusergroups	= $db->query_read("
		SELECT usergroupid, title, ei_forumid
		FROM " . TABLE_PREFIX . "usergroup
		WHERE ei_auto = 1
		ORDER BY `usergroupid`
	");

	print_table_start('admin_ei');
	print_table_header($vbphrase['ei_header_auto'],2);
	print_description_row($vbphrase['ei_auto_how_to'], 0, 2);
	
	while ($getusergroup = $db->fetch_array($getusergroups))
	{
	  	print_table_header($getusergroup['title'],2);

	  	$forumlist = explode(",",$getusergroup['ei_forumid']);
		foreach ($forumlist AS $key => $forumupdateid)
		{
			$getforuminfo = $db->query_read("SELECT title FROM " . TABLE_PREFIX . "forum WHERE forumid = $forumupdateid");
			while ($foruminfo = $db->fetch_array($getforuminfo))
			{
				$bg = fetch_row_bgclass();
				echo "<tr><td class=\"" . $bg ."\">" . $foruminfo['title'] . "</td><td class=\"" . $bg . "\"><a href=\"admin_ei.php?" . $vbulletin->session->vars['sessionurl'] . "do=autosub&f=" . $forumupdateid . "&ug=" . $getusergroup['usergroupid'] . "\">" . $vbphrase['ei_update_sub'] . "</a></td></tr>";
			}
	  	}
	}
	
	print_table_footer(2, '', '', 0);
}

// ###################### Start list #######################
if ($_REQUEST['do'] == 'autosub')
{

	$vbulletin->input->clean_array_gpc('r', array(
	'f'    => TYPE_UINT,
	'ug'   => TYPE_UINT
	));

	$users	= $db->query_read("
		SELECT userid, username
		FROM " . TABLE_PREFIX . "user
		WHERE usergroupid = '" . $vbulletin->GPC['ug'] . "' OR membergroupids REGEXP '^" . $vbulletin->GPC['ug'] . "$|^" . $vbulletin->GPC['ug'] . ",|," . $vbulletin->GPC['ug'] . ",|," . $vbulletin->GPC['ug'] . "$'
		ORDER BY `userid`
	");

	while ($user = $db->fetch_array($users))
	{
							$db->query_write("
								REPLACE INTO " . TABLE_PREFIX . "subscribeforum (userid, emailupdate, forumid)
								VALUES ('" . $user['userid'] . "', 1, '" . $vbulletin->GPC['f'] . "')
							");
	echo $vbphrase['ei_auto_updated'] . $user['username'] . "<br>";
	}
}

print_cp_footer();
?>
