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
$VERSIONS['mail.php'] = "3.0";
$adminprocess = true;
include('../incs/functions.php');
require('lang/admin_'.$CONFIG['admin_language'].'.php');

ignore_user_abort(true); // Wichtig wegen Verbindungsabbruch
@set_time_limit(6000);

#---------------------------------------------------------------------------------------------------------

session_start();
if($_SESSION['pass'] != md5($CONFIG['adminpass']) || empty($CONFIG['adminpass'])) {
    header("Location: index.php?op=config");
    exit;
}

$newpoint  = '<img src="images/o.gif" align="absmiddle">&nbsp;';
$newpoint1 = '<img src="images/y.gif" align="absmiddle" hspace="3">';

if($_POST['sendmail']) {
    if(empty($_POST['empf']) && $_POST['an']==0) {
        $Nachricht .= $newpoint.$_SP[55]."<br>";
    } elseif(!empty($_POST['empf']) && $_POST['an']==0) {
        $emailstr = str_replace(',',';',str_replace(' ','',$_POST['empf']));      // erstmal Leerzeichen entfernen und evtl. Kommata gegen Semikolons tauschen

        // mehrere Email-Adressen?
        if(stristr($emailstr,';')) {
            $emailempfs = explode(';',$emailstr);
        } else {
            $emailempfs = array($emailstr);
        }

        // Check der Emails
        while(list(,$evalue) = each($emailempfs)) {
            if(!empty($evalue)) {
                if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@([a-z0-9-]+\.){1,3}([a-z0-9-]{2,4})$",$evalue)) {
                    $Nachricht .= $newpoint.$_SP[56]."<br>";
                } else {
                    // ok!
                    $emails[][0] = $evalue;
                }
            }
        }

    } elseif($_POST['an']==1) {     // Rundmail an alle
        $emails = DB_array("SELECT email FROM ".$dbprefix."user WHERE submit IS NOT NULL GROUP BY email",'#');
    } elseif($_POST['an']==2) {     // Rundmail nur an alle Newsletterempf‰nger
        $emails = DB_array("SELECT email FROM ".$dbprefix."mailinglist GROUP BY email",'#');
    }

    if(empty($_POST['betreff'])) {
        $Nachricht .= $newpoint.$_SP[57]."<br>";
    }
    if(empty($_POST['text'])) {
        $Nachricht .= $newpoint.$_SP[58]."<br>";
    }
    if(empty($_POST['absender'])) {
        $Nachricht .= $newpoint.$_SP[59]."<br>";
    }

    // Mail schreiben
    if(!$Nachricht && $emails) {
        while(list($k, $v) = each($emails)) {
          $bcc   .= "Bcc: ".$v[0]."\n";
          $_bcc[] = $v[0];
        }
        // Kopie an Webmaster
        if($_POST['kopie']) {
            $bcc   .= "Bcc: ".$CONFIG['email_webmaster']."\n";
            $_bcc[] = $CONFIG['email_webmaster'];
        }

        // Session schlieﬂen, Time-Limit unendlich setzen, Userabort ignorieren
        session_write_close();
        set_time_limit(0);
        ignore_user_abort(true);

        #// Mail senden, grunds‰tzlich als BCC
        #sendmail('',$_POST['text'],$_POST['betreff'],$_POST['absender'],'',$bcc);

        // Direkter Mailversand ohne BCC
        reset($emails);
        while(list(, $em) = each($_bcc)) {
            sendmail($em,'<html><body>'.chop(stripslashes($_POST['text'])).'</body></html>',chop(stripslashes($_POST['betreff'])),$_POST['absender'],'html=1');
        }

        // Vorlage speichern?
        if($_POST['vorl'] && $_POST['filename']) {
            if($fp = fopen('mailtemp/'.$_POST['filename'].'.txt',"w")) {
                flock($fp,2);
                fputs($fp,chop(stripslashes($_POST['text'])));
                flock($fp,3);
                fclose($fp);
                $Nachricht .= $newpoint1.$_SP[60]."<br>";
            } else {
                $Nachricht .= $newpoint.$_SP[61]."<br>";
            }
        }

        $nnn = (count($emails)>1) ? count($emails)." ".$_SP[62] : "1 ".$_SP[63];
        $Nachricht      .= $newpoint1.sprintf($_SP[64]," $nnn")."!<br>(".implode(', ',$_bcc).")<br><br>".'<a href="javascript:window.close();">'.$_SP[65].'</a>';
        $exit = true;
    }
} else {
    // Standardwerte
    $_POST['kopie'] = true;
}


if(!$exit) {

//-> Vorlagen laden
$defvorlagen    = 0;
$vorlagen       = array();
$vorlagen[0][0] = "";

$v=1;
$handle=opendir("mailtemp");
while($file = readdir($handle)) {
    if($file != "." && $file != ".." && $file[0] != "_" && $file != ".htaccess" && $file != ".htuser" && $file != ".htgroup") {
        $vorlagen[$v][0]   = $file;
        $vorlagen[$v++][1] = str_replace('.txt','',$file);
    }
}
closedir($handle);

$vorl = ($_REQUEST['vorl']>=0 AND $select1[$vorl][0] != "") ? $vorl : $defvorlagen;

//-> Standardempf‰nger
$ss = ($_REQUEST['an']>=0 AND $select1[$_REQUEST['an']][0] != "") ? $_REQUEST['an'] : $defselect1;

//-> Standardabsender (falls nicht ausgew‰hlt)
$absender = ($_REQUEST['absender']) ? $_REQUEST['absender'] : $sendfrom['kontakt'];

} // if not exit

#---------------------------------------------------------------------------------------------------------
?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=<?PHP print DB_query("SELECT charset FROM ".$dbprefix."languages WHERE code='".$CONFIG['admin_language']."'",'charset');?>" />
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<base target="_self">

<?PHP
    $class_path = "editor/";
    require_once($class_path."class.rich.php");
    echo rich_get_head_code($class_path);
?>

</head>

<body style="padding-left:10;color=#000000">

<form method="POST" name="mailform" action="" onSubmit="save_in_textarea_all();">
<table width=600 style="margin-bottom:10">
    <tr><td valign=top height=56><img src="images/lo.gif"></td>
        <td valign=top background="images/bgt.gif" width=100% style="padding-top:5;padding-left:50">

        <div align=right>
            </div>

        <br><br>
        <font style="font-size:13"><?php print date($CONFIG['date_format'],time()+(3600*$CONFIG['timezone']));?></font>

        </td>
        </tr>
    <tr><td height=30 colspan=2 nowrap><font color="#FFFFFF"><b><?=$_SP[66]?></td></tr>
</table>

<font color="#FFFFFF"><b>
<?php

    if($Nachricht) print ShowNachricht($Nachricht,($exit==false));
    if($exit) exit;

?>
</b></font>
<table border="0" width="600">
  <?php if(!$_REQUEST['mail']) { ?>
  <tr>
    <td height="24" align=right><b><font color="#C40000">&nbsp;<?=$_SP[67]?>:&nbsp;</b></td>
    <td colspan="2">
    <select size="1" name="an">
        <option value="0"></option>
        <option value="1" <?php if($_POST['an']==1) print 'selected';?>><?=$_SP[68]?></option>
        <option value="2" <?php if($_POST['an']==2) print 'selected';?>><?=$_SP[69]?></option>
    </select></td>
  </tr>
  <?php } ?>
  <tr>
    <td align="right" valign=top><b>&nbsp;<font color="#C40000"><?=$_SP[70]?>:&nbsp;</b></td>
    <td width="100%" colspan="2"><textarea  style="width:500;height:45" name="empf"><?php print ($_POST['empf']) ? htmlspecialchars($_POST['empf']) : htmlspecialchars($_REQUEST['mail']);?></textarea></td>
  </tr>

  <tr>
    <td align="right"><b>&nbsp;<font color="#C40000"><?=$_SP[71]?>:&nbsp;</b></td>
    <td width="100%" colspan="2"><input type="text" name="betreff" value="<?php print htmlspecialchars($_POST['betreff']);?>" style="width:500"></td>
  </tr>
  <tr>
    <td align="right" nowrap><b><font color="#C40000">&nbsp;<?=$_SP[72]?>:&nbsp;</b></td>
    <td width="100%" colspan="2">
    <select size="1" name="vorlage" onChange="javascript:if(this.options[this.selectedIndex].value)open('mailtemp/'+this.options[this.selectedIndex].value,'vorlage','width=700,height=500,scrollbars')">
        <?php
            if(is_array($vorlagen)) {
                while(list(,$vo) = each($vorlagen))
                    print '<option value="'.$vo[0].'">'.$vo[1].'</option>';
            }
        ?>
    </select> <font color="#666666" style="font-size:9px"> <?=help($_SP[73],true,300)?>

    </td>
  </tr>
  <tr>
    <td align="right" valign="top"><b><font color="#C40000">&nbsp;&nbsp;&nbsp;<?=$_SP[74]?>:&nbsp;</b></td>
    <td colspan="2">
      <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
          <td>

<?PHP
        $editor = new rich('', "text", $_POST['text'], "500", "200", "../../mydir/", $DOMAIN."/mydir/");
        $editor->set_lang($admlang);
        $editor->simple_mode(true);
        $editor->active_mode(false);
        $editor->hide_tb("help");
        $editor->hide_tb("link",false);
        $editor->hide_tb("source",true);
        $editor->hide_tb("align",true);
        $editor->hide_tb("special_chars",false);
        $editor->hide_tb("image",false);
        $editor->set_borders_visibility(true);
        $editor->set_br_on_enter(true);
        $editor->draw();

        #print '<textarea name="text" style="width:500;height:500">'.htmlspecialchars($_POST['text']).'</textarea>';

?>
          &nbsp;</td>
          <td valign="top" align="right" width="100%">

            <p>&nbsp;&nbsp;</td>
          <td valign="top" align="right">&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td height="24" align=right><b><font color="#C40000">&nbsp;<?=$_SP[75]?>:&nbsp;</b></td>
    <td colspan="2">
    <select size="1" name="absender">
        <option value="<?php print $CONFIG['email_webmaster'];?>"><?php print $CONFIG['email_webmaster'];?></option>
    </select></td>
  </tr>
  <tr>
    <td align="right"><input type="checkbox" id="vorl" name="vorl" value="1" <?php if($_POST['vorl']) print 'checked';?>></td>
    <td nowrap><b><font color="#C40000"><label for="vorl"><?=$_SP[76]?></label>&nbsp;<input type="text" name="filename" value="<?php print htmlspecialchars($_POST['filename']);?>" size="50" onBlur="if((!document.mailform.vorl.checked && document.mailform.filename.value) || (document.mailform.vorl.checked && !document.mailform.filename.value)) document.mailform.vorl.click()"></td>
  </tr>
  <tr>
    <td align="right"><input type="checkbox" id="kopie" name="kopie" value="1" <?php if($_POST['kopie']) print 'checked';?>></td>
    <td nowrap><b><font color="#C40000"><label for="kopie"><?=$_SP[77]?></label></td>
  </tr>
  <tr>
    <td colspan="3" rowspan="1" align="right"><input type="submit" value="     <?=$_SP[78]?>     " name="sendmail"></td>
  </tr>

</table></form>
<script language="JavaScript" type="text/javascript" src="../wz_tooltip.js"></script>
</body>
</html>