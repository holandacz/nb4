<?php

error_reporting(E_ALL & ~E_NOTICE);

if(!is_object($vbulletin->db))
{
	exit;
}


//###########################################
// START Function for writing the xml file 
//###########################################
Global $db,$vbulletin;
$bburl = $vbulletin->options['bburl'];
$xml_file = DIR."/markers.xml";
$id_file = @fopen($xml_file,"w+")
			  or die("Cannot open XML File");


$text_file = "<"."?"."xml version=\"1.0\" encoding=\"UTF-8\"". "?"."><markers>\n";
$text_file = $text_file."<!-- (c) 2006 StonyArc -->\n";


//build the SELECT string
$select_xml = TABLE_PREFIX . "googlemapme.mapid as mapid,".TABLE_PREFIX . "googlemapme.username as username,".TABLE_PREFIX . "googlemapme.userid as userid,".TABLE_PREFIX . "googlemapme.usergroupid as usergroupid,".TABLE_PREFIX . "googlemapme.lng_map as lng, ". TABLE_PREFIX ."googlemapme.lat_map as lat, ".TABLE_PREFIX."googlemapme.text_map as text_map,".TABLE_PREFIX."googlemapme.pimage as pimage,".TABLE_PREFIX."googlemapme.title_map as title_map";

//get the values
$vbulletin->db->query_first("SET NAMES 'utf8'");
$get_xml_markers = $vbulletin->db->query_read("SELECT ".$select_xml." FROM ".TABLE_PREFIX."googlemapme WHERE approve='1' AND lng_map<>'' AND lat_map<>''");



while ($get_xml_marker = $vbulletin->db->fetch_array($get_xml_markers))
{
	$userid = $get_xml_marker[userid];
	$usergroupid = $get_xml_marker['usergroupid'];


	$gettype = $vbulletin->db->query_first("SELECT googlemapmetype FROM " . TABLE_PREFIX . "usergroup WHERE usergroupid = $usergroupid");
	
	
	if ($get_xml_marker["pimage"]!='')
	{
	$pimage = $get_xml_marker["pimage"];

	}
	else
	{	
	$pimage = $bburl."/images/googlemapme/noimage.png";

	}

	
	
	$text_file .= "<marker mapid=\"".$get_xml_marker["mapid"]."\"  title_map=\"".$get_xml_marker["title_map"]."\" lng=\"".$get_xml_marker["lng"]."\" lat=\"".$get_xml_marker["lat"]."\" userid=\"".$get_xml_marker["userid"]."\" username=\"".preg_replace("/\n|\r\n|\r/", "",nl2br(addslashes(htmlspecialchars(trim($get_xml_marker["username"])))))."\" text_map=\"".preg_replace("/\n|\r\n|\r/", "", nl2br(addslashes(htmlspecialchars(trim($get_xml_marker["text_map"])))))."\" type=\"".$gettype['googlemapmetype']."\" pimage=\"".$pimage."\" />\n";

}

$text_file .= "\n</markers>";

$verif = @fwrite($id_file,$text_file)
		 or die("Cannot write to XML File");
$verif = @fclose($id_file)
		 or die("Cannot close XML file");
//###########################################
// END Function for writing the xml file 
//###########################################


log_cron_action('Cron Vbgooglemap ME Ran', $nextitem);
?>
