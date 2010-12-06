<?php

//Show Debug function
// $message - the message to display
// $severity - what kind of message:
//      . 0 = general message
//        1 = debug message
//        2 = warning message
//        3 = error message
//        4 = fatal error message (kills script!)
function showDebug($message = "", $severity = 0) {
	global $debug;

	if($severity == 0){       //Message
		$stag = "";
		$etag = "";
	} elseif($severity == 1){ //Debug
		$stag = "<b>Debug:</b> ";
		$etag = "";
	} elseif($severity == 2){ //Warning
		$stag = "<font color=\"yellow\"><b>Warning!</b> ";
		$etag = "</font>";
	} elseif($severity == 3){ //Error
		$stag = "<font color=\"red\"><b>Error!</b> ";
		$etag = "</font>";
	} elseif($severity == 4){ //Fatal Error
		$stag = "<font color=\"red\"><b>Fatal Error!</b> ";
		$etag = "</font>";
	}

	if($severity < 4){
		if(($severity == 1 && $debug = true) || $severity != 1){
			echo $stag . $message . $etag . "<br>";
		}
	} else {
		die($stag . $message . $etag);
	}
}

//Send Email function
function sendEmail($email, $subject, $text, $html = '', $campaign_id = 0, $attachment = '') {
	global $vbulletin;
	global $phpmailer;

	if(eregi('^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$', $email['email'])){
		if(empty($html)) $html = nl2br($text);
		//lwe override
		/*
		if(empty($vbulletin->options['qhvbmailer_textsig'])){
			$text .= "\r\n\r\nYou can unsubscribe at any time by visiting the forums where this was sent from and requesting not to receive emails from administrators any more.\r\n\r\n" . $vbulletin->options['qhvbmailer_siteurl'];
		} else {
			$text .= "\r\n\r\n" . $vbulletin->options['qhvbmailer_textsig'];
		}
		if(empty($vbulletin->options['qhvbmailer_htmlsig'])){
			$html .= "<br><br>You can unsubscribe at any time by visiting the forums where this was sent from and requesting not to receive emails from administrators any more.<br><br><a href=\"" . $vbulletin->options['qhvbmailer_siteurl'] . "\">" . $vbulletin->options['qhvbmailer_siteurl'] . "</a>";
		} else {
			$html .= "<br><br>" . nl2br($vbulletin->options['qhvbmailer_htmlsig']);
		}
		*/
		if($campaign_id > 0){
			$html .= "<img src=\"" . $vbulletin->options['qhvbmailer_siteurl'] . "/qhvbmailer_track.php?campaign_id=" . $campaign_id . "&email=" . $email['email'] . "\" width=\"0\" height=\"0\">";
			if($vbulletin->options['qhvbmailer_linktracking'] == true){
				$html = preg_replace("/<a(\s[^>]*)href=(\"??)([^\" >]*?)\\2[^>]*>(.*)<\/a>/siU", "<a\\1href=\\2" . $vbulletin->options['qhvbmailer_siteurl'] . "/qhvbmailer_track.php?campaign_id=" . $campaign_id . "&email=" . $email['email'] . "&u=\\3\\2>\\4</a>", $html);
			}
		}

		$phpmailer->to = NULL;
		$phpmailer->attachment = NULL;
		$phpmailer->AddAddress($email['email']);
		if(!empty($attachment)){
			$phpmailer->AddAttachment($vbulletin->options['sitepath'] . "/qhvbmailer_attachments/" . $attachment);
		}
		$phpmailer->Subject = $subject;
		foreach($email as $key => $value){
			$text = str_replace("[" . $key . "]", $value, $text);
			$html = str_replace("[" . $key . "]", $value, $html);
		}
		if($email['field999'] == 'Text'){
			$phpmailer->IsHTML(false);
			$phpmailer->Body = $text;
		} else {
			$phpmailer->IsHTML(true);
			$phpmailer->AltBody = $text;
			$phpmailer->Body = $html;
		}
		if($phpmailer->Send()){
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

//Get Attachment function
function getAttachment($id) {
	global $vbulletin;
	global $debug;

	if($id > 0){
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_attachments WHERE id='" . $id . "'";
		if($attachment = $vbulletin->db->query_first($sql)){
			$attachment = $attachment['filename'];
			showDebug("Attachment '" . $attachment . "' ready to send");
		} else {
			$attachment = '';
			showDebug("Invalid attachment ID: '" .  $id . "'", 2);
		}
	} else {
		$attachment = '';
		//showDebug("Attachment ID must be greater than zero", 1);
	}

	return $attachment;
}

//Get phrase function
function getQHPhrase($language_id, $template_id, $field_name, $prefix_code = '') {
	global $vbulletin;

	if(!empty($prefix_code)){
		$prefix_code .= "_";
	}

	$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $template_id . "'";
	if($template = $vbulletin->db->query_first($sql)){
		$sql = "SELECT * FROM " . TABLE_PREFIX . "phrase WHERE languageid='" . $language_id . "' AND varname='" . $prefix_code . "" . $template['id'] . "_" . $template['varname'] . "_" . $field_name . "'";
		if($phrase = $vbulletin->db->query_first($sql)){
			return $phrase['text'];
		} else {
			$sql = "SELECT * FROM " . TABLE_PREFIX . "phrase WHERE languageid='0' AND varname='" . $prefix_code . "" . $template['id'] . "_" . $template['varname'] . "_" . $field_name . "'";
			if($phrase = $vbulletin->db->query_first($sql)){
				return $phrase['text'];
			} else {
				return false;
			}
		}
	} else {
		return false;
	}
}

//Insert track function
function insertTrack($campaign_id, $email, $type) {
	global $vbulletin;

	$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_campaigns WHERE id='" . $campaign_id . "'";
	if($campaign = $vbulletin->db->query_first($sql)){
		$sql = "SELECT * FROM " . TABLE_PREFIX . "user WHERE email='" . $email . "'";
		if($user = $vbulletin->db->query_first($sql)){
			$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "qhvbmailer_tracks(created, campaign_id, email, type) VALUES('" . TIMENOW . "', '" . $campaign['id'] . "', '" . $user['email'] . "', '" . $vbulletin->db->escape_string($type) . "')");
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

//Count tracks function
function countTracks($campaign_id, $type) {
	global $vbulletin;

	$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_tracks WHERE campaign_id='" . $campaign_id . "' AND type='" . $type . "'";
	$tracks = $vbulletin->db->query_read_slave($sql);
	if($vbulletin->db->num_rows($tracks) > 0){
		return $vbulletin->db->num_rows($tracks);
	} else {
		return 0;
	}
}

//Init PHPMailer function
function initPHPMailer() {
	global $vbulletin;

	require_once(DIR . '/includes/phpmailer/class.phpmailer.php');
	$phpmailer = new PHPMailer;

	$phpmailer->Mailer = $vbulletin->options['qhvbmailer_mailwith'];
	if(!empty($vbulletin->options['qhvbmailer_sendmail'])) $phpmailer->Sendmail = $vbulletin->options['qhvbmailer_sendmail'];
	if(!empty($vbulletin->options['qhvbmailer_smtphost'])) $phpmailer->Host = $vbulletin->options['qhvbmailer_smtphost'];
	if(!empty($vbulletin->options['qhvbmailer_smtpuser']) && !empty($vbulletin->options['qhvbmailer_smtppass'])){
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $vbulletin->options['qhvbmailer_smtpuser'];
		$phpmailer->Password = $vbulletin->options['qhvbmailer_smtppass'];
	}
	$phpmailer->From = $vbulletin->options['qhvbmailer_fromemail'];
	$phpmailer->FromName = $vbulletin->options['qhvbmailer_fromname'];
	$phpmailer->AddReplyTo($vbulletin->options['qhvbmailer_fromemail'], $vbulletin->options['qhvbmailer_fromname']);

	return $phpmailer;
}

//Date only function
function dateOnly() {
	return strtotime(date("Ymd"));
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