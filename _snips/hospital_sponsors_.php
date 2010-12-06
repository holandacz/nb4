<?php

$ini	= parse_ini_file('../m/config/config.ini', true);

$gvdb = $ini['default'];
$conn = mysql_connect($gvdb['db.gv.host'], $gvdb['db.gv.username'], $gvdb['db.gv.password']);
$database = $gvdb['db.gv.dbname'];
mysql_select_db($database, $conn) or die ("Database not found.");

$sql = "SELECT * FROM nb_hospitals_premium"; // view


$rows = mysql_query($sql);
$hospital_sponsors = '
<!-- hospital_sponsors -->
<div id="hospital_sponsors_r">
  <div class="header"> <a href="http://wiki.noblood.org/Advertising"> <img class="question_mark" src="styles/main/imgs/modules/question_mark.gif" alt="Advertising on noblood." /> </a> </div>
<div class="box">
<ul>';

$citystate = '';
while ($sponsor = mysql_fetch_assoc($rows)){

	if ($sponsor['city'] != $citystate){
		$hospital_sponsors .= $citystate == '' ? '' : '</li>';
		$hospital_sponsors .= '<li class="smallfont">';
		$hospital_sponsors .= '<b>' . $sponsor['city'] . '</b> ';
		$citystate = $sponsor['city'];
	}else{
		$hospital_sponsors .= ', ';
	}

	$hospital_sponsors .= '<a href="http://' . $sponsor['url'] .
	'" target = "_blank" rel="nofollow" title="Please visit ' . $sponsor['name'] . ' located in ' .
	$sponsor['city'] . ' ' . $sponsor['state'] .'">';
	$hospital_sponsors .= $sponsor['companyabbrev'] . '</a>';

}
$hospital_sponsors .= '</li></ul>
</div>
  <div class="footer"> <a href="hospitals.php"> <img class="question_mark" src="styles/main/imgs/modules/plus.gif" alt="Complete Bloodless Medicine and Surgery Hospitals Directory" /> </a>zz </div>
</div>
	<!-- / hospital_sponsors -->
';
