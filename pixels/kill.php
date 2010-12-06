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
$VERSIONS[basename(__FILE__)] = "3.01 PRO";

if(eregi(basename(__FILE__), $HTTP_SERVER_VARS[REQUEST_URI]))
    die ("You can't access this file directly! Please go to the startpage!");


if($_POST['kill'] && $_POST['i'] && $_POST['k']) {
    if($killed_data = DB_Query("SELECT email,hits,gridid FROM ".$dbprefix."user WHERE userid='".(int)$_POST['i']."' AND uniqueid='".mysql_real_escape_string(strip_tags($_POST['k']))."'",'*')) {

        // Eintrag löschen
        if(DB_query("DELETE FROM ".$dbprefix."user WHERE userid='".(int)$_POST['i']."' AND uniqueid='".mysql_real_escape_string(strip_tags($_POST['k']))."' LIMIT 1",'#')) {
            makemap(false,'',$killed_data['gridid']);
            $deleted = true;
        }
    }
}


if($deleted) {
    $TMP['%[LINKPIXEL]%'] = 'getp.php?gr='.$killed_data['gridid'];
    print template($LANGDIR.'killed.htm',$TMP);

} else {
    $TMP['%[VISITORS]%'] = (int)$killed_data['hits'];
    $TMP['%[KILLFORM]%'] = '
        <form method="post" action="">
        <input type="hidden" name="k" value="'.htmlspecialchars(strip_tags($_REQUEST['k'])).'">
        <input type="hidden" name="i" value="'.(int)$_REQUEST['i'].'">
        <input type="hidden" name="kill" value="1">

        <input type="submit" style="width:200;height:28;color:#000000;font:bold" name="ok" value="   '.$_SP[39].'   ">
        </form>';


    print template($LANGDIR.'kill.htm',$TMP);

}
