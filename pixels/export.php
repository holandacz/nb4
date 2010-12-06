<?php
/******************************************************************************************
*   Million Pixel Script (R)
*   (C) 2005-2006 by texmedia.de, all rights reserved.
*   "Million Pixel Script" and "Pixel Script" is a registered Trademark of texmedia.
*
*   This script code is protected by international Copyright Law.
*   Any violations of copyright will be dealt with seriously,
*   and offenders will be prosecuted to the fullest extent of the law.
*
*   This program is not for free, you have to buy a copy-license for your domain.
*   This copyright notice and the header above have to remain intact.
*   You do not have the permission to sell the code or parts of this code or chanced
*   parts of this code for this program.
*   This program is distributed "as is" and without warranty of any
*   kind, either express or implied.
*
*   Please check
*   http://www.texmedia.de
*   for Bugfixes, Updates and Support.
******************************************************************************************/
error_reporting(0);
if((int)$_REQUEST['u']) {
    header("Location: index.php?u=".(int)$_REQUEST['u']);
    exit;
}
if(!(int)$_REQUEST['gr']) exit;
$getprocess = true;
@ini_set('include_path',".");
include_once('incs/functions.php');
$VERSIONS[basename(__FILE__)] = "3.04 PRO";

//------------------------------------------------------------------------------------------------------------------
// Jobs
if(!$_GET['gridsearch'])
    @include_once('jobs.php');
//------------------------------------------------------------------------------------------------------------------

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$CONFIG['website_title']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$LANG_ACTIVE[$lang]['charset']?>" />
<link rel="stylesheet" type="text/css" href="<?=$designpath?>style_plugin.css" />
</head>
<body style="margin:0">
<?PHP
if($gval = DB_query("SELECT * FROM ".$dbprefix."grids WHERE gridid='".(int)$_REQUEST['gr']."' LIMIT 1",'*')) {
    if($gval['active'] && $gval['gridid']==(int)$_REQUEST['gr']) {
        $gwidth     = ($gval['grid_type']) ? $gridsizes[$gval['grid_type']]['x'] : (int)($gval['x']*$gval['blocksize_x']);
        $gheight    = ($gval['grid_type']) ? $gridsizes[$gval['grid_type']]['y'] : (int)($gval['y']*$gval['blocksize_y']);
        $gridfile   = 'grids/grid_'.$gval['gridid'].'.'.$iformat[$gval['image_format']];
        if($_GET['gridsearch']) {
            $gridfile = 'sr.php?gr='.$gval['gridid'].'&f='.$_GET['gridsearch'].'&x='.time();
            $gval['hoverimage'] = $gval['animated'] = false;
        }

        if($gval['hoverimage'] || $gval['animated']) print '<div id="ihover" style="position:relative;width:'.$gwidth.';height:'.$gheight.'">';

        // Jobs: Zufallspixel anzeigen ---------------
        if(is_array($SHOW_JOB[$gval['gridid']])) print '<div id="jobs" style="position:relative;width:'.$gwidth.';height:'.$gheight.'">';
        // -------------------------------------------

        print '<map name="grid_'.$gval['gridid'].'">';
        if($_GET['gridsearch']) {
            makemap(false,false,$gval['gridid'],true,DB_array("SELECT userid FROM ".$dbprefix."user WHERE (url LIKE '%".mysql_real_escape_string(stripslashes($_GET['gridsearch']))."%' OR title LIKE '%".mysql_real_escape_string(stripslashes($_GET['gridsearch']))."%') AND submit IS NOT NULL AND gridid='".$gval['gridid']."'",'+'),true);
        } else {
            print template('grids/area_'.$gval['gridid'].'.htm');
        }
        print '</map>';
        if($_GET['zoom']) {
            print '<div id="zoom" onmouseover="zoom_on(event,'.$gwidth.','.$gheight.',\''.$gridfile.'?x='.@filemtime($gridfile).'\');" onmousemove="zoom_move(event);" onmouseout="zoom_off();">';
            print '<img src="'.$gridfile.'?x='.@filemtime($gridfile).'" style="position:relative;z-index:0;padding:0;margin:0;border:0" width='.$gwidth.' height='.$gheight.' usemap="#grid_'.$gval['gridid'].'" /></div>';
        } else {
            print '<img src="'.$gridfile.'?x='.@filemtime($gridfile).'" width='.$gwidth.' height='.$gheight.' usemap="#grid_'.$gval['gridid'].'" '.($gval['buy_on_click'] && !$gval['dontbuy'] ? ' onClick="if(!tooo) parent.location.href=\'getp.php?gr='.$gval['gridid'].'&pa='.$gval['page_id'].'\'" style="cursor:hand"' : '').'>';
        }

        // Jobs: Zufallspixel anzeigen ---------------
        if(is_array($SHOW_JOB[$gval['gridid']])) print $SHOW_JOB[$gval['gridid']]['url'].'<img src="'.$designpath.'marked_pixel.gif" style="width:'.$gval['blocksize_x'].';height:'.$gval['blocksize_y'].';position:absolute;left:'.((substr($SHOW_JOB[$gval['gridid']]['field']-1,-2)*$gval['blocksize_x'])).'px;top:'.(((int)(($SHOW_JOB[$gval['gridid']]['field']-1)/100)*$gval['blocksize_y'])).'px;z-index:0"></a></div>';
        // -------------------------------------------

        if($gval['hoverimage'] || $gval['animated'] || is_array($SHOW_JOB[$gval['gridid']])) print '</div>';    // Jobs

        if($gval['show_box'] == !$_GET['zoom']) $include_tooltip = true;
        if($gval['popup'])                      $include_popup   = true;
    }
}
if($include_tooltip) print '<script language="JavaScript" type="text/javascript" src="tooltip.js"></script>';
if($_GET['zoom'])    print '<script type="text/javascript" src="tjpzoom.js"></script>';


// Jobs: Popups ------------------------------
if(!empty($SHOW_POPUP)) {
    while(list(,$showpopup) = each($SHOW_POPUP)) {
        if(in_array($showpopup['gridid'],$gridids)) {
            $popupID++;
            $popupurl = $showpopup['url'];
            $POPUP_NOSCROLLING = $showpopup['scroll'];
            @include('popup.php');
        }
    }
}

if($include_popup) print '<script type="text/javascript" src="popup.js"></script>';

?>
</body>
</head>
