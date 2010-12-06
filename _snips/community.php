<?php
$its	= array();
$its[]	= array(
	'title' => 'About noblood', 'desc' => 'Who we are, what we do, how we do it and what you can do.',
	'url' => 'about/'
	);
$its[]	= array(
	'title' => 'Enter Forums', 'desc' => 'Review latest discussions.',
	'url' => 'forums/'
	);
$its[]	= array(
	'title' => 'Projects NEW!', 'desc' => 'Suggest new features, report bugs, participate in site development tasks.',
	'url' => 'projects/'
	);
$its[]	= array(
	'title' => 'Ask A Question', 'desc' => 'Post a question to the community.',
	'url' => 'newthread.php?do=newthread&f=59'
	);

// setup to search for unanswered posts
$fwsfut_display = 0;
if ($vbulletin->options['fwsfut_all_grps'] == 0)
{
	$fwsfut_display = 1;
}

if ($vbulletin->options['fwsfut_all_grps'] == 1)
{
	$fwsfut_groups = explode(',',$vbulletin->options['fwsfut_grps']);
	if (is_member_of($vbulletin->userinfo,$fwsfut_groups))
	{
		$fwsfut_display = 1;
	}
}
if ($fwsfut_display == 1)
{
	$its[]	= array(
		'title' => 'Answer A Question', 'desc' => 'Share your expertise - respond to unanswered questions.',
		'url' => $vbulletin->options['bburl'] . '/search.php?' . $session['sessionurl'] . 'do=process&replyless=1&replylimit=0&exclude=' .$vbulletin->options['fwsfut_exclude_forums'] . '&nocache=' . $vbulletin->options['fwsfut_cache_enable']
		);
}




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
	'title' => 'Tell a Friend', 'desc' => 'Invite friends and collegues to participate in this community.',
	'url' => 'invite'
	);
$its[]	= array(
	'title' => 'Support', 'desc' => 'Please help us support this site.',
	'url' => '/donate'
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
