<?php 

 /******************************************************************************************
 * vBSEO Google/Yahoo Sitemap Generator for vBulletin v3.x.x by Crawlability, Inc.         *
 *-----------------------------------------------------------------------------------------*
 *                                                                                         *
 * Copyright � 2005-2008, Crawlability, Inc. All rights reserved.                          *
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

	error_reporting(E_ALL&~E_NOTICE);
   	require dirname(__FILE__).'/vbseo_sitemap_config.php';

	$smap = $_GET['sitemap'];
	$smap = preg_replace('#^.*/([^/]*)$#', '$1', $smap);
	$smap_parts = explode('.', $smap);
	$ext = $smap_parts[count($smap_parts)-1];
	$preext = $smap_parts[count($smap_parts)-2];
	ob_end_clean();


	if(file_exists(VBSEO_DAT_FOLDER . $smap))
	{
    	preg_match('#(googlebot|yahoo)#i', $_SERVER['HTTP_USER_AGENT'], $umatch);
       	$dl_log = VBSEO_DAT_FOLDER.'downloads.dat';
       	$dl_list = file_exists($dl_log) ? unserialize(implode('', file($dl_log))) : array();

       	$dl_list[] = array(
       		'time'=>time(),
       		'useragent'=>$_SERVER['HTTP_USER_AGENT'],
       		'ip'=>$_SERVER['REMOTE_ADDR'],
       		'ua'=>$umatch[1],
       		'sitemap'=>$smap
       	);
       	$pf = @fopen($dl_log, 'w');
       	@fwrite($pf, serialize($dl_list));
       	@fclose($pf);

    	$content_types = array(
    		'txt' => 'plain/text',
    		'gz' => 'application/x-gzip',
    		'xml' => 'text/xml'
    	);


	
		@ini_set("zlib.output_compression", 0);
    	@header('Content-Length: '.filesize(VBSEO_DAT_FOLDER . $smap));
    	
    	if($ext=='gz' && ($umatch[1] || !strstr($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip')) )
    	{
	    	@header( 'Content-Type: ' . $content_types[$ext] );
    	}else
    	{
    		@header('Content-Type: '.($content_types[$preext] ? $content_types[$preext] : $content_types[$ext]) );
	    	if($ext=='gz')
    			@header('Content-Encoding: gzip' );
    	}

		set_magic_quotes_runtime(0);
    	$pf = @fopen( VBSEO_DAT_FOLDER . $smap, 'rb' );
    	if($pf)
		while (!@feof($pf)) {
		   $buffer = @fread($pf, 1000000);
		   echo $buffer;
		}
  		@fclose($pf); 
	   	exit;

	}else
	{
   	    @Header ("HTTP/1.1 404 Not Found");
		@header("Status: 404 Not Found");
    	echo 'Sitemap file not found';
	}
?>