<?php
$f	= array(
	'title' => 'Blood Transfusions - Safe Medicine?',
	'subTitle' => 'Risks, Complications and Dangers of Transfused Blood',
	'source' => 'Fleur Hupston',
	'sourceArticleUrl' => 'http://public-healthcare-issues.suite101.com/article.cfm/blood_transfusions_safe_medicine',
	'date' => 'September 24, 2008',
	'imgUrl' => 'files/articles/images/feature001.jpg',
	'imgTagline' => '',
	'teaser' => 'Blood transfusions have often been given to patients unnecessarily or given to patients as a precautionary measure, causing death in many documented cases. In his article Jon Barron states "blood cell transfusions in patients having cardiac surgery is strongly associated with both infection and ischemic postoperative morbidity, hospital stay, increased early and late mortality, and hospital costs.',
	'url' => '',
	'copyright' => 'Permission to reprint this <a href="<a href="http://www.suite101.com/profile.cfm/fleurhup1234">article</a>">article</a> was granted by <a href="http://www.suite101.com/profile.cfm/fleurhup1234">article</a>Fleur Hupston</a>'
);
/* Source
http://public-healthcare-issues.suite101.com/article.cfm/blood_transfusions_safe_medicine
*/
$featured = '
<div id="featured_r">
  <div id="featured_box">
	  <div class="thumb"><img src="' . $f['imgUrl'] . '"/>
		<p class="imgTagline">' . $f['imgTagline'] . '</p>
	  </div>
	  <h1 class="title"><a href="' . $f['sourceArticleUrl'] . '">' . $f['title'] . '</a></h1>
	  <!--<h2 class="subTitle">' . $f['subTitle'] . '</h2>-->
	  <p class="source">' . $f['source'] . ' - </p>
	  <p class="date">' . $f['date'] . '</p>

	  <p class="teaser">' . $f['teaser'] . '</p>
	  <p class="more"><a href="' . $f['sourceArticleUrl'] . '">Read more...</a></p>
  </div>
</div>
';