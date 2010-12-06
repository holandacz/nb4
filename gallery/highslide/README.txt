/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

============================================================================================
Highslide and PhotoPlog Integration Instructions
============================================================================================

Versions used - Highslide JS 3.1.11 & PhotoPlog Pro v.2.1.4.6
Highslide website: http://vikjavev.no/highslide/
Highslide license: Creative Commons Attribution-NonCommercial 2.5 License
                   http://creativecommons.org/licenses/by-nc/2.5/

============================================================================================
License Information
============================================================================================

PhotoPlog owns a Highslide JS distribution licence. This distribution licence is valid for
all future versions of Highslide. Further, this distribution licence is good even if someone
from a commercial site purchases PhotoPlog, as they will then get Highslide too as part of
the PhotoPlog package. By purchasing PhotoPlog, you have the Highslide author's permission
to use Highslide JS in conjunction with PhotoPlog. This means that you do not need to purchase
a separate Highslide license to use Highslide with PhotoPlog. However, donations to the
Highslide author are encouraged. Note: The Highslide license remains separate from the
PhotoPlog license. Further, the SWFObject license remains separate from the PhotoPlog license.
Highslide JS is under a Creative Commons Non-Commercial licence, and SWFObject is under an
MIT license, even though they are bundled with the PhotoPlog package. However, PhotoPlog is
NOT under a Creative Commons Non-Commercial licence, and PhotoPlog is NOT under an MIT license.
PhotoPlog has its own license. That is, only the files *inside* the highslide directory of
the PhotoPlog package fall under a different license. You may use the files *inside* the
highslide directory of the PhotoPlog package according to the terms listed in those files
themselves. The MIT license comes without distribution restriction. For Highslide distribution
license verification, you may contact the Highslide author at http://vikjavev.no/highslide/

============================================================================================
In the highslide.css file find in four spots:
============================================================================================

	url(/YOUR-PHOTOPLOG-DIRECTORY/highslide/

============================================================================================
And in each spot make the following replacement:
============================================================================================

	Replace YOUR-PHOTOPLOG-DIRECTORY with the actual directory name. If you run
	sub-domains, you may instead use the following:

	url(http://www.YOUR-DOMAIN-NAME.com/YOUR-PHOTOPLOG-DIRECTORY/highslide/

============================================================================================
Edit the PhotoPlog Settings:
============================================================================================

Look for the Highslde Settings via the ACP and set as desired. Also, note that conflicts
between Highslide and vBulletin might occur, as both use a fair amount of JavaScript. There
is no guarantee that potential conflicts have been avoided, as both Highslide and vBulletin
could affect the same JavaScript, so if you run into an issue with a Highslide and vBulletin
conflict, turn off Highslide. Note: There is no official PhotoPlog support for Highslide.
For Highslide support visit http://vikjavev.no/highslide/forum/

============================================================================================
Enjoy!
============================================================================================