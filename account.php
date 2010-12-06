<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'account');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', 'login');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array(
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'account_shell'
);

// pre-cache templates used by specific actions
$actiontemplates = array(
	'account_signin' => array(
		'account_signin'
	)
);

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_login.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'signin';
}
// ############################### toggle user css ###############################

// set shell template name
$templatename = '';

// initialise onload event
$onload = '';

// start the navbar
$navbits = array('account.php' . $vbulletin->session->vars['sessionurl_q'] => 'Account');
$redirect_path = 'usercp.php';

if ($_REQUEST['do'] == 'signin')
{
	$templatename 	= 'account_signin';
	$class_signin	= 'class="m_highlightedlink"';
	//$class_create	= 'class="m_highlightedlink"';
	//$class_recover	= 'class="m_highlightedlink"';
}

if ($templatename != '')
{
	// make navbar
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');

	// shell template
	eval('$HTML = "' . fetch_template($templatename) . '";');
	eval('print_output("' . fetch_template('Account_Shell') . '");');
}