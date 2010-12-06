<?php
$tagsurl = $vbulletin->options['bburl'] . '/tags';
switch ($vbulletin->options['zointstags_urltype'])
{
	# url rewrite, add nothing
	case 2:
		break;

	# /index.php/
	case 1:
		$tagsurl .= '/index.php';
		break;

	# url param
	default:
		$tagsurl .= '/index.php?tag=';
		break;
}
/*SELECT tag, COUNT(*) count FROM tags GROUP BY tag ORDER BY count DESC LIMIT 50 */
$_tags = $db->query_read("
	SELECT zoints_tag.tag, COUNT(*) count FROM " . TABLE_PREFIX . "zoints_tag zoints_tag
	LEFT JOIN " . TABLE_PREFIX . "thread thread ON(zoints_tag.threadid = thread.threadid)
	WHERE thread.forumid = " . $foruminfo['forumid'] . "
		" . (!$vbulletin->options['zointstags_show_autogen'] ? 'AND autogen != 1' : '') . "
	GROUP BY zoints_tag.tag
	ORDER BY count DESC
	LIMIT " . $vbulletin->options['zointstags_showtags']
);

$count = $db->num_rows($_tags);
while ($tag = $db->fetch_array($_tags))
{
	$tags[$tag['tag']] = $tag['count'];
	$max = max($tag['count'], $max);
	$min = min($min, $tag['count']);
}

$tagcloudbits = '';
if ($count)
{
	$max -= $min;
	$max = max(1, $max);
	ksort($tags);

	$firsttag = true;
	foreach ($tags as $tag => $tagcount)
	{
		$zointstag_link = str_replace(' ', '-', $tag);

		eval('$tagcloudbits .= "' . fetch_template('zointstags_forumdisplay_tagbit') . '";');
		$firsttag = false;
	}
}

eval('$tagcloud = "' . fetch_template('zointstags_forumdisplay_tagcloud') . '";');
$tag_cloud .= 'hello';
