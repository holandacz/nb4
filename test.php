<?php
/*  Test file to see if zend or ionndube will work on your server  activecampaign.com / sales@activecampaign.com  */
error_reporting(0);


function zn_system_info() {
    ob_start();

    phpinfo();
    $info = ob_get_contents();
    ob_end_clean();
    if (strpos($info, 'Zend Optimizer')){
        if (strpos($info, 'Zend Optimizer v2.1')){
            return "old";
        }
        else{
            return "true";
        }
    }
}
function dl_system_info()  {
    ob_start();
    phpinfo(INFO_GENERAL);
    $info = ob_get_contents();
    ob_end_clean();
    return strpos($info, 'Zend Optimizer');
}
function ic_system_info() {
    $thread_safe = false;
    $debug_build = false;
    $cgi_cli = false;
    $php_ini_path = '';
    ob_start();
    phpinfo(INFO_GENERAL);
    $php_info = ob_get_contents();
    ob_end_clean();
    foreach (split("\n",$php_info) as $line) {
        if (eregi('command',$line)) {
            continue;
        }
        if (eregi('thread safety.*(enabled|yes)',$line)) {
            $thread_safe = true;
        }
        if (eregi('debug.*(enabled|yes)',$line)) {
            $debug_build = true;
        }
        if (eregi("configuration file.*(</B></td><TD ALIGN=\"left\">| => |v\">)([^ <]*)(.*</td.*)?",$line,$match)) {
            $php_ini_path = $match[2];
            if (!@file_exists($php_ini_path)) {
                $php_ini_path = '';
            }
        }
        $cgi_cli = ((strpos(php_sapi_name(),'cgi') !== false) || 		(strpos(php_sapi_name(),'cli') !== false));
    }
    return array(
        'THREAD_SAFE' => $thread_safe,
        'DEBUG_BUILD' => $debug_build, 	       
        'PHP_INI'     => $php_ini_path, 	       
        'CGI_CLI'     => $cgi_cli
    );
}
$nl =  ((php_sapi_name() == 'cli') ? "\n" : '<br>');
$ok = true;
$already_installed = false;
$here = dirname(__FILE__);
if (extension_loaded('ionCube Loader')) {
    $already_installed = true;
    $ion_good = "yes";
}
$sys_info = ic_system_info();
if (!$already_installed) {
    if ($sys_info['THREAD_SAFE'] && !$sys_info['CGI_CLI']) {
        $ion_error = $ion_error."Your PHP install appears to have threading support and run-time Loading is only possible on threaded web servers if using the CGI, FastCGI or CLI interface.$nl${nl}
            To run encoded files please install the Loader in the php.ini file.$nl";
            $ok = false;
    }
    if ($sys_info['DEBUG_BUILD']) {
        $ion_error = $ion_error."Your PHP installation appears to be built with debugging support enabled and this is incompatible with ionCube Loaders.$nl${nl}
        Debugging support in PHP produces slower execution, is not recommended for production builds and was probably a mistake.${nl}${nl}
        You should rebuild PHP without the --enable-debug option and if you obtained your PHP install from an RPM then the producer of the RPM should be notified so that it can be corrected.$nl";
        $ok = false;
    }
    if (ini_get('safe_mode')) {
        $ion_error = $ion_error."PHP safe mode is enabled and run time loading will not be possible.$nl";
        $ok = false;
    }
    elseif (!is_dir(realpath(ini_get('extension_dir')))) {
        $ion_error = $ion_error."The setting of extension_dir in the php.ini file is not a directory or may not exist and run time loading will not be possible. You do not need write permissions on the extension_dir but for run-time loading to work a path from the extensions directory to wherever the Loader is installed must exist.$nl";
        $ok = false;
    }
    if ($ok) {
        $test_old_name = false;
        $_u = php_uname();
        $_os = substr($_u,0,strpos($_u,' '));
        $_os_key = strtolower(substr($_u,0,3));
        $_php_version = phpversion();
        $_php_family = substr($_php_version,0,3);
        $_loader_sfix = (($_os_key == 'win') ? '.dll' : '.so');
        $_ln_old="ioncube_loader.$_loader_sfix";
        $_ln_old_loc="/ioncube/$_ln_old";
        $_ln_new="ioncube_loader_${_os_key}_${_php_family}${_loader_sfix}";
        $_ln_new_loc="/ioncube/$_ln_new";
        $_oid = $_id = realpath(ini_get('extension_dir'));
        $_here = dirname(__FILE__);
        if ((@$_id[1]) == ':') {
            $_id = str_replace('\\','/',substr($_id,2));
            $_here = substr($_here,2);
        }
        $_rd=str_repeat('/..',substr_count($_id,'/')).$_here.'/';
        $_rd = str_replace('\\','/',$_rd);
        $_ln = '';
        $_i=strlen($_rd);
        while($_i--) {
            if($_rd[$_i]=='/') {
                if ($test_old_name) {
                    $_lp=substr($_rd,0,$_i).$_ln_old_loc;
                    $_fqlp=$_oid.$_lp;
                    $_fqlp = str_replace('\\','/',$_fqlp);
                    $nl = str_replace('\\','/',$nl);
                    if(@file_exists($_fqlp)) {
                        $_ln=$_lp;
                        break;
                    }
                }
                $_lp=substr($_rd,0,$_i).$_ln_new_loc;
                $_fqlp=$_oid.$_lp;
                if(@file_exists($_fqlp)) {
                    $_fqlp = str_replace('\\','/',$_fqlp);
                    $_ln=$_lp;
                    break;
                }
            }
        }
        if (!$_ln) {
            if ($test_old_name) {
                if (@file_exists($_id.$_ln_old_loc)) {
                    $_ln = $_ln_old_loc;
                }
            }
            if (@file_exists($_id.$_ln_new_loc)) {
                $_ln = $_ln_new_loc;
            }
        }
        if ($_ln) {
            @dl($_ln);
            if(extension_loaded('ionCube Loader')) {
                $ion_good = "yes";
            }
            else {
                $ion_error = $ion_error."The Loader was not installed.  Check with your host or system administrator that enable_dl in your php configuration is turned on and that safe_mode is turned off.";
            }
        }
        else {
            $ion_good = "yes";
        }
    }
}
?> <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> <html> <head> <title>Test File</title> <meta http-equiv="Content-Type" content="text/html;
charset=iso-8859-1"> </head> <body bgcolor="#FFFFFF" text="#000000" link="#003399" vlink="#003399" alink="#003399"> <table width="100%" border="0" cellspacing="0" cellpadding="0"> 	<tr> 	<td><a href="http://www.activecampagin.com"><img src="http://www.activecampaign.com/images/logo.gif" border="0"></a></td> 	<td align="right"><font color="#999999" size="4" face="Arial, Helvetica, sans-serif">Requirement Testing File</font></td> 	</tr> </table><br> <table width="100%" border="0" cellspacing="0" cellpadding="7">   <tr bgcolor="#004F9D">      <td colspan="4"><font color="#FFFFFF" size="4" face="Arial, Helvetica, sans-serif"><strong>Testing        Server...</strong></font></td>   </tr>   <tr>      <td width="50" height="65"> <font size="2" face="Arial, Helvetica, sans-serif">  	  <?PHP  if (zn_system_info() == "old"){
    print "<img src=\"http://server1.activecampaign.com/check/icon_fail.gif\" width=\"45\" height=\"45\">";
}
elseif(zn_system_info() == "") {
    print "<img src=\"http://server1.activecampaign.com/check/icon_fail.gif\" width=\"45\" height=\"45\">";
}
else {
    print "<img src=\"http://server1.activecampaign.com/check/icon_pass.gif\" width=\"45\" height=\"45\">";
}
?>       </font></td>     <td width="110" height="65"><font size="2" face="Arial, Helvetica, sans-serif">Zend        Optimizer</font></td>     <td width="100" height="65"> <font size="2" face="Arial, Helvetica, sans-serif">        <?PHP  if (zn_system_info() == "old"){
    print "No";
}
elseif(zn_system_info() == "") {
    print "No";
}
else {
    print "Yes";
}
?>       </font></td>     <td height="65"><font size="2" face="Arial, Helvetica, sans-serif">         <?PHP  if (zn_system_info() == "old"){
    print "It appears you are running an older version of zend optimizer.  In order this software to work correctly your server will need zend optimizer 2.5+.";
}
elseif(zn_system_info() == "") {
    print "<a href=\"http://zend.com/store/free_download.php?pid=13\">Free Download/Install Instructions</a>   |   <a href=\"http://zend.com/store/products/optimizer-faq.php\">FAQ</a>";
}
else {
}
?> </font> </td>   </tr>   <tr>      <td width="50" height="65"> <font size="2" face="Arial, Helvetica, sans-serif">        <?PHP if ($ion_good == "yes"){
    $install = "yes";
    print "<img src=\"http://server1.activecampaign.com/check/icon_pass.gif\" width=\"45\" height=\"45\">";
}
else{
    print "<img src=\"http://server1.activecampaign.com/check/icon_fail.gif\" width=\"45\" height=\"45\">";
}
?>       </font></td>     <td width="110" height="65"><font size="2" face="Arial, Helvetica, sans-serif">Ioncube</font></td>     <td width="100" height="65"> <font size="2" face="Arial, Helvetica, sans-serif">        <?PHP if ($ion_good == "yes"){
    print "Yes.  Requires the ".$_ln_new." loader.";
}
else{
    print "No";
}
?>       </font></td>     <td height="65"><font color="#333333" size="1" face="Arial, Helvetica, sans-serif"><?PHP print $ion_error;
?>        </font></td>   </tr> </table> <?PHP if ($install == "yes"){
    ?> <br> <table width="100%" border="0" cellspacing="1" cellpadding="7">   <tr bgcolor="#004F9D">      <td colspan="2"><font color="#FFFFFF" size="4" face="Arial, Helvetica, sans-serif"><strong>Congratulations!</strong></font></td>   </tr>   <tr>      <td width="50" height="65"> <font size="2" face="Arial, Helvetica, sans-serif">        <img src="http://server1.activecampaign.com/check/icon_pass.gif" width="45" height="45"> </font></td>     <td height="65"><font size="2" face="Arial, Helvetica, sans-serif"><strong>The software        should install on your server with no server changes needed.<br>       <font color="#666666">You should download the        <font color="#FF0000"> <?PHP if (zn_system_info()){
        print "Zend Optimizer";
    }
else{
    print "Ioncube";
}
?> &nbsp;
version </font>of the software to install.</font></strong></font></td>   </tr> </table> <?PHP }
else{
    ?> <table width="100%" border="0" cellspacing="1" cellpadding="7">   <tr bgcolor="#990000">      <td colspan="2"><font color="#FFFFFF" size="4" face="Arial, Helvetica, sans-serif"><strong>Server        Changes Possibly Needed</strong></font></td>   </tr>   <tr>      <td width="50" height="65"> <font size="2" face="Arial, Helvetica, sans-serif">        <img src="http://server1.activecampaign.com/check/icon_fail.gif" width="45" height="45"> </font></td>     <td height="65"><font size="2" face="Arial, Helvetica, sans-serif">This test        script was unable to find Zend Optimizer on your server and was unable to        successfully run Ioncube. Suggestions and install instructions are to the        right of both Zend Optimizer and Ioncube above.</font></td>   </tr> </table> <p>   <?PHP }
?> </p> <p>&nbsp;
</p> <p>&nbsp;
</p> <hr align="center" width="100%" size="1" noshade> <p align="center"><font size="2" face="Arial, Helvetica, sans-serif">Have any questions? Contact us at <a href="mailto:sales@activecampaign.com">sales@activecampaign.com</a></font><font face="Arial, Helvetica, sans-serif"></font> </p> <hr align="center" width="100%" size="1" noshade> <div align="center"><font size="1">   <?PHP if (!$_GET["info"]){
    ?>   <font face="Arial, Helvetica, sans-serif"><a href="<?PHP print $PHP_SELF;
    ?>?info=1">View      detailed PHP Info</a></font></font> <font size="1">   <?PHP }
else{
    echo '<div>';
    phpinfo();
    echo '</div>';
}
?>   </font> </div> </body> </html>
