<?php
$map = '
<!-- map -->
<div id="map_r">
  <div class="top"> </div>
  <div class="box">
	<div class="title">
	  <h2>Bloodless Medicine and Surgery Map</h2>
	</div>
	<div class="map">
		<a href="bloodless-medicine-surgery-map?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $vbulletin->userinfo['userid'] . '">
		<img src="http://noblood.org/styles/main/imgs/modules/hospital_map.png" height="159" width="300" border="0" title="The World of Bloodless Medicine and Surgery" />
		</a>
	  <div class="clearboth"> </div>
	</div>
  </div>
  <div class="bottom"> </div>
</div>
<!-- / map -->
';