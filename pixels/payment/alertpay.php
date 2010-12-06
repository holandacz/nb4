<form action='https://www.alertpay.com/PayProcess.aspx' method='post'>
    <input type='hidden' name='ap_purchasetype' value='Item'>
    <input type='hidden' name='ap_merchant' value='%[EMAIL_ALERTPAY]%'>
    <input type='hidden'  name='ap_itemname' value='Pixelblocks for %[CUSTOMER_URL]%'>
    <input type='hidden'  name='ap_currency' value='%[PAY_CURRENCY]%'>
    <input type='hidden'  name='ap_returnurl' value='%[DOMAIN]%'>
    <input type='image' src='https://www.alertpay.com/images/BuyNow/big_pay_03.gif'>
    <input type='hidden'  name='ap_quantity' value='1'>
    <input type='hidden' name='ap_description' value='Userid %[USERID]%'>
    <input type='hidden'  name='ap_amount' value='%[PAY_AMOUNT]%'>
</form>