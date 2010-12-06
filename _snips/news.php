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
/*
$resultsnr = 10;
$get_stats_latestnews = $vbulletin->db->query_read("
	SELECT threadid, title, forumid, dateline, lastpost, visible, open
	FROM thread AS thread
	WHERE NOT ISNULL(threadid) AND !(forumid IN (6,41)) AND visible = '1' AND open!='10'
	ORDER BY dateline DESC
	LIMIT 0, $resultsnr
");
while ($get_latest_news = $db->fetch_array($get_stats_latestnews))
{
	$get_latest_news[fullthreadtitle] = strip_tags($get_latest_news[title]);
	if ($trimthreadtitle > 0)
	{
		$get_latest_news[titletrimmed] = fetch_trimmed_title($get_latest_news[fullthreadtitle], $trimthreadtitle);
	}
	else
	{
		$get_latest_news[titletrimmed] = $get_latest_news[fullthreadtitle];
	}
	if ($get_latest_news[lastpost] > $vbulletin->userinfo['lastvisit'])
	{
		$get_latest_news[newpost] = true;
	}
	$get_news_postdate = vbdate($vbulletin->options['cybtopstats_date_format'], $get_latest_news[dateline]);
	eval('$cybtopstats_latestnews .= "' . $vbulletin->templatecache['cyb_topstats_latestnews'] . '";');
}
*/

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
	  <p class="more"> <a href="news-hot-topics-such-hepatitis-c-sars-aids">More...</a> </p>
	</div>
</div>
';