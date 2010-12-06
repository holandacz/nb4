<?php
header("HTTP/1.0 404 Not Found");
header("Status: 404 Not Found");
$_GET['do']='page';
$_REQUEST['do']='page';
$_GET['template']='404';
$_REQUEST['template']='404';
define('VBSEO_PREPROCESSED', 1);
chdir('/home/nb/web/');
include '/home/nb/web/misc.php';
?>
