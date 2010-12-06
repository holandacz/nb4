<?php
error_reporting(E_ALL & ~E_NOTICE);
define('THIS_SCRIPT', 'map');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array();

$actiontemplates['getall'] =& $actiontemplates['none'];

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_misc.php');
require_once (DIR . '/includes/utf8_to_ascii/utf8_to_ascii.php');
require_once('_lib/ads.php');
$ads        = new ads();

$ini    = parse_ini_file('../m/config/config.ini', true);
require_once('./map_config.php'); // must go after parse_ini_file

// default action
if (empty($_REQUEST['do']))
{
    $_REQUEST['do'] = 'getall';
}

if ($_REQUEST['do'] == 'json')
{
    $conn = mysql_connect($cfg['db.gv.host'], $cfg['db.gv.username'], $cfg['db.gv.password']);
    $database = $cfg['db.gv.dbname'];
    mysql_select_db($database, $conn) or die ("Database not found.");

    $json['markers']    = get_markers();
    //$json['reccounts']    = get_reccounts();
    echo json_encode($json);
    return;
}
if ($_REQUEST['do'] == 'getall')
{
    // #############################################################################
    // show results
    // start search timer
    $searchstart = microtime();


$pageTitle            = 'NoBlood | ' . $ini['default']['site.hospitals.tagline'];
$disclaimer            = $ini['default']['site.hospitals.disclaimer'];
$keywords            = $ini['default']['site.hospitals.keywords'];
$description        = $ini['default']['site.hospitals.description'];
$headinclude = preg_replace('%(<meta name=\"keywords\"\scontent=\")(.*?)(\" />)%sim', '$1' . $keywords . '$3', $headinclude);
$headinclude = preg_replace('%(<meta name=\"description\"\scontent=\")(.*?)(\" />)%sim', '$1' . $description . '$3', $headinclude);

$active_ads_scaled    = $ads->scaled;
    //$map    = 'google.setOnLoadCallback(initialize);';

    // build navbar
    $navbits[''] = 'The World of Bloodless Medicine and Surgery';

    $searchtime = vb_number_format(fetch_microtime_difference($searchstart), 2);
    $templatename = 'map';
    $js            = '
<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
<script src="includes/gmap/Gmap.js" type="text/javascript"></script>
';
}
if ($templatename != '')
{
    $navbits = construct_navbits($navbits);
    eval('$navbar = "' . fetch_template('navbar') . '";');
    eval('print_output("' . fetch_template($templatename) . '");');
}

function get_reccounts(){
    global $vbulletin, $cfg;

    $ret_recounts    = array();

    $result = mysql_query("SELECT COUNT(*) AS rec_count FROM contacts WHERE pri_address_lat <> 0 && FIND_IN_SET('publish',nb_tags) && FIND_IN_SET('hospital',nb_tags)") or die("Failed Query");
    $row = mysql_fetch_assoc($result);
    $ret_recounts['hospitals'] = $row['rec_count'];


    $result = mysql_query("SELECT COUNT(*) AS rec_count FROM contacts WHERE pri_address_lat <> 0 && FIND_IN_SET('publish',nb_tags) && FIND_IN_SET('other_company',nb_tags)") or die("Failed Query");
    $row = mysql_fetch_assoc($result);
    $ret_recounts['companies'] = $row['rec_count'];

    return $ret_recounts;
}

function get_markers(){
    global $vbulletin, $cfg;

    $rows = mysql_query("
SELECT
  `c`.`id`                     AS `id`,
  `a`.`id`                     AS `ad_id`,
  `c`.`companyname`            AS `name`,
  `c`.`department`             AS `department`,
  `c`.`pri_address`            AS `street`,
  `c`.`pri_address_city`       AS `city`,
  `c`.`pri_address_state`      AS `state`,
  `c`.`pri_address_zip`        AS `zip`,
  `countries`.`printable_name` AS `country_name`,
  `c`.`tel_work`               AS `tel1`,
  `c`.`tel_other`              AS `tel2`,
  `c`.`pri_address_lat`        AS `lat`,
  `c`.`pri_address_long`       AS `lng`,
  `c`.`pri_address_mapurl`     AS `mapurl`,
  `c`.`webpage`                AS `url`,
  `a`.`ad_copy`                AS `ad_copy`,
  FIND_IN_SET('sponsor',`c`.`nb_tags`) AS `sponsor`,
  FIND_IN_SET('premium',`c`.`nb_tags`) AS `premium`,
  FIND_IN_SET('premium',`c`.`nb_tags`) > 0 AS `premium_first`
FROM (`contacts` `c`
   LEFT JOIN `countries`
     ON ((`countries`.`iso` = `c`.`pri_address_country`))
   LEFT JOIN `nb_ads` `a`
     ON ((`a`.`contact_id` = `c`.`id`)))
WHERE (((FIND_IN_SET('hospital',`c`.`nb_tags`) || FIND_IN_SET('other_company',nb_tags)))
       && FIND_IN_SET('publish',`c`.`nb_tags`) && pri_address_lat <> 0)
    ") or die("Failed Query");

    $markers    = array();
    $is_admin   = $vbulletin->userinfo['permissions']['adminpermissions'];
    while ($row = mysql_fetch_assoc($rows))
    {
        $url        = get_url($row);
        $html         = '';
        if ($is_sponsor    = $row['sponsor'] && $row['ad_id']){
            $img_file    = $cfg['dir.forum'] . 'ads/' . $row['ad_id'] . '.png';
            list($width, $height, $type, $attr) = getimagesize($img_file);
            $html         .= "<img src=\"$img_file\" width=$width height=$height /><br />" ;
        }


        if ($is_admin || in_array($vbulletin->userinfo['userid'], array(1,0,5650)))
            $html .=  '<a href="' . $cfg['dir.xchg'] . '/contacts/' . $row['id'] . '/edit">*</a> ';
/*
        if ($is_sponsor){
            $html    .= '<a href="' . $cfg['dir.home'] . 'our_sponsors/">Sponsor</a> ';
        }
        */
        $html    .= '<a href="' . $url . '">' . htmlspecialchars($row['name'])
            . '</a>';
        $html    .= '<br />' . htmlspecialchars(trim($row['city'] . ' ' .
                    $row['state'] . ' ' . $row['country_name']));

        $html    .= '<br />' . htmlspecialchars($row['tel1']);

        $markers[]    = array(
                'lat' => $row['lat'],
                'lng' => $row['lng'],
                'html' => $html,
            );
    }
    return $markers;
}

function get_url($data){
    $name    = preg_replace('/%\d*-*/sim', '', urlencode(str_replace(' ', '-', $data['name'])));
    $city     = preg_replace('/%\d*-*/sim', '', urlencode(str_replace(' ', '-', $data['city'])));


    return sprintf($cfg['dir.home'] . 'hospital.php?id=%d&amp;name=%s&amp;city=%s',
        $data['id'], $name, $city
    );

}

