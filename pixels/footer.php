<?php
/******************************************************************************************
*   Million Pixel Script (R)
*   (C) 2005-2006 by texmedia.de, all rights reserved.
*   "Million Pixel Script" and "Pixel Script" is a registered Trademark of texmedia.
*
*   This script code is protected by international Copyright Law.
*   Any violations of copyright will be dealt with seriously,
*   and offenders will be prosecuted to the fullest extent of the law.
*
*   This program is not for free, you have to buy a copy-license for your domain.
*   This copyright notice and the header above have to remain intact.
*   You do not have the permission to sell the code or parts of this code or chanced
*   parts of this code for this program.
*   This program is distributed "as is" and without warranty of any
*   kind, either express or implied.
*
*   Please check
*   http://www.texmedia.de
*   for Bugfixes, Updates and Support.
******************************************************************************************/
$VERSIONS[basename(__FILE__)] = "3.0 PRO";

$TMP['%[MENU_TOP]%']          = '<a href="'.$trackpage.'#t" class=f>'.$_SP[71].'</a>';
$TMP['%[MENU_LEGAL_NOTICE]%'] = ($CONFIG['menu_legaln'])   ? '&nbsp;|&nbsp; <a href="legal_notice.php'.$trackpage.'" class=f>'.$_SP[66].'</a>' : '';
$TMP['%[MENU_FEEDBACK]%']     = ($CONFIG['menu_feedback']) ? '&nbsp;|&nbsp; <a href="feedb.php'.$trackpage.'" class=f>'.$_SP[67].'</a>' : '';
$TMP['%[MENU_LINK_US]%']      = ($CONFIG['menu_linkus'])   ? '&nbsp;|&nbsp; <a href="linkus.php'.$trackpage.'" class=f>'.$_SP[68].'</a>' : '';
$TMP['%[MENU_FAVORIT]%']      = '<a href="javascript:void(addBookmark(\''.$DOMAIN."','".htmlspecialchars(addslashes($CONFIG['domainname'])).'\'));" class=f>'.$_SP[76].'</a>';
$TMP['%[MENU_RANKING]%']      = $CONFIG['menu_top']       ? '&nbsp;|&nbsp; <a href="ranking.php'.$trackpage.'" class=f>'.sprintf($_SP[36],(int)$CONFIG['ranking_value']).'</a>' : '';
$TMP['%[MENU_PIXELLIST]%']    = $CONFIG['menu_pixellist'] ? '&nbsp;|&nbsp; <a href="pixellist.php'.$trackpage.'" class=f>'.$_SP[37].'</a>' : '';
$TMP['%[MENU_REFERRER]%']     = $CONFIG['menu_referrer']  ? '&nbsp;|&nbsp; <a href="referrer.php'.$trackpage.'" class=f>'.$_SP[61].'</a>' : '';
$TMP['%[MENU_TRAFFIC]%']      = $CONFIG['menu_traffic']   ? '&nbsp;|&nbsp; <a href="traffic.php'.$trackpage.'" class=f>'.$_SP[62].'</a>' : '';
$TMP['%[MENU_BLOG]%']         = $CONFIG['menu_blog']      ? '&nbsp;|&nbsp; <a href="blog.php'.$trackpage.'" class=f>'.$_SP[63].'</a>' : '';
$TMP['%[MENU_FAQ]%']          = $CONFIG['menu_faq']       ? '&nbsp;|&nbsp; <a href="faq.php'.$trackpage.'" class=f>'.$_SP[64].'</a>' : '';
$TMP['%[MENU_RECOMMEND]%']    = $CONFIG['menu_recommend'] ? '&nbsp;|&nbsp; <a href="recommend.php'.$trackpage.'" class=f>'.$_SP[65].'</a>' : '';
$TMP['%[MENU_NEWSLETTER]%']   = $TMP['%[MENU_MAILING]%'] = $CONFIG['menu_newsletter']? '&nbsp;|&nbsp; <a href="mailing.php'.$trackpage.'" class=f>'.$_SP[106].'</a>' : '';
$TMP['%[MENU_LOGIN]%']        = $login_logged_in ? '&nbsp;|&nbsp; <a href="login.php?logoff=1'.$trackpage_.'" class=f>'.$_SP[119].'</a>' : '&nbsp;|&nbsp; <a href="login.php'.$trackpage.'" class=f>'.$_SP[107].'</a>';

// Links
$TMP['%[LINK_FAVORIT]%']      = 'javascript:void(addBookmark(\''.$DOMAIN."','".htmlspecialchars(addslashes($CONFIG['domainname'])).'\'))';
$TMP['%[LINK_HOME]%']         = $TMP['%[LINK_INDEX]%'] = 'index.php'.$trackpage;
$TMP['%[LINK_GETPIXEL]%']     = 'getpixel.php'.$trackpage;
$TMP['%[LINK_FEEDBACK]%']     = 'feedb.php'.$trackpage;
$TMP['%[LINK_RANKING]%']      = 'ranking.php'.$trackpage;
$TMP['%[LINK_PIXELLIST]%']    = 'pixellist.php'.$trackpage;
$TMP['%[LINK_REFERRER]%']     = 'referrer.php'.$trackpage;
$TMP['%[LINK_TRAFFIC]%']      = 'traffic.php'.$trackpage;
$TMP['%[LINK_BLOG]%']         = 'blog.php'.$trackpage;
$TMP['%[LINK_FAQ]%']          = 'faq.php'.$trackpage;
$TMP['%[LINK_RECOMMEND]%']    = 'recommend.php'.$trackpage;
$TMP['%[LINK_GOTOP]%']        = $trackpage.'#t';
$TMP['%[LINK_LEGAL_NOTICE]%'] = 'legal_notice.php'.$trackpage;
$TMP['%[LINK_LINK_US]%']      = 'linkus.php'.$trackpage;
$TMP['%[LINK_NEWSLETTER]%']   = $TMP['%[LINK_MAILING]%'] = 'mailing.php'.$trackpage;
$TMP['%[LINK_LOGIN]%']        = $login_logged_in ? 'login.php?logoff=1'.$trackpage_ : 'login.php'.$trackpage;

print template($LANGDIR.'footer'.$plugin.'.htm',$TMP);

if($include_tooltip)
	print '<script language="JavaScript" type="text/javascript" src="tooltip.js"></script>';

if($scrollpage)
	print $scrollpage;

if($include_popup)
	print '<script type="text/javascript" src="popup.js"></script>';

if($_GET['checkversions']=='tex')
	print_r($VERSIONS);
?>

<script language="JavaScript" type="text/javascript">
var message = ""; function clickIE() {  if(document.all) {  (message);  return false;  } }
function clickNS(e) {  if(document.layers || (document.getElementById && !document.all)) {
if(e.which == 2 || e.which == 3) {  (message);  return false; } } }
if(document.layers) {  document.captureEvents(Event.MOUSEDOWN);  document.onmousedown = clickNS;
} else {  document.onmouseup = clickNS; document.oncontextmenu = clickIE; }
document.oncontextmenu = new Function("return false")
</script>


<!-- Woopra Code Start -->
<script type="text/javascript">
	var _wh = ((document.location.protocol=='https:') ? "https://sec1.woopra.com" : "http://static.woopra.com");
	document.write(unescape("%3Cscript src='" + _wh + "/js/woopra.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<!-- Woopra Code End -->
<!-- Google Analytics -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-9461442-2");
pageTracker._trackPageview();
} catch(err) {}</script>
<!-- Google Analytics End -->


</body>



</html>