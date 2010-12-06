/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

============================================================================================
 !!! NOTE !!! !!! NOTE !!! !!! NOTE !!! !!! NOTE !!! !!! NOTE !!! !!! NOTE !!! !!! NOTE !!! 
============================================================================================

As of PhotoPlog Pro v.2.1.4.6, Highslide JS and PhotoPlog Pro have been integrated.
See the README.txt file in the highslide directory that came with the PhotoPlog Pro
package for further details. As such, the integration of Lightbox and PhotoPlog Pro
is now deprecated. You are hereby advised to use Highslide JS with PhotoPlog Pro.

============================================================================================
Lightbox and PhotoPlog Integration Instructions
============================================================================================

Versions used - Lightbox JS v.2.02 & PhotoPlog Pro v.2.1.1
Lightbox website: http://www.huddletogether.com/projects/lightbox2/
Lightbox license: Creative Commons Attribution 2.5 License
                  http://creativecommons.org/licenses/by/2.5/

============================================================================================
Download Lightbox:
============================================================================================

Download Lightbox, keep its directory structure as-is, and make these changes, 
where you replace YOUR-DOMAIN-NAME and YOUR-LIGHTBOX-DIRECTORY with your info

============================================================================================
In lightbox.css find in three spots:
============================================================================================

	url(../images/

============================================================================================
And in each spot replace with the following:
============================================================================================

	url(http://www.YOUR-DOMAIN-NAME.com/YOUR-LIGHTBOX-DIRECTORY/images/

============================================================================================
In lightbox.js find the following:
============================================================================================

var fileLoadingImage = "images/loading.gif";
var fileBottomNavCloseImage = "images/closelabel.gif";

============================================================================================
And replace with the following:
============================================================================================

var fileLoadingImage = "http://www.YOUR-DOMAIN-NAME.com/YOUR-LIGHTBOX-DIRECTORY/images/loading.gif";
var fileBottomNavCloseImage = "http://www.YOUR-DOMAIN-NAME.com/YOUR-LIGHTBOX-DIRECTORY/images/closelabel.gif";

============================================================================================
FTP Lightbox to server:
============================================================================================

FTP Lightbox into YOUR-LIGHTBOX-DIRECTORY, keeping Lightbox's directory structure as-is

============================================================================================
In the vB shell_blank template find:
============================================================================================

<head>
$headinclude
<title>$pagetitle</title>
</head>

============================================================================================
And replace with the following:
============================================================================================

<head>
$headinclude
<if condition="$photoplog_dolightbox">
	<script type="text/javascript" src="http://www.YOUR-DOMAIN-NAME.com/YOUR-LIGHTBOX-DIRECTORY/js/prototype.js"></script>
	<script type="text/javascript" src="http://www.YOUR-DOMAIN-NAME.com/YOUR-LIGHTBOX-DIRECTORY/js/scriptaculous.js?load=effects"></script>
	<script type="text/javascript" src="http://www.YOUR-DOMAIN-NAME.com/YOUR-LIGHTBOX-DIRECTORY/js/lightbox.js"></script>
	<link rel="stylesheet" href="http://www.YOUR-DOMAIN-NAME.com/YOUR-LIGHTBOX-DIRECTORY/css/lightbox.css" type="text/css" media="screen" />
</if>
<title>$pagetitle</title>
</head>

============================================================================================
Edit the PhotoPlog Settings:
============================================================================================

Look for 'Lightbox Original File' and 'Lightbox Filmstrip File' and set as desired.
Also, note that conflicts between Lightbox and vBulletin can occur, namely the
switch editor mode and inline comment moderation. PhotoPlog is coded to work around
such conflicts, but there is no guarantee that all conflicts have been avoided, as
both Lightbox and vBulletin can affect the same JavaScript, so if you run into an
issue with a Lightbox and vBulletin conflict, turn off Lightbox.

============================================================================================
Enjoy!
============================================================================================