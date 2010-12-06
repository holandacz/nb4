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

/**
* Fetches the note history for a selected note.
*
* @param	integer		Issue note to find history for (assumed to already be cleaned!)
*
* @return	resource	Database result set
*/
function &fetch_note_history($issuenoteid)
{
	global $db;

	return $db->query_read_slave("
		SELECT issuenotehistory.*, user.username
		FROM " . TABLE_PREFIX . "pt_issuenotehistory AS issuenotehistory
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = issuenotehistory.userid)
		WHERE issuenotehistory.issuenoteid = $issuenoteid
		ORDER BY issuenotehistory.dateline DESC
	");
}

/**
* Builds the history bit for a selected history point
*
* @param	array	Array of information for this histoy point
* @param	object	BB code parser
*
* @return	string	History bit HTML
*/
function build_history_bit($history, &$bbcode)
{
	global $vbulletin, $vbphrase, $show, $stylevar;

	$history['editdate'] = vbdate($vbulletin->options['dateformat'], $history['dateline'], true);
	$history['edittime'] = vbdate($vbulletin->options['timeformat'], $history['dateline']);
	$history['message'] = $bbcode->parse($history['pagetext'], 'pt');
	if ($history['reason'] === '')
	{
		$history['reason'] = $vbphrase['n_a'];
	}

	($hook = vBulletinHook::fetch_hook('project_historybit')) ? eval($hook) : false;

	eval('$edit_history = "' . fetch_template('pt_historybit') . '";');
	return $edit_history;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:45, Tue Nov 18th 2008
|| # RCS: $Revision: 17793 $
|| ####################################################################
\*======================================================================*/
?>