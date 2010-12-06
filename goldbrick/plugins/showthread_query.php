<?php
/**
 * Initiates the delivery of media.
 * 
 * @active      	true
 * @version     	$Revision: 87 $
 * @modifiedby  	$LastChangedBy: digitallyepic_siradrian $
 * @lastmodified	$Date: 2007-10-21 22:21:30 -0700 (Sun, 21 Oct 2007) $
 */
require_once(DIR . '/goldbrick/includes/functions_public.php');

goldbrick_start_delivery(
	preg_split('/,/', $ids, -1, PREG_SPLIT_NO_EMPTY)
);

?>