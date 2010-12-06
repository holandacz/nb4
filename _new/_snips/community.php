<?php
$its	= array();
$its[]	= array(
	'title' => 'Lastest Posts', 'desc' => 'Review lastest messages.',
	'url' => 'search.php?do=getnew'
	);
$its[]	= array(
	'title' => 'Ask A Question', 'desc' => 'Post a question to the community.',
	'url' => 'newthread.php?do=newthread&f=59'
	);
$its[]	= array(
	'title' => 'Answer A Question', 'desc' => 'Share your expertise - respond to unanswered questions.',
	'url' => 'search.php?searchid=13239'
	);
$its[]	= array(
	'title' => 'Share News', 'desc' => 'Share industry news.',
	'url' => 'newthread.php?do=newthread&f=4'
	);
$its[]	= array(
	'title' => 'Events Calendar', 'desc' => 'Review and share events of interest.',
	'url' => 'calendar.php?do=add&c=1'
	);
//$its[]	= array('title' => 'Link To Us', 'desc' => 'Place HTML on your site or in your blog.');
//$its[]	= array('title' => 'Get Involved', 'desc' => 'Please help us improve this resource.');
$its[]	= array(
	'title' => 'Forums', 'desc' => 'Forums directory.',
	'url' => 'forum.php'
	);
$its[]	= array(
	'title' => 'Support', 'desc' => 'Please help us support this site.',
	'url' => 'misc.php?do=donate'
	//'url' => 'payments.php'
	);

$community = '
  <div id="community_r">
	<div id="community_top">
	  <h3>Welcome To Our Community</h3>
	</div>
	<div id="community_box">
		<ul>';
$items	= '';
foreach($its as $it){
	$items .= '<li><a class="title" href="' . $it['url'] . '">' . $it['title'] . '</a> ' . $it['desc'] . '</li>';
}

$community .= $items . '
		</ul>
	</div>
  <div id="community_bottom"> </div>
  </div>
';
