<?php
/******************************************************************************************
*   Million Pixel Script (R)
*   (C) 2006 by texmedia.de, all rights reserved.
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
$VERSIONS[basename(__FILE__)] = "3.01";

    if ($_JOBS = DB_array("SELECT * FROM ".$dbprefix."jobs WHERE job_type=1 AND job_active=1 AND (DATE_FORMAT(job_laststart,'%Y-%m-%d') < CURDATE() || job_laststart IS NULL) AND (job_date=CURDATE() OR CONCAT_WS('-',YEAR(NOW()),job_every_month,job_every_day)=CURDATE() OR (WEEKDAY(NOW())+1=job_every_weekday OR (WEEKDAY(NOW())=6 AND job_every_weekday='0') ) )",'*')) {

        // Job-Type 1:
        while (list(,$job) = each($_JOBS)) {

           // Erst altes Ergebnis löschen
           DB_query("UPDATE ".$dbprefix."jobs SET job_selected_userid=NULL,job_selected_field=NULL,job_selected_gridid=NULL,job_selected_position=NULL WHERE job_id='".$job['job_id']."'",'#');

           // Zufalls Pixel ------------------------------------------------------------------------------------------------------------------
            if($job['job_type']==1) {
                if($job['job_fieldused']) {
                    $jobs_gridid        = (int)$job['job_gridid']>0        ? "AND t1.gridid='".$job['job_gridid']."'" : '';
                    $jobs_gridpayed     = (int)$job['jobs_gridpayed']>0 ? "AND t2.blockprice>0" : '';
                    $jobs_random_field  = DB_query("SELECT * FROM ".$dbprefix."user t1 LEFT JOIN ".$dbprefix."grids t2 ON(t1.gridid=t2.gridid) WHERE t1.submit IS NOT NULL AND t2.active=1 $jobs_gridid $jobs_gridpayed ORDER BY RAND() LIMIT 1",'*');
                    if(is_array($jobs_random_field['felder'])) {
                        $jobs_random_fields  = explode(',',$jobs_random_field['felder']);
                        $randomdummy         = array_rand($jobs_random_fields);
                        $jobs_random_fieldnr = $jobs_random_fields[$randomdummy];
                    } else {
                        $jobs_random_fieldnr = $jobs_random_field['felder'];
                    }
                    $jobs_random_gridid      = $jobs_random_field['gridid'];
                    $jobs_random_blocksize_x = ($jobs_random_field['grid_type']) ? 10 : $jobs_random_field['blocksize_x'];
                    $jobs_random_blocksize_y = ($jobs_random_field['grid_type']) ? 10 : $jobs_random_field['blocksize_y'];
                    $jobs_random_x_plus      = ($jobs_random_field['grid_type']) ? $gridsizes[$jobs_random_field['grid_type']]['x+'] : 0;
                    $jobs_random_y_plus      = ($jobs_random_field['grid_type']) ? $gridsizes[$jobs_random_field['grid_type']]['y+'] : 0;

                } else {
                    $jobs_gridpayed   = (int)$job['jobs_gridpayed']>0 ? "AND blockprice>0" : '';
                    if((int)$job['job_gridid']>0) $jobs_random_grid = DB_query("SELECT * FROM ".$dbprefix."grids WHERE active=1 AND gridid='".$job['job_gridid']."' $jobs_gridpayed",'*');
                    else                          $jobs_random_grid = DB_query("SELECT * FROM ".$dbprefix."grids WHERE active=1 $jobs_gridpayed ORDER BY RAND() LIMIT 1",'*');
                    $jobs_random_fieldnr     = ($jobs_random_grid['grid_type']) ? (int)rand(1,$gridsizes[$jobs_random_grid['grid_type']]['pix']) : (int)rand(1,$jobs_random_grid['x']*$jobs_random_grid['y']);
                    $jobs_random_gridid      = $jobs_random_grid['gridid'];
                    $jobs_random_blocksize_x = ($jobs_random_grid['grid_type']) ? 10 : $jobs_random_grid['blocksize_x'];
                    $jobs_random_blocksize_y = ($jobs_random_grid['grid_type']) ? 10 : $jobs_random_grid['blocksize_y'];
                    $jobs_random_x_plus      = ($jobs_random_grid['grid_type']) ? $gridsizes[$jobs_random_grid['grid_type']]['x+'] : 0;
                    $jobs_random_y_plus      = ($jobs_random_grid['grid_type']) ? $gridsizes[$jobs_random_grid['grid_type']]['y+'] : 0;

                    // Usertreffer?
                    $jobs_random_field = DB_query("SELECT * FROM ".$dbprefix."user WHERE '$jobs_random_fieldnr' IN(felder) AND gridid='".$jobs_random_grid['gridid']."' AND submit IS NOT NULL",'*');
                }

                if($jobs_random_fieldnr) {
                    $jobs_posx = ((int)(($jobs_random_fieldnr-1)/100)*$jobs_random_blocksize_x)+$jobs_random_x_plus;
                    $jobs_posy = (fsubstr($jobs_random_fieldnr-1,-2)*$jobs_random_blocksize_y)+$jobs_random_y_plus;
                    $jobs_positions = $jobs_posx."/".$jobs_posy;
                }

                $job_inactive_now = $job['job_date'] ? 'job_active=0,' : '';
                DB_query("UPDATE ".$dbprefix."jobs SET ".$job_inactive_now."job_laststart=NOW(),job_selected_userid='".$jobs_random_field['userid']."',job_selected_field=".(int)$jobs_random_fieldnr.",job_selected_gridid='".$jobs_random_gridid."',job_selected_position='".$jobs_positions."' WHERE job_id='".$job['job_id']."'",'#');

                $tmp['%[GRIDID]%']  = $jobs_random_gridid;
                $tmp['%[FIELD]%']   = $jobs_random_fieldnr;
                $tmp['%[POS_X]%']   = $jobs_posx;
                $tmp['%[POS_Y]%']   = $jobs_posy;

                // Mail an User
                if ($job['job_email_user'] && $jobs_random_field['email']) {
                    // Sprache checken
                    if($jobs_random_field['lang'] != $CONFIG['standard_language'] ) {
                        if(!$active_languages) $active_languages = DB_array("SELECT code FROM ".$dbprefix."languages WHERE active=1",'+');
                        $jobs_random_field['lang'] = (in_array($jobs_random_field['lang'],$active_languages)) ? $jobs_random_field['lang'] : $CONFIG['standard_language'];
                    }
                    sendmail($jobs_random_field['email'],template('lang/'.$jobs_random_field['lang'].'/'.$job['job_email_user'].'',$tmp),'','"'.$CONFIG['domainname'].'" <'.$CONFIG['email_webmaster'].'>');
                }
                // Mail an Admin
                if ($job['job_email_admin']) {
                    $tmp['%[USERID]%']   = $jobs_random_field['userid'];
                    $tmp['%[EMAIL]%']    = $jobs_random_field['email'];
                    $tmp['%[URL]%']      = $jobs_random_field['url'];
                    $tmp['%[JOBNAME]%']  = $job['job_name'];
                    sendmail($CONFIG['email_webmaster'],template('control/lang/mail_admin_jobinfo_field_'.$CONFIG['admin_language'].'.txt',$tmp),'','"'.$CONFIG['domainname'].'" <'.$CONFIG['email_webmaster'].'>');
                }
            }
        }
    }
    unset($JOBS);
?>