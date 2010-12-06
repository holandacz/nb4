<script language="javascript">
   window.setInterval("ReloadFrame();", 5000);
   
   function ReloadFrame() {
 	 document.frames["MyFrame1"].location.reload();
   }
 </script>
<?php

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'ad_navbarBelow'); // change this depending on your filename

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array(

);

// get special data templates from the datastore
$specialtemplates = array(
    
);

// pre-cache templates used by all actions
$globaltemplates = array(
    'ad_navbarBelow',
tarik
);
 
// pre-cache templates used by specific actions
$actiontemplates = array(

);

// ######################### REQUIRE BACK-END ############################
include_once('./global.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
{
echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{$ibforums->lang['popuptitle']}{$game['gname']}</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="refresh" content="30" >
<style type="text/css" media="all"> 
<!-- 
body { 
   margin-left: 0px; 
   margin-top: 0px; 
   margin-right: 0px; 
   margin-bottom: 0px; 
} 
object, embed {
	width: 100%;
	height: 100%;
}
--> 
</style>
</head>
<center>
$navbar_code
</center>
EOF;
exit();
}
$navbits = array();
$navbits[$parent] = 'ad_navbarBelow Page';

$navbits = construct_navbits($navbits);
eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('ad_navbarBelow') . '");');
$navbar_code

?> 
