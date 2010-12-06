<?php
error_reporting(E_ALL & ~E_NOTICE);

define('THIS_SCRIPT', 'index');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');

$phrasegroups = array('inlinemod', 'search');

$specialtemplates = array(
	'userstats',
	'maxloggedin',
	'iconcache',
	'eventcache',
	'mailqueue',
	'blogstats',
	'blogcategorycache',
	'tagcloud',
);

$globaltemplates = array(
	'HOME',
	'home_features',
	'home_content',
);

$actiontemplates = array(
	'cloud' => array(
		'tag_cloud_box',
		'tag_cloud_headinclude',
		'tag_cloud_link'
	),
	'tag' => array(
		'tag_search',
		'threadadmin_imod_menu_thread',
		'threadbit'
	)
);

require_once('./global.php');
require_once(DIR . '/includes/functions_bigthree.php');

$today = vbdate('Y-m-d', TIMENOW, false, false);
require_once('_snips/featured.php');
require_once('_snips/conditions.php');
require_once('_snips/treatments.php');
require_once('_snips/community.php');
eval('$features = "' . fetch_template('features') . '";');
//$content	= file_get_contents('_snips/content.htm');

require_once('_snips/news.php');
require_once('_snips/discussions.php');
require_once('_snips/directories.php');
require_once('_snips/content_tag_cloud.php');
require_once('_snips/hospital_sponsors.php');
//require_once('_snips/content_get_involved.php');
require_once('_snips/hot_topics.php');

eval('$content = "' . fetch_template('home_content') . '";');
eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('HOME') . '");');