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
$VERSIONS[basename(__FILE__)] = "3.02 PRO";
#$filenamenr = basename(__FILE__);
include_once('header.php');

if($BAN_DATA = DB_array("SELECT ban FROM ".$dbprefix."ban WHERE ban_referrer=1",'+')) {
    $bans = array();
    while(list(,$d)=each($BAN_DATA)) {
        if(strpos($d,'%')===false)  $bans[] = "referrer<>'".mysql_real_escape_string($d)."'";
        else                        $bans[] = "referrer NOT LIKE '".mysql_real_escape_string($d)."'";
    }
    $BAN      = implode(' AND ',$bans);
    $andBAN   = 'AND '.$BAN;
}
$data1 = DB_array("SELECT referrer,COUNT(*) AS hits FROM ".$dbprefix."ip t1 LEFT JOIN ".$dbprefix."referrer t2 USING(refid) WHERE dailydatum=SUBDATE(CURDATE(), INTERVAL 1 DAY) AND referrer<>'' $andBAN GROUP BY t1.refid ORDER BY hits DESC LIMIT ".(int)$CONFIG['referrer_value'],'*');
$data2 = DB_array("SELECT referrer,hits FROM ".$dbprefix."referrer WHERE referrer<>'' $andBAN ORDER BY hits DESC LIMIT ".(int)$CONFIG['referrer_value'],'*');

ob_start();
?>
        <table cellspacing=5>
        <?php
            if(count($data1)) {
                print '<tr><td colspan=5><br><br><b>'.sprintf($_SP[77],(int)$CONFIG['referrer_value']).'</b><br><br></td>';
                while(list(,$d) = each($data1))
                    print '<tr><td align=center><b>'.(++$i).'.&nbsp;&nbsp;</b></td><td style="padding-right:20"><b><a href="http://'.$d['referrer'].'" target=_blank>'.htmlspecialchars($d['referrer']).'</a></b></td><td><b>'.$d['hits'].'</b></td></tr>';
            }

            $i=0;
            if(count($data2)) {
                print '<tr><td colspan=5><br><br><br><b>'.sprintf($_SP[78],(int)$CONFIG['referrer_value']).'</b><br><br></td>';
                while(list(,$d) = each($data2))
                    print '<tr><td align=center><b>'.(++$i).'.&nbsp;&nbsp;</b></td><td style="padding-right:20"><b><a href="http://'.$d['referrer'].'" target=_blank>'.htmlspecialchars($d['referrer']).'</a></b></td><td><b>'.$d['hits'].'</b></td></tr>';
            }
        ?>
        </table>
<?PHP

$TMP['%[CONTENT]%'] = ob_get_contents();
$TMP['%[TOP]%']     = (int)$CONFIG['referrer_value'];
ob_end_clean();

print template($LANGDIR.'referrer.htm',$TMP);

include_once('footer.php');
?>
