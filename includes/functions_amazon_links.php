<?php
function amazon_links($text) {
	/*
	 * your partnernet ids
	 */
	$associate_id_de = 'noblood-20'; // or leave empty
	$associate_id_com = 'noblood-20'; // or leave empty
	$associate_id_ca = 'noblood-20'; // or leave empty

	/*
	 * tag=xxx and ref=xxx will be replaced with your associate id in non asin links
	 */
	$replace_targt_urls = true; // true or false



	// reeplace arrays
	$replace_src = array();
	$replace_str = array();


	// check for [URL="amazon-link"]some text[/URL]
	if(preg_match_all("/\[url\=(?:\")?(http:\/\/(?:[A-z0-9\.]+)?amazon\.(de|com|ca)\/(?:.+))(?:\")?](.+)\[\/url\]/iU", $text, $out)) {
		for($i=0;$i<count($out[1]);$i++) {
			$associate_id = ${'associate_id_'.$out[2][$i]};
			$out[4][$i] = $out[1][$i];

			if($associate_id && !empty($associate_id)) {
				// Look for an asin number
				if(preg_match("/\/[A-Z0-9]{10}(\/)?/", $out[1][$i], $asin) && strpos($out[1][$i], '/review') === false) {
					if(substr($asin[0], -1) != '/') { $asin[0] .= '/'; }
					$out[1][$i] = 'http://www.amazon.'.$out[2][$i].'/exec/obidos/ASIN'.$asin[0].'ref=nosim/'.$associate_id;
				} else {
					$target_url = $out[1][$i];
					if($replace_targt_urls) {
						$target_url = preg_replace('/ref\=([a-z0-9\-]{3,})\-([0-9]{2})/iU', 'ref='.$associate_id, $target_url);
						$target_url = preg_replace('/tag\=([a-z0-9\-]{3,})\-([0-9]{2})/iU', 'tag='.$associate_id, $target_url);
					}

					$out[1][$i] = 'http://www.amazon.'.$out[2][$i].'/exec/obidos/redirect?link_code=ur2&camp=1789&tag='.$associate_id.'&creative=9325&path='.urlencode($target_url);
				}

				$replace_src[] = '[URL="'.$out[4][$i].'"]';
				$replace_str[] = '[URL="'.$out[1][$i].'"]';

				$replace_src[] = '[URL='.$out[4][$i].']';
				$replace_str[] = '[URL='.$out[1][$i].']';

				$replace_src[] = '[url="'.$out[4][$i].'"]';
				$replace_str[] = '[url="'.$out[1][$i].'"]';

				$replace_src[] = '[url='.$out[4][$i].']';
				$replace_str[] = '[url='.$out[1][$i].']';
			}
		}
	} unset($out);


	// check for [URL]amazon-link[/URL]
	if(preg_match_all("/\[url\](http:\/\/(?:[A-z0-9\.]+)?amazon\.(de|com|ca)\/(?:.+))\[\/url\]/iU", $text, $out)) {
		for($i=0;$i<count($out[1]);$i++) {
			$associate_id = ${'associate_id_'.$out[2][$i]};
			$out[4][$i] = $out[1][$i];

			if($associate_id && !empty($associate_id)) {
				// Look for an asin number
				if(preg_match("/\/[A-Z0-9]{10}(\/)?/", $out[1][$i], $asin) && strpos($out[1][$i], '/review') === false) {
					if(substr($asin[0], -1) != '/') { $asin[0] .= '/'; }
					$out[1][$i] = 'http://www.amazon.'.$out[2][$i].'/exec/obidos/ASIN'.$asin[0].'ref=nosim/'.$associate_id;
				} else {
					$target_url = $out[1][$i];
					if($replace_targt_urls) {
						$target_url = preg_replace('/ref\=([a-z0-9\-]{3,})\-([0-9]{2})/iU', 'ref='.$associate_id, $target_url);
						$target_url = preg_replace('/tag\=([a-z0-9\-]{3,})\-([0-9]{2})/iU', 'tag='.$associate_id, $target_url);
					}

					$out[1][$i] = 'http://www.amazon.'.$out[2][$i].'/exec/obidos/redirect?link_code=ur2&camp=1789&tag='.$associate_id.'&creative=9325&path='.urlencode($target_url);
				}

				$displayed_link = $out[4][$i];
				if(strlen($displayed_link) > 55) {
					$displayed_link = substr($displayed_link, 0, 36).'...'.substr($displayed_link, -14);
				}


				$replace_src[] = '[URL]'.$out[4][$i].'[/URL]';
				$replace_str[] = '[URL="'.$out[1][$i].'"]'.$displayed_link."[/URL]";

				$replace_src[] = '[url]'.$out[4][$i].'[/url]';
				$replace_str[] = '[url="'.$out[1][$i].'"]'.$displayed_link."[/url]";
			}
		}
	} unset($out);


	// replace the message
	if(isset($replace_src[0])) {
	    $text = str_replace($replace_src, $replace_str, $text);
	} unset($replace_src, $replace_str);

    return $text;
}
?>