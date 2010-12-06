<?php
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

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ########################## REQUIRE BACK-END ############################
global $vbulletin;
require_once(DIR . '/includes/qhvbmailer_functions.php');
require_once(DIR . '/includes/adminfunctions_language.php');
$phpmailer = initPHPMailer();
$debug = true;

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminsettings'))
{
	print_cp_no_permission();
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
showDebug("Running autosender cron...");
showDebug();

$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE orderr >= 1 ORDER BY orderr";
$stemplates = $vbulletin->db->query_read_slave($sql);
while($template = $vbulletin->db->fetch_array($stemplates)){
	$templates[] = $template;
}

foreach($templates as $template){
	showDebug("Checking autosender [ID: " . $template['id'] . "]");

	$day = number_format($template['orderr'], 0) - 1;
	$sql = "SELECT * FROM " . TABLE_PREFIX . "user WHERE qhvbmailer_ar_day='" . $day . "' AND qhvbmailer_ar_last < '" . dateOnly() . "'";
	$users = $vbulletin->db->query_read_slave($sql);
	while($user = $vbulletin->db->fetch_array($users)){
		$userinfo = fetch_userinfo($user['userid'], 1);
		if($userinfo['adminemail'] > 0){
			$ntemplates = array_reverse($templates);
			foreach($ntemplates as $template2){
				$day2 = number_format($template2['orderr'], 0) - 1;
				if($day2 == $user['qhvbmailer_ar_day']){
					$subject = getQHPhrase($userinfo['languageid'], $template2['id'], 'subject', 'ar');
					$attachment = getAttachment($template2['attachment_id']);
					$text = getQHPhrase($userinfo['languageid'], $template2['id'], 'text', 'ar');
					$html = getQHPhrase($userinfo['languageid'], $template2['id'], 'html', 'ar');
					
					if(!empty($subject) && !empty($text) && !empty($html)){
						if(sendEmail($user, $subject, $text, $html, 0, $attachment)){
							showDebug("Email sent to '" . $user['email'] . "' [ID: " . $user['userid'] . "]");
							
							$sends++;
							if($sends == $vbulletin->options['qhvbmailer_sleepafter']){
								showDebug("Sleeping for " . $vbulletin->options['qhvbmailer_sleepamount'] . " seconds");
								sleep($vbulletin->options['qhvbmailer_sleepamount']);
								$sends = 0;
							}
						} else {
							showDebug("Email cannot be sent to '" . $user['email'] . "' [ID: " . $user['userid'] . "]", 2);
						}
					} else {
						showDebug("Could not get phrases for '" . $user['email'] . "' [ID: " . $user['userid'] . "]", 3);
					}
				}
			}
		} else {
			showDebug("'" . $user['email'] . "' [ID: " . $user['userid'] . "] does not wish to receive emails");
		}
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET qhvbmailer_ar_day=qhvbmailer_ar_day+1, qhvbmailer_ar_last='" . dateOnly() . "' WHERE userid='" . $user['userid'] . "'");
	}
	
	showDebug();
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