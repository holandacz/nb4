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

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

require_once(DIR . '/includes/functions_projecttools.php');

/**
* Shows the new subscribed PT issues in the user CP
*
* @return	string	Printable issue bits
*/
function process_new_subscribed_issues()
{
	global $vbulletin, $show, $stylevar, $vbphrase, $template_hook, $vbcollapse;

	if (!($vbulletin->userinfo['permissions']['ptpermissions'] & $vbulletin->bf_ugp_ptpermissions['canviewprojecttools']))
	{
		return '';
	}

	$perms_query = build_issue_permissions_query($vbulletin->userinfo);
	if (!$perms_query)
	{
		return '';
	}

	$marking = ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid']);
	if ($marking)
	{
		$issueview_sql = "IF(issueread IS NOT NULL, issueread, " . intval(TIMENOW - ($vbulletin->options['markinglimit'] * 86400)) . ")";
		$issueview_sql2 = "IF(projectread IS NOT NULL, projectread, " . intval(TIMENOW - ($vbulletin->options['markinglimit'] * 86400)) . ")";
	}
	else
	{
		$issueview = max(intval(fetch_bbarray_cookie('issue_lastview', $issue['issueid'])), intval(fetch_bbarray_cookie('issue_lastview', $issue['projectid'] . $issue['issuetypeid'])));
		if (!$issueview)
		{
			$issueview = $vbulletin->userinfo['lastvisit'];
		}
		$issueview_sql = intval($issueview);
		$issueview_sql2 = '';
	}

	build_issue_private_lastpost_sql_all($vbulletin->userinfo, $private_lastpost_join, $private_lastpost_fields);

	$replycount_clause = fetch_private_replycount_clause($vbulletin->userinfo);

	$subscriptions = $vbulletin->db->query_read("
		SELECT issue.*, issuesubscribe.subscribetype,
			project.title_clean
			" . ($marking ? ", issueread.readtime AS issueread, projectread.readtime AS projectread" : '') . "
			" . ($private_lastpost_fields ? ", $private_lastpost_fields" : '') . "
			" . ($replycount_clause ? ", $replycount_clause AS replycount" : '') . "
		FROM " . TABLE_PREFIX . "pt_issuesubscribe AS issuesubscribe
		INNER JOIN " . TABLE_PREFIX . "pt_issue AS issue ON (issue.issueid = issuesubscribe.issueid)
		INNER JOIN " . TABLE_PREFIX . "pt_project AS project ON (project.projectid = issue.projectid)
		" . ($marking ? "
			LEFT JOIN " . TABLE_PREFIX . "pt_issueread AS issueread ON (issueread.issueid = issue.issueid AND issueread.userid = " . $vbulletin->userinfo['userid'] . ")
			LEFT JOIN " . TABLE_PREFIX . "pt_projectread as projectread ON (projectread.projectid = issue.projectid AND projectread.userid = " . $vbulletin->userinfo['userid'] . " AND projectread.issuetypeid = issue.issuetypeid)
		" : '') . "
		$private_lastpost_join
		WHERE issuesubscribe.userid = " . $vbulletin->userinfo['userid'] . "
			AND (" . implode(' OR ', $perms_query) . ")
		HAVING lastpost > " . intval(TIMENOW - ($vbulletin->options['markinglimit'] * 86400)) . "
			AND lastpost > " . $issueview_sql . "
			" . (!empty($issueview_sql2) ? " AND lastpost > " . $issueview_sql2 : '' ) . "
		ORDER BY lastpost DESC
	");

	$show['issuebit_project_title'] = true;
	$subscriptionbits = '';
	while ($issue = $vbulletin->db->fetch_array($subscriptions))
	{
		$issue = prepare_issue($issue);
		eval('$subscriptionbits .= "' . fetch_template('pt_issuebit') . '";');
	}

	if (!$subscriptionbits)
	{
		return '';
	}

	eval('$return = "' . fetch_template('pt_usercp_subscriptions') . '";');
	return $return;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:45, Tue Nov 18th 2008
|| # RCS: $Revision: 27328 $
|| ####################################################################
\*======================================================================*/
?>