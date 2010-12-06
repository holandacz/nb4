<?php
/*
Page:           db.php
Created:        Aug 2006
Last Mod:       Mar 18 2007
This page handles the database update if the user
does NOT have Javascript enabled.	
--------------------------------------------------------- 
ryan masuga, masugadesign.com
ryan@masugadesign.com 
Licensed under a Creative Commons Attribution 3.0 License.
http://creativecommons.org/licenses/by/3.0/
See readme.txt for full credit details.
--------------------------------------------------------- */
error_reporting(E_ALL);
header("Cache-Control: no-cache");
header("Pragma: nocache");

//require_once('./../global.php');
include_once './../includes/krumo/class.krumo.php';
global $vbulletin;
//getting the values
$vote_sent = preg_replace("/[^0-9]/","",$_REQUEST['j']);
$id_sent = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['q']);
$ip_num = preg_replace("/[^0-9\.]/","",$_REQUEST['t']);
$units = preg_replace("/[^0-9]/","",$_REQUEST['c']);
krumo(array_keys(get_defined_vars()), $_REQUEST, $referer, $vbulletin);
$userid = $vbulletin->userinfo['userid'];
$referer  = $_SERVER['HTTP_REFERER'];

if ($vote_sent > $units) die("Sorry, vote appears to be invalid."); // kill the script because normal users will never see this.

//connecting to the database to get some information
$query = $vbulletin->db->query_read("SELECT total_votes, total_value, userids FROM " . TABLE_PREFIX . "anymedia_rating WHERE id='$id_sent' ")or die(" Error: ".mysql_error());
$numbers = $vbulletin->db->fetch_array($query);
//$checkIP = unserialize($numbers['userids']);
$count = $numbers['total_votes']; //how many votes total
$current_rating = $numbers['total_value']; //total number of rating added together and stored
$sum = $vote_sent+$current_rating; // add together the current vote value and the total vote value
$tense = ($count==1) ? "vote" : "votes"; //plural form votes/vote

// checking to see if the first vote has been tallied
// or increment the current number of votes
($sum==0 ? $added=0 : $added=$count+1);

// if it is an array i.e. already has entries the push in another value
//((is_array($checkIP)) ? array_push($checkIP,$ip_num) : $checkIP=array($ip_num));
//$insertip=serialize($checkIP);

//IP check when voting
$voted=mysql_num_rows($vbulletin->db->query_read("SELECT userids FROM " . TABLE_PREFIX . "anymedia_rating WHERE userids=$userid AND id=$id"));
if(!$voted)	//if the user hasn't yet voted, then vote normally...
{
	if (($vote_sent >= 1 && $vote_sent <= $units)) { // keep votes within range
		$update = "UPDATE " . TABLE_PREFIX . "anymedia_rating SET total_votes='".$added."', total_value='".$sum."', userids='".$userid."' WHERE id='$id_sent'";
		$result = $vbulletin->db->query_read($update);		
	}
	header("Location: $referer"); // go back to the page we came from 
	krumo(array_keys(get_defined_vars()), $result, $update, $header, $added, $sup, $userid);
	exit;
} //end for the "if(!$voted)"
?>