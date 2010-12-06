<?php
/*
Please do not remove this Copyright notice.
As you see this is a free script accordingly under GPL laws and you will see
a license attached accordingly to GNU.
You may NOT rewrite another hack and release it to the vBulletin.org community
or another website using this code without our permission due to the fact 
we have a full version and this full version is a paid version, as this 
version will differ but some of the logistics are the same. 
So do NOT try to release a hack using any part of our code without written permission from us!
Copyright 2007, BlogToRank
*/
/*======================================================================*\
|| #################################################################### ||
|| # Copyright QH  vbMailer, www.BlogToRank.com, All rights Reserved! # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
include('./global.php');
global $vbulletin;

require_once(DIR . '/includes/qhvbmailer_functions.php');

$vbulletin->input->clean_array_gpc('g', array(
	'campaign_id' => TYPE_UINT,
	'email' => TYPE_STR,
	'u' => TYPE_STR
));
$campaign_id = $vbulletin->GPC['campaign_id'];
$email = $vbulletin->GPC['email'];
$url = $vbulletin->GPC['u'];

if($campaign_id > 0 && !empty($email)){
	if(empty($url)){
		insertTrack($campaign_id, $email, 'read');
	
		$tracks = countTracks($campaign_id, 'unique_read');
		if($tracks == 0){
			insertTrack($campaign_id, $email, 'unique_read');
		}
	} else {
		insertTrack($campaign_id, $email, 'link');
		header("Location: " . $url);
	}
}
/*
Please do not remove this Copyright notice.
As you see this is a free script accordingly under GPL laws and you will see
a license attached accordingly to GNU.
You may NOT rewrite another hack and release it to the vBulletin.org community
or another website using this code without our permission, So do NOT try to release a hack using any part of our code without written permission from us!
Copyright 2007, BlogToRank.com
QH vbMailer v2.1
December 2, 2007
*/
/*======================================================================*\
|| #################################################################### ||
|| # Copyright QH  vbMailer, www.BlogToRank.com, All rights Reserved! # ||
|| #################################################################### ||
\*======================================================================*/
?>