<?php

// ###################### Start create_new_thread ########################
/**
* Creates new thread or gives error and then redirects user
*
* @param	string	Title of thread
* @param	string	Message of post
* @param	integer	ForumID for thread
* @param	boolean	Allow guest posts
*/
function create_new_thread($title = 'Defauglt Title', $message = 'Defagult Message', $id = 3, $guest = false)
{
	// set some globals

	global $forumperms, $vbulletin, $vbphrase;

	// init some variables

	$fail = 0;
	$errors = array();
	$newpost = array();

	// init post information

	if ($guest AND $vbulletin->userinfo['userid'] == 0)
	{
		$newpost['username'] = $vbphrase['guest'];
	}
	$newpost['title'] = $title;
	$newpost['message'] = $message;
	$newpost['signature'] = '0';
	if ($vbulletin->userinfo['signature'] != '')
	{
		$newpost['signature'] = '1';
	}
	$newpost['parseurl'] = '1';
	$newpost['emailupdate'] = '9999';

	// attempt thread create

	$foruminfo = verify_id('forum', $id, 0, 1);
	if (!$foruminfo['forumid'])
	{
		$fail = 1;
	}
	$forumperms = fetch_permissions($foruminfo['forumid']);
	if (!function_exists('build_new_post'))
	{
		require_once(DIR . '/includes/functions_newpost.php');
	}
	build_new_post('thread', $foruminfo, array(), array(), $newpost, $errors);
	if (sizeof($errors) > 0)
	{
		$fail = 1;
	}

	// do redirection

	if (!$fail)
	{
		$vbulletin->url = $vbulletin->options['bburl'] . '/showthread.php?' . $vbulletin->session->vars['sessionurl'] . "p=".$newpost['postid']."#post".$newpost['postid'];
		eval(print_standard_redirect('redirect_postthanks'));
	}
	else
	{
		$vbulletin->url = $vbulletin->options['bburl'];
		eval(print_standard_redirect($vbphrase['error'].': '.$vbphrase['redirecting'],0,1));
	}
}

function latestvids()
{
	global $vbulletin, $stylevar;
	$latestvids = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "anymedia ORDER BY " . TABLE_PREFIX . "anymedia.date DESC LIMIT 0,10");

	while($lv = $vbulletin->db->fetch_array($latestvids))
	{
		$wtf = $lv;
		$lv['date']		 = vbdate($vbulletin->options['dateformat'], $lv['date']);
    	$lv['time']      = vbdate($vbulletin->options['timeformat'], $lv['date']);
		//$lv['body'] = cutOffString($lv['body'],500, '', DO_CLOSE_BBCODE);
		//require_once(DIR . '/includes/class_bbcode.php');
		//$lv['body'] = strip_tags($lv['body']);
		//$parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
		//$lv['body'] = $parser->do_parse($lv['body'], false, true, true, false, true, false);\
		//$vids .= "$lv[media_url]";
		//$vid .= fetch_template('anymedia_vids')
		echo eval('$vid .= "' . fetch_template('anymedia_vids') . '";');
		return $lv;
	}
	return $please;
}

/*
The function that draws the rating bar.
--------------------------------------------------------- 
ryan masuga, masugadesign.com
ryan@masugadesign.com 
Licensed under a Creative Commons Attribution 3.0 License.
http://creativecommons.org/licenses/by/3.0/
See readme.txt for full credit details.
Made to work with Vbulletin by Nix
--------------------------------------------------------- */
function rating_bar($id,$units='',$static='')
{
	global $vbulletin;
	$rating_unitwidth     = 30;
	require_once('./global.php');
		
	//set some variables
	$userid = $vbulletin->userinfo['userid'];
	if (!$units) {$units = 10;}
	if (!$static) {$static = FALSE;}

	// get votes, values, ips for the current rating bar
	$query = $vbulletin->db->query_read("SELECT total_votes, total_values, userids FROM " . TABLE_PREFIX . "anymedia_rating WHERE id='$id' ")or die(" Error: ".mysql_error());

	// insert the id in the DB if it doesn't exist already
	// see: http://www.masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/#comment-121
	if (mysql_num_rows($query) == 0) {
	$sql = "INSERT INTO " . TABLE_PREFIX . "anymedia_rating (`id`,`total_votes`, `total_values`, `userids`) VALUES ('$id', '0', '0', '0')";
	$result = $vbulletin->db->query_read($sql);
	}
	
	$numbers=$vbulletin->db->fetch_array($query);
	$numbers['userids'] = unserialize($numbers['userids']);

	if ($numbers['total_votes'] < 1) {
		$count = 0;
	} else {
		$count=$numbers['total_votes']; //how many votes total
	}
	$current_rating=$numbers['total_values']; //total number of rating added together and stored
	$tense=($count==1) ? "vote" : "votes"; //plural form votes/vote

	// determine whether the user has voted, so we know how to draw the ul/li
	//$voted=mysql_num_rows($vbulletin->db->query_read("SELECT userids FROM " . TABLE_PREFIX . "anymedia_rating WHERE userids=$userid AND id=$id"));

	// now draw the rating bar
	$rating_width = @number_format($current_rating/$count,2)*$rating_unitwidth;
	$rating1 = @number_format($current_rating/$count,1);
	$rating2 = @number_format($current_rating/$count,2);

	if ($static == 'static') {

			$static_rater = array();
			$static_rater[] .= "\n".'<div class="ratingblock">';
			$static_rater[] .= '<div id="unit_long'.$id.'">';
			$static_rater[] .= '<ul id="unit_ul'.$id.'" class="unit-rating" style="width:'.$rating_unitwidth*$units.'px;">';
			$static_rater[] .= '<li class="current-rating" style="width:'.$rating_width.'px;">Currently '.$rating2.'/'.$units.'</li>';
			$static_rater[] .= '</ul>';
			$static_rater[] .= '<p class="static">'.$id.'. Rating: <strong> '.$rating1.'</strong>/'.$units.' ('.$count.' '.$tense.' cast) <em>This is \'static\'.</em></p>';
			$static_rater[] .= '</div>';
			$static_rater[] .= '</div>'."\n\n";

			return join("\n", $static_rater);


	} else {

	      $rater ='';
	      $rater.='<div class="ratingblock">';

	      $rater.='<div id="unit_long'.$id.'">';
	      $rater.='  <ul id="unit_ul'.$id.'" class="unit-rating" style="width:'.$rating_unitwidth*$units.'px;">';
	      $rater.='     <li class="current-rating" style="width:'.$rating_width.'px;">Currently '.$rating2.'/'.$units.'</li>';

	      for ($ncount = 1; $ncount <= $units; $ncount++) { // loop from 1 to the number of units
	           if(!in_array_multi($userid, $numbers['userids'])) { // if the user hasn't yet voted, draw the voting stars
	              $rater.='<li><a href="anymedia.php?avote=rate&amp;j='.$ncount.'&amp;q='.$id.'&amp;t='.$userid.'&amp;c='.$units.'" title="'.$ncount.' out of '.$units.'" class="r'.$ncount.'-unit rater" rel="nofollow">'.$ncount.'</a></li>';
	           }
	      }
	      $ncount=0; // resets the count

	      $rater.='  </ul>';
	      $rater.='  <p';
	      if($voted){ $rater.=' class="voted"'; }
	      $rater.='>'.$id.' Rating: <strong> '.$rating1.'</strong>/'.$units.' ('.$count.' '.$tense.' cast)';
	      $rater.='  </p>';
	      $rater.='</div>';
	      $rater.='</div>';
	      return $rater;
	 }
}

function in_array_multi($needle, $haystack)
{
   if(!is_array($haystack)) return $needle == $haystack;
   foreach($haystack as $value) if(in_array_multi($needle, $value)) return true;
   return false;
}


function quickReply ()
{
	global $vbulletin;
	// *********************************************************************************
	// build quick reply if appropriate
	if ($show['quickreply'])
	{
		require_once(DIR . '/includes/functions_editor.php');

		$show['wysiwyg'] = ($forum['allowbbcode'] ? is_wysiwyg_compatible() : 0);
		$istyles_js = construct_editor_styles_js();

		// set show signature hidden field
		$showsig = iif($vbulletin->userinfo['signature'], 1, 0);

		// set quick reply initial id
		if ($threadedmode == 1)
		{
			$qrpostid = $curpostid;
			$show['qr_require_click'] = 0;
		}
		else if ($vbulletin->options['quickreply'] == 2)
		{
			$qrpostid = 0;
			$show['qr_require_click'] = 1;
		}
		else
		{
			$qrpostid = 'who cares';
			$show['qr_require_click'] = 0;
		}

		$editorid = construct_edit_toolbar('', 0, $foruminfo['forumid'], ($foruminfo['allowsmilies'] ? 1 : 0), 1, false, 'qr');
		$messagearea = "
			<script type=\"text/javascript\">
			<!--
				var threaded_mode = $threadedmode;
				var require_click = $show[qr_require_click];
				var is_last_page = $show[allow_ajax_qr]; // leave for people with cached JS files
				var allow_ajax_qr = $show[allow_ajax_qr];
				var ajax_last_post = " . intval($effective_lastpost) . ";
			// -->
			</script>
			$messagearea
		";

		if (is_browser('mozilla') AND $show['wysiwyg'] == 2)
		{
			// Mozilla WYSIWYG can't have the QR collapse button,
			// so remove that and force QR to be expanded
			$show['quickreply_collapse'] = false;

			unset(
				$vbcollapse["collapseobj_quickreply"],
				$vbcollapse["collapseimg_quickreply"],
				$vbcollapse["collapsecel_quickreply"]
			);
		}
		else
		{
			$show['quickreply_collapse'] = true;
		}
	}
	else if ($show['ajax_js'])
	{
		require_once(DIR . '/includes/functions_editor.php');

		$vBeditJs = construct_editor_js_arrays();
		eval('$vBeditTemplate[\'clientscript\'] = "' . fetch_template('editor_clientscript') . '";');
	}
	
}

function insert_tags()
{
	

	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "anymedia_tags (vid, tag, count) VALUES ('114', 'sexy', '')");
	
	
	
	
	
	/* 
	 * 
	 * foreach($shipment_base_invoice as $value) 
	{
	$insert="INSERT INTO boeing_shipmenttbl (shipment_base_invoice) VALUES ('$value')";UPDATE table SET foo = foo +1 WHERE bar = x
	mysql_query($insert) OR die(mysql_error())
	
	INSERT INTO vb_anymedia_tags (vid, tag, count) VALUES ('114', 'sexy', '')

	}*/
}

function get_tag_cloud()
{
	// Default font sizes
	$min_font_size = 12;
	$max_font_size = 30;
	// Pull in tag data
	$tags = get_tag_data();
	
	if ($tags)
	{
		$minimum_count = min(array_values($tags));
		$maximum_count = max(array_values($tags));
		$spread = $maximum_count - $minimum_count;

		if($spread == 0) {
		    $spread = 1;
		}
		$cloud_html = '';
		$cloud_tags = array(); // create an array to hold tag code
		foreach ($tags as $tag => $count) {
			$size = $min_font_size + ($count - $minimum_count) 
				* ($max_font_size - $min_font_size) / $spread;
			$cloud_tags[] = '<a style="font-size: '. floor($size) . 'px' 
				. '" class="tag_cloud" href="http://www.google.com/search?q=' . $tag 
				. '" title="\'' . $tag  . '\' returned a count of ' . $count . '">' 
				. htmlspecialchars(stripslashes($tag)) . '</a>';
		}
		$cloud_html = join("\n", $cloud_tags) . "\n";
		return $cloud_html;
	} else {
		return false;
	}
}

function get_tag_data()
{
	global $vbulletin, $stylevar;
	$result = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "anymedia_tags GROUP BY tag ORDER BY count DESC");
	if ($result)
	{
		while($gtag = $vbulletin->db->fetch_array($result)) { 
			$arr[$gtag['tag']] = $gtag['count'];
		} 

		if (is_array($arr))
		{
			ksort($arr);
		} 
		return $arr;
	} else {
		return false;
	}
	
}

?>