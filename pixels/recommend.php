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
$VERSIONS[basename(__FILE__)] = "3.01 PRO";
#$filenamenr = basename(__FILE__);
include_once('header.php');
/******************************************************************************************
// If you get troubles with the tell a friend form that users can't send emails, try to delete the asterix (raute)
// from the followin line and see, if it will work then. It must be
// $from = $CONFIG['email_feedback'];
******************************************************************************************/

#$from = $CONFIG['email_webmaster'];

/******************************************************************************************/


$newpoint   = '<img src="'.$designpath.'stop.gif" align="absmiddle">&nbsp;';
$Focusform  = 'e';
$Focusfeld  = 'e_email';
unset($Nachricht,$from,$gesendet);

if(isset($_POST['ok'])) {
    // Email Absender
    if(empty($_POST['e_email']))  {
        $Nachricht .= $newpoint.$_SP[4].'<br>';
        $Fehlerfeld[] = "form[e_email]";
    } elseif(!empty($_POST['e_email']) && !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@([a-z0-9-]+\.){1,3}([a-z0-9-]{2,3})$",$_POST['e_email'])) {
        $Nachricht .= $newpoint.$_SP[5].'<br>';
        $Fehlerfeld[] = "form[e_email]";
    }
    // Email Empfänger
    if(empty($_POST['e_email_e']))  {
        $Nachricht .= $newpoint.$_SP[31].'<br>';
        $Fehlerfeld[] = "form[e_email_e]";
    } elseif(!empty($_POST['e_email_e']) && !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@([a-z0-9-]+\.){1,3}([a-z0-9-]{2,3})$",$_POST['e_email_e'])) {
        $Nachricht .= $newpoint.$_SP[32].'<br>';
        $Fehlerfeld[] = "form[e_email_e]";
    }
    if(!$Nachricht) {

        // Geblockte emails checken
        if($BAN_DATA = DB_array("SELECT ban FROM ".$dbprefix."ban WHERE ban_recommend=1",'+')) {
            while(list(,$d)=each($BAN_DATA)) {
                if(strpos($d,'%')===false) {
                    if(strtolower($_POST['e_email'])==strtolower($d) || strtolower($_POST['e_email_e'])==strtolower($d)) {
                        $banned = true;
                        break;
                    }
                } else {
                    if(stristr($_POST['e_email'],str_replace('%','',$d)) || stristr($_POST['e_email_e'],str_replace('%','',$d))) {
                        $banned = true;
                        break;
                    }
                }

            }
        }
        if($banned) {
            $Nachricht = $newpoint.$_SP[122];
        } else {
            // Alles ok, Mail verschicken.
            $TMP['%[EMAIL]%'] = stripslashes(strip_tags($_POST['e_email']));
            $from     = $from ? $from : $TMP['%[EMAIL]%'];

            sendmail(strip_tags($_POST['e_email_e']),template($LANGDIR.'mail_recommend.txt',$TMP),'',$from,'reply='.$TMP['%[EMAIL]%']);
            $gesendet = true;

            $Focusfeld = 'e_email_e';
            $_POST['e_email_e'] = $_POST['e_text'] = '';

            DB_query("UPDATE ".$dbprefix."config SET recommends=recommends+1","#");
        }
    }
}

$TMP = array();
$TMP['%[SENDINFO]%']      = $gesendet ?  '<br><br>'.$_SP[40].'<br><br>' : '';
$TMP['%[ERRORINFO]%']     = $Nachricht ? '<br><br>'.$Nachricht.'<br>' : '';
$TMP['%[RECOMMENDFORM]%'] = '<table  cellspacing="2">
                              <form method=post name="e">
                              <tr><td colspan="3"></td></tr>
                              <tr><td align="right"><font color="#0147AA"><b>'.$_SP[41].'&nbsp;</font></td>
                                  <td><input type="text" name="e_email" size="30" tabindex="1" style="color:#000000" value="'.htmlspecialchars(strip_tags(stripslashes($_POST['e_email']))).'" maxlength="50"></td>
                              <tr><td colspan="3"></td></tr>
                              <tr><td align="right"><font color="#0147AA"><b>'.$_SP[42].'&nbsp;</font></td>
                                  <td><input type="text" name="e_email_e" size="30" tabindex="2" style="color:#000000" value="'.htmlspecialchars(strip_tags(stripslashes($_POST['e_email_e']))).'" maxlength="50"></td>
                              <tr><td colspan="3"></td></tr>
                              <tr><td></td><td nowrap height="29"><input type=submit name=ok value="   '.$_SP[43].'   "></td></tr></form>
                            </table>';

print template($LANGDIR.'recommend.htm',$TMP);

include_once('incs/java_focus.php');
include_once('footer.php');
?>
