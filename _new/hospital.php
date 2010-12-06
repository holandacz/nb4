<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'hospital');
define('HOSPITAL_TABLE', 'aa_companies');
//define('CMPSUSRS_TABLE', '_cos_nas');
//define('CMPSUSRS_KEY', 'coid');

define('CMPSUSRS_TABLE', 'aa_cmps_usrs');
define('CMPSUSRS_KEY', 'id');

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

if (!$hospital = $db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . HOSPITAL_TABLE . " WHERE id = '" . $db->escape_string($vbulletin->GPC['id']) . "'"))
	die('hospital query failed');


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
$sql	= '
SELECT s.id, s.outlook_id, s.adminNotes, s.inserted_on, s.inserted_userid, s.inserted_username, s.updated_on, s.updated_userid, s.updated_username, s.delete, s.initials, s.gender, s.isJDub, s.assistantname, s.managername, s.profession, s.referredby, s.spouce, s.business_fax, s.business_telephone, s.business_telephone2, s.assistanttelephone, s.callbacktelephone, s.companymaintelephone, s.mobilephone, s.pager, s.primarytelephone, s.importance, s.publish, s.userid, s.company_id, s.department, s.jobtitle, CONCAT(trim(CONCAT(lastname, " ", suffix)), if(trim(CONCAT(title, " ", firstname, " ", middlename))<>"", CONCAT(", ", trim(CONCAT(title, " ", firstname, " ", middlename))),"")) AS fullname, CONCAT(trim( CONCAT(business_telephone, if(business_telephone2>"", CONCAT("; ", business_telephone2), ""))), if(mobilephone>"", CONCAT("; ", CONCAT(mobilephone, "-Cell", "")), ""), if(pager>"", CONCAT("; ", CONCAT(pager, "-Pager", "")), ""), if(business_fax>"", CONCAT("; ", CONCAT(business_fax, "-FAX", "")), "") ) AS phones
FROM '.CMPSUSRS_TABLE.' AS `s`
WHERE ( s.publish AND userid>0 AND company_id=' . $hospital['id'] . ')
ORDER BY fullname ASC
';


if ($contacts = $db->query_read_slave($sql)){

	$mUrl = isset($_SERVER['SystemRoot']) && $_SERVER['SystemRoot'] == "C:\\WINDOWS" ? "http://m" : "http://m.noblood.org";
	$hospital['mUrl']			= $mUrl;
	while ($contact = $db->fetch_array($contacts))
	{
		$contact['mUrl']			= $mUrl;
		$has_contacts	= true;
		eval('$hospital_contact_bits .= "' . fetch_template('hospital_contact_resultsbit') . '";');
	}
}

$navbits = construct_navbits(array(
	'hospitals.php?' => $ini['default']['site.hospitals.tagline'] . ' Directory',
	'' => $hospital['name']
));

eval('$navbar = "' . fetch_template('navbar') . '";');

$bgclass = 'alt2';
$bgclass1 = 'alt1';

$templatename = 'hospital';

$pageTitle			= 'NoBlood | ' . $hospital['name'] . ' ' . $hospital['city'] . ', ' . $hospital['state'];
$disclaimer			= $ini['default']['site.hospitals.disclaimer'];


$description	= $hospital['name'] . ' located in ' . $hospital['city'] . ', ' . $hospital['state']
					. ' ' . $ini['default']['site.hospital.description'];

$keywords		= $hospital['name'] . ',' . implode(',', split(' ', $hospital['name'])) . ',' . $hospital['city'] . ','
				. $row['state'] . ',' . $ini['default']['site.hospital.keywords'];
$headinclude = preg_replace('%(<meta name=\"keywords\"\scontent=\")(.*?)(\" />)%sim', '$1' . $keywords . '$3', $headinclude);
$headinclude = preg_replace('%(<meta name=\"description\"\scontent=\")(.*?)(\" />)%sim', '$1' . $description . '$3', $headinclude);

eval('print_output("' . fetch_template($templatename) . '");');