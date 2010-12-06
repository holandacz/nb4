<?php
/* ====================================================================== /*
  GTSEARCH: GOOGLE ™  CUSTOM SEARCH ENGINE v 1.0.0
  -------------------------------------------------------------------
  Copyright ©2005 - 2007, GO-TOTAL, LLC. All Rights Reserved.
  Copyright ©2000-2005 Jelsoft Enterprises Ltd. All Rights Reserved.
  To be used with vBulletin 3.6.0+.
  This file may not be redistributed in whole or significant part.
   -------------------------------------------------------------------
  http://mygtblog.org | http://www.google.com/coop/docs/cse/tos.html
/* ====================================================================== */

/* ==========[ SET PHP ENVIRONMENT ]===================================== */
error_reporting(E_ALL & ~E_NOTICE);

/* ==========[ DEFINE IMPORTANT CONSTANTS ]============================== */
define('THIS_SCRIPT', 'gtsearch');

/* ==========[ PRE-CACHE TEMPLATES AND DATA ]============================ */
// get special phrase groups
$phrasegroups = array('search');

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
  'GTSEARCH_GOOGLE',
  );

// pre-cache templates used by specific actions
$actiontemplates = array();

/* ==========[ REQUIRE BACK-END ]======================================== */
require_once('./global.php');   

/* ====================================================================== */
/* ==========[ START MAIN SCRIPT ]======================================= */
/* ====================================================================== */
if (!$vbulletin->options['gtsearch_enable'] OR !$vbulletin->options['gtsearch_search_box_code'])
{
  print_no_permission(); 
}

$vbulletin->input->clean_array_gpc('r', array( 
  'q'  => TYPE_NOHTML,
  'sa' => TYPE_NOHTML,
));   
   
$gtgoogle = array();   
$gtgoogle['query'] = $vbulletin->GPC['q'];
$gtgoogle['qencode'] = urlencode($gtgoogle['query']);           
      
if ($vbulletin->GPC['sa'] AND empty($gtgoogle['query']))
{
  $show['errors'] = true; 
}

$navbits[] = $gtgoogle['query'] ? construct_phrase($vbphrase['search_for_x'], $gtgoogle['query']) : $vbphrase['errors_occured_with_search'];
 
/* ==========[ PRINT PAGE ]============================================== */  
// print navbar
$navbits = construct_navbits($navbits);   
eval('$navbar = "' . fetch_template('navbar') . '";');
// print shell
eval('print_output("' . fetch_template('GTSEARCH_GOOGLE') . '");');    
?>