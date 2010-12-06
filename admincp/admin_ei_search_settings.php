<?php

/*
Original concept code by Colin F in this mod -
http://www.vbulletin.org/forum/showthread.php?t=68113

This code has been COMPLETELY rewritten from the ground up with the exception of including the free mimedecode class file.

Thanks to RedTyger, Bob Denny, Ed Kohwley and Chris McKeever for their additions and assistance with portions of this code.

Huge thanks also goes to the many individuals that have helped test various incarnations of this mod.

Most especially huge thanks to those that have donated!!

If you'd like to donate to the continued production of this modification please do so here -

https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=cyricx%40mmogcommunities%2ecom&item_name=Email%20Integration%20Donation&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8'

-=-=-=-=- Cyricx -=-=-=-=-=-=-

Version 2.5.4

This script is primarily focused on assisting users with determining what connection settings they should use.

*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style');
$specialtemplates = array('products');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');

 // ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminpermissions'))
{
    print_cp_no_permission();
}

print_cp_header($vbphrase['ei_header']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'start';
}

if (!function_exists(imap_open))
{
  echo "Imap not recognized!! You MUST enable IMAP extensions on your server in your php.ini file!<br>";
  continue;
}
else
{

// ###################### Start list #######################
if ($_REQUEST['do'] == 'start')
{
	print_form_header('admin_ei_search_settings', 'test');
	print_table_header($vbphrase['ei_header_search_settings'],2);
	print_description_row($vbphrase['ei_search_settings_how_to'], 0, 2);
	print_table_header($vbphrase['ei_help_enter'],2);
print_input_row($vbphrase['ei_help_hostname'], 'hostname');
print_input_row($vbphrase['ei_help_username'], 'username');
print_input_row($vbphrase['ei_help_password'], 'password');
print_input_row($vbphrase['ei_help_inbox'], 'inbox', 'INBOX');
print_submit_row("Submit!");
	print_table_footer(2, '', '', 0);
}

// ###################### Start list #######################
if ($_POST['do'] == 'test')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'hostname'		=> TYPE_STR,
		'username'		=> TYPE_STR,
		'password'      => TYPE_STR,
		'inbox'         => TYPE_STR
	));

	print_table_start('admin_ei_search_settings');
	print_table_header($vbphrase['ei_header_search_settings'],2);




$success = 0;
$pass = $vbulletin->GPC['password'];
$host = '';
$user = '';
$connect = '';
$box = '';

$connecttypes = array(':110/pop3/notls', ':143/novalidate-cert', ':993/pop3/ssl', ':995/php3/ssl/novalidate-cert', ':993/imap/ssl', ':995/imap/ssl/novalidate-cert');
$connectphrases = array($vbphrase['ei_pop3'],$vbphrase['ei_imap'], $vbphrase['ei_pop3_ssl_of'], $vbphrase['ei_pop3_ssl_ss'], $vbphrase['ei_imap_ssl_of'], $vbphrase['ei_imap_ssl_ss']);
$hosttypes = array($vbulletin->GPC['hostname'],"mail." . $vbulletin->GPC['hostname'] ."", "localhost");
$usertypes = array($vbulletin->GPC['username'], $vbulletin->GPC['username']  . "@". $vbulletin->GPC['hostname']);

if (!preg_match("/inbox/i", $vbulletin->GPC['inbox']))
{
$boxnumber = 2;
$boxtypes = array($vbulletin->GPC['inbox'], "INBOX");
}
else
{
  $boxnumber = 1;
  $boxtypes = array($vbulletin->GPC['inbox']);
}


for ($hostloop = 0; $hostloop < 3; $hostloop = $hostloop + 1)
{
	$host = $hosttypes[$hostloop];
	
	for ($connectloop = 0; $connectloop < 6; $connectloop = $connectloop + 1)
	{
		$connect = $connecttypes[$connectloop];

			for ($boxloop = 0; $boxloop < $boxnumber; $boxloop = $boxloop + 1)
			{
			    $box = $boxtypes[$boxloop];

				for ($userloop = 0; $userloop < 2; $userloop = $userloop + 1)
				{
					$user = $usertypes[$userloop];

					$connectmailserver = "{" . $host . $connect . "}" . $box;
					$mailbox = imap_open($connectmailserver, $user, $pass);

					if ($mailbox == TRUE)
					{
						$success = 1;
						print_description_row($vbphrase['ei_search_settings_use'], 0, 2);
	  					print_table_header($vbphrase['ei_search_settings_success'],2);
						$bg = fetch_row_bgclass();
						echo "<tr><td class=\"" . $bg ."\">HOSTNAME TO USE</td><td class=\"" . $bg . "\">" . $host . "</td></tr>";
						echo "<tr><td class=\"" . $bg ."\">HOSTNAME TO USE</td><td class=\"" . $bg . "\">" . $host . "</td></tr>";
						echo "<tr><td class=\"" . $bg ."\">USERNAME TO USE</td><td class=\"" . $bg . "\">" . $user . "</td></tr>";
						echo "<tr><td class=\"" . $bg ."\">PASSWORD TO USE</td><td class=\"" . $bg . "\">" . $pass . "</td></tr>";
						echo "<tr><td class=\"" . $bg ."\">MAILBOX TO USE</td><td class=\"" . $bg . "\">" . $box . "</td></tr>";
						echo "<tr><td class=\"" . $bg ."\">CONNECTION TYPE TO USE</td><td class=\"" . $bg . "\">" . $connectphrases[$connectloop] . "</td></tr>";
						imap_close($mailbox);
					}
				}
			}
			
	}

}

if ($success == 0)
{
				$bg = fetch_row_bgclass();
				echo "<tr><td colspan =\"2\" class=\"" . $bg ."\">Unable to determine settings! Please click the back button and try again. Please be sure you entered your domain name per the example!!</td></tr>";
}

	print_table_footer(2, '', '', 0);
}
}
?>
