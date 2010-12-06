/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

============================================================================================
PhotoPlog Pro 'vBadvanced Module' Installation
============================================================================================

1) FTP photoplog_thumbs.php in ASCII mode into your /forum/modules directory
   (delete/overwrite the old photoplog_thumbs.php file if present)

2) Create a new vB template (ACP -> Style Manager -> Add New Template)
   (delete/edit the old adv_portal_photoplog_thumbs template if present)

	Title: adv_portal_photoplog_thumbs
	Template:

	<tr>
		<td class="alt1" align="center">
			$photoplog[thumbnails]
		</td>
	</tr>

    2a) Note that you can make another template and use $photoplog[minithumbnails]

3) Create a new vBa PHP File module (ACP -> vBa CMPS -> Add Module)
   (delete/edit the old module if present)

	Title: PhotoPlog Thumbs
	File: photoplog_thumbs.php
	Template: adv_portal_photoplog_thumbs

    3a) If you make another template, place that template name in the template box
        and open photoplog_thumbs.php and set your template name, i.e.:

        eval('$home[$mods[\'modid\']][\'content\'] = "' . fetch_template('PLACE_YOUR_TEMPLATE_NAME_HERE') . '";');

4) Enable the module (ACP -> vBa CMPS -> Edit Pages)

============================================================================================
Enjoy!
============================================================================================