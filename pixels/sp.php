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
error_reporting(0);
$VERSIONS[basename(__FILE__)] = "3.0";
$showpicprocess = true;
include_once('incs/functions.php');

header("Content-type: image/png");

if((int)$_GET['u']>0) {
    if(@file_exists('grids/u'.(int)$_GET['u'].'.png')) {
        $src = imagecreatefrompng('grids/u'.(int)$_GET['u'].'.png');
        print imagepng($src);
        imagedestroy($src);
    } else {
        print base64_decode(DB_query("SELECT bild FROM ".$dbprefix."user WHERE userid='".(int)$_GET['u']."'",'bild'));
    }
} else {
    print base64_decode($_SESSION['p']);
}

?>