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
*
*   THIS FILE IS CHECKING THE EXPIRATION OF PIXELS AND DELETE THEM
*   YOU CAN CALL THIS FILE DAILY BY A CRON JOB (IN THIS CASE SET IN ADMIN AREA THE
*   CONFIG VALUE DAILY TO 'INTERNAL') OR IT WILL BE INCLUDED EACH DAY THRUE THE INDEX.PHP
*   FILE OF YOUR WEBSITE.
*
******************************************************************************************/
    @ini_set('include_path',".");
    include_once('incs/functions.php');
    $VERSIONS[basename(__FILE__)] = "3.0 PRO";

    // Alte IP Einträge nach 7 Tagen löschen
    DB_query("DELETE FROM ".$dbprefix."ip WHERE ADDDATE(dailydatum,INTERVAL 7 DAY)<CURDATE()",'#');


    // Pre-Einträge löschen, die nicht aktiviert wurden und Map renew
    if($deleted_gridids = DB_array("SELECT gridid FROM ".$dbprefix."user WHERE submit IS NULL AND DATE_ADD(regdat, INTERVAL ".(int)$CONFIG['delete_days']." DAY) < NOW() AND enddate IS NULL GROUP BY gridid",'#')) {

        if($abg_prepixel = DB_array("SELECT userid,bildext FROM ".$dbprefix."user WHERE submit IS NULL AND DATE_ADD(regdat, INTERVAL ".(int)$CONFIG['delete_days']." DAY) < NOW() AND enddate IS NULL",'*')) {
                while(list(,$abg_ppixel) = each($abg_prepixel)) {
                    @unlink("grids/u".$abg_ppixel['userid'].'.png');
                    @unlink("grids/u".$abg_ppixel['userid'].'_orig'.$abg_ppixel['bildext']);
                }
        }

        // Jetzt die Pixeleinträge löschen
        DB_query("DELETE FROM ".$dbprefix."user WHERE submit IS NULL AND DATE_ADD(regdat, INTERVAL ".(int)$CONFIG['delete_days']." DAY) < NOW() AND enddate IS NULL",'#');

        while(list(,$delvar) = each($deleted_gridids)) {
            makemap(false,false,$delvar);
        }
    }

    // Abgelaufene Pixel löschen
    @set_time_limit(60);
    if($ABG_GRIDS = DB_array("SELECT expire_days,gridid FROM ".$dbprefix."grids WHERE active=1 AND expire_days>0",'*')) {
        while(list(,$abg_gr) = each($ABG_GRIDS)) {
            if($abg_pixel = DB_array("SELECT * FROM ".$dbprefix."user WHERE ((DATE_ADD(submit, INTERVAL ".$abg_gr['expire_days']." DAY) < NOW() AND enddate IS NULL) OR enddate<CURDATE()) AND gridid='".$abg_gr['gridid']."'",'*')) {

                // Löschen
                DB_query("DELETE FROM ".$dbprefix."user WHERE ((DATE_ADD(submit, INTERVAL ".$abg_gr['expire_days']." DAY) < NOW() AND enddate IS NULL) OR enddate<CURDATE()) AND gridid='".$abg_gr['gridid']."'",'#');
                makemap(false,false,$abg_gr['gridid']);

                while(list(,$abg_val) = each($abg_pixel)) {
                    // Bilder löschen
                    @unlink("grids/u".$abg_val['userid'].'.png');
                    @unlink("grids/u".$abg_val['userid'].'_orig'.$abg_val['bildext']);

                    // Sprache checken
                    if($abg_val['lang'] != $CONFIG['standard_language'] ) {
                        if(!$active_languages)
                            $active_languages = DB_array("SELECT code FROM ".$dbprefix."languages WHERE active=1",'+');

                        $abg_val['lang'] = (in_array($abg_val['lang'],$active_languages)) ? $abg_val['lang'] : $CONFIG['standard_language'];
                    }
                    $abg_tmp['%[URL]%']           = $abg_val['url'];
                    $abg_tmp['%[VISITORS]%']      = $abg_val['hits'];
                    $abg_tmp['%[EXPIRE_DAYS]%']   = $abg_gr['expire_days'] ? $abg_gr['expire_days'] : '';
                    $abg_tmp['%[EXPIRE_MONTHS]%'] = $abg_gr['expire_days'] ? (int)($abg_gr['expire_days']/30)  : '';
                    $abg_tmp['%[EXPIRE_YEARS]%']  = $abg_gr['expire_days'] ? (int)($abg_gr['expire_days']/365) : '';
                    sendmail($abg_val['email'],template('lang/'.$abg_val['lang'].'/mail_deleted.txt',$abg_tmp),'','"'.$CONFIG['domainname'].'" <'.$CONFIG['email_webmaster'].'>');
                }
            }
        }
    }


    // Jobs includieren
    @include_once('jobcalls.php');

    // Letztes Ausführungsdatum speichern, damit das File hier nicht jedesmal aufgerufen wird.
    DB_query("UPDATE ".$dbprefix."config SET daily_date='".$daily_date."'",'#');
?>
