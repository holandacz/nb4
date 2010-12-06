<?php
$its	= array();
$its[]	= array('title' => 'Forums', 'desc' => 'Forums directory.');
$its[]	= array('title' => 'Lastest Posts', 'desc' => 'Review lastest messages.');
$its[]	= array('title' => 'Ask A Question', 'desc' => 'Post a question to the community.');
$its[]	= array('title' => 'Answer A Question', 'desc' => 'Share your expertise - respond to unanswered questions.');
$its[]	= array('title' => 'Share News', 'desc' => 'Share industry news.');
$its[]	= array('title' => 'Events Calendar', 'desc' => 'Review and share events of interest.');
$its[]	= array('title' => 'Link To Us', 'desc' => 'Place HTML on your site or in your blog.');
$its[]	= array('title' => 'Get Involved', 'desc' => 'Please help us improve this resource.');
$its[]	= array('title' => 'Support', 'desc' => 'Please help us support this site.');
$home_community = '
  <div id="home_community">
	<div class="home_community_top">
	  <h3>Community</h3>
	</div>
	<div class="home_community_box">
		<ul>';
$items	= '';
foreach($its as $it){
	$items .= '<li><a class="title" href="">' . $it['title'] . '</a> ' . $it['desc'] . '</li>';
}

$home_community .= $items . '
		</ul>
	</div>
  </div>
';
