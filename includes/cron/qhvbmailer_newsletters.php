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
$debug = false;
global $vbulletin;
require_once(DIR . '/includes/qhvbmailer_functions.php');
require_once(DIR . '/includes/adminfunctions_language.php');
$phpmailer = initPHPMailer();

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminsettings'))
{
	print_cp_no_permission();
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
showDebug("Running newsletter cron...");

$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE orderr < 1 AND nl_every > 0 ORDER BY created DESC";
$newsletters = $vbulletin->db->query_read_slave($sql);
while($newsletter = $vbulletin->db->fetch_array($newsletters)){
	$days = floor((TIMENOW - $newsletter['nl_last']) / 86400);
	if($days >= $newsletter['nl_every']){
		showDebug();
		showDebug("Time to send '" . $newsletter['varname'] . "'");
		
		$sql = "SELECT * FROM " . TABLE_PREFIX . "user";
		$users = $vbulletin->db->query_read_slave($sql);
		while($user = $vbulletin->db->fetch_array($users)){
			$userinfo = fetch_userinfo($user['userid'], 1);
			if($userinfo['adminemail'] > 0){
				$subject = getQHPhrase($userinfo['languageid'], $newsletter['id'], 'subject', 'nl');
				$attachment = getAttachment($newsletter['attachment_id']);
				$text = getQHPhrase($userinfo['languageid'], $newsletter['id'], 'text', 'nl');
				$html = getQHPhrase($userinfo['languageid'], $newsletter['id'], 'html', 'nl');
				
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
			} else {
				showDebug("'" . $user['email'] . "' [ID: " . $user['userid'] . "] has chosen not to receive emails");
			}
		}
		
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "qhvbmailer_templates SET nl_last='" . TIMENOW . "' WHERE id='" . $newsletter['id'] . "'");
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