<?php
$sql = "
SELECT threadid, title, replycount, lastposter, views, dateline, visible
FROM thread
WHERE visible AND forumid=4
ORDER BY lastpost DESC
LIMIT 10
";

$rows = $db->query($sql);
$html = '';
while ($row = $db->fetch_array($rows)){
	$html .= '<li><a href="showthread.php?t=' . $row['threadid'] . '&goto=newpost">';
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
	  <p class="more"> <a href="">More...</a> </p>
	</div>
</div>
';