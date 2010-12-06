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
include_once('incs/functions.php');
$VERSIONS[basename(__FILE__)] = "3.01 PRO";
$filenamenr = basename(__FILE__);
include_once('header.php');
$USED_CURR = DB_array("SELECT iso,`dec` FROM ".$dbprefix."currency",'++');

// Featured Ads
if($pixel_used) {
    $biggest_ad = DB_query("SELECT url,title,hits,bild,userid,gridid,kaesten FROM ".$dbprefix."user WHERE submit IS NOT NULL $IN_GRIDS ORDER BY kaesten DESC LIMIT 1",'*');

    if($GRID[$biggest_ad['gridid']]['featured_ads']) {
        $temp_href  = $GRID[$biggest_ad['gridid']]['track_clicks'] ? 'index.php?u='.$biggest_ad['userid'] : $biggest_ad['url'];
        $temp_hits  = $GRID[$biggest_ad['gridid']]['track_clicks'] && $GRID[$biggest_ad['gridid']]['show_clicks'] ? ' ('.$biggest_ad['hits'].')' : '';
        $temp_title = $GRID[$biggest_ad['gridid']]['show_box'] ? ' onmouseover="return escape(\''.htmlspecialchars(addslashes($biggest_ad['title'])).$temp_hits.'\')"' : ' title="'.htmlspecialchars(stripslashes($biggest_ad['title'])).$temp_hits.'"';
        $TEMP['%[MIN_BLOCKS]%'] = $biggest_ad['kaesten']+1;
        $TEMP['%[BIGGEST_AD]%'] = '<a href="'.$temp_href.'" target="_blank"'.$temp_title.'><img src="sp.php?u='.$biggest_ad['userid'].'"></a>';

        $latest_ad  = DB_query("SELECT url,title,hits,bild,userid,gridid FROM ".$dbprefix."user WHERE submit IS NOT NULL $IN_GRIDS ORDER BY submit DESC LIMIT 1",'*');
        $temp_href  = $GRID[$latest_ad['gridid']]['track_clicks'] ? 'index.php?u='.$latest_ad['userid'] : $latest_ad['url'];
        $temp_hits  = $GRID[$latest_ad['gridid']]['track_clicks'] && $GRID[$latest_ad['gridid']]['show_clicks'] ? ' ('.$latest_ad['hits'].')' : '';
        $temp_title = $GRID[$latest_ad['gridid']]['show_box'] ? ' onmouseover="return escape(\''.htmlspecialchars(addslashes($latest_ad['title'])).$temp_hits.'\')"' : 'title="'.htmlspecialchars(stripslashes($latest_ad['title'])).$temp_hits;
        $TEMP['%[LATEST_AD]%']  = '<a href="'.$temp_href.'" target="_blank"'.$temp_title.'><img src="sp.php?u='.$latest_ad['userid'].'"></a>';
        $featured_ads = template($LANGDIR.'featured_ads.htm',$TEMP);
        unset($TEMP);
        $include_tooltip = true;
    }
}

$TEMP['%[FEATURED_ADS]%'] = $featured_ads;

$page_template_file = !@file_exists($LANGDIR.'page_'.$PAGE_ID.'_get.htm') ? 'standard_get.htm' : 'page_'.$PAGE_ID.'_get.htm';

$TEMP['%[GRIDS]%']  = $tmp['%[BLOCKSIZE]%'] = $tmp['%[BLOCKSIZE_X]%'] = $tmp['%[BLOCKSIZE_Y]%'] = '';

while(list($gkey,$gval) = each($GRID)) {
    if($gval['active'] && !$gval['plugin'] && !$gval['dontbuy']) {
        $grid_template_file     = !@file_exists($LANGDIR.'grid_'.$gval['gridid'].'_getstep1_start.htm') ? 'standard_getstep1_start.htm' : 'grid_'.$gval['gridid'].'_getstep1_start.htm';
        $tmp = array();
        $tmp['%[MAXFIELDS]%']         = $gval['maxfields'] ? $gval['maxfields'] : '';
        $tmp['%[GRID_NAME]%']         = $gval['name'] ? htmlspecialchars($gval['name']) : '';
        $tmp['%[PAGE_NAME]%']         = $gval['page_name'] ? htmlspecialchars($gval['page_name']) : '';
        $tmp['%[PAGE_SLOGAN]%']       = $gval['page_slogan'] ? htmlspecialchars($gval['page_slogan']) : '';
        $tmp['%[EXPIRE_DAYS]%']       = $gval['expire_days'] ? $gval['expire_days'] : '';
        $tmp['%[EXPIRE_MONTHS]%']     = $gval['expire_days'] ? (int)($gval['expire_days']/30)  : '';
        $tmp['%[EXPIRE_YEARS]%']      = $gval['expire_days'] ? (int)($gval['expire_days']/365) : '';
        $tmp['%[BUTTON_TEXT_START]%'] = '<input type=button onClick="location.href=\'getp.php?gr='.$gval['gridid'].''.$trackpage_.'\'" style="height:28;color:#000000;font-weight:bold" value="';
        $tmp['%[BUTTON_TEXT_END]%']   = $tmp['%[BUTTON_TEXT_ENDE]%'] = '">';
        $tmp['%[GRIDID]%']            = $gval['gridid'];

        if($gval['blockprice']>0) {
            $tmp['%[BLOCKPRICE]%']      = number_format($gval['blockprice'],$USED_CURR[$gval['currency']]['dec'],$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']).' '.strtoupper($gval['currency']);
            $tmp['%[PIXELPRICE]%']      = number_format($gval['blockprice']/($gval['blocksize_x']*$gval['blocksize_y']),$USED_CURR[$gval['currency']]['dec'],$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']).' '.strtoupper($gval['currency']);
            $tmp['%[PAY_INFO_START]%']  = '';
            $tmp['%[PAY_INFO_END]%']    = '';
            $tmp['%[FREE_INFO_START]%'] = '<!--';
            $tmp['%[FREE_INFO_END]%']   = '-->';
        } else {
            $tmp['%[BLOCKPRICE]%']      = '';
            $tmp['%[PIXELPRICE]%']      = '';
            $tmp['%[PAY_INFO_START]%']  = '<!--';
            $tmp['%[PAY_INFO_END]%']    = '-->';
            $tmp['%[FREE_INFO_START]%'] = '';
            $tmp['%[FREE_INFO_END]%']   = '';
        }

        $tmp['%[BLOCKSIZE]%']   = $gval['blocksize_x'].'x'.$gval['blocksize_y'];
        $tmp['%[BLOCKSIZE_X]%'] = $gval['blocksize_x'];
        $tmp['%[BLOCKSIZE_Y]%'] = $gval['blocksize_y'];

        $tmp['%[VAT]%']       =
         $tmp['%[MWST]%']     =
         $tmp['%[VAT_INFO]%'] =
         $tmp['%[MWST_INFO]%'] = '';

        if($gval['vat']>0) {
            $tmp['%[VAT]%'] = $tmp['%[MWST]%'] = number_format($gval['vat'],1,$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']).' %';
            $tmp['%[VAT_INFO]%']        = $tmp['%[MWST_INFO]%'] = ($gval['vat_add']) ? sprintf($_SP[101] , $tmp['%[VAT]%']) : sprintf($_SP[100] , $tmp['%[VAT]%']);
            $tmp['%[BASE_BLOCKPRICE]%'] = $tmp['%[BLOCKPRICE]%'];
            $tmp['%[BLOCKPRICE]%']      = $tmp['%[BLOCKPRICE]%'].' '.$tmp['%[VAT_INFO]%'];
        }

        $TEMP['%[GRIDS]%'] .= template($LANGDIR.$grid_template_file,$tmp).'<br><br><br>';
    }
}

print template($LANGDIR.$page_template_file,$TEMP);

include_once('footer.php');
?>
