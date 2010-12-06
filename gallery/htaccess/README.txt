/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

============================================================================================
Some forum software, by default, does not render images from links with query strings in
[IMG] tags. What happens is that, instead of displaying the image, a text link is shown.
If this affects you, set the PhotoPlog 'Use .htaccess Link' setting to yes via the vB ACP
and place the following in a .htaccess file in your web root. Note mod_rewrite is required.
============================================================================================

# start .htaccess file - change /YOUR-PHOTOPLOG_DIR/ to your photoplog directory
RewriteEngine on
RewriteBase /
RewriteRule /YOUR-PHOTOPLOG_DIR/file_([0-9]+)\.jpg$ /YOUR-PHOTOPLOG_DIR/file.php?n=$1 [L]
# end .htaccess file

============================================================================================
Enjoy!
============================================================================================