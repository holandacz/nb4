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
@ini_set('include_path',".");
include_once('incs/functions.php');
$VERSIONS[basename(__FILE__)] = "3.1 PRO";
$filenamenr = basename(__FILE__);
include_once('header.php');

switch ($_GET['sort']) {
    case '1':
        $sort = 'regdat,kaesten DESC';
        $sa   = 'a';
        break;
    case '1a':
        $sort = 'regdat DESC,kaesten DESC';
        $sa   = '';
        break;
    case '2':
        $sort = 'url,title,kaesten DESC';
        $sa   = 'a';
        break;
    case '2a':
        $sort = 'url DESC,title DESC,kaesten DESC';
        $sa   = '';
        break;
    case '3':
        $sa   = 'a';
        $sort = 'kaesten';
        break;
    default:
        $sa   = '';
        $sort = 'kaesten DESC';
}

if($_GET['f']) $find = "(url LIKE '%".mysql_real_escape_string($_GET['f'])."%' OR title LIKE '%".mysql_real_escape_string($_GET['f'])."%') AND ";

if($_GET['global']) $IN_GRIDS = '';

$data1 = DB_array("SELECT url,title,bildext,regdat,kaesten,t1.userid,target,new_window,popup,popup_height,popup_width,real_url,blocksize_x,blocksize_y FROM ".$dbprefix."user t1 LEFT JOIN ".$dbprefix."grids t2 ON(t1.gridid=t2.gridid) WHERE $find submit IS NOT NULL ".str_replace('gridid','t2.gridid',$IN_GRIDS)." ORDER BY ".$sort,'*');

$TMP['%[CONTENT]%'] = '';

if(count($data1)) {
    while(list(,$d) = each($data1)) {
        $href  = !$d['real_url'] || empty($d['url']) ? 'index.php?u='.$d['userid'] : $d['url'];
        if(empty($d['target'])) $blank = $d['new_window'] ? ' target="_blank"' : '';
        else                    $blank = ' target="'.htmlspecialchars($d['target'],ENT_QUOTES).'"';
        if($d['popup'] && empty($d['url'])) {
            $href  = 'javascript:P(\''.$href.'\',\''.$d['userid'].'\',\'sr\','.(int)$d['popup_width'].','.(int)$d['popup_height'].')';
            $blank = '';
        }
        $onClick = $d['real_url'] && !empty($d['url']) ? ' onClick="window.open(\'index.php?u='.$d['userid'].'\',\''.htmlspecialchars($d['target'],ENT_QUOTES).'\')"' : '';
        $d['anzeige'] = $d['title']!='' ? $d['title'] : $d['url'];
        $TMP['%[CONTENT]%'] .= '<tr><td style="padding:4" align=center'.$col.'>'.date($CONFIG['date_format'],strtotime($d['regdat'])+(3600*$CONFIG['timezone'])).'</td><td align=center'.$col.'><img src="sp.php?u='.$d['userid'].'" width="40"></td><td'.$col.' style="padding:1 10"><b><a href="'.$href.'" '.$blank.$onClick.'>'.htmlspecialchars(stripslashes($d['anzeige'])).'</a></b></td><td style="padding:0 10" align=right'.$col.'>'.($d['kaesten']*$d['blocksize_y']*$d['blocksize_x']).'</td></tr>';
        if($d['popup']) $include_popup   = true;
    }
}

$TMP['%[SEARCHFORM]%'] = '
    <form method="GET" action="" name="find">
    <input type="hidden" name="sa" value="'.$sa.'">
    <input type="hidden" name="pa" value="'.$_REQUEST['pa'].'">
    <input type="hidden" name="sort" value="'.$_GET['sort'].'">
    <img src="'.$designpath.'search.gif" align="absmiddle">
    <input type="text" name="f" style="width:200" value="'.htmlspecialchars(stripslashes($_GET['f'])).'">
    <input type="submit" value=" '.$_SP[38].' ">
    </form>';

$TMP['%[LINKDATUM]%']   = '<a href="pixellist.php?sort=1'.$sa.'&f='.htmlspecialchars(stripslashes($_GET['f'])).'" class=black>';
$TMP['%[LINKWEBSITE]%'] = '<a href="pixellist.php?sort=2'.$sa.'&f='.htmlspecialchars(stripslashes($_GET['f'])).'" class=black>';
$TMP['%[LINKPIXEL]%']   = '<a href="pixellist.php?sort=3'.$sa.'&f='.htmlspecialchars(stripslashes($_GET['f'])).'" class=black>';

print template($LANGDIR.'pixellist.htm',$TMP);

include_once('footer.php');
?>
