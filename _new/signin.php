<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'signin');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', 'signin');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array('signin');

// pre-cache templates used by specific actions
$actiontemplates = array(
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
// ############################### start do login ###############################
// this was a _REQUEST action but where do we all login via request?
if ($_REQUEST['do'] == 'signin')
{

eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('signin') . '");');

}