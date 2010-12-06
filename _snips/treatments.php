<?php
$lst	= array();
$lst[] = array("tag" => "Anesthetic techniques", "name" => "Anesthetic techniques", "subs" => "Anesthetic techniques");
$lst[] = array("tag" => "Apheresis", "name" => "Apheresis", "subs" => "Apheresis");
$lst[] = array("tag" => "Autologous Blood Donation", "name" => "Autologous Blood Donation", "subs" => "Autologous Blood Donation");
$lst[] = array("tag" => "Blood", "name" => "Blood", "subs" => "Blood");
$lst[] = array("tag" => "Blood fractions", "name" => "Blood fractions", "subs" => "Blood fractions");
$lst[] = array("tag" => "Blood substitutes", "name" => "Blood substitutes", "subs" => "Blood substitutes");
$lst[] = array("tag" => "Blood conservation techniques", "name" => "Blood conservation techniques", "subs" => "Blood conservation techniques");
$lst[] = array("tag" => "Cell salvage", "name" => "Cell salvage", "subs" => "Cell salvage");
$lst[] = array("tag" => "Chemotherapy", "name" => "Chemotherapy", "subs" => "Chemotherapy");
$lst[] = array("tag" => "Cryoprecipitate", "name" => "Cryoprecipitate", "subs" => "Cryoprecipitate");
$lst[] = array("tag" => "Dialysis", "name" => "Dialysis", "subs" => "Dialysis");
$lst[] = array("tag" => "Extracorporeal therapy", "name" => "Extracorporeal therapy", "subs" => "Extracorporeal therapy");
$lst[] = array("tag" => "Erythropoietin (EPO)", "name" => "Erythropoietin (EPO)", "subs" => "Erythropoietin");
$lst[] = array("tag" => "Factor VIIa", "name" => "Factor VIIa", "subs" => "Factor VIIa");
$lst[] = array("tag" => "Fluid replacement", "name" => "Fluid replacement", "subs" => "Fluid replacement");
$lst[] = array("tag" => "Hemodilution", "name" => "Hemodilution", "subs" => "Hemodilution");
$lst[] = array("tag" => "Hemofiltration", "name" => "Hemofiltration", "subs" => "Hemofiltration");
$lst[] = array("tag" => "Hemostatic agents", "name" => "Hemostatic agents", "subs" => "Hemostatic agents");
$lst[] = array("tag" => "Hemostatic devices", "name" => "Hemostatic devices", "subs" => "Hemostatic devices");
$lst[] = array("tag" => "Hydroxyurea", "name" => "Hydroxyurea", "subs" => "Hydroxyurea");
$lst[] = array("tag" => "Hyperbaric therapy", "name" => "Hyperbaric therapy", "subs" => "Hyperbaric therapy");
$lst[] = array("tag" => "Iron therapy", "name" => "Iron therapy", "subs" => "Iron therapy");
//$lst[] = array("tag" => "IV therapy", "name" => "IV therapy", "subs" => "IV therapy");
$lst[] = array("tag" => "Joint replacement", "name" => "Joint replacement", "subs" => "Joint replacement");
//$lst[] = array("tag" => "Medications", "name" => "Medications", "subs" => "Medications");
$lst[] = array("tag" => "Microsampling", "name" => "Microsampling", "subs" => "Microsampling");
$lst[] = array("tag" => "Minimally invasive procedures", "name" => "Minimally invasive procedures", "subs" => "Minimally invasive procedures");
//$lst[] = array("tag" => "Oral medications", "name" => "Oral medications", "subs" => "Oral medications");
$lst[] = array("tag" => "Organ transplantation", "name" => "Organ transplantation", "subs" => "Organ transplantation");
$lst[] = array("tag" => "Oxygenation", "name" => "Oxygenation", "subs" => "Oxygenation");
$lst[] = array("tag" => "Radiation therapy", "name" => "Radiation therapy", "subs" => "Radiation therapy");
$lst[] = array("tag" => "Stem cell therapy", "name" => "Stem cell therapy", "subs" => "Stem cell therapy");
$lst[] = array("tag" => "Transfusion therapy", "name" => "Transfusion therapy", "subs" => "Transfusion therapy");


$treatments = '
  <div id="treatments_r">
	<div id="treatments_top">
	  <h3>Treatments</h3>
	</div>
	<div id="treatments_box">
	  <ul>';

foreach($lst as $l)
	$treatments .= '<li><a href="tags/' . $l['tag'] . '/"' . ' title="' . $l['name']
	. ($l['name'] ? ': ' . $l['subs'] : '') . '">' . $l['name'] . '</a></li>';

$treatments .= '</ul>
	</div>
  </div>
';
