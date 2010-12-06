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

error_reporting(E_ALL & ~E_NOTICE);
ignore_user_abort(1);
@set_time_limit(0);
require dirname(__FILE__).'/vbseo_sitemap_config.php';

if(!defined('DIR'))
	define('DIR', dirname(__FILE__).'/../');

if (!is_object($vbulletin->db)&&!is_object($db)&&!is_object($DB_site))
{
    define('SKIP_SESSIONCREATE', 1);
    define('NOCOOKIES', 1);
    define('THIS_SCRIPT', 'login');

	chdir(dirname(__FILE__).'/../');
	$globaltemplates = $phrasegroups = $specialtemplates = array();
	include getcwd().'/global.'.VBSEO_PHP_EXT;

	require_once(dirname(__FILE__). '/vbseo_sitemap_functions.php');

	require_once( DIR . '/includes/functions_cron.'.VBSEO_PHP_EXT);

	if(isset($_SERVER['REQUEST_METHOD']))
	if($_COOKIE['runcode'] != md5($vboptions['vbseo_sm_runcode']))
	{
		echo 'Cannot run sitemap generator directly: you should be logged in to do this.';
		exit();
	}
}

require_once(dirname(__FILE__) . '/vbseo_sitemap_functions.php');
require_once(DIR . '/includes/functions_forumlist.'.VBSEO_PHP_EXT);

log_cron_action('Google Sitemap [Started]', $nextitem);
$vbseo_stat = array();

$vbseo_stat['start'] = array_sum(explode(' ', microtime()));

cache_ordered_forums(1);
fetch_last_post_array();
vbseo_get_last_tpl_update();

$vbseo_vars['forumslist'] = vbseo_get_forumlist();
if($vboptions['vbseo_sm_exclude_forums'])
	$vbseo_vars['forumslist'] = 
		array_values(array_diff(
			$vbseo_vars['forumslist'], 
			explode(' ', $vboptions['vbseo_sm_exclude_forums'])
			));


vbseo_load_progress();

vbseo_sitemap_homepage();

vbseo_sitemap_extra();
if($vboptions['vbseo_sm_forumdisplay'])
{
	$vbseo_stat['f'] += vbseo_sitemap_forumdisplay();
}

if($vboptions['vbseo_sm_showthread'])
{
	vbseo_sitemap_showthread(false, $vboptions['vbseo_sm_showpost']);
}

if($vboptions['vbseo_sm_archive'])
{
	vbseo_sitemap_archive_homepage();
	$vbseo_stat['af'] += vbseo_sitemap_forumdisplay(true) + 1;

	if(!VBSEO_ON || !VBSEO_REWRITE_ARCHIVE_URLS)
		$vbseo_stat['at'] += vbseo_sitemap_showthread(true);
}

if($vboptions['vbseo_sm_member'])
{
	$vbseo_stat['m'] += vbseo_sitemap_member();
}

if($vboptions['vbseo_sm_poll'])
{
	$vbseo_stat['poll'] += vbseo_sitemap_polls();
}

if($vboptions['vbseo_sm_blog'])
{
	$vbseo_stat['blog'] += vbseo_sitemap_blogs();
}

if($vboptions['vbseo_sm_album'])
{
	$vbseo_stat['a'] += vbseo_sitemap_albums();
}

if($vboptions['vbseo_sm_group'])
{
	$vbseo_stat['g'] += vbseo_sitemap_groups();
}

if($vboptions['vbseo_sm_tag'])
{
	$vbseo_stat['tag'] += vbseo_sitemap_tags();
}

if($vboptions['vbseo_sm_rss'])
{
	vbseo_sitemap_rss();
}

$addons = preg_split('#[\r\n]+#', $vboptions['vbseo_sm_addons']);

foreach($addons as $addon)
if($addon && file_exists(VBSEO_DAT_FOLDER_ADDON . $addon))
{
   	vbseo_log_entry("[addon module] $addon", true);
	include (VBSEO_DAT_FOLDER_ADDON . $addon);
}

vbseo_flush_index();

vbseo_clean_progress();

if($vboptions['vbseo_sm_ping'])
{
	vbseo_sitemap_ping();
}

vbseo_log_entry('Sitemap has been created. <a href="index.php">Click here to return</a>', true);

$vbseo_stat['end'] = array_sum(explode(' ', microtime()));
$vbseo_stat['files'] = $vbseo_vars['sitemap_files'];

vbseo_sitemap_stat($vbseo_stat, $vboptions['vbseo_sm_email']);
vbseo_sm_prune(VBSEO_DAT_FOLDER);
vbseo_sm_prune(VBSEO_DAT_FOLDER_BOT);

log_cron_action('Google Sitemap Created', $nextitem);

?>