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
$VERSIONS[basename(__FILE__)] = "3.01 PRO";
#$filenamenr = basename(__FILE__);
include('header.php');

$newpoint   = '<img src="'.$designpath.'/stop.gif" width="16" height="16" align="absmiddle">&nbsp;';
$Focusform = 'ml';
$Focusfeld = 'e_email';

if($_POST['ui']) {
    $_POST['e_email'] = $_POST['ui'];

} elseif($_POST['a']) {

    // Email Absender
    if(empty($_POST['e_email']))  {
        $Nachricht .= $newpoint.$_SP[4].'<br>';
        $Fehlerfeld[] = "form[e_email]";
    } elseif(!empty($_POST['e_email']) && !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@([a-z0-9-]+\.){1,3}([a-z0-9-]{2,3})$",$_POST['e_email'])) {
        $Nachricht .= $newpoint.$_SP[5].'<br>';
        $Fehlerfeld[] = "form[e_email]";
    } elseif($_POST['a']==2 && !DB_query("DELETE FROM ".$dbprefix."mailinglist WHERE email='".mysql_real_escape_string($_POST['e_email'])."'",'#')) {
        $Nachricht .= $newpoint.$_SP[46].'<br>';
    } elseif($_POST['a']==1 && DB_query("SELECT email FROM ".$dbprefix."mailinglist WHERE email='".mysql_real_escape_string($_POST['e_email'])."'",'#')) {
        $Nachricht .= $newpoint.$_SP[47].'<br>';
    }

    if(!$Nachricht) {

        if($_POST['a']==2) $entfernt = true;
        else {
            // Geblockte emails checken
            if($BAN_DATA = DB_array("SELECT ban FROM ".$dbprefix."ban WHERE ban_newsletter=1",'+')) {
                while(list(,$d)=each($BAN_DATA)) {
                    if(strpos($d,'%')===false) {
                        if(strtolower($_POST['e_email'])==strtolower($d)) {
                            $banned = true;
                            break;
                        }
                    } else {
                        if(stristr($_POST['e_email'],str_replace('%','',$d))) {
                            $banned = true;
                            break;
                        }
                    }

                }
            }
            if($banned) {
                $Nachricht = $newpoint.$_SP[122];
            } else {
                DB_query("INSERT INTO ".$dbprefix."mailinglist SET email='".mysql_real_escape_string($_POST['e_email'])."',lang='".mysql_real_escape_string(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2))."'","#");
                $hinzu = true;
            }
        }
    }

}


$TMP = array();
if($entfernt)   $TMP['%[INFO]%'] = '<br><br>'.$_SP[44].'<br><br>';
elseif($hinzu)  $TMP['%[INFO]%'] = '<br><br>'.$_SP[45].'<br><br>';
else $TMP['%[INFO]%'] = '';

$TMP['%[ERRORINFO]%']     = $Nachricht ? '<br><br>'.$Nachricht.'<br>' : '';
$TMP['%[MAILINGFORM]%'] = '<table border="0" width="500" cellspacing="1" cellpadding="0">
                              <form method=post name="ml" action="">
                              <tr><td colspan="3"></td></tr>
                              <tr><td align="right"><font color="#0147AA"><b>'.$_SP[41].'&nbsp;</font></td>
                                  <td><input type="text" name="e_email" style="width:200" size="30" tabindex="1" style="color:#000000" value="'.htmlspecialchars($_POST['e_email']).'" maxlength="50"></td>
                              <tr><td colspan="3"></td></tr>
                              <tr><td align="right" valign="top">&nbsp;</td>
                                  <td><select name=a style="width:200;color:#000000" tabindex=2>
                                        <option value=1 '.($_POST['a']==1 ? "selected" : "").'>'.$_SP[72].'</option>
                                        <option value=2 '.($_POST['a']==2 ? "selected" : "").'>'.$_SP[73].'</option>
                                      </select></td>
                              <tr><td colspan="3"></td></tr>
                              <tr><td></td><td nowrap height="29"><input tabindex=3 type=submit name=ok style="width:80" value="   OK   "></td></tr></form>
                            </table>';

print template($LANGDIR.'mailing.htm',$TMP);

include_once('incs/java_focus.php');
include_once('footer.php');
?>
