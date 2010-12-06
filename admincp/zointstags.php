<?php
# Zoints Thread Tags System 
#
# Copyright 2006 Zoints Inc.
# This code may not be redistributed without prior written consent.
#

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array('zointstags_list');

// ########################## REQUIRE BACK-END ############################
require_once('global.php');

// ############################# LOG ACTION ###############################

log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['zointstags_manager']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'view';
}

# 3.5 backwards compatibility
if (!is_array($vbulletin->zointstags_list))
{
	$vbulletin->zointstags_list = @unserialize($vbulletin->zointstags_list);
}


if ($_REQUEST['do'] == 'view')
{
	print_form_header('zointstags','stoplist');
	print_table_header($vbphrase['zointstags_autogen_stoplist']);
	print_description_row($vbphrase['zointstags_autogen_stoplist_desc']);
	print_textarea_row($vbphrase['zointstags_stopwords'], 'stoplist', @implode("\n", $vbulletin->zointstags_list));
	print_submit_row();

	print_form_header('zointstags','auto-populate');
	print_table_header($vbphrase['zointstags_auto_populate_old_threads']);
	print_description_row($vbphrase['zointstags_auto_populate']);
	print_submit_row($vbphrase['submit'], false);
	
	print_form_header('zointstags','reset');
	print_table_header($vbphrase['zointstags_reset']);
	print_description_row($vbphrase['zointstags_reset_desc']);
	print_submit_row($vbphrase['submit'], false);
}

if ($_POST['do'] == 'stoplist')
{
	$vbulletin->input->clean_gpc('p', 'stoplist', TYPE_STR);
	
	$words = preg_split("#[\r\n]+#", $vbulletin->GPC['stoplist'], -1, PREG_SPLIT_NO_EMPTY);
	
	# make proper tags out of them
	require_once(DIR . '/includes/class_zointstags.php');
	$z_tags =& new zointstags_tags();
	$z_tags->vbulletin =& $vbulletin;
	$z_tags->set_char_replacements($vbulletin->options['zointstags_char_replacement']);

	if (count($words))
	{
		foreach ($words as $k => $word)
		{
			$word = $z_tags->clean($word);
			
			if (empty($word))
			{
				unset($words[$k]);
			}
			else
			{
				$words[$k] = $word;
			}
		}
		$words = array_filter(array_unique($words));
		sort($words);
	}
	else
	{
		$words = array();
	}
	
	build_datastore('zointstags_list', serialize($words), 1);
	print_cp_redirect('zointstags.php?do=view');
}

if ($_POST['do'] == 'reset')
{
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "zoints_tag WHERE autogen = 1");
	print_stop_message('zointstags_reset_complete');
}

if ($_REQUEST['do'] == 'auto-populate')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'threadid'		=> TYPE_UINT
	));
	
	require_once(DIR . '/includes/class_zointstags.php');
	$z_tags =& new zointstags_tags();
	# replicate $z_tags->init($vbulletin);
	$z_tags->vbulletin =& $vbulletin;
	$z_tags->set_char_replacements($vbulletin->options['zointstags_char_replacement']);
	$z_tags->stopwords = $vbulletin->zointstags_list;
	$z_tags->postings = true;
	
	
	$nextthreadid = 1;
	list ($maxthreadid) = $db->query_first("SELECT MAX(threadid) FROM " . TABLE_PREFIX . "thread", DBARRAY_NUM);
	
	$conds = array();
	$exclude = @unserialize($vbulletin->options['zointstags_disable_forums']);
	if (is_array($exclude) AND count($exclude))
	{
		$conds[] = "forumid NOT IN(" . implode(',', $exclude) . ")";
	}
	if ($vbulletin->GPC['threadid'])
	{
		$conds[] = "threadid > " . $vbulletin->GPC['threadid'];
	}
	
	list ($totalthreads) = $db->query_first("SELECT COUNT(*) - 750 FROM " . TABLE_PREFIX . "thread " . (count($conds) ? "WHERE " . implode(' AND ', $conds) :  '') . "", DBARRAY_NUM);
	
	$highest = 0;
	$threadids = array();
	$threads = array();
	$_threads = $db->query_read("
		SELECT threadid, title FROM " . TABLE_PREFIX . "thread
		" . (count($conds) ? "WHERE " . implode(' AND ', $conds) :  '') . "
		ORDER BY threadid
		LIMIT 750
	");
	
	while ($thread = $db->fetch_array($_threads))
	{
		$threadids[] = $thread['threadid'];
		$threads[$thread['threadid']] = $thread['title'];
	}
	
	$curthreadid = key(array_reverse($threads, true));
	
	if (!count($threads))
	{
		define('CP_BACKURL', 'zointstags.php?do=view');
		print_stop_message('zointstags_autocompletion_done');
	}
	
	# get existing tags for threads to exlude from being tagged again
	
	$_tags = $db->query_read("SELECT threadid FROM " . TABLE_PREFIX . "zoints_tag WHERE threadid IN(" . implode(',', $threadids) . ")");
	while (list($threadid) = $db->fetch_row($_tags))
	{
		unset($threads[$threadid]);
	}
	if (!count($threads))
	{
		print_form_header('','');
		print_table_header($vbphrase['zointstags_progress']);
		print_description_row(construct_phrase($vbphrase['zointstags_there_are_x_threads_left'], $totalthreads));
		print_table_footer();
		print_cp_redirect("zointstags.php?do=auto-populate&threadid=" . $curthreadid, 3);
	}
	
	$tagbits = array();
	foreach ($threads as $threadid => $title)
	{
		$z_tags->tags = array();
		# get tags from title
		$words = preg_split("#[\r\n\t\s,]+#", $title, -1, PREG_SPLIT_NO_EMPTY);
		if (count($words))
		{
			foreach ($words as $word)
			{
				$word = str_replace('&amp;', ' ', $word);
				$z_tags->error = 0;
				$z_tags->add($word);
				if ($z_tags->error)
				{
					continue;
				}
			}
			
			if (count($z_tags->tags))
			{
				foreach ($z_tags->tags as $word)
				{
					$tagbits[] = "($threadid, '" . $db->escape_string($word) . "',1)";
				}
			}
		}
	}

	if (count($tagbits))
	{
		$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "zoints_tag
				(threadid, tag, autogen)
			VALUES " . implode(',', $tagbits) . "
		");
		
	}
	
	if ($curthreadid >= $maxthreadid)
	{
		define('CP_BACKURL', 'zointstags.php?do=view');
		print_stop_message('zointstags_autocompletion_done');
	}
	else
	{
		print_form_header('','');
		print_table_header($vbphrase['zointstags_progress']);
		print_description_row(construct_phrase($vbphrase['zointstags_there_are_x_threads_left'], $totalthreads));
		print_table_footer();
		print_cp_redirect("zointstags.php?do=auto-populate&threadid=" . $curthreadid, 3);
	}
}

print_cp_footer();
?>