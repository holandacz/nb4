<?php
$f	= array(
	'title' => 'Blood Transfusions - Safe Medicine?',
	'subTitle' => 'Risks, Complications and Dangers of Transfused Blood',
	'source' => 'BioWorld Today',
	'date' => 'June 2, 2008',
	'imgUrl' => 'http://nobloodtest.org/styles/main/imgs/layout/shared/logo.png',
	'imgTagline' => 'image tag line',
	'teaser' => 'Blood transfusions doubled for patients with chemotherapy-induced anemia at one Georgia clinic after the government issued a policy that markedly restricted payments for anemia drugs used in cancer patients, researchers reported Sunday',
	'url' => 'news-hot-topics-such-hepatitis-c-sars-aids/4582-study-shows-transfusions-increase-after-cms-rule.html'
);
$home_featured = '
  <div id="home_featured">
  <div id="home_featured_box">
  <div class="img"><img src="' . $f['imgUrl'] . '"/>
  <div class="tagline">' . $f['imgTagline'] . '</div>
  </div>
  <p class="title">' . $f['title'] . '</p>
  <p class="source">' . $f['source'] . '</p>
  <p class="date">' . $f['date'] . '</p>
  <p class="teaser">' . $f['teaser'] . '</p>
  <p class="more"><a href="' . $f['url'] . '">Read more...</a></p>
  </div></div>
';