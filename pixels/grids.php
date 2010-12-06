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
if(eregi(basename(__FILE__), $HTTP_SERVER_VARS[REQUEST_URI]))
    die ("You can't access this file directly! Please go to the startpage!");

$VERSIONS[basename(__FILE__)] = "3.03 PRO";
$TMP['%[GRIDS]%'] = '';

//------------------------------------------------------------------------------------------------------------------
// Jobs
if(!$_GET['gridsearch'])
    @include_once('jobs.php');
//------------------------------------------------------------------------------------------------------------------


while(list($gkey,$gval) = each($GRID)) {
    if($gval['active'] && !$gval['plugin']) {
        $gwidth   = $gval['grid_type']  ? $gridsizes[$gval['grid_type']]['x'] : (int)($gval['x']*$gval['blocksize_x']);
        $gheight  = $gval['grid_type']  ? $gridsizes[$gval['grid_type']]['y'] : (int)($gval['y']*$gval['blocksize_y']);
        $x_plus   = $gval['grid_type']  ? $gridsizes[$gval['grid_type']]['x+'] : 0;
        $y_plus   = $gval['grid_type']  ? $gridsizes[$gval['grid_type']]['y+'] : 0;
        $gridfile = 'grids/grid_'.$gval['gridid'].'.'.$iformat[$gval['image_format']];
        if($_GET['gridsearch']) {
            $gridfile = 'sr.php?gr='.$gval['gridid'].'&f='.$_GET['gridsearch'].'&x='.time();
            $gval['hoverimage'] = $gval['animated'] = false;
        }

        ob_start();

        if($gval['hoverimage'] || $gval['animated']) print '<div id="ihover" style="position:relative;width:'.$gwidth.';height:'.$gheight.'">';

        print '<table width='.$gwidth.' height='.$gheight.' style="background:url('.$designpath.'loading.gif) no-repeat;background-position:center 10;">';
        print '<tr><td><map name="grid_'.$gval['gridid'].'">';

        // Jobs: Zufallspixel anzeigen ---------------
        if(is_array($SHOW_JOB[$gval['gridid']])) print '<div id="jobs" style="position:relative;width:'.$gwidth.';height:'.$gheight.'">';
        // -------------------------------------------

        if($_GET['gridsearch']) {
            makemap(false,false,$gval['gridid'],true,DB_array("SELECT userid FROM ".$dbprefix."user WHERE (url LIKE '%".mysql_real_escape_string(stripslashes($_GET['gridsearch']))."%' OR title LIKE '%".mysql_real_escape_string(stripslashes($_GET['gridsearch']))."%') AND submit IS NOT NULL AND gridid='".$gval['gridid']."'",'+'),true);
        } else {
            @include('grids/area_'.$gval['gridid'].'.htm');
        }
        print '</map>';
        if($_GET['zoom']) {
            print '<div id="zoom" onmouseover="zoom_on(event,'.$gwidth.','.$gheight.',\''.$gridfile.'?x='.@filemtime($gridfile).'\');" onmousemove="zoom_move(event);" onmouseout="zoom_off();">';
            print '<img src="'.$gridfile.'?x='.@filemtime($gridfile).'" style="position:relative;z-index:0;padding:0;margin:0;border:0" width='.$gwidth.' height='.$gheight.' usemap="#grid_'.$gval['gridid'].'" /></div>';
        } else {
            print '<img src="'.$gridfile.'?x='.@filemtime($gridfile).'" width='.$gwidth.' height='.$gheight.' usemap="#grid_'.$gval['gridid'].'" '.($gval['buy_on_click'] && !$gval['dontbuy'] ? ' onClick="if(!tooo) location.href=\'getp.php?gr='.$gval['gridid'].$trackpage_.'\'" style="cursor:hand"' : '').'>';
        }

        // Jobs: Zufallspixel anzeigen ---------------
        if(is_array($SHOW_JOB[$gval['gridid']])) print $SHOW_JOB[$gval['gridid']]['url'].'<img src="'.$designpath.'marked_pixel.gif" style="width:'.$gval['blocksize_x'].';height:'.$gval['blocksize_y'].';position:absolute;left:'.((fsubstr($SHOW_JOB[$gval['gridid']]['field']-1,-2)*$gval['blocksize_x'])+$x_plus).'px;top:'.(((int)(($SHOW_JOB[$gval['gridid']]['field']-1)/100)*$gval['blocksize_y'])+$y_plus).'px;z-index:0"></a></div>';
        // -------------------------------------------

        print '</td></tr></table>';

        if($gval['hoverimage'] || $gval['animated'] || is_array($SHOW_JOB[$gval['gridid']])) print '</div>';    // Jobs

        $TMP['%[GRIDS]%'] .= $TMP['%[GRID_'.$gval['gridid'].']%'] = ob_get_contents();
        ob_end_clean();

        if($gval['show_box'] == !$_GET['zoom']) $include_tooltip = true;
        if($gval['popup'])                      $include_popup   = true;
    }
}

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


if(@file_exists($LANGDIR.'header_grids_page'.$PAGE_ID.'.htm')) {
    print template($LANGDIR.'header_grids_page'.$PAGE_ID.'.htm',$TMP);
} else {
    print template($LANGDIR.'header_grids.htm',$TMP);
}


?>

