<?php
if ($show['registerbutton']) {
	$featured = '
	<div id="featured_r">
	  <div id="featured_box">
	<h1 class="title"><a href="about/">Welcome to the latest in bloodless medicine and blood transfusion alternatives.</a></h1>
	<div class="thumb_about"><img src="styles/main/imgs/modules/about_noblood_img_150x100.jpg"/>
	  <p class="imgTagline"></p>
	</div>
	<h2>We are a community of medical professionals and members of the public who are responding to the worldwide concern about the efficacy, cost and availability of donor blood.</h2>
	<p class="teaser"><b>You are currently viewing this site as a guest.</b> <a href="signin/" title="Sign in to your account."><span style="color:red"><u>Sign in</u></span></a> or <a href="signup/" title="Create your new account."><span style="color:red"><u>sign up</u></span></a> to gain full access to the many resources and services available at noblood. If you have any problems with the registration process or your account login, please <a href="sendemail/"><b>Contact Us</b></a>.</p>
	  </div>
	</div>
	';
}else{
	$f	= array(
		'title' => 'Bloodless Atrial Septal Defect Repair (ASD)<br />in 8 Month Old',
		'subTitle' => '',
		'source' => '<a href="http://www.noblood.org/members/jmalak.html">Joe Malak, MD FAAP</a>',
		'sourceArticleUrl' => 'http://wiki.noblood.org/index.php/Bloodless_Atrial_Septal_Defect_Repair_(ASD)_in_8_Month-Old',
		'date' => 'April 9, 2009',
		'imgUrl' => 'http://wiki.noblood.org/images/5/55/Bloodless_Atrial_Septal_Defect_Repair_1_150x196.png',
		'imgTagline' => '',
		'teaser' => 'Do you like an adventure with a happy ending? With a cute little girl? With real life drama and decision-making? Then this feature story is for you. Are you a healthcare professional? Part I was written with you in mind. Are you a parent, or do you have a family member that might depend on you to coordinate their bloodless healthcare? Then Part II will likely capture your focus.',
		'url' => '',
		'copyright' => ''
	);
	/* Source
	http://public-healthcare-issues.suite101.com/article.cfm/blood_transfusions_safe_medicine
	*/
	$featured = '
	<div id="featured_r">
	  <div id="featured_box">
		<div class="thumb"><img src="' . $f['imgUrl'] . '"/>
			<p class="imgTagline">' . $f['imgTagline'] . '</p>
		  </div>
		  <h1 class="title"><a href="' . $f['sourceArticleUrl'] . '">' . $f['title'] . '</a></h1>
		  <!--<h2 class="subTitle">' . $f['subTitle'] . '</h2>-->
		  <p class="source">' . $f['source'] . ' - </p>
		  <p class="date">' . $f['date'] . '</p>

		  <p class="teaser">' . $f['teaser'] . '</p>
		  <p class="more"><a href="' . $f['sourceArticleUrl'] . '">Read more...</a></p>
	  </div>
	</div>
	';

	$featured = '
	<div id="featured_r">
	  <div id="featured_box"><object><param name="movie" value="http://www.youtube.com/watch?v=XHJfLMquC5Y"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/XHJfLMquC5Y?fs=1&amp;hl=en_US" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="415" height="269"></embed></object></div></div>
	';
}
