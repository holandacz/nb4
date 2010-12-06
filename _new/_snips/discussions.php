<?php
$sql = "
SELECT threadid, title, replycount, lastposter, views, dateline, visible
FROM thread
WHERE visible AND forumid IN(18,59,13,8,40,23,9,21,25,19,5,24,54,17,16,47,51,87,22)
ORDER BY lastpost DESC
LIMIT 15
";
/*
FORUM LIST
SELECT forumid, title, replycount, lastpost, threadcount FROM forum order by lastpost desc
*/

$rows = $db->query($sql);
$html = '';
while ($row = $db->fetch_array($rows)){
	$html .= '<li><a href="showthread.php?t=' . $row['threadid'] . '&goto=newpost">';
	$html .= $row['title'] . '</a></li>';
}
$discussions = '
<div id="discussions_r">
	<div class="header">
	  <div class="title">Latest Discussions</div>
	  <div class="rss_button_fmt"> <a href="external.php?forumids=4"> <img src="styles/main/imgs/layout/shared/rss_button.jpg" border="0" alt="DISCUSSIONS via RSS"> </img> </a> </div>
	  <div class="rss_text_fmt">Discussions VIA</div>
	</div>
	<div class="links">
	  <ul>' .
	  $html
	  . '</ul>
	  <p class="more"> <a href="">More...</a> </p>
	</div>
</div>
';