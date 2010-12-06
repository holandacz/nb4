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
$VERSIONS[basename(__FILE__)] = "3.02";
#$filenamenr = basename(__FILE__);

$newpoint   = '<img src="'.$designpath.'stop.gif" align="absmiddle">&nbsp;';
$Focusform  = 'login';
$Focusfeld  = 'kennung';
unset($Nachricht,$login_logged_in,$TMP);

$_SESSION['kennung']   = isset($_POST['ok']) ? trim(strip_tags($_POST['kennung']))    : $_SESSION['kennung'];
$_SESSION['logincode'] = isset($_POST['ok']) ? md5(stripslashes($_POST['logincode'])) : $_SESSION['logincode'];


// Logoff?
if($_GET['logoff']) {
    unset($_SESSION);
    session_destroy();
    header("Location: ".$CONFIG['scriptpath']."/index.php".$trackpage);
    exit;
}

if($_SESSION['loginctries']>10) {
    $Nachricht = $newpoint.$_SP[112].'<br>';
    $no_loginform = true;
    $_SESSION['loginctries']++;

} elseif(!isset($_POST['ok']) && !$_SESSION['kennung'] && !$_SESSION['logincode']) {
    $Fehlerfeld[] = "form[kennung]";

} elseif(empty($_SESSION['kennung']) || empty($_SESSION['logincode']) || eregi("[^0-9]",$_SESSION['kennung']) ) {
    $Nachricht = $newpoint.$_SP[111].'<br>';
    $Fehlerfeld[] = "form[kennung]";
    $_SESSION['loginctries']++;

} elseif($logindata = DB_query("SELECT * FROM ".$dbprefix."user t0 LEFT JOIN ".$dbprefix."grids t1 USING(gridid) WHERE t0.userid='".mysql_real_escape_string($_SESSION['kennung'])."' AND md5(logincode)='".mysql_real_escape_string($_SESSION['logincode'])."' AND logincode<>'' AND logincode IS NOT NULL AND t1.editpixel=1 AND submit IS NOT NULL AND (reserved IS NULL OR reserved=0) LIMIT 1",'*')) {
    // Alles ok, einloggen erlaubt.
    $login_logged_in = true;
    unset($_SESSION['loginctries']);


} else {
    $login_logged_in = false;
    $Nachricht = $newpoint.$_SP[113].'<br>';
    $Fehlerfeld[] = "form[kennung]";
    $_SESSION['loginctries']++;
}

// Muss hier stehen wegen Login/Logoff Erkennung
include_once('header.php');


// Login-Editor
if($login_logged_in) {

    $_POST['gr'] = $_REQUEST['gr'] = $logindata['gridid'];
    $_POST['userid'] = $logindata['userid'];
    $GRID[$logindata['gridid']] = $logindata;

    // Daten speichern
    if($_POST['save']) {
        // Url
        $_POST['url'] = trim(strip_tags(str_replace('http://','',str_replace('https://','',strtolower($_POST['url'])))));
        if(empty($_POST['url']) && !$logindata['popup']) {
            $Nachricht .= $newpoint.$_SP[0].'<br>';
            $Fehlerfeld['url'] = true;
        } elseif(!empty($_POST['url']) && eregi("[^-a-zäüöß0-9_/?&=%+()*;,#@.:~\]",$_POST['url'])) {
            $Nachricht .= $newpoint.$_SP[2].'<br>';
            $Fehlerfeld['url'] = true;
        } elseif(!empty($_POST['url']) && (strpos($_POST['url'],'.')===false || ($_POST['host']!='http://' && $_POST['host']!='https://'))) {
            $Nachricht .= $newpoint.$_SP[1].'<br>';
            $Fehlerfeld['url'] = true;
        }
        $logindata['url'] = !empty($_POST['url']) ? $_POST['host'].$_POST['url'] : '';

        // Titel
        if(!empty($_POST['title']) && strlen($_POST['title']) > $logindata['title_chars']) {
            $Nachricht .= $newpoint.sprintf($_SP[3],$logindata['title_chars']).'<br>';
            $Fehlerfeld['title'] = true;
        }
        $logindata['title'] = $_POST['title'];

        // eMails
        $_POST['email']  = trim(strip_tags(strtolower($_POST['email'])));
        if(empty($_POST['email']))  {
            $Nachricht .=  $newpoint.$_SP[4].'<br>';
            $Fehlerfeld['email'] = true;
        } elseif(!eregi("^[_a-zäüöß0-9-]+(\.[_a-zäüöß0-9-]+)*@([a-zäüöß0-9-]+\.){1,3}([a-zäüöß0-9-]{2,4})$",$_POST['email'])) {
            $Nachricht .=  $newpoint.$_SP[5].'<br>';
            $Fehlerfeld['email'] = true;
        }
        $logindata['email'] = $_POST['email'];

        // Doppelte URL/email?
        if($logindata['unique_url'] && !empty($logindata['url'])) {
            if($regdat = DB_query("SELECT DATE_FORMAT(regdat,'%d.%m.%Y') AS d FROM ".$dbprefix."user WHERE gridid='".(int)$_POST['gr']."'AND (email='".mysql_real_escape_string($logindata['email'])."' OR url='".mysql_real_escape_string($logindata['url'])."' OR url='".mysql_real_escape_string(str_replace('www.','',$logindata['url']))."') AND userid<>'".$logindata['userid']."'",'d')) {
                $Nachricht .=  $newpoint.sprintf($_SP[8],$regdat);
                $Fehlerfeld['url']   = true;
                $Fehlerfeld['email'] = true;
            }
        }

        // Passwort ändern
        if(!empty($_POST['pass1']) || !empty($_POST['pass2'])) {
            if(empty($_POST['pass1']) || empty($_POST['pass2'])) {
                $Nachricht .= $newpoint.$_SP[120];
                $Fehlerfeld['pass1'] = true;
                $Fehlerfeld['pass2'] = true;
            } elseif(strlen($_POST['pass1']) < 6 || strlen($_POST['pass2']) < 6) {
                $Nachricht .= $newpoint.$_SP[121];
                $Fehlerfeld['pass1'] = true;
                $Fehlerfeld['pass2'] = true;
            } elseif($_POST['pass1'] != $_POST['pass2']) {
                $Nachricht .= $newpoint.$_SP[120];
                $Fehlerfeld['pass1'] = true;
                $Fehlerfeld['pass2'] = true;
            } else {
                $NEWPASS = ",logincode='".mysql_real_escape_string($_POST['pass1'])."'";
            }
        }

        //-> Upload
        if($_FILES['upload'] && !empty($_FILES['upload']['tmp_name'])) {
            if($_FILES['upload']['error'] == 3)
                $Nachricht = $newpoint.$_SP[12];
            elseif($_FILES['upload']['error'] == 1 || $_FILES['upload']['error'] == 2 || $_FILES['upload']['size'] > $logindata['image_upload_kbytes']*1024)
                $Nachricht = $newpoint.sprintf($_SP[11],$logindata['image_upload_kbytes']);
            elseif(is_uploaded_file($_FILES['upload']['tmp_name']) && $_FILES['upload']['size']>0) {
                //-> Figur bestimmen
                $f_hidden = explode(',',$logindata['felder']);
                $BLOCKSIZE_X = $logindata['blocksize_x'];
                $BLOCKSIZE_Y = $logindata['blocksize_y'];
                while(list(,$v)=each($f_hidden)) {
                    $tops[]  = (int)(($v-1)/100)*$BLOCKSIZE_Y;
                    $lefts[] = fsubstr($v-1,-2)*$BLOCKSIZE_X;
                }
                sort($tops);
                sort($lefts);
                $hoehe  = $tops[count($tops)-1]-$tops[0];
                $breite = $lefts[count($lefts)-1]-$lefts[0];
                reset($f_hidden);
                $xy = array();
                while(list(,$v)=each($f_hidden)) {
                    $top   = (int)(($v-1)/100)*$BLOCKSIZE_Y;
                    $left  = fsubstr($v-1,-2)*$BLOCKSIZE_X;
                    $xy[(int)$i]['x']   = $left-$lefts[0];
                    $xy[(int)$i++]['y'] = $top-$tops[0];
                }
                reset($f_hidden);
                $imageinfo = getimagesize($_FILES['upload']['tmp_name']);
                $imagetype = array(1 => '.gif','.jpg','.png');
                if($imageinfo[2] > 0 && $imageinfo[2] < 4) {
                    $Ext = $imagetype[$imageinfo[2]];
                    $image = $_FILES['upload']['tmp_name'];
                }
                //-> Logo generieren
                $logineditprocess = true;
                if($Ext)   $temp_pic  = include('ci.php');
                else       $Nachricht = $newpoint.$_SP[13];

                $alte_bildext = $logindata['bildext'];
                $BILD = ($temp_pic) ? ",bild='".$temp_pic."',bildext='".mysql_real_escape_string($_SESSION['bildext'])."'" : '';
            } else {
                $Nachricht = $newpoint.$_SP[12];
            }
        }

        // Banned?
        if($BAN_DATA = DB_array("SELECT ban,ban_url,ban_title,ban_email FROM ".$dbprefix."ban WHERE ban_url=1 OR ban_title=1 OR ban_email",'*')) {
            while(list(,$d)=each($BAN_DATA)) {
                if(strpos($d['ban'],'%')===false) $checkban = "/\b".preg_quote($d['ban'],"/")."\b/i";
                else                              $checkban = "/\b".str_replace('%','(.*)',preg_quote($d['ban'],"/"))."\b/i";
                if($d['ban_email'] && preg_match($checkban,$_POST['email'])) $Nachricht .= $newpoint.$_SP[122].'<br>';
                if($d['ban_url']   && preg_match($checkban,$_POST['url']))   $Nachricht .= $newpoint.$_SP[123].'<br>';
                if($d['ban_title'] && preg_match($checkban,$_POST['title'])) $Nachricht .= $newpoint.$_SP[124].'<br>';
                if($Nachricht) break;
            }
        }


        // Fehlermeldung?
        if(!$Nachricht) {
            if(DB_query("UPDATE ".$dbprefix."user SET email='".mysql_real_escape_string($logindata['email'])."',url='".mysql_real_escape_string($logindata['url'])."',target='".mysql_real_escape_string($_POST['target'])."',title='".mysql_real_escape_string($logindata['title'])."'".$BILD.$NEWPASS." WHERE userid='".(int)$logindata['userid']."'",'#')) {
                $Nachricht .=  $newpoint1.$_SP[118];

                if($BILD) {
                    // Originalbild direkt abspeichern
                    if($logindata['image_saveorig'] && $temp_pic) {
                        // Altes Bild löschen, falls Extension anders
                        if(!empty($alte_bildext) && $alte_bildext != $_SESSION['bildext']) @unlink('grids/u'.(int)$logindata['userid'].'_orig'.$alte_bildext);
                        if($move_uploaded_file) {
                            move_uploaded_file($image,'grids/u'.(int)$logindata['userid'].'_orig'.$_SESSION['bildext']);
                        } elseif($handle = fopen('grids/u'.(int)$logindata['userid'].'_orig'.$_SESSION['bildext'],'wb')) {
                            fwrite($handle, base64_decode($_SESSION['origimg']));
                            fclose($handle);
                        }

                    }
                    // Pixelbild direkt abspeichern
                    if($temp_pic) {
                        if($handle = fopen('grids/u'.(int)$logindata['userid'].'.png','wb')) {
                            fwrite($handle, base64_decode($temp_pic));
                        }
                        fclose($handle);
                    }
                    $logindata['bildext'] = $_SESSION['bildext'];
                    unset($temp_pic,$_SESSION['origimg'],$_SESSION['bildext']);
                }

                makemap(false,false,$logindata['gridid']);

                if($NEWPASS) $_SESSION['logincode'] = md5(stripslashes($_POST['pass1']));

            } else {
                // Fehler beim Speichern

            }
        }



    } else {

    }

    $host = parse_url($logindata['url']);
    $logindata['host']  = $host['scheme'];
    $logindata['url']   = $host['host'];


    $TMP['%[ERRORINFO]%'] = $Nachricht ? '<br><br>'.$Nachricht : '';
    $TMP['%[USERID]%']  = $logindata['userid'];

    $TMP['%[EDITFORM]%'] = '<form method="POST" enctype="multipart/form-data" name="editpixel">';

    $TMP['%[INPUT_FILE]%'] = $logindata['image_upload'] ? '<input type="file" name="upload" maxbyte="'.($logindata['image_upload_kbytes']*1024).'" class="savepixel_fileupload" tabindex="1">' : '';

    $TMP['%[INPUT_URL]%']  = '<select name="host" class="savepixel_http" tabindex="2"><option value="http://"'.($logindata['host']=="http" ? 'selected' : '').'>http://</option><option value="https://"'.($logindata['host']=="https" ? 'selected' : '').'>https://</option></select>';
    $TMP['%[INPUT_URL]%'] .= '<input type="text" name="url" class="savepixel_url" maxlength="1000" value="'.htmlspecialchars(stripslashes($logindata['url'])).'" tabindex="3"';
        if($Fehlerfeld['url']) $TMP['%[INPUT_URL]%'].= ' style="border: 1px solid red"';
        $TMP['%[INPUT_URL]%'].= '">';

    $TMP['%[INPUT_TITLE]%'] = '<input type="text" name="title" class="savepixel_inputs" maxlength="'.$logindata['title_chars'].'" value="'.htmlspecialchars(stripslashes($logindata['title'])).'" tabindex="4"';
        if($Fehlerfeld['title']) $TMP['%[INPUT_TITLE]%'] .= ' style="border: 1px solid red"';
        $TMP['%[INPUT_TITLE]%'] .= '">';

    $TMP['%[INPUT_EMAIL]%'] = '<input type="text" name="email" class="savepixel_inputs" maxlength="90" tabindex="5" value="'.htmlspecialchars(stripslashes($logindata['email'])).'"';
        if($Fehlerfeld['email']) $TMP['%[INPUT_EMAIL]%'] .= ' style="border: 1px solid red"';
        $TMP['%[INPUT_EMAIL]%'] .= '">';

    $TMP['%[INPUT_PASS1]%'] = '<input type="password" name="pass1" class="savepixel_password" tabindex="6"';
        if($Fehlerfeld['pass1']) $TMP['%[INPUT_PASS1]%'] .= ' style="border: 1px solid red"';
        $TMP['%[INPUT_PASS1]%'] .= '">';
    $TMP['%[INPUT_PASS2]%'] = '<input type="password" name="pass2" class="savepixel_password" tabindex="7"';
        if($Fehlerfeld['pass2']) $TMP['%[INPUT_PASS2]%'] .= ' style="border: 1px solid red"';
        $TMP['%[INPUT_PASS2]%'] .= '">';

    $TMP['%[SUBMIT]%'] = '<input type="submit" name="save" value=" '.$_SP[22].' "  class="savepixel_submitbutton" tabindex="8"></form>';

    $TMP['%[IMAGE]%']           = '<img src="sp.php?u='.(int)$logindata['userid'].'&x='.time().'">';
    $TMP['%[ORIGINAL_IMAGE]%']  = @file_exists('grids/u'.(int)$logindata['userid'].'_orig'.$logindata['bildext']) && $logindata['image_saveorig'] ? '<img src="grids/u'.(int)$logindata['userid'].'_orig'.$logindata['bildext'].'?x='.time().'">' : '';

    print template($LANGDIR.'login_editor.htm',$TMP);


// Login-Formular
} else {

    $TMP['%[ERRORINFO]%'] = $Nachricht ? '<br><br>'.$Nachricht : '';
    $TMP['%[LOGINFORM]%'] = $no_loginform ? '' : '
                            <table  cellspacing="2">
                              <form method=post name="login">
                              <tr><td colspan="3"></td></tr>
                              <tr><td align="right"><font color="#0147AA"><b>'.$_SP[108].'&nbsp;</font></td>
                                  <td><input type="text" name="kennung" size="30" tabindex="1" style="color:#000000" value="'.htmlspecialchars(strip_tags(stripslashes($_POST['kennung']))).'" maxlength="50"></td>
                              <tr><td colspan="3"></td></tr>
                              <tr><td align="right"><font color="#0147AA"><b>'.$_SP[109].'&nbsp;</font></td>
                                  <td><input type="password" name="logincode" size="30" tabindex="2" style="color:#000000"  maxlength="50"></td>
                              <tr><td colspan="3"></td></tr>
                              <tr><td></td><td nowrap height="29"><input type=submit name=ok value="   '.$_SP[110].'   "></td></tr></form>
                            </table>';

    print template($LANGDIR.'login.htm',$TMP);
    include_once('incs/java_focus.php');
}

include_once('footer.php');
?>
