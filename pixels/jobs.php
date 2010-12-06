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
$VERSIONS[basename(__FILE__)] = "3.0";
if(eregi(basename(__FILE__), $HTTP_SERVER_VARS[REQUEST_URI]))
    die ("You can't access this file directly! Please go to the startpage!");

$JOB_IN_GRIDS = $IN_GRIDS ? "AND ( job_gridid IN(".implode(',',$gridids).") OR job_gridid=0 )" : '';
if($ACTIVE_JOBS = DB_array("SELECT * FROM ".$dbprefix."jobs WHERE (( job_type=1 AND job_laststart IS NOT NULL AND job_fieldhighlight>0 AND ADDDATE(job_laststart, INTERVAL job_fieldhighlight HOUR) >= NOW() )"
                                                                 ." OR ( job_type>1 AND job_active=1 )"
                                                                 .") $JOB_IN_GRIDS",'*')) {
    $SHOW_JOB = $SHOW_POPUP = array();
    $popupcounter = 0;
    while(list(,$job) = each($ACTIVE_JOBS)) {
        // Zufallsfeld anzeigen
        if($job['job_type']==1) {
            $SHOW_JOB[$job['job_selected_gridid']]['field']  = $job['job_selected_field'];
            $SHOW_JOB[$job['job_selected_gridid']]['xy']     = $job['job_selected_position'];
            $SHOW_JOB[$job['job_selected_gridid']]['userid'] = $job['job_selected_userid'];
            $SHOW_JOB[$job['job_selected_gridid']]['selfw']  = $job['job_selfwindow'] ? '' : 'target="_blank"';

            if($job['job_url']) {
                // Platzhalter
                $job['job_url'] = str_replace('[url]',urlencode($job_userdata['url']),$job['job_url']);
                $job['job_url'] = str_replace('[title]',urlencode($job_userdata['title']),$job['job_url']);
                $job['job_url'] = str_replace('[userid]',urlencode($job_userdata['userid']),$job['job_url']);
                $SHOW_JOB[$job['job_selected_gridid']]['url']    = '<a href="'.$job['job_url'].'" '.$SHOW_JOB[$job['job_selected_gridid']]['selfw'].'>';
           } elseif((int)$job['job_selected_userid']>0) {
                $job_userdata = DB_query("SELECT * FROM ".$dbprefix."user  WHERE userid='".$job['job_selected_userid']."'",'*');
                $job_griddata = $GRID[$job_userdata['gridid']];

                $href  = $job_griddata['track_clicks'] ? '?u='.$job_userdata['userid'] : $job_userdata['url'];
                $hits  = $job_griddata['track_clicks'] && $job_griddata['show_clicks'] ? ' ('.$job_userdata['hits'].')' : '';
                $title = $job_griddata['show_box'] ? ' onmouseover="return escape(\''.htmlentities(($job_userdata['title'])).$hits.'\')"' : 'title="'.htmlentities(stripslashes($job_userdata['title'])).$hits.'"';
                $blank = $job_griddata['new_window'] ? ' target="_blank"' : '';

                $SHOW_JOB[$job['job_selected_gridid']]['url']    = '<a href="'.$href.'" '.$title.$blank.'>';
            }

        // Benutzer-URL aufrufen
        } elseif($job['job_type']==2 AND ($job['job_show']==0 OR $job['job_show']>(int)$_SESSION['job_show'][$job['job_id']]) ) {
            if($job['job_show']>0)    $_SESSION['job_show'][$job['job_id']]++;
            if($job['job_gridid']==0) $job['job_gridid'] = $gridids[0];
            $SHOW_POPUP[$popupcounter]['url']      = 'jobslide.php?js='.$job['job_id'];
            $SHOW_POPUP[$popupcounter]['gridid']   = $job['job_gridid'];
            $SHOW_POPUP[$popupcounter++]['scroll'] = true;

        // Vorgegebene URL aufrufen
        } elseif($job['job_type']==3 AND ($job['job_show']==0 OR $job['job_show']>(int)$_SESSION['job_show'][$job['job_id']]) ) {
            if($job['job_show']>0)    $_SESSION['job_show'][$job['job_id']]++;
            if($job['job_gridid']==0) $job['job_gridid'] = $gridids[0];
            $SHOW_POPUP[$popupcounter]['url']      = strip_tags($job['job_url']);
            $SHOW_POPUP[$popupcounter]['gridid']   = $job['job_gridid'];
            $SHOW_POPUP[$popupcounter++]['scroll'] = false;
        }
    }
}
?>