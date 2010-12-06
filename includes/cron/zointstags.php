<?php
# Zoints Thread Tags System 
#
# Copyright 2006 Zoints Inc.
# This code may not be redistributed without prior written consent.
#
error_reporting(E_ALL & ~E_NOTICE);

if (!is_object($vbulletin->db))
{
	exit;
}

if ($vbulletin->options['zointstags_on'] AND $vbulletin->options['zointstags_zoints'] AND !empty($vbulletin->options['zointstags_token']) AND !empty($vbulletin->options['zointstags_authkey']))
{
	# only get publicly viewable threads
	$guest = array();
	cache_permissions($guest);
	
	$visible = array();
	foreach ($vbulletin->forumcache as $forumid => $forum)
	{
		$forumperms = $guest['forumpermissions']["$forumid"];
		if ((!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) AND !$vbulletin->options['showprivateforums']) OR !$forum['displayorder'] OR !($forum['options'] & $vbulletin->bf_misc_forumoptions['active']))
		{
			continue;
		}
		$visible[] = $forumid;
	}
	if (!count($visible))
	{
		$visible = array(0);
	}

	# get recently changed tags from db
	$threads = array();
	$threadids = array();
	$firstpostids = array();
	$_threads = $vbulletin->db->query_read("
		SELECT thread.* FROM " . TABLE_PREFIX . "zoints_tag_update ztu
			LEFT JOIN " . TABLE_PREFIX . "thread thread ON(ztu.threadid = thread.threadid)
		WHERE thread.forumid IN(" . implode(',', $visible) . ")
		LIMIT 250
	");
	while ($thread = $vbulletin->db->fetch_array($_threads))
	{
		$threads[$thread['threadid']] = $thread;
		$threadids[] = $thread['threadid'];
		$firstpostids[] = $thread['firstpostid'];
	}
	
	if (count($threads))
	{
		# get the tags
		$tags = array();
		$_tags = $vbulletin->db->query_read("
			SELECT * FROM " . TABLE_PREFIX . "zoints_tag
			WHERE threadid IN(" . implode(',', $threadids) . ")
				AND autogen != 1
		");
		while ($tag = $vbulletin->db->fetch_array($_tags))
		{
			$tags[$tag['threadid']][] = $tag['tag'];
		}
		
		# get the first posts for 30 word process
		$posts = array();
		$_posts = $vbulletin->db->query_read("
			SELECT postid, pagetext FROM " . TABLE_PREFIX . "post
			WHERE postid IN(" . implode(',', $firstpostids) . ")
		");
		while (list($postid, $post) = $vbulletin->db->fetch_row($_posts))
		{
			# cut out first 30 words
			$post = strip_bbcode($post);
			$words = preg_split("#[\r\n\s]+#", $post, 31, PREG_SPLIT_NO_EMPTY);
			if (count($words) > 30)
			{
				array_pop($words);
				array_push($words, '...');
			}
			$posts[$postid] = implode(' ', $words);
		}
		
		# compile data
		
		$xmldata = '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n\n";
		$xmldata .= "<zointstags>\n";
		foreach ($threads as $threadid => $thread)
		{
			#$thread['url'] = $vbulletin->options['bburl'] . '/showthread.php?t=' . $threadid;
			$thread['url'] = 'showthread.php?t=' . $threadid;
			
			($hook = vBulletinHook::fetch_hook('zointstags_threadurl')) ? eval($hook) : false;
		
			if (!preg_match("#^[a-z]+://#i", $thread['url']))
			{
				$thread['url'] = $vbulletin->options['bburl'] . '/' . $thread['url'];
			}
		
			$xmldata .= "<thread id=\"$threadid\">\n";
			$xmldata .= "\t<title><![CDATA[{$thread['title']}]]></title>\n";
			$xmldata .= "\t<description><![CDATA[{$posts[$thread['firstpostid']]}]]></description>\n";
			$xmldata .= "\t<url><![CDATA[$thread[url]]]></url>\n";
			$xmldata .= "\t<tags>\n";
			if (count($tags[$threadid]))
			{
				foreach ($tags[$threadid] as $tag)
				{
					$xmldata .= "\t\t<tag><![CDATA[$tag]]></tag>\n";
				}
			}
			$xmldata .= "\t</tags>\n";
			$xmldata .= "</thread>\n";
		}
		$xmldata .= "</zointstags>";
		
		# clean up existing data, mark as 'unmodified'
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "zoints_tag_update WHERE threadid IN(" . implode(',', $threadids) . ")");
		
		require_once(DIR. '/includes/class_zointstags.php');
		$snoopy =& new ZointsTagsSnoopy();
		$snoopy->submit($vbulletin->options['zointstags_tagurl'], array(
			'cmd'		=> 'forum-submission',
			'auth-key'	=> $vbulletin->options['zointstags_authkey'],
			'token'		=> $vbulletin->options['zointstags_token'],
			'xmldata'	=> $xmldata
		));
		
		$log_entry = $snoopy->results;
		log_cron_action($log_entry, $nextitem, 1);
	}
}
?>