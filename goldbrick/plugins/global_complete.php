<?php
/**
 * Adds Goldbrick header to headinclude for css and javascript
 * 
 * @hook        	false
 * @version     	$Revision:114 $
 * @modifiedby  	$LastChangedBy:digitallyepic_nix $
 * $lastmodified	$Date:2007-11-05 17:04:19 -0800 (Mon, 05 Nov 2007) $
 */
$hashes = $GLOBALS['goldbrick_engine']->get_expired_hashes(); 

$output = str_replace(
	'</body>', 
	'<img src="goldbrick/images/cleanup.php?hashes=' . $hashes . '" alt="" /></body>',
	$output
);

?>