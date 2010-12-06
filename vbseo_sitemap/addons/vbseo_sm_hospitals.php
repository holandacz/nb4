<?
/*
	$baseUrl = $vbseo_vars['bburl'].'/hospital.php?id=';

	$hospitals = $db->query_read_slave("
		SELECT id FROM aa_companies	WHERE type=1 AND publish = 1
	");

	while ($hospital = $db->fetch_array($hospitals))
	{
		$url = $baseUrl . $hospital['id'];
echo $url . "<br />";
		if(VBSEO_ON)
			$url = vbseo_any_url($url);

		vbseo_add_url($url, 1.0, '', 'weekly');
	}

*/


	$baseUrl = $vbseo_vars['bburl'] . '/bloodless-medicine-surgery-hospital/';
	$hospitals = $db->query_read_slave("
		SELECT id, name, city FROM aa_companies	WHERE type=1 AND publish = 1
	");
	while ($hospital = $db->fetch_array($hospitals))
	{
		$url = $baseUrl;
		$name = preg_replace('/%\d*-*/sim', '', urlencode(str_replace(' ', '-', $hospital['name'])));
		$city = preg_replace('/%\d*-*/sim', '', urlencode(str_replace(' ', '-', $hospital['city'])));
		$url = $baseUrl . $city . '-' . $name . '-' . $hospital['id'];
		echo $url . "<br />";
		if(VBSEO_ON)
			$url = vbseo_any_url($url);

		vbseo_add_url($url, 1.0, '', 'daily');
	}  // end while
?>