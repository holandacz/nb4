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
$VERSIONS['colors.php'] = "3.0";
$adminprocess = true;
include('../incs/functions.php');
require('lang/admin_'.$CONFIG['admin_language'].'.php');

ignore_user_abort(true); // Wichtig wegen Verbindungsabbruch
@set_time_limit(6000);

#---------------------------------------------------------------------------------------------------------

session_start();
if($_SESSION['pass'] != md5($CONFIG['adminpass']) || empty($CONFIG['adminpass'])) {
    header("Location: index.php?op=config");
    exit;
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Color</title>
<style type="text/css">
<!--


.hexfield {
font-size:10pt;
font-family:verdana, arial, helvetica;
font-weight:bold;
color:#808080;
border-style:solid;
border-color:#000000;
border-width:1px;
background-color:#FFFFFF;
}


.NArial   {font-family: arial; font-size: 10pt}
.NArialL  {font-family: arial; font-size: 12pt}
.NArialS  {font-family: arial; font-size: 8pt}
.NArialW  {COLOR: #FFFFFF; font-family: arial; font-size: 10pt}
.NArialWL  {COLOR: #FFFFFF; font-family: arial; font-size: 12pt}

-->
</style>
<script language = "javascript">
<!--

function shouldset(passon){
if(document.areas.hexvalue.value.length == 7){co(passon)}
}
function co(elem){
document.areas.hexvalue.value=elem
     document.areas.selcolor.style.backgroundColor=elem
}

//-->
</script>
</head>

<body bgcolor="#DDDDDD">


<form name="areas">


<table border="0" width="100" bgcolor="#000000" cellspacing="0" cellpadding="0">
<tr><td width="100%">


<table cellSpacing="1" cellPadding="0" width="100" border="0">
<tr>
<td bgColor="#00FF00"><a href="javascript:co('#00FF00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00FF33"><a href="javascript:co('#00FF33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00FF66"><a href="javascript:co('#00FF66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00FF99"><a href="javascript:co('#00FF99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00FFCC"><a href="javascript:co('#00FFCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00FFFF"><a href="javascript:co('#00FFFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00CC00"><a href="javascript:co('#00CC00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00CC66"><a href="javascript:co('#00CC66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00CC66"><a href="javascript:co('#00CC66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00CC99"><a href="javascript:co('#00CC99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00CCCC"><a href="javascript:co('#00CCCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#00CCFF"><a href="javascript:co('#00CCFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#009900"><a href="javascript:co('#009900')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#009933"><a href="javascript:co('#009933')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#009966"><a href="javascript:co('#009966')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#009999"><a href="javascript:co('#009999')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#0099CC"><a href="javascript:co('#0099CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#0099FF"><a href="javascript:co('#0099FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#33FF00"><a href="javascript:co('#33FF00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33FF33"><a href="javascript:co('#33FF33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33FF66"><a href="javascript:co('#33FF66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33FF99"><a href="javascript:co('#33FF99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33FFCC"><a href="javascript:co('#33FFCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33FFFF"><a href="javascript:co('#33FFFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33CC00"><a href="javascript:co('#33CC00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33CC33"><a href="javascript:co('#33CC33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33CC66"><a href="javascript:co('#33CC66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33CC99"><a href="javascript:co('#33CC99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33CCCC"><a href="javascript:co('#33CCCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#33CCFF"><a href="javascript:co('#33CCFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#339900"><a href="javascript:co('#339900')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#339933"><a href="javascript:co('#339933')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#339966"><a href="javascript:co('#339966')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#339999"><a href="javascript:co('#339999')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#3399CC"><a href="javascript:co('#3399CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#3399FF"><a href="javascript:co('#3399FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#66FF00"><a href="javascript:co('#66FF00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66FF33"><a href="javascript:co('#66FF33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66FF66"><a href="javascript:co('#66FF66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66FF99"><a href="javascript:co('#66FF99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66FFCC"><a href="javascript:co('#66FFCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66FFFF"><a href="javascript:co('#66FFFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66CC00"><a href="javascript:co('#66CC00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66CC33"><a href="javascript:co('#66CC33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66CC66"><a href="javascript:co('#66CC66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66CC99"><a href="javascript:co('#66CC99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66CCCC"><a href="javascript:co('#66CCCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#66CCFF"><a href="javascript:co('#66CCFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#669900"><a href="javascript:co('#669900')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#669933"><a href="javascript:co('#669933')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#669966"><a href="javascript:co('#669966')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#669999"><a href="javascript:co('#669999')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#6699CC"><a href="javascript:co('#6699CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#6699FF"><a href="javascript:co('#6699FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#99FF00"><a href="javascript:co('#99FF00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99FF33"><a href="javascript:co('#99FF33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99FF66"><a href="javascript:co('#99FF66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99FF99"><a href="javascript:co('#99FF99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99FFCC"><a href="javascript:co('#99FFCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99FFFF"><a href="javascript:co('#99FFFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99CC00"><a href="javascript:co('#99CC00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99CC33"><a href="javascript:co('#99CC33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99CC66"><a href="javascript:co('#99CC66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99CC99"><a href="javascript:co('#99CC99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99CCCC"><a href="javascript:co('#99CCCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#99CCFF"><a href="javascript:co('#99CCFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#999900"><a href="javascript:co('#999900')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#999933"><a href="javascript:co('#999933')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#999966"><a href="javascript:co('#999966')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#999999"><a href="javascript:co('#999999')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#9999CC"><a href="javascript:co('#9999CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#9999FF"><a href="javascript:co('#9999FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#CCFF00"><a href="javascript:co('#CCFF00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCFF33"><a href="javascript:co('#CCFF33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCFF66"><a href="javascript:co('#CCFF66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCFF99"><a href="javascript:co('#CCFF99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCFFCC"><a href="javascript:co('#CCFFCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCFFFF"><a href="javascript:co('#CCFFFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCCC00"><a href="javascript:co('#CCCC00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCCC33"><a href="javascript:co('#CCCC33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCCC66"><a href="javascript:co('#CCCC66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCCC99"><a href="javascript:co('#CCCC99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCCCCC"><a href="javascript:co('#CCCCCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CCCCFF"><a href="javascript:co('#CCCCFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC9900"><a href="javascript:co('#CC9900')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC9933"><a href="javascript:co('#CC9933')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC9966"><a href="javascript:co('#CC9966')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC9999"><a href="javascript:co('#CC9999')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC99CC"><a href="javascript:co('#CC99CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC99FF"><a href="javascript:co('#CC99FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#FFFF00"><a href="javascript:co('#FFFF00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFFF33"><a href="javascript:co('#FFFF33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFFF66"><a href="javascript:co('#FFFF66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFFF99"><a href="javascript:co('#FFFF99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFFFCC"><a href="javascript:co('#FFFFCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFFFFF"><a href="javascript:co('#FFFFFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFCC00"><a href="javascript:co('#FFCC00')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFCC33"><a href="javascript:co('#FFCC33')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFCC66"><a href="javascript:co('#FFCC66')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFCC99"><a href="javascript:co('#FFCC99')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFCCCC"><a href="javascript:co('#FFCCCC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FFCCFF"><a href="javascript:co('#FFCCFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF9900"><a href="javascript:co('#FF9900')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF9933"><a href="javascript:co('#FF9933')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF9966"><a href="javascript:co('#FF9966')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF9999"><a href="javascript:co('#FF9999')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF99CC"><a href="javascript:co('#FF99CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF99FF"><a href="javascript:co('#FF99FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#006600"><a href="javascript:co('#006600')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#006633"><a href="javascript:co('#006633')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#006666"><a href="javascript:co('#006666')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#006699"><a href="javascript:co('#006699')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#0066CC"><a href="javascript:co('#0066CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#0066FF"><a href="javascript:co('#0066FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#003300"><a href="javascript:co('#003300')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#003333"><a href="javascript:co('#003333')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#003366"><a href="javascript:co('#003366')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#003399"><a href="javascript:co('#003399')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#0033CC"><a href="javascript:co('#0033CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#0033FF"><a href="javascript:co('#0033FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#000000"><a href="javascript:co('#000000')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#000033"><a href="javascript:co('#000033')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#000066"><a href="javascript:co('#000066')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#000099"><a href="javascript:co('#000099')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#0000CC"><a href="javascript:co('#0000CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#0000FF"><a href="javascript:co('#0000FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#336600"><a href="javascript:co('#336600')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#336633"><a href="javascript:co('#336633')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#336666"><a href="javascript:co('#336666')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#336699"><a href="javascript:co('#336699')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#3366CC"><a href="javascript:co('#3366CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#3366FF"><a href="javascript:co('#3366FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#333300"><a href="javascript:co('#333300')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#333333"><a href="javascript:co('#333333')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#333366"><a href="javascript:co('#333366')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#333399"><a href="javascript:co('#333399')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#3333CC"><a href="javascript:co('#3333CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#3333FF"><a href="javascript:co('#3333FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#330000"><a href="javascript:co('#330000')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#330033"><a href="javascript:co('#330033')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#330066"><a href="javascript:co('#330066')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#330099"><a href="javascript:co('#330099')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#3300CC"><a href="javascript:co('#3300CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#3300FF"><a href="javascript:co('#3300FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#666600"><a href="javascript:co('#666600')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#666633"><a href="javascript:co('#666633')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#666666"><a href="javascript:co('#666666')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#666699"><a href="javascript:co('#666699')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#6666CC"><a href="javascript:co('#6666CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#6666FF"><a href="javascript:co('#6666FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#663300"><a href="javascript:co('#663300')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#663333"><a href="javascript:co('#663333')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#663366"><a href="javascript:co('#663366')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#663399"><a href="javascript:co('#663399')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#6633CC"><a href="javascript:co('#6633CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#6633FF"><a href="javascript:co('#6633FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#660000"><a href="javascript:co('#660000')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#660033"><a href="javascript:co('#660033')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#660066"><a href="javascript:co('#660066')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#660099"><a href="javascript:co('#660099')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#6600CC"><a href="javascript:co('#6600CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#6600FF"><a href="javascript:co('#6600FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#996600"><a href="javascript:co('#996600')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#996633"><a href="javascript:co('#996633')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#996666"><a href="javascript:co('#996666')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#996699"><a href="javascript:co('#996699')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#9966CC"><a href="javascript:co('#9966CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#9966FF"><a href="javascript:co('#9966FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#993300"><a href="javascript:co('#993300')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#993333"><a href="javascript:co('#993333')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#993366"><a href="javascript:co('#993366')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#993399"><a href="javascript:co('#993399')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#9933CC"><a href="javascript:co('#9933CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#9933FF"><a href="javascript:co('#9933FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#990000"><a href="javascript:co('#990000')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#990033"><a href="javascript:co('#990033')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#990066"><a href="javascript:co('#990066')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#990099"><a href="javascript:co('#990099')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#9900CC"><a href="javascript:co('#9900CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#9900FF"><a href="javascript:co('#9900FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#CC6600"><a href="javascript:co('#CC6600')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC6633"><a href="javascript:co('#CC6633')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC6666"><a href="javascript:co('#CC6666')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC6699"><a href="javascript:co('#CC6699')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC66CC"><a href="javascript:co('#CC66CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC66FF"><a href="javascript:co('#CC66FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC3300"><a href="javascript:co('#CC3300')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC3333"><a href="javascript:co('#CC3333')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC3366"><a href="javascript:co('#CC3366')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC3399"><a href="javascript:co('#CC3399')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC33CC"><a href="javascript:co('#CC33CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC33FF"><a href="javascript:co('#CC33FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC0000"><a href="javascript:co('#CC0000')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC0033"><a href="javascript:co('#CC0033')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC0066"><a href="javascript:co('#CC0066')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC0099"><a href="javascript:co('#CC0099')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC00CC"><a href="javascript:co('#CC00CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#CC00FF"><a href="javascript:co('#CC00FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
<tr>
<td bgColor="#FF6600"><a href="javascript:co('#FF6600')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF6633"><a href="javascript:co('#FF6633')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF6666"><a href="javascript:co('#FF6666')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF6699"><a href="javascript:co('#FF6699')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF66CC"><a href="javascript:co('#FF66CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF66FF"><a href="javascript:co('#FF66FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF3300"><a href="javascript:co('#FF3300')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF3333"><a href="javascript:co('#FF3333')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF3366"><a href="javascript:co('#FF3366')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF3399"><a href="javascript:co('#FF3399')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF33CC"><a href="javascript:co('#FF33CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF33FF"><a href="javascript:co('#FF33FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF0000"><a href="javascript:co('#FF0000')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF0033"><a href="javascript:co('#FF0033')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF0066"><a href="javascript:co('#FF0066')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF0099"><a href="javascript:co('#FF0099')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF00CC"><a href="javascript:co('#FF00CC')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
<td bgColor="#FF00FF"><a href="javascript:co('#FF00FF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
</tr>
</table>


</td></tr>
</table>



<table border="0" width="290" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%"><img border="0" src="images/b.gif" width="50" height="10"></td>
  </tr>
  <tr>
    <td width="100%">
      <table border="0" width="100" cellspacing="0" cellpadding="0" bgcolor="#000000">
        <tr>
          <td width="100%">
            <table border="0" width="100%" cellspacing="1" cellpadding="0">
              <tr>
                <td bgcolor="#FFFFFF"><a href="javascript:co('#FFFFFF')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
                <td bgcolor="#DDDDDD"><a href="javascript:co('#DDDDDD')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
                <td bgcolor="#C0C0C0"><a href="javascript:co('#C0C0C0')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
                <td bgcolor="#969696"><a href="javascript:co('#969696')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
                <td bgcolor="#808080"><a href="javascript:co('#808080')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
                <td bgcolor="#646464"><a href="javascript:co('#646464')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
                <td bgcolor="#4B4B4B"><a href="javascript:co('#4B4B4B')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
                <td bgcolor="#242424"><a href="javascript:co('#242424')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
                <td bgcolor="#000000"><a href="javascript:co('#000000')"><img border="0" src="images/b.gif" width="15" height="15"></a></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>




<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%"><img border="0" src="images/b.gif" width="50" height="10"></td>
  </tr>
  <tr>
    <td width="100%"><input type="text" name="hexvalue" value="<?=str_replace('_','#',$_GET['a'])?>" size="10" class="hexfield" onchange="shouldset(this.value)"><img border="0" src="images/b.gif" width="10" height="10"><input type="text" name="selcolor" style="background-color:<?=str_replace('_','#',$_GET['a'])?>" size="18" class="hexfield" onfocus="this.blur()">
    <input type="button" onClick="<?=$_GET['b']?>.value=areas.hexvalue.value;<?=$_GET['c']?>=areas.hexvalue.value;window.close()" value=" OK " class="hexfield" >
    </td>
  </tr>
</table>
</form>

</body>

</html>