<?php 

 /******************************************************************************************
 * vBSEO Google/Yahoo Sitemap Generator for vBulletin v3.x.x by Crawlability, Inc.         *
 *-----------------------------------------------------------------------------------------*
 *                                                                                         *
 * Copyright © 2005-2008, Crawlability, Inc. All rights reserved.                          *
 * You may not redistribute this file or its derivatives without written permission.       *
 *                                                                                         *
 * Sales Email: sales@crawlability.com                                                     *
 *                                                                                         *
 *-------------------------------------LICENSE AGREEMENT-----------------------------------*
 * 1. You are free to download and install this plugin on any vBulletin forum for which    *
 *    you hold a valid vB license.                                                         *
 * 2. You ARE NOT allowed to REMOVE or MODIFY the copyright text within the .php files     *
 *    themselves.                                                                          *
 * 3. You ARE NOT allowed to DISTRIBUTE the contents of any of the included files.         *
 * 4. You ARE NOT allowed to COPY ANY PARTS of the code and/or use it for distribution.    *
 ******************************************************************************************/

	global $vbulletin, $vbseo_vars, $vbseo_stat, $vboptions, $db, $forumcache, $bbuserinfo;

	error_reporting(E_ALL & ~E_NOTICE);
	define('VBSEO_SM_VERSION', '2.2');

	if(is_object($vbulletin))
	{
		$vboptions = $vbulletin->options;
		$forumcache = $vbulletin->forumcache;
		$bbuserinfo = $vbulletin->userinfo;
		if(!defined('CANVIEW'))
		{
			define('CANVIEW', $vbulletin->bf_ugp_forumpermissions['canview']);
			define('CANVIEWOTHERS', $vbulletin->bf_ugp_forumpermissions['canviewothers']);
			define('CANVIEWTHREADS', $vbulletin->bf_ugp_forumpermissions['canviewthreads']);
		}
		$vboptions['hideprivateforums'] = !$vboptions['showprivateforums'];
	}
		

	if($vbulletin->db)
		$GLOBALS['db'] = &$vbulletin->db;
		else
		$GLOBALS['db'] = &$DB_site;

	define('VBSEO_VB_IS_30', !isset($vboptions['vbseo_sm_maxurls']));
    if(VBSEO_VB_IS_30)
    {
    	require_once(dirname(__FILE__) . '/vbseo_sm_config30x.php');
    	$vboptions = array_merge($vboptions, $vbseo_sm_config30x);
    }

 	if($vboptions['vbseo_sm_vbseo'])
	if(@include_once(DIR . '/includes/functions_vbseo.php'))
	{
	   	vbseo_startup();
	   	if(!$GLOBALS['g_cache'])
	   		$GLOBALS['g_cache'] = & $GLOBALS['vbseo_gcache'];
   	}
   	define('VBSEO_ON', defined('VBSEO_ENABLED') && VBSEO_ENABLED && $vboptions['vbseo_sm_vbseo']);

   	$vbseo_vars['bburl']  = preg_replace('#/+$#', '', $vboptions['bburl']);
   	$vbseo_vars['topurl'] = preg_replace('#/+$#', '', $vboptions['vbseo_sm_toppath'] ? $vboptions['vbseo_sm_toppath'] : $vboptions['bburl']);

	define('VBSEO_SLASH_METHOD', ((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' AND stristr($_SERVER['SERVER_SOFTWARE'], 'apache') === false) OR (strpos(SAPI_NAME, 'cgi') !== false AND @!ini_get('cgi.fix_pathinfo'))) ? '?' : '/');

// ================================================================================
// ================================================================================
// ================================================================================

   	function vbseo_get_last_tpl_update()
   	{
   		global $db, $vboptions, $vbseo_vars;

      	$tpl_update = $db->query_first("
            SELECT max( dateline ) as max
            FROM `".TABLE_PREFIX."template`
            WHERE styleid = '$vboptions[styleid]'
      	");

      	$vbseo_vars['tpl_update'] = $tpl_update['max'];
   	}

   	function vbseo_get_forumlist($parentid = 0)
   	{
    	global $vbulletin, $vboptions, $forumcache, $_FORUMOPTIONS, $bbuserinfo;

    	if($vbulletin->forumcache)
    		$forumcache = $vbulletin->forumcache;


    	$forumlist = array();

		$forums_scope = array_keys($forumcache);

    	foreach ($forums_scope AS $forumid)
		if($forumid>0)
		{
    		$forum = $forumcache["$forumid"];
    		if (!($forum['options'] & ($_FORUMOPTIONS?$_FORUMOPTIONS['active']:$vbulletin->bf_misc_forumoptions['active'])))
			{
				//continue;
			}

			$forumperms = $bbuserinfo['forumpermissions']["$forumid"];
			if ((!($forumperms & CANVIEW)
				||!($forumperms & CANVIEWOTHERS)
				||(defined('CANVIEWTHREADS')&&!($forumperms & CANVIEWTHREADS))) 
				
				AND ($vboptions['hideprivateforums']||!isset($vboptions['hideprivateforums'])) )
			{
				continue;
    		}

   			$forumlist[] = $forumid;

    	}   

    	return array_unique($forumlist);
   	}

   	function vbseo_sitemap_extra()
   	{
   		global $vbseo_vars;
   		
   		if(vbseo_check_progress(2)) return;

   		if(file_exists($vbseo_vars['extra_urls']) && filesize($vbseo_vars['extra_urls'])>0)
   		{
   			$pf = fopen($vbseo_vars['extra_urls'], 'r');

   			if($pf)
   			while(!feof($pf))
   			{
   				$nurl = trim(fgets($pf, 1024));
   				if($nurl)
   				{
   					$url_part = explode(',', $nurl);
        			vbseo_add_url($url_part[0], $url_part[2]?$url_part[2]:1.0, 0, $url_part[1]);
        		}
   			}
   			fclose($pf);
   		}
   	}

   	function vbseo_sitemap_forumdisplay($archived = false)
   	{
   		global $db, $vboptions, $vbseo_vars, $forumcache;
   		
   		if(vbseo_check_progress($archived?6:3)) return;

   		$added_urls = 0;

   		$perpage = $archived ? $vboptions['archive_threadsperpage'] : $vboptions['maxthreads'];
       	vbseo_log_entry("[SECTION START] forumdisplay".($archived?" archived":""), true);

   		foreach($vbseo_vars['forumslist'] as $forumid)
   		{
   			if($forumcache[$forumid]['link'])continue;
   			$dprune = $forumcache[$forumid]['daysprune'];
        	$threadscount = $db->query_first("
        		SELECT COUNT(*) AS threads
        		FROM " . TABLE_PREFIX . "thread AS thread
        		WHERE forumid = $forumid
        			AND sticky = 0
        			AND visible = 1
        			".($archived?"AND open != 10":"")."
        			".($dprune>0?"AND lastpost >= " . (time() - ($dprune * 86400)):"")."
        	");
        	$totalthreads = $threadscount['threads'];
        	$totalpages = max(ceil($totalthreads / $perpage),1);
        	
        	vbseo_log_entry("[forumdisplay] forum_id: $forumid, total threads: $totalthreads, pages: $totalpages", !$archived);
		
			for($p=1; $p<=$totalpages; $p++)
			{
  			$added_urls += vbseo_add_2urls(
  				vbseo_url_forum($forumid, $p, $archived),
  				vbseo_url_forum($forumid, $p, $archived, true),
  	   			$vboptions['vbseo_sm_priority_f'],
  				$forumcache[$forumid]['lastpost'],
  				$vboptions['vbseo_sm_freq_f']
			);
			}
   		}
   		return $added_urls;
   	}

   	function vbseo_math_avg_weight($value, $min, $max, $avg)
   	{
   		if($value > $avg)
   			$relp = (($max - $avg) > 0 ? 
   				($value - $avg)/($max - $avg)*0.5 : 0 ) + 0.5;
   		else
   			$relp = $avg > 0 ? ($avg - $value)*0.5/$avg : 0;

   		return $relp;
   	}

   	function vbseo_sitemap_showthread($archived = false, $showpost = false)
   	{
   		global $db, $vboptions, $vbseo_vars, $vbseo_stat, $vbseo_progress;

   		if(vbseo_check_progress($archived?7:4)) return;

   		$added_urls = 0;
   		$perpage = $archived ? $vboptions['archive_postsperpage'] : $vboptions['maxposts'];
       	vbseo_log_entry("[SECTION START] showthread".($archived?" archived":""), true);

       	$from_forum = $vbseo_progress['step2'];
       	$smart_p_pingbacks = $vboptions['vbseo_sm_priority_smart'] && VBSEO_ON && !VBSEO_VB_IS_30 &&
       		(defined('VBSEO_IN_PINGBACK')&&(VBSEO_IN_PINGBACK || VBSEO_IN_TRACKBACK));

       	if($smart_p_pingbacks)
       	{
        	$lb_qnew = vbseo_dbtbl_exists('vbseo_linkback');
        	$lb_tblname = $lb_qnew ? 'vbseo_linkback' : 'linkback';

        	$mp_query = $db->query("
        		SELECT t_threadid,count(*) as cnt
        		FROM " . TABLE_PREFIX . $lb_tblname."
        		GROUP BY t_threadid
        	");
        	if(!$mp_query)
        	$mp_query = $db->query("
        		SELECT t_threadid,count(*) as cnt
        		FROM " . TABLE_PREFIX . "vbseo_linkback
        		GROUP BY t_threadid
        	");
        	$mp_array = array();
        	$max_ping = 0;
			while ($nextmp = $db->fetch_array($mp_query))
			{
				$mp_array[$nextmp['t_threadid']] = $nextmp['cnt'];
				if($nextmp['cnt']>$max_ping)$max_ping=$nextmp['cnt'];
			}
       	}

   		foreach($vbseo_vars['forumslist'] as $forumid)
   		{
   			if($from_forum && $from_forum!=$forumid)
   				continue;

   			$from_forum = 0;
			$vbseo_progress['step2'] = $forumid;

        	$st = $db->query_first("
        		SELECT count(*) as cnt
        			,max(views) as maxv,avg(views) as avgv
        			,max(replycount) as maxre,avg(replycount) as avgre
        		FROM " . TABLE_PREFIX . "thread 
        		WHERE forumid = $forumid
        			AND visible = 1
        	");
        	$getthreads = $db->query("
        		SELECT *
        		FROM " . TABLE_PREFIX . "thread AS thread
	            LEFT JOIN " . TABLE_PREFIX . "deletionlog AS deletionlog ON(deletionlog.primaryid = thread.threadid AND type = 'thread')
        		WHERE forumid = $forumid
        			AND visible = 1
        			AND deletionlog.primaryid IS NULL
        		LIMIT ".intval($vbseo_progress['step3']).",".$st['cnt']."
        	");
			
			while ($threadrow = $db->fetch_array($getthreads))
			{
				$vbseo_progress['step3']++;
				if($threadrow['open'] == 10) continue;

            	$totalposts = $threadrow['replycount'] + 1;
            	$totalpages = ceil($totalposts / $perpage);
            	
   		    	$prior = $vboptions['vbseo_sm_priority_t'];
    		    if($vboptions['vbseo_sm_priority_smart'])
   		    	{
	   		    	if($threadrow['sticky'])
	   		    	{
	   		    		$prior = 1;
	   		    	}
	   		    	else
	   		    	{
   		    		$rate = $threadrow['votenum'] ? $threadrow['votetotal']/$threadrow['votenum'] : 0;
   		    		$relp1 = vbseo_math_avg_weight($threadrow['views'], 0, $st['maxv'], $st['avgv']);
   		    		$relp2 = vbseo_math_avg_weight($threadrow['replycount'], 0, $st['maxre'], $st['avgre']);
   		    		$relp3 = $rate/5;
   		    		$relp4 = $max_ping?$mp_array[$threadrow['threadid']]/$max_ping:0;

   		    		$relp = $relp1*0.45 + $relp2*0.25 + $relp3*0.15 + $relp4*0.15;
   		    		$relp = min(1,max($relp,0));

   		    		$prior = number_format($prior + $relp * (1-$prior), 2);
   		    		}
   		    	}

    		    if($vboptions['vbseo_sm_freq_tsmart'])
   		    	{
   		    		$dpassed = (time() - $threadrow['lastpost'])/86400;
   		    		if($dpassed<3)$freq = 'daily';
   		    		else if($dpassed<10)$freq = 'weekly';
   		    		else if($dpassed<100)$freq = 'monthly';
   		    		else $freq = 'yearly';
   		    	}else
    		    	$freq = $vboptions['vbseo_sm_freq_t'];
    		    	     
            	vbseo_log_entry("[showthread] forum_id: $forumid, thread_id: $threadrow[threadid], total posts: $totalposts, pages: $totalpages, views: $threadrow[views] $prior");

    			for($p=1; $p<=$totalpages; $p++)
    			{
    			$vbseo_stat[$archived?'at':'t'] += vbseo_add_2urls(
    				vbseo_url_thread($threadrow, $p, $archived),
    				vbseo_url_thread($threadrow, $p, $archived, true),
    				$prior,
    				$threadrow['lastpost'],
					$freq
    			);
				
				}

    			if($showpost)
    			{

            	$getposts = $db->query("
            		SELECT p.dateline,p.postid,p.threadid
            		FROM " . TABLE_PREFIX . "post AS p
            		WHERE p.threadid = $threadrow[threadid]
            		    AND visible = 1
            		ORDER BY p.dateline
            	");
    			
    			$pcount = 0;
    			while ($postrow = $db->fetch_array($getposts))
    			{
    				$pcount++;
                	vbseo_log_entry("[showpost] forum_id: $forumid, thread_id: $postrow[threadid], post_id: $postrow[postid]");
	    			$vbseo_stat['p'] += vbseo_add_2urls(
        				vbseo_url_post($threadrow, $postrow, $pcount),
        				vbseo_url_post($threadrow, $postrow, $pcount, true),
    		   			$vboptions['vbseo_sm_priority_p'],
        				$postrow['dateline'],
    					$vboptions['vbseo_sm_freq_p']
        			);
				}
    			$db->free_result($getposts);
    			}
			}
			$db->free_result($getthreads);
			$vbseo_progress['step3'] = 0;
   		}
   	}

   	function vbseo_sitemap_polls()
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress(9)) return;
   		$added_urls = 0;
       	vbseo_log_entry("[SECTION START] polls", true);

   		foreach($vbseo_vars['forumslist'] as $forumid)
   		{
        	$getthreads = $db->query("
        		SELECT *
        		FROM " . TABLE_PREFIX . "thread AS thread
        		WHERE forumid = $forumid
        			AND visible = 1
        			AND pollid > 0
        	");

			while ($threadrow = $db->fetch_array($getthreads))
			{
            	$getpoll = $db->query_first("
            		SELECT *
            		FROM " . TABLE_PREFIX . "poll
            		WHERE pollid = ".$threadrow['pollid']."
            	");
            	if(!$getpoll)
            		continue;

            	vbseo_log_entry("[poll] forum_id: $forumid, thread_id: $threadrow[threadid], pollid: $threadrow[pollid]");

    			$added_urls++;

    			$added_urls += vbseo_add_2urls(
    				vbseo_url_poll($threadrow, $getpoll),
    				vbseo_url_poll($threadrow, $getpoll, true),
   		   			$vboptions['vbseo_sm_priority_poll'],
    				$getpoll['dateline'],
   					$vboptions['vbseo_sm_freq_poll']
    			);
				
			}
			$db->free_result($getthreads);
   		}
   		return $added_urls;
   	}

   	function vbseo_sitemap_blogs()
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress(10)) return;
       	vbseo_log_entry("[SECTION START] blogs", true);
        vbseo_add_url(VBSEO_ON ? vbseo_any_url($vbseo_vars['bburl'].'/blog.'.VBSEO_PHP_EXT) : $vbseo_vars['bburl'].'/blog.'.VBSEO_PHP_EXT, 1.0);
   		$added_urls = 0;

      	if(!vbseo_dbtbl_exists('blog'))
      		return 0;

       	$getblogs = $db->query("
       		SELECT *
       		FROM " . TABLE_PREFIX . "blog
       		WHERE state = 'visible'
       	");
   		while ($blogrow = $db->fetch_array($getblogs))
   		{
           	vbseo_log_entry("[blog] blog_id: $blogrow[blogid]");

   			$added_urls += vbseo_add_2urls(
   				vbseo_url_blog_entry($blogrow),
   				vbseo_url_blog_entry($blogrow, true),
  		   		$vboptions['vbseo_sm_priority_b'],
   				$blogrow['dateline'],
  				$vboptions['vbseo_sm_freq_b']
   			);
   			
   		}
   		$db->free_result($getblogs);
   		return $added_urls;
   	}

   	function vbseo_sitemap_member()
   	{
   		global $db, $vboptions;

   		if(vbseo_check_progress(8)) return;
       	vbseo_log_entry("[SECTION START] member", true);

   		$added_urls = 0;
        $st = $db->query_first("
      		SELECT count(*) as cnt
      		FROM " . TABLE_PREFIX . "user
        ");
      	$getmembers = $db->query("
      		SELECT userid, username, lastpost
      		FROM " . TABLE_PREFIX . "user
      		ORDER BY username
       		LIMIT ".intval($vbseo_progress['step2']).",".$st['cnt']."
      	");
  		
  		while ($member = $db->fetch_array($getmembers))
  		{
          	vbseo_log_entry("[member] user_id: $member[userid]");
          	$vbseo_progress['step2']++;
  		
  			$added_urls += vbseo_add_2urls(
  				vbseo_url_member($member['userid'], $member['username']),
  				vbseo_url_member($member['userid'], $member['username'], true),
	   			$vboptions['vbseo_sm_priority_m'],
  				$member['lastpost'],
				$vboptions['vbseo_sm_freq_m']
  			);
  		}
  		$db->free_result($getmembers);
  		return $added_urls;
   	}

   	function vbseo_sitemap_albums()
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress(11)) return;
       	vbseo_log_entry("[SECTION START] albums", true);

      	if(!vbseo_dbtbl_exists('album') || !function_exists('vbseo_album_url_row'))
      		return 0;

   		$added_urls = 0;

       	$getrecords = $db->query("
       		SELECT a.*,u.username
       		FROM " . TABLE_PREFIX . "album a
       		LEFT JOIN " . TABLE_PREFIX . "user u on u.userid=a.userid
       		WHERE visible = 2
       		ORDER BY userid
       	");
       	$ouserid = 0;
   		while ($rrow = $db->fetch_array($getrecords))
   		{
           	vbseo_log_entry("[album] album_id: $rrow[albumid]");

           	if($ouserid!=$rrow['userid'])
           	{
           	$rrow2 = $rrow;
           	unset($rrow2['albumid']);
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_album($rrow2, false, 'VBSEO_URL_MEMBER_ALBUMS'),
   				vbseo_url_album($rrow2, true),
  		   		$vboptions['vbseo_sm_priority_a'],
   				$rrow['lastpicturedate'],
  				$vboptions['vbseo_sm_freq_a']
   			);
   			$ouserid = $rrow['userid'];
           	}

   			$added_urls += vbseo_add_2urls(
   				vbseo_url_album($rrow, false, 'VBSEO_URL_MEMBER_ALBUM'),
   				vbseo_url_album($rrow, true),
  		   		$vboptions['vbseo_sm_priority_a'],
   				$rrow['lastpicturedate'],
  				$vboptions['vbseo_sm_freq_a']
   			);
   			
         	$getitems = $db->query("
         		SELECT ap.*,p.caption
         		FROM " . TABLE_PREFIX . "albumpicture ap
         		LEFT JOIN " . TABLE_PREFIX . "picture p on p.pictureid=ap.pictureid
         		WHERE state = 'visible' AND albumid = '".$rrow['albumid'] ."'
         	");
     		while ($ritem = $db->fetch_array($getitems))
     		{
             	vbseo_log_entry("[picture] picture_id: $ritem[pictureid]");
             	$ritem = array_merge($rrow, $ritem);

     			$added_urls += vbseo_add_2urls(
     				vbseo_url_album($ritem, false, 'VBSEO_URL_MEMBER_PICTURE'),
     				vbseo_url_album($ritem, true),
    		   		$vboptions['vbseo_sm_priority_a'],
     				$ritem['dateline'],
    				$vboptions['vbseo_sm_freq_a']
     			);
     			
     		}
     		$db->free_result($getitems);
   		}
   		$db->free_result($getrecords);

   		return $added_urls;
   	}

   	function vbseo_sitemap_groups()
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress(12)) return;
       	vbseo_log_entry("[SECTION START] groups", true);

      	if(!vbseo_dbtbl_exists('socialgroup') || !function_exists('vbseo_group_url_row'))
      		return 0;

   		$added_urls = 0;

   		$added_urls += vbseo_add_2urls(
   			vbseo_url_group(array(), false, VBSEO_URL_GROUPS_HOME),
   			vbseo_url_group(array(), true),
  	   		$vboptions['vbseo_sm_priority_g'],
   			0,
  			$vboptions['vbseo_sm_freq_g']
   		);

       	$getrecords = $db->query("
       		SELECT g.*
       		FROM " . TABLE_PREFIX . "socialgroup g
       		WHERE type = 'public'
       	");
       	$ouserid = 0;
   		while ($rrow = $db->fetch_array($getrecords))
   		{
           	vbseo_log_entry("[album] group_id: $rrow[groupid]");
         	$tcount = $db->query_first("
         		SELECT count(*)as cnt,max(dateline) as lastupdate
         		FROM " . TABLE_PREFIX . "groupmessage
         		WHERE groupid='$rrow[groupid]'
         	");
         	$pcount = ceil($tcount['cnt']/$vboptions['vm_perpage']);
         	$rrow2 = $rrow;
         	for($i=0;$i<$pcount;$i++)
         	{
        	if($i) $rrow2['page'] = $i+1;
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_group($rrow2, false, $i ? VBSEO_URL_GROUPS_PAGE : VBSEO_URL_GROUPS),
   				vbseo_url_group($rrow2, true),
  		   		$vboptions['vbseo_sm_priority_g'],
   				$rrow['lastupdate'],
  				$vboptions['vbseo_sm_freq_g']
   			);
   			}

         	$getitems = $db->query("
         		SELECT gp.*,p.caption
         		FROM " . TABLE_PREFIX . "socialgrouppicture gp
         		LEFT JOIN " . TABLE_PREFIX . "picture p on p.pictureid=gp.pictureid
         		WHERE state = 'visible' AND groupid = '".$rrow['groupid'] ."'
         	");
         	if($getitems)
         	{
             	$par = 'do=grouppictures';
     			$added_urls += vbseo_add_2urls(
     				vbseo_url_group($rrow, false, VBSEO_URL_GROUPS_PIC, $par),
     				vbseo_url_group($rrow, true, '', $par),
    		   		$vboptions['vbseo_sm_priority_gi'],
     				$rrow['lastpost'],
    				$vboptions['vbseo_sm_freq_gi']
     			);
			}

     		while ($ritem = $db->fetch_array($getitems))
     		{
             	vbseo_log_entry("[group] group_id: $ritem[groupid]");
             	$ritem = array_merge($rrow, $ritem);

             	$par = 'do=picture';
     			$added_urls += vbseo_add_2urls(
     				vbseo_url_group($ritem, false, VBSEO_URL_GROUPS_PICTURE, $par),
     				vbseo_url_group($ritem, true, '', $par),
    		   		$vboptions['vbseo_sm_priority_gi'],
     				$ritem['dateline'],
    				$vboptions['vbseo_sm_freq_gi']
     			);
     			
     		}
     		$db->free_result($getitems);
   		}
   		$db->free_result($getrecords);

   		return $added_urls;
   	}

   	function vbseo_sitemap_tags()
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress(13)) return;
       	vbseo_log_entry("[SECTION START] tags", true);

      	if(!vbseo_dbtbl_exists('tag') || !function_exists('vbseo_tags_url'))
      		return 0;

   		$added_urls = 0;

   		$added_urls += vbseo_add_2urls(
   			vbseo_url_tag(array(), false, VBSEO_URL_TAGS_HOME),
   			vbseo_url_tag(array(), true),
  	   		$vboptions['vbseo_sm_priority_tag'],
   			0,
  			$vboptions['vbseo_sm_freq_tag']
   		);

       	$getrecords = $db->query("
       		SELECT tagid, tagtext as tag
       		FROM " . TABLE_PREFIX . "tag
       	");
       	$ouserid = 0;
   		while ($rrow = $db->fetch_array($getrecords))
   		{
           	vbseo_log_entry("[tag] tag_id: $rrow[tagid]");
         	$tcount = $db->query_first("
         		SELECT count(*)as cnt,max(dateline) as lastupdate
         		FROM " . TABLE_PREFIX . "tagthread
         		WHERE tagid='$rrow[tagid]'
         	");
         	$pcount = ceil($tcount['cnt']/$vboptions['maxthreads']);
         	for($i=0;$i<$pcount;$i++)
         	{
        	if($i) $rrow['page'] = $i+1;
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_tag($rrow, false, $i ? VBSEO_URL_TAGS_ENTRYPAGE : VBSEO_URL_TAGS_ENTRY),
   				vbseo_url_tag($rrow, true),
  		   		$vboptions['vbseo_sm_priority_tag'],
   				$rrow['lastupdate'],
  				$vboptions['vbseo_sm_freq_tag']
   			);
   			}

   		}
   		$db->free_result($getrecords);

   		return $added_urls;
   	}

   	function vbseo_sitemap_homepage()
   	{
   		global $vbseo_vars;
   		if(vbseo_check_progress(1)) return;

       	vbseo_log_entry("[homepage]", true);
        vbseo_add_url($vbseo_vars['bburl'].'/', 1.0);
   	}

   	function vbseo_sitemap_archive_homepage()
   	{
   		global $vbseo_vars;

   		if(vbseo_check_progress(5)) return;

       	vbseo_log_entry("[archive homepage]", true);

		vbseo_add_2urls(
        	$vbseo_vars['bburl'].((VBSEO_ON&&VBSEO_REWRITE_FORUM) ? VBSEO_ARCHIVE_ROOT : '/archive/index.'.VBSEO_PHP_EXT), 
        	$vbseo_vars['bburl']. '/archive/index.'.VBSEO_PHP_EXT,
        	1.0);

   	}

// ================================================================================
// ================================================================================
// ================================================================================
   	function vbseo_add_2urls($url, $url2, $priority = 1.0, $lastmod = 0, $freq = '')
   	{
   		global $vboptions;
   		
   		$added_urls = 1;
   		vbseo_add_url($url, $priority, $lastmod, $freq);
		if($vboptions['vbseo_sm_oldurls'] && VBSEO_ON)
		{
   			vbseo_add_url($url2, $priority, $lastmod, $freq);
   			$added_urls++;
   		}
   		return $added_urls;
   	}

   	function vbseo_add_url($url, $priority = 1.0, $lastmod = 0, $freq = '')
   	{
   		global $vbseo_vars, $vboptions, $vbseo_stat;

   		if(!$freq)
   			$freq = 'daily';

   		if(!$lastmod)
   			$lastmod = time();

   		if($lastmod<$vbseo_vars['tpl_update'])
   			$lastmod = $vbseo_vars['tpl_update'];

   		if(!$priority)
   			$priority = $vboptions['vbseo_sm_priority'];

		$lastmod = gmdate('Y-m-d\TH:i:s+00:00', $lastmod);

   		$vbseo_vars['sitemap_content'][] = array(
	  		'url'=> $url,
  			'priority'=> $priority,
    	    'lastmod' => $lastmod,
	        'freq' => $freq
        );
   	
		$vbseo_stat['urls_no']++;
		$vbseo_stat['urls_no_tot']++;

        if( ($vbseo_stat['urls_no'] == ($vboptions['vbseo_sm_maxurls']?$vboptions['vbseo_sm_maxurls']:50000)) )
			vbseo_flush_sitemap(true);
			else
        if( ($vbseo_stat['urls_no'] % 1000) == 0)
			vbseo_flush_sitemap(false);
   	}


   	function vbseo_flush_sitemap($split = true, $last = false)
   	{
   		global $vbseo_vars, $vboptions, $vbseo_stat;

   		if(!$vbseo_vars['sitemap_content'])return;

   		$sm_filename = vbseo_ext_gz('sitemap_'.(count($vbseo_vars['sitemap_files'])+1).'.xml');
   		$xs = 'xml_started_'.count($vbseo_vars['sitemap_files']);

   		if(!$vbseo_vars[$xs])
   		{
   			$vbseo_vars['pfname'] = VBSEO_DAT_FOLDER . $sm_filename;
	   		$vbseo_vars['pf'] = fopen($vbseo_vars['pfname'], 'w');
	   	}

   		if($vboptions['vbseo_sm_txt'])
   		{
   			$vbseo_vars['pf2name'] = vbseo_ext_gz(VBSEO_DAT_FOLDER . VBSEO_YAHOO_SM);
   			$vbseo_vars['pf2'] = fopen($vbseo_vars['pf2name'], $vbseo_vars['txt_started'] ? 'a' : 'w');
   		}

   		if(!$vbseo_vars[$xs])
   		fwrite($vbseo_vars['pf'], 
'<?xml version="1.0" encoding="UTF-8"?>
<urlset
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="
            http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/09/sitemap.xsd">');//<?php

		foreach($vbseo_vars['sitemap_content'] as $sc)
		{
			$xcont = "
<url>
  <loc>$sc[url]</loc>
  <priority>$sc[priority]</priority>
  <lastmod>$sc[lastmod]</lastmod>
  <changefreq>$sc[freq]</changefreq>
</url>";
			fwrite($vbseo_vars['pf'], $xcont);
	   	
	   		if($vboptions['vbseo_sm_txt'])
				fwrite($vbseo_vars['pf2'], str_replace('&amp;', '&', $sc['url'])."\n");

	   		$vbseo_vars[$xs] += strlen($xcont);
		}



   		$vbseo_vars['sitemap_content'] = array();

   		if(!$vbseo_vars['txt_started'] || $split)
   			$vbseo_vars['txt_started']++;


   		if(!$split)
   			return;

		fwrite($vbseo_vars['pf'], "\n</urlset>");
		fclose($vbseo_vars['pf']);
		vbseo_gz_compress($vbseo_vars['pfname']);
		@chmod(VBSEO_DAT_FOLDER . $sm_filename, 0666);

		if($vbseo_vars['pf2'])
		{
			fclose($vbseo_vars['pf2']);
			@chmod($vbseo_vars['pf2name'], 0666);
		}

   		vbseo_log_entry("[create sitemap file] filename: $sm_filename, number of urls: $vbseo_stat[urls_no]", true);

   		if($vboptions['vbseo_sm_txt'])
   		{
	   		vbseo_log_entry("[create sitemap in text format] part #".$vbseo_vars['txt_started'], true);
		}


   		$vbseo_vars['sitemap_files'][] = array(
   			'url'=>vbseo_sitemap_furl($sm_filename),
   			'size'=>filesize($vbseo_vars['pfname']),
   			'uncompsize'=>$vbseo_vars[$xs],
   			'urls'=>$vbseo_stat['urls_no'],
   			);

   		$vbseo_stat['urls_no'] = 0;
   		$vbseo_vars['sm_done']++;
		vbseo_save_progress();

		if($vbseo_vars['sm_done']==$vbseo_vars['split_generation'])
		{
	   		vbseo_log_entry("[split generation] STOP", true);
	   		exit;
		}
		

		if(!$last)
			sleep($vboptions['vbseo_sm_delay']);

   		return;
   	}

   	function vbseo_flush_index()
   	{
   		global $vbseo_vars, $vboptions;

   		vbseo_flush_sitemap(true, true);
   		if($vboptions['vbseo_sm_txt'])
   		{
	   		$vbseo_vars['txt']['size'] = @filesize($vbseo_vars['pf2name']);
		}

   		$sm_filename = vbseo_ext_gz('sitemap_index.xml');

   		$smaps = '';
    	foreach($vbseo_vars['sitemap_files'] as $smfile)
    		$smaps.="<sitemap>
	<loc>".$smfile['url']."</loc>
	<lastmod>".date('Y-m-d\TH:i:s+00:00')."</lastmod>
</sitemap>\n";

   		$smcontent = 
'<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="
            http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/09/siteindex.xsd">
'.$smaps.'
</sitemapindex>';//<?php

	    vbseo_write_file(VBSEO_DAT_FOLDER . $sm_filename, $smcontent);
		vbseo_gz_compress(VBSEO_DAT_FOLDER . $sm_filename);
   		vbseo_log_entry("[create sitemap index] filename: $sm_filename, number of sitemaps: ".count($vbseo_vars['sitemap_files']), true);

		vbseo_gz_compress($vbseo_vars['pf2name']);
   		return;
   	}

   	function vbseo_sitemap_ping_url($url)
   	{
   		global $vbseo_stat;
   		$purl = 'http://www.google.com/webmasters/tools/ping?sitemap='.urlencode($url);
   		$rping = vbseo_query_http($purl);
		$vbseo_stat['ping'] = strstr($rping, 'Received');

   		//$purl = 'http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap='.urlencode($url);
   		$purl = 'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid='.VBSEO_YAHOO_APPID.'&url='.urlencode($url);

		$rping = vbseo_query_http($purl);
		$vbseo_stat['pingyahoo'] = strstr($rping, 'successfully');

   		$purl = 'http://submissions.ask.com/ping?sitemap='.urlencode($url);
		$rping = vbseo_query_http($purl);
		$vbseo_stat['pingask'] = strstr($rping, 'successful');

   		$purl = 'http://api.moreover.com/ping?u='.urlencode($url);
		$rping = vbseo_query_http($purl);
		$vbseo_stat['pingmore'] = strstr($rping, 'Thank you');

   		$purl = 'http://webmaster.live.com/webmaster/ping.aspx?siteMap='.urlencode($url);
		$rping = vbseo_query_http($purl);
		$vbseo_stat['pinglive'] = strstr($rping, 'Thanks for');

		return ;
   	}

   	function vbseo_sitemap_furl($sitemap)
   	{
   		global $vbseo_vars, $vboptions;

				
   		return $vbseo_vars['topurl'] . '/' . ($vboptions['vbseo_sm_norwurl'] ? 'vbseo_sitemap_file.php?sitemap=':'') . $sitemap;
   	}

   	function vbseo_sitemap_ping()
   	{   
   		global $vbseo_vars;

   		$smindex = vbseo_sitemap_furl('sitemap_index.xml.gz');
   		return vbseo_sitemap_ping_url($smindex);
   	}

   	function vbseo_sitemap_stat($stat, $email)
   	{
   		global $vbseo_vars, $vboptions;

		$stat['txt'] = $vbseo_vars['txt'];

		$logfname = VBSEO_DAT_FOLDER . time() . '.log';
   		$pf = fopen($logfname, 'w');
   		fwrite($pf, serialize($stat));
   		fclose($pf);
   		@chmod($logfname, 0666);

   		if(!$email) return;


   		$mailbody = 
"Hello!

The vBSEO Google/Yahoo Sitemap has been successfully generated for your vBulletin forums at:
$vboptions[bbtitle] ($vbseo_vars[bburl])

Report:
============================
Click the following link for your vBSEO Google/Yahoo Sitemap Report:
$vbseo_vars[bburl]/vbseo_sitemap/

Summary:
============================
Forum Display: ".$stat['f']."
Show Thread: ".$stat['t']."
Show Post: ".$stat['p']."
Member Profiles: ".$stat['m']."
Poll Results: ".$stat['poll']."
Archive: ".($stat['af']+$stat['at'])."

Total Indexed URLs: ".$stat['urls_no_tot']."
Total Processing Time: ".number_format($stat['end']-$stat['start'],2)." seconds

Google ping: ".(isset($stat['ping'])?($stat['ping']?'Successful':'FAILED'):'Disabled').".
Yahoo ping: ".(isset($stat['pingyahoo'])?($stat['pingyahoo']?'Successful':'FAILED'):'Disabled').".
Ask ping: ".(isset($stat['pingask'])?($stat['pingask']?'Successful':'FAILED'):'Disabled').".
Moreover ping: ".(isset($stat['pingmore'])?($stat['pingmore']?'Successful':'FAILED'):'Disabled').".
Live.com ping: ".(isset($stat['pinglive'])?($stat['pinglive']?'Successful':'FAILED'):'Disabled').".

============================
vBSEO(TM) c 2005-2008 Crawlability, Inc.
http://www.crawlability.com/vbseo
http://www.vbseo.com


Note for vBSEO users: This version of the sitemap generator works with vBSEO 2.4.1 up.
Please download the most recent vBSEO here: http://www.vbseo.com/downloads/
";

if(VBSEO_ON)
	$mailbody .= "

Find out more out vBSEO - vBulletin Search Engine Optimization
============================

vBSEO is the definitive SEO enhancement for your vBulletin community forums!

vBSEO makes it easier for search engines to crawl more of your valuable vBulletin content faster and more often giving you higher keyword relevancy.

By installing vBSEO for your vBulletin forums you should expect to:

    * Get more of your forum pages indexed in the major search engines
    * Get your pages indexed faster
    * Improve your keyword relevancy for all pages
    * Prevent possible duplicate content penalties

The result of installing vBSEO you should expect is:

    * Higher visitor to member conversion rate (i.e. gain more new members faster)
    * Get visitors who are more highly targeted to the content you provide
    * Increase the monthly revenues earned from your forums
    * Improve your chances of achieving big-board.com status

vBulletin + vBSEO
============================
Serious forum admins choose vBSEO for increased search engine traffic!
http://www.vbseo.com/purchase/
";
		if(function_exists('vbmail_start'))
		{
	        vbmail_start();
    	    vbmail($email, 'vBSEO Google/Yahoo Sitemap Updated', $mailbody);
        	vbmail_end();
        }else
        {
			mail($email, 
			'vBSEO Google/Yahoo Sitemap Updated', 
			$mailbody,
			"From: ".$email);
		}
   	}


// ================================================================================
// ================================================================================
// ================================================================================

	function vbseo_load_progress()
	{
		global $vbseo_progress, $vbseo_stat, $vbseo_vars;

		$vbseo_progress = array();
		if(file_exists(VBSEO_DAT_PROGRESS))
		{
			$vbseo_progress = unserialize(implode('',file(VBSEO_DAT_PROGRESS)));
			$vbseo_stat = $vbseo_progress['stats'];
			$vbseo_vars = $vbseo_progress['vars'];
			$vbseo_vars['sm_done'] = 0;
	       	vbseo_log_entry("[RESUME GENERATION] step#" . $vbseo_progress['step']);
		}
    }

	function vbseo_save_progress()
	{
		global $vbseo_progress, $vbseo_stat, $vbseo_vars;

		$vbseo_progress['stats'] = $vbseo_stat;
		$vbseo_progress['vars'] = $vbseo_vars;
		$pf = fopen(VBSEO_DAT_PROGRESS,'w');
		fwrite($pf, serialize($vbseo_progress));
		fclose($pf);
   		@chmod(VBSEO_DAT_PROGRESS, 0666);
    }

	function vbseo_clean_progress()
	{
		global $vbseo_progress;
		$vbseo_progress = array();
		unlink(VBSEO_DAT_PROGRESS);
    }

	function vbseo_check_progress($step)
	{
		global $vbseo_progress;
   		if($vbseo_progress['step'] > $step)
   			return true;

   		if($vbseo_progress['step'] < $step)
   		{
   			$vbseo_progress['step2'] = $vbseo_progress['step3'] = 0;
   			$vbseo_progress['step'] = $step;
   		}
   		return false;
	}

	function vbseo_url_bburl($url)
	{
		global $vbseo_vars;
		return (strstr($url, '://') ? '' : $vbseo_vars['bburl']. ($url[0]=='/' ? '' : '/')) . $url;
	}

	function vbseo_url_forum($forum_id, $page = 1, $archived = false, $old = false)
	{
		global $vbseo_vars;
		
		$is_vbseo = (VBSEO_ON && VBSEO_REWRITE_FORUM && !$old);

		if($archived)
		{
			$url = ($is_vbseo ? VBSEO_ARCHIVE_ROOT : '/archive/index.'.VBSEO_PHP_EXT.VBSEO_SLASH_METHOD) .
				'f-'.$forum_id.($page>1?'-p-'.$page:'').'.html';
		}else
			$url = ($is_vbseo ? vbseo_forum_url($forum_id, $page) :
				'forumdisplay.'.VBSEO_PHP_EXT.'?f='.$forum_id . ($page>1?'&amp;page='.$page:''));

		return vbseo_url_bburl($url);
	}

	function vbseo_url_blog_entry($blogrow, $old = false)
	{
		global $vbseo_vars, $g_cache;
		
		$is_vbseo = (VBSEO_ON && VBSEO_REWRITE_BLOG && !$old);
		$g_cache['blog'][$blogrow['blogid']] = $blogrow;

		$url = 
			($is_vbseo ? 
				vbseo_blog_url(VBSEO_URL_BLOG_ENTRY, array('b'=>$blogrow['blogid'])) :
				'blog.'.VBSEO_PHP_EXT.'?b='.$blogrow['blogid'] );
		unset($g_cache['blog'][$blogrow['blogid']]);
		return vbseo_url_bburl($url);
	}

	function vbseo_url_thread($thread_row, $page = 1, $archived = false, $old = false)
	{
		global $vbseo_vars;

		$is_vbseo = (VBSEO_ON && VBSEO_REWRITE_THREADS && !$old);

		if($archived)
		{
			$url = ($is_vbseo ? VBSEO_ARCHIVE_ROOT : '/archive/index.'.VBSEO_PHP_EXT.''.VBSEO_SLASH_METHOD) .
				't-'.$thread_row['threadid'].($page>1?'-p-'.$page:'').'.html';
		}else
			$url = ($is_vbseo ? vbseo_thread_url_row($thread_row, $page) :
			'showthread.'.VBSEO_PHP_EXT.'?t='.$thread_row['threadid'].($page>1?'&amp;page='.$page:''));

		return vbseo_url_bburl($url);
	}

	function vbseo_url_post($thread_row, $post_row, $postcount = 1, $old = false)
	{
		global $vbseo_vars;

		if(VBSEO_ON && VBSEO_REWRITE_SHOWPOST && !$old)
		{
			if(strstr(VBSEO_URL_POST_SHOW, '%t')||strstr(VBSEO_URL_POST_SHOW, '%f'))
			$url = vbseo_post_url_row($thread_row, $post_row, $postcount);
			else
			$url = str_replace(
				array('%post_id%','%post_count%'),
				array($post_row['postid'],$postcount),
				VBSEO_URL_POST_SHOW
			);
		}else
			$url = 'showpost.'.VBSEO_PHP_EXT.'?p='.$post_row['postid'].'&amp;postcount='.$postcount;

		return vbseo_url_bburl($url);
	}

	function vbseo_url_member($userid, $username, $old = false)
	{
		global $vbseo_vars;

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_MEMBERS && !$old) ? vbseo_member_url_row($userid, $username) :
			 'member.'.VBSEO_PHP_EXT.'?u='.$userid)
			 ) ;
	}

	function vbseo_url_poll($threadrow, $getpoll, $old = false)
	{
		global $vbseo_vars;

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_POLLS && !$old) ? vbseo_poll_url_direct($threadrow, $getpoll) :
			 'poll.'.VBSEO_PHP_EXT.'?do=showresults&amp;pollid='.$getpoll['pollid']) 
			 );
	}

	function vbseo_url_group($arow, $old = false, $format = '', $par = '')
	{
		global $vbseo_vars;
		$urlpar = $par ? array($par) : array();
		if($arow['groupid'])
			$urlpar[] = 'groupid='.$arow['groupid'];
		if($arow['pictureid'])
			$urlpar[] = 'pictureid='.$arow['pictureid'];
		if($arow['page'])
			$urlpar[] = 'page='.$arow['page'];

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_GROUPS && !$old) ? 
				vbseo_group_url_row($format , $arow) :
			 	'group.'.VBSEO_PHP_EXT.''.($urlpar ? '?'.implode('&amp;', $urlpar):'')
			 	)
			 );
	}
	
	function vbseo_url_tag($arow, $old = false, $format = '', $par = '')
	{
		global $vbseo_vars;
		if(function_exists('unhtmlspecialchars'))
			$arow['tag'] = unhtmlspecialchars($arow['tag']);

		$arow['tag'] = urlencode($arow['tag']);
		$urlpar = $par ? array($par) : array();
		if($arow['tag'])
			$urlpar[] = 'tag='.$arow['tag'];
		if($arow['page'])
			$urlpar[] = 'page='.$arow['page'];

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_TAGS && !$old) ? 
				vbseo_tags_url($format , $arow) :
			 	'tags.'.VBSEO_PHP_EXT.''.($urlpar ? '?'.implode('&amp;', $urlpar):'')
			 	)
			 );
	}
	
	function vbseo_url_album($arow, $old = false, $format = '')
	{
		global $vbseo_vars;

		$urlpar = array();
		if($arow['userid'])
			$urlpar[] = 'u='.$arow['userid'];
		if($arow['albumid'])
			$urlpar[] = 'albumid='.$arow['albumid'];
		if($arow['pictureid'])
			$urlpar[] = 'pictureid='.$arow['pictureid'];
		if($arow['page'])
			$urlpar[] = 'page='.$arow['page'];

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_MEMBERS && !$old) ? 
				vbseo_album_url_row(
				$format ? $format :
				($arow['pictureid'] ? 'VBSEO_URL_MEMBER_PICTURE' : 'VBSEO_URL_MEMBER_ALBUM'), 
				$arow) :
			 	'album.'.VBSEO_PHP_EXT.''.($urlpar ? '?'.implode('&amp;', $urlpar):'')
			 	)
			 );
	}
	
// ================================================================================
// ================================================================================
// ================================================================================

	function vbseo_dbtbl_exists($tblname)
	{
		global $db;
		$db->hide_errors();
      	$supported = $db->query_first("SHOW TABLES LIKE '" . TABLE_PREFIX . $tblname . "'");
		$db->show_errors();
		return $supported ? 1 : 0;
	}

   	function vbseo_log_entry($message, $more_important = false)
   	{
   		global $vbseo_vars, $vbseo_stat;

   		if((THIS_SCRIPT!='cron') && ($vbseo_vars['log_detailed'] || $more_important) )
   		{
	        if (function_exists('memory_get_usage'))
    	    	$message.=' ['.number_format(memory_get_usage()/1024,1).'Kb mem used]';
			$tm = array_sum(explode(' ', microtime()))-$vbseo_stat['start'];
			$message .= ' ['.number_format($tm,0).'s (+'.number_format($tm-$vbseo_vars['last_tm'],0).'s)]';
			$vbseo_vars['last_tm'] = $tm;
	   		echo $message."<br/>\n";
	   		flush();
	   	}
   	}

   	function vbseo_write_file($filename, &$filecont, $append = false)
   	{
	    $pf = fopen($filename, $append?'a':'w');
	    fwrite($pf, $filecont);
	    fclose($pf);
	    @chmod($filename, 0666);
   	}

   	function vbseo_ext_gz($filename)
   	{
    	return VBSEO_SM_GZFUNC ? $filename.'.gz' : $filename;
   	}

   	function vbseo_gz_compress($filename)
   	{
   		if(VBSEO_SM_GZFUNC && function_exists('gzopen') && file_exists($filename))
   		{
   			$pf = fopen($filename, 'r');
   			$fcont = fread($pf, filesize($filename));
   			fclose($pf);

   			$gf = gzopen($filename, 'w');
   			gzwrite($gf, $fcont);
   			gzclose($gf);
   		}
   	}

   	function vbseo_file_gz($filename)
   	{
   		if(file_exists(VBSEO_DAT_FOLDER.$filename.'.gz'))
   			return $filename.'.gz';
   			else
   			return $filename;
   	}

   	function vbseo_sm_prune($dir)
   	{
   		if(defined('VBSEO_SM_PRUNE') && VBSEO_SM_PRUNE)
   		{
   			$prune_limit = time() - VBSEO_SM_PRUNE * 24 * 60 * 60;
        	$pd = @opendir($dir);
        	while($fn = @readdir($pd))
        	if(strstr($fn, '.log') && (filemtime($dir.$fn)< $prune_limit))
        		unlink($dir.$fn);
        	@closedir($pd);
   			
   		}
   	}

	function vbseo_query_http($url)
	{

		$s = @implode('', file($url));
		if(!$s)
			$s = vbseo_query_http_socket($url);
		return $s;
	}

	function vbseo_query_http_socket($url)
	{
   	    ini_set('default_socket_timeout', 5);
   	    $purl = parse_url($url);
        $connsocket = @fsockopen($purl['host'], 80, $errno, $errstr, 5);
   		$start = 0;
   		$timeout = 50;
   		while($start < $timeout)
   		{
			$start++;
			if ($connsocket)
			{
             $out = "GET ".$purl['path']."?".$purl['query']." HTTP/1.1\n";
             $out .= "Host: ".$purl['host']."\n";
   		     $out .= "Referer: http://".$purl['host']."/\n";
             $out .= "Connection: Close\n\n";
     		 $inp = '';
             @fwrite($connsocket, $out);
             while (!feof($connsocket)) {
                $inp .= @fread($connsocket, 4096);
             }
             @fclose($connsocket);
			 break;
            }

		}
        preg_match("#^(.*?)\r?\n\r?\n(.*)$#s",$inp,$hm);
        return $hm[2];
	}
?>