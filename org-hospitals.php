<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_misc.php');
require_once (DIR . '/includes/utf8_to_ascii/utf8_to_ascii.php');
require_once('_lib/ads.php');
$ads		= new ads();

$ini	= parse_ini_file('../m/config/config.ini', true);
$xchg_url = $ini['default']['dir.xchg'];

// default action
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'getall';
}

if ($_REQUEST['do'] == 'getall')
{
	$gvdb = $ini['default'];
	$conn = mysql_connect($gvdb['db.gv.host'], $gvdb['db.gv.username'], $gvdb['db.gv.password']);
	$database = $gvdb['db.gv.dbname'];
	mysql_select_db($database, $conn) or die ("Database not found.");

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
WHERE ((FIND_IN_SET('hospital',`c`.`nb_tags`) AND FIND_IN_SET('publish',`c`.`nb_tags`)))
ORDER BY  premium_first DESC, companyname asc
    ") or die("Failed Query");












    $xchg_url = "http://prm.xchg.com";
    $nb_url = "http://noblood.org/";
    $nb_profile_url = "http://noblood.org/member.php?u=";
    $nb_user_admin_url = "http://noblood.org/moderator.php?do=useroptions&u=";
	$nb_invoices = "C:/Users/Larry/My Dropbox/org/NoBlood/invoices/";
	while ($h = mysql_fetch_assoc($hospitals))
	{
        echo '** ';
        echo $h['name'] . ' ';
        echo $h['country'] . ' ';
        echo $h['state'] . ' ';
        echo $h['city'] . ' ';
        echo  '[[' . $nb_url . '/hospital.php?id=' . $h['id'] .  '][HOSP]] ';
        if ($h['premium']){
            echo '| PREM ';
        }
        echo '<br />';
        echo '*** Details: ';
        echo $h['department'] . ' ' . $h['tel1'] . ' ' . $h['tel2'] . ' ';
        echo  '| [[' . $h['url'] . '][WEB]] ';
        echo  '| [[' . $h['mapurl'] . '][MAP]] ';

        echo '<br />';

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
        WHERE parent_id = '" . $h['id'] . "' && publish
        ORDER BY weight, lastname
        ");

        while ($contact = mysql_fetch_assoc($contacts))
        {
            echo '*** ' . $contact['fullname'] . ' ' . $contact['jobtitle'] . ' ';
            echo '[[' . $xchg_url . '/contacts/' . $contact['id'] . '][XCHG]] ';
            echo '[[' . $xchg_url . '/contacts/' . $contact['id'] . '/edit][ED]] ';
            echo '| [[' . $nb_profile_url . $contact['userid'] . '][NB_Profile]] ';
            echo '[[' . $nb_user_admin_url . $contact['userid'] . '][ED]] ' . $contact['phones'] . '<br />';
        }
        echo '*** Accounting <br />';
        echo '[[file:' . $nb_invoices . '][invoice]]<br />';
        echo '*** Log';
        echo '<br /><br />';

	}  // end while
}
