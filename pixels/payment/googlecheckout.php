<form action="https://checkout.google.com/cws/v2/Merchant/%[EMAIL_STORMPAY]%/checkoutForm" id="BB_BuyButtonForm" method="post" name="BB_BuyButtonForm">
    <input name="item_name_1" type="hidden" value="Pixel for %[CUSTOMER_URL]%"/>
    <input name="item_description_1" type="hidden" value="Userid %[USERID]%"/>
    <input name="item_quantity_1" type="hidden" value="1"/>
    <input name="item_price_1" type="hidden" value="%[PAY_AMOUNT]%"/>
    <input name="item_currency_1" type="hidden" value="USD"/>
    <input name="_charset_" type="hidden" value="utf-8"/>
    <input alt="" src="https://checkout.google.com/buttons/buy.gif?merchant_id=%[EMAIL_STORMPAY]%&amp;w=117&amp;h=48&amp;style=trans&amp;variant=text&amp;loc=en_US" type="image"/>
</form>
