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
		'threadadmin_imod_menu_thread',
		'threadbit'
	)
);

require_once('./global.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/functions_search.php');
require_once(DIR . '/includes/functions_misc.php');

$today = vbdate('Y-m-d', TIMENOW, false, false);



require_once('_lib/ads.php');
$ads		= new ads();

require_once('_snips/featured.php');
require_once('_snips/conditions.php');
require_once('_snips/treatments.php');
require_once('_snips/community.php');
eval('$features = "' . fetch_template('features') . '";');
//$content	= file_get_contents('_snips/content.htm');

require_once('_snips/news.php');
require_once('_snips/discussions.php');

require_once('gallery/thumbnails.php');

require_once('_snips/gallery.php');
//require_once('_snips/ads.php');
require_once('_snips/directories.php');
require_once('_snips/content_tag_cloud.php');
require_once('_snips/hospital_sponsors.php');
//require_once('_snips/content_get_involved.php');
require_once('_snips/map.php');
require_once('_snips/the_pulse.php');
require_once('_snips/hot_topics.php');
require_once('_snips/tag_cloud.php');
if ( is_member_of( $vbulletin->userinfo, 6 ) )
	require_once('_snips/google_tag_cloud.php');

eval('$content = "' . fetch_template('home_content') . '";');
eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('HOME') . '");');
