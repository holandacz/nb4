<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'hospitals');

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
require_once (DIR . '/includes/utf8_to_ascii/utf8_to_ascii.php');
require_once('_lib/ads.php');
$ads		= new ads();

$ini	= parse_ini_file('../m/config/config.ini', true);
$xchg_url = $ini['default']['dir.xchg'];
// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// default action
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'getall';
}

if ($_REQUEST['do'] == 'getall')
{
	$vbphrase[hospitals]		= "Bloodless Medicine Surgery Hospitals Directory";
	$vbphrase[search_hospitals]		= "Search Hospitals";

	$vbphrase[country]				= "Country";
	$vbphrase[state]				= "State/Province";
	$vbphrase[city]					= "City";
	$vbphrase[location]				= "Location";
	$vbphrase[name]					= "Hospital";
	$vbphrase[phone]				= "Phone";

	$perpage 						= 50; //$vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);
	$sortfield 						= $vbulletin->input->clean_gpc('r', 'sortfield', TYPE_STR);
	$sortorder 						= $vbulletin->input->clean_gpc('r', 'sortorder', TYPE_STR);
	$ltr 							= $vbulletin->input->clean_gpc('r', 'ltr', TYPE_NOHTML);
	$pagenumber 					= $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

	$vbulletin->input->clean_array_gpc('r', array(
		'country_name' 		=> TYPE_STR,
		'state'       		=> TYPE_STR,
		'city'       		=> TYPE_STR,
		'name'       		=> TYPE_STR,
		'phone'            	=> TYPE_STR
	));

	// set defaults and sensible values

	if ($sortfield == '')
	{
		$sortfield = 'sponsor';
	}

	if ($sortorder == '')
	{
		$sortorder = 'asc';
	}

	$show['advancedlink'] 	= false;

	// #############################################################################
	// show results
	// start search timer
	$searchstart = microtime();

	// get conditions
	$condition = '1=1';
	if ($vbulletin->GPC['country_name'])
	{
		$condition  .=  " AND country_name LIKE '%" . $db->escape_string_like(htmlspecialchars_uni($vbulletin->GPC['country_name'])) . "%' ";
	}

	if ($vbulletin->GPC['state'])
	{
		$condition .= " AND pri_address_state LIKE '%" . $db->escape_string_like(htmlspecialchars_uni($vbulletin->GPC['state'])) . "%' ";
	}
	if ($vbulletin->GPC['city'])
	{
		$condition .= " AND pri_address_city LIKE '%" . $db->escape_string_like(htmlspecialchars_uni($vbulletin->GPC['city'])) . "%' ";
	}
	if ($vbulletin->GPC['location'])
	{
		$condition .= " AND location LIKE '%" . $db->escape_string_like(htmlspecialchars_uni($vbulletin->GPC['location'])) . "%' ";
	}
	if ($vbulletin->GPC['name'])
	{
		$condition .= " AND companyname LIKE '%" . $db->escape_string_like($vbulletin->GPC['name']) . "%' ";
	}

	if ($ltr != '')
	{
		$ltr = chr(intval(ord($ltr)));
		$condition = 'companyname LIKE("' . $db->escape_string_like($ltr) . '%") OR ';
		$condition .= 'pri_address_city LIKE("' . $db->escape_string_like($ltr) . '%")';
	}

	$sortorder = strtolower($sortorder);

	//$sortfield = 'name';
	//$sqlsort = '_cos.name';



switch ($sortfield)
{
	case 'city':
		$sqlsort = 'pri_address_city';
		$sortfield = 'pri_address_city';
		break;
	case 'state':
		$sqlsort = 'pri_address_state';
		$sortfield = 'pri_address_state';
		break;
	case 'country':
		$sqlsort = 'country_name';
		$sortfield = 'country_name';
		break;
	case 'tel1':
		$sqlsort = 'tel1';
		$sortfield = 'tel1';
		break;
	default:
		$sqlsort = 'companyname';
		$sortfield = 'companyname';
}










	if ($sortorder != 'asc')
	{
		$sortorder = 'desc';
		$oppositesort = 'asc';
	}
	else
	{ // $sortorder = 'ASC'
		$oppositesort = 'desc';
	}

	$selectedletter =& $ltr;

	// build letter selector
	// now do alpha-characters
	for ($i=65; $i < 91; $i++)
	{
		$currentletter = chr($i);
		$linkletter =& $currentletter;
		$show['selectedletter'] = $selectedletter == $currentletter ? true : false;
		eval('$letterbits .= "' . fetch_template('hospital_list_letter') . '";');
	}


	$gvdb = $ini['default'];
echo $gvdb['db.gv.host'];

	//$conn = mysql_connect($gvdb['db.gv.host'], $gvdb['db.gv.username'], $gvdb['db.gv.password']);
echo 'here';
        $conn = mysql_connect('xchg.com', 'nb', 'n.2009B');

	$database = $gvdb['db.gv.dbname'];
	mysql_select_db($database, $conn) or die ("Database not found.");

	$rows = mysql_query("
SELECT COUNT(*) AS hospitals
FROM ((`contacts` `c`)
   LEFT JOIN `countries`
     ON ((`countries`.`iso` = `c`.`pri_address_country`)))
WHERE (($condition) && (FIND_IN_SET('hospital',`c`.`nb_tags`)
       AND FIND_IN_SET('publish',`c`.`nb_tags`)))
    ") or die("Failed Hospital Count Query");


	$row = mysql_fetch_assoc($rows);
	$totalhospitals = $row['hospitals'];


	if (!$totalhospitals)
	{
		eval(standard_error(fetch_error('searchnoresults', $displayCommon)));
	}

	// set defaults
	sanitize_pageresults($totalhospitals, $pagenumber, $perpage, 100, $vbulletin->options['hospitalsperpage']);

	$sortaddon = ($vbulletin->GPC['countrylower']) ? 'countrylower=' . $vbulletin->GPC['countrylower'] . '&amp;' : '';
	$sortaddon .= ($vbulletin->GPC['countryupper']) ? 'countryupper=' . $vbulletin->GPC['countryupper'] . '&amp;' : '';
	$sortaddon .= ($vbulletin->GPC['state'] != '') ? 'state=' . urlencode($vbulletin->GPC['state']) . '&amp;' : '';
	$sortaddon .= ($vbulletin->GPC['city'] != '') ? 'city=' . urlencode($vbulletin->GPC['city']) . '&amp;' : '';
	$sortaddon .= ($vbulletin->GPC['name'] != '') ? 'companyname=' . urlencode($vbulletin->GPC['name']) . '&amp;' : '';
	$sortaddon .= ($ltr != '') ? 'ltr=' . urlencode($ltr) . '&amp;' : '';

	$sortaddon = preg_replace('#&amp;$#s', '', $sortaddon);

	//$sorturl = 'hospitals.php?' . $vbulletin->session->vars['sessionurl'] . $sortaddon;
	$sorturl = 'hospitalsDirectory.php?' . $vbulletin->session->vars['sessionurl'] . $sortaddon;


	eval('$sortarrow[' . $sortfield . '] = "' . fetch_template('forumdisplay_sortarrow') . '";');

	$hospitalsbit = '';
	$limitlower = ($pagenumber - 1) * $perpage + 1;
	$limitupper = ($pagenumber) * $perpage;
	$counter = 0;

	if ($limitupper > $totalhospitals)
	{
		$limitupper = $totalhospitals;
		if ($limitlower > $totalhospitals)
		{
			$limitlower = $totalhospitals - $perpage;
		}
	}
	if ($limitlower <= 0)
	{
		$limitlower = 1;
	}

	//$sortorder = ($sortorder == 'desc' ? 'asc' : 'desc');
//echo $sqlsort, $sortorder, $secondarysortsql;

    $hospitals = mysql_query("
SELECT
  `c`.`id`                     AS `id`,
  `c`.`companyname`            AS `name`,
  `c`.`companyabbrev`          AS `companyabbrev`,
  `c`.`department`             AS `department`,
  `c`.`pri_address`            AS `street`,
  `c`.`pri_address_city`       AS `city`,
  `c`.`pri_address_state`      AS `state`,
  `c`.`pri_address_zip`        AS `zip`,
  `c`.`pri_address_country`    AS `country`,
  `countries`.`printable_name` AS `country_name`,
  `c`.`tel_work`               AS `tel1`,
  `c`.`tel_other`              AS `tel2`,
  `c`.`pri_address_lat`        AS `lat`,
  `c`.`pri_address_long`       AS `lng`,
  `c`.`pri_address_mapurl`     AS `mapurl`,
  `c`.`webpage`                AS `url`,
  FIND_IN_SET('sponsor',`c`.`nb_tags`) AS `sponsor`,
  FIND_IN_SET('premium',`c`.`nb_tags`) AS `premium`,
  FIND_IN_SET('premium',`c`.`nb_tags`) > 0 AS `premium_first`
FROM (`contacts` `c`
   LEFT JOIN `countries`
     ON ((`countries`.`iso` = `c`.`pri_address_country`)))
WHERE (($condition) AND (FIND_IN_SET('hospital',`c`.`nb_tags`) AND FIND_IN_SET('publish',`c`.`nb_tags`)))
ORDER BY  premium_first DESC, $sqlsort $sortorder $secondarysortsql
LIMIT " . ($limitlower - 1) . ", $perpage
    ") or die("Failed Query");





	$counter = 0;
	$hospitalsbits = '';
	$today_year = vbdate('Y', TIMENOW, false, false);
	$today_month = vbdate('n', TIMENOW, false, false);
	$today_day = vbdate('j', TIMENOW, false, false);

	// initialize counters
	$itemcount = ($pagenumber - 1) * $perpage;
	$first = $itemcount + 1;
	$totalcols = 7;


	$mUrl = isset($_SERVER['SystemRoot']) && $_SERVER['SystemRoot'] == "C:\\WINDOWS" ? "http://localhost:9000" : "http://gv.xchg.com";
	//while ($hospitalinfo = $db->fetch_array($hospitals) AND $counter++ < $perpage)
	while ($hospitalinfo = mysql_fetch_assoc($hospitals) AND $counter++ < $perpage)
	{
		$hospitalinfo['mUrl']			= $mUrl;
		$result = preg_replace('/%\d*-*/sim', '', urlencode(str_replace(' ', '-', $hospitalinfo['name'])));
		$hospitalinfo['paramName']		= $result;
		$result = preg_replace('/%\d*-*/sim', '', urlencode(str_replace(' ', '-', $hospitalinfo['city'])));
		$hospitalinfo['paramCity']		= $result;

		$bgclass = iif(($totalcols % 2) == 1, 'alt2', 'alt1');
		$itemcount++;
		eval('$hospitalsbits .= "' . fetch_template('hospital_list_resultsbit') . '";');
	}  // end while

	$last = $itemcount;

	$pagenav = construct_page_nav($pagenumber, $perpage, $totalhospitals, 'hospitalsDirectory.php?' . $vbulletin->session->vars['sessionurl'] . 'do=getall', ''
		. (!empty($vbulletin->GPC['perpage']) ? "&amp;pp=$perpage" : "")
		. (!empty($sortorder) ? "&amp;order=$sortorder" : "")
		. (!empty($sortfield) ? "&amp;sort=$sortfield" : "")
		. (!empty($sortaddon) ? "&amp;$sortaddon" : "")
	);

$pageTitle			= 'NoBlood | ' . $ini['default']['site.hospitals.tagline'];
$disclaimer			= $ini['default']['site.hospitals.disclaimer'];
$keywords			= $ini['default']['site.hospitals.keywords'];
$description		= $ini['default']['site.hospitals.description'];
$headinclude = preg_replace('%(<meta name=\"keywords\"\scontent=\")(.*?)(\" />)%sim', '$1' . $keywords . '$3', $headinclude);
$headinclude = preg_replace('%(<meta name=\"description\"\scontent=\")(.*?)(\" />)%sim', '$1' . $description . '$3', $headinclude);

	// build navbar
	$navbits[trim($ini['default']['site.hospitals.pathPart'], '/')] = $ini['default']['site.hospitals.tagline'];
	$navbits[''] = 'Directory';

	$searchtime = vb_number_format(fetch_microtime_difference($searchstart), 2);
	$templatename = 'hospitalsDirectory';
}

if ($templatename != '')
{
	$navbits = construct_navbits($navbits);
	eval('$navbar = "' . fetch_template('navbar') . '";');
	eval('print_output("' . fetch_template($templatename) . '");');
}
