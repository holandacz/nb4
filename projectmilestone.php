<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Project Tools 2.0.0 - Licence Number VBP05E32E9
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'project');
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('projecttools', 'posting');

// get special data templates from the datastore
$specialtemplates = array(
	'pt_bitfields',
	'pt_permissions',
	'pt_issuestatus',
	'pt_issuetype',
	'pt_projects',
	'pt_categories',
	'pt_assignable',
	'pt_versions',
	'smiliecache',
	'bbcodecache',
);

// pre-cache templates used by all actions
$globaltemplates = array(
);

// pre-cache templates used by specific actions
$actiontemplates = array(
	'issuelist' => array(
		'pt_issuebit',
		'pt_issuelist_arrow',
		'pt_milestone_issuelist'
	),
	'milestone' => array(
		'pt_issuebit',
		'pt_milestone',
	),
	'project' => array(
		'pt_milestonebit',
		'pt_project_milestones'
	)
);

if (empty($_REQUEST['do']))
{
	if (!empty($_REQUEST['milestoneid']))
	{
		$_REQUEST['do'] = 'milestone';
		$actiontemplates['none'] =& $actiontemplates['milestone'];
	}
	else if (!empty($_REQUEST['projectid']))
	{
		$_REQUEST['do'] = 'project';
		$actiontemplates['none'] =& $actiontemplates['project'];
	}
}

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
if (empty($vbulletin->products['vbprojecttools']))
{
	standard_error(fetch_error('product_not_installed_disabled'));
}

require_once(DIR . '/includes/functions_projecttools.php');
require_once(DIR . '/includes/functions_pt_milestone.php');

if (!($vbulletin->userinfo['permissions']['ptpermissions'] & $vbulletin->bf_ugp_ptpermissions['canviewprojecttools']))
{
	print_no_permission();
}

($hook = vBulletinHook::fetch_hook('projectmilestone_start')) ? eval($hook) : false;

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if ($_REQUEST['do'] == 'issuelist')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'milestoneid' => TYPE_UINT,
		'pagenumber' => TYPE_UINT,
		'sortfield' => TYPE_NOHTML,
		'sortorder' => TYPE_NOHTML,
		'filter'    => TYPE_NOHTML
	));

	$milestone = verify_milestone($vbulletin->GPC['milestoneid']);
	$project = verify_project($milestone['projectid']);
	$projectperms = fetch_project_permissions($vbulletin->userinfo, $project['projectid']);

	$perms_query = build_issue_permissions_query($vbulletin->userinfo);
	if (empty($perms_query["$project[projectid]"]))
	{
		print_no_permission();
	}

	$milestone_types = fetch_viewable_milestone_types($projectperms);
	if (!$milestone_types)
	{
		print_no_permission();
	}

	// issues per page = 0 means "unlmiited"
	if (!$vbulletin->options['pt_issuesperpage'])
	{
		$vbulletin->options['pt_issuesperpage'] = 999999;
	}

	switch ($vbulletin->GPC['filter'])
	{
		case 'active':
			$status_flag_value = 0;
			break;

		case 'completed':
			$status_flag_value = 1;
			break;

		default:
			$vbulletin->GPC['filter'] = '';
			$status_flag_value = null;
	}
	$filter_value = $vbulletin->GPC['filter'];

	$status_limit = array();
	if ($vbulletin->GPC['filter'])
	{
		foreach ($vbulletin->pt_issuestatus AS $issuestatus)
		{
			if ($issuestatus['issuecompleted'] == $status_flag_value)
			{
				$status_limit[] = $issuestatus['issuestatusid'];
			}
		}

		if (!$status_limit)
		{
			standard_error(fetch_error('pt_no_issue_statues_represent_this_state'));
		}
	}

	require_once(DIR . '/includes/class_pt_issuelist.php');
	$issue_list =& new vB_Pt_IssueList($project, $vbulletin);
	$issue_list->set_sort($vbulletin->GPC['sortfield'], $vbulletin->GPC['sortorder']);

	$list_criteria = $perms_query["$project[projectid]"] . "
		AND issue.milestoneid = $milestone[milestoneid]
		AND issue.issuetypeid IN ('" . implode("','", $milestone_types) . "')
		" . ($status_limit ? "AND issue.issuestatusid IN (" . implode(',', $status_limit) . ")" : '') . "
		AND issue.visible IN ('visible', 'private')
	";

	$issue_list->exec_query($list_criteria, $vbulletin->GPC['pagenumber'], $vbulletin->options['pt_issuesperpage']);

	$nav_url_base = 'projectmilestone.php?' . $vbulletin->session->vars['sessionurl'] . "do=issuelist&amp;milestoneid=$milestone[milestoneid]" .
			($vbulletin->GPC['filter'] ? '&amp;filter=' . $vbulletin->GPC['filter'] : '');

	$sort_arrow = $issue_list->fetch_sort_arrow_array($nav_url_base);

	$pagenav = construct_page_nav(
		$issue_list->real_pagenumber,
		$vbulletin->options['pt_issuesperpage'],
		$issue_list->total_rows,
		$nav_url_base,
		($issue_list->sort_field != 'lastpost' ? '&amp;sort=' . urlencode($issue_list->sort_field) : '') .
			($issue_list->sort_order != 'desc' ? '&amp;order=asc' : '')
	);

	$issuebits = '';
	while ($issue = $db->fetch_array($issue_list->result))
	{
		$issuebits .= build_issue_bit($issue, $project, $projectperms["$issue[issuetypeid]"]);
	}

	// issue state filter
	$filter_options = array(
		'active'   => '',
		'completed' => '',
		'any'      => ''
	);
	$filter_options[$vbulletin->GPC['filter'] ? $vbulletin->GPC['filter'] : 'any'] = ' selected="selected"';

	// search box data
	$show['search_options'] = false;
	foreach ($milestone_types AS $milestone_typeid)
	{
		if ($projectperms["$milestone_typeid"]['generalpermissions'] & $vbulletin->pt_bitfields['general']['cansearch'])
		{
			$show['search_options'] = true;
			break;
		}
	}
	if ($show['search_options'])
	{
		$assignable_users = fetch_assignable_users_select($project['projectid']);
		$search_status_options = fetch_issue_status_search_select($projectperms);
	}

	// navbar and output
	$navbits = construct_navbits(array(
		'project.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['projects'],
		"project.php?" . $vbulletin->session->vars['sessionurl'] . "projectid=$project[projectid]" => $project['title_clean'],
		"projectmilestone.php?" . $vbulletin->session->vars['sessionurl'] . "milestoneid=$milestone[milestoneid]" => $milestone['title_clean'],
		'' => $vbphrase['issue_list']
	));
	eval('$navbar = "' . fetch_template('navbar') . '";');

	eval('print_output("' . fetch_template('pt_milestone_issuelist') . '");');
}

// #######################################################################
if ($_REQUEST['do'] == 'milestone')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'milestoneid' => TYPE_UINT,
	));

	$milestone = verify_milestone($vbulletin->GPC['milestoneid']);
	$project = verify_project($milestone['projectid']);
	$projectperms = fetch_project_permissions($vbulletin->userinfo, $project['projectid']);

	$perms_query = build_issue_permissions_query($vbulletin->userinfo);
	if (empty($perms_query["$project[projectid]"]))
	{
		print_no_permission();
	}

	$milestone_types = fetch_viewable_milestone_types($projectperms);
	if (!$milestone_types)
	{
		print_no_permission();
	}

	$counts = fetch_milestone_count_data("
		milestonetypecount.milestoneid = $milestone[milestoneid]
		AND milestonetypecount.issuetypeid IN ('" . implode("','", $milestone_types) . "')
	");

	$raw_counts = fetch_milestone_counts($counts["$milestone[milestoneid]"], $projectperms);
	$stats = prepare_milestone_stats($milestone, $raw_counts);

	require_once(DIR . '/includes/class_pt_issuelist.php');
	$issue_list =& new vB_Pt_IssueList($project, $vbulletin);
	$issue_list->calc_total_rows = false;

	$list_criteria = $perms_query["$project[projectid]"] . "
		AND issue.milestoneid = $milestone[milestoneid]
		AND issue.issuetypeid IN ('" . implode("','", $milestone_types) . "')
		AND issue.visible IN ('visible', 'private')
	";

	$issue_list->exec_query($list_criteria, 1, $vbulletin->options['pt_project_recentissues']);

	$issuebits = '';
	while ($issue = $db->fetch_array($issue_list->result))
	{
		$issuebits .= build_issue_bit($issue, $project, $projectperms["$issue[issuetypeid]"]);
	}

	// search box data
	$show['search_options'] = false;
	foreach ($milestone_types AS $milestone_typeid)
	{
		if ($projectperms["$milestone_typeid"]['generalpermissions'] & $vbulletin->pt_bitfields['general']['cansearch'])
		{
			$show['search_options'] = true;
			break;
		}
	}
	if ($show['search_options'])
	{
		$assignable_users = fetch_assignable_users_select($project['projectid']);
		$search_status_options = fetch_issue_status_search_select($projectperms);
	}

	// navbar and output
	$navbits = construct_navbits(array(
		'project.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['projects'],
		"project.php?" . $vbulletin->session->vars['sessionurl'] . "projectid=$project[projectid]" => $project['title_clean'],
		"projectmilestone.php?" . $vbulletin->session->vars['sessionurl'] . "projectid=$project[projectid]" => $vbphrase['milestones'],
		'' => $milestone['title_clean']
	));
	eval('$navbar = "' . fetch_template('navbar') . '";');

	eval('print_output("' . fetch_template('pt_milestone') . '");');
}

// #######################################################################
if ($_REQUEST['do'] == 'project')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'projectid' => TYPE_UINT,
		'viewall'   => TYPE_BOOL
	));

	$project = verify_project($vbulletin->GPC['projectid']);
	$projectperms = fetch_project_permissions($vbulletin->userinfo, $project['projectid']);

	$milestone_types = fetch_viewable_milestone_types($projectperms);
	if (!$milestone_types)
	{
		print_no_permission();
	}

	$milestone_data = $vbulletin->db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "pt_milestone
		WHERE projectid = $project[projectid]
		ORDER BY completeddate DESC, targetdate
	");
	if (!$db->num_rows($milestone_data))
	{
		standard_error(fetch_error('invalidid', $vbphrase['project'], $vbulletin->options['contactuslink']));
	}

	$counts = fetch_milestone_count_data("
		milestone.projectid = $project[projectid]
		AND milestonetypecount.issuetypeid IN ('" . implode("','", $milestone_types) . "')
	");

	$active_milestones = '';
	$no_target_milestones = '';
	$completed_milestones = '';
	$count_completed = 0;

	while ($milestone = $db->fetch_array($milestone_data))
	{
		if ($milestone['completeddate'] AND !$vbulletin->GPC['viewall'])
		{
			$count_completed++;
			continue;
		}

		$raw_counts = fetch_milestone_counts($counts["$milestone[milestoneid]"], $projectperms);
		$stats = prepare_milestone_stats($milestone, $raw_counts);

		if ($milestone['completeddate'])
		{
			eval('$completed_milestones .= "' . fetch_template('pt_milestonebit') . '";');
		}
		else if ($milestone['targetdate'])
		{
			eval('$active_milestones .= "' . fetch_template('pt_milestonebit') . '";');
		}
		else
		{
			eval('$no_target_milestones .= "' . fetch_template('pt_milestonebit') . '";');
		}
	}

	$show['active_milestones'] = ($active_milestones OR $no_target_milestones);
	$show['completed_placeholder'] = (!$vbulletin->GPC['viewall'] AND $count_completed);
	$count_completed = vb_number_format($count_completed);

	// navbar and output
	$navbits = construct_navbits(array(
		'project.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['projects'],
		"project.php?" . $vbulletin->session->vars['sessionurl'] . "projectid=$project[projectid]" => $project['title_clean'],
		'' => $vbphrase['milestones']
	));
	eval('$navbar = "' . fetch_template('navbar') . '";');

	eval('print_output("' . fetch_template('pt_project_milestones') . '");');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:45, Tue Nov 18th 2008
|| # RCS: $Revision: 26769 $
|| ####################################################################
\*======================================================================*/
?>