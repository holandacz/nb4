<?php
/*======================================================================*\
|| #################################################################### ||
|| # VBGooglemap Member Edition 3.0.1 by StonyArc for 3.6.x       	  # ||
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is ?2000-2007 StonyArc		              # ||
|| # Email: admin@xboxlivenation.com or admin@stonyarc.com            # ||
|| # Copyright notice must remain intact						                  # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);
define('GET_EDIT_TEMPLATES', true);
define('THIS_SCRIPT', 'vbgooglemapme');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups

// pre-cache templates used by all actions
$globaltemplates = array(
'vbgooglemapme',
'vbgooglemapme_list_header',
'vbgooglemapme_list',
'vbgooglemapme_close_list',
'vbgooglemapme_display_map',
'vbgooglemapme_display_yourlocation',
'vbgooglemapme_set_yourlocation',
'vbgooglemapme_offline',
'vbgooglemapme_menavbar',
'vbgooglemapme_select_option',
'vbgooglemapme_showabout',
'vbgooglemapme_legend'
);
// get special data templates from the datastore
$specialtemplates = array(
	'vbgooglemapme'
);


// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once('./includes/functions_vbgooglemapme.php');
// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// ### vbgooglemapme by StonyArc ###########################################

global $vbulletin;


if (!($permissions['vbgmepermissions'] & $vbulletin->bf_ugp_vbgmepermissions['canviewvbgme']))
{
	print_no_permission();
}



//get the information from the datastore
$vbulletin->vbgooglemapme = unserialize($vbulletin->vbgooglemapme);
$setting =& $vbulletin->vbgooglemapme;
$zoomlevel = $vbulletin->input->clean_gpc('r', 'zoomlevel', TYPE_UINT);
$userid = $vbulletin->userinfo['userid'];
$usergroupid = $vbulletin->userinfo['usergroupid'];
$username = nl2br(addslashes(htmlspecialchars(trim($vbulletin->userinfo['username']))));
$bburl = $vbulletin->options['bburl'];
$maptype = $setting['googlemapme_maptype'];

	if  ($maptype == 1)
	{
		$map_type = "{mapTypes:[G_NORMAL_MAP,G_SATELLITE_MAP,G_HYBRID_MAP]}";
	}
	if  ($maptype == 2)
	{
		$map_type = "{mapTypes:[G_HYBRID_MAP,G_NORMAL_MAP,G_SATELLITE_MAP]}";
	}
	if  ($maptype == 3)
	{
		$map_type = "{mapTypes:[G_SATELLITE_MAP,G_NORMAL_MAP,G_HYBRID_MAP]}";
	}

$active= $setting['googlemapme_active'];
$autoapprove= $setting['googlemapme_autoapprove'];
$perpage = $setting['googlemapme_spots_perpage'];
$googlemapme_key = $setting['googlemapme_key'];
$googlemapme_smallmapcontrol = $setting['googlemapme_smallmapcontrol'];
$mapwidth = $setting['googlemapme_width']."px";
$mapheight = $setting['googlemapme_height']."px";
$zoomlevel = $setting['googlemapme_zoom'];
$linkzoomlevel = $setting['googlemapme_linkzoom'];
$dlat = $setting['googlemapme_lat'];
$dlng = $setting['googlemapme_lng'];
$gmtexttablewidth = $setting['googlemapme_balloon'];
$gmimagemax = ($gmtexttablewidth - 15);
$textchars = $setting['googlemapme_textchars'];
$textcharstitle = $setting['googlemapme_textcharstitle'];
$mouseoversupport = $setting['googlemapme_mouseoversupport'];

$teststring ="";

if ($googlemapme_smallmapcontrol == 1)
{
$smallmapcontrol = "
		map.addControl(new GOverviewMapControl(new GSize(150,150)));
		var overview = document.getElementById(\"memap_overview\");
		document.getElementById(\"memap\").appendChild(overview);";
}
if ($googlemapme_smallmapcontrol == 0)
{
$smallmapcontrol ='';
}


$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);

	if (!$pagenumber)
	{
	$pagenumber = 1;
	}


$vbulletin->input->clean_array_gpc('g', array(
 'lng' => TYPE_NUM,
 'lat' => TYPE_NUM,
 'zoom' => TYPE_UINT
));

if ( isset($HTTP_GET_VARS["lng"]) && !empty($HTTP_GET_VARS["lng"]) && isset($HTTP_GET_VARS["lat"]) && !empty($HTTP_GET_VARS["lat"]) )
{
  $dlng = $vbulletin->GPC['lng'];
  $dlat = $vbulletin->GPC['lat'];
}
if (isset($HTTP_GET_VARS["zoom"]) && !empty($HTTP_GET_VARS["zoom"])) {
  $zoomlevel = $vbulletin->GPC['zoom'];
}



$googlemescript="<script src=\"http://maps.google.com/maps?file=api&v=2&key=$googlemapme_key\" type=\"text/javascript\"></script>";

// Check the online/offline modus
if ($active <> 1)
{
eval('$vbgooglemapme_offline .= "' . fetch_template('vbgooglemapme_offline') . '";');
$navbits = construct_navbits(array('' => $vbphrase[googlemap]));
eval('$navbar = "' . fetch_template('navbar') . '";');

construct_forum_jump();

eval('print_output("' . fetch_template('vbgooglemapme') . '");');
exit;
}


if (!$_REQUEST['do'])
{
	$_REQUEST['do'] = 'showmain';
}

if ($_REQUEST['do'] == 'showmain')
{


$r = rand(0, 15000);
$gettypes= $db->query("SELECT title,googlemapmetype	FROM " . TABLE_PREFIX . "usergroup where showonvbgme = '1'");



	while ($gettype = $db->fetch_array($gettypes))
	{
			$gettype['title'] = $gettype['title'];
			$gettype['googlemapmetype'] = $gettype['googlemapmetype'];
			eval('$showcatlegend .= "' . fetch_template('vbgooglemapme_legend') . '";');

	}

if ($mouseoversupport == 0)
{

		$getlistenerinformation = "
		GEvent.addListener(marker, \"click\", function() {
		marker.openInfoWindowTabsHtml(infoTabs);
	    id = document.getElementById('username');
	    id.innerHTML = username;

		  });
		";

}
if ($mouseoversupport == 1)
{
	   $getlistenerinformation = "
	   GEvent.addListener(marker,\"mouseover\", function() {
	   marker.openInfoWindowTabsHtml(infoTabs);
  		document.getElementById('username').value= username;
	    id = document.getElementById('username');
	    id.innerHTML = username;

        });
	   ";

}




eval('$vbgooglemapme_display_map .= "' . fetch_template('vbgooglemapme_display_map') . '";');
}
if ($_REQUEST['do'] == 'updateconfirm')
{

if (!($permissions['vbgmepermissions'] & $vbulletin->bf_ugp_vbgmepermissions['caneditownvbgme']))
{
	print_no_permission();
}

	if ($_POST['deletemapentry'] == "on")
	{

	$db->query("DELETE FROM " . TABLE_PREFIX . "googlemapme WHERE userid='" . $userid . "'");

	CreateXMLme('markers.xml');
	$vbulletin->url = "./vbgooglemapme.php" . $vbulletin->session->vars['sessionurl'];
	eval(print_standard_redirect('redirect_vbgooglemapme', true, true));

  }
	else
	{

	$vbulletin->input->clean_array_gpc('p', array(
		'title_map' => TYPE_STR,
		'text_map' => TYPE_STR,
	));
	if ($vbulletin->GPC['title_map'] == '' )
	{
		eval(standard_error(fetch_error('title_map_empty')));
	}
	if ($vbulletin->GPC['text_map'] == '' )
	{
		eval(standard_error(fetch_error('text_map_empty')));
	}
	$vbulletin->input->clean_array_gpc('p', array(
		'title_map' => TYPE_STR,
		'text_map' => TYPE_STR,
		'mapid' => TYPE_INT,
		'lat_map' => TYPE_NUM,
		'lng_map' => TYPE_NUM,
		'muserid' => TYPE_INT,
		'musergroupid' => TYPE_INT,
		'musername' => TYPE_STR,
		'pimage' => TYPE_STR
	));


	$title_map = $db->escape_string(trim($vbulletin->GPC['title_map']));
	$text_map = $db->escape_string(trim($vbulletin->GPC['text_map']));
	$mapid = $db->escape_string($vbulletin->GPC['mapid']);
	$lat_map = $db->escape_string($vbulletin->GPC['lat_map']);
	$muserid =$db->escape_string($vbulletin->GPC['muserid']);
	$musername = $db->escape_string($vbulletin->GPC['musername']);
	$musergroupid = $db->escape_string($vbulletin->GPC['musergroupid']);
	$lng_map = $db->escape_string($vbulletin->GPC['lng_map']);
	$pimage = $db->escape_string(trim($vbulletin->GPC['pimage']));



		$db->query_write("
		UPDATE " . TABLE_PREFIX . "googlemapme SET
		userid ='" . $muserid . "',
		username='" . $musername . "',
		usergroupid='" . $musergroupid . "',
		title_map='" . $title_map . "',
		text_map='" . $text_map. "',
		lat_map='" . $lat_map. "',
		lng_map='" . $lng_map. "',
		pimage='" . $pimage. "'
		WHERE mapid = $mapid"
		);

	}
CreateXMLme('markers.xml');
$vbulletin->url = "./vbgooglemapme.php" . $vbulletin->session->vars['sessionurl'];
eval(print_standard_redirect('redirect_vbgooglemapme', true, true));

}

if ($_REQUEST['do'] == 'addelement')
{
if (!($permissions['vbgmepermissions'] & $vbulletin->bf_ugp_vbgmepermissions['canaddvbgme']))
{
	print_no_permission();
}
	// Get the user's ID
	if(empty($vbulletin->GPC['userid']))
	{
		// If we don't have a userid set, check to see if there is a username
		if($vbulletin->GPC['username'])
		{
			$userid = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username = '" . $db->escape_string($vbulletin->GPC['username']) . "'");
			$userid = $userid[userid];
		}
		else
		{
		// If there's not a username, send the user to their own page if they aren't a member
			if($vbulletin->userinfo['userid'])
			{
		  	 $userid=$vbulletin->userinfo['userid'];
			}
			else
			{
			// If they aren't a member, throw up an error page.
				eval(standard_error(fetch_error('invalid_page_specified')));
			}
		}
	}
	else
	{
	// When all else fails, get the userid from the url
		$userid = $vbulletin->GPC['userid'];
	}
	// Find the user's name and any other info we may want to use
	$userinfo = fetch_userinfo($userid);
	if(!$userinfo)
	{
  	eval(standard_error(fetch_error('invalid_page_specified')));
	}
	// Check if the user already has made an entry
	$checkuser = $db->query_read("SELECT userid FROM " . TABLE_PREFIX . "googlemapme WHERE userid = '" . $db->escape_string($vbulletin->userinfo['userid']) . "'");

	$check= $db->num_rows($checkuser);

	if($check=="0")
	{

	//action when no userid is present
	//redirect to insert template
	eval('$vbgooglemapme_set_yourlocation .= "' . fetch_template('vbgooglemapme_set_yourlocation') . '";');

	}
	else
	{

if (!($permissions['vbgmepermissions'] & $vbulletin->bf_ugp_vbgmepermissions['caneditownvbgme']))
{
	print_no_permission();
}


	$getinfo_me_list = $db->query_first("SELECT mapid,title_map,text_map,lat_map,lng_map,usergroupid,username,pimage,approve,userid FROM " . 		TABLE_PREFIX . "googlemapme WHERE userid = '" . $db->escape_string($vbulletin->userinfo['userid']) . "'");

	$muserid= $getinfo_me_list['userid'];
	$musername= $getinfo_me_list['username'];
	$musergroupid= $getinfo_me_list['usergroupid'];
	$mapid= $getinfo_me_list['mapid'];
	$title_map =  $getinfo_me_list['title_map'];
	$text_map = $getinfo_me_list['text_map'];
	$lat_map = $getinfo_me_list['lat_map'];
	$lng_map= $getinfo_me_list['lng_map'];
	$username= $getinfo_me_list['username'];
	$userid= $getinfo_me_list['userid'];
	$pimage= $getinfo_me_list['pimage'];

    eval('$vbgooglemapme_display_yourlocation .= "' . fetch_template('vbgooglemapme_display_yourlocation') . '";');
	}
}

if ($_REQUEST['do'] == 'deleteelement')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'mapid' => TYPE_INT
	));

$db->query_write("delete from " . TABLE_PREFIX . "googlemapme where mapid = '" . $db->escape_string($vbulletin->GPC['mapid']) . "'");

CreateXMLme('markers.xml');

$vbulletin->url = "./vbgooglemapme.php" . $vbulletin->session->vars['sessionurl'];
eval(print_standard_redirect('redirect_vbgooglemapme_delete', true, true));

}
if ($_REQUEST['do'] == 'deleteconfirm')
{

	$vbulletin->input->clean_array_gpc('r', array(
		'mapid' => TYPE_INT
	));

	$mapid = $db->escape_string($vbulletin->GPC['mapid']);
	eval('$vbgooglemapme_delete_confirm .= "' . fetch_template('vbgooglemapme_delete_confirm') . '";');


}

if ($_REQUEST['do'] == 'insertconfirm')
{

	$vbulletin->input->clean_array_gpc('p', array(
		'title_map' => TYPE_STR,
		'text_map' => TYPE_STR
	));

	if ($vbulletin->GPC['title_map'] == '' )
	{
		eval(standard_error(fetch_error('title_map_empty')));
	}
	if ($vbulletin->GPC['text_map'] == '' )
	{
		eval(standard_error(fetch_error('text_map_empty')));
	}


	$vbulletin->input->clean_array_gpc('p', array(
		'title_map' => TYPE_STR,
		'text_map' => TYPE_STR,
		'lat_map' => TYPE_NUM,
		'lng_map' => TYPE_NUM,
		'pimage' => TYPE_STR
	));


	$title_map = $db->escape_string(trim($vbulletin->GPC['title_map']));
	$text_map = $db->escape_string(trim($vbulletin->GPC['text_map']));
	$lat_map = $db->escape_string($vbulletin->GPC['lat_map']);
	$lng_map = $db->escape_string($vbulletin->GPC['lng_map']);
	$pimage = $db->escape_string($vbulletin->GPC['pimage']);


	if ($autoapprove == 1)
	{
	$approve = '1';
	}
	else
	{
	$approve = '0';
	}
	$creation = time();



$db->query_write("
INSERT INTO " . TABLE_PREFIX . "googlemapme
(userid,username,usergroupid,title_map,text_map,lat_map,lng_map,pimage,approve) VALUES ('" . $userid . "','" . $username . "','" . $usergroupid . "','" . $title_map . "','" . $text_map. "','" . $lat_map. "','" . $lng_map. "','" . $pimage. "',
'" . $approve. "')
");


CreateXMLme('markers.xml');
$vbulletin->url = "./vbgooglemapme.php" . $vbulletin->session->vars['sessionurl'];
eval(print_standard_redirect('redirect_vbgooglemapme', true, true));

}
if ($_REQUEST['do'] == 'deleteelement')
{

	$vbulletin->input->clean_array_gpc('r', array(
		'mapid' => TYPE_INT
	));


$db->query_write("delete from " . TABLE_PREFIX . "googlemapme where mapid = '" . $db->escape_string($vbulletin->GPC['mapid']) . "'");


CreateXMLme('markers.xml');

$vbulletin->url = "./vbgooglemapme.php" . $vbulletin->session->vars['sessionurl'];
eval(print_standard_redirect('redirect_vbgooglemapme_delete', true, true));

}


if ($_REQUEST['do'] == 'list')
{
$getcount = $db->query_first("SELECT count(userid) AS counterme FROM " . TABLE_PREFIX . "googlemapme where approve ='1'");

	$totalme = $getcount['counterme'];
	if ($_REQUEST['page']){
	$pagenumber =$_REQUEST['page'];
	}
	else
	{
	$pagenumber =1;
	}

	$limitlower = ($pagenumber - 1) * $perpage;
	$limitupper = ($pagenumber) * $perpage;

	if ($limitupper > $totalme)
	{
		$limitupper = $totalme;
		if ($limitlower > $totalme)
		{
			$limitlower = ($totalme - $perpage) - 1;
		}
	}
	if ($limitlower < 0)
	{
		$limitlower = 0;
	}


	$getinfo_me = $db->query_read("SELECT mapid,usergroupid,title_map,text_map,username,lat_map,lng_map,pimage,approve,userid FROM " . TABLE_PREFIX . "googlemapme where approve = '1' order by username asc LIMIT $limitlower, $perpage");

while ($getinfo_me_list = $db->fetch_array($getinfo_me))
{
	$getinfo_me_list[mapid] =  $getinfo_me_list['mapid'];
  $usergroupid  =$db->escape_string($getinfo_me_list['usergroupid']);
  //$usergroupid = $getinfo_me_list[usergroupid];
  $getug = $db->query_first("SELECT title,googlemapmetype FROM " . TABLE_PREFIX . "usergroup WHERE usergroupid = $usergroupid");
	$getinfo_me_list[usergrouptitle]=$getug['title'];
 	$getinfo_me_list[type] = $getug['googlemapmetype'];
 	$getinfo_me_list[title_map] =  $getinfo_me_list['title_map'];
	$getinfo_me_list[text_map] = $getinfo_me_list['text_map'];
	$getinfo_me_list[lat_map] = $getinfo_me_list['lat_map'];
	$getinfo_me_list[lng_map]= $getinfo_me_list['lng_map'];
	$getinfo_me_list[username]= $getinfo_me_list['username'];
	$getinfo_me_list[userid]= $getinfo_me_list['userid'];



eval('$vbgooglemapme_list .= "' . fetch_template('vbgooglemapme_list') . '";');

}

$pagenav = construct_page_nav($pagenumber, $perpage, $totalme, 'vbgooglemapme.php?do=list'.$vbulletin->session->vars['sessionurl'] .(!empty($vbulletin->GPC['perpage']) ? "&amp;pp=$perpage" : ""));
eval('$vbgooglemapme_list_header .= "' . fetch_template('vbgooglemapme_list_header') . '";');
eval('$vbgooglemapme_close_list .= "' . fetch_template('vbgooglemapme_close_list') . '";');
}



if ($_REQUEST['do'] == 'editelement')
{

if (!($permissions['vbgmepermissions'] & $vbulletin->bf_ugp_vbgmepermissions['caneditvbgme']))
{
	print_no_permission();
}

	$vbulletin->input->clean_array_gpc('r', array(
		'mapid' => TYPE_INT
	));


$getinfo_me_list = $db->query_first("SELECT mapid,title_map,text_map,usergroupid,username,lat_map,lng_map,pimage,approve,userid FROM " . TABLE_PREFIX . "googlemapme where mapid = '" . $db->escape_string($vbulletin->GPC['mapid']) . "'");



	$mapid =  $getinfo_me_list['mapid'];
 	$title_map =  $getinfo_me_list['title_map'];
	$text_map = $getinfo_me_list['text_map'];
	$lat_map = $getinfo_me_list['lat_map'];
	$lng_map= $getinfo_me_list['lng_map'];
	$username= $getinfo_me_list['username'];
	$muserid= $getinfo_me_list['userid'];
	$musername= $getinfo_me_list['username'];
	$musergroupid= $getinfo_me_list['usergroupid'];

	$pimage= $getinfo_me_list['pimage'];

	$foruser= "- editing entry of user ".$username;



eval('$vbgooglemapme_display_yourlocation .= "' . fetch_template('vbgooglemapme_display_yourlocation') . '";');
}


if ($_REQUEST['do'] == 'showabout')
{
eval('$vbgooglemapme_showabout .= "' . fetch_template('vbgooglemapme_showabout') . '";');


}


eval('$menavbar .= "' . fetch_template('vbgooglemapme_menavbar') . '";');

$navbits = construct_navbits(array('' => $vbphrase[vbgooglemapme]));
eval('$navbar = "' . fetch_template('navbar') . '";');

construct_forum_jump();

eval('print_output("' . fetch_template('vbgooglemapme') . '");');

// ########################################################################
// ######################### END MAIN SCRIPT ##############################
// ########################################################################
?>
