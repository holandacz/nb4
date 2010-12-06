<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################

$phrasegroups = array(
);

$specialtemplates = array(
);

$globaltemplates = array(
    'hospital'
);

$actiontemplates = array();
require_once('./global.php');
$ini    = parse_ini_file('../m/config/config.ini', true);
$xchg_url = $ini['default']['dir.xchg'];

$vbulletin->input->clean_array_gpc('r', array(
    'find' => TYPE_STR,
    'id' => TYPE_UINT,
    'coid' => TYPE_UINT
));

$vbphrase[hospital]             = "Hospital Info";
$vbphrase[hospital_contacts]    = 'Staff Contacts';
$vbphrase[hospital_location]    = 'Location';

if (!$vbulletin->GPC['id'])
    if (!$vbulletin->GPC['coid'])
        die('missing id');
    else
        $vbulletin->GPC['id']    = $vbulletin->GPC['coid'];

$gvdb = $ini['default'];
$conn = mysql_connect($gvdb['db.gv.host'], $gvdb['db.gv.username'], $gvdb['db.gv.password']);
$database = $gvdb['db.gv.dbname'];

mysql_select_db($database, $conn) or die ("Database not found.");
$rows = mysql_query("
SELECT
  `c`.`id`                     AS `id`,
  `c`.`companyname`            AS `name`,
  `c`.`companyabbrev`          AS `companyabbrev`,
  `c`.`department`             AS `dept`,
  `c`.`pri_address`            AS `address1`,
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
  `c`.`webpage`                AS `webpage`,
  `nb_ads`.`id`                AS `ad_id`,
  `nb_ads`.`width`         AS `width`,
  `nb_ads`.`height`        AS `height`,
  FIND_IN_SET('sponsor',`c`.`nb_tags`) > 0 AS `sponsor`,
  FIND_IN_SET('premium',`c`.`nb_tags`) > 0 AS `premium`
FROM (`contacts` `c`
   LEFT JOIN `countries`
     ON ((`countries`.`iso` = `c`.`pri_address_country`))
   LEFT JOIN `nb_ads`
     ON ((`nb_ads`.`contact_id` = `c`.`id`))
     )
WHERE (c.id = '" . $db->escape_string($vbulletin->GPC['id']) . "')
");

//echo $sql . '<hr>';

if (!$hospital = mysql_fetch_assoc($rows))
    die('hospital query failed');


$contacts = mysql_query("
SELECT
r.id AS id,
CONCAT(TRIM(CONCAT(c.lastname, ' ', c.suffix)),
    IF(TRIM(CONCAT(c.title, ' ', c.firstname, ' ', c.middlename))<>'',
    CONCAT(', ', TRIM(CONCAT(c.title, ' ',
    c.firstname, ' ', c.middlename))),'')) AS fullname,

CONCAT(TRIM(c.tel_work),'',
    IF(c.tel_mobile>'', CONCAT('; ', CONCAT(c.tel_mobile, '-Mobile', '')), ''),
    IF(c.tel_pager>'', CONCAT('; ', CONCAT(c.tel_pager, '-Pager', '')), '')
    ) AS phones,
c.nb_user_id AS userid,
r.publish AS publish,
r.type AS TYPE,
r.weight AS weight,
r.department AS department,
r.jobtitle AS jobtitle
FROM contacts_related r
   LEFT JOIN `contacts` `c`
     ON ((`r`.`child_id` = `c`.`id`))
WHERE parent_id = '" . $db->escape_string($vbulletin->GPC['id']) . "' && publish
ORDER BY weight, lastname
");

while ($contact = mysql_fetch_assoc($contacts))
{
    //print_r($contact);
    $has_contacts    = true;
    eval('$hospital_contact_bits .= "' . fetch_template('hospital_contact_resultsbit') . '";');
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

$pageTitle            = 'NoBlood | ' . $hospital['name'] . ' ' . $hospital['city'] . ', ' . $hospital['state'];
$vbulletin->userinfo['where'] = $hospital['name'] . ' ' . $hospital['city'] . ', ' . $hospital['state'];
$disclaimer           = $ini['default']['site.hospitals.disclaimer'];


$description = $hospital['name'] . ' located in ' . $hospital['city'] . ', ' . $hospital['state']
                    . ' ' . $ini['default']['site.hospital.description'];

$keywords        = $hospital['name'] . ',' . implode(',', split(' ', $hospital['name'])) . ',' . $hospital['city'] . ','
                . $row['state'] . ',' . $ini['default']['site.hospital.keywords'];
$headinclude = preg_replace('%(<meta name=\"keywords\"\scontent=\")(.*?)(\" />)%sim', '$1' . $keywords . '$3', $headinclude);
$headinclude = preg_replace('%(<meta name=\"description\"\scontent=\")(.*?)(\" />)%sim', '$1' . $description . '$3', $headinclude);

eval('print_output("' . fetch_template($templatename) . '");');
