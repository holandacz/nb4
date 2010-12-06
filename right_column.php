<?php
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'right_column');

if (! strstr($_SERVER["SCRIPT_NAME"], 'payments.php') && ! strstr($_SERVER["SCRIPT_NAME"], 'misc.php')){

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once('./includes/functions_misc.php');

define('RIGHT_COL_WIDTH', 160);
global $stylevar;

$ini	= parse_ini_file('../m/config/config.ini', true);

$column_right = '<!-- right_column -->';
	$column_right .= '
	<table width="' . RIGHT_COL_WIDTH . '" border="0" cellspacing="' . $stylevar[cellspacing] . '>
	<tr><td align="center" class="alt1">
	<b><span style="color:red;">! ! ! NEW ! ! !</span><br />
		<a href="http://mobile.noblood.org/"><img style="margin-top:10px" title="NoBlood Mobile" src="images/vbseo_skin_1.0/WebPhoneFaceLeft-87x125.png" alt="NoBlood Mobile" border="0"></a>
	</td></tr>
	<tr><td class="alt1" align="center">
	<b><a href="http://mobile.noblood.org/">NoBlood Mobile</a><br /><span style="color:red"><i>beta</i></span></b>
	</td></tr>
	</table>
';


if (! strstr($_SERVER["SCRIPT_NAME"], 'hospital')){
	$column_right .= '
	<br />
	<table width="' . RIGHT_COL_WIDTH . '" border="0" cellspacing="' . $stylevar[cellspacing] . '>
	<tr><td align="center" class="alt1">
		<a href="hospitals.php"><img title="' . $ini['default']['site.hospitals.tagline'] . ' Directory' . '" src="images/vbseo_skin_1.0/hospital.png" alt="' . $ini['default']['site.hospitals.tagline'] . '" border="0"></a>
	</td></tr>
	<tr><td class="alt1" align="center">
	<b><a href="hospitals.php">' . $ini['default']['site.hospitals.tagline'] . '</a></b>
	</td></tr>
	</table>
';
}


if (! strstr($_SERVER["SCRIPT_NAME"], 'hospital')){



	$conn = mysql_connect("mysql", 'nb', 'n.2009B');
	$database = 'noblood_forum';
	mysql_select_db($database, $conn) or die ("Database not found.");




	$sql = "
		SELECT * FROM nb_hospitals_premium"; // view

	$sponsors = mysql_query($sql);
	$sponsors_html = '<ul style="text-align:left;padding-top:0px;">';
	$citystate = '';
	while ($sponsor = mysql_fetch_assoc($sponsors)){

		if ($sponsor['city'] != $citystate){
			$sponsors_html .= $citystate == '' ? '' : '</li>';
			$sponsors_html .= '<li class="smallfont">';
			$sponsors_html .= '<b>' . $sponsor['city'] . '</b> ';
			$citystate = $sponsor['city'];
		}else{
			$sponsors_html .= ', ';
		}

		$sponsors_html .= '<a href="http://' . $sponsor['url'] .
		'" target = "_blank" title="Please visit ' . $sponsor['name'] . ' located in ' .
		$sponsor['city'] . ' ' . $sponsor['state'] .'">';
		$sponsors_html .= $sponsor['companyabbrev'] . '</a>';

	}
	$sponsors_html .= '</li></ul>';


	$column_right .= '
	<br />
	<table width="' . RIGHT_COL_WIDTH . '" border="0" cellspacing="' . $stylevar[cellspacing] . '" class="tborder">
	<tr><td align="center" class="tcat">Featured<br />Hospital Sponsors</td></tr>
	<tr><td align="left" class="alt1">
	<div class="smallfont">' .
	$sponsors_html .
	'</div>
	</td>
	</tr>
	</table>
	';
}





$column_right .= '
<br />
<table width="' . RIGHT_COL_WIDTH . '" border="0" cellpadding="' . $stylevar[cellpadding] . '" cellspacing="' . $stylevar[cellspacing] . '" class="tborder">
<tr><td class="alt1" align="center">
<a href="http://www.noblood.org/payments.php"><img src="ads/NBCoffeeAd.jpg" border="0" alt="Click here to help us make a difference today." /></a>
Yes, for the price of a cup of coffee, you can help NoBlood continue its mission to advance knowledge and
awareness of transfusion alternatives, blood conservation, blood management, bloodless medicine and bloodless surgery.
';

if ($vbulletin->userinfo["userid"])
	$column_right .= '<a href="payments.php"><br />';
else
	$column_right .= '<a href="misc.php?do=donate"><br />';

$column_right .= '
Please help us continue to make a difference today.</a>
</td></tr></table>
<br />
';


$column_right .= '
<table width="' . RIGHT_COL_WIDTH . '" border="0" cellpadding="' . $stylevar[cellpadding] . '" cellspacing="' . $stylevar[cellspacing] . '" class="tborder">
<tr><td class="tcat" align="center">Highlights</td></tr>
<tr><td align="left" class="alt1">
<b>Looking for help?</b>
<div class="smallfont">
<ul>
<li class="smallfont" style="line-height: 140%;"><a href="search.php" rel="nofollow">Search existing knowledge.</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="forumdisplay.php?f=59" rel="nofollow">Review questions & answers.</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="newthread.php?do=newthread&f=59" rel="nofollow">Post a question.</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="hospitals.php" rel="nofollow">Find a hospital.</a></li>
</ul>

</div>
</td>
</tr>

<tr><td class="alt1">
<b>Can you help?</b>
<div class="smallfont">
<ul style="text-align:left">
<li class="smallfont" style="line-height: 140%;"><a href="search.php?searchid=6763" rel="nofollow">Unanswered questions.</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="search.php?do=getnew" rel="nofollow">Review new posts.</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="newthread.php?do=newthread&f=4" rel="nofollow">Share industry news.</a></li>
</ul>
</div>
</td>
</tr>
</table>
<br />
<table width="' . RIGHT_COL_WIDTH . '" border="0" cellpadding="' . $stylevar[cellpadding] . '" cellspacing="' . $stylevar[cellspacing] . '" class="tborder">
<tr><td class="tcat" align="center">Key Wiki Articles</td></tr>
<tr><td class="alt1" align="left">
<div class="smallfont">
<ul>
<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/Erythropoietin_%28EPO%29">Erythropoietin (EPO)</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/Acute_Normovolemic_Hemodilution">Acute Normovolemic Hemodilution (ANH)</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="http://noblood.org/wiki/PBS_documentary_Knocking_-_Jehovah%27s_Witnesses_and_Blood">PBS documentary Knocking - Jehovah\'s Witnesses and Blood</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/Advance_Directive">Prepare Now For a Possible Medical Emergency</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/Epidural_Blood_Patch">Epidural Blood Patch (EBP)</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/Intraoperative_blood_salvage">Intraoperative blood salvage</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="showthread.php?t=4032">Patients Who Refuse Blood Transfusions - FAQs</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/Polyheme">Polyheme</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/Category:Blood_Fractions">Blood Fractions Guide</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/Get_Involved">Get Involved!</a></li>
<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/Fundraising">Please Support NoBlood Today!</a></li>

<li class="smallfont" style="line-height: 140%;"><a href="http://www.noblood.org/wiki/"><b>More...</b></a></li>
</ul>
<b>Note:</b> Click to <a href="sendmessage.php">request permission</a> to edit wiki articles.
</div>
</td>
</tr>
</table>
';

$column_right .= '
</td>
';

$column_right .= '<!-- / right_column -->';
}