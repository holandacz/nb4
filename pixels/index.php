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

    // Daily Aktionen (wenn nicht über Cron)
    if($CONFIG['daily'] && $CONFIG['daily_date']!=date("Y-m-d")) {
        $daily_date = date("Y-m-d");
        include_once('daily.php');
    }

    //------------------------------------------------------------------------------------------------------------------
    // Url-Aufruf (Tracking)?
    if((int)$_GET['u']) {
        if($ENTRY = DB_Query("SELECT * FROM ".$dbprefix."user t0 LEFT JOIN ".$dbprefix."grids t1 ON(t0.gridid=t1.gridid) WHERE userid='".(int)$_GET['u']."'",'*')) {

            // Hits inkrementieren
            if($ENTRY['track_clicks']) {
                if($sesshit = getenv("REMOTE_ADDR")) {
                    if(!db_query("SELECT ip FROM ".$dbprefix."ip WHERE dailydatum=CURDATE() AND ip='$sesshit' AND system='".mysql_real_escape_string(getenv("HTTP_USER_AGENT"))."' AND userid='".$ENTRY['userid']."'",'ip')) {
                        Db_query("INSERT INTO ".$dbprefix."ip SET dailydatum=CURDATE(),ip='$sesshit',system='".mysql_real_escape_string(getenv("HTTP_USER_AGENT"))."',userid='".$ENTRY['userid']."'",'#');
                        DB_query("UPDATE ".$dbprefix."user SET hits=hits+1 WHERE userid='".$ENTRY['userid']."'",'#');

                    } elseif(!$ENTRY['unique_clicks']) {
                        DB_query("UPDATE ".$dbprefix."user SET hits=hits+1 WHERE userid='".$ENTRY['userid']."'",'#');
                    }
                }

                // Grid-Refresh
                if($gridlasttime = @filemtime('grids/grid_'.$ENTRY['gridid'].'.'.$iformat[$ENTRY['image_format']])) {
                    if(($ENTRY['grid_refresh']==='0' && $gridlasttime < time()-86400) || ($ENTRY['grid_refresh']>0 && $gridlasttime < time()-(3600*$ENTRY['grid_refresh']))) {
                        makemap(false,'',$ENTRY['gridid']);
                    }
                }

            }
            if($ENTRY['popup'] && empty($ENTRY['url'])) {
                $tmp['%[URL]%']               = htmlspecialchars(stripslashes($ENTRY['url']));
                $tmp['%[URL_LINKED]%']        = '<a href="'.$ENTRY['url'].'" target="_blank">'.$ENTRY['url'].'</a>';
                $tmp['%[EMAIL]%']             = htmlspecialchars(stripslashes($ENTRY['email']));
                $tmp['%[EMAIL_LINKED]%']      = '<a href="mailto:'.$ENTRY['email'].'">'.$ENTRY['email'].'</a>';
                $tmp['%[IMAGE_ORIG_URL]%']    = 'grids/u'.$ENTRY['userid'].'_orig'.$ENTRY['bildext'];
                $tmp['%[IMAGE_ORIG]%']        = '<img src="grids/u'.$ENTRY['userid'].'_orig'.$ENTRY['bildext'].'">';
                $tmp['%[IMAGE_PIXEL_URL]%']   = 'grids/u'.$ENTRY['userid'].'.png';
                $tmp['%[IMAGE_PIXEL]%']       = '<img src="grids/u'.$ENTRY['userid'].'.png">';
                $tmp['%[TITLE]%']             = htmlspecialchars(stripslashes($CONFIG['website_title']));
                $tmp['%[TEXT]%']              = htmlspecialchars(stripslashes($ENTRY['title']));
                $tmp['%[HITS]%']              = (int)$ENTRY['hits'];
                print template($LANGDIR.'grid_'.$ENTRY['gridid'].'_popup.htm',$tmp);
            } else {
                header("Location: ".$ENTRY['url']);
            }

        } else {
            header("Location: ".$DOMAIN);
        }
        exit;
    }


    //------------------------------------------------------------------------------------------------------------------
    // Referrer
    $referrer = strtolower($_SERVER['HTTP_REFERER']);
    if($referrer != '' && $sesshit = getenv("REMOTE_ADDR")) {
        $ref       = parse_url($referrer);
        $refdomain = str_replace('www.','',$ref['host']);

        if($refdomain!=str_replace('http://','',str_replace('www.','',$DOMAINNAME))) {
            // Referrerid aus Datenbank
            if(!$refid = DB_query("SELECT refid FROM ".$dbprefix."referrer WHERE referrer='".mysql_real_escape_string($refdomain)."'",'refid')) {
                if($refid = DB_query("INSERT INTO ".$dbprefix."referrer SET referrer='".mysql_real_escape_string($refdomain)."',hits=1",'##'))
                    DB_query("INSERT INTO ".$dbprefix."ip SET dailydatum=CURDATE(),ip='$sesshit',system='".mysql_real_escape_string(getenv("HTTP_USER_AGENT"))."',refid='".$refid."'",'#');

            // Hits inkrementieren
            } elseif(!DB_query("SELECT ip FROM ".$dbprefix."ip WHERE dailydatum=CURDATE() AND ip='$sesshit' AND system='".mysql_real_escape_string(getenv("HTTP_USER_AGENT"))."' AND refid='".$refid."'",'ip')) {
                DB_query("UPDATE ".$dbprefix."referrer SET hits=hits+1 WHERE refid='".$refid."'",'#');
                DB_query("INSERT INTO ".$dbprefix."ip SET dailydatum=CURDATE(),ip='$sesshit',system='".mysql_real_escape_string(getenv("HTTP_USER_AGENT"))."',refid='".$refid."'",'#');
            }
        }
    }



    //------------------------------------------------------------------------------------------------------------------
    // Aktivierung
    if($_GET['a']) {
        include_once("header.php");
        if($activateinfo = DB_query("SELECT * FROM ".$dbprefix."user t0 LEFT JOIN ".$dbprefix."grids t1 ON(t0.gridid=t1.gridid) WHERE uniqueid='".mysql_real_escape_string($_GET['a'])."'",'*')) {

            if($activateinfo['expire_days']) $enddate = ",enddate=CURDATE()+INTERVAL ".(int)$activateinfo['expire_days']." DAY";

            // Vorkontrolle
            if($activateinfo['precontrol']) {
                print template($LANGDIR.'submitted.htm');
                $sendinfomail = true;

            } elseif(DB_query("UPDATE ".$dbprefix."user SET submit=NOW() $enddate WHERE uniqueid='".mysql_real_escape_string($_GET['a'])."'",'#')) {
                print template($LANGDIR.'submitted.htm');
                makemap(false,false,$activateinfo['gridid']);
                $sendinfomail = true;

            } elseif(!DB_query("SELECT userid FROM ".$dbprefix."user WHERE uniqueid='".mysql_real_escape_string($_GET['a'])."'",'userid')) {
                print template($LANGDIR.'submiterror.htm');
            }
        } else {
            include_once('grids.php');
        }
        include_once("footer.php");

        // Zugangsdaten an User verschicken falls erlaubt
        if($activateinfo['editpixel'] && $activateinfo['logincode']) {
            $tmp['%[USERID]%']    = $activateinfo['userid'];
            $tmp['%[LOGINCODE]%'] = $activateinfo['logincode'];
            sendmail($activateinfo['email'],template($LANGDIR.'mail_sendpass.txt',$tmp),'','"'.$CONFIG['domainname'].'" <'.$CONFIG['email_webmaster'].'>');
        }
        // Mail verschicken an Admin falls gewünscht
        if($activateinfo['adminmail'] && $sendinfomail) {
            $tmp['%[TITLE]%']    = stripslashes($activateinfo['title']);
            $tmp['%[EMAIL]%']    = stripslashes($activateinfo['email']);
            $tmp['%[BLOCKS]%']   = $activateinfo['kaesten'];
            $tmp['%[LANGUAGE]%'] = $activateinfo['lang'];
            $tmp['%[USERID]%']   = $activateinfo['userid'];
            $tmp['%[URL]%']      = $activateinfo['url'];
            $tmp['%[GRID]%']     = $activateinfo['name'];
            $tmp['%[B]%']        = '-';
            $tmp['%[STATUS]%']   = $_SP[69];
            sendmail($CONFIG['email_webmaster'],template('control/lang/mail_admin_pixelinfo_'.$CONFIG['admin_language'].'.txt',$tmp),'','"'.$CONFIG['domainname'].'" <'.$CONFIG['email_webmaster'].'>');
        }

        exit;



    //------------------------------------------------------------------------------------------------------------------
    // Löschen durch User
    } elseif($_GET['k'] && $_GET['i']) {
        if(!DB_Query("SELECT userid FROM ".$dbprefix."user WHERE userid='".(int)$_GET['i']."' AND uniqueid='".mysql_real_escape_string($_GET['k'])."'",'userid')) {
            include_once("header.php");
            print template($LANGDIR.'killerror.htm');
            include_once("footer.php");

        // Ok, Sicherheitsbestätigung
        } else {
            include_once("header.php");
            include_once("kill.php");
            include_once("footer.php");
        }
        exit;
    }


    // Sonst normale Anzeige
    include_once("header.php");
    include_once("grids.php");
	echo "</div>";
    include_once("footer.php");

?>