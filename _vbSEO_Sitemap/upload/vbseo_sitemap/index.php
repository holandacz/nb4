<?php 

 /******************************************************************************************
 * vBSEO Google/Yahoo Sitemap Generator for vBulletin v3.x.x by Crawlability, Inc.         *
 *-----------------------------------------------------------------------------------------*
 *                                                                                         *
 * Copyright © 2005-2008, Crawlability, Inc. All rights reserved.                          *
 * You may not redistribute this file or its derivatives without written permission.       *
 *                                                                                         *
 * Sales Email: sales@crawlability.com                                                     *
 *                                                                                         *
 *-------------------------------------LICENSE AGREEMENT-----------------------------------*
 * 1. You are free to download and install this plugin on any vBulletin forum for which    *
 *    you hold a valid vB license.                                                         *
 * 2. You ARE NOT allowed to REMOVE or MODIFY the copyright text within the .php files     *
 *    themselves.                                                                          *
 * 3. You ARE NOT allowed to DISTRIBUTE the contents of any of the included files.         *
 * 4. You ARE NOT allowed to COPY ANY PARTS of the code and/or use it for distribution.    *
 ******************************************************************************************/

define('CSRF_PROTECTION', true);
define('SKIP_SESSIONCREATE', 1);
define('NOCOOKIES', 1);
define('THIS_SCRIPT', 'login');

error_reporting(E_ALL & ~E_NOTICE);
require dirname(__FILE__).'/vbseo_sitemap_config.php';

chdir('../');
$globaltemplates = $phrasegroups = $specialtemplates = array();
include getcwd().'/global.'.VBSEO_PHP_EXT;
if(!isset($config))
{
	include getcwd().'/includes/config.'.VBSEO_PHP_EXT;
}
require_once(dirname(__FILE__). '/vbseo_sitemap_functions.php');


if(isset($_POST['runcode']))
{
	setcookie('runcode',md5($_POST['runcode']));
    $_COOKIE['runcode'] = md5($_POST['runcode']);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>vBSEO Google / Yahoo Sitemap for vBulletin</title>

<style type="text/css">
BODY {
	FONT-SIZE: 11px; MARGIN: 10px; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; height:100%
}
TABLE {
	FONT-SIZE: 11px; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif
}
#nav {
	margin:20px
}
A:link {
	COLOR: #003399; TEXT-DECORATION: underline
}
A:visited {
	COLOR: #003399; TEXT-DECORATION: underline
}
A:active {
	COLOR: #003399; TEXT-DECORATION: underline
}
A:hover {
	COLOR: #003399; TEXT-DECORATION: none
}
.formtbl {
	BACKGROUND-COLOR: #000000
}
.header {
	COLOR: #ffffff; BACKGROUND-COLOR: #465786; font-weight:bold;
}
.subheader {
	COLOR: #ffffff; BACKGROUND-COLOR: #6E7A9A
}
.altfirst {
	BACKGROUND-COLOR: #ffffff
}
.altsecond {
	BACKGROUND-COLOR: #f0f0f0
}
ul {
	margin-top:5px; margin-bottom:5px
}
#jump {
	margin:5px
}
h1 {
	font-size: 140%; margin:5px
}
h2 {
	font-size: 110%; margin:5px
}
</style>
</head>
<body>
<table style="width:100%; "><tr><td valign="top" height="450">
<table cellpadding="3" style="width:100%; BACKGROUND: #eaeaea; COLOR: #6f7877; FONT-SIZE: 11px; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;">
  <tr>
    <td align="left"><strong>vBSEO Google / Yahoo Sitemap for vBulletin - <a href="index.php">Sitemap Reports Home</a><?php  if($logged_in){?> | <a href="../<?php echo $config['Misc']['admincpdir']?>/">vBulletin Admin CP</a><?php }?></strong></td>
    <td align="right"><?php  if($logged_in){?>
&nbsp;| <a style="COLOR: #003399; TEXT-DECORATION: underline;" href="vbseocp.php?logout=true">Logout</a>
<?php  }?>

    </td>
  </tr>
</table>


<?php 

if($_COOKIE['runcode'] != md5($vboptions['vbseo_sm_runcode']))
{
?>
<form action="index.php" method=POST name="loginform">
              <table border="0" cellspacing="0" cellpadding="4" align="center">
                <tr>
                  <td align="center"><strong>This area is password protected.
<?php      
	if(isset($_POST['runcode']) && $_POST['runcode'] != $vboptions['vbseo_sm_runcode'])
	{
		echo '<br/><br/><font color="#ff0000">Login failed</font>';
	}
?>                          
                  
                  </strong></td>
                </tr>
                <tr align="center">
                  <td>Enter your "vBSEO Sitemap Interface Access Password" from admincp:
                  	<br/><input type=password name="runcode" size=15>
                          <input type="hidden" name="login" value="1">
				  		  <input type="hidden" name="securitytoken" value="<?php echo $bbuserinfo[securitytoken];?>" />
                          <input type="submit" name="submit" value="Login">
                          
                          </td>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
  document.forms['loginform'].runcode.focus();
</script>
	
                  </tr>
              </table>
</form>
<?php 
}else
{
	vbseo_sm_prune(VBSEO_DAT_FOLDER);
	vbseo_sm_prune(VBSEO_DAT_FOLDER_BOT);
?>
<br/>
<div id="jump"><strong>Useful Links</strong></div>
<ul>
<li><strong><a href="index.php?rlist=true">Show Reports List</a></strong></li>
<li><a href="index.php?dlist=true">Show Sitemap Downloads Log</a></li>
<li><a href="index.php?hits=true">Show SE Bots Activity Log</a> (<a href="http://www.vbseo.com">vBSEO</a> v2.4.1 or higher is required)</li>
<li><a href="vbseo_sitemap.php" onclick="return confirm('Are you sure?')">Run Sitemap Generator</a></li>
<li><a href="<?php echo $vboptions['bburl']?>">Your Forums Homepage: <?php echo $vboptions['bbtitle']?></a></li>
<li>Your Google Sitemap Index URL: <a href="<?php echo vbseo_sitemap_furl(vbseo_file_gz('sitemap_index.xml'))?>"><?php echo vbseo_sitemap_furl(vbseo_file_gz('sitemap_index.xml'))?></a><br />&nbsp;</li>

<li><a href="http://www.google.com/webmasters/sitemaps" target="_blank">Your Google Sitemaps Page</a></li>
<li><a href="https://www.google.com/webmasters/sitemaps/docs/en/faq.html" target="_blank">Google Sitemap FAQ</a></li>
<li><a href="http://submit.search.yahoo.com/free/request/" target="_blank">Submit Yahoo Sitemap</a></li>
<li><a href="http://www.vbseo.com" target="_blank">vBSEO forums</a></li>
<?php
if(!VBSEO_ON)
{
?>
<script language="Javascript" type="text/javascript" src="http://www.crawlability.com/js/vbseo_menu.js"></script>
<?php
}
?>

</ul>



<br/><br/>
<?php 

	function vbseo_get_loglist($folder = VBSEO_DAT_FOLDER)
	{
		global $log_list;

    	$log_list = array();
    	$pd = @opendir($folder);
    	while($fn = @readdir($pd))
    	if(strstr($fn, '.log'))
    		$log_list[] = $fn;
    	@closedir($pd);
    	sort($log_list);
    }

	function vbseo_get_datlog($filename)
	{
		return unserialize(implode('', file($filename)));
	}

	function vbseo_get_dllog()
	{
		global $dl_log;
		$dl_log = VBSEO_DAT_FOLDER.'downloads.dat';
   		$dl_list = file_exists($dl_log) ? vbseo_get_datlog($dl_log) : array();
   		return $dl_list;
	}

	function vbseo_chg_show($new, $old)
	{
		if(!isset($old) || !isset($new) || ($new-$old==0) )return '-';
		return ($new-$old>0?'+':'').number_format($new-$old, 0);
	}

	function vbseo_val_show($val)
	{
		return $val ? number_format($val,0) : '-';
	}

	if(($remlist = $_GET['removelog'])||($remlist = $_POST['removelog']))
	{
		foreach($remlist as $rlog=>$rval)
		 if(preg_match('#^\d+\.log$#', $rlog))
			@unlink(VBSEO_DAT_FOLDER . $rlog);
	
		$_GET['rlist'] = true;
	}

	if(($remlist = $_GET['removedl'])||($remlist = $_POST['removedl']))
	{
		$dllist = vbseo_get_dllog();

		foreach($remlist as $ri=>$rt)
		{
    		if($dllist[$ri-1]['time']==$rt)
    			unset($dllist[$ri-1]);

     	}
   		$dllist = array_values($dllist);
      	$pf = fopen($dl_log, 'w');
      	fwrite($pf, serialize($dllist));
      	fclose($pf);
		$_GET['dlist'] = true;
	}

	function vbseo_sm_pager($total, $pagesize, $cpage, $preurl)
	{
		$pager = '';

		for($i=0;$i*$pagesize<$total||!$i;$i++)
		$pager .= ($i+1==$cpage)?'['.($i+1).'] ':'<a href="'.$preurl.'&page='.($i+1).'">'.($i+1).'</a> ';

		return 'pages: '.$pager;
	}


	if($_GET['hits'])
	{
		vbseo_get_loglist(VBSEO_DAT_FOLDER_BOT);
	if(VBSEO_SORT_ORDER == 'desc')
		rsort($log_list);

	$cpage = $_GET['page'] ? $_GET['page'] : 1;
	$pager = vbseo_sm_pager(count($log_list), VBSEO_SM_PAGESIZE, $cpage, 'index.php?hits=true');
	$log_list = array_slice($log_list, ($cpage-1)*VBSEO_SM_PAGESIZE, VBSEO_SM_PAGESIZE);

?>
<h3>SE Bots Activity Log</h3>
<div><?php echo $pager;?></div><br />
<TABLE class="formtbl" cellSpacing="1" cellPadding="4" border=0>
<?php 
	$main_bots_list = array(
	'Googlebot', 'Yahoo', 'msnbot'
	);
?>
<TR class=header>
      <TD colSpan="<?php echo count($main_bots_list)+10?>">SE Bots Activity Log</TD>
</TR>
<tr bgcolor="#ccccff" style="border-bottom: 1px solid #000000">
	<td rowspan="2">No</td>
	<td rowspan="2">Date</td>
	<td rowspan="2">Total</td>
	<td colspan="5">Main Pages</td>
	<td colspan="<?php echo count($main_bots_list)+1?>">Main Bots</td>
	<td rowspan="2">Details</td>
</tr>
<tr bgcolor="#ccccff" style="border-bottom: 1px solid #000000">
	<td>Forum Display</td>
	<td>Show Thread</td>
	<td>Show Post</td>
	<td>Member Profile</td>
	<td>Others</td>
<?php 
	foreach($main_bots_list as $botname)
		echo "<td>$botname</td>\n";
?>
	<td>Others</td>
</tr>
<?php 


	$ln = 0;
	foreach($log_list as $ll)
	{
		$ln++;
		$stat = vbseo_get_datlog(VBSEO_DAT_FOLDER_BOT.$ll);
		$datepart = str_replace('.log', '', $ll);
?>
<tr class="<?php echo $ln%2?'altfirst':'altsecond'?>">
	<td><?php echo $ln+($cpage-1)*VBSEO_SM_PAGESIZE?></td>
	<td><?php echo substr($datepart,0,4).'-'.substr($datepart,4,2).'-'.substr($datepart,6)?></td>
	<td><?php echo vbseo_val_show($stat['all']['total'])?></td>
	<td><?php echo vbseo_val_show($stat['all']['forumdisplay.'.VBSEO_PHP_EXT])?></td>
	<td><?php echo vbseo_val_show($stat['all']['showthread.'.VBSEO_PHP_EXT])?></td>
	<td><?php echo vbseo_val_show($stat['all']['showpost.'.VBSEO_PHP_EXT])?></td>
	<td><?php echo vbseo_val_show($stat['all']['member.'.VBSEO_PHP_EXT])?></td>
	<td><?php echo vbseo_val_show($stat['all']['total'] - (
		$stat['all']['forumdisplay.'.VBSEO_PHP_EXT]+$stat['all']['showthread.'.VBSEO_PHP_EXT]+$stat['all']['showpost.'.VBSEO_PHP_EXT]+$stat['all']['member.'.VBSEO_PHP_EXT]
));?></td>
<?php 
	$mbots_tot = 0;
	foreach($main_bots_list as $botname)
	{
		$bot_hits = $stat[$botname]['total']+0;
		$mbots_tot += $bot_hits;
		echo "<td>".vbseo_val_show($bot_hits)."</td>\n";
	}
?>
	<td><?php echo vbseo_val_show($stat['all']['total'] - $mbots_tot)?></td>
	<td>
<a href="index.php?hitdetails=<?php echo $ll?>">View details</a>
	</td>
</tr>
<?php 		
	$pind = $stat['urls_no_tot'];
	}
?>
</TABLE>
<?php   
	}else
	if($_GET['hitdetails'])
	{
		$stat = vbseo_get_datlog(VBSEO_DAT_FOLDER_BOT.$_GET['hitdetails']);
		$datepart = str_replace('.log', '', $_GET['hitdetails']);
		$bots = array_keys($stat);
		function ucmp_bot($a, $b) 
		{  global $stat;if ($stat[$a]['total'] == $stat[$b]['total']) return 0; return ($stat[$a]['total'] > $stat[$b]['total']) ? -1 : 1; }

		usort($bots, "ucmp_bot");

		$pages = $stat['all'];
		arsort($pages);
?>
<h3>SE Bots Activity Details - <?php echo substr($datepart,0,4).'-'.substr($datepart,4,2).'-'.substr($datepart,6)?></h3>

<TABLE class="formtbl" cellSpacing="1" cellPadding="4" border=0>
<tr class="header" bgcolor="#ccccff" style="border-bottom: 1px solid #000000">
	<td >SE Bot / Page</td>
<?php  foreach($bots as $bot) echo "
	<td width=\"80\">$bot</td>";
?>
</tr>
<?php 
	$ln = 0;
	foreach($pages as $pg=>$cnt)
	{
?>
<tr <?php echo $pg=='total' ? ' bgcolor="#ccccff"' : 'class="'.($ln%2?'altfirst':'altsecond').'"';?>>
	<td bgcolor="#ccccff"><?php echo $pg?></td>
<?php  foreach($bots as $bot) echo "
	<td".($bot=='all' ? ' style="font-weight:bold"':'').">".vbseo_val_show($stat[$bot][$pg])."</td>";
?>
</tr>
<?php 		
	$pind = $stat['urls_no_tot'];
	}
?>
</TABLE>
<?php   
	}else
	if($_GET['details'])
	{
		vbseo_get_loglist();
		$stat = vbseo_get_datlog(VBSEO_DAT_FOLDER.$_GET['details']);
		$li = array_search($_GET['details'], $log_list);
		$stat2 = $li ? vbseo_get_datlog(VBSEO_DAT_FOLDER.$log_list[$li-1]):array();

?>
<h3>Report Details</h3>

<TABLE class="formtbl" cellSpacing="1" cellPadding="4" border=0>
<TR class=header>
      <TD colSpan="2">Generated Sitemap Details</TD>
</TR>
<tr class="altfirst">
	<td>Date</td>
	<td><b><?php echo date('Y-m-d H:i',$stat['start'])?></b></td>
</tr>
<tr class="altsecond">
	<td>Processing time</td>
	<td><?php echo number_format($stat['end']-$stat['start'],2)?> s</td>
</tr>
<tr class="altfirst">
	<td>Total URLs</td>
	<td><?php echo number_format($stat['urls_no_tot'],0)?>
	(<?php echo vbseo_chg_show($stat['urls_no_tot'],$stat2['urls_no_tot'])?>)</td>
</tr>
<?php
$urltypes = array(
'f'=>'Forumdisplay URLs',
't'=>'Showthread URLs',
'p'=>'ShowPost URLs',
'arc'=>'Archive URLs',
'm'=>'Member Profile URLs',
'poll'=>'Poll Results URLs',
'blog'=>'Blog Entries URLs',
'a'=>'Album URLs',
'g'=>'Social Group URLs',
'tag'=>'Tag URLs',
);
$stat['arc'] = $stat['af']+$stat['at'];
$stat2['arc'] = $stat2['af']+$stat2['at'];

foreach($urltypes as $t=>$desc)
{
$i++;
?>
<tr class="<?php echo $i%2 ? 'altsecond':'altfirst';?>">
	<td><?php echo $desc;?></td>
	<td><?php echo number_format($stat[$t],0)?> (<?php echo vbseo_chg_show($stat[$t],$stat2[$t])?>)</td>
</tr>
<?php 
}
?>
<TR class=header>
      <TD colSpan="2">Sitemap Files</TD>
</TR>
<tr class="altfirst">
	<td>Index File</td>
	<td><a target="_blank" href="<?php echo vbseo_sitemap_furl(vbseo_file_gz('sitemap_index.xml'))?>"><?php echo vbseo_file_gz('sitemap_index.xml');?></a></td>
</tr>
<?php  
$fn=0;
foreach($stat['files'] as $file)
{$fn++;
?>
<tr class="altfirst">
	<td>Sitemap File #<?php echo $fn?></td>
	<td><a target="_blank" href="<?php echo $file['url']?>"><?php echo basename($file['url'])?></a> (<?php echo number_format($file['urls'])?> URLs, <?php echo number_format($file['size']/1024,2)?>Kb) <span style="color:#999;"><?php echo number_format($file['uncompsize']/1024,2)?>Kb uncompressed</span></td> 
</tr>
<?php 
}?>

<tr class="altsecond">
	<td>Text Format File</td>
	<td><a target="_blank" href="<?php echo vbseo_sitemap_furl(vbseo_file_gz(VBSEO_YAHOO_SM))?>"><?php echo vbseo_file_gz(VBSEO_YAHOO_SM)?></a> (<?php echo number_format($stat['txt']['size']/1024,2)?>Kb)</td>
</tr>

<TR class=header>
      <TD colSpan="2">Search Engines Pings</TD>
</TR>
<tr class="altfirst">
	<td>Google</td>
	<td><?php echo (isset($stat['ping'])?($stat['ping']?'Successful':'FAILED'):'Disabled')?></td>
</tr>
<tr class="altfirst">
	<td>Yahoo</td>
	<td><?php echo (isset($stat['pingyahoo'])?($stat['pingyahoo']?'Successful':'FAILED'):'Disabled')?></td>
</tr>
<tr class="altfirst">
	<td>Ask</td>
	<td><?php echo (isset($stat['pingask'])?($stat['pingask']?'Successful':'FAILED'):'Disabled')?></td>
</tr>
<tr class="altfirst">
	<td>Moreover</td>
	<td><?php echo (isset($stat['pingmore'])?($stat['pingmore']?'Successful':'FAILED'):'Disabled')?></td>
</tr>

</table>
<?php 
	}else
	if($_GET['dlist'])
	{

	$dl_list = vbseo_get_dllog();
	
	if(VBSEO_SORT_ORDER == 'desc')
		$dl_list = array_reverse($dl_list);

	$cpage = $_GET['page'] ? $_GET['page'] : 1;
	$pager = vbseo_sm_pager(count($dl_list), VBSEO_SM_PAGESIZE, $cpage, 'index.php?dlist=true');
	$dl_list = array_slice($dl_list, ($cpage-1)*VBSEO_SM_PAGESIZE, VBSEO_SM_PAGESIZE);


?>
<h3>Sitemap Downloads Log</h3>
<div><?php echo $pager;?></div><br />
<a href="index.php?dlist=true&botsonly=true">Show bots records only</a>
<br/><br/>
<TABLE class="formtbl" cellSpacing="1" cellPadding="4" border="0">
<TR class=header>
      <TD colSpan="10">Sitemap Downloads Log</TD>
</TR>
<form name="selform" action="index.php" method="POST">
<script>
var selopts = new Array('<?php echo implode("','", range(1,count($dl_list)))?>')
function selectall(chk)
{
	for(var i=0;i<selopts.length;i++)
	{
		document.forms['selform'].elements['removedl['+selopts[i]+']'].checked = chk
	}
}
</script>
<tr bgcolor="#ccccff" style="border-bottom: 1px solid #000000">
	<td>No</td>
	<td>Date</td>
	<td>Sitemap File</td>
	<td>Bot</td>
	<td>IP</td>
	<td>User-agent</td>
	<td>Action</td>
	<td>
<input type="checkbox" name="rm" onclick="selectall(this.checked)" value="1">
	</td>
</tr>
<?php 


   	$dn=$dd=0;
   	foreach($dl_list as $dl)
   	{ $dn++;

   	$dt = date('Y-m-d',$dl['time']);
   	if($dt==$ddt)
   		$sdate = date('H:i',$dl['time']);
   	else
   	{
   		$sdate = date('Y-m-d H:i',$dl['time']);
   		$ddt = $dt;
   		$dd++;
   	}

   	if($_GET['botsonly'] && !$dl['ua'])continue;
   		
?>
<tr class="<?php echo $dd%2?'altfirst':'altsecond'?>">
	<td><?php echo $dn+($cpage-1)*VBSEO_SM_PAGESIZE?></td>
	<td align="right"><?php echo $sdate?></td>
	<td><?php echo $dl['sitemap']?></td>
	<td><b><?php echo $dl['ua']?></b></td>
	<td><?php echo $dl['ip']?></td>
	<td><?php echo $dl['useragent']?></td>
	<td>
<a href="index.php?removedl[<?php echo $dn?>]=<?php echo $dl['time']?>&botsonly=<?php echo $_GET['botsonly']?>" onclick="return confirm('Are you sure?')">Remove record</a>
	</td>
	<td>
<input type="checkbox" name="removedl[<?php echo $dn?>]" value="<?php echo $dl['time']?>">
	</td>
</tr>
<?php  	}
?>
<tr bgcolor="#ccccff" style="border-bottom: 1px solid #000000">
	<td colspan="8" align="right"><input type="submit" name="remove" value="Remove selected"  onclick="return confirm('Are you sure?')"></td>
</tr>
</TABLE>
</form>
<?php 
	}else
	if($_GET['rlist'])
	{
		vbseo_get_loglist();

	if(VBSEO_SORT_ORDER == 'desc')
		$log_list = array_reverse($log_list);

	$cpage = $_GET['page'] ? $_GET['page'] : 1;
	$pager = vbseo_sm_pager(count($log_list), VBSEO_SM_PAGESIZE, $cpage, 'index.php?rlist=true');
	$log_list = array_slice($log_list, ($cpage-1)*VBSEO_SM_PAGESIZE, VBSEO_SM_PAGESIZE);

	$log_list_full = array();
	foreach($log_list as $ll)
	{
		$stat = vbseo_get_datlog(VBSEO_DAT_FOLDER.$ll);
		$ll2=array(
			'stat' => $stat,
			'pind' => $pind,
			'll' => $ll
			);
		$log_list_full[] = $ll2;
		$pind = $stat['urls_no_tot'];
	}



?>
<h3>Reports List</h3>
<div><?php echo $pager;?></div><br />
<TABLE class="formtbl" cellSpacing="1" cellPadding="4" border=0>
<TR class=header>
      <TD colSpan="10">Sitemap Generator History</TD>
</TR>
<form name="selform" action="index.php" method="POST">
<script>
var selopts = new Array('<?php echo implode("','", $log_list)?>')
function selectall(chk)
{
	for(var i=0;i<selopts.length;i++)
	{
		document.forms['selform'].elements['removelog['+selopts[i]+']'].checked = chk
	}
}
</script>
<tr bgcolor="#ccccff" style="border-bottom: 1px solid #000000">
	<td>No</td>
	<td>Date</td>
	<td>Run Time</td>
	<td>Total URLs</td>
	<td>Change</td>
	<td>Google Notify</td>
	<td>Yahoo Notify</td>
	<td>Details</td>
	<td>
<input type="checkbox" name="rm" onclick="selectall(this.checked)" value="1">
	</td>
</tr>
<?php 

	$ln = 0;
	for($ln=0;$ln<count($log_list_full);$ln++)
	{
		$ll2 = $log_list_full[$ln];
		$ll = $ll2['ll'];
		$stat = $ll2['stat'];
?>
<tr class="<?php echo $ln%2?'altfirst':'altsecond'?>">
	<td><?php echo $ln+($cpage-1)*VBSEO_SM_PAGESIZE?></td>
	<td><?php echo date('Y-m-d H:i',$stat['start'])?></td>
	<td><?php echo number_format($stat['end']-$stat['start'],2)?> s</td>
	<td><?php echo number_format($stat['urls_no_tot'],0)?></td>
	<td><?php echo vbseo_chg_show($stat['urls_no_tot'], $log_list_full[$ln+((VBSEO_SORT_ORDER=='desc')?1:-1)]['stat']['urls_no_tot'])?></td>
	<td><?php echo $stat['ping']?'Yes':'No'?></td>
	<td><?php echo $stat['pingyahoo']?'Yes':'No'?></td>
	<td>
<a href="index.php?details=<?php echo $ll?>">View details</a> |
<a href="index.php?removelog[<?php echo $ll?>]=1" onclick="return confirm('Are you sure?')">Remove record</a>
	</td>
	<td>
<input type="checkbox" name="removelog[<?php echo $ll?>]" value="1">
	</td>
</tr>
<?php 		
	}
?>
<tr bgcolor="#ccccff" style="border-bottom: 1px solid #000000">
	<td colspan="9" align="right"><input type="submit" name="remove" value="Remove selected"  onclick="return confirm('Are you sure?')"></td>
</tr>
</TABLE>
</form>
<?php   }
}

?>
</TD></TR>
<TR><TD>
      <TABLE cellSpacing=0 cellPadding=0 width=500 align=center 
      border=0>
        <TBODY>
        <TR>
          <TD></TD></TR></TBODY></TABLE>
      <TABLE cellSpacing=0 cellPadding=1 align=center border=0>
        <TBODY>
        <TR>
          <TD align=center>vBSEO Google/Yahoo Sitemap Generator v<?php echo VBSEO_SM_VERSION?> is &copy; 2005-2008 <a href="http://www.crawlability.com" target="_blank">Crawlability, Inc.</a> All Rights Reserved.
          <div style="color:#999"><?php echo date('Y-m-d H:i')?></div>
          </TD>
        </TR></TBODY></TABLE>
      </TD>
  </TR></TBODY></TABLE>
</TD></TR></TBODY></TABLE>
</body>
</html>