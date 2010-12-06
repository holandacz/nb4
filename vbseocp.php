<?php

/************************************************************************************
* vBSEO 3.2.0 for vBulletin v3.x.x by Crawlability, Inc.
*                                                                                   *
* Copyright © 2005-2008, Crawlability, Inc. All rights reserved.                    *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/

@define('VBSEO_IS_VBSEOCP', 1);
error_reporting(E_ALL&~E_NOTICE);
if (!defined('VBSEO_CONFIG_FILENAME'))
define('VBSEO_CONFIG_FILENAME', dirname(__FILE__) . '/includes/config_vbseo.php');
$vbseo_filename = VBSEO_CONFIG_FILENAME;
$vbseo_funcfilename = 'includes/functions_vbseo.php';
$lang_dir = dirname(__FILE__) . '/includes/xml/';
$fcontent = @implode('', file($vbseo_filename));
preg_match('#define\(\s*\'VBSEO_ADMIN_PASSWORD\',\s*\'(.*?)\'#is', $fcontent, $getp);
$login_seed = md5(md5($getp[1] . VBSEO_CONFIG_ID));
$login_seed2 = md5(md5(md5(md5($getp[1])) . VBSEO_CONFIG_ID));
$logged_in = ($_COOKIE['vbseocpid'] == $login_seed) || ($_COOKIE['vbseocpid'] == $login_seed2);
define('VBSEO_NO_LICENSE_CHECK_5342', true);
function vbseo_check_arr (&$ptxt, $remove = true)
{
$ptxt = trim($ptxt);
$psplit = preg_split('#[\r\n]+#', trim($ptxt));
$pcomb = '';
for($i = 0; $i < count($psplit); $i++)
{
unset($GLOBALS['vbseo_crcheck']);
$psi = preg_replace('#,+\s*$#', '', $psplit[$i]);
@eval("\$GLOBALS['vbseo_crcheck']=array(\n" . $psi . "\n);");
if ($GLOBALS['vbseo_crcheck'])
$pcomb .= $psi . ",\n";
else
if ($psi && !preg_match('#^\s*//#', $psi))
{
$ptxt = preg_replace('#^' . preg_quote($psi, '#') . '$#m', '// $0', $ptxt);
}
}
return $pcomb;
}
function vbseo_check_arr_nonassoc ($ar)
{
$ret = '';
if ($ar && is_array($ar))
foreach($ar as $as)
$ret .= ($ret?',':'') . "\n'" . str_replace("'", "\\'", $as) . "'";
return $ret;
}
function vbseocp_option_set_array($optname, $value)
{
global $fcontent;
$optdef = '$'.$optname.' = array(';
$p1 = strpos($fcontent, $optdef);
$p2 = strpos($fcontent, ');', $p1);
$p1 += strlen($optdef);
$fcontent = substr_replace($fcontent, '', $p1, $p2 - $p1);
$fcontent = preg_replace('#(\$'.$optname.' = array\s*\()\s*.*?\s*(\);)#s', '$1' . $value . '$2', $fcontent);
}
function vbseocp_unhtmlentities ($string)
{
$trans_tbl = get_html_translation_table (HTML_ENTITIES);
$trans_tbl = array_flip ($trans_tbl);
return strtr ($string , $trans_tbl);
}
function vbseocp_addslashes($value)
{
return str_replace(array("\\", "'", '$'), array("\\\\", "\\'", '\\$'), $value);
}
function vbseocp_change_setting($option, $value, $type = 0)
{
global $fcontent;
if ($type == 1) $value = $value ? 1 : 0;
if ($type == 2) $value = intval($value) ? intval($value) : 0;
$value = vbseocp_addslashes($value);
if (preg_match('#(define\(\'' . $option . '\',\s*?)\'\'#is', $fcontent))
$fcontent = preg_replace('#(define\(\'' . $option . '\',\s*?)\'\'#is',
"$01'" . $value . "'", $fcontent);
else
{
if ($type > 0)
$fcontent = preg_replace('#(define\(\'' . $option . '\',\s*?)\d+#is',
"$01" . $value . "", $fcontent);
else
$fcontent = preg_replace('#(define\(\'' . $option . '\',\s*?)\'.*?[^\\\\]\'#is',
"$01'" . $value . "'", $fcontent);
}
}
function vbseocp_get_setting($option, $type)
{
global $fcontent;
if (preg_match('#(define\(\'' . $option . '\',\s*?)\'\'#is', $fcontent))
return '';
else
if ($type > 0)
preg_match('#(define\(\'' . $option . '\',\s*?)(\d+)#is', $fcontent, $pm);
else
preg_match('#(define\(\'' . $option . '\',\s*?)\'(.*?[^\\\\])\'#is', $fcontent, $pm);
return $pm[2];
}
function vbseo_check_arr_assoc ($ar)
{
$ret = '';
if ($ar)
foreach($ar as $ak => $av)
$ret .= ($ret?',':'') . "\n'" . str_replace("'", "\\'", $ak) . "' => '" . str_replace("'", "\\'", $av) . "'";
return $ret;
}
function vbseo_cp_get_options($noninc = array(), $getv = '')
{
global $vbseo_filename;
global $seo_replacements, $vbseo_custom_rules, $vbseo_custom_rules_text,
$vbseo_custom_301, $vbseo_custom_301_text,
$vbseo_custom_char_replacement, $vbseo_relev_replace_t, $vbseo_relev_replace_b, $vbseo_relev_replace,
$vbseo_images_dim, $vbseo_forum_slugs;
$fcontent = @implode('', file($vbseo_filename));
include_once $vbseo_filename;
$acros = $crules = $c301 = '';
preg_match_all('#define\(\s*\'([^\']+)\',\s*\'()\'#im', $fcontent, $setm, PREG_SET_ORDER);
preg_match_all('#define\(\s*\'([^\']+)\',\s*(\d+)#im', $fcontent, $setm2, PREG_SET_ORDER);
preg_match_all('#define\(\s*\'([^\']+)\',\s*\'(.*?[^\\\\])\'#im', $fcontent, $setm3, PREG_SET_ORDER);
$setm2 = array_merge($setm2, $setm3);
foreach($setm2 as $sm2)
{
$found = false;
foreach($setm as $sm)
if ($sm[1] == $sm2[1])
{
$found = true;
break;
}
if (!$found)$setm[] = $sm2;
}
if ($seo_replacements)
foreach($seo_replacements as $k => $v)
$acros .= "\n'" . addslashes($k) . "' => '" . addslashes($v) . "'";
if ($vbseo_custom_rules)
foreach($vbseo_custom_rules as $k => $v)
$crules .= "\n'" . addslashes(htmlspecialchars($k)) . "' => '" . addslashes(htmlspecialchars($v)) . "'";
if ($vbseo_custom_301)
foreach($vbseo_custom_301 as $k => $v)
$c301 .= "\n'" . addslashes(htmlspecialchars($k)) . "' => '" . addslashes(htmlspecialchars($v)) . "'";
$setm[] = array('', 'images_dim', serialize($vbseo_images_dim));
$setm[] = array('', 'forum_slugs', serialize($vbseo_forum_slugs));
$setm[] = array('', 'relev_repl', serialize($vbseo_relev_replace));
$setm[] = array('', 'relev_repl_t', serialize($vbseo_relev_replace_t));
$setm[] = array('', 'char_repl', serialize($vbseo_custom_char_replacement));
$setm[] = array('', 'acronyms', $acros);
$setm[] = array('', 'custom_rules', $crules);
$setm[] = array('', 'custom_rules_text', addslashes(htmlspecialchars($vbseo_custom_rules_text)));
$setm[] = array('', 'custom_301', $c301);
$setm[] = array('', 'custom_301_text', addslashes(htmlspecialchars($vbseo_custom_301_text)));
if ($getv == 'urw')
{
$setm2 = array();
for($i = 0; $i < count($setm); $i++)
{
$st = $setm[$i][1];
if ((strstr($st, 'VBSEO_REWRITE_') 
&& !strstr($st, 'META') 
&& !strstr($st, 'ADDTITLE') 
&& !strstr($st, 'KEYWORDS') 
&& !strstr($st, 'EMAILS')
&& !strstr($st, 'URLENCODING')
) 
|| (strstr($st, 'VBSEO_URL_') 
&& !strstr($st, '_DIRECT')
)
|| strstr($st, 'VBSEO_FORUM_TITLE_BIT')
)$setm2[] = $setm[$i];
}
$setm = $setm2;
}
if ($noninc)
{
$setm2 = array();
for($i = 0; $i < count($setm); $i++)
if (!in_array($setm[$i][1], $noninc))
$setm2[] = $setm[$i];
$setm = $setm2;
}
return $setm;
}
function vbseo_cp_put_options($setm, $noninc = array())
{
global $vbseo_filename, $fcontent;
$fcontent = @implode('', file($vbseo_filename));
$def_values = array();
for($i = 0; $i < count($setm); $i++)
if (!in_array($setm[$i][1], $noninc))
{
$sk = $setm[$i];
switch ($sk[1])
{
case 'images_dim':
$v = unserialize($sk[2]);
$va = '';
if ($v)
{
foreach($v as $k2 => $v2)
$va .= "\n'$k2' => array($v2[0],$v2[1]),";
$va .= "\n";
}
$v = 'array(' . $va . ')';
$fcontent = preg_replace('#(\$vbseo_images_dim\s*=\s*)array\s*\(\s*.*?\s*\)(;)#s', "$1" . $v . "$2", $fcontent);
break;
case 'relev_repl':
$v = unserialize($sk[2]);
$v = vbseo_check_arr_nonassoc($v);
$fcontent = preg_replace('#(\$vbseo_relev_replace = array\s*\()\s*.*?\s*(\);)#s', '$1' . $v . '$2', $fcontent);
break;
case 'relev_repl_t':
$v = unserialize($sk[2]);
$v = vbseo_check_arr_nonassoc($v);
$fcontent = preg_replace('#(\$vbseo_relev_replace_t = array\s*\()\s*.*?\s*(\);)#s', '$1' . $v . '$2', $fcontent);
break;
case 'char_repl':
$v = unserialize($sk[2]);
$v = vbseo_check_arr_assoc ($v);
$fcontent = preg_replace('#(\$vbseo_custom_char_replacement = array\s*\()\s*.*?\s*(\);)#s', '$1' . $v . '$2', $fcontent);
break;
case 'forum_slugs':
$v = unserialize($sk[2]);
$v = vbseo_check_arr_assoc($v);
$fcontent = preg_replace('#(\$vbseo_forum_slugs = array\s*\()\s*.*?\s*(\);)#s', '$1' . $v . '$2', $fcontent);
break;
case 'acronyms':
$v = preg_replace('#[\r\n]+#', ",\n", trim($sk[2]));
$fcontent = preg_replace('#(\$seo_replacements\s*=\s*array\(\s*).*?(\s*\);)#s', "$1" . $v . "$2", $fcontent);
break;
case 'custom_rules':
$v = vbseocp_unhtmlentities(preg_replace('#[\r\n]+#', ",\n", trim($sk[2])));
$fcontent = preg_replace('#(\$vbseo_custom_rules\s*=\s*array\(\s*).*?(\s*\);)#s', "$1" . str_replace('$', '\$', $v) . "$2", $fcontent);
break;
case 'custom_rules_text':
$v = vbseocp_unhtmlentities($sk[2]);
$fcontent = preg_replace('#(\$vbseo_custom_rules_text = \').*?(\';)#s', '$1' . str_replace(array("'", '$'), array("\\'", '\$'), $v) . '$2', $fcontent);
break;
case 'custom_301':
$v = vbseocp_unhtmlentities(preg_replace('#[\r\n]+#', ",\n", trim($sk[2])));
$fcontent = preg_replace('#(\$vbseo_custom_301\s*=\s*array\(\s*).*?(\s*\);)#s', "$1" . str_replace('$', '\$', $v) . "$2", $fcontent);
break;
case 'custom_301_text':
$v = vbseocp_unhtmlentities($sk[2]);
$fcontent = preg_replace('#(\$vbseo_custom_301_text = \').*?(\';)#s', '$1' . str_replace(array("'", '$'), array("\\'", '\$'), $v) . '$2', $fcontent);
break;
default:
$sk[2] = stripslashes($sk[2]);
$def_values[$sk[1]] = $sk[2];
vbseocp_change_setting($sk[1], $sk[2], (($sk[0][strlen($sk[0])-1] == "'") || !preg_match('#^\d+$#', $sk[2]))?0:2);
break;
}
}
global $url_formats;
$urlformats_arr = '';
include_once dirname(__FILE__).'/includes/functions_vbseo_url.php';
$pre_replace = vbseo_prep_format_replacements(
vbseocp_get_setting('VBSEO_FILTER_FOREIGNCHARS', 1), 
vbseocp_get_setting('VBSEO_SPACER', 0),
vbseocp_get_setting('VBSEO_REWRITE_MEMBER_MORECHARS', 1)
);
foreach($url_formats as $uformat => $udefinition)
{
$ff = vbseocp_get_setting($udefinition, 0);
$ff = preg_quote($ff, '#');
$ff = preg_replace(array_keys($pre_replace), $pre_replace, $ff);
$urlformats_arr[] = "'" . $udefinition . "' => '" . $ff . "'";
}
$urlformats_list = implode(",\n", $urlformats_arr);
$fcontent = preg_replace('#(\$vbseo_url_formats = array\()\s*.*?(\);)#s', '$1' . $urlformats_list . '$2', $fcontent);
if (file_exists($vbseo_filename) && $fcontent)
{
$pf = fopen($vbseo_filename, 'w');
fwrite($pf, $fcontent);
fclose($pf);
}
}
$url_formats = array(
'bformat' => 'VBSEO_FORUM_TITLE_BIT',
'fformat' => 'VBSEO_URL_FORUM',
'findexformat' => 'VBSEO_URL_FORUM_PAGENUM',
'tformat' => 'VBSEO_URL_THREAD',
'tmultiformat' => 'VBSEO_URL_THREAD_PAGENUM',
'tlastpostformat' => 'VBSEO_URL_THREAD_LASTPOST',
'tnewpostformat' => 'VBSEO_URL_THREAD_NEWPOST',
'tgotopostformat' => 'VBSEO_URL_THREAD_GOTOPOST',
'tmultigotopostformat' => 'VBSEO_URL_THREAD_GOTOPOST_PAGENUM',
'tprevthreadformat' => 'VBSEO_URL_THREAD_PREV',
'tnextthreadformat' => 'VBSEO_URL_THREAD_NEXT',
'pformat' => 'VBSEO_URL_POLL',
'aformat' => 'VBSEO_URL_FORUM_ANNOUNCEMENT',
'amultiformat' => 'VBSEO_URL_FORUM_ANNOUNCEMENT_ALL',
'mformat' => 'VBSEO_URL_MEMBER',
'mmsgformat' => 'VBSEO_URL_MEMBER_MSGPAGE',
'mconvformat' => 'VBSEO_URL_MEMBER_CONV',
'mconvpageformat' => 'VBSEO_URL_MEMBER_CONVPAGE',
'mfriendsformat' => 'VBSEO_URL_MEMBER_FRIENDSPAGE',
'malbumsformat' => 'VBSEO_URL_MEMBER_ALBUMS',
'malbumformat' => 'VBSEO_URL_MEMBER_ALBUM',
'mpicformat' => 'VBSEO_URL_MEMBER_PICTURE',
'mpicpageformat' => 'VBSEO_URL_MEMBER_PICTURE_PAGE',
'mpicimgformat'=>'VBSEO_URL_MEMBER_PICTURE_IMG',
'mlistformat' => 'VBSEO_URL_MEMBERLIST',
'mpagesformat' => 'VBSEO_URL_MEMBERLIST_PAGENUM',
'mletterformat' => 'VBSEO_URL_MEMBERLIST_LETTER',
'avatarformat' => 'VBSEO_URL_AVATAR',
'fbulletformat' => 'VBSEO_URL_FORUM_TREE_ICON',
'tbulletformat' => 'VBSEO_URL_THREAD_TREE_ICON',
'attachformat' => 'VBSEO_URL_ATTACHMENT',
'attachaltformat' => 'VBSEO_URL_ATTACHMENT_ALT',
'tprintthreadformat' => 'VBSEO_URL_THREAD_PRINT',
'tmultiprintformat' => 'VBSEO_URL_THREAD_PRINT_PAGENUM',
'tshowpostformat' => 'VBSEO_URL_POST_SHOW',
'arcroot' => 'VBSEO_ARCHIVE_ROOT',
'bloghomeformat' => 'VBSEO_URL_BLOG_HOME',
'blogindformat' => 'VBSEO_URL_BLOG_ENTRY',
'blogindpageformat' => 'VBSEO_URL_BLOG_ENTRY_PAGE',
'blogindredirformat' => 'VBSEO_URL_BLOG_ENTRY_REDIR',
'bloguserformat' => 'VBSEO_URL_BLOG_USER',
'bloguserpageformat' => 'VBSEO_URL_BLOG_USER_PAGE',
'blogcatformat' => 'VBSEO_URL_BLOG_CAT',
'blogcatpageformat' => 'VBSEO_URL_BLOG_CAT_PAGE',
'bloglistformat' => 'VBSEO_URL_BLOG_LIST',
'bloglistpageformat' => 'VBSEO_URL_BLOG_LIST_PAGE',
'blogblistformat' => 'VBSEO_URL_BLOG_BLIST',
'blogblistpageformat' => 'VBSEO_URL_BLOG_BLIST_PAGE',
'blognextformat' => 'VBSEO_URL_BLOG_NEXT',
'blogprevformat' => 'VBSEO_URL_BLOG_PREV',
'blogfeedformat' => 'VBSEO_URL_BLOG_FEED',
'blogfeeduserformat' => 'VBSEO_URL_BLOG_FEEDUSER',
'blogmonthformat' => 'VBSEO_URL_BLOG_MONTH',
'blogdayformat' => 'VBSEO_URL_BLOG_DAY',
'blogmonthpageformat' => 'VBSEO_URL_BLOG_MONTH_PAGE',
'blogdaypageformat' => 'VBSEO_URL_BLOG_DAY_PAGE',
'blogumonthformat' => 'VBSEO_URL_BLOG_UMONTH',
'blogudayformat' => 'VBSEO_URL_BLOG_UDAY',
'blogattformat' => 'VBSEO_URL_BLOG_ATT',
'blogbestentformat' => 'VBSEO_URL_BLOG_BEST_ENT',
'blogbestblogsformat' => 'VBSEO_URL_BLOG_BEST_BLOGS',
'blogbestentpageformat' => 'VBSEO_URL_BLOG_BEST_ENT_PAGE',
'blogbestblogspageformat' => 'VBSEO_URL_BLOG_BEST_BLOGS_PAGE',
'blogclistformat' => 'VBSEO_URL_BLOG_CLIST',
'blogclistpageformat' => 'VBSEO_URL_BLOG_CLIST_PAGE',
'groupshomeformat' => 'VBSEO_URL_GROUPS_HOME',
'groupshomepageformat' => 'VBSEO_URL_GROUPS_HOME_PAGE',
'groupsformat' => 'VBSEO_URL_GROUPS',
'groupspageformat' => 'VBSEO_URL_GROUPS_PAGE',
'groupmembersformat' => 'VBSEO_URL_GROUPS_MEMBERS',
'groupmemberspageformat' => 'VBSEO_URL_GROUPS_MEMBERS_PAGE',
'grouppicformat' => 'VBSEO_URL_GROUPS_PIC',
'grouppicpageformat' => 'VBSEO_URL_GROUPS_PIC_PAGE',
'grouppictureformat' => 'VBSEO_URL_GROUPS_PICTURE',
'grouppicturepageformat' => 'VBSEO_URL_GROUPS_PICTURE_PAGE',
'grouppictureimgformat' => 'VBSEO_URL_GROUPS_PICTURE_IMG',
'tagshomeformat' => 'VBSEO_URL_TAGS_HOME',
'tagsformat' => 'VBSEO_URL_TAGS_ENTRY',
'tagspageformat' => 'VBSEO_URL_TAGS_ENTRYPAGE',
);
if (isset($_POST['setpass']))
{
include_once $vbseo_filename;
$fail_setpass = '';
if ($_POST['password'] != $_POST['password2'])
$fail_setpass = 'pass_notsame';
elseif ($_POST['password'] == '')
$fail_setpass = 'pass_empty';
elseif (VBSEO_ADMIN_PASSWORD != '')
$fail_setpass = 'pass_defined';
elseif (!is_writable($vbseo_filename))
$fail_setpass = 'config_readonly';
if (!$fail_setpass)
{
$fcontent = @implode('', file($vbseo_filename));
vbseocp_change_setting('VBSEO_ADMIN_PASSWORD', md5(md5($_POST['password'])));
if (file_exists($vbseo_filename) && $fcontent)
{
$pf = fopen($vbseo_filename, 'w');
fwrite($pf, $fcontent);
fclose($pf);
}
setcookie('vbseocpid', $_COOKIE['runcode'] = md5(md5(md5(md5($_POST['password'])) . VBSEO_CONFIG_ID))); //.$_SERVER['REMOTE_ADDR'])));
$logged_in = 1;
}
else
$logged_in = 0;
}
else
if (isset($_POST['login']))
{
include_once $vbseo_filename;
if ((md5(md5($_POST['password'])) == VBSEO_ADMIN_PASSWORD) ||
(($_POST['password'] == VBSEO_ADMIN_PASSWORD) && !preg_match('#[a-f0-9]{32}#', VBSEO_ADMIN_PASSWORD))
)
{
setcookie('vbseocpid', $_COOKIE['runcode'] = md5(md5(md5(md5($_POST['password'])) . VBSEO_CONFIG_ID))); //.$_SERVER['REMOTE_ADDR'])));
$logged_in = true;
$success_login = true;
}
else
{
setcookie('vbseocpid', $_COOKIE['runcode'] = '');
$fail_login = true;
}
}
else
if (isset($_GET['logout']))
{
setcookie('vbseocpid', $_COOKIE['runcode'] = '');
$logged_out = true;
}
else
{
if (function_exists('session_write_close'))
session_write_close();
if ($logged_in)
{
if ($_POST) $p = &$_POST;
else $p = &$_GET;
$fcontent = @implode('', file($vbseo_filename));
if (isset($p['getsettings']))
{
$sets = '';
$setm = vbseo_cp_get_options(array('VBSEO_ADMIN_PASSWORD'), $p['get']);
for($i = 0; $i < count($setm); $i++)
{
$sk = $setm[$i];
$sets .= "\n\t<setting>\n\t\t<name>" . $sk[1] . "</name>\n\t\t<value>" . $sk[2] . "</value>\n\t</setting>";
}
$expnames = array ('all' => 'vbseo_all',
'urw' => 'vbseo_urls',
);
$xcont = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n\n<settings>$sets\n</settings>";
@header('Content-Type: application/xml');
@header('Content-Disposition: attachment; filename=' . $expnames[$p['get']] . '.xml');
echo $xcont;
exit;
}
if (isset($p['putpreset']) && $p['preset'])
{
$xcont = implode('', file($lang_dir . 'vbseo_urls_' . $p['preset'] . '.xml'));
preg_match_all('#<setting>.*?<name>(.*?)</name>.*?<value>(.*?)</value>#is', $xcont, $setm, PREG_SET_ORDER);
$setm2 = array();
foreach($setm as $sm)
{
$k = $sm[1];
$v = $sm[2];
if($_REQUEST['type'] == 'blog' && strstr($k,'VBSEO_URL_BLOG'))
$setm2[] = $sm;
else
if($_REQUEST['type'] != 'blog' && !strstr($k,'VBSEO_URL_BLOG'))
$setm2[] = $sm;
}
vbseo_cp_put_options($setm2, array('VBSEO_SPACER','VBSEO_URL_PART_MAX'));
$config_imported = true;
}
if (isset($p['putsettings']) && ($fl = $_FILES['file']) && $fl['size'])
{
$xcont = implode('', file($fl['tmp_name']));
preg_match_all('#<setting>.*?<name>(.*?)</name>.*?<value>(.*?)</value>#is', $xcont, $setm, PREG_SET_ORDER);
vbseo_cp_put_options($setm, array('VBSEO_REFBACK_BLACKLIST'));
$config_imported = true;
}
if (isset($p['editlic']))
{
vbseocp_change_setting('VBSEO_LICENSE_CODE', trim($p['license_code']));
}
if (isset($p['saveoptions']))
{
if (get_magic_quotes_gpc())
{
$unmagic = array('images_dim', 'replacements', 'customrules', 'custom301', 'stopwords',
'domwhitelist', 'domblacklist', 'linkback_black', 'hpaliases', 'ignorepages', 'extaddtitles_black',
'ping_stopwords', 'mc_hosts'
);
foreach($p as $k => $v)
if (!is_array($v))
$p[$k] = stripslashes($v);
for($i = 0;$i < 3;$i++)
{
$p['relev_repl'][$i] = stripslashes($p['relev_repl'][$i]);
$p['relev_repl_t'][$i] = stripslashes($p['relev_repl_t'][$i]);
}
}
$unlist = array('stopwords', 'ping_stopwords', 'domwhitelist', 'pingback_service', 
'domblacklist', 'hpaliases', 'ignorepages', 'extaddtitles_black');
foreach($unlist as $un)
{
$p[$un] = preg_replace('#\s*[\r\n]+\s*#', '|', trim($p[$un]));
}
$p['acustomrules'] = vbseo_check_arr($p['customrules']);
$p['acustom301'] = vbseo_check_arr($p['custom301']);
$p['replacements'] = vbseo_check_arr($p['replacements']);
$p['relev_repl'] = vbseo_check_arr_nonassoc($p['relev_repl']);
$p['relev_repl_t'] = vbseo_check_arr_nonassoc($p['relev_repl_t']);
$p['images_dim'] = vbseo_check_arr($p['images_dim']);
$p['images_dim'] = preg_replace('#\'(\d+)x(\d+)\'#', "array(\\1,\\2)", $p['images_dim']);
vbseocp_change_setting('VBSEO_ENABLED', $p['activate'], 1);
vbseocp_change_setting('VBSEO_CP_LANGUAGE', $p['vbseocplang']);
vbseocp_change_setting('VBSEO_URL_BLOG_DOMAIN', $p['blogdom'], 0);
vbseocp_change_setting('VBSEO_REWRITE_BLOGS', $p['blog_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_BLOGS_ENT', $p['blog_urls_ent'], 1);
vbseocp_change_setting('VBSEO_REWRITE_BLOGS_CAT', $p['blog_urls_cat'], 1);
vbseocp_change_setting('VBSEO_REWRITE_BLOGS_ATT', $p['blog_urls_att'], 1);
vbseocp_change_setting('VBSEO_REWRITE_BLOGS_FEED', $p['blog_urls_feed'], 1);
vbseocp_change_setting('VBSEO_REWRITE_BLOGS_LIST', $p['blog_urls_list'], 1);
vbseocp_change_setting('VBSEO_LINK', $p['vbseolink'], 1);
vbseocp_change_setting('VBSEO_404_HANDLE', $p['hp404'], 2);
vbseocp_change_setting('VBSEO_404_CUSTOM', $p['hp404custom']);
vbseocp_change_setting('VBSEO_THREAD_301_REDIRECT', $p['thread301redirect'], 1);
vbseocp_change_setting('VBSEO_CACHE_TYPE', $p['cachetype'], 2);
vbseocp_change_setting('VBSEO_MEMCACHE_PERS', $p['mc_pers'], 1);
vbseocp_change_setting('VBSEO_MEMCACHE_TTL', $p['mc_ttl'], 2);
vbseocp_change_setting('VBSEO_MEMCACHE_TIMEOUT', $p['mc_timeout'], 2);
vbseocp_change_setting('VBSEO_MEMCACHE_RETRY', $p['mc_retry'], 2);
vbseocp_change_setting('VBSEO_MEMCACHE_COMPRESS', $p['mc_compress'], 2);
vbseocp_change_setting('VBSEO_MEMCACHE_HOSTS', $p['mc_hosts'], 0);
vbseocp_change_setting('VBSEO_FILTER_FOREIGNCHARS', $p['foreignchars'], 2);
vbseocp_change_setting('VBSEO_CODE_CLEANUP', $p['codecleanup'], 1);
vbseocp_change_setting('VBSEO_BOOKMARK_THREAD', ($p['bookmark_disp'] > 0), 1);
vbseocp_change_setting('VBSEO_BOOKMARK_POST', ($p['bookmark_disp'] == 1), 1);
vbseocp_change_setting('VBSEO_BOOKMARK_BLOG', $p['blogbookm'], 2);
vbseocp_change_setting('VBSEO_BOOKMARK_DIGG', $p['bmark_digg'], 1);
vbseocp_change_setting('VBSEO_BOOKMARK_DELICIOUS', $p['bmark_delicious'], 1);
vbseocp_change_setting('VBSEO_BOOKMARK_TECHNORATI', $p['bmark_tech'], 1);
vbseocp_change_setting('VBSEO_BOOKMARK_FURL', $p['bmark_furl'], 1);
vbseocp_change_setting('VBSEO_BOOKMARK_CUSTOM', $p['bmark_custom'], 1);
$bmarkserv = preg_replace('#[\r\n]+#', '|', $p['bmark_serv']);
vbseocp_change_setting('VBSEO_BOOKMARK_SERVICES', $bmarkserv);
vbseocp_change_setting('VBSEO_CATEGORY_ANCHOR_LINKS', $p['catlinks'], 1);
vbseocp_change_setting('VBSEO_CODE_CLEANUP_PREVIEW', $p['cleanup_preview'], 1);
vbseocp_change_setting('VBSEO_CODE_CLEANUP_MEMBER_DROPDOWN', $p['cleanup_memdropdown'], 1);
vbseocp_change_setting('VBSEO_CODE_CLEANUP_LASTPOST', $p['cleanup_lastpost'], 2);
vbseocp_change_setting('VBSEO_FORUMJUMP_OFF', $p['forumjump'], 1);
vbseocp_change_setting('VBSEO_DIRECTLINKS_THREADS', $p['dirlinks_threads'], 1);
$a_rd = $a_rw = false;
switch ($p['archive'])
{
case 'arc1':$a_rd = true;
break;
case 'arc2':$a_rw = true;
break;
case 'arc3':$a_rd = $a_rw = true;
break;
}
vbseocp_change_setting('VBSEO_REWRITE_ARCHIVE_URLS', $a_rw, 1);
vbseocp_change_setting('VBSEO_REDIRECT_ARCHIVE', $a_rd, 1);
vbseocp_change_setting('VBSEO_ARCHIVE_ORDER_DESC', $p['arcorder'], 1);
vbseocp_change_setting('VBSEO_ARCHIVE_LINKS_FOOTER', $p['arc_footer'], 2);
vbseocp_change_setting('VBSEO_REWRITE_FORUM', $p['forum_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_THREADS', $p['thread_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_THREADS_ADDTITLE', $p['addtitles'], 2);
vbseocp_change_setting('VBSEO_REWRITE_THREADS_ADDTITLE_POST', $p['addtitlespost'], 1);
vbseocp_change_setting('VBSEO_REWRITE_EXT_ADDTITLE', $p['extaddtitles'], 1);
vbseocp_change_setting('VBSEO_REWRITE_EXT_ADDTITLE_BLACKLIST', $p['extaddtitles_black']);
vbseocp_change_setting('VBSEO_REWRITE_ANNOUNCEMENT', $p['announcement_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_POLLS', $p['polls_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_MEMBERS', $p['member_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_MEMBER_LIST', $p['memberlist_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_AVATAR', $p['avatar_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_TREE_ICON', $p['treeicon_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_ATTACHMENTS', $p['attachment_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_ATTACHMENTS_ALT', $p['attachment_alts'], 1);
vbseocp_change_setting('VBSEO_REWRITE_GROUPS', $p['groups_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_TAGS', $p['tags_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_PRINTTHREAD', $p['printthread_urls'], 1);
vbseocp_change_setting('VBSEO_REWRITE_SHOWPOST', $p['showpost_urls'], 2);
vbseocp_change_setting('VBSEO_REWRITE_EMAILS', $p['emails_urls'], 1);
vbseocp_change_setting('VBSEO_SITEMAP_MOD', $p['sitemap_mod'], 1);
vbseocp_change_setting('VBSEO_HP_FORCEINDEXROOT', $p['hp_forceindexroot'], 1);
vbseocp_change_setting('VBSEO_IMAGES_DIM', $p['imagesdim'], 1);
vbseocp_change_setting('VBSEO_NOFOLLOW_PRINTTHREAD', $p['nofollow_printthread'], 1);
vbseocp_change_setting('VBSEO_NOFOLLOW_SHOWPOST', $p['nofollow_showpost'], 2);
vbseocp_change_setting('VBSEO_NOFOLLOW_SORT', $p['nofollow_sort'], 1);
vbseocp_change_setting('VBSEO_NOFOLLOW_DYNA', $p['nofollow_dyna'], 1);
vbseocp_change_setting('VBSEO_NOFOLLOW_EXTERNAL', $p['nofollow_ext'], 1);
vbseocp_change_setting('VBSEO_REDIRECT_PRIV_EXTERNAL', $p['redirect_ext_priv'], 1);
vbseocp_change_setting('VBSEO_DOMAINS_WHITELIST', $p['domwhitelist']);
vbseocp_change_setting('VBSEO_DOMAINS_BLACKLIST', $p['domblacklist']);
vbseocp_change_setting('VBSEO_ADD_ANALYTICS_CODE', $p['googlean'], 1);
vbseocp_change_setting('VBSEO_ANALYTICS_CODE', $p['googlean_code']);
vbseocp_change_setting('VBSEO_ADD_ANALYTICS_CODE_EXT', $p['googleanext'], 1);
vbseocp_change_setting('VBSEO_ANALYTICS_EXT_FORMAT', $p['googleanext_format']);
vbseocp_change_setting('VBSEO_ADD_ANALYTICS_GOAL', $p['googleadgoal'], 1);
vbseocp_change_setting('VBSEO_GOOGLE_AD_SEC', $p['googleadsec'], 1);
vbseocp_change_setting('VBSEO_EXT_PINGBACK', $p['pingback'], 1);
vbseocp_change_setting('VBSEO_EXT_TRACKBACK', $p['trackback'], 1);
vbseocp_change_setting('VBSEO_IN_PINGBACK', $p['inpingback'], 1);
vbseocp_change_setting('VBSEO_IN_TRACKBACK', $p['intrackback'], 1);
vbseocp_change_setting('VBSEO_IN_REFBACK', $p['inrefback'], 1);
vbseocp_change_setting('VBSEO_LINKBACK_IGNOREDUPE', $p['linkignore'], 1);
vbseocp_change_setting('VBSEO_LINKBACK_BLACKLIST', '');
vbseocp_change_setting('VBSEO_POSTBIT_PINGBACK', $p['postbitpingback'], 2);
vbseocp_change_setting('VBSEO_PERMALINK_PROFILE', $p['plink_profile'], 2);
vbseocp_change_setting('VBSEO_PERMALINK_ALBUM', $p['plink_album'], 2);
vbseocp_change_setting('VBSEO_PERMALINK_BLOG', $p['plink_blog'], 2);
vbseocp_change_setting('VBSEO_PERMALINK_GROUPS', $p['plink_groups'], 2);
vbseocp_change_setting('VBSEO_PINGBACK_NOTIFY', $p['pingback_notify'], 1);
vbseocp_change_setting('VBSEO_PINGBACK_NOTIFY_BCC', $p['pingback_notify_bcc']);
vbseocp_change_setting('VBSEO_PINGBACK_SERVICE', $p['pingback_service']);
vbseocp_change_setting('VBSEO_PINGBACK_STOPWORDS', $p['ping_stopwords']);
vbseocp_change_setting('VBSEO_NOFOLLOW_MEMBER_POSTBIT', $p['nofollow_member_postbit'], 1);
vbseocp_change_setting('VBSEO_NOFOLLOW_MEMBER_FORUMHOME', $p['nofollow_member_forumhome'], 1);
vbseocp_change_setting('VBSEO_URL_THREAD_NEXT_DIRECT', $p['next_prev_thread_direct'], 1);
vbseocp_change_setting('VBSEO_URL_THREAD_PREV_DIRECT', $p['next_prev_thread_direct'], 1);
vbseocp_change_setting('VBSEO_FORUMLINK_DIRECT', $p['forum_link_direct'], 1);
vbseocp_change_setting('VBSEO_REWRITE_META_KEYWORDS', $p['replace_meta_keywords'], 1);
vbseocp_change_setting('VBSEO_REWRITE_META_DESCRIPTION', $p['replace_meta_description'], 1);
vbseocp_change_setting('VBSEO_META_DESCRIPTION_MAX_CHARS', $p['length_meta_description'], 2);
vbseocp_change_setting('VBSEO_META_DESCRIPTION_MEMBER', $p['member_meta_description']);
vbseocp_change_setting('VBSEO_URL_PART_MAX', $p['length_url_part'], 2);
vbseocp_change_setting('VBSEO_SPACER', $p['spacer']);
vbseocp_change_setting('VBSEO_AFFILIATE_ID', $p['aff_id']);
if (isset($p['tgarsmultiformat']))
$url_formats['tgarsmultiformat'] = 'VBSEO_URL_THREAD_GARS_PAGENUM';
include_once dirname(__FILE__).'/includes/functions_vbseo_url.php';
$pre_replace = vbseo_prep_format_replacements($p['foreignchars'], $p['spacer'], vbseocp_get_setting('VBSEO_REWRITE_MEMBER_MORECHARS', 1));
$urlformats_arr = '';
foreach($url_formats as $uformat => $udefinition)
{
$ff = preg_replace('#[\[\]]#', '%', 
($p[$uformat] == 'custom') ? $p['cust_' . $uformat] : $p[$uformat]);
vbseocp_change_setting($udefinition, trim($ff));
$ff = preg_quote($ff, '#');
$ff = preg_replace(array_keys($pre_replace), $pre_replace, $ff);
$urlformats_arr[] = "'" . $udefinition . "' => '" . $ff . "'";
}
$urlformats_list = implode(",\n", $urlformats_arr);
$fcontent = preg_replace('#(\$vbseo_url_formats = array\()\s*.*?(\);)#s', '$1' . $urlformats_list . '$2', $fcontent);
vbseocp_change_setting('VBSEO_FILTER_STOPWORDS', ($p['remove_stopwords'] > 0)?1:0, 1);
vbseocp_change_setting('VBSEO_KEEP_STOPWORDS_SHORT', ($p['remove_stopwords'] == 2)?1:0, 1);
vbseocp_change_setting('VBSEO_USE_HOSTNAME_IN_URL', $p['include_domain'], 1);
vbseocp_change_setting('VBSEO_STOPWORDS', $p['stopwords']);
vbseocp_change_setting('VBSEO_IGNOREPAGES', $p['ignorepages']);
vbseocp_change_setting('VBSEO_HOMEPAGE_ALIASES', $p['hpaliases']);
vbseocp_change_setting('VBSEO_REWRITE_KEYWORDS_IN_URLS', $p['apply_replacements_in_urls'], 1);
vbseocp_change_setting('VBSEO_ACRONYMS_IN_CONTENT', $p['apply_replacements_in_cont'], 1);
vbseocp_change_setting('VBSEO_ACRONYM_SET', $p['acroset'], 2);
$fcontent = preg_replace('#(\$vbseo_relev_replace = array\s*\()\s*.*?\s*(\);)#s', '$1' . $p['relev_repl'] . '$2', $fcontent);
$fcontent = preg_replace('#(\$vbseo_relev_replace_t = array\s*\()\s*.*?\s*(\);)#s', '$1' . $p['relev_repl_t'] . '$2', $fcontent);
vbseocp_option_set_array('vbseo_images_dim', $p['images_dim']);
$fcontent = preg_replace('#(\$seo_replacements = array\()\s*.*?\s*(\);)#s', '$1' . $p['replacements'] . '$2', $fcontent);
if (VBSEO_LICENSE_CRR)
{
$fcontent = preg_replace('#(\$vbseo_custom_rules = array\()\s*.*?(\);)#s', '$1' . str_replace('$', '\$', $p['acustomrules']) . '$2', $fcontent);
$fcontent = preg_replace('#(\$vbseo_custom_rules_text = \').*?(\';)#s', '$1' . vbseocp_addslashes($p['customrules']) . '$2', $fcontent);
}
$fcontent = preg_replace('#(\$vbseo_custom_301 = array\()\s*.*?(\);)#s', '$1' . str_replace('$', '\$', $p['acustom301']) . '$2', $fcontent);
$fcontent = preg_replace('#(\$vbseo_custom_301_text = \').*?(\';)#s', '$1' . vbseocp_addslashes($p['custom301']) . '$2', $fcontent);
}
if (isset($p['saveoptions']) || isset($p['editlic']))
{
if (file_exists($vbseo_filename) && $fcontent)
{
$pf = @fopen($vbseo_filename, 'w');
@fwrite($pf, $fcontent);
@fclose($pf);
$config_imported = true;
}
}
}
include_once $vbseo_filename;
}
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
include $vbseo_funcfilename;
vbseo_get_options();
if (defined('VBSEO_CONFIG_INIT') && VBSEO_CONFIG_INIT && is_writable($vbseo_filename))
{
$db = vbseo_get_db();
if ($setcode)
$vbo['vbseo_confirmation_code'] = $vbseoo['license'] = $setcode;
$rid = $db->vbseodb_query($q = "select data from " . vbseo_tbl_prefix('datastore') . " where title = 'vbseo_options'");
$vbseostore = $db->funcs['fetch_assoc']($rid);
$vbseoo = @unserialize($vbseostore['data']);
$setm = isset($vbseoo['settings_backup']) ? $vbseoo['settings_backup'] : array();
$setm[] = array('\'', 'VBSEO_CONFIG_INIT', 0);
for($i = 0;$i < count($setm);$i++)
{
$setm[$i][2] = stripslashes($setm[$i][2]);
}
vbseo_cp_put_options($setm, array('VBSEO_REFBACK_BLACKLIST'));
$config_imported = true;
}
function vbseo_cp_get_flist($pattern)
{
global $lang_dir;
$flist = array();
$pd = @opendir($lang_dir);
while ($fn = @readdir($pd))
if (preg_match('#^' . $pattern . '$#', $fn, $fm))
$flist[] = $fm[1];
@closedir($pd);
return $flist;
}
$vbseocp_languages = vbseo_cp_get_flist('vbseocp_(.+)\.xml');
$vbseocp_presets2 = vbseo_cp_get_flist('vbseo_urls_(\d{3}.*)\.xml');
sort($vbseocp_presets2);
$vbseocp_presets = $vbseocp_presets_f = $vbseocp_presets_b = array();
$current_preset = '';
$cursetm2 = vbseo_cp_get_options(array('VBSEO_ADMIN_PASSWORD'), 'urw');
$cursetm = array();
foreach($cursetm2 as $cs2)
$cursetm[$cs2[1]] = $cs2[2];
foreach($vbseocp_presets2 as $pset)
{
$xcont = implode('', file($lang_dir . 'vbseo_urls_' . $pset . '.xml'));
preg_match_all('#<setting>.*?<name>(.*?)</name>.*?<value>(.*?)</value>#is', $xcont, $setm, PREG_SET_ORDER);
$match_forum = $match_blog = true;
$fmatches = $bmatches = 0;
foreach($setm as $cs2)
{
if(in_array($cs2[1], array('VBSEO_SPACER','VBSEO_URL_PART_MAX')))
continue;
if (($cursetm[$cs2[1]] != $cs2[2]) &&
($cursetm[$cs2[1]] || $cs2[2]) &&
isset($cursetm[$cs2[1]])
)
{
if(strstr($cs2[1],'VBSEO_URL_BLOG'))
{
$match_blog = false;
}
else
$match_forum = false;
}
if(strstr($cs2[1],'VBSEO_URL_BLOG'))
$bmatches++;
else
$fmatches++;
}
if ($match_forum && $fmatches && !$current_preset_forum)$current_preset_forum = $pset;
if ($match_blog && $bmatches && !$current_preset_blog)$current_preset_blog = $pset;
preg_match('#<settings title="(.*?)">#i', $xcont, $pm);
$vbseocp_presets[$pset] = $pm[1];
if($bmatches) $vbseocp_presets_b[$pset] = $pm[1];
if($fmatches) $vbseocp_presets_f[$pset] = $pm[1];
}
if (count($vbseocp_languages) == 0)
$vbseocp_languages[] = 'english';
$lang_file = $lang_dir . 'vbseocp_' . VBSEO_CP_LANGUAGE . '.xml';
$lcont = implode('', file($lang_file));
preg_match_all('#<message>.*?<name>(.*?)</name>.*?<value>(.*?)</value>#is', $lcont, $langm, PREG_SET_ORDER);
$alang = array();
foreach($langm as $kl)
$alang[$kl[1]] = vbseocp_unhtmlentities($kl[2]);
function uformat_prep($format)
{
$ind = 0;
$format = preg_replace('#%#e', '(($ind++)%2?"]":"[")', $format);
return $format;
}
$jumpto = $_POST['jumpto'] ? $_POST['jumpto'] : $_GET['jumpto'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html dir="<?php echo $alang['lang_dir']?$alang['lang_dir']:'ltr';
?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $alang['htmlcharset']?$alang['htmlcharset']:'iso-8859-1';
?>">
<title>vBSEO v.<?php echo VBSEO_VERSION2_MORE;
?></title>
<style type="text/css">
<!--
body {
font-size: 11px; margin: 10px; font-family: verdana, arial, helvetica, sans-serif; height:100%
}
table {
font-size: 11px; font-family: verdana, arial, helvetica, sans-serif
}
#top {
background: #eaeaea; color: #6f7877; padding:10px
}
#nav {
padding:20px;
}
.xmltable {
border:1px solid #000000;
background-color: #f2f2f2;
margin-bottom: 20px
}
input {
font-size: 11px; font-family: verdana, arial, helvetica, sans-serif;
direction: ltr;
}
textarea {
font-size: 11px; font-family: verdana, arial, helvetica, sans-serif
}
select {
font-size: 11px; font-family: verdana, arial, helvetica, sans-serif
}
a:link {
color: #003399; text-decoration: underline
}
a:visited {
color: #003399; text-decoration: underline
}
a:active {
color: #003399; text-decoration: underline
}
a:hover {
color: #003399; text-decoration: none
}
.formtbl {
background-color: #000000
}
table.area {
background-color: #000000
}
tr.area {
background-color: #ffffff;
}
.altfirst td {
background-color: #ffffff; padding:6px; width:50%;
}
.altsecond td {
background-color: #f2f2f2; padding:6px; width:50%;
}
.header td {
color: #ffffff; background-color: #465786; font-weight:bold;
}
.altnew {
background-color: #ffffcc; padding:6px;
}
.altfeature {
background-color: #ffcc99; padding:6px;
}
.subheader,.subheader td {
color: #ffffff; background-color: #6e7a9a;
padding: 4px;
}
ul {
margin-top:5px; margin-bottom:5px
}
#jump {
margin:5px;
margin-right:25px;
}
h1 {
font-size: 140%; margin:5px
}
h2 {
font-size: 110%; margin:5px
}
#forumlink {
float:<?php echo ($alang['lang_dir'] == 'rtl')?'left':'right';
?>;
}
#forumlink a {
color: #ffff66;
}
#button {
height:20px;
border:solid 1px #000000
}
.new {  font-size: 9px;
color: #ffffff;
background-color: #ff0000;
font-family: verdana, arial, helvetica, sans-serif;
padding: 1px;
}
.beta { font-size: 9px;
color: #ffffff;
background-color: #009933;
font-family: verdana, arial, helvetica, sans-serif;
padding: 1px;
}
.updated {  font-size: 9px;
color: #ffffff;
background-color: #6666cc;
font-family: verdana, arial, helvetica, sans-serif;
padding: 1px;
}
-->
</style>
</head>
<body>
<table cellpadding="3" style="width:100%; BACKGROUND: #eaeaea; COLOR: #6f7877; FONT-SIZE: 11px; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;">
<tr>
<td align="left"><strong>[<i><?php echo $alang['patent_pending'];
?></i>] vBSEO <?php echo VBSEO_VERSION2_MORE;
?> - <?php echo $alang['adm_cp'];
?></strong></td>
<td align="right"><?php
$s = '<a style="COLOR: #003399; TEXT-DECORATION: underline;" href="index.' . VBSEO_VB_EXT . '">' . $alang['back_to'] . '</a>';
make_crawlable($s);
echo $s;
?>
<?php if ($logged_in)
{
?>
&nbsp;| <a style="COLOR: #003399; TEXT-DECORATION: underline;" href="vbseocp.php?logout=true"><?php echo $alang['logout'];
?></a>
<?php }
?>
</td>
</tr>
</table>
<div align="right" style="padding:5px">
vBSEO Links:
<strong><a href="http://www.vbseo.com/" target="_blank"><?php echo $alang['forums'];
?></a></strong> -
<strong><a href="http://www.vbseo.com/tickets/" target="_blank"><?php echo $alang['supportsystem'];
?></a></strong> -
<strong><a href="http://www.vbseo.com/aff/" target="_blank"><?php echo $alang['affiliates'];
?></a></strong> -
<strong><a href="http://www.vbseo.com/contactus.html" target="_blank"><?php echo $alang['contact'];
?></a></strong>
</div>
<TABLE cellSpacing=0 cellPadding=0 height=90% width=100% align=center border=0>
<TBODY>
<TR>
<TD>
<?php if ($logged_out)
{
?>
<br /><br />
<p align="center"><strong><font color="#FF0000"><?php echo $alang['cookies_cleared'];
?></font></strong><br />
<a href="vbseocp.php"><?php echo $alang['click_redirect'];
?></a>
<script language="Javascript">setTimeout('document.location="vbseocp.php"',3000)</script>
</div>
<?php }
else
if (VBSEO_ADMIN_PASSWORD == '' && !$logged_in)
{
echo '<center>';
if ($fail_setpass)
{
echo '<font color="#ff0000">' . $alang['error'] . ': ' . $alang[$fail_setpass] . '</font><br />';
}
?>
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="100% "height="100%" align="center">
<br />
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><form action="vbseocp.php" method=POST>
<table border="0" cellspacing="0" cellpadding="4">
<tr bgcolor="#E0E0E0">
<td colspan="2"><strong><?php echo $alang['pass_select'];
?></strong></td>
</tr>
<tr align="left">
<td width="100%" colspan="2" bgcolor="f2f2f2"><table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr>
<td><?php echo $alang['password'];
?>: </td>
<td><input type=password name=password size=15></td>
</tr>
<tr>
<td><?php echo $alang['confirm'];
?>:</td>
<td><input type=password name=password2 size=15>
<input type="hidden" name="setpass" value="1">
<input type="submit" name="submit" value="<?php echo $alang['login'];
?>"></td>
</tr>
</table>                  </td>
</tr>
</table>
</form></td>
</tr>
</table>
<?php if (!is_writable($vbseo_filename))
{
?>
<br />
<font color="#FF0000"><?php echo $alang['note_config'];
?></font><br /></td>
<?php }
?>
</tr>
</table>
<?php }
else
if (!$logged_in)
{
echo '<center>';
if ($fail_login)
{
echo '<font color="#ff0000">' . $alang['login_failed'] . '</font><br />';
}
?>
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="100% "height="100%" align="center">
<br />
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><form action="vbseocp.php" method=POST name="loginform">
<table border="0" cellspacing="0" cellpadding="4">
<tr>
<td><strong><?php echo $alang['area_protected'];
?></strong></td>
</tr>
<tr align="left">
<td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
<tr>
<td><?php echo $alang['password'];
?>:</td>
<td><input type=password name=password size=15>
<input type="hidden" name="login" value="1">
<input type="submit" name="submit" value="Login"></td>
</tr>
</table></td>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
document.forms['loginform'].password.focus();
</script>
</tr>
</table>
</form></td>
</tr>
</table>
<br /></td>
</tr>
</table>
<?php
}
else if (!is_writable($vbseo_filename) && (isset($success_login) || isset($config_imported)))
{
?>
<p align="center"><strong><font color="#FF0000"><?php echo $alang['warn_readonly'];
?></font></strong><br />
<a href="vbseocp.php<?php echo $jumpto?"#$jumpto":""?>"><?php echo $alang['click_redirect'];
?></a>
<script language="Javascript">setTimeout('document.location="vbseocp.php<?php echo $jumpto?"#$jumpto":"";
?>"',1000)</script>
<?php
}
else if (isset($config_imported))
{
$db = vbseo_get_db();
$setm = vbseo_cp_get_options(array('VBSEO_ADMIN_PASSWORD'));
vbseo_extra_inc('linkback');
if(isset($p['linkback_black']))
{
vbseo_linkback_unbandomain('', 1);
$blackdoms = preg_split('#[\r\n]+#', trim($p['linkback_black']));
foreach($blackdoms as $bdom)
vbseo_linkback_bandomain($bdom, 1);
}
$vlink = $db->vbseodb_query_first("SELECT COUNT(*) as cnt FROM " . vbseo_tbl_prefix('thread') . " WHERE vbseo_linkbacks_no>0");
if($vlink['cnt'] == 0)
vbseo_linkback_recalc();
$vbo = vbseo_get_datastore('options');
$vbseoo = vbseo_get_datastore('vbseo_options');
$setcode = isset($p['editlic']) ? VBSEO_LICENSE_CODE : $vboptions['vbseo_confirmation_code'];
$vbseoo['settings_backup'] = $setm;
$vbo['vbseo_opt'] = array();
vbseo_set_datastore('vbseo_options', $vbseoo);
vbseo_set_datastore('options', $vbo);
$rid = $db->vbseodb_query("SHOW COLUMNS FROM " . vbseo_tbl_prefix('plugin') . " LIKE 'executionorder'");
$excolumn = $db->funcs['fetch_assoc']($rid);
if($excolumn)
$db->vbseodb_query($q="UPDATE " . vbseo_tbl_prefix('plugin') . "
SET executionorder = 15
WHERE product = 'crawlability_vbseo' AND hookname = 'global_complete' AND executionorder = 5");
vbseo_cache_start();
$vbseo_cache->cachereset();
?>
<p align="center"><strong><font color="#FF0000"><?php echo $alang['saved_ok'];
?></font></strong><br />
<a href="vbseocp.php<?php echo $jumpto?"#$jumpto":"";
?>"><?php echo $alang['click_redirect'];
?></a>
<script language="Javascript">setTimeout('document.location="vbseocp.php<?php echo $jumpto?"#$jumpto":""?>"',1000)</script>
<?php
}
else
{
?>
<table id="nav" width="100%" "border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
<td valign="middle">
<br />
 
<div> <?php echo $alang['quick_jump'];
?>
: </div> <div id = "jump"> <strong> <a href = "#general"> <?php echo $alang['gen_set'];
?></a></strong></div>
<ul>
<li><a href="#general_vbseo"><?php echo $alang['vbseo_opt']?></a></li>
<li><a href="#general_log"><?php echo $alang['log_opt']?></a></li>
<li><a href="#general_arc"><?php echo $alang['arc_opt']?></a></li>
<li><a href="#general_cache"><?php echo $alang['cache_opt']?></a></li>
</ul>
<div id="jump"><strong><a href="#url"><?php echo $alang['url_opt']?></a></strong></div>
<ul>
<li><a href="#url_gen"><?php echo $alang['url_gen']?></a></li>
<li><a href="#url_forum"><?php echo $alang['url_forum']?></a></li>
<li><a href="#url_blog"><?php echo $alang['url_blog']?></a></li>
</ul>
<div id="jump"><strong><a href="#linkbacks"><?php echo $alang['linkbacks']?></a></strong></div>
<div id="jump"><strong><a href="#permalinks"><?php echo $alang['permalinks']?></a></strong></div>
<div id="jump"><strong><a href="#custom"><?php echo $alang['crrs']?></a></strong></div>
<div id="jump"><strong><a href="#custom301"><?php echo $alang['custom301']?></a></strong></div>
<div id="jump"><strong><a href="#seo_relevant"><?php echo $alang['rel_replacements']?></a></strong></div>
<div id="jump"><strong><a href="#seo"><?php echo $alang['seo_func']?></a></strong></div>
<ul>
<li><a href="#seo_meta"><?php echo $alang['dyn_metas']?></a></li>
<li><a href="#seo_npdirect"><?php echo $alang['dir_links']?></a></li>
<li><a href="#seo_addtitles"><?php echo $alang['add_ttitle']?></a></li>
<li><a href="#seo_addexttitles"><?php echo $alang['add_extttitle']?></a></li>
<li><a href="#seo_stopword"><?php echo $alang['stopwords']?></a></li>
<li><a href="#seo_acronym"><?php echo $alang['acronym']?></a></li>
<li><a href="#seo_homepage"><?php echo $alang['hp_set']?></a></li>
<li><a href="#seo_intrelnofollow"><?php echo $alang['int_rel']?></a></li>
<li><a href="#seo_extrelnofollow"><?php echo $alang['ext_rel']?></a></li>
</ul>
<div id="jump"><strong><a href="#other"><?php echo $alang['oth_enh']?></a></strong></div>
<ul>
<li><a href="#other_cleanup"><?php echo $alang['cleanup_html']?></a></li>
<li><a href="#other_arcfooter"><?php echo $alang['footer_arc']?></a></li>
<li><a href="#other_catlinks"><?php echo $alang['catlinks']?></a></li>
<li><a href="#other_guests"><?php echo $alang['guests_only']?></a></li>
<li><a href="#other_bookmark"><?php echo $alang['bookmark']?></a></li>
<li><a href="#other_image"><?php echo $alang['img_size']?></a></li>
</ul>
 
</td>
<td align="center" valign="middle">
 
<FORM method = post action = "vbseocp.php?go=true"> <table width = "520px" border = "0" cellpadding = "0" cellspacing = "0" class = xmltable>
<tr> <td bgcolor = "#E0E0E0" style = "border-bottom: 1px solid #000000" >
<table border = "0" cellspacing = "0" cellpadding = "6"> <tr> <td> <?php echo $alang['license_desc'];
?></td> </tr> </table> </td> </tr> <tr> <td> <table width = "100%" border = "0" cellspacing = "0" cellpadding = "6" > <tr align = "left"> <td>
<?php echo $alang['key_config'];
?>
:
<br />
<input type="text" size="45" name="license_code" id="license_code" value="<?php echo VBSEO_LICENSE_CODE?>">
<INPUT id="button" type=submit value="<?php echo $alang['save_key']?>" name=editlic>
<br /><br /><?php echo $alang['current_ver']?>: <b>vBSEO <?php echo VBSEO_VERSION2_MORE?></b><?php echo defined('VBSEO_LICENSE_STR') ? (VBSEO_LICENSE_TYPE==1 ? '' : ' '.VBSEO_LICENSE_STR) : ', Unreg'?>
<br /><?php echo $alang['forums_root']?>: <b><?php echo $vboptions['bburl']?></b>
<br /><?php echo $alang['current_key']?>: <?php
$liccode = $vboptions['vbseo_confirmation_code'];
$iskey = $liccode && !preg_match('#^vresp(.*)#',$vboptions['vbseo_confirmation_code'],$unregpm);
if ($liccode && ($liccode == VBSEO_LICENSE_CODE))
echo '<b style="color:green">' . $liccode . '</b>';
else
echo '<b style="color:red">' . 
($iskey ? $liccode:
($unregpm[1]?$unregpm[1].', ':'').'Open a <a href="http://www.vbseo.com/support/">support ticket</a> or contact <a style="color:red" href="mailto:licenses@crawlability.com">licenses@crawlability.com</a>') . '</b>';
?></TD>
</tr>
</table></td>
</tr>
</table>
</FORM>
 
<table width="520px" border="0" cellpadding="0" cellspacing="0" class=xmltable>
<tr>
<td bgcolor="#E0E0E0" style="border-bottom: 1px solid #000000"><table border="0" cellspacing="0" cellpadding="6">
<tr>
<td><strong><?php echo $alang['dl_backup']?></strong></td>
</tr>
</table></td>
</tr>
<tr>
<td style="border-bottom: 1px solid #000000"><table width="100%" border="0" cellpadding="6" cellspacing="1" bgcolor="#FFFFFF">
<tr bgcolor="#F2F2F2">
<td><strong><?php echo $alang['download']?></strong></td>
<td><strong><?php echo $alang['filename']?></strong></td>
<td align="left"><strong><?php echo $alang['description']?></strong> </td>
</tr>
<tr>
<td align="center" bgcolor="#f2f2f2"><a href="vbseocp.php?getsettings=true&get=all">XML</a></td>
<td align="left" bgcolor="#f2f2f2">vbseo_all.xml</td>
<td align="left" bgcolor="#f2f2f2"><?php echo $alang['backup_all']?>
</td>
</tr>
 
<tr>
<td align="center" bgcolor="#f2f2f2"><a href="vbseocp.php?getsettings=true&get=urw">XML</a></td>
<td align="left" bgcolor="#f2f2f2">vbseo_urls.xml</td>
<td bgcolor="#f2f2f2" align="left"><?php echo $alang['backup_urls']?>
</td>
</tr>
 
</table></td>
</tr>
<tr>
<td bgcolor="#E0E0E0" style="border-bottom: 1px solid #000000"><table border="0" cellspacing="0" cellpadding="6">
<tr>
<td><strong><?php echo $alang['upload_set']?></strong></td>
</tr>
</table></td>
</tr>
<form method=post action="vbseocp.php" enctype="multipart/form-data" onSubmit="
el=this.elements['file'];
if(el.value.indexOf('.xml')<0)
{
alert('<?php echo $alang['choose_file']?>');
el.focus();
return false;
}">
<input type="hidden" name="putsettings" value="1">
<tr>
<td><table  border="0" cellspacing="0" cellpadding="6">
<tr align="left">
<td colspan="2"><?php echo $alang['note_upcrr']?></td>
</tr>
<tr align="left">
<td align="right"><?php echo $alang['upload']?>:</td>
<td><input name="file" type="file" id="button" size="34">
<input id="button" type="submit" name="Submit" value="<?php echo $alang['import_set']?>"></td>
</tr>
</table></td>
</tr>
</form>
</table></td>
</TR>
</table></td>
</tr>
</table>
<?php
include 'vbseocpform.php';
}
?>
<TABLE height=10 cellSpacing=0 cellPadding=0 width=500 align=center
border=0>
<TBODY>
<TR>
<TD></TD></TR></TBODY></TABLE>
<?php
if (!isset($config_imported) && ((VBSEO_ADMIN_PASSWORD != '') || !$logged_in))
{
?>
<TABLE cellSpacing=0 cellPadding=1 align=center border=0>
<TBODY>
<TR>
<TD align=middle>[<i><?php echo $alang['patent_pending'];
?></i>] vBSEO <?php echo VBSEO_VERSION2_MORE;
?> is &copy; 2005-2008 <a href="http://www.crawlability.com" target="_blank">Crawlability, Inc.</a> <?php echo $alang['rights_reserved']?>.</TD>
</TR></TBODY></TABLE>
<?php }
?>
</td></TR></TBODY></TABLE>
</body>
</html>
 