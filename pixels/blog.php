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
include('incs/functions.php');
$VERSIONS[basename(__FILE__)] = "3.01";
#$filenamenr = basename(__FILE__);
include('header.php');

if(!$blog = DB_array("SELECT * FROM ".$dbprefix."blog WHERE lang='$lang' ORDER by blog_datetime DESC",'*'))
    $blog = DB_array("SELECT * FROM ".$dbprefix."blog WHERE lang='".mysql_real_escape_string($CONFIG['standard_language'])."' OR lang IS NULL ORDER by blog_datetime DESC",'*');

if(is_array($blog)) {
    while(list(,$bl) = each($blog)) {
        $blog_content .= '<br><font class="blog_date">'.date($CONFIG['date_format'],strtotime($bl['blog_datetime'])+(3600*$CONFIG['timezone'])).'</font><h2 class="blog_title">'.$bl['blog_title'].'</h2>'.nl2br(html_entity_decode($bl['blog_content'])).'<hr class="blog_line">';
    }
}
$TMP['%[CONTENT]%'] = $blog_content;

print template($LANGDIR.'blog.htm',$TMP);

include('footer.php');

?>
