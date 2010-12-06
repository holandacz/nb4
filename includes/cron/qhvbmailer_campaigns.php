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
showDebug("Running campaign cron...");
showDebug();

$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_campaigns WHERE send_when <= '" . TIMENOW . "' AND send_switch='0'";
$campaigns = $vbulletin->db->query_read_slave($sql);
while($campaign = $vbulletin->db->fetch_array($campaigns)){
	if($template = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $campaign['template_id'] . "'")){
		$attachment = getAttachment($campaign['attachment_id']);

		showDebug("Checking campaign '" . $campaign['subject'] . "' [ID: " . $campaign['id'] . "]");

		if($campaign['send_to'] > 0){
			//$extra_sql = " WHERE usergroupid='" . $campaign['send_to'] . "'";
			//$extra_sql .= " OR membergroupids like '%" . $campaign['send_to'] . "%'";


			$extra_sql = " WHERE concat(',', usergroupid, if(STRCMP(membergroupids, ''), ',', ''), membergroupids) LIKE '%," . $campaign['send_to'] . "%'";




		}
		$sql = "SELECT * FROM " . TABLE_PREFIX . "user" . $extra_sql;
		$users = $vbulletin->db->query_read_slave($sql);
		while($user = $vbulletin->db->fetch_array($users)){
			$userinfo = fetch_userinfo($user['userid'], 1);
			// lwe added overrider for bloodless pros usergroup.
			if($campaign['send_to'] == '20' || $userinfo['adminemail'] > 0){
				$subject = getQHPhrase($userinfo['languageid'], $campaign['template_id'], 'subject');
				$text = getQHPhrase($userinfo['languageid'], $campaign['template_id'], 'text');
				$html = getQHPhrase($userinfo['languageid'], $campaign['template_id'], 'html');

				if(!empty($subject) && !empty($text) && !empty($html)){
					if(sendEmail($user, $subject, $text, $html, $campaign['id'], $attachment)){
						insertTrack($campaign['id'], $user['email'], 'sent');
						showDebug("Email sent to '" . $user['email'] . "' [ID: " . $user['userid'] . "]");

						$sends++;
						if($sends == $vbulletin->options['qhvbmailer_sleepafter']){
							showDebug("Sleeping for " . $vbulletin->options['qhvbmailer_sleepamount'] . " seconds");
							sleep($vbulletin->options['qhvbmailer_sleepamount']);
							$sends = 0;
						}
					} else {
						insertTrack($campaign['id'], $user['email'], 'failed');
						showDebug("Email cannot be sent to '" . $user['email'] . "' [ID: " . $user['userid'] . "]", 2);
					}
				} else {
					showDebug("Could not get phrases for '" . $user['email'] . "' [ID: " . $user['userid'] . "]", 3);
				}
			} else {
				showDebug("'" . $user['email'] . "' [ID: " . $user['userid'] . "] does not wish to receive emails");
			}
		}
	} else {
		showDebug("Template #" . $campaign['template_id'] . " does not exist", 3);
	}

	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "qhvbmailer_campaigns SET send_switch='1' WHERE id='" . $campaign['id'] . "'");
	showDebug("Campaign sent [ID: " . $campaign['id'] . "]");
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