<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Project Tools 2.0.0 - Licence Number VBP05E32E9
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

require_once(DIR . '/includes/functions_projecttools.php');

/**
* Prepare to show projects in the forum bit list.
*
* @param	array	(Output) List of forums which show selected projects after. [forumid][] = projectid
* @param	array	(Output) Last post/counts for the selected projects
*/
function pt_forumbit_setup(&$project_forums, &$project_types)
{
	global $vbulletin, $db;

	$after_projects = array();

	$project_forums = array();
	$project_types = array();

	if (!is_array($vbulletin->pt_projects))
	{
		return;
	}

	if (!($vbulletin->userinfo['permissions']['ptpermissions'] & $vbulletin->bf_ugp_ptpermissions['canviewprojecttools']))
	{
		return;
	}

	foreach ($vbulletin->pt_projects AS $project)
	{
		if ($project['afterforumids'])
		{
			$after = explode(',', $project['afterforumids']);
			foreach ($after AS $afterforumid)
			{
				$project_forums["$afterforumid"][] = $project['projectid'];
			}

			$after_projects[] = $project['projectid'];
		}
	}

	if ($after_projects)
	{
		build_project_private_lastpost_sql_all($vbulletin->userinfo,
			$private_lastpost_join, $private_lastpost_fields
		);
		$marking = ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid']);

		$project_type_data = $db->query_read("
			SELECT projecttype.*
				" . ($marking ? ", projectread.readtime AS projectread" : '') . "
				" . ($private_lastpost_fields ? ", $private_lastpost_fields" : '') . "
			FROM " . TABLE_PREFIX . "pt_projecttype AS projecttype
			" . ($marking ? "
				LEFT JOIN " . TABLE_PREFIX . "pt_projectread AS projectread ON
					(projectread.projectid = projecttype.projectid AND projectread.issuetypeid = projecttype.issuetypeid AND projectread.userid = " . $vbulletin->userinfo['userid'] . ")
			" : '') . "
			$private_lastpost_join
			WHERE projecttype.projectid IN (" . implode(',', $after_projects) . ")
		");
		while ($project_type = $db->fetch_array($project_type_data))
		{
			$project_types["$project_type[projectid]"]["$project_type[issuetypeid]"] = $project_type;
		}
	}
}

/**
* Displays project information after this forum (if selected by the user).
* Requires $project_forums and $project_types to be global
*
* @param	array	Forum information
*
* @return	string	Project bits
*/
function pt_forumbit_display(&$forum)
{
	global $vbulletin, $db, $stylevar, $vbphrase, $show, $template_hook;
	global $project_forums, $project_types;

	$projectbits = '';

	if (isset($project_forums["$forum[forumid]"]))
	{
		foreach ($project_forums["$forum[forumid]"] AS $projectid)
		{
			$project = $vbulletin->pt_projects["$projectid"];

			$projectperms = fetch_project_permissions($vbulletin->userinfo, $project['projectid']);
			$project['lastpost'] = 0;
			$show['private_lastpost'] = false;
			$project['newflag'] = false;

			$type_counts = '';
			foreach ($project_types["$project[projectid]"] AS $type)
			{
				if (!($projectperms["$type[issuetypeid]"]['generalpermissions'] & $vbulletin->pt_bitfields['general']['canview']))
				{
					continue;
				}

				if ($projectperms["$type[issuetypeid]"]['generalpermissions'] & $vbulletin->pt_bitfields['general']['cansearch'])
				{
					$show['search_options'] = true;
				}

				if ($type['lastpost'] > $project['lastpost'])
				{
					$project['lastpost'] = $type['lastpost'];
					$project['lastpostuserid'] = $type['lastpostuserid'];
					$project['lastpostusername'] = $type['lastpostusername'];
					$project['lastpostid'] = $type['lastpostid'];
					$project['lastissueid'] = $type['lastissueid'];
					$project['lastissuetitle'] = $type['lastissuetitle'];

					$show['private_lastpost'] = (($projectperms["$type[issuetypeid]"]['generalpermissions'] & $vbulletin->pt_bitfields['general']['canviewothers']) ? false : true);
				}

				$typename = $vbphrase["issuetype_$type[issuetypeid]_plural"];
				$type['issuecount'] = vb_number_format($type['issuecount']);

				if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
				{
					$projettypeview = max($type['projectread'], TIMENOW - ($vbulletin->options['markinglimit'] * 86400));
				}
				else
				{
					$projettypeview = intval(fetch_bbarray_cookie('project_lastview', $project['projectid'] . $type['issuetypeid']));
					if (!$projettypeview)
					{
						$projettypeview = $vbulletin->userinfo['lastvisit'];
					}
				}
				if ($type['lastpost'] > $projettypeview)
				{
					$type['newflag'] = true;
					$project['newflag'] = true;
				}
				$project['projectread'] = max($project['projectread'], $projettypeview);

				$type['countid'] = "project_typecount_$project[projectid]_$forum[forumid]_$type[issuetypeid]";

				eval('$type_counts .= "' . fetch_template('pt_projectbit_typecount') . '";');
			}

			if (!$type_counts)
			{
				continue;
			}

			if ($project['lastpost'])
			{
				$project['lastpostdate'] = vbdate($vbulletin->options['dateformat'], $project['lastpost'], true);
				$project['lastposttime'] = vbdate($vbulletin->options['timeformat'], $project['lastpost']);
				$project['lastissuetitle_short'] = fetch_trimmed_title(fetch_censored_text($project['lastissuetitle']));
			}
			else
			{
				$project['lastpostdate'] = '';
				$project['lastposttime'] = '';
			}

			($hook = vBulletinHook::fetch_hook('project_forumbit')) ? eval($hook) : false;

			eval('$projectbits .= "' . fetch_template('pt_forumbit_project') . '";');
		}
	}

	return $projectbits;
}

/**
* Displays project information after this forum (if selected by the user), in the subforum list.
* Requires $project_forums and $project_types to be global
*
* @param	array	Forum information
*
* @return	string	Project bits
*/
function pt_subforumbit_display(&$forum)
{
	global $vbulletin, $db, $stylevar, $vbphrase, $show, $template_hook;
	global $project_forums, $project_types;

	$projectbits = '';

	if (isset($project_forums["$forum[forumid]"]))
	{
		foreach ($project_forums["$forum[forumid]"] AS $projectid)
		{
			$project = $vbulletin->pt_projects["$projectid"];

			$projectperms = fetch_project_permissions($vbulletin->userinfo, $project['projectid']);
			$project['lastactivity'] = 0;

			$can_view = false;
			foreach ($project_types["$project[projectid]"] AS $type)
			{
				if (!($projectperms["$type[issuetypeid]"]['generalpermissions'] & $vbulletin->pt_bitfields['general']['canview']))
				{
					continue;
				}

				$can_view = true;
				break;
			}

			if (!$can_view)
			{
				continue;
			}

			//($hook = vBulletinHook::fetch_hook('project_subforumbit')) ? eval($hook) : false;

			eval('$projectbits .= "' . fetch_template('pt_subforumbit_project') . '";');
		}
	}

	return $projectbits;
}


/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:45, Tue Nov 18th 2008
|| # RCS: $Revision: 27494 $
|| ####################################################################
\*======================================================================*/
?>