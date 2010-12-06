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
require_once(DIR . '/includes/qhvbmailer_class_pop3.php');
$pop3 = new POP3(false, "", true);
$debug = true;

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminsettings'))
{
	print_cp_no_permission();
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
showDebug("Running bounce management cron...");

if($pop3->connect($vbulletin->options['qhvbmailer_smtphost'])){
	showDebug("Connected to " . $vbulletin->options['qhvbmailer_smtphost']);
	if($pop3->login($vbulletin->options['qhvbmailer_smtpuser'], $vbulletin->options['qhvbmailer_smtppass'])){
		showDebug("Logged in using " . $vbulletin->options['qhvbmailer_smtpuser']);
		if($msg_list = $pop3->get_office_status()){
			showDebug("Fetched message list");
			showDebug();
			for($i = 1; $i <= $msg_list["count_mails"]; $i++){
				if(!$header = $pop3->get_top($i)){
					showDebug($pop3->error, 3);
				}
				
				$g = 0;
				while(!ereg("</HEADER>",$header[$g])){
					if(eregi("X-Failed-Recipients", $header[$g])){
						$email = trim(eregi_replace("X-Failed-Recipients: ", "", $header[$g]));
						showDebug("Found bounce [" . $email . "]");
						$email_query = $vbulletin->db->query_read_slave("SELECT * FROM " . TABLE_PREFIX . "user WHERE email='" . $email . "'");
						if($vbulletin->db->num_rows($email_query) > 0){
							$user = $vbulletin->db->fetch_array($email_query);
							$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET options = options - 16 WHERE options & 16 AND userid = '" . $user['userid'] . "'");
							$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET options = options - 4096 WHERE options & 4096 AND userid = '" . $user['userid'] . "'");
							$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET options = options - 256 WHERE options & 256 AND userid = '" . $user['userid'] . "'");
							$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "subscribethread SET emailupdate = 0 WHERE userid = '" . $user['userid'] . "'");
							$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "subscribeforum SET emailupdate = 0 WHERE userid = '" . $user['userid'] . "'");
							showDebug($email . ": Email inactivated");
							
							$adminu = $vbulletin->db->query_read_slave("SELECT * FROM " . TABLE_PREFIX . "user WHERE username='" . $vbulletin->options['qhvbmailer_pmsfrom'] . "'");
							if($vbulletin->db->num_rows($adminu) > 0){
								$updatelink = $vbulletin->options['bburl'] . "/profile.php?" . $session['sessionurl'] . "do=editpassword";
								$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY); 
								$pmdm->set('fromuserid', $adminu['userid']); 
								$pmdm->set('fromusername', $adminu['username']); 
								$pmdm->overridequota = true;
								$pmdm->set('title', 'Your email is invalid!'); 
								$pmdm->set('message', 'It has come to our attention that your email address is invalid, due to a bounce we received when attempting to email you. We encourage you to update your email by visiting
								
								[url]' . $updatelink . '[/url]
								
								Thank you!'); 
								$pmdm->set_recipients($user['username'], $botpermissions); 
								$pmdm->set('dateline', TIMENOW);
								$pmdm->pre_save(); 
								$pmdm->save(); 
								showDebug($email . ": User PM sent");
							
								$adminu = $vbulletin->db->fetch_array($adminu);
								$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY); 
								$pmdm->set('fromuserid', $adminu['userid']); 
								$pmdm->set('fromusername', $adminu['username']); 
								$pmdm->overridequota = true;
								$pmdm->set('title', 'Email ' . $user['email'] . ' has bounced!'); 
								$pmdm->set('message', 'You are receiving this message because the email address ' . $vbulletin->userinfo['email'] . ' has bounced upon attempted emailing. The user info pertaining to this email is:
								
								User ID: ' . $user['userid'] . '
								Username: ' . $user['username']); 
								$pmdm->set_recipients($adminu['username'], $botpermissions); 
								$pmdm->set('dateline', TIMENOW);
								$pmdm->pre_save(); 
								$pmdm->save(); 
								showDebug($email . ": Admin PM sent");
							} else {
								showDebug($email . ": Invalid PMs from account", 2);
							}
						} else {
							showDebug($email . ": Email does not exist");
						}
						
						if($pop3->delete_mail($i)){
							showDebug($email . ": Email deleted");
						} else {
							showDebug($email . ": Error deleting email", 3);
						}
					}
					$g++;
				}
				unset($g);
			}
		} else {
			echo $pop3->error;
		}
		$pop3->close();
		showDebug("Connection closed");
	} else {
		showDebug("Could not login with " . $vbulletin->options['qhvbmailer_smtpuser'], 3);
	}
} else {
	showDebug("Could not connect to " . $vbulletin->options['qhvbmailer_smtphost'], 3);
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