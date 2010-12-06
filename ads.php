<?php

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'ads'); // change this depending on your filename

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array(

);

// get special data templates from the datastore
$specialtemplates = array(
    
);

// pre-cache templates used by all actions
$globaltemplates = array(
    'ads',
);

// pre-cache templates used by specific actions
$actiontemplates = array(

);

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$navbits = array();
$navbits[$parent] = 'ads Page';

$navbits = construct_navbits($navbits);
eval('$navbar = "' . fetch_template('navbar') . '";');
eval('print_output("' . fetch_template('ad_full_page') . '");');

?> 