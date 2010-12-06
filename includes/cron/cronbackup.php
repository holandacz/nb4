<?php
/*
<!-- $Header: d:\cvs/3.7/Auto-backup/includes/cron/cronbackup.php,v 3.2 2008/01/06 15:32:44 pem Exp $ -->

Auto-Backup (Lite) for vBulletin 3.7 - Paul M - v 3.7.001
This lite version is adapted from the original 3.0 version by Trigunflame.
*/

error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db)) exit;

@ignore_user_abort(1);
@set_magic_quotes_runtime(0);

@set_time_limit(3600); // 1 Hour Timeout

require_once(DIR.'/includes/adminfunctions.php');
require_once(DIR.'/includes/mysqlbackupconfig.php');
require_once(DIR.'/includes/mysqlbackup.php');

if (method_exists($vbulletin->datastore,'do_fetch'))
{ // Datastore extension exists, use it
	$vbulletin->datastore->do_fetch('options',$errors);
	if ($errors[0])
	{ // Fetch failed, use original datastore
		$vbulletin->datastore->do_db_fetch("'options'");
	}
}
else
{ // No extension, use original datastore
	$vbulletin->datastore->do_db_fetch("'options'");
}

$mysqlBackup = &new mysqlBackup($backup, $vbulletin->db, $vbulletin->options);

$mysqlBackup->cronBackup();

echo $mysqlBackup->STATUS;
log_cron_action($mysqlBackup->STATUS, $nextitem);

?>
