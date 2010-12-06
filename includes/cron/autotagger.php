<?php
/*======================================================================*\
|| #################################################################### ||
|| # Automatic Thread Tagger                                          # ||
|| # ---------------------------------------------------------------- # ||
|| # Originally created by MrEyes (1.0 Beta 3)                        # ||
|| # Copyright ©2008 Marius Czyz. All Rights Reserved.                # ||
|| #################################################################### ||
\*======================================================================*/ 

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
	
if ($vbulletin->options['autotag_enabled_all'])
{
	
	require_once(DIR . '/includes/functions_autotagger.php'); 
	require_once(DIR . '/includes/functions_newpost.php'); 
	
	$threads = $vbulletin->db->query_read("SELECT
		t.taglist,
		t.dateline,
		t.forumid,
		t.postuserid,
		t.title,
		t.threadid,
		t.prefixid
		FROM " . TABLE_PREFIX . "thread as t
		WHERE t.taglist='' AND autoskip=0
	"); 
	

	
	$processed = 0;
	while ($thread = $vbulletin->db->fetch_array($threads) AND $processed < $vbulletin->options['autotag_cron_count'])
	{
			if (intval($thread['tagid']) == 0)
			{
				ProcessThread($thread);
				$processed++;	
			}
	}
	
	log_cron_action('Auto Thread Tagger processed '.$processed.' threads.', $nextitem);
}

?>