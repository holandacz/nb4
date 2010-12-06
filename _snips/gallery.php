<?php
$gallery = '
<div id="news_r">
	<div class="header">
	  <div class="title">Images Gallery</div>
	  <div class="rss_button_fmt"> <a href="gallery/feed.php"> <img src="styles/main/imgs/layout/shared/rss_button.jpg" border="0" alt="Images Gallery via rss"> </img> </a> </div>
	  <div class="rss_text_fmt">IMAGES GALLERY VIA</div>
	</div>
	<div id="gallery_fp_random">
		<table><tr>' .
	  $photoplog_minithumb_pics
	  . '</tr></table>
	  <p class="more"> <a href="gallery/">More...</a> </p>
	</div>
</div>
';