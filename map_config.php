<?php
$host	= $_SERVER['HTTP_HOST'];
//echo $host;
if ($host == 'nobloodtest.org'){
	$gmap_key	= 'ABQIAAAAkwgSr5TUiSaSdMoV_dNXdRRtH9cdMHXjx-T5vydXQzNbQiRWKhQnXTBKWJabQe4l4Ot2gyTV7SkPVg';
	$cfg		= $ini['default'] + $ini['dev : default'];
}else{
	$gmap_key	= 'ABQIAAAAkwgSr5TUiSaSdMoV_dNXdRQxJRXFyDzIzWYBJS6CKOVveqrNSBRWyavctZfqKvsweGSwKJ5u3ZtWQQ';
	$cfg		= $ini['default'];
}
