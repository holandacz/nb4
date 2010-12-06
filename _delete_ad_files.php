<?php

define('ADS_LAYOUTS', 'ads/layouts/');

if ($dir = @opendir(ADS_LAYOUTS)) {
    while ($file = readdir($dir)) {
        if (!is_dir($file)) {
            unlink(ADS_LAYOUTS . $file);
        }
    }
    closedir($dir);
}