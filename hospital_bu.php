<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'hospital');
define('HOSPITAL_TABLE', 'nbcompanies_hospitals_active');
//define('HOSPITAL_TABLE', 'nbcompanies');
//define('CMPSUSRS_TABLE', '_cos_nas');
//define('CMPSUSRS_KEY', 'coid');

define('CMPSUSRS_TABLE', 'nbnam_links');
define('CMPSUSRS_KEY', 'id');

define('ADS_TABLE', 'ads_active');
define('ADS_KEY', 'nam_id');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array(
);

// get special data templates from the datastore
$specialtemplates = array(
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'hospital'
);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
$ini	= parse_ini_file('../m/config/config.ini', true);
// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$vbulletin->input->clean_array_gpc('r', array(
	'find' => TYPE_STR,
	'id' => TYPE_UINT,
	'coid' => TYPE_UINT
));

$vbphrase[hospital]				= "Hospital Info";
$vbphrase[hospital_contacts]	= 'Staff Contacts';
$vbphrase[hospital_location]	= 'Location';

if (!$vbulletin->GPC['id'])
	if (!$vbulletin->GPC['coid'])
		die('missing id');
	else
		$vbulletin->GPC['id']	= $vbulletin->GPC['coid'];

$gvdb = $ini['default'];
$conn = mysql_connect($gvdb['db.gv.host'], $gvdb['db.gv.username'], $gvdb['db.gv.password']);
$database = $gvdb['db.gv.dbname'];



mysql_select_db($database, $conn) or die ("Database not found.");



// replug in new banner ads
$rows = mysql_query("SELECT * FROM " . TABLE_PREFIX . HOSPITAL_TABLE . " as h
LEFT JOIN " . TABLE_PREFIX . ADS_TABLE . " as a
		ON (h.id = a.nam_id)
WHERE id = '" . $db->escape_string($vbulletin->GPC['id']) . "'");


//echo $sql . '<hr>';

if (!$hospital = mysql_fetch_assoc($rows))
	die('hospital query failed');
//print_r($hospital);
//echo '<hr>';
/*
$sql	= '
SELECT
	'.CMPSUSRS_TABLE.'.'.CMPSUSRS_KEY.'
	, '.CMPSUSRS_TABLE.'.dept
	, '.CMPSUSRS_TABLE.'.title
	, '.CMPSUSRS_TABLE.'.email
	, user.userid
	, user.username
	, user.email
	, userfield.field20 AS Prefix
	, userfield.field10 AS FirstName
	, userfield.field11 AS LastName
	, userfield.field19 AS Suffix
	, userfield.field12 AS Title
	, userfield.field9 AS Phones
FROM
	'.CMPSUSRS_TABLE.'
	INNER JOIN user
		ON ('.CMPSUSRS_TABLE.'.userid = user.userid)
	INNER JOIN userfield
		ON (user.userid = userfield.userid)
WHERE ('.CMPSUSRS_TABLE.'.publish AND '.CMPSUSRS_TABLE.'.company_id = ' . $hospital['id'] . ')
ORDER BY userfield.field11 ASC, userfield.field10 ASC
';
*/




if ($contacts = mysql_query('SELECT * FROM '.CMPSUSRS_TABLE.' WHERE ( parent_id = ' . $hospital['id'] . ') ORDER BY fullname ASC')){

    $mUrl = isset($_SERVER['SystemRoot']) && $_SERVER['SystemRoot'] == "C:\\WINDOWS" ? "http://localhost:9000" : "http://gv.xchg.com";
	$hospital['mUrl']			= $mUrl;
	while ($contact = mysql_fetch_assoc($contacts))
	{
		//print_r($contact);
		$contact['mUrl']			= $mUrl;
		$has_contacts	= true;
		eval('$hospital_contact_bits .= "' . fetch_template('hospital_contact_resultsbit') . '";');
	}
}

$navbits = construct_navbits(array(
	//'hospitals.php?' => $ini['default']['site.hospitals.tagline'] . ' Directory',
	'bloodless-medicine-surgery-hospitals-directory?' => $ini['default']['site.hospitals.tagline'] . ' Directory',
	'' => $hospital['name']
));

eval('$navbar = "' . fetch_template('navbar') . '";');

$bgclass = 'alt2';
$bgclass1 = 'alt1';

$templatename = 'hospital';

$pageTitle			= 'NoBlood | ' . $hospital['name'] . ' ' . $hospital['city'] . ', ' . $hospital['state'];
$vbulletin->userinfo['where'] = $hospital['name'] . ' ' . $hospital['city'] . ', ' . $hospital['state'];
$disclaimer			= $ini['default']['site.hospitals.disclaimer'];


$description	= $hospital['name'] . ' located in ' . $hospital['city'] . ', ' . $hospital['state']
					. ' ' . $ini['default']['site.hospital.description'];

$keywords		= $hospital['name'] . ',' . implode(',', split(' ', $hospital['name'])) . ',' . $hospital['city'] . ','
				. $row['state'] . ',' . $ini['default']['site.hospital.keywords'];
$headinclude = preg_replace('%(<meta name=\"keywords\"\scontent=\")(.*?)(\" />)%sim', '$1' . $keywords . '$3', $headinclude);
$headinclude = preg_replace('%(<meta name=\"description\"\scontent=\")(.*?)(\" />)%sim', '$1' . $description . '$3', $headinclude);

eval('print_output("' . fetch_template($templatename) . '");');
