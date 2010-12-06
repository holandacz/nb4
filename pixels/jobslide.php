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
include('incs/functions.php');
$VERSIONS[basename(__FILE__)] = "3.0";

if(!(int)$_GET['js']) exit();
elseif($job = DB_query("SELECT * FROM ".$dbprefix."jobs WHERE job_type=2 AND job_active IS NOT NULL AND job_every_seconds>0 AND job_id='".(int)$_GET['js']."'",'*')) {
    if((int)$job['job_gridpayed']>0) {
        $jobgrid = DB_query("SELECT * FROM ".$dbprefix."grids t1 LEFT JOIN ".$dbprefix."user t2 ON(t1.gridid=t2.gridid) WHERE t2.userid IS NOT NULL AND active=1 AND blockprice>0 ORDER BY RAND() LIMIT 1",'*');
        $job['job_gridid'] = $jobgrid['gridid'];
    }
    $job_gridid = (int)$job['job_gridid']>0 ? "AND gridid='".$job['job_gridid']."'" : '';
    if($popups = DB_query("SELECT * FROM ".$dbprefix."user WHERE submit IS NOT NULL $job_gridid ORDER BY RAND() LIMIT 1",'*'))
        $popupurl = $jobgrid['track_clicks'] ? 'index.php?u='.$popups['userid'] : strip_tags($popups['url']);

} else exit();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$popups['url']?></title>
</head>

<body marginheight="0" marginwidth="0" topmargin="0" leftmargin="0" onMouseover="stop=true" onMouseout="stop=false;window.setTimeout('Refresh()', <?=(int)$job['job_every_seconds']*1000?>)">
<iframe src="<?=$popupurl?>" width="100%" height="100%" name="popupurl" scrolling="yes" marginheight="0" marginwidth="0" frameborder="0"></iframe>
</body>
</html>
<script type="text/javascript">
    var stop = false;
    function Refresh() {
        if(stop==false) location.reload();
    }
    window.setTimeout("Refresh()", <?=(int)$job['job_every_seconds']*1000?>);
</script>
