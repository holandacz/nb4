<?php
$sql = "
	SELECT aa_companies.*
	FROM " . TABLE_PREFIX . "aa_companies
	WHERE publish AND premium
	ORDER BY city, state, name";


$rows = $db->query($sql);
$hospital_sponsors = '
<!-- hospital_sponsors -->
<div id="hospital_sponsors_r">
  <div class="header"> <a href="http://wiki.noblood.org/Advertising"> <img class="question_mark" src="styles/main/imgs/modules/question_mark.gif" alt="" /> </a> </div>
<div class="box">
<ul>';

$citystate = '';
while ($sponsor = $db->fetch_array($rows)){

	if ($sponsor['city'] != $citystate){
		$hospital_sponsors .= $citystate == '' ? '' : '</li>';
		$hospital_sponsors .= '<li class="smallfont">';
		$hospital_sponsors .= '<b>' . $sponsor['city'] . '</b> ';
		$citystate = $sponsor['city'];
	}else{
		$hospital_sponsors .= ', ';
	}

	$hospital_sponsors .= '<a href="http://' . $sponsor['url'] .
	'" target = "_blank" title="Please visit ' . $sponsor['name'] . ' located in ' .
	$sponsor['city'] . ' ' . $sponsor['state'] .'">';
	$hospital_sponsors .= $sponsor['nameAbbr'] . '</a>';

}
$hospital_sponsors .= '</li></ul>
</div>
  <div class="footer"> <a href="hospitals.php"> <img class="question_mark" src="styles/main/imgs/modules/plus.gif" alt="" /> </a> </div>
</div>
	<!-- / hospital_sponsors -->
';





/*



$hospital_sponsors = '
<!-- hospital_sponsors -->
<div id="hospital_sponsors_r">
  <div class="header"> <a href="http://wiki.noblood.org/Advertising"> <img class="question_mark" src="styles/main/imgs/modules/question_mark.gif" alt="" /> </a> </div>
<div class="box">
<ul><li class="smallfont"><b>Atlanta</b> <a href="http://www.atlantamedcenter.com/CWSContent/atlantamedcenter/ourServices/medicalServices/bloodlessMedicineSurgeryProgram+.htm" target="_blank" title="Please visit Atlanta Medical Center located in Atlanta GA">Atlanta</a></li><li class="smallfont"><b>Bridgeport</b> <a href="http://www.bridgeporthospital.org/BloodlessMedicine" target="_blank" title="Please visit Bridgeport Hospital located in Bridgeport CT">Bridgeport</a></li><li class="smallfont"><b>Cleveland</b> <a href="http://www.rainbowbabies.org/" target="_blank" title="Please visit Rainbow Babies &amp; Children\'s Hospital located in Cleveland OH">Rainbow Babies</a></li><li class="smallfont"><b>Hackensack</b> <a href="http://humed.com" target="_blank" title="Please visit Hackensack University Medical Center located in Hackensack NJ">Hackensack</a></li><li class="smallfont"><b>Houston</b> <a href="http://www.sjmctx.com/" target="_blank" title="Please visit St. Joseph Medical Center located in Houston TX">St Joseph</a></li><li class="smallfont"><b>Jacksonville</b> <a href="http://community.e-baptisthealth.com/services/noblood/index.html" target="_blank" title="Please visit Baptist Health located in Jacksonville FL">Baptist</a></li><li class="smallfont"><b>Kalamazoo</b> <a href="http://www.bronsonhealth.com/" target="_blank" title="Please visit Bronson Methodist Hospital located in Kalamazoo MI">Bronson Methodist</a></li><li class="smallfont"><b>Kansas City</b> <a href="http://www.saintlukeshealthsystem.org/app/hp.asp" target="_blank" title="Please visit St. Luke\'s Hospital located in Kansas City MO">St Luke\'s</a></li><li class="smallfont"><b>Omaha</b> <a href="http://www.CreightonHospital.com" target="_blank" title="Please visit Creighton University Medical Center located in Omaha NE">Creighton</a></li><li class="smallfont"><b>Philadelphia</b> <a href="http://www.hahnemannhospital.com/CWSContent/hahnemannhospital" target="_blank" title="Please visit Hahnemann University Hospital located in Philadelphia PA">Hahnemann</a>, <a href="http://www.pennhealth.com/bloodless" target="_blank" title="Please visit Pennsylvania Hospital located in Philadelphia PA">Pennsylvania Hospital</a>, <a href="http://www.stchristophershospital.com/CWSContent/stchristophershospital" target="_blank" title="Please visit St. Christopher\'s Hospital for Children located in Philadelphia PA">St Christopher\'s</a></li><li class="smallfont"><b>Phoenix</b> <a href="http://www.bannerhealth.com/Locations/Arizona/Banner+Good+Samaritan+Medical+Center/Programs+and+Services/Specialty+Services/Blood+Conservation.htm" target="_blank" title="Please visit Banner Good Samaritan Medical Center located in Phoenix AZ">Banner Health</a></li><li class="smallfont"><b>Riverside</b> <a href="http://www.rchc.org/" target="_blank" title="Please visit Riverside Community Hospital located in Riverside CA">Community</a></li><li class="smallfont"><b>San Antonio</b> <a href="http://www.baptisthealthsystem.com/services_surgery_treatments_bloodless.aspx" target="_blank" title="Please visit Northeast Baptist Hospital located in San Antonio TX">Northeast Baptist</a></li><li class="smallfont"><b>San Ramon</b> <a href="http://www.sanramonmedctr.com/CWSContent/sanramonmedctr/ourServices/medicalServices/Blood+Conservation+Program.htm" target="_blank" title="Please visit San Ramon Regional Medical Center located in San Ramon CA">San Ramon</a></li></ul>
</div>
  <div class="footer"> <a href="hospitals.php"> <img class="question_mark" src="styles/main/imgs/modules/plus.gif" alt="" /> </a> </div>
</div>
	<!-- / hospital_sponsors -->
';

*/