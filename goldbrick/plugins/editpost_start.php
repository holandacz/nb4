<?php 
/**
 * Creates goldbrick media for edit post
 * 
 * @active      	true
 * @execution   	1
 * @version     	$Revision: 108 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * $lastmodified	$Date: 2007-10-28 23:10:50 -0700 (Sun, 28 Oct 2007) $
 */
if ($vbulletin->options['gb_enabled'] && $foruminfo['gb_enabled'] && ($vbulletin->userinfo['permissions']['gb_permissions'] & $vbulletin->bf_ugp['gb_permissions']['canuse']))
{
	require_once(DIR . '/goldbrick/includes/functions_public.php');
	
	$post['message'] = $postinfo['pagetext'];
	//$post['posthash'] = 0;

	$postinfo['pagetext'] = goldbrick_process_post(
		$post['message'], 
		$vbulletin->userinfo['userid'], 
		$postinfo['postid'], 
		$post['posthash']
	);
	
}

?>