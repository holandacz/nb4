<?php





$sql = "
	SELECT title, threadid from " . TABLE_PREFIX . "thread
	WHERE forumid=4
	ORDER BY lastpost DESC
	LIMIT 10";

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

	$html .= '<li><a href="showthread.php?t=' . $row['threadid'] . '&goto=newpost' .
	'" target = "_blank">';
	$html .= $row['title'] . '</a></li>';
}

$news = '
<div id="news_r">
	<div class="header">
	  <div class="title">Latest News</div>
	  <div class="rss_button_fmt"> <a href=""> <img src="styles/main/imgs/layout/shared/rss_button.jpg" border="0" alt="News via rss"> </img> </a> </div>
	  <div class="rss_text_fmt">NEWS VIA</div>
	</div>
	<div class="clear_fmt"> </div>
	<div class="links">
	  <ul>' .
	  $html
	  . '</ul>
	  <p class="more"> <a href="">The NoBlood News Forum</a> </p>
	</div>
</div>
';