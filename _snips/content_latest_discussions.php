<?php
$sql = "
SELECT threadid, title, replycount, lastposter, views, dateline, visible
FROM thread
WHERE visible AND forumid IN(18,59,13,8,40,23,9,21,25,19,5,24,54,17,16,47,51,87,22)
ORDER BY dateline DESC
LIMIT 20
";

/*
FORUM LIST
SELECT forumid, title, replycount, lastpost, threadcount FROM forum order by lastpost desc
*/


$rows = $db->query($sql);
$html = '';
while ($row = $db->fetch_array($rows)){
	if ($sponsor['city'] != $citystate){
		$sponsors_html .= $citystate == '' ? '' : '</li>';
		$sponsors_html .= '<li class="smallfont">';
		$sponsors_html .= '<b>' . $sponsor['city'] . '</b> ';
		$citystate = $sponsor['city'];
	}else{
		$sponsors_html .= ', ';
	}

	$html .= '<li><a href="showthread.php?t=' . $row['threadid'] . '&goto=newpost';
	$html .= $row['title'] . '</a></li>';
}

$news = '
<div id="news_r">
	<div class="header">
	  <div class="title">Latest News</div>
	  <div class="rss_button_fmt"> <a href="external.php?forumids=4"> <img src="styles/main/imgs/layout/shared/rss_button.jpg" border="0" alt="News via rss"> </img> </a> </div>
	  <div class="rss_text_fmt">NEWS VIA</div>
	</div>
	<div class="links">
	  <ul>' .
	  $html
	  . '</ul>
	  <p class="more"> <a href="">The NoBlood News Forum</a> </p>
	</div>
</div>
';









<?php


SELECT threadid, title, replycount, lastposter, views, dateline, visible
FROM thread
WHERE visible AND forumid IN(18,59,13,8,40,23,9,21,25,19,5,24,54,17,16,47,51,87,22)
ORDER BY dateline DESC
LIMIT 20

/*
FORUM LIST
SELECT forumid, title, replycount, lastpost, threadcount FROM forum order by lastpost desc
*/


$discussions = '
<!-- discussions -->
<div >
  <div id="discussions_r">
	<div class="header">
	  <div class="title">Latest Discussions</div>
	  <div class="rss_button_fmt"> <a href="external.php"> <img src="styles/main/imgs/layout/shared/rss_button.jpg" border="0" alt="News via rss"> </img> </a> </div>
	  <div class="rss_text_fmt">DISCUSSIONS VIA</div>
	</div>
	<div class="links">
	  <ul>
		<li> <a href="">Spoiled Rotten Kids – Part One</a> </li>
		<li> <a href="">Fear of Taking Meds</a> </li>
		<li> <a href="">Ask the Presidential Candidates</a> </li>
		<li> <a href="">Spoiled Rotten Kids – Part One</a> </li>
		<li> <a href="">Fear of Taking Meds</a> </li>
		<li> <a href="">Ask the Presidential Candidates</a> </li>
		<li> <a href="">Spoiled Rotten Kids – Part One</a> </li>
		<li> <a href="">Fear of Taking Meds</a> </li>
		<li> <a href="">Ask the Presidential Candidates</a> </li>
		<li> <a href="">Spoiled Rotten Kids – Part One</a> </li>
		<li> <a href="">Fear of Taking Meds</a> </li>
		<li> <a href="">Ask the Presidential Candidates</a> </li>
		<li> <a href="">Spoiled Rotten Kids – Part One</a> </li>
		<li> <a href="">Fear of Taking Meds</a> </li>
		<li> <a href="">Ask the Presidential Candidates</a> </li>
	  </ul>
	</div>
  </div>
</div>
<!-- / discussions -->
';