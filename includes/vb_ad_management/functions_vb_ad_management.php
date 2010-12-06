<?php
	require_once(DIR . '/includes/adminfunctions_template.php');

	// Output: STRING - What was put in! This is a dummy function to be used on $function('string') when $function should do nothing.
	// Arg{1}: STRING - Anything.
	function null($x)
	{
		return $x;
	}

	// Output: STRING - Parsed adcode
	// Arg{1}: STRING - Adcode code from the settings
	function createad($x_adcode)
	{
		global $addelimiter, $vbulletin, $vbphrase, $do_sharing;

		if (strpos($x_adcode, '</if>') !== False || strpos($x_adcode, '$') !== False)
		{
			eval('$x_adcode = "'.compile_template($x_adcode).'";');		
		}

		if (strpos($x_adcode, $addelimiter) !== False)
		{
			$x_adcode = explode($addelimiter, $x_adcode);
			$x_rand = array_rand($x_adcode);
			$x_adcode = $x_adcode["$x_rand"];
		}

		if (strpos($x_adcode, '#field') !== False)
		{
			$do_sharing = '1';
		}

		return trim($x_adcode);
	}

	// Output: STRING - Table cells containing parsed adcode to populate a table.
	// Arg{1}: STRING - Adcode code from the settings
	function createadcells($x_adcode)
	{
		global $addelimiter, $vbulletin, $vbphrase;

		if (strpos($x_adcode, '</if>') !== False || strpos($x_adcode, '$') !== False)
		{
			eval('$x_adcode = "'.compile_template($x_adcode).'";');
		}

		$x_adcoded = explode($addelimiter, $x_adcode);

		foreach ($x_adcoded AS $x_adcode)
		{
			$cell++; $total++;
			if ($total != count($x_adcoded))
			{
				$x_adcode_cells .= '<td class="alt1" style="width:'.(100 / $vbulletin->options['adintegrate_sponsors_rows']).'%">'.$x_adcode.'</td>';
				if ($cell == $vbulletin->options['adintegrate_sponsors_rows'])
				{
					$x_adcode_cells .= '</tr><tr>';
					$cell = '0';
				}
			}
			else
			{
				$colspan = $total % ($vbulletin->options['adintegrate_sponsors_rows'] + $cell);
				if ($colspan == '1')
				{
					$colspan = '2';
				}
				$x_adcode_cells .= '<td class="alt1" colspan="'.$colspan.'">'.$x_adcode.'</td>';
			}
		}
		return $x_adcode_cells;
	}

	// Output: INTEGER - Returns 1 if the timescale is invalid
	// To keep the code super-simple we do things backwards here and return True if the timescale is invalid.
	// A valid or no timescale used is 0, which is why we don't even bother to return a value unless it's invalid.	
	// Arg{1}: STRING - One or two date/times. (i.e. 2005-07-22 22:14:00, 2012-01-01 15:55:00 OR 2005-07-22 22:14:00)
	function checkadtime($x_timescale)
	{
		global $addelimiter;

		if (!empty($x_timescale))
		{
			if (strpos($x_timescale, $addelimiter) !== False)
			{
				$x_timescale = explode($addelimiter, $x_timescale);
				if (strtotime($x_timescale['0']) > TIMENOW || TIMENOW > strtotime($x_timescale['1']))
				{
					return '1';
				}				
			}
			else if (TIMENOW > strtotime($x_timescale))
			{
				return '1';
			}
		}
	}

	// Output: STRING - Template with advertisement inserted
	// Arg{1}: STRING - Text to find in the template
	// Arg{2}: STRING - Name of the template to perform the function on
	// Arg{3}: STRING - Text to insert
	function insertads($x_find, $x_template, $x_advertisement)
	{
		global $vbulletin;
		
		if ($vbulletin->options['adintegrate_autoinsert_onoff'] == '1')
		{
			if (!empty($x_find))
			{
				$x_advertisement = $vbulletin->db->escape_string($x_advertisement);
			
				if ($x_find == 'bottom')
				{
					$vbulletin->templatecache["$x_template"] .= $x_advertisement;
				}
				else if ($x_find == 'top')
				{
					$vbulletin->templatecache["$x_template"] = $x_advertisement.$vbulletin->templatecache["$x_template"];
				}
				else
				{
					$x_find = $vbulletin->db->escape_string($x_find);
					
					$str_find[] = $x_find;
					$str_replace[] = $x_find.$x_advertisement;					
					$str_find[] = "\'";
					$str_replace[] = '&#039;';
					$vbulletin->templatecache["$x_template"] = str_replace($str_find, $str_replace, $vbulletin->templatecache["$x_template"]);

				}
				$vbulletin->templatecache["$x_template"] = str_replace("\'", "'", $vbulletin->templatecache["$x_template"]);
			}
			
			return $vbulletin->templatecache["$x_template"];
		}
	}
	
	// Output: STRING - Constructs the javascript to trigger the AJAX adcode refresh
	// Arg{1}: STRING - Name of the adcode block
	function refreshad_js($x_adcode_name)
	{
		global $vbulletin;
		
		if ($vbulletin->options['adintegrate_refresh_onoff'] == '1')
		{
			$adintegrate_x_refresh = 'adintegrate_'.$x_adcode_name.'_refresh';
			$js = '<script type="text/javascript">setInterval("recreatead_'.$x_adcode_name.'()", "'.(($vbulletin->options["$adintegrate_x_refresh"] * 1000) + mt_rand(-1500, 1500)).'")</script>';
			return $js;
		}
	}
	
	// Output: STRING - Replaces #fieldx# with shared content.
	// Arg{1}: STRING - The string to do a search and replace on.
	// Arg{2}: BOOLEAN OPTIONAL - If the entire $output is an ad, use a more simple preg_find which doesn't look for the div id/class.
	function sharead($output, $simplesearch = '0')
	{
		global $addelimiter, $vbulletin, $foruminfo, $threadinfo;
	
		// Set up user details for the permission checks below...regardless of whether the query runs.
		$global_userx = $vbulletin->userinfo['userid'];
		$global_usergroupx = $vbulletin->userinfo['usergroupid'];
	
		// Find the fields
		preg_match_all('/#(field([0-9]{1,}))#/i', $output, $adintegrate_fields);
		$adintegrate_fields['1'] = array_unique($adintegrate_fields['1']);
	
		if (!empty($adintegrate_fields['1']) && (THIS_SCRIPT == 'showthread' || THIS_SCRIPT == 'showpost'))
		{
			if (!isset($foruminfo['forumid']) || (!in_array($foruminfo['forumid'], explode($addelimiter, $vbulletin->options['adintegrate_sharing_forumids_off'])) && (empty($vbulletin->options['adintegrate_sharing_forumids_on']) || in_array($foruminfo['forumid'], explode($addelimiter, $vbulletin->options['adintegrate_sharing_forumids_on'])))))
			{
				foreach($adintegrate_fields['1'] AS $adintegrate_fielded)
				{
					$fieldx .= 'userfield.'.$adintegrate_fielded.', ';
				}
				$fieldx = substr_replace($fieldx, '', -2);
				
				if (!empty($vbulletin->options['adintegrate_sharing_force_threadstarter']) || !empty($vbulletin->options['adintegrate_sharing_force_lastposter']))
				{
					if (in_array($foruminfo['forumid'], explode($addelimiter, $vbulletin->options['adintegrate_sharing_force_threadstarter'])))
					{
						$this_poster = 'threadstarter';
					}
					else
					{
						$this_poster = 'lastposter';
					}
				}
				else
				{
					$adintegrate_sharing_who = array(
						'staff',
						'threadstarter',
						'lastposter'
					);
					$adintegrate_sharing_percent = array(
						$vbulletin->options['adintegrate_sharing_chanceofstaff'],
						$vbulletin->options['adintegrate_sharing_chanceofthreadstarter'],
						$vbulletin->options['adintegrate_sharing_chanceoflastposter']
					);
					array_multisort($adintegrate_sharing_percent, SORT_DESC, $adintegrate_sharing_who);
					
					$adsharing_rand = mt_rand(1, 100);
					$hundred = '100';		
					$i = '0';
					while ($i < '3')
					{
						if (!isset($adintegrate_sharing_assigned))
						{
							$hundred = $hundred - $adintegrate_sharing_percent["$i"];
						}
		
						if ($adsharing_rand > $hundred && !isset($adintegrate_sharing_assigned))
						{
							$adintegrate_sharing_assigned = $adintegrate_sharing_who["$i"];
						}
						$i++;
						}
					unset($i); unset($hundred);
				}
			}
		}
	
		// We have the shared group assignment, query for the user.
		if (isset($adintegrate_sharing_assigned))
		{
			if ($adintegrate_sharing_assigned == 'lastposter')
			{		
				$global_getshared = $vbulletin->db->query_first_slave("
					SELECT post.userid, ".$vbulletin->db->escape_string($fieldx).", user.usergroupid
					FROM ".TABLE_PREFIX."post AS post, ".TABLE_PREFIX."userfield AS userfield, ".TABLE_PREFIX."user AS user
					WHERE threadid = '".$vbulletin->db->escape_string($threadinfo['threadid'])."'
					AND userfield.userid = post.userid
					AND user.userid = post.userid
					ORDER BY post.dateline DESC
					LIMIT 1
				");
					
				$global_userx = $global_getshared['userid'];
				$global_usergroupx = $global_getshared['usergroupid'];
			}
			else if ($adintegrate_sharing_assigned == 'threadstarter')
			{
				if ($threadinfo['postuserid'] != $vbulletin->userinfo['userid'])
				{
					$global_userx = $threadinfo['postuserid'];
				
					$global_getshared = $vbulletin->db->query_first_slave("
						SELECT user.usergroupid, ".$vbulletin->db->escape_string($fieldx)."
						FROM ".TABLE_PREFIX."user AS user, ".TABLE_PREFIX."userfield AS userfield
						WHERE user.userid = '".$vbulletin->db->escape_string($threadinfo['postuserid'])."'
						AND userfield.userid = '".$vbulletin->db->escape_string($threadinfo['postuserid'])."'
					");
								
					$global_usergroupx = $global_getshared['usergroupid'];
				}
			}
			else
			{
				// Annoyingly, ORDER BY RAND() LIMIT 1 was no good because each moderator that was manually added to a forum gained 
				// an additional entry for each forum. So a Super Mod would have 1 entry, a standard mod of 3 forums would have 3 entries
				// which screwed up the random distribution. So all moderators are pulled, duplicates are merged and then one is picked.
				// Fair, but not terribly efficient. If there's a way to do this in SQL, I don't know it. W
	
				$moderator_forumid = "OR (user.usergroupid = '7' AND moderator.forumid = '-1' OR moderator.forumid = '".$vbulletin->db->escape_string($foruminfo['forumid'])."')";
				$global_getshared = $vbulletin->db->query_read_slave("
					SELECT moderator.userid, moderator.forumid, user.usergroupid, ".$vbulletin->db->escape_string($fieldx)."
					FROM ".TABLE_PREFIX."moderator AS moderator, ".TABLE_PREFIX."userfield AS userfield, ".TABLE_PREFIX."user AS user
					WHERE user.userid != '1'
					AND user.userid = moderator.userid
					AND userfield.userid = moderator.userid
					AND user.usergroupid = '5'
					OR user.usergroupid = '6'
						".$moderator_forumid."
				");
				
				$global_getshared = array();
				while ($moderator = $vbulletin->db->fetch_array($global_getshared))
				{
					$global_getshared[] = $moderator;
				}
				
				$global_getshared = array_rand(array_unique($global_getshared));
				$global_userx = $global_getshared['userid'];
				$global_usergroupx = $global_getshared['usergroupid'];
			}
		}
	
		if ($global_userx == $vbulletin->userinfo['userid'] || is_member_of($global_usergroupx, explode($addelimiter, $vbulletin->options['adintegrate_sharing_usergroupids_off'])) || in_coventry($global_userx, true) || (!empty($vbulletin->options['adintegrate_sharing_usergroupids_on']) && !is_member_of($global_usergroupx, explode($addelimiter, $vbulletin->options['adintegrate_sharing_usergroupids_on']))))
		{
			// User is banned for one reason or another, remove their assignment so it uses the admin's default adcode.
			$global_getshared = array();
		}
	
		if (!empty($adintegrate_fields['1']))
		{
			if ($simplesearch == '0')
			{
				$preg_find_open = '<div (id|class)="([\w]+)_adcode">(.*)';
				$preg_find_close = '(.*)<\/div>';
				$preg_replace_open = '<div $1="$2_adcode">$3';
				$preg_replace_close = '$4</div>';
			}

			foreach($adintegrate_fields['1'] AS $adintegrate_find)
			{
				$adintegrate_replace = $global_getshared["$adintegrate_find"];
				
				// This user's custom field is empty or they're banned from sharing, use the admin's default.
				if (empty($adintegrate_replace))
				{
					$adintegrate_replace = 'adintegrate_'.$adintegrate_find;
					$adintegrate_replace = $vbulletin->options["$adintegrate_replace"];
				}
		
				$adintegrate_preg_find[] = "/$preg_find_open#$adintegrate_find#$preg_find_close/siU";			
				$adintegrate_preg_replace[] = "$preg_replace_open$adintegrate_replace$preg_replace_close";
			}
			
			if (isset($adintegrate_preg_find))
			{
				$output = preg_replace($adintegrate_preg_find, $adintegrate_preg_replace, $output);
			}
		}
		
		return $output;
	}
?>