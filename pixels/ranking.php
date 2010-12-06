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
$VERSIONS[basename(__FILE__)] = "3.0 PRO";
$filenamenr = basename(__FILE__);
include_once('header.php');

if($BAN_DATA = DB_array("SELECT ban FROM ".$dbprefix."ban WHERE ban_top=1",'+')) {
    $bans = array();
    while(list(,$d)=each($BAN_DATA)) {
        if(strpos($d,'%')===false)  $bans[] = "url<>'".mysql_real_escape_string($d)."' AND title<>'".mysql_real_escape_string($d)."'";
        else                        $bans[] = "url NOT LIKE '".mysql_real_escape_string($d)."' AND title NOT LIKE '".mysql_real_escape_string($d)."'";
    }
    $andBAN   = 'AND ('.implode(' AND ',$bans).')';
}
$data1 = DB_array("SELECT url,title,hits,t2.userid,COUNT(*) AS hits FROM ".$dbprefix."ip t1 LEFT JOIN ".$dbprefix."user t2 USING(userid) WHERE submit IS NOT NULL AND dailydatum=SUBDATE(CURDATE(), INTERVAL 1 DAY) $andBAN $IN_GRIDS GROUP BY t1.userid ORDER BY hits DESC LIMIT ".(int)$CONFIG['ranking_value'],'*');
$data2 = DB_array("SELECT url,title,hits,userid FROM ".$dbprefix."user WHERE submit IS NOT NULL AND hits>0 $andBAN $IN_GRIDS ORDER BY hits DESC LIMIT ".(int)$CONFIG['ranking_value'],'*');

ob_start();
?>
        <table cellspacing=5>
        <?php
            if(count($data1)) {
                print '<tr><td colspan=5><b>'.sprintf($_SP[29],(int)$CONFIG['ranking_value']).'</b><br><br></td>';
                while(list(,$d) = each($data1)) {
                    $url       = parse_url($d['url']);
                    $urldomain = str_replace('www.','',$url['host']);
                    print '<tr><td align=center'.$col.'><b>'.(++$i).'.&nbsp;&nbsp;</b></td><td style="padding-right:20"><img src="sp.php?u='.$d['userid'].'"></td><td style="padding-right:20"'.$col.'><b><a href="'.$d['url'].'" target=_blank>'.$urldomain.'</a></b></td><td style="padding-right:20;"'.$col.'>'.htmlspecialchars(stripslashes($d['title'])).'</td></tr>';
                }
            }

            $i=0;
            if(count($data2)) {
                print '<tr><td colspan=5><br><br><br><b>'.sprintf($_SP[30],(int)$CONFIG['ranking_value']).'</b><br><br></td>';
                while(list(,$d) = each($data2)) {
                    $url       = parse_url($d['url']);
                    $urldomain = str_replace('www.','',$url['host']);
                    print '<tr><td align=center'.$col.'><b>'.(++$i).'.&nbsp;&nbsp;</b></td><td style="padding-right:20"><img src="sp.php?u='.$d['userid'].'"></td><td style="padding-right:20"'.$col.'><b><a href="'.$d['url'].'" target=_blank>'.$urldomain.'</a></b></td><td style="padding-right:20;"'.$col.'>'.htmlspecialchars(stripslashes($d['title'])).'</td></tr>';
                }
            }
        ?>
        </table>
<?PHP
$TMP['%[CONTENT]%'] = ob_get_contents();
$TMP['%[TOP]%']     = (int)$CONFIG['ranking_value'];
ob_end_clean();

print template($LANGDIR.'ranking.htm',$TMP);

include_once('footer.php');
?>
