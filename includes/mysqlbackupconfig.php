<?php
/*
<!-- $Header: d:\cvs/3.7/Auto-backup/includes/mysqlbackupconfig.php,v 3.2 2008/01/06 15:32:44 pem Exp $ -->

Auto-Backup (Lite) for vBulletin 3.7 - Paul M - v 3.7.001
This lite version is adapted from the original 3.0 version by Trigunflame.
*/

  // Combine all tables into one file
  $backup['COMBINE'] = 0;  // 0 = No, 1 = Yes

  // Close Forum during Backup
  $backup['SHUTDOWN'] = 0;  // 0 = No, 1 = Yes
  $backup['MESSAGE'] = "The Forum is closed because a database backup is in progress.";

  // File Saving Information
  $backup['DATE'] = 'Y-M-d';  // Backup file date format (See http://uk.php.net/date)
  $backup['PREFIX']	= '';  // Backup file prefix, applied to all files
  $backup['DUMP_PATH']	= '../backups/';  // Path to backups folder, must have a trailing slash

// -----------------------------------------------------------------------------------------------//

  // Backup Options
  $backup['LOCK'] = 0; // Lock Tables during dump
  $backup['REPAIR']	= 0; // Repair & Optimize Tables before dump

  // Backup Type To Use
  $backup['TYPE'] = 2; // 1 = Only Specified Tables, 2 = All except Specified Tables
  $backup['TABLES']	= array(); // Table List - e.g. array('table1', 'table2')

  // Backup Optimizations
  $backup['INNODB'] = 0; // Set to 1 if you have Innodb Tables

  // Execution Function
  $backup['COMMAND'] = 'exec'; // exec, system or passthru

?>
