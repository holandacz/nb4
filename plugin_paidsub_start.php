<?php
$subscriptions_intro = '
<img src="ads/NBCoffeeAdFull.jpg" style="float:left; margin-right:15px; border:none" alt="Please help us make a difference today.">
<h1>Please help us make a difference today.</h1>
<p>Providing this online resource for the benefit of the tens of thousands that visit NoBlood.org each month is a very substantial undertaking, both in terms of time and expense. And yet there is SO MUCH MORE that can be accomplished.</p>
<p>If you appreciate the hard work going on here at NoBlood and would like to help us to continue to grow and improve, please take a couple of minutes now to help us share in defraying the growing expenses necessary to make this site possible. Your assistance is genuinely needed and is greatly appreciated.';


if ($vbulletin->userinfo["userid"] && ! strstr($_SERVER["SCRIPT_NAME"], 'misc.php'))
	$subscriptions_intro .= '
	Please select from the following &quot;Premium User&quot; subscriptions.  If you would prefer to make a one-time donation instead, click <a href="misc.php?do=donate">here</a>.
	';
elseif ($vbulletin->userinfo["userid"])
	$subscriptions_intro .= '
	If you would prefer to make a recurring donation, please click <a href="payments.php">here</a>.
	';
else
	$subscriptions_intro .= '
	If you would prefer to make a recurring donation, please login or register and then click on the Donation link again.
	';

$subscriptions_intro .= '
</p>
<p>Yes, for the price of a cup of coffee, you can help NoBlood continue its mission to advance knowledge and
  awareness of transfusion alternatives, blood conservation, blood management, bloodless medicine and bloodless surgery.</p>
<p>From all of us at NoBlood, thank you very much for your kind and generous support.*</p>
';

$subscriptions_intro .= '
<div class="smallfont">
* NoBlood, formally know as Bloodless Healthcare International, Inc., a California non-profit corporation, is registered as a charitable organization with the State of California and may lawfully solicit donations under California law. NoBlood has been granted official tax exempt status (section 501(c)3 of the Internal Revenue Code) from the United States Internal Revenue Service. <b>You may deduct donations from your federally-taxable income.</b> Please contact a tax professional for the details of deducting such a donation. Our tax ID# is: 33-0858519.
Copy of 501(c)3 status letter <a href="http://noblood.org/wiki/images/b/b2/BHI501c3p1.gif" class="extiw" title="n:wiki/images/b/b2/BHI501c3p1.gif">page 1</a>
<a href="http://noblood.org/wiki/images/5/52/BHI501c3p2.gif" class="extiw" title="n:wiki/images/5/52/BHI501c3p2.gif">page 2</a>
</div>
<br />
';


$subscriptions_intro .= '
<form action="payments.php?do=order" method="post">
  <input type="hidden" name="s" value="" />
  <input type="hidden" name="securitytoken" value="' . $vbulletin->userinfo['securitytoken'] . '" />
  <input type="hidden" name="do" value="order" />
  <table class="tborder" cellpadding="6" cellspacing="0" border="0" width="100%" align="center">
	<tr>
	  <td class="tcat">Available Subscriptions</td>
	</tr>
	<tr>
	  <td class="panelsurround" align="center"><div class="panel">
		  <div align="left">
			<fieldset class="fieldset">
			<legend>Silver Monthly</legend>
			<table cellpadding="0" cellspacing="3" border="0" width="100%">
			  <tr>
				<td width="67%" valign="top" rowspan="2"><div style="margin-bottom:3px"><strong>Silver Monthly</strong></div></td>
				<td width="3%" rowspan="2" style="border-left:1px solid #fdf9ed">&nbsp;</td>
				<td width="30%" valign="top"></td>
			  </tr>
			  <tr>
				<td valign="bottom" nowrap="nowrap"> Cost:<br />
				  <select name="currency[1]" style="width:125px">
					<option value="">--------</option>
					<optgroup label="1 Month *">
					<option value="0_usd" >US$3.00</option>
					<option value="0_eur" >&euro;2.00</option>
					<option value="0_aud" >AU$3.40</option>
					<option value="0_cad" >CA$4.00</option>
					</optgroup>
				  </select>
				  <input type="submit" class="button" name="subscriptionids[1]" value="Order" style="font-weight:normal" />
				</td>
			  </tr>
			</table>
			</fieldset>
			<fieldset class="fieldset">
			<legend>Gold Monthly</legend>
			<table cellpadding="0" cellspacing="3" border="0" width="100%">
			  <tr>
				<td width="67%" valign="top" rowspan="2"><div style="margin-bottom:3px"><strong>Gold Monthly</strong></div></td>
				<td width="3%" rowspan="2" style="border-left:1px solid #fdf9ed">&nbsp;</td>
				<td width="30%" valign="top"></td>
			  </tr>
			  <tr>
				<td valign="bottom" nowrap="nowrap"> Cost:<br />
				  <select name="currency[2]" style="width:125px">
					<option value="">--------</option>
					<optgroup label="1 Month *">
					<option value="0_usd" >US$5.00</option>
					<option value="0_eur" >&euro;3.40</option>
					<option value="0_aud" >AU$5.70</option>
					<option value="0_cad" >CA$5.00</option>
					</optgroup>
				  </select>
				  <input type="submit" class="button" name="subscriptionids[2]" value="Order" style="font-weight:normal" />
				</td>
			  </tr>
			</table>
			</fieldset>
			<fieldset class="fieldset">
			<legend>Platinum Monthly</legend>
			<table cellpadding="0" cellspacing="3" border="0" width="100%">
			  <tr>
				<td width="67%" valign="top" rowspan="2"><div style="margin-bottom:3px"><strong>Platinum Monthly</strong></div></td>
				<td width="3%" rowspan="2" style="border-left:1px solid #fdf9ed">&nbsp;</td>
				<td width="30%" valign="top"></td>
			  </tr>
			  <tr>
				<td valign="bottom" nowrap="nowrap"> Cost:<br />
				  <select name="currency[3]" style="width:125px">
					<option value="">--------</option>
					<optgroup label="1 Month *">
					<option value="0_usd" >US$10.00</option>
					</optgroup>
				  </select>
				  <input type="submit" class="button" name="subscriptionids[3]" value="Order" style="font-weight:normal" />
				</td>
			  </tr>
			</table>
			</fieldset>
			<fieldset class="fieldset">
			<legend>Silver Anual</legend>
			<table cellpadding="0" cellspacing="3" border="0" width="100%">
			  <tr>
				<td width="67%" valign="top" rowspan="2"><div style="margin-bottom:3px"><strong>Silver Anual</strong></div></td>
				<td width="3%" rowspan="2" style="border-left:1px solid #fdf9ed">&nbsp;</td>
				<td width="30%" valign="top"></td>
			  </tr>
			  <tr>
				<td valign="bottom" nowrap="nowrap"> Cost:<br />
				  <select name="currency[4]" style="width:125px">
					<option value="">--------</option>
					<optgroup label="1 Year *">
					<option value="0_usd" >US$36.00</option>
					<option value="0_eur" >&euro;25.00</option>
					<option value="0_aud" >AU$41.00</option>
					<option value="0_cad" >CA$36.00</option>
					</optgroup>
				  </select>
				  <input type="submit" class="button" name="subscriptionids[4]" value="Order" style="font-weight:normal" />
				</td>
			  </tr>
			</table>
			</fieldset>
			<fieldset class="fieldset">
			<legend>Gold Anual</legend>
			<table cellpadding="0" cellspacing="3" border="0" width="100%">
			  <tr>
				<td width="67%" valign="top" rowspan="2"><div style="margin-bottom:3px"><strong>Gold Anual</strong></div></td>
				<td width="3%" rowspan="2" style="border-left:1px solid #fdf9ed">&nbsp;</td>
				<td width="30%" valign="top"></td>
			  </tr>
			  <tr>
				<td valign="bottom" nowrap="nowrap"> Cost:<br />
				  <select name="currency[5]" style="width:125px">
					<option value="">--------</option>
					<optgroup label="1 Year *">
					<option value="0_usd" >US$60.00</option>
					<option value="0_eur" >&euro;41.00</option>
					<option value="0_aud" >AU$68.00</option>
					<option value="0_cad" >CA$60.00</option>
					</optgroup>
				  </select>
				  <input type="submit" class="button" name="subscriptionids[5]" value="Order" style="font-weight:normal" />
				</td>
			  </tr>
			</table>
			</fieldset>
			<fieldset class="fieldset">
			<legend>Platinum Anual</legend>
			<table cellpadding="0" cellspacing="3" border="0" width="100%">
			  <tr>
				<td width="67%" valign="top" rowspan="2"><div style="margin-bottom:3px"><strong>Platinum Anual</strong></div></td>
				<td width="3%" rowspan="2" style="border-left:1px solid #fdf9ed">&nbsp;</td>
				<td width="30%" valign="top"></td>
			  </tr>
			  <tr>
				<td valign="bottom" nowrap="nowrap"> Cost:<br />
				  <select name="currency[6]" style="width:125px">
					<option value="">--------</option>
					<optgroup label="1 Year *">
					<option value="0_usd" >US$120.00</option>
					<option value="0_eur" >&euro;80.00</option>
					<option value="0_aud" >AU$137.00</option>
					<option value="0_cad" >CA$120.00</option>
					</optgroup>
				  </select>
				  <input type="submit" class="button" name="subscriptionids[6]" value="Order" style="font-weight:normal" />
				</td>
			  </tr>
			</table>
			</fieldset>
			<div class="fieldset" style="margin:0px; padding:3px"> Validity periods marked <strong class="highlight">*</strong> indicate that purchasing this subscription is recurring, this means after the period is complete it will automatically be renewed. </div>
		  </div>
		</div></td>
	</tr>
  </table>
</form>

<br />
<br />
';