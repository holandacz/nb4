<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.7.3 Patch Level 1 - Licence Number VBFDF477A7
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

/**
 * Quick Method of building the CPNav Template
 *
 * @param	string	The selected item in the CPNav
 */
function construct_usercp_nav($selectedcell = 'usercp')
{
	global $navclass, $cpnav, $gobutton, $stylevar, $vbphrase;
	global $messagecounters, $subscribecounters, $vbulletin;
	global $show, $subscriptioncache, $template_hook;

	$cells = array(
		'usercp',

		'signature',
		'profile',
		'options',
		'password',
		'avatar',
		'profilepic',
		'album',

		'pm_messagelist',
		'pm_newpm',
		'pm_trackpm',
		'pm_editfolders',

		'substhreads_listthreads',
		'substhreads_editfolders',

		'deletedthreads',
		'deletedposts',
		'moderatedthreads',
		'moderatedposts',
		'moderatedvms',
		'deletedvms',
		'moderatedgms',
		'deletedgms',
		'moderatedpcs',
		'deletedpcs',
		'moderatedpics',

		'event_reminders',
		'paid_subscriptions',
		'usergroups',
		'buddylist',
		'ignorelist',
		'attachments',
		'customize'
	);

	($hook = vBulletinHook::fetch_hook('usercp_nav_start')) ? eval($hook) : false;


	// Get forums that allow canview access
	$canget = $canpost = '';
	foreach ($vbulletin->userinfo['forumpermissions'] AS $forumid => $perm)
	{
		if (($perm & $vbulletin->bf_ugp_forumpermissions['canview']) AND ($perm & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) AND ($perm & $vbulletin->bf_ugp_forumpermissions['cangetattachment']))
		{
			$canget .= ",$forumid";
		}
		if (($perm & $vbulletin->bf_ugp_forumpermissions['canpostattachment']) AND !empty($vbulletin->userinfo['attachmentextensions']))
		{
			$canpost .= ",$forumid";
		}
	}

	if (!$canpost)
	{
		$attachments = $vbulletin->db->query_first_slave("
			SELECT COUNT(*) AS total
			FROM " . TABLE_PREFIX . "attachment AS attachment
			LEFT JOIN " . TABLE_PREFIX . "post AS post ON (post.postid = attachment.postid)
			LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
			WHERE attachment.userid = " . $vbulletin->userinfo['userid'] . "
				AND	((forumid IN(0$canget) AND thread.visible = 1 AND post.visible = 1) OR attachment.postid = 0)
		");
		$totalattachments = intval($attachments['total']);
	}

	if (!($vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_profile_styling']))
	{
		$show['customizelink'] = false;
	}
	else if (
		($vbulletin->userinfo['permissions']['usercsspermissions'] & $vbulletin->bf_ugp_usercsspermissions['caneditfontfamily'])
		OR ($vbulletin->userinfo['permissions']['usercsspermissions'] & $vbulletin->bf_ugp_usercsspermissions['caneditfontsize'])
		OR ($vbulletin->userinfo['permissions']['usercsspermissions'] & $vbulletin->bf_ugp_usercsspermissions['caneditcolors'])
		OR ($vbulletin->userinfo['permissions']['usercsspermissions'] & $vbulletin->bf_ugp_usercsspermissions['caneditbgimage'])
		OR ($vbulletin->userinfo['permissions']['usercsspermissions'] & $vbulletin->bf_ugp_usercsspermissions['caneditborders'])
	)
	{
		$show['customizelink'] = true;
	}
	else
	{
		$show['customizelink'] = false;
	}

	if ($show['avatarlink'] AND !($vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canuseavatar']))
	{
		$membergroups = fetch_membergroupids_array($vbulletin->userinfo);
		// We don't have any predefined avatars or user's groups are all denied permission
		if (!empty($vbulletin->noavatarperms) AND ($vbulletin->noavatarperms['all'] == true OR !count(array_diff($membergroups, $vbulletin->noavatarperms))))
		{
			$show['avatarlink'] = false;
		}
		else if (!empty($vbulletin->userinfo['infractiongroupids']))
		{
			$show['avatarlink'] = ($categorycache =& fetch_avatar_categories($vbulletin->userinfo));
		}
	}

	if ($totalattachments OR $canpost)
	{
		$show['attachments'] = true;
	}
	if ($vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_groups']
		AND $vbulletin->userinfo['permissions']['socialgrouppermissions'] & $vbulletin->bf_ugp_socialgrouppermissions['canviewgroups'])
	{
		$show['socialgroupslink'] = true;
	}

	if (!$vbulletin->options['subscriptionmethods'])
	{
		$show['paidsubscriptions'] = false;
	}
	else
	{
		// cache all the subscriptions - should move this to a datastore object at some point
		require_once(DIR . '/includes/class_paid_subscription.php');
		$subobj = new vB_PaidSubscription($vbulletin);
		$subobj->cache_user_subscriptions();
		$show['paidsubscriptions'] = false;
		foreach ($subobj->subscriptioncache AS $subscription)
		{
			$subscriptionid =& $subscription['subscriptionid'];
			if ($subscription['active'] AND (empty($subscription['deniedgroups']) OR count(array_diff(fetch_membergroupids_array($vbulletin->userinfo), $subscription['deniedgroups']))))
			{
				$show['paidsubscriptions'] = true;
				break;
			}
		}
	}

	// check to see if there are usergroups available
	$show['publicgroups'] = false;
	foreach ($vbulletin->usergroupcache AS $usergroup)
	{
		if ($usergroup['ispublicgroup'] OR ($usergroup['canoverride'] AND is_member_of($vbulletin->userinfo, $usergroup['usergroupid'])))
		{
			$show['publicgroups'] = true;
			break;
		}
	}

	// Setup Moderation Links
	if (can_moderate())
	{
		$show['deleteditems'] = true;
		$show['deletedmessages'] = true;
	}

	if (can_moderate(0, 'canmoderateposts'))
	{
		$show['moderatedposts'] = true;
	}

	if (can_moderate(0, 'canmoderatevisitormessages') AND $vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_visitor_messaging'])
	{
		$show['moderatedvms'] = true;
		$show['deletedvms'] = true;
	}
	if (can_moderate(0, 'canmoderategroupmessages') AND $vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_groups'] AND $vbulletin->options['socnet_groups_msg_enabled'])
	{
		$show['moderatedgms'] = true;
		$show['deletedgms'] = true;
	}
	if (can_moderate(0, 'canmoderatepicturecomments') AND $vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_albums'] AND $vbulletin->options['pc_enabled'])
	{
		$show['moderatedpcs'] = true;
		$show['deletedpcs'] = true;
	}
	if (can_moderate(0, 'canmoderatepictures') AND $vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_albums'])
	{
		$show['moderatedpics'] = true;
	}

	$show['moderateditems'] = ($show['moderatedposts'] OR $show['moderatedvms'] OR $show['moderatedgms'] OR $show['moderatedpcs'] OR $show['moderatedpics']);

	$show['moderation'] = ($show['deleteditems'] OR $show['moderateditems']);

	// album setup
	$show['albumlink'] = ($vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_albums']
		AND $vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canviewmembers']
		AND $vbulletin->userinfo['permissions']['albumpermissions'] & $vbulletin->bf_ugp_albumpermissions['canviewalbum']
		AND $vbulletin->userinfo['permissions']['albumpermissions'] & $vbulletin->bf_ugp_albumpermissions['canalbum']
	);

	// set the class for each cell/group
	$navclass = array();
	foreach ($cells AS $cellname)
	{
		$navclass["$cellname"] = 'alt2';
	}
	$navclass["$selectedcell"] = 'alt1';

	// variable to hold templates for pm / subs folders
	$cpnav = array();

	// get PM folders
	$cpnav['pmfolders'] = '';
	$pmfolders = array('0' => $vbphrase['inbox'], '-1' => $vbphrase['sent_items']);
	if (!empty($vbulletin->userinfo['pmfolders']))
	{
		$pmfolders = $pmfolders + unserialize($vbulletin->userinfo['pmfolders']);
	}
	foreach ($pmfolders AS $folderid => $foldername)
	{
		$linkurl = 'private.php?' . $vbulletin->session->vars['sessionurl'] . "folderid=$folderid";
		eval('$cpnav[\'pmfolders\'] .= "' . fetch_template('usercp_nav_folderbit') . '";');
	}

	// get subscriptions folders
	$cpnav['subsfolders'] = '';
	$subsfolders = unserialize($vbulletin->userinfo['subfolders']);
	if (!empty($subsfolders))
	{
		foreach ($subsfolders AS $folderid => $foldername)
		{
			$linkurl = 'subscription.php?' . $vbulletin->session->vars['sessionurl'] . "folderid=$folderid";
			eval('$cpnav[\'subsfolders\'] .= "' . fetch_template('usercp_nav_folderbit') . '";');
		}
	}
	if ($cpnav['subsfolders'] == '')
	{
		$linkurl = 'subscription.php?' . $vbulletin->session->vars['sessionurl'] . 'folderid=0';
		$foldername = $vbphrase['subscriptions'];
		eval('$cpnav[\'subsfolders\'] .= "' . fetch_template('usercp_nav_folderbit') . '";');
	}

	($hook = vBulletinHook::fetch_hook('usercp_nav_complete')) ? eval($hook) : false;
}

/**
 * Fetches the Avatar Category Cache
 *
 * @param	array	User Information
 *
 * @return	array	Avatar Category Cache
 *
 */
function &fetch_avatar_categories(&$userinfo)
{
	global $vbulletin;
	static $categorycache = array();

	if (isset($categorycache["$userinfo[userid]"]))
	{
		return $categorycache["$userinfo[userid]"];
	}
	else
	{
		$categorycache["$userinfo[userid]"] = array();
	}

	$membergroups = fetch_membergroupids_array($userinfo);
	$infractiongroups = explode(',', str_replace(' ', '', $userinfo['infractiongroupids']));

	// ############### DISPLAY AVATAR CATEGORIES ###############
	// get all the available avatar categories
	$avperms = $vbulletin->db->query_read_slave("
		SELECT imagecategorypermission.imagecategoryid, usergroupid
		FROM " . TABLE_PREFIX . "imagecategorypermission AS imagecategorypermission, " . TABLE_PREFIX . "imagecategory AS imagecategory
		WHERE imagetype = 1
			AND imagecategorypermission.imagecategoryid = imagecategory.imagecategoryid
		ORDER BY imagecategory.displayorder
	");
	$noperms = array();
	while ($avperm = $vbulletin->db->fetch_array($avperms))
	{
		$noperms["{$avperm['imagecategoryid']}"][] = $avperm['usergroupid'];
	}
	foreach($noperms AS $imagecategoryid => $usergroups)
	{
		foreach($usergroups AS $usergroupid)
		{
			if (in_array($usergroupid, $infractiongroups))
			{
				$badcategories .= ",$imagecategoryid";
			}
		}
		if (!count(array_diff($membergroups, $usergroups)))
		{
			$badcategories .= ",$imagecategoryid";
		}
	}

	$categories = $vbulletin->db->query_read_slave("
		SELECT imagecategory.*, COUNT(avatarid) AS avatars
		FROM " . TABLE_PREFIX . "imagecategory AS imagecategory
		LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON
			(avatar.imagecategoryid=imagecategory.imagecategoryid)
		WHERE imagetype=1
		AND avatar.minimumposts <= " . intval($userinfo['posts']) . "
		AND avatar.avatarid <> " . intval($userinfo['avatarid']) . "
		AND imagecategory.imagecategoryid NOT IN (0$badcategories)
		GROUP BY imagecategory.imagecategoryid
		HAVING avatars > 0
		ORDER BY imagecategory.displayorder
	");

	while ($category = $vbulletin->db->fetch_array($categories))
	{
		$categorycache["$userinfo[userid]"]["{$category['imagecategoryid']}"] = $category;
	}

	return $categorycache["$userinfo[userid]"];
}

/**
 * Fetches the URL for a User's Avatar
 *
 * @param	integer	The User ID
 * @param	boolean	Whether to get the Thumbnailed avatar or not
 *
 * @return	array	Information regarding the avatar
 *
 */
function fetch_avatar_url($userid, $thumb = false)
{
	global $vbulletin;

	if ($avatarinfo = $vbulletin->db->query_first_slave("
		SELECT user.avatarid, user.avatarrevision, avatarpath, NOT ISNULL(customavatar.userid) AS hascustom, customavatar.dateline,
			customavatar.width, customavatar.height, customavatar.width_thumb, customavatar.height_thumb
		FROM " . TABLE_PREFIX . "user AS user
		LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON avatar.avatarid = user.avatarid
		LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON customavatar.userid = user.userid
		WHERE user.userid = $userid"))
	{
		if (!empty($avatarinfo['avatarpath']))
		{
			return array($avatarinfo['avatarpath']);
		}
		else if ($avatarinfo['hascustom'])
		{
			$avatarurl = array('hascustom' => 1);

			if ($vbulletin->options['usefileavatar'])
			{
				$avatarurl[] = $vbulletin->options['avatarurl'] . ($thumb ? '/thumbs' : '') . "/avatar{$userid}_{$avatarinfo['avatarrevision']}.gif";
			}
			else
			{
				$avatarurl[] = "image.php?u=$userid&amp;dateline=$avatarinfo[dateline]" . ($thumb ? '&type=thumb' : '') ;
			}

			if ($thumb)
			{
				if ($avatarinfo['width_thumb'] AND $avatarinfo['height_thumb'])
				{
					$avatarurl[] = " width=\"$avatarinfo[width_thumb]\" height=\"$avatarinfo[height_thumb]\" ";
				}
			}
			else
			{
				if ($avatarinfo['width'] AND $avatarinfo['height'])
				{
					$avatarurl[] = " width=\"$avatarinfo[width]\" height=\"$avatarinfo[height]\" ";
				}
			}
			return $avatarurl;
		}
		else
		{
			return '';
		}
	}
}

/**
 * Fetches the User's Avatar from Pre-processed information. Avatar data placed
 * in $userinfo (avatarurl, avatarwidth, avatarheight)
 *
 * @param	array	User Information
 * @param	boolean	Whether to return a Thumbnail
 * @param	boolean	Whether to return a placeholder Avatar if no avatar is found
*/
function fetch_avatar_from_userinfo(&$userinfo, $thumb = false, $returnfakeavatar = true)
{
	global $vbulletin, $stylevar;

	if (!empty($userinfo['avatarpath']))
	{
		// using a non custom avatar
		if ($thumb)
		{
			if (@file_exists(DIR . '/images/avatars/thumbs/' . $userinfo['avatarid'] . '.gif'))
			{
				$userinfo['avatarurl'] = 'images/avatars/thumbs/' . $userinfo['avatarid'] . '.gif';
			}
			else
			{
				// no width/height known, scale to the maximum allowed width
				$userinfo['avatarwidth'] = FIXED_SIZE_AVATAR_WIDTH;
				$userinfo['avatarurl'] = $userinfo['avatarpath'];
			}
		}
		else
		{
			$userinfo['avatarurl'] = $userinfo['avatarpath'];
		}
	}
	else if ($userinfo['hascustom'] OR $userinfo['hascustomavatar'])
	{
		// custom avatar
		if ($vbulletin->options['usefileavatar'])
		{
			if ($thumb AND @file_exists($vbulletin->options['avatarpath'] . "/thumbs/avatar$userinfo[userid]_$userinfo[avatarrevision].gif"))
			{
				$userinfo['avatarurl'] = $vbulletin->options['avatarurl'] . "/thumbs/avatar$userinfo[userid]_$userinfo[avatarrevision].gif";
			}
			else
			{
				$userinfo['avatarurl'] =  $vbulletin->options['avatarurl'] . "/avatar$userinfo[userid]_$userinfo[avatarrevision].gif";
			}
		}
		else
		{
			if ($thumb AND $userinfo['filedata_thumb'])
			{
				$userinfo['avatarurl'] = 'image.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $userinfo['userid'] . "&amp;dateline=$userinfo[avatardateline]&amp;type=thumb";
			}
			else
			{
				$userinfo['avatarurl'] = 'image.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $userinfo['userid'] . "&amp;dateline=$userinfo[avatardateline]";
			}
		}

		if ($thumb)
		{
			// use the known sizes if available, otherwise calculate as necessary
			if ($userinfo['width_thumb'])
			{
				$userinfo['avatarwidth'] = $userinfo['width_thumb'];
				$userinfo['avatarheight'] = $userinfo['height_thumb'];
			}
			else if ($userinfo['avwidth'] AND $userinfo['avheight'])
			{
				// resize to the most restrictive size; never increase size (ratios > 1)
				$resize_ratio = min(1, FIXED_SIZE_AVATAR_WIDTH / $userinfo['avwidth'], FIXED_SIZE_AVATAR_HEIGHT / $userinfo['avheight']);
				$userinfo['avatarwidth'] = floor($userinfo['avwidth'] * $resize_ratio);
				$userinfo['avatarheight'] = floor($userinfo['avheight'] * $resize_ratio);
			}
			else
			{
				// no width/height known, scale to the maximum allowed width
				$userinfo['avatarwidth'] = FIXED_SIZE_AVATAR_WIDTH;
			}
		}
		else
		{
			$userinfo['avatarwidth'] = $userinfo['avwidth'];
			$userinfo['avatarheight'] = $userinfo['avheight'];
		}
	}

	// final case: didn't get an avatar, so use the fake one
	if (empty($userinfo['avatarurl']) AND $returnfakeavatar)
	{
		$userinfo['avatarurl'] = $stylevar['imgdir_misc'] . '/unknown.gif';
	}
}

/**
 * Generates a totally random string
 *
 * @param	integer	Length of string to create
 *
 * @return	string	Generated String
 *
 */
function fetch_user_salt($length = 3)
{
	$salt = '';
	for ($i = 0; $i < $length; $i++)
	{
		$salt .= chr(rand(33, 126));
	}
	return $salt;
}

// nb: function verify_profilefields no longer exists, and is handled by vB_DataManager_User::set_userfields($values)

/**
 * Fetches the Profile Fields for a User input form
 *
 * @param	integer	Forum Type: 0 indicates a profile field, 1 indicates an option field
 *
 */
function fetch_profilefields($formtype = 0) // 0 indicates a profile field, 1 indicates an option field
{
	global $vbulletin, $stylevar, $customfields, $bgclass, $show;
	global $vbphrase, $altbgclass, $bgclass1, $tempclass;

	// get extra profile fields
	$profilefields = $vbulletin->db->query_read_slave("
		SELECT * FROM " . TABLE_PREFIX . "profilefield
		WHERE editable IN (1,2)
			AND form " . iif($formtype, '>= 1', '= 0'). "
		ORDER BY displayorder
	");
	while ($profilefield = $vbulletin->db->fetch_array($profilefields))
	{
		$profilefieldname = "field$profilefield[profilefieldid]";
		if ($profilefield['editable'] == 2 AND !empty($vbulletin->userinfo["$profilefieldname"]))
		{
			continue;
		}

		if ($formtype == 1 AND in_array($profilefield['type'], array('select', 'select_multiple')))
		{
			$show['optionspage'] = true;
		}
		else
		{
			$show['optionspage'] = false;
		}

		if (($profilefield['required'] == 1 OR $profilefield['required'] == 3) AND $profilefield['form'] == 0) // Ignore the required setting for fields on the options page
		{
			exec_switch_bg(1);
		}
		else
		{
			exec_switch_bg($profilefield['form']);
		}

		$tempcustom = fetch_profilefield($profilefield);

		// now add the HTML to the completed lists

		if (($profilefield['required'] == 1 OR $profilefield['required'] == 3) AND $profilefield['form'] == 0) // Ignore the required setting for fields on the options page
		{
			$customfields['required'] .= $tempcustom;
		}
		else
		{
			if ($profilefield['form'] == 0)
			{
				$customfields['regular'] .= $tempcustom;
			}
			else // not implemented
			{
				switch ($profilefield['form'])
				{
					case 1:
						$customfields['login'] .= $tempcustom;
						break;
					case 2:
						$customfields['messaging'] .= $tempcustom;
						break;
					case 3:
						$customfields['threadview'] .= $tempcustom;
						break;
					case 4:
						$customfields['datetime'] .= $tempcustom;
						break;
					case 5:
						$customfields['other'] .= $tempcustom;
						break;
					default:
						($hook = vBulletinHook::fetch_hook('profile_fetch_profilefields_loc')) ? eval($hook) : false;
				}
			}
		}


	}
}

/**
 * Fetches a single profile Field
 *
 * @param	array	Profile Field Record from the database
 * @param	string	Template to wrap this profilefield in
 *
 * @return	string	HTML for this Profile Field
 *
 */
function fetch_profilefield($profilefield, $wrapper_template = 'userfield_wrapper')
{
	global $vbulletin, $stylevar, $customfields, $bgclass, $show;
	global $vbphrase, $altbgclass, $bgclass1, $tempclass;

	$profilefieldname = "field$profilefield[profilefieldid]";
	$optionalname = $profilefieldname . '_opt';
	$optional = '';
	$optionalfield = '';

	$profilefield['title'] = $vbphrase[$profilefieldname . '_title'];
	$profilefield['description'] = $vbphrase[$profilefieldname . '_desc'];

	($hook = vBulletinHook::fetch_hook('profile_fetch_profilefields')) ? eval($hook) : false;

	if ($profilefield['type'] == 'input')
	{
		eval('$custom_field_holder = "' . fetch_template('userfield_textbox') . '";');
	}
	else if ($profilefield['type'] == 'textarea')
	{
		eval('$custom_field_holder = "' . fetch_template('userfield_textarea') . '";');
	}
	else if ($profilefield['type'] == 'select')
	{
		$data = unserialize($profilefield['data']);
		$selectbits = '';
		$foundselect = 0;
		foreach ($data AS $key => $val)
		{
			$key++;
			$selected = '';
			if ($vbulletin->userinfo["$profilefieldname"])
			{
				if (trim($val) == $vbulletin->userinfo["$profilefieldname"])
				{
					$selected = 'selected="selected"';
					$foundselect = 1;
				}
			}
			else if ($profilefield['def'] AND $key == 1)
			{
				$selected = 'selected="selected"';
				$foundselect = 1;
			}
			eval('$selectbits .= "' . fetch_template('userfield_select_option') . '";');
		}
		if ($profilefield['optional'])
		{
			if (!$foundselect AND (!empty($vbulletin->userinfo["$profilefieldname"]) OR $vbulletin->userinfo["$profilefieldname"] === '0'))
			{
				$optional = $vbulletin->userinfo["$profilefieldname"];
			}
			eval('$optionalfield = "' . fetch_template('userfield_optional_input') . '";');
		}
		if (!$foundselect)
		{
			$selected = 'selected="selected"';
		}
		else
		{
			$selected = '';
		}
		$show['noemptyoption'] = iif($profilefield['def'] != 2, true, false);
		eval('$custom_field_holder = "' . fetch_template('userfield_select') . '";');
	}
	else if ($profilefield['type'] == 'radio')
	{
		$data = unserialize($profilefield['data']);
		$radiobits = '';
		$foundfield = 0;
		$perline = 0;
		$unclosedtr = true;

		foreach ($data AS $key => $val)
		{
			$key++;
			$checked = '';
			if (!$vbulletin->userinfo["$profilefieldname"] AND $key == 1 AND $profilefield['def'] == 1)
			{
				$checked = 'checked="checked"';
			}
			else if (trim($val) == $vbulletin->userinfo["$profilefieldname"])
			{
				$checked = 'checked="checked"';
				$foundfield = 1;
			}
			if ($perline == 0)
			{
				$radiobits .= '<tr>';
			}
			eval('$radiobits .= "' . fetch_template('userfield_radio_option') . '";');
			$perline++;
			if ($profilefield['perline'] > 0 AND $perline >= $profilefield['perline'])
			{
				$radiobits .= '</tr>';
				$perline = 0;
				$unclosedtr = false;
			}
		}

		if ($unclosedtr)
		{
			$radiobits .= '</tr>';
		}

		if ($profilefield['optional'])
		{
			if (!$foundfield AND $vbulletin->userinfo["$profilefieldname"])
			{
				$optional = $vbulletin->userinfo["$profilefieldname"];
			}
			eval('$optionalfield = "' . fetch_template('userfield_optional_input') . '";');
		}
		eval('$custom_field_holder = "' . fetch_template('userfield_radio') . '";');
	}
	else if ($profilefield['type'] == 'checkbox')
	{
		$data = unserialize($profilefield['data']);
		$radiobits = '';
		$perline = 0;
		$unclosedtr = true;
		foreach ($data AS $key => $val)
		{
			if ($vbulletin->userinfo["$profilefieldname"] & pow(2,$key))
			{
				$checked = 'checked="checked"';
			}
			else
			{
				$checked = '';
			}
			$key++;
			if ($perline == 0)
			{
				$radiobits .= '<tr>';
			}
			eval('$radiobits .= "' . fetch_template('userfield_checkbox_option') . '";');
			$perline++;
			if ($profilefield['perline'] > 0 AND $perline >= $profilefield['perline'])
			{
				$radiobits .= '</tr>';
				$perline = 0;
				$unclosedtr = false;
			}
		}
		if ($unclosedtr)
		{
			$radiobits .= '</tr>';
		}
		eval('$custom_field_holder = "' . fetch_template('userfield_radio') . '";');
	}
	else if ($profilefield['type'] == 'select_multiple')
	{
		$data = unserialize($profilefield['data']);
		$selectbits = '';

		if ($profilefield['height'] == 0)
		{
			$profilefield['height'] = count($data);
		}

		foreach ($data AS $key => $val)
		{
			if ($vbulletin->userinfo["$profilefieldname"] & pow(2, $key))
			{
				$selected = 'selected="selected"';
			}
			else
			{
				$selected = '';
			}
			$key++;
			eval('$selectbits .= "' . fetch_template('userfield_select_option') . '";');
		}
		eval('$custom_field_holder = "' . fetch_template('userfield_select_multiple') . '";');
	}

	eval('$tempcustom = "' . fetch_template($wrapper_template) . '";');

	return $tempcustom;
}

/**
 * Checks whether the email provided is banned from the forums
 *
 * @param	string	The email address to check
 *
 * @return	boolean	Whether the email address is banned or not
 *
 */
function is_banned_email($email)
{
	global $vbulletin;

	if ($vbulletin->options['enablebanning'] AND $vbulletin->banemail !== null)
	{
		$bannedemails = preg_split('/\s+/', $vbulletin->banemail, -1, PREG_SPLIT_NO_EMPTY);

		foreach ($bannedemails AS $bannedemail)
		{
			if (is_valid_email($bannedemail))
			{
				$regex = '^' . preg_quote($bannedemail, '#') . '$';
			}
			else
			{
				$regex = preg_quote($bannedemail, '#') . ($vbulletin->options['aggressiveemailban'] ? '' : '$');
			}

			if (preg_match("#$regex#i", $email))
			{
				return 1;
			}
		}
	}

	return 0;
}

/**
 * (Re)Generates an Activation ID for a user
 *
 * @param	integer	User's ID
 * @param	integer	The group to move the user to when they are activated
 * @param	integer	0 for Normal Activation, 1 for Forgotten Password
 * @param	boolean	Whether this is an email change or not
 *
 * @return	string	The Activation ID
 *
 */
function build_user_activation_id($userid, $usergroupid, $type, $emailchange = 0)
{
	global $vbulletin;

	if ($usergroupid == 3 OR $usergroupid == 0)
	{ // stop them getting stuck in email confirmation group forever :)
		$usergroupid = 2;
	}

	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "useractivation WHERE userid = $userid AND type = $type");
	$activateid = vbrand(0,100000000);
	/*insert query*/
	$vbulletin->db->query_write("
		REPLACE INTO " . TABLE_PREFIX . "useractivation
			(userid, dateline, activationid, type, usergroupid, emailchange)
		VALUES
			($userid, " . TIMENOW . ", $activateid , $type, $usergroupid, " . intval($emailchange) . ")
	");

	if ($userinfo = fetch_userinfo($userid))
	{
		$userdata =& datamanager_init('User', $vbulletin, ERRTYPE_SILENT);
		$userdata->set_existing($userinfo);
		$userdata->set_bitfield('options', 'noactivationmails', 0);
		$userdata->save();
	}

	return $activateid;
}

/**
 * Constructs a Forum Jump Menu based on moderator permissions
 *
 * @param	integer	The "root" forum to work from
 * @param	integer	The ID of the forum that is currently selected
 * @param	integer	Characters to prepend to the item in the menu
 * @param	string	The moderator permission to check when building the Forum Jump Menu
 *
 * @return	string	The built forum Jump menu
 *
 */
function construct_mod_forum_jump($parentid = -1, $selectedid, $prependchars, $modpermission = '')
{
	global $vbulletin;

	if (empty($vbulletin->iforumcache))
	{
		cache_ordered_forums();
	}

	if (empty($vbulletin->iforumcache["$parentid"]) OR !is_array($vbulletin->iforumcache["$parentid"]))
	{
		return;
	}

	foreach($vbulletin->iforumcache["$parentid"] AS $forumid)
	{
		$forumperms = $vbulletin->userinfo['forumpermissions']["$forumid"];
		if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !can_moderate($forumid, $modpermission) OR $vbulletin->forumcache["$forumid"]['link'])
		{
			continue;
		}

		// set $forum from the $vbulletin->forumcache
		$forum = $vbulletin->forumcache["$forumid"];

		$optionvalue = $forumid;
		$optiontitle = $prependchars . " $forum[title_clean] ";
		$optionclass = 'fjdpth' . iif($forum['depth'] > 4, 4, $forum['depth']);
		$optionselected = '';

		if ($selectedid == $optionvalue)
		{
			$optionselected = 'selected="selected"';
			$optionclass = 'fjsel';
		}

		eval('$forumjumpbits .= "' . fetch_template('option') . '";');

		$forumjumpbits .= construct_mod_forum_jump($optionvalue, $selectedid, $prependchars . FORUM_PREPEND, $modpermission);

	} // end foreach ($vbulletin->iforumcache[$parentid] AS $forumid)

	return $forumjumpbits;

}

/**
* Constructs the User's Custom CSS
*
* @param	array	An array of userinfo
* @param	bool	(Return) Whether to show the user css on/off switch to the user
*
* @return	string	HTML for the User's CSS
*/
function construct_usercss(&$userinfo, &$show_usercss_switch)
{
	global $vbulletin;

	// profile styling globally disabled
	if (!($vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_profile_styling']))
	{
		$show_usercss_switch = false;
		return '';
	}

	// check if permissions have changed and we need to rebuild this user's css
	if ($userinfo['hascachedcss'] AND $userinfo['cssbuildpermissions'] != $userinfo['permissions']['usercsspermissions'])
	{
		require_once(DIR . '/includes/class_usercss.php');
		$usercss =& new vB_UserCSS($vbulletin, $userinfo['userid'], false);
		$userinfo['cachedcss'] = $usercss->update_css_cache();
	}

	if (!($vbulletin->userinfo['options'] & $vbulletin->bf_misc_useroptions['showusercss']) AND $vbulletin->userinfo['userid'] != $userinfo['userid'])
	{
		// user has disabled viewing css; they can reenable
		$show_usercss_switch = (trim($userinfo['cachedcss']) != '');
		$usercss = '';
	}
	else if (trim($userinfo['cachedcss']))
	{
		$show_usercss_switch = true;
		$userinfo['cachedcss'] = str_replace('/*sessionurl*/', $vbulletin->session->vars['sessionurl_js'], $userinfo['cachedcss']);
		eval('$usercss = "' . fetch_template('memberinfo_usercss') . '";');
	}
	else
	{
		$show_usercss_switch = false;
		$usercss = '';
	}

	return $usercss;
}

/**
* Constructs the User's Custom CSS Switch Phrase
*
* @param	bool	If the switch is going to be shown or not
* @param	string	The phrase to use (Reference)
*
* @return	void
*/
function construct_usercss_switch($show_usercss_switch, &$usercss_switch_phrase)
{
	global $vbphrase, $vbulletin;

	if ($show_usercss_switch AND $vbulletin->userinfo['userid'])
	{
		if ($vbulletin->userinfo['options'] & $vbulletin->bf_misc_useroptions['showusercss'])
		{
			$usercss_switch_phrase = $vbphrase['hide_user_customizations'];
		}
		else
		{
			$usercss_switch_phrase = $vbphrase['show_user_customizations'];
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 12:56, Sat Oct 11th 2008
|| # CVS: $RCSfile$ - $Revision: 27273 $
|| ####################################################################
\*======================================================================*/
?>
