<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'hospitals');
define('HOSPITAL_TABLE', 'aa_companies');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array(
	'user',
	'search'
);

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
	'HOSPITAL_LIST'
);

// pre-cache templates used by specific actions
$actiontemplates = array(
	'none' => array(
		'hospital_list',
		'hospital_list_letter',
		'hospital_list_resultsbit',
		'hospitals_results_header',
		'hospitals_resultsbit_field',
		'forumdisplay_sortarrow'
	),
	'search' => array(
		'memberlist_search',
		'memberlist_search_radio',
		'memberlist_search_select',
		'memberlist_search_select_multiple',
		'memberlist_search_select',
		'memberlist_search_textbox',
		'memberlist_search_optional_input'
	)
);

$actiontemplates['getall'] =& $actiontemplates['none'];

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_misc.php');

$ini	= parse_ini_file('../m/config/config.ini', true);
// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$vbphrase[hospitals]		= "Bloodless Medicine Surgery Hospitals";


$mUrl = isset($_SERVER['SystemRoot']) && $_SERVER['SystemRoot'] == "C:\\WINDOWS" ? "http://m" : "http://mobile.noblood.org";
$tagline			= $ini['default']['site.hospitals.tagline'];
$pageTitle			= 'NoBlood | ' . $ini['default']['site.hospitals.tagline'];
$keywords			= $ini['default']['site.hospitals.keywords'];
$description		= $ini['default']['site.hospitals.description'];
$headinclude = preg_replace('%(<meta name=\"keywords\"\scontent=\")(.*?)(\" />)%sim', '$1' . $keywords . '$3', $headinclude);
$headinclude = preg_replace('%(<meta name=\"description\"\scontent=\")(.*?)(\" />)%sim', '$1' . $description . '$3', $headinclude);

// build navbar
$navbits = array('' => $ini['default']['site.hospitals.tagline']);
$templatename = 'hospitals';

if ($templatename != '')
{
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('print_output("' . fetch_template($templatename) . '");');
}