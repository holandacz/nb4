<?php
/**
 * GoldBrick Media Manager
 * For Media Options and uploads in posts
 * 
 * @version     	$Revision: 109 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-29 00:03:13 -0700 (Mon, 29 Oct 2007) $
 */
error_reporting(E_ALL ^ E_NOTICE);

require('.././global.php');


switch ($_REQUEST['do'])
{
	case "submit":

	break;

	case "upload":
		echo "holder";

	break;

	case 'uploaders':

	break;

	default:
    	//include_once '../krumo/class.krumo.php';
		eval('print_output("' . fetch_template('gb_media_form'). '");');
}
?>