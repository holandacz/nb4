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
$VERSIONS[basename(__FILE__)] = "3.0";
$filenamenr = basename(__FILE__);
include_once('header.php');

$newpoint   = '<img src="'.$designpath.'stop.gif" align="absmiddle">&nbsp;';
$Focusform  = 'sendpass';
$Focusfeld  = 'kennung';
unset($Nachricht,$TMP);

$_POST['kennung'] = isset($_POST['ok']) ? trim(strip_tags($_POST['kennung'])) : '';

if(!isset($_POST['ok'])) {
    $Fehlerfeld[] = "form[kennung]";

} elseif(empty($_POST['kennung']) ) {
    $Nachricht = $newpoint.$_SP[114].'<br>';
    $Fehlerfeld[] = "form[kennung]";

   print "H";

} elseif($logindata = DB_query("SELECT email,userid,logincode FROM ".$dbprefix."user t0 LEFT JOIN ".$dbprefix."grids t1 USING(gridid) WHERE t0.userid='".mysql_real_escape_string($_POST['kennung'])."' AND logincode<>'' AND logincode IS NOT NULL AND t1.editpixel=1 LIMIT 1",'*')) {
    // Ok, Zugandsdaten senden
    $tmp['%[USERID]%']    = $logindata['userid'];
    $tmp['%[LOGINCODE]%'] = $logindata['logincode'];
    sendmail($logindata['email'],template($LANGDIR.'mail_sendpass.txt',$tmp),'','"'.$CONFIG['domainname'].'" <'.$CONFIG['email_webmaster'].'>');
    $pwsend = '<br>'.$_SP[117];

} else {
    $Nachricht = $newpoint.$_SP[115].'<br>';
    $Fehlerfeld[] = "form[kennung]";

}


$TMP['%[ERRORINFO]%']  = $Nachricht ? '<br><br>'.$Nachricht : '';
$TMP['%[SENDPWFORM]%'] = $pwsend ? $pwsend : '
                            <table  cellspacing="2">
                              <form method=post name="sendpass"><input type="hidden" name="ok" value="1">
                              <tr><td colspan="3"></td></tr>
                              <tr><td align="right"><font color="#0147AA"><b>'.$_SP[108].'&nbsp;</font></td>
                                  <td><input type="text" name="kennung" size="50" tabindex="1" style="color:#000000" value="'.htmlspecialchars(strip_tags(stripslashes($_POST['kennung']))).'" maxlength="80"></td>
                              <tr><td colspan="3"></td></tr>
                              <tr><td></td><td nowrap height="29"><input type=submit name=ok value="   '.$_SP[116].'   "></td></tr></form>
                            </table>';

print template($LANGDIR.'login_sendpass.htm',$TMP);
include_once('footer.php');
include_once('incs/java_focus.php');

?>
