<?PHP
/*
Original concept code by Colin F in this mod -
http://www.vbulletin.org/forum/showthread.php?t=68113

This code has been COMPLETELY rewritten from the ground up with the exception of including the free mimedecode class file.

Thanks to RedTyger, Bob Denny, Ed Kohwley and Chris McKeever for their additions and assistance with portions of this code.

Huge thanks also goes to the many individuals that have helped test various incarnations of this mod.

Most especially huge thanks to those that have donated!!

If you'd like to donate to the continued production of this modification please do so here -

https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=cyricx%40campgaea%2eorg&item_name=Email%20Integration%20Donation&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8'

-=-=-=-=- Cyricx -=-=-=-=-=-=-

Version 2.6.1
*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}


// ######################### REQUIRE BACK-END ############################
require_once(DIR . '/includes/functions_newpost.php');
require_once(DIR . '/includes/mimeDecode.php');
require_once(DIR . '/includes/functions_databuild.php');
require_once(DIR . '/includes/functions_file.php');
require_once(DIR . '/includes/functions_wysiwyg.php');

global $vbphrase;

// comment the below line out if you do not have thread prefixes installed by adding a // at the start of the line
//require_once(DIR . '/includes/functions_threadprefix.php');

// debug command - LEAVE THIS OFF!!!
// Turning this on will stop emails from being deleted from the server!
$debug = 0;
// enter the email address error messages should go to when debug is on.
$debugemail = 'larryweitel@gmail.com';

if (!function_exists(imap_open))
{
  echo "Imap not recognized!! You MUST enable IMAP extensions on your server in your php.ini file!<br>";
  continue;
}
else
{


vbmail_start();

$eiforums = $vbulletin->db->query_read("
	SELECT forumid
	FROM " . TABLE_PREFIX . "forum
	WHERE ei_active = 1
");

while ($eiforum = $vbulletin->db->fetch_array($eiforums))
{
	$foruminfo = fetch_foruminfo($eiforum['forumid']);
//$foruminfo['ei_connectiontype'] = 0;
	// ##### Determining Connection Type ######
	switch ($foruminfo['ei_connectiontype'])
	{
		case 0:
			$connectmailserver = "{" . $foruminfo[ei_hostname] . ":110/pop3/notls}" . $foruminfo[ei_folder] . "";
			break;
		case 1:
			$connectmailserver = "{" . $foruminfo[ei_hostname] . ":143/novalidate-cert}" . $foruminfo[ei_folder] . "";
			break;
		case 2:
			$connectmailserver = "{" . $foruminfo[ei_hostname] . ":993/pop3/ssl}" . $foruminfo[ei_folder] . "";
			break;
		case 3:
			$connectmailserver = "{" . $foruminfo[ei_hostname] . ":995/pop3/ssl/novalidate-cert}" . $foruminfo[ei_folder] . "";
			break;
		case 4:
			$connectmailserver = "{" . $foruminfo[ei_hostname] . ":993/imap/ssl}" . $foruminfo[ei_folder] . "";
			break;
		case 5:
			$connectmailserver = "{" . $foruminfo[ei_hostname] . ":995/imap/ssl/novalidate-cert}" . $foruminfo[ei_folder] . "";
			break;
	}

	// Defining user and password
	$user = $foruminfo['ei_username'];
	$pass = $foruminfo['ei_password'];
echo 'server connection: ' . $connectmailserver . "\n";
	// Opening mainbox
	//echo 'user: ' . $user;
	//echo 'pass: ' . $pass;
	$mailbox = imap_open($connectmailserver, $user, $pass);

	if ($mailbox == FALSE)
	{
		echo "<br />Failed to connect to the mailbox for forum - " . $foruminfo['title_clean'] . "<br />";
	}
	else
	{

echo ' In mailbox. ' . "\n";
		$eilog .= "Opened mailbox for " . $foruminfo['title_clean'] . "\r\n";

		require_once(DIR . '/includes/class_bbcode_alt.php');
		$plaintext_parser =& new vB_BbCodeParser_PlainText($vbulletin, fetch_tag_list());
		require_once(DIR . '/includes/class_bbcode.php');
		$parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

		// Get headers
		$headers = imap_headers($mailbox);

		if ($headers != false)
		{
echo ' headers != false. ' . "\n";
			$checkmailbox=imap_check($mailbox);
			$amountmessages=$checkmailbox->Nmsgs;
			$letters = imap_fetch_overview($mailbox,"1:$amountmessages",0);
			$size=count($letters);
echo ' No of msgs. ' . $amountmessages;
			// ##### Examine Emails Individually #####
			for($i=0;$i<=$size-1;$i++)
			{

				$msgno = $letters[$i]->msgno;
				$letter = imap_headerinfo($mailbox, $msgno);

				// always start with reply_to
				$fromaddress = $letter->reply_to[0]->mailbox."@".$letter->reply_to[0]->host;

				// if strict security is enabled or reply_to was empty get sender info
				// otherwise reply to is our key
				if ($vbulletin->options['ei_strict'] == 1 OR !$fromaddress)
				{
					$fromaddress = $letter->sender[0]->mailbox ."@".$letter->sender[0]->host;
				}

				$subject1 = imap_mime_header_decode($letter->subject);
				$subject = '';
				$till = sizeof($subject1);

				// ##### Rebuilds Subject Line #####
				for ($w=0; $w<=$till-1; $w++)
				{
					$subject .= $subject1[$w]->text;
				}

				// checks for blocked subject lines
				$blocks = explode(',', $vbulletin->options['ei_block_list']);
				foreach($blocks as $block)
				{
					if (preg_match("/(.*)" . $block . "(.*)/i", $subject, $subjectnothin))
					{
					if (!$debug)
					{
						imap_delete($mailbox,$msgno);
					}
					$eilog .= "Blocked from " . $fromaddress . "\r\n";
					continue 2;
					}
				}

				// ###### Gets the ThreadID and other needed info from References,  #####

				if ($letter->in_reply_to)
				{
					$refs = explode(' ', $letter->in_reply_to);
					$parentref = $refs[count($refs) - 1];	// Last ref is immediate parent
					if (preg_match("/<([0-9]+)\.([0-9]+)@emailintegration>/", $parentref, $parentparts))
					{
						// this is a reply with parent info so let's get the parent info
						preg_match('/(.*)\[(.*)\](.*)/', $subject, $subjectparts);
						$title = $subjectparts[3];
						$threadid = $parentparts[1];
						$parentid = $parentparts[2];
						$type = "reply";
						$threadinfo = fetch_threadinfo($threadid);
					}
					else if (preg_match('/(.*)\[(.*)\-t\-([0-9]+)\](.*)/', $subject, $subjectparts))
					{
						$title = $subjectparts[4];
						$threadid = $subjectparts[3];
						$type="reply";
						$threadinfo = fetch_threadinfo($threadid);
					}
					else
					{
						// this is a new thread :)
						$title = $subject;
						$type = "thread";
					}
				}
				else
				{
					// no parent info found, this may be a post started before the change
					if (preg_match('/(.*)\[(.*)\-t\-([0-9]+)\](.*)/', $subject, $subjectparts))
					{
						$title = $subjectparts[4];
						$threadid = $subjectparts[3];
						$threadinfo = fetch_threadinfo($threadid);
						$type="reply";
					}
					else
					{
						// this is a new thread :)
						$title = $subject;
						$type = "thread";
					}
				}

				$mailmessage = imap_body($mailbox,$msgno);
				$mailheader = imap_fetchheader($mailbox, $msgno);

				$title = str_replace(array("&gt;", "&lt;", "&quot;", "&amp;"), array(">", "<", "\"", "&"), $title);

				$userinfo = $vbulletin->db->query_first("
					SELECT user.*, usertextfield.*
					FROM " . TABLE_PREFIX . "user AS user
					LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON (usertextfield.userid = user.userid)
					WHERE email = '".addslashes(htmlspecialchars($fromaddress))."'
				");

				$userid = $userinfo['userid'];

				$mime_class = new Mail_mimeDecode;
				$mime_class->_decode_bodies = true;
				$output = $mime_class->_decode($mailheader, $mailmessage);
				$d_message = array();
				parse_output($output, $d_message);
				$mailmessage = $d_message['text'][0];
				// Adds functionality for PHP < 5
				if (!function_exists("htmlspecialchars_decode"))
				{
					function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT)
					{
						return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
					}
				}

				if (strlen($mailmessage) == 0) $mailmessage = str_replace('&nbsp;',' ',htmlspecialchars_decode(strip_tags($d_message['html'][0])));

				// Cleaning Message Layouts
				// OLD FORMAT
				// THANKS TO CGMCKEEVER FOR THE QUOTING ROUTINE!
				if ($vbulletin->options['ei_email_layout'])
				{

					$pattern[1] = "/(http:\/\/[^\s]*)/i";
					$replace[1] = '[URL=\'$1\']$1[/URL]';
					// any instance of the mailing list
					$pattern[2] = "/" . $foruminfo['ei_replyaddress'] . "/i";
					$replace[2] = '';
					$pattern[3] = "/=\r\n/";
					$replace[3] = '';
					$pattern[4] = "/=\n/";
					$replace[4] = '';
					// EI footer
					$pattern[5] = "/-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-(.*)-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-/is";
					$replace[5] = "";
					$pattern[6] = "/-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-(.*)-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-/s";
					$replace[6] = "";
					$mailmessage = preg_replace($pattern, $replace, $mailmessage);


					// start quoting routine
					// lets try to quote this based on lines starting with a >
					// cgmckeever
					$mail_line = explode("\n",$mailmessage);
					$vBquote = '';
					$mailmessage = "";
					foreach($mail_line AS $line)
					{
						if ($line[0] == '>' AND strlen($vBquote) == 0)
						{
							// start quote
							$vBquote = $line . "\n";
						}
						elseif (strlen($vBquote) != 0 AND $line[0] != '>')
						{
							// looks like quoting is done
							if (strlen(str_replace(array("\n","\r",'>',' '),array(),$vBquote)) != 0)
							{
								// if this string is comepletely empty less those characters, add it
								$mailmessage .= "\n[quote]" . $vBquote . "[/quote]\n";
							}
							$mailmessage .= $line . "\n";
							$vBquote = '';
						}
						elseif (strlen($vBquote) != 0)
						{
							// building quote
							$vBquote .= $line . "\n";
						}
						else $mailmessage .= $line . "\n";
					}
					if (strlen(str_replace(array("\n","\r",'>',' '),array(),$vBquote)) != 0) $mailmessage .= "\n[quote]" . $vBquote . "[/quote]\n";
					// end quoting routine
				}
				else
				// NEW FORMAT
				{
					// changes urls from html to bbcode
					$pattern[1] = "/(http:\/\/[^\s]*)/i";
					$replace[1] = '[URL=\'$1\']$1[/URL]';
					// any instance of the mailing list
					$pattern[2] = "/" . $foruminfo['ei_replyaddress'] . "/i";
					$replace[2] = "";
					$pattern[3] = "/=\r\n/";
					$replace[3] = '';
					$pattern[4] = "/=\n/";
					$replace[4] = '';
					// cleans up the > from replied lines
					$pattern[5] = "/\n>/";
					$replace[5] = "\n";
					$mailmessage = preg_replace($pattern, $replace, $mailmessage);

					if (preg_match("/-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-(.*)-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-/s", $mailmessage, $mailmessageparts))
					{
						$mailmessage = $mailmessageparts[1];
					}
					else if (preg_match("/-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-(.*)-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-=3D-/s", $mailmessage, $mailmessageparts))
					{
						$mailmessage = $mailmessageparts[1];
					}

				}

				// ############################
				// ##### Few Error Checks #####
				// ############################

				// If Email Address belongs to a user
				if (!$userid)
				{
					$userinfo['languageid'] = 0;
					eval(fetch_email_phrases('ei_error_nouser', $userinfo['languageid']));
					if (!$debug)
					{
						imap_delete($mailbox,$msgno);
						vbmail($fromaddress, $subject, $message);
					}
					if ($debug)
					{
						vbmail($debugemail, $subject, $message);
					}
					$eilog .= "Invalid email " . $fromaddress . "\r\n";
					continue;
				}

								$messagecheck = str_replace(array("\n", "\r", "\t", " "), '', $mailmessage);
				// message length

				if (strlen($messagecheck) < 	$vbulletin->options['postminchars'])
				{
					eval(fetch_email_phrases('ei_error_tooshort', $userinfo['languageid']));
					if (!$debug)
					{
						imap_delete($mailbox,$msgno);
						vbmail($fromaddress, $subject, $message);
					}
					if ($debug)
					{
						vbmail($debugemail, $subject, $message);
					}
					$eilog .= "Not enough characters " . $fromaddress . "\r\n";
					continue;
				}

				// if it's a thread, that it has a title
				if ($title == '' AND $type == 'thread')
				{
					eval(fetch_email_phrases('ei_error_notitle', $userinfo['languageid']));
					if (!$debug)
					{
						imap_delete($mailbox,$msgno);
						vbmail($fromaddress, $subject, $message);
					}
					if ($debug)
					{
						vbmail($debugemail, $subject, $message);
					}
					$eilog .= "No title " . $fromaddress . "\r\n";
					continue;
				}

				// forum password?
				if ($foruminfo['password'])
				{
					eval(fetch_email_phrases('ei_error_password', $userinfo['languageid']));
					if (!$debug)
					{
						imap_delete($mailbox,$msgno);
						vbmail($fromaddress, $subject, $message);
					}
					if ($debug)
					{
						vbmail($debugemail, $subject, $message);
					}
					$eilog .= "Forum with password: " . $userinfo['username'] . "\r\n";
					continue;
				}

				// ensure forum isn't closed
				else if (!$foruminfo['allowposting'])
				{
					eval(fetch_email_phrases('ei_error_closed', $userinfo['languageid']));
					if (!$debug)
					{
						imap_delete($mailbox,$msgno);
						vbmail($fromaddress, $subject, $message);
					}
					if ($debug)
					{
						vbmail($debugemail, $subject, $message);
					}
					$eilog .= "Post to closed forum: " . $userinfo['username'] . "\r\n";
					continue;
				}

				// Check if they have permission!
				$usergroupids = explode(",", $foruminfo['ei_usergroups']);
				if (!is_member_of($userinfo, $usergroupids))
				{
					eval(fetch_email_phrases('ei_error_nopermission', $userinfo['languageid']));
					if (!$debug)
					{
						imap_delete($mailbox,$msgno);
						vbmail($fromaddress, $subject, $message);
					}
					if ($debug)
					{
						vbmail($debugemail, $subject, $message);
					}
					$eilog .= "No access to post " . $userinfo['username'] . "\r\n";
					continue;
				}

				// [rbd] Get thread info and do some more checks. Delay attachment
				// [rbd] processing till every check possible has passed.
				if ($type != "thread")
				{
					if (!$threadid)
					{
						continue;
					}

					// check if thread is visible or is not deleted
					if (!$threadinfo['visible'] OR $threadinfo['isdeleted'])
					{
						eval(fetch_email_phrases('ei_error_invalidid', $userinfo['languageid']));
						if (!$debug)
						{
							imap_delete($mailbox,$msgno);
							vbmail($fromaddress, $subject, $message);
						}
						if ($debug)
						{
							vbmail($debugemail, $subject, $message);
						}
						$eilog .= "Thread not visible or deleted: " . $userinfo['username'] . "\r\n";
						continue;
					}

					// check if thread is open
					elseif (!$threadinfo['open'])
					{
						// check if user is a moderator and can post to closed threads
						if (!can_moderate($threadinfo['forumid'], 'canopenclose', $userid))
						{
							eval(fetch_email_phrases('ei_error_threadclosed', $userinfo['languageid']));
							if (!$debug)
							{
								imap_delete($mailbox,$msgno);
								vbmail($fromaddress, $subject, $message);
							}
							if ($debug)
							{
								vbmail($debugemail, $subject, $message);
							}
							$eilog .= "Thread closed: " . $userinfo['username'] . "\r\n";
							continue;
						}
					}
				}
				else
				{
					$threadinfo = array();
				}

				// Check for attachment errors and build attachment array for later
				// and do more error checks
				if ($d_message['attachments'])
				{
					if ($d_message['attachments'] AND $vbulletin->options['ei_allow_attachments'] == 0 AND $vbulletin->options['ei_dismiss_attachments'] == 0)
					{
						eval(fetch_email_phrases('ei_error_noattachments', $userinfo['languageid']));
						if (!$debug)
						{
							imap_delete($mailbox,$msgno);
							vbmail($fromaddress, $subject, $message);
						}
						if ($debug)
						{
							vbmail($debugemail, $subject, $message);
						}
						$eilog .= "Attachments disabled: " . $userinfo['username'] . "\r\n";
						continue;
					}
					else if ($d_message['attachments'] AND $vbulletin->options['ei_allow_attachments'] == 0 AND $vbulletin->options['ei_dismiss_attachments'] == 1)
					{
						$eilog .= "Attachments dismissed: " . $userinfo['username'] . "\r\n";
						$mailmessage .= "\r\n\r\n[Attachments are disabled from email. Non-text portions have been removed.]";
						continue;
					}
					else
					{
						// check maximum space for all attachments
						if ($vbulletin->options['attachtotalspace'])
						{
							$attachsize = $vbulletin->db->query_first("SELECT SUM(filesize) AS sum FROM " . TABLE_PREFIX . "attachment");
						}

						$attachtypeslist = $vbulletin->db->query_read("
							SELECT extension, size
							FROM " . TABLE_PREFIX . "attachmenttype
						");

						// build array of extensions and sizes
						while ($attachtypelist = $vbulletin->db->fetch_array($attachtypeslist))
						{
								$attachmenttype["$typenumber"] = array(
									'extension' => $attachtypelist['extension'],
									'size' => $attachtypelist['size']
								);
								$typenumber++;
						}


						$attachmentnumber = 0;
						$attachmentinfo = array();

						foreach ($d_message['attachments'] as $attach)
						{
							$filedata = $attach['data'];
							$filesize = strlen($filedata);
							if (preg_match("/\=\?utf\-8\?q\?(.*)\?\=/i", $attach['filename'], $filenameparts))
							{
								$pattern[1] = "/=20/";
								$replace[1] = " ";
								$attach['filename'] = preg_replace($pattern, $replace, $filenameparts[1]);
							}
							$filename = $attach['filename'];
							$emailsize = 0;
							$maxsize = 0;
							// get extension
							if (preg_match("/(.*)\.(.*)/", $attach['filename'],$attachext))
							{
								$preextension = $attachext[2];
							}
							// compare extensions to get max size
							foreach ($attachmenttype as $attachtype)
							{
								if ($attachtype['extension'] == $preextension)
								{
									$fileextension = $preextension;
									$maxsize = $attachtype['size'];
									break;
								}
								else
								{
									$fileextension = 'email';
								}

								if ($attachtype['extension'] == 'email')
								{
									$emailsize = $attachtype['size'];
								}
							}
							// if do not allow all types dump non-matches
							if ($vbulletin->options['ei_restrict_attachments'] == '0' AND $fileextension == 'email')
							{
								eval(fetch_email_phrases('ei_error_wrongextension', $userinfo['languageid']));
								if (!$debug)
								{
									imap_delete($mailbox,$msgno);
									vbmail($fromaddress, $subject, $message);
								}
								if ($debug)
								{
									vbmail($debugemail, $subject, $message);
								}
								$eilog .= "Attachment invalid extension " . $preextension . ": " . $userinfo['username'] . "\r\n";
								continue 2;
							}
							if ($fileextension == 'email' AND $emailsize < $filesize)
							{

								eval(fetch_email_phrases('ei_error_toolarge', $userinfo['languageid']));
								if (!$debug)
								{
									imap_delete($mailbox,$msgno);
									vbmail($fromaddress, $subject, $message);
								}
								if ($debug)
								{
									vbmail($debugemail, $subject, $message);
								}
								$eilog .= "Attachment too large: " . $userinfo['username'] . "\r\n";
								continue 2;
							}
							if ($fileextension != 'email' AND $maxsize < $filesize)
							{
								eval(fetch_email_phrases('ei_error_toolarge', $userinfo['languageid']));
								if (!$debug)
								{
									imap_delete($mailbox,$msgno);
									vbmail($fromaddress, $subject, $message);
								}
								if ($debug)
								{
									vbmail($debugemail, $subject, $message);
								}
								$eilog .= "Attachment too large: " . $userinfo['username'] . "\r\n";
								continue 2;
							}

							if ($vbulletin->options['attachtotalspace'])
							{
								if (($attachsize['sum'] + $filesize) > $vbulletin->options['attachtotalspace'])
								{
									$overage = vb_number_format($attachdata['sum'] + $filesize - $vbulletin->options['attachtotalspace'], 1, true);
									$admincpdir = $vbulletin->config['Misc']['admincpdir'];
									eval(fetch_email_phrases('attachfull', 0));
									vbmail($vbulletin->options['webmasteremail'], $subject, $message);
									eval(fetch_email_phrases('ei_error_dbfull', $userinfo['languageid']));
									if (!$debug)
									{
										imap_delete($mailbox,$msgno);
										vbmail($fromaddress, $subject, $message);
									}
									if ($debug)
									{
										vbmail($debugemail, $subject, $message);
									}
									$eilog .= "Error sent to admin for database or attachments full\r\n";
									continue 2;
								}
							}
							if (($vbulletin->options['attachlimit'] != 0) AND ($vbulletin->options['attachlimit'] < $attachmentnumber))
							{
								eval(fetch_email_phrases('ei_error_attachlimit', $userinfo['languageid']));
								if (!$debug)
								{
									imap_delete($mailbox,$msgno);
									vbmail($fromaddress, $subject, $message);
								}
								if ($debug)
								{
									vbmail($debugemail, $subject, $message);
								}
								$eilog .= "Too many attachments: " . $userinfo['username'] . "\r\n";
								continue 2;
							}
							$attachmentinfo["$attachmentnumber"] = array(
								'filename' => $attach['filename'],
								'filedata' => $filedata,
								'filesize' => $filesize,
								'fileextension' => $fileextension
							);
							$attachmentnumber++;
						}
					}
				}
				// ########### END ERROR CHECKS! #################

echo ' Post a reply. ';
				// ##### Time To Post The Reply #####
				if ($type == "reply")
				{

					// Error check to be sure we have a parentid
					if (!$parentid)
					{
						$getparent = $vbulletin->db->query_first("
							SELECT postid
							3 . "post AS post
							WHERE threadid = " . $threadid . "
							ORDER BY postid DESC
							LIMIT 1
						");
						$parentid = $getparent['postid'];
					}

					// Building post info
					$threaddata =& datamanager_init('Post', $vbulletin, ERRTYPE_ARRAY, 'threadpost');
					$threaddata->set_info('forum', $foruminfo);
					$threaddata->set_info('user', $userinfo);
					$threaddata->set_info('skip_floodcheck', true);
					$threaddata->set_info('skip_charcount', true);
					$threaddata->set_info('skip_maximagescheck', true);
					$threaddata->set('threadid', $threadid);
					$threaddata->set('parentid', $parentid);
					$threaddata->set('userid', $userid);
					$threaddata->set('title', $title);
					$threaddata->set('pagetext', $mailmessage);
					$threaddata->set('visible', 1);
					$threaddata->set('allowsmilie', 1);
					$threaddata->set('showsignature', 1);
					if ($vbulletin->options['ei_iconid'])
					{
						$threaddata->set('iconid', $vbulletin->options['ei_iconid']);
					}
					$threaddata->pre_save();
					$pid = $threaddata->save();
					$post['postid'] = $pid;
					$postid = $post['postid'];

					$eilog .= "Reply added from " . $userinfo['username'] . " to threadid " . $threadid . "\r\n";

					// Check for attachments
					if ($attachmentinfo)
					{
						$attachmentmessage = "";
						foreach ($attachmentinfo as $attach)
						{
							$filename = $attach['filename'];
							$filedata = $attach['filedata'];
							$filehash = md5($attach['filedata']);
							$filesize = $attach['filesize'];
							$dateline = TIMENOW;
							$fileextension = $attach['fileextension'];

							// save attachments in database
							// insert into database
							if ($vbulletin->options['attachfile'])
							{
								$tempdata = $filedata;
								$filedata = '';
							}
							$vbulletin->db->query_write("
								INSERT INTO " . TABLE_PREFIX . "attachment
									(
										userid,
										dateline,
										thumbnail_dateline,
										filename,
										filedata,
										visible,
										counter,
										filesize,
										postid,
										filehash,
										extension
									)
								VALUES
									(
										$userid,
										'" . $vbulletin->db->escape_string($dateline) . "',
										'" . $vbulletin->db->escape_string($thumbnail['dateline']) . "',
										'" . $vbulletin->db->escape_string($filename) . "',
										'" . $vbulletin->db->escape_string($filedata) . "',
										1,
										0,
										'" . $vbulletin->db->escape_string($filesize) . "',
										$postid,
										'" . $vbulletin->db->escape_string($filehash) . "',
										'" . $vbulletin->db->escape_string($fileextension) . "'
									)
							");
							$vbulletin->db->query_write("
								UPDATE " . TABLE_PREFIX . "post
								SET
								attach = 1
								WHERE postid = $postid
							");
							$vbulletin->db->query_write("
								UPDATE " . TABLE_PREFIX . "thread
								SET
								attach = 1
								WHERE threadid = $threadid
							");

							$eilog .= "Attachments added to threadid " . $threadid . " from " . $userinfo['username'] . "\r\n";

							// get id for email and for filesystem
							$getid = $vbulletin->db->query_first("
								SELECT attachmentid,dateline,filename
								FROM " . TABLE_PREFIX . "attachment
								WHERE userid = $userid AND dateline = $dateline AND filehash = '" . $vbulletin->db->escape_string($filehash) . "'
							");
							$attachmentid = $getid['attachmentid'];

							// create attachment message for upcoming emails
							$attachmentmessage .= "
Attached to this message is [url=" . $vbulletin->options['bburl'] . "/attachment.php?attachmentid=" . $getid['attachmentid'] . "&d=" . $getid['dateline'] . "]" . $getid['filename'] . "[/url]";


							if ($vbulletin->options['attachfile'])
							{
								if ($vbulletin->options['attachfile'] == 2)
								{
									$path = $vbulletin->options['attachpath'] . '/' . implode('/', preg_split('//', $userid,  -1, PREG_SPLIT_NO_EMPTY));
								}
								else
								{
									$path = $vbulletin->options['attachpath'] . '/' . $userid;
								}
								vbmkdir($path);
								{
									$path .= '/' . $attachmentid . '.attach';
								}
								$fp = fopen($path, 'wb');
								fwrite($fp,$tempdata);
								fclose($fp);
							}
						}
					}

					$mainpost = $vbulletin->db->query_first("
						SELECT parentid, pagetext,attach, username, threadid, title, userid
						FROM " . TABLE_PREFIX . "post
						WHERE postid = $postid
					");

					if ($mainpost['parentid'])
					{
						$parentpost = $vbulletin->db->query_first("
							SELECT pagetext, username
							FROM " . TABLE_PREFIX . "post AS post
							WHERE postid = " . $mainpost['parentid'] . "
						");
					}
					else
					{
						$parentpost = $vbulletin->db->query_first("
							SELECT pagetext, username
							FROM " . TABLE_PREFIX . "post AS post
							WHERE threadid = " . $mainpost['threadid'] . " AND
							postid != " . $postid . "
							ORDER BY postid DESC
							LIMIT 1
						");
					}

					$dupsubs = $vbulletin->db->query_read_slave("
						SELECT user.userid, subscribethread.*
						FROM " . TABLE_PREFIX . "subscribethread AS subscribethread
						INNER JOIN " . TABLE_PREFIX . "user AS user ON (subscribethread.userid = user.userid)
						LEFT JOIN " . TABLE_PREFIX . "subscribeforum AS subscribeforum ON (subscribeforum.userid = user.userid)
						WHERE subscribeforum.forumid = " . $foruminfo['forumid'] . " AND subscribeforum.emailupdate = 1 AND subscribethread.threadid = " . $mainpost['threadid'] . " AND
						subscribethread.emailupdate = 1
					");
					while ($dupsub = $vbulletin->db->fetch_array($dupsubs))
					{
						$vbulletin->db->query_write("
							DELETE FROM " . TABLE_PREFIX . "subscribethread
							WHERE subscribethreadid = " . $dupsub['subscribethreadid'] . "
						");
					}

					$useremails = $vbulletin->db->query_read("
						SELECT user.*, subscribeforum.*
						FROM " . TABLE_PREFIX . "subscribeforum AS subscribeforum
						INNER JOIN " . TABLE_PREFIX . "user AS user ON (subscribeforum.userid = user.userid)
						LEFT JOIN " . TABLE_PREFIX . "usergroup AS usergroup ON (usergroup.usergroupid = user.usergroupid)
						LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON (usertextfield.userid = user.userid)
						WHERE subscribeforum.forumid = " . $foruminfo['forumid'] . " AND
						subscribeforum.emailupdate = 1 AND
						" . ($userid ? "CONCAT(' ', IF(usertextfield.ignorelist IS NULL, '', usertextfield.ignorelist), ' ') NOT LIKE ' " . intval($userid) . " ' AND" : '') . "
						user.usergroupid <> 3 AND
						(usergroup.genericoptions & " . $vbulletin->bf_ugp_genericoptions['isnotbannedgroup'] . ")
					");


					$newpost['postid'] = $postid;
					$pagetext = $mainpost['pagetext'];
					$threadinfo['replycount'] = $threadinfo['replycount']+1;
					if ($mainpost['title'])
					{
						$title = $mainpost['title'];
					}
					else
					{
						$title = $threadinfo['title'];
					}
					$evalemailplain = array();
					$evalemailhtml = array();
					while ($touser = $vbulletin->db->fetch_array($useremails))
					{
						if (!($vbulletin->usergroupcache["$touser[usergroupid]"]['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
						{
							continue;
						}

						// Checks this user's usergroup against the list of allowable usergroupids that can use this feature
						$forumusergroupids = explode(",", $foruminfo['ei_usergroups']);
						if (!is_member_of($touser, $forumusergroupids))
						{
							continue;
						}

						// Checks if the user to be emailed is the same as the poster and if they do not want to get their own posts via email
						if (($touser['userid'] == $mainpost['userid']) AND !$touser['ei_own_posts'])
						{
							continue;
						}

						$touser['username'] = unhtmlspecialchars($touser['username']);
						$touser['languageid'] = iif($touser['languageid'] == 0, $vbulletin->options['languageid'], $touser['languageid']);
						$touser['auth'] = md5($touser['userid'] . $touser['subscribethreadid'] . $touser['salt'] . COOKIE_SALT);

						if (empty($evalemailplain))
						{
							if ($vbulletin->options['ei_email_layout'])
							{
								$email_texts = $vbulletin->db->query_read("
									SELECT text, languageid, fieldname
									FROM " . TABLE_PREFIX . "phrase
									WHERE fieldname IN ('emailsubject', 'emailbody') AND varname = 'ei_notify_post_plain_old'
								");
							}
							else
							{
								$email_texts = $vbulletin->db->query_read("
									SELECT text, languageid, fieldname
									FROM " . TABLE_PREFIX . "phrase
									WHERE fieldname IN ('emailsubject', 'emailbody') AND varname = 'ei_notify_post_plain'
								");
							}

							while ($email_text = $vbulletin->db->fetch_array($email_texts))
							{
								$emails["$email_text[languageid]"]["$email_text[fieldname]"] = $email_text['text'];
							}
							require_once(DIR . '/includes/functions_misc.php');
							foreach ($emails AS $languageid => $email_text)
							{
								// lets cycle through our array of notify phrases
								$text_message = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailbody']), $emails['-1']['emailbody'], $email_text['emailbody'])));
								$text_message = replace_template_variables($text_message);
								$text_subject = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailsubject']), $emails['-1']['emailsubject'], $email_text['emailsubject'])));
								$text_subject = replace_template_variables($text_subject);

								$evalemailplain["$languageid"] = '
									$message = "' . $text_message . '";
									$subject = "' . $text_subject . '";
								';
							}
						}

						if (empty($evalemailhtml))
						{

							$emaillinks = "";

							if ($vbulletin->options['ei_email_footer'])
							{
								if ($vbulletin->options['ei_email_homepage'])
								{
									$emaillinks .= construct_phrase($vbphrase['ei_links_homepage'], $vbulletin->options['homeurl']);
								}
								if ($vbulletin->options['ei_email_forums'])
								{
									if ($emaillinks)
									{
										$emaillinks .= " | ";
									}
									$emaillinks .= construct_phrase($vbphrase['ei_links_forums'], $vbulletin->options['bburl']);
								}
								if ($vbulletin->options['ei_email_calendar'])
								{
									if ($emaillinks)
									{
										$emaillinks .= " | ";
									}
									$emaillinks .= construct_phrase($vbphrase['ei_links_calendar'], $vbulletin->options['bburl']);
								}
								if ($vbulletin->options['ei_email_memberlist'])
								{
									if ($emaillinks)
									{
										$emaillinks .= " | ";
									}
									$emaillinks .= construct_phrase($vbphrase['ei_links_memberlist'], $vbulletin->options['bburl']);
								}
								if ($vbulletin->options['ei_email_search'])
								{
									if ($emaillinks)
									{
										$emaillinks .= " | ";
									}
									$emaillinks .= construct_phrase($vbphrase['ei_links_search'], $vbulletin->options['bburl']);
								}

								if ($emaillinks)
								{
									$emaillinks = $vbphrase['ei_links_start'] . $emaillinks . $vbphrase['ei_links_end'];
								}
							}

							if ($vbulletin->options['ei_email_layout'])
							{
								$email_texts = $vbulletin->db->query_read("
									SELECT text, languageid, fieldname
									FROM " . TABLE_PREFIX . "phrase
									WHERE fieldname IN ('emailsubject', 'emailbody') AND varname = 'ei_notify_post_html_old'
								");
							}
							else
							{
								$email_texts = $vbulletin->db->query_read("
									SELECT text, languageid, fieldname
									FROM " . TABLE_PREFIX . "phrase
									WHERE fieldname IN ('emailsubject', 'emailbody') AND varname = 'ei_notify_post_html'
								");
							}

							while ($email_text = $vbulletin->db->fetch_array($email_texts))
							{
								$emails["$email_text[languageid]"]["$email_text[fieldname]"] = $email_text['text'];
							}
							// for replace_template_variables()
							require_once(DIR . '/includes/functions_misc.php');
							foreach ($emails AS $languageid => $email_text)
							{
								// lets cycle through our array of notify phrases
								$text_message = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailbody']), $emails['-1']['emailbody'], $email_text['emailbody'])));
								$text_message = replace_template_variables($text_message);
								$text_subject = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailsubject']), $emails['-1']['emailsubject'], $email_text['emailsubject'])));
								$text_subject = replace_template_variables($text_subject);

								$evalemailhtml["$languageid"] = '
									$message = "' . $text_message . '";
									$subject = "' . $text_subject . '";
								';
							}
						}

						// Build new References: header
						if ($refs)
						{
							while(count($refs) > 9)
							{
								array_shift($refs);					// Limit refs to 10 total in reply (RFC std)
							}
							$replyrefs = '';
							foreach($refs as $ref)
							{
								$replyrefs .= $ref . ' ';			// Will end in ' '
							}
							$replyrefs = trim($replyrefs);
						}

						if ((!$vbulletin->options['ei_format_allow_user'] AND $vbulletin->options['ei_format_forced']) OR ($vbulletin->options['ei_format_allow_user'] AND $touser['ei_format']))
						{
							if ($vbulletin->options['ei_email_layout'])
							{
								eval(iif(empty($evalemailhtml["$touser[languageid]"]), $evalemailhtml["-1"], $evalemailhtml["$touser[languageid]"]));
								if(preg_match('#(.*<div id=[\'\"]ei_body[\'\"]>)(.*)(</div>\s*<div id=[\'\"]ei_sig[\'\"]>)(.*)(</div>\s*<div id=[\'\"]ei_links[\'\"]>.*)#is', $message, $messageparts))
								{
									$message = $messageparts[1] .
									$parser->do_parse($messageparts[2], 1, 1, 1, 1, 1, 0) .
									$messageparts[3] .
									$parser->do_parse($messageparts[4], 1, 1, 1, 1, 1, 0) .
									$messageparts[5];
								}
								else
								{
									$message = $parser->do_parse($message, 1, 1, 1, 1, 1, 0);
								}
							}
							else
							{
								eval(iif(empty($evalemailhtml["$touser[languageid]"]), $evalemailhtml["-1"], $evalemailhtml["$touser[languageid]"]));
								if(preg_match('#(.*<div id=[\'\"]ei_reply[\'\"]>)(.*)(</div>\s*<div id=[\'\"]ei_body[\'\"]>)(.*)(</div>\s*<div id=[\'\"]ei_links[\'\"]>.*)#is', $message, $messageparts))
								{
									$message = $messageparts[1] .
									$parser->do_parse($messageparts[2], 1, 1, 1, 1, 1, 0) .
									$messageparts[3] .
									$parser->do_parse($messageparts[4], 1, 1, 1, 1, 1, 0) .
									$messageparts[5];
								}
								else
								{
									$message = $parser->do_parse($message, 1, 1, 1, 1, 1, 0);
								}
							}
						}
						else
						{
							eval(iif(empty($evalemailplain["$touser[languageid]"]), $evalemailplain["-1"], $evalemailplain["$touser[languageid]"]));
							$plaintext_parser->set_parsing_language($touser['languageid']);
							$message = $plaintext_parser->parse($message, $foruminfo['forumid']);
						}

						$emailreturnaddress = $foruminfo['ei_replyaddress'];
						$eiusername = $userinfo['username'];
						$eidelimiter = stripcslashes($vbulletin->options['ei_header_newline']);
						$eiheaders = '';
						$eiheaders .= 'Date: ' . date('r') . $eidelimiter;
						$eifromheader = 'From: ';
						if($vbulletin->options['ei_use_single_from_address'])
						{
							$eifromheader .= $vbulletin->options['ei_single_from_name'] . '<' . $vbulletin->options['ei_single_from_address'] .'>';
						}
						else
						{
							$eifromheader .= "$eiusername <$emailreturnaddress>";
						}
						$eiheaders .= $eifromheader . $eidelimiter;
						$eiheaders .= "Reply-To: $emailreturnaddress" . $eidelimiter;
						$eiheaders .= 'MIME-Version: 1.0' . $eidelimiter;

						if ((!$vbulletin->options['ei_format_allow_user'] AND $vbulletin->options['ei_format_forced']) OR ($vbulletin->options['ei_format_allow_user'] AND $touser['ei_format']))
						{
							$eiheaders .= 'Content-Type: text/html' . iif($encoding, "; charset=\"$encoding\"") . $eidelimiter;
						}
						else
						{
							$eiheaders .= 'Content-Type: text/plain' . iif($encoding, "; charset=\"$encoding\"") . $eidelimiter;
						}

						$eiheaders .= 'Content-Transfer-Encoding: 8bit' . $eidelimiter;
						$eiheaders .= "Message-Id: <$threadid.$postid@emailintegration>" . $eidelimiter;
						$eiheaders .= "References: $replyrefs" . $eidelimiter;
						$eiheaders .= 'X-Priority: 3' . $eidelimiter;
						$eiheaders .= 'X-Mailer: vBulletin Mail via PHP' . $eidelimiter;
						vbmail($touser['email'], $subject, $message, false, '', $eiheaders);
					}
					unset($useremails, $touser);

					// THREAD SUBSCRIPTIONS EMAILS!
					// get last reply time
					if ($postid)
					{
						$dateline = $vbulletin->db->query_first("
							SELECT dateline, pagetext
							FROM " . TABLE_PREFIX . "post
							WHERE postid = $postid
						");
						$pagetext_orig = $dateline['pagetext'];
							$lastposttime = $vbulletin->db->query_first("
							SELECT MAX(dateline) AS dateline
							FROM " . TABLE_PREFIX . "post AS post
							WHERE threadid = $threadid
								AND dateline < $dateline[dateline]
								AND visible = 1
						");
					}
					else
					{
						$lastposttime = $vbulletin->db->query_first("
							SELECT MAX(postid) AS postid, MAX(dateline) AS dateline
							FROM " . TABLE_PREFIX . "post AS post
							WHERE threadid = $threadid
								AND visible = 1
						");
							$pagetext = $vbulletin->db->query_first("
							SELECT pagetext
							FROM " . TABLE_PREFIX . "post
							WHERE postid = $lastposttime[postid]
						");
						$pagetext_orig = $pagetext['pagetext'];
						unset($pagetext);
					}

					$threadinfo['title'] = unhtmlspecialchars($threadinfo['title']);
					$foruminfo['title_clean'] = unhtmlspecialchars($foruminfo['title_clean']);
					$temp = $userinfo['username'];

					if ($postid)
					{
						$postinfo = fetch_postinfo($postid);
						$userinfo['username'] = unhtmlspecialchars($postinfo['username']);
					}
					else
					{
						$userinfo['username'] = unhtmlspecialchars(
							(!$userinfo['userid'] ? $postusername : $userinfo['username'])
						);
					}

					require_once(DIR . '/includes/class_bbcode_alt.php');
					$plaintext_parser =& new vB_BbCodeParser_PlainText($vbulletin, fetch_tag_list());
					$pagetext_cache = array(); // used to cache the results per languageid for speed
					$mod_emails = fetch_moderator_newpost_emails('newpostemail', $foruminfo['parentlist'], $language_info);

					$useremails = $vbulletin->db->query_read_slave("
						SELECT user.*, subscribethread.emailupdate, subscribethread.subscribethreadid
						FROM " . TABLE_PREFIX . "subscribethread AS subscribethread
						INNER JOIN " . TABLE_PREFIX . "user AS user ON (subscribethread.userid = user.userid)
						LEFT JOIN " . TABLE_PREFIX . "usergroup AS usergroup ON (usergroup.usergroupid = user.usergroupid)
						LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON (usertextfield.userid = user.userid)
						WHERE subscribethread.threadid = $threadid AND
							subscribethread.emailupdate IN (1, 4) AND
							subscribethread.canview = 1 AND
							" . ($userid ? "CONCAT(' ', IF(usertextfield.ignorelist IS NULL, '', usertextfield.ignorelist), ' ') NOT LIKE ' " . intval($userid) . " ' AND" : '') . "
							user.usergroupid <> 3 AND
							user.userid <> " . intval($userid) . " AND
							user.lastactivity >= " . intval($lastposttime['dateline']) . " AND
							(usergroup.genericoptions & " . $vbulletin->bf_ugp_genericoptions['isnotbannedgroup'] . ")
					");

					$evalemail = array();
					while ($touser = $vbulletin->db->fetch_array($useremails))
					{
						if (!($vbulletin->usergroupcache["$touser[usergroupid]"]['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
						{
							continue;
						}
						else if (in_array($touser['email'], $mod_emails))
						{
							// this user already received an email about this post via
							// a new post email for mods -- don't send another
							continue;
						}
						$touser['username'] = unhtmlspecialchars($touser['username']);
						$touser['languageid'] = iif($touser['languageid'] == 0, $vbulletin->options['languageid'], $touser['languageid']);
						$touser['auth'] = md5($touser['userid'] . $touser['subscribethreadid'] . $touser['salt'] . COOKIE_SALT);

						if (empty($evalemail))
						{
							$email_texts = $vbulletin->db->query_read_slave("
								SELECT text, languageid, fieldname
								FROM " . TABLE_PREFIX . "phrase
								WHERE fieldname IN ('emailsubject', 'emailbody') AND varname = 'ei_thread_notify'
							");

							while ($email_text = $vbulletin->db->fetch_array($email_texts))
							{
								$emails["$email_text[languageid]"]["$email_text[fieldname]"] = $email_text['text'];
							}

							require_once(DIR . '/includes/functions_misc.php');

							foreach ($emails AS $languageid => $email_text)
							{
								// lets cycle through our array of notify phrases
								$text_message = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailbody']), $emails['-1']['emailbody'], $email_text['emailbody'])));
								$text_message = replace_template_variables($text_message);
								$text_subject = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailsubject']), $emails['-1']['emailsubject'], $email_text['emailsubject'])));
								$text_subject = replace_template_variables($text_subject);

								$evalemail["$languageid"] = '
									$message = "' . $text_message . '";
									$subject = "' . $text_subject . '";
								';
							}
						}

						// parse the page text into plain text, taking selected language into account
						if (!isset($pagetext_cache["$touser[languageid]"]))
						{
							$plaintext_parser->set_parsing_language($touser['languageid']);
							$pagetext_cache["$touser[languageid]"] = $plaintext_parser->parse($pagetext_orig, $foruminfo['forumid']);
						}
						$pagetext = $pagetext_cache["$touser[languageid]"];
						eval(iif(empty($evalemail["$touser[languageid]"]), $evalemail["-1"], $evalemail["$touser[languageid]"]));
						if ($touser['emailupdate'] == 4 AND !empty($touser['icq']))
						{ // instant notification by ICQ
							$touser['email'] = $touser['icq'] . '@pager.icq.com';
						}
						vbmail($touser['email'], $subject, $message);
					}
					unset($plaintext_parser, $pagetext_cache);


					// Build thread info
					build_thread_counters($threadid);
					build_forum_counters($foruminfo['forumid']);
					if (!$debug)
					{
						imap_delete($mailbox,$msgno);
					}
				}

echo ' Post a thread. ';
				// ##### Time To Post The Thread
				if ($type == "thread")
				{
					$forumid = $foruminfo['forumid'];
					$threaddm =& datamanager_init('Thread_FirstPost', $vbulletin, ERRTYPE_ARRAY, 'threadpost');
					$threaddm->set('forumid', $forumid);
					$threaddm->set('userid', $userid);
					$threaddm->set_info('user', $userinfo);
					$threaddm->set_info('skip_floodcheck', true);
					$threaddm->set_info('skip_charcount', true);
					$threaddm->set_info('skip_maximagescheck', true);
					$threaddm->set('pagetext', $mailmessage);
					$threaddm->set('title', $title);
					if ($vbulletin->options['ei_iconid'])
					{
						$threaddm->set('iconid', $vbulletin->options['ei_iconid']);
					}
					$threaddm->set('allowsmilie', 1);
					$threaddm->set('visible', 1);
					$threaddm->set_info('forum', $foruminfo);
					$threaddm->set('showsignature', 1);
					$threaddm->pre_save();
					$tid = $threaddm->save();
					$newpost['message'] = $mailmessage;
					$threadinfo =& $threaddm->thread;
					$newpost['postid'] = $threaddm->fetch_field('firstpostid');
					$threadid = $threadinfo['threadid'];
					$postid = $newpost['postid'];

					$eilog .= "Newthread added from " . $userinfo['username'] . " to forum " . $foruminfo['title_clean'] . "\r\n";

					// Check for attachments
					if ($attachmentinfo)
					{
						$attachmentmessage = "";
						foreach ($attachmentinfo as $attach)
						{
							$filename = $attach['filename'];
							$filedata = $attach['filedata'];
							$filehash = md5($attach['filedata']);
							$filesize = $attach['filesize'];
							$dateline = TIMENOW;
							$fileextension = $attach['fileextension'];

							// save attachments in database
							// insert into database
							if ($vbulletin->options['attachfile'])
							{
								$tempdata = $filedata;
								$filedata = '';
							}
							$vbulletin->db->query_write("
								INSERT INTO " . TABLE_PREFIX . "attachment
									(
										userid,
										dateline,
										thumbnail_dateline,
										filename,
										filedata,
										visible,
										counter,
										filesize,
										postid,
										filehash,
										extension
									)
								VALUES
									(
										$userid,
										'" . $vbulletin->db->escape_string($dateline) . "',
										'" . $vbulletin->db->escape_string($thumbnail['dateline']) . "',
										'" . $vbulletin->db->escape_string($filename) . "',
										'" . $vbulletin->db->escape_string($filedata) . "',
										1,
										0,
										'" . $vbulletin->db->escape_string($filesize) . "',
										$postid,
										'" . $vbulletin->db->escape_string($filehash) . "',
										'" . $vbulletin->db->escape_string($fileextension) . "'
									)
							");
							$vbulletin->db->query_write("
								UPDATE " . TABLE_PREFIX . "post
								SET
								attach = 1
								WHERE postid = $postid
							");
							$vbulletin->db->query_write("
								UPDATE " . TABLE_PREFIX . "thread
								SET
								attach = 1
								WHERE threadid = $threadid
							");

							$eilog .= "Attachments added to threadid " . $threadid . " from " . $userinfo['username'] . "\r\n";

							// get id for email and for filesystem
							$getid = $vbulletin->db->query_first("
								SELECT attachmentid,dateline,filename
								FROM " . TABLE_PREFIX . "attachment
								WHERE userid = $userid AND dateline = $dateline AND filehash = '" . $vbulletin->db->escape_string($filehash) . "'
							");
							$attachmentid = $getid['attachmentid'];

							// create attachment message for upcoming emails
							$attachmentmessage .= "
Attached to this message is [url=" . $vbulletin->options['bburl'] . "/attachment.php?attachmentid=" . $getid['attachmentid'] . "&d=" . $getid['dateline'] . "]" . $getid['filename'] . "[/url]";


							if ($vbulletin->options['attachfile'])
							{
								if ($vbulletin->options['attachfile'] == 2)
								{
									$path = $vbulletin->options['attachpath'] . '/' . implode('/', preg_split('//', $userid,  -1, PREG_SPLIT_NO_EMPTY));
								}
								else
								{
								$path = $vbulletin->options['attachpath'] . '/' . $userid;
								}
								if (vbmkdir($path))
								{
									$path .= '/' . $attachmentid . '.attach';
								}
								$fp = fopen($path, 'wb');
								fwrite($fp,$tempdata);
								fclose($fp);
							}
						}
					}
					// start sending email
					$useremails = $vbulletin->db->query_read("
						SELECT user.*, subscribeforum.*
						FROM " . TABLE_PREFIX . "subscribeforum AS subscribeforum
						INNER JOIN " . TABLE_PREFIX . "user AS user ON (subscribeforum.userid = user.userid)
						LEFT JOIN " . TABLE_PREFIX . "usergroup AS usergroup ON (usergroup.usergroupid = user.usergroupid)
						LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON (usertextfield.userid = user.userid)
						WHERE subscribeforum.forumid = " . $foruminfo['forumid'] . " AND
						subscribeforum.emailupdate = 1 AND
						" . ($userid ? "CONCAT(' ', IF(usertextfield.ignorelist IS NULL, '', usertextfield.ignorelist), ' ') NOT LIKE ' " . intval($userid) . " ' AND" : '') . "
						user.usergroupid <> 3 AND
						(usergroup.genericoptions & " . $vbulletin->bf_ugp_genericoptions['isnotbannedgroup'] . ")
					");

					$evalemailplain = array();
					$evalemailhtml = array();
					$pagetext = $newpost['message'];
					$poster = $userinfo['username'];
					$signature = $userinfo['signature'];
					while ($touser = $vbulletin->db->fetch_array($useremails))
					{
						if (!($vbulletin->usergroupcache["$touser[usergroupid]"]['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
						{
							continue;
						}


						// Checks this user's usergroup against the list of allowable usergroupids that can use this feature
						$forumusergroupids = explode(",", $foruminfo['ei_usergroups']);
						if (!is_member_of($touser, $forumusergroupids))
						{
							continue;
						}

						// Checks if the user to be emailed is the same as the poster and if they do not want to get their own posts via email
						if (($touser['userid'] == $userinfo['userid']) AND !$touser['ei_own_posts'])
						{
							continue;
						}

						$touser['username'] = unhtmlspecialchars($touser['username']);
						$touser['languageid'] = iif($touser['languageid'] == 0, $vbulletin->options['languageid'], $touser['languageid']);

						if (empty($evalemailplain))
						{
							if ($vbulletin->options['ei_email_layout'])
							{
								$email_texts = $vbulletin->db->query_read("
									SELECT text, languageid, fieldname
									FROM " . TABLE_PREFIX . "phrase
									WHERE fieldname IN ('emailsubject', 'emailbody') AND varname = 'ei_notify_thread_plain_old'
								");
							}
							else
							{
								$email_texts = $vbulletin->db->query_read("
									SELECT text, languageid, fieldname
									FROM " . TABLE_PREFIX . "phrase
									WHERE fieldname IN ('emailsubject', 'emailbody') AND varname = 'ei_notify_thread_plain'
								");
							}

							while ($email_text = $vbulletin->db->fetch_array($email_texts))
							{
								$emails["$email_text[languageid]"]["$email_text[fieldname]"] = $email_text['text'];
							}
							require_once(DIR . '/includes/functions_misc.php');
							foreach ($emails AS $languageid => $email_text)
							{

								// lets cycle through our array of notify phrases
								$text_message = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailbody']), $emails['-1']['emailbody'], $email_text['emailbody'])));
								$text_message = replace_template_variables($text_message);
								$text_subject = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailsubject']), $emails['-1']['emailsubject'], $email_text['emailsubject'])));
								$text_subject = replace_template_variables($text_subject);

								$evalemailplain["$languageid"] = '
									$message = "' . $text_message . '";
									$subject = "' . $text_subject . '";
								';
							}
						}
						if (empty($evalemailhtml))
						{

							$emaillinks = "";

							if ($vbulletin->options['ei_email_footer'])
							{
								if ($vbulletin->options['ei_email_homepage'])
								{
									$emaillinks .= construct_phrase($vbphrase['ei_links_homepage'], $vbulletin->options['homeurl']);
								}
								if ($vbulletin->options['ei_email_forums'])
								{
									if ($emaillinks)
									{
										$emaillinks .= " | ";
									}
									$emaillinks .= construct_phrase($vbphrase['ei_links_forums'], $vbulletin->options['bburl']);
								}
								if ($vbulletin->options['ei_email_calendar'])
								{
									if ($emaillinks)
									{
										$emaillinks .= " | ";
									}
									$emaillinks .= construct_phrase($vbphrase['ei_links_calendar'], $vbulletin->options['bburl']);
								}
								if ($vbulletin->options['ei_email_memberlist'])
								{
									if ($emaillinks)
									{
										$emaillinks .= " | ";
									}
									$emaillinks .= construct_phrase($vbphrase['ei_links_memberlist'], $vbulletin->options['bburl']);
								}
								if ($vbulletin->options['ei_email_search'])
								{
									if ($emaillinks)
									{
										$emaillinks .= " | ";
									}
									$emaillinks .= construct_phrase($vbphrase['ei_links_search'], $vbulletin->options['bburl']);
								}

								if ($emaillinks)
								{
									$emaillinks = $vbphrase['ei_links_start'] . $emaillinks . $vbphrase['ei_links_end'];
								}
							}

							if ($vbulletin->options['ei_email_layout'])
							{
								$email_texts = $vbulletin->db->query_read("
									SELECT text, languageid, fieldname
									FROM " . TABLE_PREFIX . "phrase
									WHERE fieldname IN ('emailsubject', 'emailbody') AND varname = 'ei_notify_thread_html_old'
								");
							}
							else
							{
								$email_texts = $vbulletin->db->query_read("
									SELECT text, languageid, fieldname
									FROM " . TABLE_PREFIX . "phrase
									WHERE fieldname IN ('emailsubject', 'emailbody') AND varname = 'ei_notify_thread_html'
								");
							}

							while ($email_text = $vbulletin->db->fetch_array($email_texts))
							{
								$emails["$email_text[languageid]"]["$email_text[fieldname]"] = $email_text['text'];
							}
							require_once(DIR . '/includes/functions_misc.php');
							foreach ($emails AS $languageid => $email_text)
							{

								// lets cycle through our array of notify phrases
								$text_message = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailbody']), $emails['-1']['emailbody'], $email_text['emailbody'])));
								$text_message = replace_template_variables($text_message);
								$text_subject = str_replace("\\'", "'", addslashes(iif(empty($email_text['emailsubject']), $emails['-1']['emailsubject'], $email_text['emailsubject'])));
								$text_subject = replace_template_variables($text_subject);

								$evalemailhtml["$languageid"] = '
									$message = "' . $text_message . '";
									$subject = "' . $text_subject . '";
								';
							}
						}

						if ((!$vbulletin->options['ei_format_allow_user'] AND $vbulletin->options['ei_format_forced']) OR ($vbulletin->options['ei_format_allow_user'] AND $touser['ei_format']))
						{
							if ($vbulletin->options['ei_email_layout'])
							{
								eval(iif(empty($evalemailhtml["$touser[languageid]"]), $evalemailhtml["-1"], $evalemailhtml["$touser[languageid]"]));
								if(preg_match('#(.*<div id=[\'\"]ei_body[\'\"]>)(.*)(</div>\s*<div id=[\'\"]ei_sig[\'\"]>)(.*)(</div>\s*<div id=[\'\"]ei_links[\'\"]>.*)#is', $message, $messageparts))
								{
									$message = $messageparts[1] .
									$parser->do_parse($messageparts[2], 1, 1, 1, 1, 1, 0) .
									$messageparts[3] .
									$parser->do_parse($messageparts[4], 1, 1, 1, 1, 1, 0) .
									$messageparts[5];
								}
								else
								{
									$message = $parser->do_parse($message, 1, 1, 1, 1, 1, 0);
								}
							}
							else
							{
								eval(iif(empty($evalemailhtml["$touser[languageid]"]), $evalemailhtml["-1"], $evalemailhtml["$touser[languageid]"]));
								if(preg_match('#(.*<div id=[\'\"]ei_reply[\'\"]>)(.*)(</div>\s*<div id=[\'\"]ei_body[\'\"]>)(.*)(</div>\s*<div id=[\'\"]ei_links[\'\"]>.*)#is', $message, $messageparts))
								{
									$message = $messageparts[1] .
									$parser->do_parse($messageparts[2], 1, 1, 1, 1, 1, 0) .
									$messageparts[3] .
									$parser->do_parse($messageparts[4], 1, 1, 1, 1, 1, 0) .
									$messageparts[5];
								}
								else
								{
									$message = $parser->do_parse($message, 1, 1, 1, 1, 1, 0);
								}
							}
						}
						else
						{
							eval(iif(empty($evalemailplain["$touser[languageid]"]), $evalemailplain["-1"], $evalemailplain["$touser[languageid]"]));
							$plaintext_parser->set_parsing_language($touser['languageid']);
							$message = $plaintext_parser->parse($message, $foruminfo['forumid']);
						}

						$emailreturnaddress = $foruminfo['ei_replyaddress'];
						$eiusername = $userinfo['username'];
						$eidelimiter = stripcslashes($vbulletin->options['ei_header_newline']);
						$eiheaders = '';
						$eiheaders .= 'Date: ' . date('r') . $eidelimiter;
						$eifromheader = 'From: ';
						if($vbulletin->options['ei_use_single_from_address'])
						{
							$eifromheader .= $vbulletin->options['ei_single_from_name'] . '<' . $vbulletin->options['ei_single_from_address'] .'>';
						}
						else
						{
							$eifromheader .= "$eiusername <$emailreturnaddress>";
						}
						$eiheaders .= $eifromheader . $eidelimiter;
						$eiheaders .= "Reply-To: $emailreturnaddress" . $eidelimiter;
						$eiheaders .= 'MIME-Version: 1.0' . $eidelimiter;

						if ((!$vbulletin->options['ei_format_allow_user'] AND $vbulletin->options['ei_format_forced']) OR ($vbulletin->options['ei_format_allow_user'] AND $touser['ei_format']))
						{
							$eiheaders .= 'Content-Type: text/html' . iif($encoding, "; charset=\"$encoding\"") . $eidelimiter;
						}
						else
						{
							$eiheaders .= 'Content-Type: text/plain' . iif($encoding, "; charset=\"$encoding\"") . $eidelimiter;
						}

						$eiheaders .= 'Content-Transfer-Encoding: 8bit' . $eidelimiter;
						$eiheaders .= "Message-Id: <$threadid.$postid@emailintegration>" . $eidelimiter;
						$eiheaders .= 'X-Priority: 3' . $eidelimiter;
						$eiheaders .= 'X-Mailer: vBulletin Mail via PHP' . $eidelimiter;
				//		mail($touser['email'], $subject, $message, $eiheaders);
						vbmail($touser['email'], $subject, $message, false, '', $eiheaders);
					}
					if (!$debug)
					{
						imap_delete($mailbox,$msgno);
					}
				}
				unset ($attachmentinfo,$parentid,$refs,$fromaddress,$userinfo);
			}

			// deletes marked messages
			imap_expunge($mailbox);
		}
		else
		{
		$eilog .= "No messages found for " . $foruminfo['title_clean'] . "\r\n";
		}
		// closes mailbox
		imap_close($mailbox);
		unset ($parser);
	}
}

vbmail_end();
}
echo $eilog;
log_cron_action($eilog, $nextitem, 1);

?>
