<?php
//echo getcwd();
// not used

$img_path		= 'ads/';
$ads_html_file	= $img_path . 'ads.html';

if (file_exists($ads_html_file)){
	$ads_full		=  file_get_contents($ads_html_file);
}

if (!$rows = $db->query_read_slave("SELECT * FROM ads_active")){
	$ads_full		=  '';
}else{

	$style	= '
<style><!--
#ads_full {
	text-align:center;
	padding: 0px;
	width:162px;
}
#ads_full h3 {
	font-size: 11px;
}
#ads_full img {
	border:none;
	padding: 1px;
}

--></style>
	';


	$ads		= array();
	while ($row = $db->fetch_array($rows)){

		$img_file	= $img_path . $row['ads_id'] . '.png';

		list($width, $height, $type, $attr) = getimagesize($img_file);
		$title		= strip_tags($row['title'] . ($row['ad_copy'] ? "\n" . $row['ad_copy'] : ''));
		$url		= $row['url'];
		$ad			= "<a href=\"$url\">";
		$ad			.= "<img src=\"$img_file\" width=$width height=$height title=\"$title\" />" ;
		$ad			.= "</a>";

		$ads[] 		= $ad;

	}
	$ads_html		= implode('<br />', $ads);

	$ads_full		= $style . '<div id="ads_full">' . $ads_html .
				'<h3><a href="http://wiki.noblood.org/Advertising">Become a Sponsor</a></h3>
					</div>';

	file_put_contents($ads_html_file, $ads_full);
}