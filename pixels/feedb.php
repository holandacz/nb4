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
// If you get troubles with the feedback form that users can't send emails, try to delete the asterix (raute)
// from the followin line and see, if it will work then. It must be:
// $from = $CONFIG['email_feedback'];
******************************************************************************************/

#$from = $CONFIG['email_feedback'];

/******************************************************************************************/
$betreff = array();
$betreff[0] = $_SP[56];
$betreff[1] = $_SP[57];
$betreff[2] = $_SP[58];
$betreff[3] = $_SP[59];

unset($Nachricht,$from);
$newpoint   = '<img src="'.$designpath.'stop.gif" align="absmiddle">&nbsp;';

// Voreinstellung per Parameterübergabe
if(isset($_GET['betreff']) && (int)$_GET['betreff']>0 && (int)$_GET['betreff']<count($betreff))
    $pflichtbetreff = true;

if(isset($_POST['submit'])) {
    if(empty($_POST['email']))  {
        $Nachricht .= $newpoint.$_SP[4]."<br>";
    } elseif(!empty($_POST['email']) && !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@([a-z0-9-]+\.){1,3}([a-z0-9-]{2,3})$",$_POST['email'])) {
        $Nachricht .= $newpoint.$_SP[5]."<br>\n";
    }
    if(empty($_POST['betreff']) && !$pflichtbetreff)  {
        $Nachricht .= $newpoint.$_SP[54]."<br>\n";
    }
    if(empty($_POST['text']))  {
        $Nachricht .= $newpoint.$_SP[55]."<br>\n";
    }

    if(!$Nachricht) {
        // Geblockte emails checken
        if($BAN_DATA = DB_array("SELECT ban FROM ".$dbprefix."ban WHERE ban_feedback=1",'+')) {
            while(list(,$d)=each($BAN_DATA)) {
                if(strpos($d,'%')===false) {
                    if(strtolower($_POST['email'])==strtolower($d)) {
                        $banned = true;
                        break;
                    }
                } else {
                    if(stristr($_POST['email'],str_replace('%','',$d))) {
                        $banned = true;
                        break;
                    }
                }

            }
        }
        if($banned) {
            $Nachricht = $newpoint.$_SP[122];
        } else {

            // Mailinhalt definieren:
            $Text  = "Feedback:\n";
            $Text .= ($_POST['email'])   ? "Email: ".strip_tags($_POST['email'])."\n"     : "Email: no data\n";
            $Text .= "Subject: ".$betreff[1]."\n\n";
            $Text .= str_repeat('-',70)."\n".strip_tags($_POST['text'])."\n";

            // Alles ok, Mail verschicken.
            $absender = ($_POST['email']) ? strip_tags($_POST['email']) : $CONFIG['email_feedback'];
            $from     = $from ? $from : $absender;

            sendmail($CONFIG['email_feedback'],stripslashes($Text),$betreff[(int)$_REQUEST['betreff']],$from,"reply=$absender");
            $gesendet = true;

        }
    }
}


$TMP = array();
if(!$gesendet)   $TMP['%[SENDINFO]%'] = $_SP[49];
else             $TMP['%[SENDINFO]%'] = '';

$TMP['%[ERRORINFO]%'] = $Nachricht ? '<br><br>'.$Nachricht.'<br>' : '';

ob_start();
?>
<form method="POST" name="kontakt" action="">
  <input type="hidden" value="1" name="submit">
  <table border="0">
    <tr>
      <td valign="middle" align="right" nowrap><font color="#CC0000"><b><?=$_SP[51]?>&nbsp;</font></td>
      <td><input type="text" name="email" size="30" value="<?php print htmlspecialchars(stripslashes($_POST['email']));?>" tabindex="2"></td>
    </tr>
    <tr>
      <td valign="middle" align="right" nowrap><font color="#CC0000"><b><?=$_SP[52]?>&nbsp;</font></td>
      <td><?PHP
                if(!$pflichtbetreff) {
                    print '<select size="1" name="betreff" tabindex="4">';

                    while(list($a,$b) = each($betreff))
                        if($a == (int)$_REQUEST['betreff'])
                            print '<option value="'.$a.'" selected>'.$b.'</option>';
                        else
                            print '<option value="'.$a.'">'.$b.'</option>';
                    print '</select>';
                } else {
                    print $betreff[(int)$_GET['betreff']];
                }
          ?></td>
    </tr>
    <tr>
      <td valign="top" align="right"><b><?=$_SP[53]?>&nbsp;</font></td>
      <td><textarea rows="8" name="text" cols="55" tabindex="5"><?php print htmlspecialchars(strip_tags(stripslashes($_POST['text'])));?></textarea></td>
    </tr>
    <tr>
      <td valign="top" align="right"></td>
      <td><input type="submit" value="  <?=$_SP[50]?>  " border="0"></td>
    </tr></form>
  </table>
<?PHP


$TMP['%[FEEDBACKFORM]%']  = !$gesendet  ? ob_get_contents() : $_SP[48].'<br>';
ob_end_clean();

print template($LANGDIR.'feedback.htm',$TMP);

include_once('incs/java_focus.php');
include_once('footer.php');
?>