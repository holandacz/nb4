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
$getprocess = true;
include_once('incs/functions.php');
$VERSIONS[basename(__FILE__)] = "3.3 PRO";

if(($recheck || ((int)$_GET['u']) && $recheck = DB_query("SELECT *,DATE_FORMAT(regdat,'%d.%m.%Y') AS d FROM ".$dbprefix."user WHERE submit IS NULL AND userid='".(int)$_GET['u']."'",'*'))) {

    // Grid neu laden, da evtl. anders als Startseite sein kann:
    $GRID_TO_PAY = DB_query("SELECT exchange_rate,pay_currency,vat,plugin FROM ".$dbprefix."grids WHERE gridid='".(int)$recheck['gridid']." LIMIT 1'",'*');

    // Zahlungsmöglichkeiten laden
    $PAYMETHOD = DB_array("SELECT * FROM ".$dbprefix."currency",'+++');

    // Templates für die Zahlungsintegration
    $AMOUNT   = $recheck['amount'];
    $CURR     = strtoupper($recheck['currency']);
    $MWST     = $recheck['vat'];
    $MWST_ADD = $recheck['vat_add'];
    $PAYTMP['%[CUSTOMER_URL]%'] = $recheck['url'];
    $PAYTMP['%[CUSTOMER_EMAIL]%'] = $recheck['email'];
    $PAYTMP['%[USERID]%']       = $recheck['userid'];
    $PAYTMP['%[PIXELBLOCKS]%']  = (int)$recheck['kaesten'];


    // Betrag
    $PAYTMP['%[B]%'] = $PAYTMP['%[BASE_AMOUNT]%'] = $PAYTMP['%[AMOUNT]%'] = number_format($AMOUNT,(int)$PAYMETHOD[$CURR]['dec'],$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']).' '.$CURR;

    // Mit Mehrwertsteuerausweisung
    if($MWST>0) {
        // Mwst hinzu
        if($MWST_ADD) {
            $PAYTMP['%[BASE_AMOUNT]%'] = number_format($AMOUNT-(($AMOUNT*100)/(100+$MWST)),(int)$PAYMETHOD[$CURR]['dec'],$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']).' '.$CURR;
        }
        $PAYTMP['%[VAT]%']      = $PAYTMP['%[MWST]%']      = number_format($MWST,(int)$PAYMETHOD[$CURR]['dec'],$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']).' '.$CURR;
        $PAYTMP['%[VAT_INFO]%'] = $PAYTMP['%[MWST_INFO]%'] = sprintf($_SP[100] , $PAYTMP['%[VAT]%']);
        $PAYTMP['%[B]%']        = $PAYTMP['%[AMOUNT]%']    = number_format($AMOUNT,(int)$PAYMETHOD[$CURR]['dec'],$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']).' '.$CURR.' '.$PAYTMP['%[VAT_INFO]%'];
    }


    // Wechselkurs?
    if($GRID_TO_PAY['pay_currency'] && $GRID_TO_PAY['exchange_rate']) {
        $AMOUNT = $GRID_TO_PAY['exchange_rate']*$AMOUNT;
        $CURR   = $GRID_TO_PAY['pay_currency'];

        $PAYTMP['%[INFO_OTHER_CURRENCY]%'] = '<img src="'.$designpath.'i.gif" hspace=3 align=absmiddle><b>'.sprintf($_SP[60],$PAYMETHOD[$CURR]['long'],number_format(round($AMOUNT,2),(int)$PAYMETHOD[$CURR]['dec'],$LANG_ACTIVE[$lang]['dec_point'],$LANG_ACTIVE[$lang]['thousands']).' '.$PAYMETHOD[$CURR]['iso']).'</b><br>';
    } else {
        $PAYTMP['%[INFO_OTHER_CURRENCY]%'] = '';
    }


    $PAYTMP['%[PAY_AMOUNT]%']      = $AMOUNT;
    $PAYTMP['%[PAY_CURRENCY]%']    = $CURR;
    $PAYTMP['%[EMAIL_WEBMASTER]%'] = $PAYTMP['%[EMAIL]%'] = $CONFIG['email_webmaster'];

    // Paypal
    $payfiles['paypal.php']     = $CONFIG['email_paypal'] ? true : false;
    $PAYTMP['%[PAYPAL_EMAIL]%'] = $CONFIG['email_paypal'];

    // 2checkout
    $payfiles['2checkout.php']  = ($CONFIG['2checkout_id']&&  $CONFIG['2checkout_product_id']) ? true : false;
    $PAYTMP['%[2CHECKOUT_ID]%'] = $CONFIG['2checkout_id'];
    $PAYTMP['%[2CHECKOUT_PRODUCT_ID]%'] = $CONFIG['2checkout_product_id'];

    // googlecheckout
    $payfiles['googlecheckout.php'] = $CONFIG['email_stormpay'] ? true : false;
    $PAYTMP['%[EMAIL_STORMPAY]%'] = $CONFIG['email_stormpay'];

    // AlertPay
    $payfiles['alertpay.php']     = $CONFIG['email_alertpay'] ? true : false;
    $PAYTMP['%[EMAIL_ALERTPAY]%'] = $CONFIG['email_alertpay'];

    // eGold
    $payfiles['egold.php']         = $CONFIG['egold_account'] ? true : false;
    $PAYTMP['%[EGOLD_ACCOUNT]%']   = $CONFIG['egold_account'];
    if($CURR == 'USD')      $PAYTMP['%[PAYMENT_UNITS]%'] = 1;
    elseif($CURR == 'JPY')  $PAYTMP['%[PAYMENT_UNITS]%'] = 81;
    elseif($CURR == 'AUD')  $PAYTMP['%[PAYMENT_UNITS]%'] = 61;
    elseif($CURR == 'CAD')  $PAYTMP['%[PAYMENT_UNITS]%'] = 2;
    elseif($CURR == 'CHF')  $PAYTMP['%[PAYMENT_UNITS]%'] = 41;
    elseif($CURR == 'EUR')  $PAYTMP['%[PAYMENT_UNITS]%'] = 85;
    elseif($CURR == 'GBP')  $PAYTMP['%[PAYMENT_UNITS]%'] = 44;

    // SafePay
    $payfiles['safepay.php']      = $CONFIG['email_safepay'] ? true : false;
    $PAYTMP['%[USER_SAFEPAY]%']   = $CONFIG['email_safepay'];


    $PAYTMP['%[PAYMENT]%'] = '';
    $dhandle   = opendir("payment/");
    $filenames = array();
    while($files = readdir($dhandle)) {
        if($files != "." && $files != ".." && $files != ".htaccess") {
            if($payfiles[$files]) {
                $PAYTMP['%[PAYMENT]%'] .= $PAYTMP['%[PAYMENT_'.strtoupper(str_replace('.php','',$files)).']%'] = template('payment/'.$files,$PAYTMP);
            } else {
                $PAYTMP['%[PAYMENT_'.strtoupper(str_replace('.php','',$files)).']%'] = '';
            }
        }
    }
    closedir($dhandle);
    clearstatcache();


    $getprocess  = $recheck['gridid'];
    $grid_plugin = $GRID_TO_PAY['plugin'] ? true : false;

    include_once('header.php');

    print template($LANGDIR.'getend.htm',$PAYTMP);
    include_once('footer.php');

} else {
    header("Location: index.php");
}
?>