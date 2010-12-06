<?php
// tag cloud display
$tag_links = fetch_tagcloud('usage');
$tag_cloud = '
<!-- tag cloud -->
<div id="tag_cloud_r">
  <div class="top"> </div>
  <div class="box">
	<div class="title">
	  <h2>Popular Tags</h2>
	</div>
	<div class="links">' .
		$tag_links .
	  '<div class="clearboth"> </div>
	</div>
  </div>
  <div class="bottom"> </div>
</div>
<!-- / tag cloud -->
';