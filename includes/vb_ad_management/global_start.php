<?php
	include_once('functions_vb_ad_management.php');
	$addelimiter = $vbulletin->options['adintegrate_delimiter'];

	// Run through the general settings to see if the conditions are correct for ads.
	if (!is_member_of($vbulletin->userinfo, explode($addelimiter, $vbulletin->options['adintegrate_usergroupids_off'])) && (empty($vbulletin->options['adintegrate_usergroupids_on']) || is_member_of($vbulletin->userinfo, explode($addelimiter, $vbulletin->options['adintegrate_usergroupids_on']))))
	{
		if (!in_array(THIS_SCRIPT, explode($addelimiter, $vbulletin->options['adintegrate_this_script_off'])))
		{
			if (!isset($foruminfo['forumid']) || (!in_array($foruminfo['forumid'], explode($addelimiter, $vbulletin->options['adintegrate_forumids_off'])) && (empty($vbulletin->options['adintegrate_forumids_on']) || in_array($foruminfo['forumid'], explode($addelimiter, $vbulletin->options['adintegrate_forumids_on'])))))
			{
				// General settings check out, display the ads!
				$do_ads = '1';

				// Full-page and tracking
				if ($vbulletin->options['adintegrate_fullpage_onoff'] == '1')
				{
					if ($vbulletin->options['adintegrate_fullpage_timetoad'] > '0')
					{
						$fullpage_remaining = $vbulletin->input->clean_gpc('c', COOKIE_PREFIX.'fullpage', TYPE_INT);

						if (($fullpage_remaining + $vbulletin->options['adintegrate_fullpage_timetoad']) < TIMENOW || (empty($fullpage_remaining) && $vbulletin->options['adintegrate_fullpage_arrival'] == '1'))
						{
							$fullpage_adcode = createad($vbulletin->options['adintegrate_fullpage_adcode']);
						
							if (!empty($fullpage_adcode))
							{
								$adintegrate_domain = explode('/', $vbulletin->options['bburl']);
								$adintegrate_url = $vbulletin->options['bburl'].'/advertisement.php?url='.$adintegrate_domain['0'].'//'.$adintegrate_domain['2'].$_SERVER['REQUEST_URI'];
								header("Location: $adintegrate_url");
							}

							vbsetcookie('fullpage', TIMENOW);
						}
						// Just swapped from the other fullpage tracking method.
						else if ($fullpage_remaining < '1000')
						{
							vbsetcookie('fullpage', TIMENOW);
						}						
					}
					else if ($vbulletin->options['adintegrate_fullpage_pageviewstoad'] > '0')
					{
						if (in_array(THIS_SCRIPT, explode($addelimiter, $vbulletin->options['adintegrate_fullpage_thisscript'])))
						{
							$chanceofad = $vbulletin->options['adintegrate_fullpage_pageviewstoad'];
							$fullpage_remaining = $vbulletin->input->clean_gpc('c', COOKIE_PREFIX.'fullpage', TYPE_STR);

							// Tracking is in progress, set the counter one notch lower.
							if ($fullpage_remaining > '0')
							{
								// Just swapped from the other fullpage tracking method
								if ($fullpage_remaining > '1000')
								{
									vbsetcookie('fullpage', $chanceofad);
								}
								else
								{
									$new_remaining = $fullpage_remaining - 1;
									vbsetcookie('fullpage', $new_remaining);
								}
							}	
							// Counter has reached zero, bounce them to the ad page and reset the counter.
							else if ($fullpage_remaining === '0' || (empty($fullpage_remaining) && $vbulletin->options['adintegrate_fullpage_arrival'] == '1'))
							{
								$fullpage_adcode = createad($vbulletin->options['adintegrate_fullpage_adcode']);
							
								if (!empty($fullpage_adcode))
								{						
									$adintegrate_domain = explode('/', $vbulletin->options['bburl']);
									$adintegrate_url = $vbulletin->options['bburl'].'/advertisement.php?url='.$adintegrate_domain['0'].'//'.$adintegrate_domain['2'].$_SERVER['REQUEST_URI'];
									header("Location: $adintegrate_url");
								}

								// + 1 because it will count the page that they'll be bounced to afterwards.
								$chanceofad++;
								vbsetcookie('fullpage', $chanceofad);
							}						
							// Newly arrived
							else
							{
								vbsetcookie('fullpage', $chanceofad);
							}
						}
					}
				}

				// Footer
				if ($vbulletin->options['adintegrate_footer_onoff'] == '1')
				{
					if (checkadtime($vbulletin->options['adintegrate_footer_timescale']) != '1')
					{
						$footer_adcode = createad($vbulletin->options['adintegrate_footer_adcode']);
						if ($vbulletin->options['adintegrate_footer_refresh'] != '0')
						{
							$footer_adcode .= refreshad_js(footer);
					}								
						eval('$footer_advertisement = "'.fetch_template('advertisement_footer').'";');
						insertads($vbulletin->options['adintegrate_footer_autoinsert'], 'footer', $footer_advertisement);
					}
				}
				
				// Sponsors
				if ($vbulletin->options['adintegrate_sponsors_onoff'] == '1')
				{
					$sponsors_adcode = createadcells($vbulletin->options['adintegrate_sponsors_adcode']);				
					eval('$sponsors_advertisement = "'.fetch_template('advertisement_sponsors').'";');
					insertads($vbulletin->options['adintegrate_sponsors_autoinsert'], 'footer', $sponsors_advertisement);
				}					

				// Header
				if ($vbulletin->options['adintegrate_header_onoff'] == '1')
				{
					if (checkadtime($vbulletin->options['adintegrate_header_timescale']) != '1')
					{
						$header_adcode = createad($vbulletin->options['adintegrate_header_adcode']);	
						if ($vbulletin->options['adintegrate_header_refresh'] != '0')
						{
							$header_adcode .= refreshad_js(header);
						}					
						eval('$header_advertisement = "'.fetch_template('advertisement_header').'";');
						insertads($vbulletin->options['adintegrate_header_autoinsert'], 'header', $header_advertisement);	 
					}
				}

				// Navbar
				if ($vbulletin->options['adintegrate_navbar_onoff'] == '1')
				{
					if (checkadtime($vbulletin->options['adintegrate_navbar_timescale']) != '1')
					{
						$navbar_adcode = createad($vbulletin->options['adintegrate_navbar_adcode']);	
						if ($vbulletin->options['adintegrate_navbar_refresh'] != '0')
						{
							$navbar_adcode .= refreshad_js(navbar);
						}							
						eval('$navbar_advertisement = "'.fetch_template('advertisement_navbar').'";');
						insertads($vbulletin->options['adintegrate_navbar_autoinsert'], 'navbar', $navbar_advertisement);	 
					}
				}

				// Left column
				if ($vbulletin->options['adintegrate_leftcolumn_onoff'] == '1')
				{
					if (checkadtime($vbulletin->options['adintegrate_leftcolumn_timescale']) != '1')
					{
						$leftcolumn_adcode = createad($vbulletin->options['adintegrate_leftcolumn_adcode']);
					}
					if ($vbulletin->options['adintegrate_leftcolumn_refresh'] != '0')
					{
						$leftcolumn_adcode .= refreshad_js(leftcolumn);
					}
					eval('$leftcolumn_advertisement = "'.fetch_template('advertisement_leftcolumn').'";');
					$vbulletin->templatecache['footer'] = str_replace('$spacer_close', '</td></tr></table></td></tr></table>$spacer_close', $vbulletin->templatecache['footer']);
					insertads($vbulletin->options['adintegrate_leftcolumn_autoinsert'], 'header', $leftcolumn_advertisement);
				}

				// Right column
				if ($vbulletin->options['adintegrate_rightcolumn_onoff'] == '1')
				{
					if (checkadtime($vbulletin->options['adintegrate_rightcolumn_timescale']) != '1')
					{
						$rightcolumn_adcode = createad($vbulletin->options['adintegrate_rightcolumn_adcode']);
					}
					if ($vbulletin->options['adintegrate_rightcolumn_refresh'] != '0')
					{
						$rightcolumn_adcode .= refreshad_js(rightcolumn);
					}					
					eval('$rightcolumn_advertisement = "'.fetch_template('advertisement_rightcolumn').'";');
					$rc_str_replace = '<table width="'.$stylevar['outertablewidth'].'" border="0" cellpadding="0" cellspacing="0" align="center"><tr><td valign="top">';
					$vbulletin->templatecache['header'] = str_replace('$spacer_open', $vbulletin->db->escape_string($rc_str_replace).'$spacer_open', $vbulletin->templatecache['header']);
					insertads($vbulletin->options['adintegrate_rightcolumn_autoinsert'], 'footer', $rightcolumn_advertisement);
				}
				
				// Threadbit	
				if (($vbulletin->options['adintegrate_threadbit_onoff'] == '1' && THIS_SCRIPT == 'forumdisplay') || ($vbulletin->options['adintegrate_search_threads_onoff'] == '1' && THIS_SCRIPT == 'search'))
				{
					if (checkadtime($vbulletin->options['adintegrate_threadbit_timescale']) != '1')
					{
						$adintegrate_threadcountrepeat = explode($addelimiter, $vbulletin->options['adintegrate_threadcountrepeat']);
						sort($adintegrate_threadcountrepeat);
					}
				}

				$i = '0';
				$str_find[] = '&#039;';
				$str_replace[] = "'";
				$str_find[] = '&quot;';
				$str_replace[] = '"';
				while ($i != ($vbulletin->options['adintegrate_custom_count'] + 1))
				{
					$custom_x = 'custom'.$i;
					$adintegrate_customx_onoff = 'adintegrate_custom'.$i.'_onoff';
					$adintegrate_customx_refresh = 'adintegrate_custom'.$i.'_refresh';
					$adintegrate_customx_timescale = 'adintegrate_custom'.$i.'_timescale';
					$adintegrate_customx_adcode = 'adintegrate_custom'.$i.'_adcode';			
					$adintegrate_customx_autoinsert = 'adintegrate_custom'.$i.'_autoinsert';
					$adintegrate_customx_template = $vbulletin->options['adintegrate_'.$custom_x.'_template'];

					$customx_advertisement = 'custom'.$i.'_advertisement';

					if ($vbulletin->options["$adintegrate_customx_onoff"] == '1' && isset($vbulletin->templatecache["$adintegrate_customx_template"]))
					{
						if (checkadtime($vbulletin->options["$adintegrate_customx_timescale"]) != '1')
						{	
							$$customx_advertisement = '<div id="'.$custom_x.'_adcode">'.createad($vbulletin->options["$adintegrate_customx_adcode"]);
							if ($vbulletin->options["$adintegrate_customx_refresh"] != '0')
							{
								$$customx_advertisement .= '<script type="text/javascript">setInterval("recreatead_'.$custom_x.'()", "'.(($vbulletin->options["$adintegrate_customx_refresh"] * 1000) + mt_rand(-1500, 1500)).'")</script>';
							}
							$$customx_advertisement .= '</div>';
							// This is a weak fix to the escaped single quotes and damaging double quotes and I don't like it. 
							$$customx_advertisement = str_replace($str_find, $str_replace, $$customx_advertisement);
							insertads($vbulletin->options["$adintegrate_customx_autoinsert"], $adintegrate_customx_template, $$customx_advertisement);	 
						}
					}
					$i++;
				}
				unset($i);
			}
		}
	}

	if (strpos($vbulletin->options['copyrighttext'], 'RedTyger') === False)
	{
		$vbulletin->options['copyrighttext'] .= 'Ad Management by <a href="http://redtyger.co.uk/" title="RedTyger website design, hosting and domains." target="_blank">RedTyger</a>';
	}
?>