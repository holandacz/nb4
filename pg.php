<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'pg');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');

// get special data templates from the datastore
$specialtemplates = array(
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'pg_shell'
);

// pre-cache templates used by specific actions
$actiontemplates = array(
);

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once('_lib/ads.php');
$ads		= new ads();
//require_once(DIR . '/includes/functions_login.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'default';
}
// ############################### toggle user css ###############################

// set shell template name
$templatename = '';

// initialise onload event
$onload = '';

$phrasegroups = array('search');

$specialtemplates = array(
	'iconcache',
	'eventcache',
	'mailqueue',
	'blogcategorycache',
	'tagcloud',
);

$globaltemplates = array(
	'HOME',
	'pg_content',
	'tag_cloud_link',
	'tag_cloud_box_search',
	'tag_cloud_headinclude'
);

$actiontemplates = array(
	'cloud' => array(
		'tag_cloud_box',
		'tag_cloud_headinclude',
		'tag_cloud_link'
	),
	'tag' => array(
		'tag_search',
	)
);

require_once('./global.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/functions_search.php');
require_once(DIR . '/includes/functions_misc.php');

$today = vbdate('Y-m-d', TIMENOW, false, false);

if ($_REQUEST['do'] == 'our_sponsors')
{
	$sponsorsBits	= $ads->list;
	$templatename 	= 'pg_sponsors_list';
	$pg_title_class	= 'pg_titleBar';
	$pg_title_text	= 'noblood Sponsors';
	$pg_nav_links_template = 'pg_about_nav_links';
	$here8	= 'pg_nav_here';
	$navbits = array('pg.php' . $vbulletin->session->vars['sessionurl_q'] => 'Our Sponsors');
}


if ($_REQUEST['do'] == 'default')
{
	$templatename 	= 'pg_about';
	$pg_title_class	= 'pg_titleBar';
	$pg_title_text	= 'About noblood';
	$pg_nav_links_template = 'pg_about_nav_links';
	$here0	= 'pg_nav_here';
	$navbits = array('pg.php' . $vbulletin->session->vars['sessionurl_q'] => 'About');
}

if ($_REQUEST['do'] == 'safe_list')
{
	$templatename 	= 'pg_safe_list';
	$pg_title_class	= 'pg_titleBar';
	$pg_title_text	= 'Safe List';
	$pg_nav_links_template = 'pg_about_nav_links';
	$here20	= 'pg_nav_here';
	$navbits = array('pg.php' . $vbulletin->session->vars['sessionurl_q'] => 'Safe List');
}

if ($_REQUEST['do'] == 'link_to_us')
{
	$templatename 	= 'pg_link_to_us';
	$pg_title_class	= 'pg_titleBar';
	$pg_title_text	= 'Link To Us';
	$pg_nav_links_template = 'pg_about_nav_links';
	$here21	= 'pg_nav_here';
	$navbits = array('pg.php' . $vbulletin->session->vars['sessionurl_q'] => 'Link To Us');
}

require_once('_snips/content_tag_cloud.php');
require_once('_snips/tag_cloud.php');

if ($templatename != '')
{
	// make navbar
	$navbits = construct_navbits($navbits);
	eval('$pg_nav_links = "' . fetch_template($pg_nav_links_template) . '";');
	eval('$html = "' . fetch_template($templatename) . '";');
	eval('$content = "' . fetch_template('pg_content') . '";');
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('print_output("' . fetch_template('HOME') . '");');
}