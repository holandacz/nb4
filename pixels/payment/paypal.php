<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="%[PAYPAL_EMAIL]%">
<input type="hidden" name="item_name" value="Pixel for %[CUSTOMER_URL]%">
<input type="hidden" name="currency_code" value="%[PAY_CURRENCY]%">
<input type="hidden" name="amount" value="%[PAY_AMOUNT]%">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="invoice" value="Userid %[USERID]%">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but5.gif" name="submit" alt="">
</form>