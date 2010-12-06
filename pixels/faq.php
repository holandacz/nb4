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
@ini_set('include_path',".");
include('incs/functions.php');
$VERSIONS[basename(__FILE__)] = "3.03";
#$filenamenr = basename(__FILE__);
include('header.php');

$TMP['%[CONTENT]%'] = '';

if(!$faq = DB_array("SELECT * FROM ".$dbprefix."faq WHERE lang='$lang' ORDER by faq_nr,faq_id",'*'))
    $faq = DB_array("SELECT * FROM ".$dbprefix."faq WHERE lang='".mysql_real_escape_string($CONFIG['standard_language'])."' OR lang IS NULL ORDER by faq_nr,faq_id",'*');

if(is_array($faq)) {
    while(list(,$fq) = each($faq)) {
        $TMP['%[CONTENT]%']   .= '<br><a name="'.$fq['faq_id'].'"></a><h2 class="faq_question">'.$fq['faq_question'].'</h2>'.nl2br(html_entity_decode($fq['faq_answer'])).'<hr class="faq_line">';
        $TMP['%[FAQ_INDEX]%'] .= '<li class="faq_line"><a href="faq.php?pa='.$PAGE_ID.'#'.$fq['faq_id'].'"><h2 class="faq_question">'.$fq['faq_question'].'</h2></a></li>';
    }
}

$TMP['%[CONTENT]%']      = '<ul class="faq_line">'.$TMP['%[FAQ_INDEX]%'].'</ul><hr class="faq_line">'.$TMP['%[CONTENT]%'];
$TMP['%[MAIN_CONTENT]%'] = $TMP['%[CONTENT]%'];

print template($LANGDIR.'faq.htm',$TMP);

include('footer.php');
?>