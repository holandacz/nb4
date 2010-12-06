<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', '_int');
define('INTERSPIRE_DB', 'noblood_out');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array(
);

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
);

// pre-cache templates used by specific actions
$actiontemplates = array(
);

$actiontemplates['getall'] =& $actiontemplates['none'];

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_misc.php');
require_once (DIR . '/includes/utf8_to_ascii/utf8_to_ascii.php');

$ini	= parse_ini_file('../m/config/config.ini', true);
// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// default action
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'update';
}

if ($_REQUEST['do'] == 'update')
{

	$hospitals = $db->query_read_slave("
		SELECT " . HOSPITAL_TABLE . ".*, IF(premium, 0, 1) as premium_first
		FROM " . TABLE_PREFIX . HOSPITAL_TABLE . "
		WHERE ($condition) AND type=1 AND publish = 1
		ORDER BY  premium_first, $sqlsort $sortorder $secondarysortsql
		LIMIT " . ($limitlower - 1) . ", $perpage
	");


$xml = '<xmlrequest>
	<username>admin</username>
	<usertoken>d467e49b221137215ebdab1ea4e046746de7d0ea</usertoken>
	<requesttype>subscribers</requesttype>
	<requestmethod>AddSubscriberToList</requestmethod>
	<details>
		<emailaddress>email@domain.com</emailaddress>
		<mailinglist>1</mailinglist>
		<format>html</format>
		<confirmed>yes</confirmed>
		<customfields>
			<item>
				<fieldid>1</fieldid>
				<value>John Smith</value>
			</item>
		</customfields>
	</details>
</xmlrequest>
';

$ch = curl_init('http://www.yourdomain.com/IEM/xml.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
$result = @curl_exec($ch);
if($result === false) {
	echo "Error performing request";
}
else {
	$xml_doc = simplexml_load_string($result);
	echo 'Status is ', $xml_doc->status, '<br/>';
	if ($xml_doc->status == 'SUCCESS') {
		echo 'Data is ', $xml_doc->data, '<br/>';
	} else {
		echo 'Error is ', $xml_doc->errormessage, '<br/>';
	}
}