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
$VERSIONS[basename(__FILE__)] = "3.01 PRO";

$trackpage    = (int)$_GET['pa']  ? '?pa='.(int)$_GET['pa'].$trackgroup : '';
$trackpage_   = (int)$_GET['pa']  ? '&pa='.(int)$_GET['pa'].$trackgroup : '';
$filenamenr   = empty($filenamenr) ? 'index.php' : $filenamenr;

// Pages Submenu
if(count($PAGES)>1) {
	while(list($key,$val) = each($PAGES)) {
		if($key==$PAGE_ID) {
			$PAGESclass         = 'pages_highlight';
			$PAGESactive        = 'selected';
		} else {
			$PAGESclass         = 'pages';
			$PAGESactive        = '';
		}
		$PAGESslogan        = $_SP['pageslogan'.$key] ? $_SP['pageslogan'.$key] : $val['slogan'];
		$PAGESname          = $_SP['pagename'.$key] ? $_SP['pagename'.$key] : $val['name'];
		$PAGEMENU[]         = '<a href="'.$filenamenr.'?pa='.$key.'" class="'.$PAGESclass.'" title="'.$PAGESslogan.'">'.($PAGESname ? $PAGESname : $key).'</a>';
		$PAGEMENU_OPTIONS  .= '<option value="'.$key.'" '.$PAGESactive.'>'.($PAGESname ? $PAGESname : $key).'</option>';
	}
	$PAGESUBMENU         = '<img src="'.$designpath.'pages.gif">'.implode('<span style="color:#FFFFFF">|</span>',$PAGEMENU);
	$PAGESUBMENU_OPTIONS = '<img src="'.$designpath.'pages.gif" align="top"> <select class=pagessubmenu onChange="location.href=\''.$filenamenr.'?pa=\' + this.options[this.options.selectedIndex].value">'.$PAGEMENU_OPTIONS.'</select>';
}

// Activated languages
if($LANG_ACTIVE) {
	$sel[1] = 'selected';
	if(count($LANG_ACTIVE)>1) {
		while(list(,$lg) = each($LANG_ACTIVE)) {
			$langopt .= '<option name="lang" value="'.$lg['code'].'" '.$sel[$lg['code']==$lang].'>'.$lg['language'].'</option>';
		}
		$langopt = '<select name="lang" class=language onChange="location.href=\''.$filenamenr.'?lang=\' + this.options[this.options.selectedIndex].value +  \''.$trackpage_.'\'">'.$langopt.'</select>';
	}
	reset($LANG_ACTIVE);
}


$TMP['%[TITLE]%']             = $CONFIG['website_title'];
$TMP['%[STATUSINFO]%']        = 'onMouseOver="window.status=\''.htmlspecialchars(addslashes($CONFIG['domainname'])).'\';return true;"';
$TMP['%[LOGO]%']              = $CONFIG['logo'] ? 'mydir/'.$CONFIG['logo'] : $designpath.'logo.gif';
$TMP['%[PAGE_SLOGAN]%']       = $_SP['pageslogan'.$PAGE_ID] ? $_SP['pageslogan'.$PAGE_ID] : $PAGES[$PAGE_ID]['slogan'];
$TMP['%[LANGUAGES]%']         = $langopt;

$PAGESUBMENU = $CONFIG['submenu_style'] ? $PAGESUBMENU_OPTIONS : $PAGESUBMENU;
$TMP['%[GRID_SUBPAGES]%']        = $getprocess ? '' : $PAGESUBMENU;
$TMP['%[GRID_SUBPAGES_SELECT]%'] = $getprocess ? '' : $PAGESUBMENU_OPTIONS;
$TMP['%[PIXEL_FREE]%']        = $TMP['%[PIXEL_TOTAL_TEXT]%'] = $_SP[104];
$TMP['%[PIXEL_BUSY]%']        = $TMP['%[PIXEL_USED_TEXT]%']  = $_SP[105];
$TMP['%[PIXEL_ALL]%']         = number_format($pixel_gesamt,0,$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']);
$TMP['%[PIXEL_TOTAL]%']       = number_format($pixel_total,0,$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']);
$TMP['%[PIXEL_USED]%']        = number_format($pixel_used,0,$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']);
$TMP['%[BLOCKS_ALL]%']        = number_format($blocks_gesamt,0,$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']);
$TMP['%[BLOCKS_TOTAL]%']      = number_format($blocks_total,0,$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']);
$TMP['%[BLOCKS_USED]%']       = number_format($blocks_used,0,$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']);

// Mainmenu
$TMP['%[MENU_HOME]%']         = $TMP['%[MENU_INDEX]%'] = '<a href="index.php'.$trackpage.'" class=m>'.$_SP[102].'</a>';
$TMP['%[MENU_GETPIXEL]%']     = '&nbsp;|&nbsp; <a href="getpixel.php'.$trackpage.'" class=m>'.$_SP[103].'</a>';
$TMP['%[MENU_TOP]%']          = $TMP['%[MENU_RANKING]%'] = $CONFIG['menu_top']       ? '&nbsp;|&nbsp; <a href="ranking.php'.$trackpage.'" class=m>'.sprintf($_SP[36],(int)$CONFIG['ranking_value']).'</a>' : '';
$TMP['%[MENU_PIXELLIST]%']    = $CONFIG['menu_pixellist'] ? '&nbsp;|&nbsp; <a href="pixellist.php'.$trackpage.'" class=m>'.$_SP[37].'</a>' : '';
$TMP['%[MENU_REFERRER]%']     = $CONFIG['menu_referrer']  ? '&nbsp;|&nbsp; <a href="referrer.php'.$trackpage.'" class=m>'.$_SP[61].'</a>' : '';
$TMP['%[MENU_TRAFFIC]%']      = $CONFIG['menu_traffic']   ? '&nbsp;|&nbsp; <a href="traffic.php'.$trackpage.'" class=m>'.$_SP[62].'</a>' : '';
$TMP['%[MENU_BLOG]%']         = $CONFIG['menu_blog']      ? '&nbsp;|&nbsp; <a href="blog.php'.$trackpage.'" class=m>'.$_SP[63].'</a>' : '';
$TMP['%[MENU_FAQ]%']          = $CONFIG['menu_faq']       ? '&nbsp;|&nbsp; <a href="faq.php'.$trackpage.'" class=m>'.$_SP[64].'</a>' : '';

// Premenu
$TMP['%[MENU_FAVORIT]%']      = '<a href="javascript:void(addBookmark(\''.$DOMAIN."','".htmlspecialchars(addslashes($CONFIG['domainname'])).'\'));" class=l>'.$_SP[76].'</a>';
$TMP['%[MENU_FEEDBACK]%']     = $CONFIG['menu_feedback']  ? '&nbsp;|&nbsp; <a href="feedb.php'.$trackpage.'" class=l>'.$_SP[34].'</a>' : '';
$TMP['%[MENU_LINKUS]%']       = $CONFIG['menu_linkus']    ? '&nbsp;|&nbsp; <a href="linkus.php'.$trackpage.'" class=l>'.$_SP[35].'</a>' : '';
$TMP['%[MENU_RECOMMEND]%']    = $CONFIG['menu_recommend'] ? '&nbsp;|&nbsp; <a href="recommend.php'.$trackpage.'" class=l>'.$_SP[65].'</a>' : '';
$TMP['%[MENU_LOGIN]%']        = $login_logged_in ? '&nbsp;|&nbsp; <a href="login.php?logoff=1'.$trackpage_.'" class=l>'.$_SP[119].'</a>' : '&nbsp;|&nbsp; <a href="login.php'.$trackpage.'" class=l>'.$_SP[107].'</a>';
$TMP['%[MENU_NEWSLETTER]%']   = $CONFIG['menu_newsletter']? '&nbsp;|&nbsp; <a href="mailing.php'.$trackpage.'" class=l>'.$_SP[106].'</a>' : '';

// Newsletter form optional
$TMP['%[FORM_NEWSLETTER]%']   = $CONFIG['menu_newsletter']? '<form name=m method=post action="mailing.php'.$trackpage.'">' : '';
$TMP['%[INPUT_NEWSLETTER]%']  = $CONFIG['menu_newsletter']? '<input type=text value="your@email.address" name=ui maxlength=100 style="width:120;height=18" onClick="this.value=\'\'">' : '';
$TMP['%[SUBMIT_NEWSLETTER]%'] = $CONFIG['menu_newsletter']? '<input type=submit value="OK" name=sub style="width:35;height=20"><div style="float:right"></form></div>' : '';

// Clear Links
$TMP['%[LINK_HOME]%']         = $TMP['%[LINK_INDEX]%'] = 'index.php'.$trackpage;
$TMP['%[LINK_GETPIXEL]%']     = 'getpixel.php'.$trackpage;

$TMP['%[LINK_FAVORIT]%']      = 'javascript:void(addBookmark(\''.$DOMAIN."','".htmlspecialchars(addslashes($CONFIG['domainname'])).'\'))';
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
$TMP['%[LINK_NEWSLETTER]%']   = 'mailing.php'.$trackpage;
$TMP['%[LINK_LOGIN]%']        = $login_logged_in ? 'login.php?logoff=1'.$trackpage_ : 'login.php'.$trackpage;

// Suchfelder global/grid visible
$TMP['%[SEARCHGLOBAL]%'] = (!$getprocess) ?
						 '<form method="GET" action="pixellist.php" name="find">
						 <input type="hidden" name="global" value="1">
						 <input type="hidden" name="pa" value="'.$_REQUEST['pa'].'">
						 <input type="text" name="f" value="'.htmlspecialchars(stripslashes($_REQUEST['f'])).'" class="globalsearch_input">
						 <input type="submit" value=" '.$_SP[38].' " class="globalsearch_submit"><div style="float:right"></form></div>' : '';

$TMP['%[SEARCHALL]%'] = (!$getprocess) ?
						 '<form method="GET" action="" name="find">
						 <input type="hidden" name="pa" value="'.$_REQUEST['pa'].'">
						 <input type="text" name="gridsearch" value="'.htmlspecialchars(stripslashes($_REQUEST['gridsearch'])).'" class="globalsearch_input">
						 <input type="submit" value=" '.$_SP[38].' " class="globalsearch_submit"><div style="float:right"></form></div>' : '';

if($ZOOM) {
	$TMP['%[MENU_ZOOM]%'] = !$_GET['zoom'] ? '&nbsp;|&nbsp; <a href="index.php?zoom=1'.$trackpage_.'" class=l>'.$_SP[74].'</a>' : '&nbsp;|&nbsp; <a href="index.php'.$trackpage.'" class=l>'.$_SP[75].'</a>';
	$TMP['%[LINK_ZOOM]%'] = !$_GET['zoom'] ? 'index.php?zoom=1'.$trackpage_ : 'index.php'.$trackpage;
} else {
	$TMP['%[MENU_ZOOM]%'] = $TMP['%[LINK_ZOOM]%'] = '';
}


// No-Cache-Header
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
echo '
<link rel="shortcut icon" href="favicon.ico" >
<link rel="icon" href="animated_favicon1.gif" type="image/gif" >
';
$plugin = $GRID[$getprocess]['plugin'] || $grid_plugin ? '_plugin' : '';

print template($LANGDIR.'header'.$plugin.'.htm',$TMP);

if($_GET['zoom']) print '<script type="text/javascript" src="tjpzoom.js"></script>';

$TMP = array();
flush();

if($getprocess)
	print '<script type="text/javascript" src="wz_jsgraphics.js"></script>';

?>
<script language="JavaScript1.2" type="text/javascript">
	function addBookmark(url,title) {
		if (window.sidebar) {
			window.sidebar.addPanel(title, url,""); } else if( document.all ) {
			window.external.AddFavorite( url, title); } else if( window.opera && window.print ) { return true;
		}
	}
</script>