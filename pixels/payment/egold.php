<form action="https://www.e-gold.com/sci_asp/payments.asp" method="POST" target=_top>
<input type="hidden" name="PAYEE_ACCOUNT" value="%[EGOLD_ACCOUNT]%">
<input type="hidden" name="PAYEE_NAME" value="%[DOMAINNAME]%">
<input type=hidden name="PAYMENT_AMOUNT" value="%[PAY_AMOUNT]%">
<input type=hidden name="PAYMENT_UNITS" value=%[PAYMENT_UNITS]%>
<input type=hidden name="PAYMENT_METAL_ID" value=1>
<input type="hidden" name="STATUS_URL" value="mailto:%[EMAIL_WEBMASTER]%">
<input type="hidden" name="NOPAYMENT_URL" value="%[DOMAIN]%">
<input type="hidden" name="NOPAYMENT_URL_METHOD" value="LINK">
<input type="hidden" name="PAYMENT_URL" value="%[DOMAIN]%">
<input type="hidden" name="PAYMENT_URL_METHOD" value="LINK">
<input type="hidden" name="BAGGAGE_FIELDS" value="USERID">
<input type="hidden" name="USERID" value="Userid: %[USERID]%">
<input type="hidden" name="SUGGESTED_MEMO" value='Pixel for %[CUSTOMER_URL]%'>
<input type="image" src=http://www.e-gold.com/gif/paywith.gif border=0 Alt="Pay Now with e-gold" name="PAYMENT_METHOD" value="Buy Now">
</form>