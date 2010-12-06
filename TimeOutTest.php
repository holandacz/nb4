<?php

$set_time_limit = in_array('set_time_limit', explode(',', str_replace(' ', '', ini_get('disable_functions'))));

echo 'safe_mode: ' . (int)ini_get('safe_mode') . "\n";
echo 'set_time_limit: ' . (int)$set_time_limit . "\n";

?>