<?php

/*
Original concept code by Colin F in this mod -
http://www.vbulletin.org/forum/showthread.php?t=68113

This code has been COMPLETELY rewritten from the ground up with the exception of including the free mimedecode class file.

Thanks to RedTyger, Bob Denny, Ed Kohwley and Chris McKeever for their additions and assistance with portions of this code.

Huge thanks also goes to the many individuals that have helped test various incarnations of this mod.

Most especially huge thanks to those that have donated!!

-=-=-=-=- Cyricx -=-=-=-=-=-=-

Version 2.5.5
*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'usercp');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('user', 'infractionlevel');

// get special data templates from the datastore
$specialtemplates = array(
	'iconcache',
	'noavatarperms',
	'smiliecache',
	'bbcodecache',
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'USERCP_SHELL',
	'USERCP',
	'USERCP_SUB_MGR',
	'usercp_sub_mgr_forumbits',
	'usercp_nav_folderbit',
);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_forumlist.php');
require_once(DIR . '/includes/functions_user.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if (!$vbulletin->userinfo['userid'])
{
	print_no_permission();
}

if (empty($_REQUEST['action']))
{
	if (empty($_REQUEST['do']))
	{
		$_REQUEST['do'] = 'list';
	}
}
// ###########################################################################
// ############################### LIST FORUMS ###############################
// ###########################################################################

if ($_REQUEST['do'] == "list")
{

	if (!$vbulletin->userinfo['userid'])
	{
		print_no_permission();
	}

	// get all forums
	cache_ordered_forums(1, 0);
	$show['forums'] = true;
	$showallinstant = 0;
	//generate the radio button form
	foreach ($vbulletin->forumcache AS $forumid => $forum)
	{
		if (!$forum['link'])
		{
			$showinstant = 0;
			//get full forum info
			$forum = fetch_foruminfo($forum["forumid"], false);

			//if the user has permission to view the given forum, and if forum is postable...
			if(($perms = fetch_permissions($forum["forumid"])) AND ($perms & $vbulletin->bf_ugp_forumpermissions["canview"]) AND $forum["cancontainthreads"] AND $forum["displayorder"] > 0 AND ($forum["options"] & $vbulletin->bf_misc_forumoptions['active']))
			{
				//get the level of the subscription for the given forum
				$sub_level = $db->query_first("
					SELECT emailupdate
					FROM " . TABLE_PREFIX . "subscribeforum
					WHERE forumid = '" . $forum["forumid"] . "' AND userid = " . $vbulletin->userinfo['userid'] . " LIMIT 1
				");

				$usergroupids = explode(",", $forum["ei_usergroups"]);
				//if the user hits the set all none or set all instant buttons...
				if($_REQUEST["setall"]=="none")
					$sub_level["emailupdate"] = 0;
				//write out a row with the info - title, description, and selected subscription
				if ($forum["ei_active"] AND is_member_of($vbulletin->userinfo, $usergroupids))
				{
					$showinstant = 1;
					$showallinstant = 1;
					if($_REQUEST["setall"]=="instant")
					$sub_level["emailupdate"] = 1;
	  			}
	  			else if($_REQUEST["setall"]=="instant")
		  		{
					$sub_level["emailupdate"] = 0;
				}
				eval('$forumbits .= "' . fetch_template('usercp_sub_mgr_forumbits') . '";');
			}
		}
	}
	// draw cp nav bar
	construct_usercp_nav('usercp');

	$frmjmpsel['usercp'] = 'class="fjsel" selected="selected"';
	construct_forum_jump();

	eval('$HTML = "' . fetch_template('USERCP_SUB_MGR') . '";');

	// build navbar
	$navbits = construct_navbits(array('' => $vbphrase['user_control_panel']));
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('print_output("' . fetch_template('USERCP_SHELL') . '");');
}

// ###########################################################################
// ########################### UPDATE SUBSCRIPTIONS ##########################
// ###########################################################################
if ($_REQUEST['action'] == "updatesubs")
{
	if (!$vbulletin->userinfo['userid'])
	{
		print_no_permission();
	}


	//get a list of all the forum ID's
	$tables = $db->query_read("
		SELECT forumid
		FROM " . TABLE_PREFIX . "forum
	");

	//scan through the list to perform the subscription action
	while($curr_forum = $db->fetch_array($tables) )
	{
		//get full forum info
		$curr_forum = fetch_foruminfo($curr_forum['forumid'], false);
		//if the user has permission to view the given forum, and if forum is postable...
		if(($perms = fetch_permissions($curr_forum["forumid"])) AND ($perms & $vbulletin->bf_ugp_forumpermissions['canview']) AND $curr_forum["cancontainthreads"] )
		{


			$vbulletin->input->clean_gpc('r',
					'forumid'.$curr_forum['forumid'], TYPE_UINT
			);

			$new_sublevel=$vbulletin->GPC["forumid".$curr_forum["forumid"]];
			
			if ($new_sublevel == 0)
			{
				$db->query_write("
				DELETE FROM " . TABLE_PREFIX . "subscribeforum
				WHERE userid = '" . $vbulletin->userinfo['userid'] . "'
				AND forumid = '" . $curr_forum['forumid'] . "'");
	  		}
	  		else
	  		{
				$db->query_write("
					REPLACE INTO " . TABLE_PREFIX . "subscribeforum (userid, emailupdate, forumid)
					VALUES ('" . $vbulletin->userinfo['userid'] . "', '" . $new_sublevel . "', '" . $curr_forum['forumid'] . "')
				");
			}
		}
	}

	$vbulletin->url = "subscribeforums.php";
	eval(print_standard_redirect('ei_sub_forums_updated', true, true));
}

?>
