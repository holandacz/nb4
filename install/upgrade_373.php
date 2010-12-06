<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.7.4 - Licence Number VBFD20582F
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

error_reporting(E_ALL & ~E_NOTICE);

define('THIS_SCRIPT', 'upgrade_373.php');
define('VERSION', '3.7.3');
define('PREV_VERSION', '3.7.2');

$phrasegroups = array();
$specialtemplates = array();

// #############################################################################
// require the code that makes it all work...
require_once('./upgradecore.php');

// #############################################################################
// welcome step
if ($vbulletin->GPC['step'] == 'welcome')
{
	if ($vbulletin->options['templateversion'] == PREV_VERSION)
	{
		echo "<blockquote><p>&nbsp;</p>";
		echo "$vbphrase[upgrade_start_message]";
		echo "<p>&nbsp;</p></blockquote>";
	}
	else
	{
		echo "<blockquote><p>&nbsp;</p>";
		echo "$vbphrase[upgrade_wrong_version]";
		echo "<p>&nbsp;</p></blockquote>";
		print_upgrade_footer();
	}
}

// #############################################################################
// FINAL step (notice the SCRIPTCOMPLETE define)
if ($vbulletin->GPC['step'] == 1)
{
	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "phrase
		WHERE varname LIKE 'notice\_%\_title'
			AND fieldname = 'global'
	");
	
	$db->query_write("
		ALTER TABLE " . TABLE_PREFIX . "event CHANGE utc utc DECIMAL(4,2) NOT NULL DEFAULT '0.0'
	");
	
	$rows = $db->query_first("SELECT COUNT(*) AS count FROM " . TABLE_PREFIX . "adminmessage WHERE varname = 'after_upgrade_password_check'");
	if ($rows['count'] == 0)
	{
		$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "adminmessage
				(varname, dismissable, script, action, execurl, method, dateline, status)
			VALUES
				('after_upgrade_password_check', 1, 'passwordcheck.php', 'check', 'passwordcheck.php?do=check', 'get', " . TIMENOW . ", 'undone')
			
		");
	}
	
	print_form_header('', '');
	print_table_header($upgrade_phrases['upgrade_373.php']['weak_password_notice']);
	print_description_row($upgrade_phrases['upgrade_373.php']['weak_password_description']);
	print_table_footer();

	// tell log_upgrade_step() that the script is done
	define('SCRIPTCOMPLETE', true);
}

// #############################################################################

print_next_step();
print_upgrade_footer();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 09:39, Wed Nov 5th 2008
|| # CVS: $RCSfile$ - $Revision: 13568 $
|| ####################################################################
\*======================================================================*/
?>