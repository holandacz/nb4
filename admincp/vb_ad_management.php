<?php
// ######################### ERROR REPORTING #############################
error_reporting(E_ALL & ~E_NOTICE);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
define('NO_REGISTER_GLOBALS', 1);

$specialtemplates   = array();

include_once('./global.php');
include_once('./includes/functions_misc.php');
include_once('./includes/vb_ad_management/functions_vb_ad_management.php');

$addelimiter = $vbulletin->options['adintegrate_delimiter'];

// Get the product version, for the link to the manual.
$adintegrate_rt = $db->query_first_slave("
	SELECT version
	FROM ".TABLE_PREFIX."product
	WHERE productid = 'adintegrate_rt'
");

// Output: STRING - Constructs the javascript for the AJAX adcode preview
// Arg{1}: STRING - PHP function to perform
// Arg{2}: STRING - Name of the adcode block
function adcode_preview_js($x_function, $x_adcode_name)
{
	global $vbphrase;

	$js = '<script type="text/javascript">
	<!--
	function '.$x_adcode_name.'_adcode_previewing(adcode_contents)
	{
		fetch_object(\''.$x_adcode_name.'_preview_working\').innerHTML = \''.$vbphrase['setting_adintegrate_working'].'\'
		'.$x_adcode_name.'_adcode_preview = new vB_AJAX_Handler(true)
		'.$x_adcode_name.'_adcode_preview.onreadystatechange('.$x_adcode_name.'_adcode_previewed)
		'.$x_adcode_name.'_adcode_preview.send(\'vb_ad_management.php?do='.$x_function.'_preview&adcode=\' + PHP.urlencode(adcode_contents))
	}
	function '.$x_adcode_name.'_adcode_previewed()
	{
		if ('.$x_adcode_name.'_adcode_preview.handler.readyState == 4 && '.$x_adcode_name.'_adcode_preview.handler.status == 200)
		{
			fetch_object(\''.$x_adcode_name.'_adcode_contents\').innerHTML = '.$x_adcode_name.'_adcode_preview.handler.responseText
			fetch_object(\''.$x_adcode_name.'_preview_working\').innerHTML = \'\'
		}
	}
	//-->
	</script>';
	
	return $js;
}

// Warning messages
if ($vbulletin->options['adintegrate_onoff'] == '0')
{
	$adintegrate_onoff_warning = '<span style="float:right;color:#500;">'.$vbphrase['setting_adintegrate_ads_are_disabled'].'</span>';
}
else if ($vbulletin->options['adintegrate_sharing_onoff'] == '0')
{
	$adintegrate_sharing_onoff_warning = '<span style="float:right;color:#500;">'.$vbphrase['setting_adintegrate_sharing_is_disabled'].'</span>';
}
if ($vbulletin->options['adintegrate_autoinsert_onoff'] == '0')
{
	$adintegrate_autoinsert_warning = '<span style="float:right;color:#500;">'.$vbphrase['setting_adintegrate_autoinsert_is_disabled'].'</span>';
}
if (($vbulletin->options['adintegrate_sharing_chanceofstaff'] + $vbulletin->options['adintegrate_sharing_chanceofthreadstarter'] + $vbulletin->options['adintegrate_sharing_chanceoflastposter']) > '100')
{
	$adintegrate_autoinsert_chances_warning = '<span style="float:right;color:#500;">'.$vbphrase['setting_adintegrate_chances_too_great'].'</span>';
}
if ($vbulletin->options['adintegrate_refresh_onoff'] == '0')
{
	$adintegrate_refresh_onoff_warning = '<span style="float:right;color:#500;">'.$vbphrase['setting_adintegrate_refresh_is_disabled'].'</span>';
}

// AJAX Adcode Preview
if ($_REQUEST['do'] == 'createad_preview')
{
	if (!empty($_REQUEST['adcode']))
	{
		$output = createad(urldecode($_REQUEST['adcode']));
	}

	if (!empty($output))
	{
		echo $output;
	}
	else
	{
		echo $vbphrase['setting_adintegrate_nothing_to_display'];
	}	
	exit;
}

// AJAX AdCells Preview.
if ($_REQUEST['do'] == 'createadcells_preview')
{
	if (!empty($_REQUEST['adcode']))
	{
		$output = '<table><tr>'.createadcells(urldecode($_REQUEST['adcode'])).'</tr></table>';
	}

	if (!empty($output))
	{
		echo $output;
	}
	else
	{
		echo '<table><tr><td>'.$vbphrase['setting_adintegrate_nothing_to_display'].'</td></tr></table>';
	}	
	exit;
}

if ($_REQUEST['do'] == 'adintegrate_global')
{
	print_cp_header($vbphrase['setting_adintegrate_global']);
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_global" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_global']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], ''), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], '').'</span>', 'setting[adintegrate_onoff]', $vbulletin->options['adintegrate_onoff']);
	
	print_description_row($vbphrase['setting_adintegrate_delimiter_title'], 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_delimiter_desc'].'</span>', 'setting[adintegrate_delimiter]', $vbulletin->options['adintegrate_delimiter'], 0, '1');
	
	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_usergroupids_on_title'], strtolower($vbphrase['setting_adintegrate_advertisements'])), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_usergroupids_on_desc'], strtolower($vbphrase['setting_adintegrate_advertisements'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_usergroupids_on]', $vbulletin->options['adintegrate_usergroupids_on'], 0);	

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_usergroupids_off_title'], strtolower($vbphrase['setting_adintegrate_advertisements'])), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_usergroupids_off_desc'], strtolower($vbphrase['setting_adintegrate_advertisements'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_usergroupids_off]', $vbulletin->options['adintegrate_usergroupids_off']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_forumids_on_title'], strtolower($vbphrase['setting_adintegrate_advertisements'])), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_forumids_on_desc'], strtolower($vbphrase['setting_adintegrate_advertisements'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_forumids_on]', $vbulletin->options['adintegrate_forumids_on']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_forumids_off_title'], strtolower($vbphrase['setting_adintegrate_advertisements'])), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_forumids_off_desc'], strtolower($vbphrase['setting_adintegrate_advertisements'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_forumids_off]', $vbulletin->options['adintegrate_forumids_off']);	

	print_description_row($vbphrase['setting_adintegrate_this_script_off_title'], 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_this_script_off_desc'].'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_thisscript_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_thisscript_note']).'</span></span>', 'setting[adintegrate_this_script_off]', $vbulletin->options['adintegrate_this_script_off'], 5, 33, 0, 0);	

	print_description_row($vbphrase['setting_adintegrate_refresh_onoff_title'], 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_refresh_onoff_desc'].'</span>', 'setting[adintegrate_refresh_onoff]', $vbulletin->options['adintegrate_refresh_onoff']);

	print_description_row($vbphrase['setting_adintegrate_autoinsert_onoff_title'], 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_autoinsert_onoff_desc'].'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_autoinsert_onoff_note']).'</span></span>', 'setting[adintegrate_autoinsert_onoff]', $vbulletin->options['adintegrate_autoinsert_onoff']);

	print_submit_row();
	echo '<br />';
	print_table_start();

	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}




if ($_REQUEST['do'] == 'adintegrate_sharing')
{
	// See what user fields are available, using the admin as base user.
	$adintegrate_userfields = $db->query_first_slave("
		SELECT *
		FROM ".TABLE_PREFIX."userfield
		WHERE userid = '1'
	");

	if (is_array($adintegrate_userfields))
	{
		$adintegrate_userfields = array_keys($adintegrate_userfields);
		
		// Get the text and descriptions for the fields
		$i = '0';
		foreach ($adintegrate_userfields AS $adintegrate_userfielded)
		{
			if (substr($adintegrate_userfielded, 0, 5) == 'field')
			{	
				$adintegrate_userfields_pull .= '\''.$adintegrate_userfielded.'_title\', \''.$adintegrate_userfielded.'_desc\', ';
			}
			else
			{
				unset($adintegrate_userfields["$i"]);
			}
			$i++;
		}
		unset($i);
	}
	
	if (isset($adintegrate_userfields_pull))
	{
		$adintegrate_userfields_pull = substr($adintegrate_userfields_pull, 0, -2);
	
		$phrases = $db->query_read_slave("
			SELECT text
			FROM " . TABLE_PREFIX . "phrase
			WHERE languageid = 0
			AND fieldname = 'cprofilefield'
			AND varname
				IN ($adintegrate_userfields_pull)
			ORDER BY varname
		");
	
		$i = '1';
		while ($phrase = $db->fetch_array($phrases))
		{
			if ($i == '1')
			{
				$field_desc[] = $phrase['text'];
			}
			else if ($i == '2')
			{
				$field_title[] = $phrase['text'];
				$i = '0';
			}
			$i++;
		}	
		unset($i);
	}
	
	print_cp_header($vbphrase['setting_adintegrate_ad_sharing']);
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_sharing" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_ad_sharing']);

	print_description_row($adintegrate_onoff_warning.$adintegrate_sharing_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_sharing_onoff_note']).'</span></span>', 'setting[adintegrate_sharing_onoff]', $vbulletin->options['adintegrate_sharing_onoff']);

	print_description_row($vbphrase['setting_adintegrate_sharing_allowhtml_title'], 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_sharing_allowhtml_desc'].'</span>', 'setting[adintegrate_sharing_allowhtml]', $vbulletin->options['adintegrate_sharing_allowhtml']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_usergroupids_on_title'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_usergroupids_on_desc'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_sharing_usergroupids_on]', $vbulletin->options['adintegrate_sharing_usergroupids_on'], 0);	

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_usergroupids_off_title'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_usergroupids_off_desc'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_sharing_usergroupids_off]', $vbulletin->options['adintegrate_sharing_usergroupids_off']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_forumids_on_title'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_forumids_on_desc'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_sharing_forumids_on]', $vbulletin->options['adintegrate_sharing_forumids_on']);
	
	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_forumids_off_title'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_forumids_off_desc'], strtolower($vbphrase['setting_adintegrate_ad_sharing'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_sharing_forumids_off]', $vbulletin->options['adintegrate_sharing_forumids_off']);

	print_description_row($adintegrate_autoinsert_chances_warning.construct_phrase($vbphrase['setting_adintegrate_sharing_chance_of_x_title'], strtolower($vbphrase['setting_adintegrate_thread_starter'])), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_sharing_chance_of_x_desc'], strtolower($vbphrase['setting_adintegrate_thread_starter'])).'</span>', 'setting[adintegrate_sharing_chanceofthreadstarter]', array(
		'0' => '0%',
		'5' => '5%',
		'10' => '10%',
		'15' => '15%',
		'20' => '20%',
		'25' => '25%',
		'30' => '30%',
		'35' => '35%',
		'40' => '40%',
		'45' => '45%',
		'50' => '50%',
		'55' => '55%',
		'60' => '60%',
		'65' => '65%',
		'70' => '70%',
		'75' => '75%',
		'80' => '80%',
		'85' => '85%',
		'90' => '90%',
		'95' => '95%',
		'100' => '100%'),
		$vbulletin->options['adintegrate_sharing_chanceofthreadstarter'], 0, 1
	);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_sharing_chance_of_x_title'], strtolower($vbphrase['setting_adintegrate_last_poster'])), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_sharing_chance_of_x_desc'], strtolower($vbphrase['setting_adintegrate_last_poster'])).'</span>', 'setting[adintegrate_sharing_chanceoflastposter]', array(
		'0' => '0%',
		'5' => '5%',
		'10' => '10%',
		'15' => '15%',
		'20' => '20%',
		'25' => '25%',
		'30' => '30%',
		'35' => '35%',
		'40' => '40%',
		'45' => '45%',
		'50' => '50%',
		'55' => '55%',
		'60' => '60%',
		'65' => '65%',
		'70' => '70%',
		'75' => '75%',
		'80' => '80%',
		'85' => '85%',
		'90' => '90%',
		'95' => '95%',
		'100' => '100%'),
		$vbulletin->options['adintegrate_sharing_chanceoflastposter'], 0, 1
	);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_sharing_chance_of_x_title'], strtolower($vbphrase['setting_adintegrate_forum_staff'])), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_sharing_chance_of_x_desc'], strtolower($vbphrase['setting_adintegrate_forum_staff'])).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_sharing_chanceofstaff_note']).'</span></span>', 'setting[adintegrate_sharing_chanceofstaff]', array(
		'0' => '0%',
		'5' => '5%',
		'10' => '10%',
		'15' => '15%',
		'20' => '20%',
		'25' => '25%',
		'30' => '30%',
		'35' => '35%',
		'40' => '40%',
		'45' => '45%',
		'50' => '50%',
		'55' => '55%',
		'60' => '60%',
		'65' => '65%',
		'70' => '70%',
		'75' => '75%',
		'80' => '80%',
		'85' => '85%',
		'90' => '90%',
		'95' => '95%',
		'100' => '100%'),
		$vbulletin->options['adintegrate_sharing_chanceofstaff'], 0, 1
	);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_sharing_force_x_title'], strtolower($vbphrase['setting_adintegrate_thread_starter']), $addelimiter), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_sharing_force_x_desc'], strtolower($vbphrase['setting_adintegrate_thread_starter']), $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_sharing_force_threadstarter]', $vbulletin->options['adintegrate_sharing_force_threadstarter'], 0);
	
	print_description_row(construct_phrase($vbphrase['setting_adintegrate_sharing_force_x_title'], strtolower($vbphrase['setting_adintegrate_last_poster']), $addelimiter), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_sharing_force_x_desc'], strtolower($vbphrase['setting_adintegrate_last_poster']), $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_sharing_force_lastposter]', $vbulletin->options['adintegrate_sharing_force_lastposter'], 0);
	
	print_submit_row();
	print_table_footer();
	echo '<br />';
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_sharing" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_sharing_custom_fields_title']);

	print_description_row($vbphrase['setting_adintegrate_custom_fields_title'], 0, 99, 'optiontitle', 'left');
	print_description_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_sharing_custom_fields_desc'].'</span>', 0, 99);
	
	// If the field is empty, it may not exist in the settings db, so create it.
	$vbulletin->db->hide_errors();
	$i = '0';
	foreach ($adintegrate_userfields AS $adintegrate_userfielded)
	{
		$adintegrate_fieldx = 'adintegrate_'.$adintegrate_userfielded;
		
		if (!isset($vbulletin->options["$adintegrate_fieldx"]))
		{
			$db->query_write("
				INSERT INTO ".TABLE_PREFIX."setting
					(varname, grouptitle, value, defaultvalue, optioncode, displayorder, advanced, volatile, datatype, product, validationcode, blacklist)
				VALUES
					('adintegrate_".$adintegrate_userfielded."', 'adintegrate_sharing', '', '', 'textarea', '', '0', '1', 'free', 'adintegrate_rt', '', '0')
			");
		}
		
		print_description_row(ucfirst($adintegrate_userfielded), 0, 99, 'optiontitle', 'left');
		print_textarea_row('<span class="smallfont"><strong>'.$field_title["$i"].'</strong><br /><br />'.$field_desc["$i"].'</span>', 'setting['.$adintegrate_fieldx.']', $vbulletin->options["$adintegrate_fieldx"], 5, 33, 0, 0);
		$i++;
	}
	unset($i);
	$vbulletin->db->show_errors();

	print_submit_row();
	echo '<br />';
	print_table_start();
	
	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}





if ($_REQUEST['do'] == 'adintegrate_headernavbar')
{
	print_cp_header($vbphrase['setting_adintegrate_headernavbar'], '', adcode_preview_js('createad', 'header').adcode_preview_js('createad', 'navbar'));
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_headernavbar" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_header']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_header'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_header'])).'</span>', 'setting[adintegrate_header_onoff]', $vbulletin->options['adintegrate_header_onoff']);

	print_description_row($adintegrate_refresh_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_refresh_title'], $vbphrase['setting_adintegrate_header']), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_refresh_desc'], strtolower($vbphrase['setting_adintegrate_header'])).'</span>', 'setting[adintegrate_header_refresh]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'10' => '10',
		'15' => '15',
		'20' => '20',
		'25' => '25',
		'30' => '30',
		'35' => '35',
		'40' => '40',
		'45' => '45',
		'50' => '50',
		'55' => '55',
		'60' => '60 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '1')),
		$vbulletin->options['adintegrate_header_refresh'], 0, 1
	);	

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_header']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_header']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_header_timescale]', $vbulletin->options['adintegrate_header_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_header']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createad_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="header_adcode_previewing(fetch_object(\'ta_setting[adintegrate_header_adcode]_4\').value); return false;" /> <span id="header_preview_working"></span>', 'setting[adintegrate_header_adcode]', $vbulletin->options['adintegrate_header_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_header_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="header_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_description_row($adintegrate_autoinsert_warning.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_title'], $vbphrase['setting_adintegrate_header']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_desc'], strtolower($vbphrase['setting_adintegrate_header'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_demo'], strtolower($vbphrase['setting_adintegrate_header'])).'</span></span>', 'setting[adintegrate_header_autoinsert]', $vbulletin->options['adintegrate_header_autoinsert'], 5, 33, 0, 0);	

	print_submit_row();
	print_table_footer();
	echo '<br />';
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_headernavbar" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_navbar']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_navbar'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_navbar'])).'</span>', 'setting[adintegrate_navbar_onoff]', $vbulletin->options['adintegrate_navbar_onoff']);

	print_description_row($adintegrate_refresh_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_refresh_title'], $vbphrase['setting_adintegrate_navbar']), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_refresh_desc'], strtolower($vbphrase['setting_adintegrate_navbar'])).'</span>', 'setting[adintegrate_navbar_refresh]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'10' => '10',
		'15' => '15',
		'20' => '20',
		'25' => '25',
		'30' => '30',
		'35' => '35',
		'40' => '40',
		'45' => '45',
		'50' => '50',
		'55' => '55',
		'60' => '60 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '1')),
		$vbulletin->options['adintegrate_navbar_refresh'], 0, 1
	);	

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_navbar']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_navbar']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_navbar_timescale]', $vbulletin->options['adintegrate_navbar_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_navbar']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createad_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="navbar_adcode_previewing(fetch_object(\'ta_setting[adintegrate_navbar_adcode]_9\').value); return false;" /> <span id="navbar_preview_working"></span>', 'setting[adintegrate_navbar_adcode]', $vbulletin->options['adintegrate_navbar_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_navbar_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="navbar_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_description_row($adintegrate_autoinsert_warning.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_title'], $vbphrase['setting_adintegrate_navbar']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_desc'], strtolower($vbphrase['setting_adintegrate_navbar'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_demo'], strtolower($vbphrase['setting_adintegrate_navbar'])).'</span></span>', 'setting[adintegrate_navbar_autoinsert]', $vbulletin->options['adintegrate_navbar_autoinsert'], 5, 33, 0, 0);	

	print_submit_row();
	echo '<br />';
	print_table_start();
	
	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}




if ($_REQUEST['do'] == 'adintegrate_leftright')
{
	print_cp_header($vbphrase['setting_adintegrate_leftright'], '', adcode_preview_js('createad', 'leftcolumn').adcode_preview_js('createad', 'rightcolumn'));
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_leftright" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_left_column']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_left_column'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_left_column'])).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_column_note'], strtolower($vbphrase['setting_adintegrate_left_column'])).'</span></span>', 'setting[adintegrate_leftcolumn_onoff]', $vbulletin->options['adintegrate_leftcolumn_onoff']);

	print_description_row($adintegrate_refresh_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_refresh_title'], $vbphrase['setting_adintegrate_left_column']), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_refresh_desc'], strtolower($vbphrase['setting_adintegrate_left_column'])).'</span>', 'setting[adintegrate_leftcolumn_refresh]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'10' => '10',
		'15' => '15',
		'20' => '20',
		'25' => '25',
		'30' => '30',
		'35' => '35',
		'40' => '40',
		'45' => '45',
		'50' => '50',
		'55' => '55',
		'60' => '60 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '1')),
		$vbulletin->options['adintegrate_leftcolumn_refresh'], 0, 1
	);	
	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_left_column']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_left_column']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_leftcolumn_timescale]', $vbulletin->options['adintegrate_leftcolumn_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_left_column']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createad_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="leftcolumn_adcode_previewing(fetch_object(\'ta_setting[adintegrate_leftcolumn_adcode]_4\').value); return false;" /> <span id="leftcolumn_preview_working"></span>', 'setting[adintegrate_leftcolumn_adcode]', $vbulletin->options['adintegrate_leftcolumn_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_leftcolumn_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="leftcolumn_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_submit_row();
	print_table_footer();
	echo '<br />';
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_leftright" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_right_column']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_right_column'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_rightcolumn_archive'])).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_column_note'], strtolower($vbphrase['setting_adintegrate_right_column'])).'</span></span>', 'setting[adintegrate_rightcolumn_onoff]', $vbulletin->options['adintegrate_rightcolumn_onoff']);

	print_description_row($adintegrate_refresh_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_refresh_title'], $vbphrase['setting_adintegrate_right_column']), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_refresh_desc'], strtolower($vbphrase['setting_adintegrate_right_column'])).'</span>', 'setting[adintegrate_rightcolumn_refresh]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'10' => '10',
		'15' => '15',
		'20' => '20',
		'25' => '25',
		'30' => '30',
		'35' => '35',
		'40' => '40',
		'45' => '45',
		'50' => '50',
		'55' => '55',
		'60' => '60 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '1')),
		$vbulletin->options['adintegrate_rightcolumn_refresh'], 0, 1
	);	

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_right_column']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_right_column']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_rightcolumn_timescale]', $vbulletin->options['adintegrate_rightcolumn_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_right_column']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createad_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="rightcolumn_adcode_previewing(fetch_object(\'ta_setting[adintegrate_rightcolumn_adcode]_7\').value); return false;" /> <span id="rightcolumn_preview_working"></span>', 'setting[adintegrate_rightcolumn_adcode]', $vbulletin->options['adintegrate_rightcolumn_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_rightcolumn_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="rightcolumn_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_submit_row();
	echo '<br />';
	print_table_start();
	
	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}




if ($_REQUEST['do'] == 'adintegrate_postbit')
{
	print_cp_header($vbphrase['setting_adintegrate_postbit'], '', adcode_preview_js('createad', 'postbit'));
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_postbit" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_postbit']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_announcements'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_announcements'])).'</span>', 'setting[adintegrate_announcements_onoff]', $vbulletin->options['adintegrate_announcements_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_pms'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_pms'])).'</span>', 'setting[adintegrate_pms_onoff]', $vbulletin->options['adintegrate_pms_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_posts'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_posts'])).'</span>', 'setting[adintegrate_posts_onoff]', $vbulletin->options['adintegrate_posts_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_search_posts'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_search_posts'])).'</span>', 'setting[adintegrate_search_posts_onoff]', $vbulletin->options['adintegrate_search_posts_onoff']);
	
	print_description_row(construct_phrase($vbphrase['setting_adintegrate_blend_x_template_title'], strtolower($vbphrase['setting_adintegrate_postbit'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_blend_x_template_desc'], strtolower($vbphrase['setting_adintegrate_postbit'])).'</span>', 'setting[adintegrate_postbit_blend_onoff]', $vbulletin->options['adintegrate_postbit_blend_onoff']);		

	print_description_row($adintegrate_refresh_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_refresh_title'], $vbphrase['setting_adintegrate_postbit']), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_refresh_desc'], strtolower($vbphrase['setting_adintegrate_postbit'])).'</span>', 'setting[adintegrate_postbit_refresh]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'10' => '10',
		'15' => '15',
		'20' => '20',
		'25' => '25',
		'30' => '30',
		'35' => '35',
		'40' => '40',
		'45' => '45',
		'50' => '50',
		'55' => '55',
		'60' => '60 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '1')),
		$vbulletin->options['adintegrate_postbit_refresh'], 0, 1
	);	
	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_postbit']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_postbit']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_postbit_timescale]', $vbulletin->options['adintegrate_postbit_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_postbit']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createad_demo'], $addelimiter).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span></span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="postbit_adcode_previewing(fetch_object(\'ta_setting[adintegrate_postbit_adcode]_8\').value); return false;" /> <span id="postbit_preview_working"></span></span>', 'setting[adintegrate_postbit_adcode]', $vbulletin->options['adintegrate_postbit_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_postbit_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="postbit_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_description_row($adintegrate_autoinsert_warning.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_title'], $vbphrase['setting_adintegrate_postbit']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_desc'], strtolower($vbphrase['setting_adintegrate_postbit'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_demo'], strtolower($vbphrase['setting_adintegrate_postbit'])).'</span></span>', 'setting[adintegrate_postbit_autoinsert]', $vbulletin->options['adintegrate_postbit_autoinsert'], 5, 33, 0, 0);
	
	print_description_row($vbphrase['setting_adintegrate_minpostcount_title'], 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_minpostcount_desc'].'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_disabled_on_search_note']).'</span></span>', 'setting[adintegrate_minpostcount]', array(
		'' => '1 '.$vbphrase['setting_adintegrate_minpostcount_nouse'],
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'10' => '10',
		'11' => '11',
		'12' => '12',
		'13' => '13',
		'14' => '14',
		'15' => '15',
		'16' => '16',
		'17' => '17',
		'18' => '18',
		'19' => '19',
		'20' => '20',
		'21' => '21',
		'22' => '22',
		'23' => '23',
		'24' => '24',
		'25' => '25',
		'26' => '26',
		'27' => '27',
		'28' => '28',
		'29' => '29',
		'30' => '30',
		'31' => '31',
		'32' => '32',
		'33' => '33',
		'34' => '34',
		'35' => '35',
		'36' => '36',
		'37' => '37',
		'38' => '38',
		'39' => '39',
		'40' => '40',
		'41' => '41',
		'42' => '42',
		'43' => '43',
		'44' => '44',
		'45' => '45',
		'46' => '46',
		'47' => '47',
		'48' => '48',
		'49' => '49',
		'50' => '50'	
		),
		$vbulletin->options['adintegrate_minpostcount'], 0, 1
	);	

	print_description_row($vbphrase['setting_adintegrate_firstpost_title'], 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_firstpost_desc'].'</span>', 'setting[adintegrate_firstpost]', $vbulletin->options['adintegrate_firstpost']);
	
	print_description_row($vbphrase['setting_adintegrate_lastpost_title'], 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_lastpost_desc'].'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_disabled_on_search_note']).'</span></span>', 'setting[adintegrate_lastpost]', $vbulletin->options['adintegrate_lastpost']);	

	print_description_row($vbphrase['setting_adintegrate_postcountrepeat_title'], 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_postcountrepeat_desc'].'</span>', 'setting[adintegrate_postcountrepeat]', array(
		'' => '0 '.$vbphrase['setting_adintegrate_postcountrepeat_nouse'].'',
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'10' => '10',
		'11' => '11',
		'12' => '12',
		'13' => '13',
		'14' => '14',
		'15' => '15',
		'16' => '16',
		'17' => '17',
		'18' => '18',
		'19' => '19',
		'20' => '20',
		'21' => '21',
		'22' => '22',
		'23' => '23',
		'24' => '24',
		'25' => '25',
		'26' => '26',
		'27' => '27',
		'28' => '28',
		'29' => '29',
		'30' => '30',
		'31' => '31',
		'32' => '32',
		'33' => '33',
		'34' => '34',
		'35' => '35',
		'36' => '36',
		'37' => '37',
		'38' => '38',
		'39' => '39',
		'40' => '40'),
		$vbulletin->options['adintegrate_postcountrepeat'], 0, 1
	);	
	
	print_description_row($vbphrase['setting_adintegrate_xpostonly_title'], 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_xpostonly_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_xpostonly]', $vbulletin->options['adintegrate_xpostonly']);

	print_submit_row();
	echo '<br />';
	print_table_start();
	
	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}




if ($_REQUEST['do'] == 'adintegrate_forumbitthreadbit')
{
	print_cp_header($vbphrase['setting_adintegrate_forumbitnavbar'], '', adcode_preview_js('createad', 'forumbit').adcode_preview_js('createad', 'threadbit'));
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_forumbitthreadbit" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_forumbit']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_forumbit'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_forumbit'])).'</span>', 'setting[adintegrate_forumbit_onoff]', $vbulletin->options['adintegrate_forumbit_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_blend_x_template_title'], strtolower($vbphrase['setting_adintegrate_forumbit'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_blend_x_template_desc'], strtolower($vbphrase['setting_adintegrate_forumbit'])).'</span>', 'setting[adintegrate_forumbit_blend_onoff]', $vbulletin->options['adintegrate_forumbit_blend_onoff']);

	print_description_row($adintegrate_refresh_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_refresh_title'], $vbphrase['setting_adintegrate_forumbit']), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_refresh_desc'], strtolower($vbphrase['setting_adintegrate_forumbit'])).'</span>', 'setting[adintegrate_forumbit_refresh]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'10' => '10',
		'15' => '15',
		'20' => '20',
		'25' => '25',
		'30' => '30',
		'35' => '35',
		'40' => '40',
		'45' => '45',
		'50' => '50',
		'55' => '55',
		'60' => '60 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '1')),
		$vbulletin->options['adintegrate_forumbit_refresh'], 0, 1
	);	
	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_forumbit']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_forumbit']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_forumbit_timescale]', $vbulletin->options['adintegrate_forumbit_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_forumbit']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createad_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="forumbit_adcode_previewing(fetch_object(\'ta_setting[adintegrate_forumbit_adcode]_5\').value); return false;" /> <span id="forumbit_preview_working"></span>', 'setting[adintegrate_forumbit_adcode]', $vbulletin->options['adintegrate_forumbit_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_forumbit_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="forumbit_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_description_row($adintegrate_autoinsert_warning.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_title'], $vbphrase['setting_adintegrate_forumbit']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_desc'], strtolower($vbphrase['setting_adintegrate_forumbit'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_demo'], strtolower($vbphrase['setting_adintegrate_forumbit'])).'</span></span>', 'setting[adintegrate_forumbit_autoinsert]', $vbulletin->options['adintegrate_forumbit_autoinsert'], 5, 33, 0, 0);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_forumbit_forumids_on_title']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_forumbit_forumids_on_desc']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_forumbit_forumids_on]', $vbulletin->options['adintegrate_forumbit_forumids_on']);

	print_submit_row();
	print_table_footer();
	echo '<br />';
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_forumbitthreadbit" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_threadbit']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_threadbit'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_threadbit_archive'])).'</span>', 'setting[adintegrate_threadbit_onoff]', $vbulletin->options['adintegrate_threadbit_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_search_threads'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_search_threads'])).'</span>', 'setting[adintegrate_search_threads_onoff]', $vbulletin->options['adintegrate_search_threads_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_blend_x_template_title'], strtolower($vbphrase['setting_adintegrate_threadbit'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_blend_x_template_desc'], strtolower($vbphrase['setting_adintegrate_threadbit'])).'</span>', 'setting[adintegrate_threadbit_blend_onoff]', $vbulletin->options['adintegrate_threadbit_blend_onoff']);

	print_description_row($adintegrate_refresh_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_refresh_title'], $vbphrase['setting_adintegrate_threadbit']), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_refresh_desc'], strtolower($vbphrase['setting_adintegrate_threadbit'])).'</span>', 'setting[adintegrate_threadbit_refresh]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'10' => '10',
		'15' => '15',
		'20' => '20',
		'25' => '25',
		'30' => '30',
		'35' => '35',
		'40' => '40',
		'45' => '45',
		'50' => '50',
		'55' => '55',
		'60' => '60 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '1')),
		$vbulletin->options['adintegrate_threadbit_refresh'], 0, 1
	);	

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_threadbit']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_threadbit']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_threadbit_timescale]', $vbulletin->options['adintegrate_threadbit_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_threadbit']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createad_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="threadbit_adcode_previewing(fetch_object(\'ta_setting[adintegrate_threadbit_adcode]_13\').value); return false;" /> <span id="threadbit_preview_working"></span>', 'setting[adintegrate_threadbit_adcode]', $vbulletin->options['adintegrate_threadbit_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_threadbit_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="threadbit_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_description_row($adintegrate_autoinsert_warning.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_title'], $vbphrase['setting_adintegrate_threadbit']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_desc'], strtolower($vbphrase['setting_adintegrate_threadbit'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_demo'], strtolower($vbphrase['setting_adintegrate_threadbit'])).'</span></span>', 'setting[adintegrate_threadbit_autoinsert]', $vbulletin->options['adintegrate_threadbit_autoinsert'], 5, 33, 0, 0);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_threadcountrepeat_title']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_threadcountrepeat_desc']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_ids_demo'], $addelimiter).'</span></span>', 'setting[adintegrate_threadcountrepeat]', $vbulletin->options['adintegrate_threadcountrepeat']);

	print_submit_row();
	echo '<br />';
	print_table_start();
	
	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}




if ($_REQUEST['do'] == 'adintegrate_footersponsors')
{
	print_cp_header($vbphrase['setting_adintegrate_footersponsors'], '', adcode_preview_js('createad', 'footer').adcode_preview_js('createadcells', 'sponsors'));
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_footersponsors" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_footer']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_footer'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_footer'])).'</span>', 'setting[adintegrate_footer_onoff]', $vbulletin->options['adintegrate_footer_onoff']);

	print_description_row($adintegrate_refresh_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_refresh_title'], $vbphrase['setting_adintegrate_footer']), 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_refresh_desc'], strtolower($vbphrase['setting_adintegrate_footer'])).'</span>', 'setting[adintegrate_footer_refresh]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'10' => '10',
		'15' => '15',
		'20' => '20',
		'25' => '25',
		'30' => '30',
		'35' => '35',
		'40' => '40',
		'45' => '45',
		'50' => '50',
		'55' => '55',
		'60' => '60 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '1')),
		$vbulletin->options['adintegrate_footer_refresh'], 0, 1
	);	
	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_footer']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_footer']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_footer_timescale]', $vbulletin->options['adintegrate_footer_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_footer']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createad_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="footer_adcode_previewing(fetch_object(\'ta_setting[adintegrate_footer_adcode]_4\').value); return false;" /> <span id="footer_preview_working"></span>', 'setting[adintegrate_footer_adcode]', $vbulletin->options['adintegrate_footer_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_footer_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="footer_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_description_row($adintegrate_autoinsert_warning.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_title'], $vbphrase['setting_adintegrate_footer']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_desc'], strtolower($vbphrase['setting_adintegrate_footer'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_demo'], strtolower($vbphrase['setting_adintegrate_footer'])).'</span></span>', 'setting[adintegrate_footer_autoinsert]', $vbulletin->options['adintegrate_footer_autoinsert'], 5, 33, 0, 0);	
	
	print_submit_row();
	print_table_footer();
	echo '<br />';
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_footersponsors" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_sponsors']);
	
	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_sponsors'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_sponsors'])).'</span>', 'setting[adintegrate_sponsors_onoff]', $vbulletin->options['adintegrate_sponsors_onoff']);

	print_description_row($vbphrase['setting_adintegrate_sponsors_rows_title'], 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_sponsors_rows_desc'].'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_sponsors_rows_note']).'</span></span>', 'setting[adintegrate_sponsors_rows]', array(
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'10' => '10'
		),
		$vbulletin->options['adintegrate_sponsors_rows'], 0, 1
	);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_sponsors']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createadcells_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createadcells_demo'], $addelimiter).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="sponsors_adcode_previewing(fetch_object(\'ta_setting[adintegrate_sponsors_adcode]_8\').value); return false;" /> <span id="sponsors_preview_working"></span>', 'setting[adintegrate_sponsors_adcode]', $vbulletin->options['adintegrate_sponsors_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createadcells($vbulletin->options['adintegrate_sponsors_adcode']);
	if (strip_tags($adcode) == '')
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="sponsors_adcode_contents"><table><tr>'.$adcode.'</tr></table></span>', 0, 99, 'alt2', 'center');
	
	print_description_row($adintegrate_autoinsert_warning.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_title'], $vbphrase['setting_adintegrate_sponsors']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_desc'], strtolower($vbphrase['setting_adintegrate_sponsors'])).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_demo'], strtolower($vbphrase['setting_adintegrate_sponsors'])).'</span></span>', 'setting[adintegrate_sponsors_autoinsert]', $vbulletin->options['adintegrate_sponsors_autoinsert'], 5, 33, 0, 0);		

	print_submit_row();
	echo '<br />';
	print_table_start();
	
	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}




if ($_REQUEST['do'] == 'adintegrate_fullpage')
{
	print_cp_header($vbphrase['setting_adintegrate_fullpage'], '', adcode_preview_js('createad', 'fullpage'));
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_fullpage" />';
	print_table_start();	
	print_table_header($vbphrase['setting_adintegrate_fullpage']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_fullpage'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_fullpage'])).'</span>', 'setting[adintegrate_fullpage_onoff]', $vbulletin->options['adintegrate_fullpage_onoff']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_fullpage_arrival_title']), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_fullpage_arrival_desc']).'</span>', 'setting[adintegrate_fullpage_arrival]', $vbulletin->options['adintegrate_fullpage_arrival']);

	print_description_row($vbphrase['setting_adintegrate_fullpage_timeout_title'], 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_fullpage_timeout_desc'].'</span>', 'setting[adintegrate_fullpage_timeout]', array(
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'10' => '10',
		'11' => '11',
		'12' => '12',
		'13' => '13',
		'14' => '14',
		'15' => '15',
		'16' => '16',
		'17' => '17',
		'18' => '18',
		'19' => '19',
		'20' => '20',
		'21' => '21',
		'22' => '22',
		'23' => '23',
		'24' => '24',
		'25' => '25',
		'26' => '26',
		'27' => '27',
		'28' => '28',
		'29' => '29',
		'30' => '30',
		'31' => '31',
		'32' => '32',
		'33' => '33',
		'34' => '34',
		'35' => '35',
		'36' => '36',
		'37' => '37',
		'38' => '38',
		'39' => '39',
		'40' => '40',
		'41' => '41',
		'42' => '42',
		'43' => '43',
		'44' => '44',
		'45' => '45',
		'46' => '46',
		'47' => '47',
		'48' => '48',
		'49' => '49',
		'50' => '50',
		'51' => '51',
		'52' => '52',
		'53' => '53',
		'54' => '54',
		'55' => '55',
		'56' => '56',
		'57' => '57',
		'58' => '58',
		'59' => '59',
		'60' => '60'	
		),
		$vbulletin->options['adintegrate_fullpage_timeout'], 0, 1
	);

	print_description_row($vbphrase['setting_adintegrate_fullpage_timetoad_title'], 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_fullpage_timetoad_desc'].'</span>', 'setting[adintegrate_fullpage_timetoad]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'120' => '120 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '2'),
		'130' => '130',
		'1400' => '140',
		'1500' => '150',
		'160' => '160',
		'170' => '170',
		'180' => '180 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '3'),
		'190' => '190',
		'200' => '200',
		'210' => '210',
		'220' => '220',
		'230' => '230',
		'240' => '240 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '4'),
		'250' => '250',
		'260' => '260',
		'270' => '270',
		'280' => '280',
		'290' => '290',
		'300' => '300 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '5'),
		'310' => '310',
		'320' => '320',
		'330' => '330',
		'340' => '340',
		'350' => '350',
		'360' => '360 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '6'),
		'370' => '370',
		'380' => '380',
		'390' => '390',
		'400' => '400',
		'410' => '410',
		'420' => '420 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '7'),
		'430' => '430',
		'440' => '440',
		'450' => '450',
		'460' => '460',
		'470' => '470',
		'480' => '480 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '8'),
		'490' => '490',
		'500' => '500',
		'510' => '510',
		'520' => '520',
		'530' => '530',
		'540' => '540 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '9'),
		'550' => '550',
		'560' => '560',
		'570' => '570',
		'580' => '580',
		'590' => '590',
		'600' => '600 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '10'),
		'610' => '10',
		'620' => '620',
		'630' => '630',
		'640' => '640',
		'650' => '650',
		'660' => '660 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '11'),
		'670' => '670',
		'680' => '680',
		'690' => '690',
		'700' => '700',
		'710' => '710',
		'720' => '720 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '12'),
		'730' => '730',
		'740' => '740',
		'750' => '750',
		'760' => '760',
		'770' => '770',
		'780' => '780 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '13'),
		'790' => '790',
		'800' => '800',
		'810' => '810',
		'820' => '820',
		'830' => '830',
		'840' => '840 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '14'),
		'850' => '850',
		'860' => '860',
		'870' => '870',
		'880' => '880',
		'890' => '890',
		'900' => '900 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '15')		
		),
		$vbulletin->options['adintegrate_fullpage_timetoad'], 0, 1
	);

	print_description_row($vbphrase['setting_adintegrate_fullpage_pageviewstoad_title'], 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_fullpage_pageviewstoad_desc'].'</span>', 'setting[adintegrate_fullpage_pageviewstoad]', array(
		'0' => $vbphrase['setting_adintegrate_disabled'],
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'10' => '10',
		'11' => '11',
		'12' => '12',
		'13' => '13',
		'14' => '14',
		'15' => '15',
		'16' => '16',
		'17' => '17',
		'18' => '18',
		'19' => '19',
		'20' => '20',
		'21' => '21',
		'22' => '22',
		'23' => '23',
		'24' => '24',
		'25' => '25',
		'26' => '26',
		'27' => '27',
		'28' => '28',
		'29' => '29',
		'30' => '30',
		'31' => '31',
		'32' => '32',
		'33' => '33',
		'34' => '34',
		'35' => '35',
		'36' => '36',
		'37' => '37',
		'38' => '38',
		'39' => '39',
		'40' => '40',
		'41' => '41',
		'42' => '42',
		'43' => '43',
		'44' => '44',
		'45' => '45',
		'46' => '46',
		'47' => '47',
		'48' => '48',
		'49' => '49',
		'50' => '50'	
		),
		$vbulletin->options['adintegrate_fullpage_pageviewstoad'], 0, 1
	);
	
	print_description_row($vbphrase['setting_adintegrate_fullpage_thisscript_title'], 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_fullpage_thisscript_desc'].'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_thisscript_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_thisscript_note']).'</span></span>', 'setting[adintegrate_fullpage_thisscript]', $vbulletin->options['adintegrate_fullpage_thisscript'], 5, 33, 0, 0);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_fullpage']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_createad_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="fullpage_adcode_previewing(fetch_object(\'ta_setting[adintegrate_fullpage_adcode]_7\').value); return false;" /> <span id="fullpage_preview_working"></span>', 'setting[adintegrate_fullpage_adcode]', $vbulletin->options['adintegrate_fullpage_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_fullpage_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="fullpage_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_submit_row();
	echo '<br />';
	print_table_start();
	
	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}




if ($_REQUEST['do'] == 'adintegrate_archive')
{
	print_cp_header($vbphrase['setting_adintegrate_archive'], '', adcode_preview_js('createad', 'header_archive').adcode_preview_js('createad', 'footer_archive').adcode_preview_js('createad', 'leftcolumn_archive').adcode_preview_js('createad', 'rightcolumn_archive'));
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_archive" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_header_archive']);	
	
	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_header_archive'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_header_archive'])).'</span>', 'setting[adintegrate_header_archive_onoff]', $vbulletin->options['adintegrate_header_archive_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_header_archive']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_header_archive']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_header_archive_timescale]', $vbulletin->options['adintegrate_header_archive_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_header_archive']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_archive_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="header_archive_adcode_previewing(fetch_object(\'ta_setting[adintegrate_header_archive_adcode]_3\').value); return false;" /> <span id="header_archive_preview_working"></span>', 'setting[adintegrate_header_archive_adcode]', $vbulletin->options['adintegrate_header_archive_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_header_archive_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="header_archive_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_submit_row();
	print_table_footer();
	echo '<br />';
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_archive" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_footer_archive']);

	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_footer_archive'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_footer_archive'])).'</span>', 'setting[adintegrate_footer_archive_onoff]', $vbulletin->options['adintegrate_footer_archive_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_footer_archive']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_footer_archive']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_footer_archive_timescale]', $vbulletin->options['adintegrate_footer_archive_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_footer_archive']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_archive_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="footer_archive_adcode_previewing(fetch_object(\'ta_setting[adintegrate_footer_archive_adcode]_6\').value); return false;" /> <span id="footer_archive_preview_working"></span>', 'setting[adintegrate_footer_archive_adcode]', $vbulletin->options['adintegrate_footer_archive_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_footer_archive_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="footer_archive_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_submit_row();
	print_table_footer();
	echo '<br />';
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_archive" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_leftcolumn_archive']);	
	
	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_leftcolumn_archive'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_leftcolumn_archive'])).'</span>', 'setting[adintegrate_leftcolumn_archive_onoff]', $vbulletin->options['adintegrate_leftcolumn_archive_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_leftcolumn_archive']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_leftcolumn_archive']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_leftcolumn_archive_timescale]', $vbulletin->options['adintegrate_leftcolumn_archive_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_leftcolumn_archive']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_archive_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="leftcolumn_archive_adcode_previewing(fetch_object(\'ta_setting[adintegrate_leftcolumn_archive_adcode]_9\').value); return false;" /> <span id="leftcolumn_archive_preview_working"></span>', 'setting[adintegrate_leftcolumn_archive_adcode]', $vbulletin->options['adintegrate_leftcolumn_archive_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_leftcolumn_archive_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="leftcolumn_archive_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_submit_row();
	print_table_footer();
	echo '<br />';
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_archive" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_rightcolumn_archive']);	
	
	print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_rightcolumn_archive'])), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_rightcolumn_archive'])).'</span>', 'setting[adintegrate_rightcolumn_archive_onoff]', $vbulletin->options['adintegrate_rightcolumn_archive_onoff']);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_rightcolumn_archive']), 0, 99, 'optiontitle', 'left');
	print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_rightcolumn_archive']).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_rightcolumn_archive_timescale]', $vbulletin->options['adintegrate_rightcolumn_archive_timescale'], 0, 40);

	print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_rightcolumn_archive']), 0, 99, 'optiontitle', 'left');
	print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_archive_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="rightcolumn_archive_adcode_previewing(fetch_object(\'ta_setting[adintegrate_rightcolumn_archive_adcode]_12\').value); return false;" /> <span id="rightcolumn_archive_preview_working"></span>', 'setting[adintegrate_rightcolumn_archive_adcode]', $vbulletin->options['adintegrate_rightcolumn_archive_adcode'], 10, 50, 0, 1);	
	print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
	$adcode = createad($vbulletin->options['adintegrate_rightcolumn_archive_adcode']);
	if (empty($adcode))
	{
		$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
	}
	print_description_row('<span id="rightcolumn_archive_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');

	print_submit_row();
	echo '<br />';
	print_table_start();
	
	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}




if ($_REQUEST['do'] == 'adintegrate_custom')
{
	// See what user fields are available, using the admin as base user.
	$vbulletin->db->hide_errors();
	$adintegrate_customs = $db->query_read_slave("
		SELECT varname
		FROM ".TABLE_PREFIX."setting
		WHERE varname LIKE = 'adintegrate_custom%'
	");
	$vbulletin->db->show_errors();
	
	$templates = $db->query_read_slave("
		SELECT title
		FROM ".TABLE_PREFIX."template
		WHERE templatetype = 'template'
		AND title NOT LIKE '%bit%'
	");
	while ($template = $vbulletin->db->fetch_array($templates))
	{
		$template_array["$template[title]"] = $template['title'];
	}	
	$vbulletin->db->show_errors();
	
	$i = '1';
	while ($i != ($vbulletin->options['adintegrate_custom_count'] + 1))
	{
		$custom_js .= adcode_preview_js('createad', "custom$i");
		$i++;
	}
	unset($i);
	print_cp_header($vbphrase['setting_adintegrate_custom'], '', $custom_js);
	print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
	echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_custom" />';
	print_table_start();
	print_table_header($vbphrase['setting_adintegrate_custom']);
	
	print_description_row($vbphrase['setting_adintegrate_custom_count_title'], 0, 99, 'optiontitle', 'left');
	print_select_row('<span class="smallfont">'.$vbphrase['setting_adintegrate_custom_count_desc'].'</span>', 'setting[adintegrate_custom_count]', array(
		'' => '0',
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'10' => '10',
		'11' => '11',
		'12' => '12',
		'13' => '13',
		'14' => '14',
		'15' => '15',
		'16' => '16',
		'17' => '17',
		'18' => '18',
		'19' => '19',
		'20' => '20',
		'21' => '21',
		'22' => '22',
		'23' => '23',
		'24' => '24',
		'25' => '25'	
		),
		$vbulletin->options['adintegrate_custom_count'], 0, 1
	);
	
	print_description_row(construct_phrase($vbphrase['setting_adintegrate_custom_delete_title']), 0, 99, 'optiontitle', 'left');
	print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_custom_delete_desc']).'</span>', 'setting[adintegrate_custom_delete]', '0');		

	if ($vbulletin->options['adintegrate_custom_count'] < '1')
	{
		print_submit_row();
		echo '<br />';
		print_table_start();
		
		print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
		print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
		print_table_footer();
		print_cp_footer();
	}
	else
	{
		print_submit_row();
		print_table_footer();
		echo '<br />';

		// Start at 1, Custom Adcode 0 sounds crummy.
		$i = '1';
		// Count for the javascript, which needs to know how many options have been on the page so far.
		$j = '3';
		while ($i != ($vbulletin->options['adintegrate_custom_count'] + 1))
		{
			$customx = 'custom'.$i;
			$setting_adintegrate_customx = 'setting_adintegrate_custom'.$i;
			$adintegrate_customx_onoff = 'adintegrate_custom'.$i.'_onoff';
			$adintegrate_customx_refresh = 'adintegrate_custom'.$i.'_refresh';
			$adintegrate_customx_timescale = 'adintegrate_custom'.$i.'_timescale';
			$adintegrate_customx_adcode = 'adintegrate_custom'.$i.'_adcode';			
			$adintegrate_customx_autoinsert = 'adintegrate_custom'.$i.'_autoinsert';
			$adintegrate_customx_template = 'adintegrate_custom'.$i.'_template';
			
			// Create the block if it doesn't exist yet.
			if (!isset($vbulletin->options["$adintegrate_customx_onoff"]))
			{
				$vbulletin->db->hide_errors();
				$db->query_write("
					INSERT INTO ".TABLE_PREFIX."setting
						(varname, grouptitle, value, defaultvalue, optioncode, displayorder, advanced, volatile, datatype, product, validationcode, blacklist)
					VALUES
						('".$adintegrate_customx_onoff."', 'adintegrate_global', '0', '', 'yesno', '', '0', '1', 'boolean', 'adintegrate_rt', '', '0'),
						('".$adintegrate_customx_refresh."', 'adintegrate_global', '0', '', 'yesno', '', '0', '1', 'boolean', 'adintegrate_rt', '', '0'),
						('".$adintegrate_customx_timescale."', 'adintegrate_global', '', '', '', '', '0', '1', 'free', 'adintegrate_rt', '', '0'),
						('".$adintegrate_customx_adcode."', 'adintegrate_global', '', '', 'textarea', '', '0', '1', 'free', 'adintegrate_rt', '', '0'),
						('".$adintegrate_customx_autoinsert."', 'adintegrate_global', '', '', 'textarea', '', '0', '1', 'free', 'adintegrate_rt', '', '0'),
						('".$adintegrate_customx_template."', 'adintegrate_global', '', '', '', '', '0', '1', 'free', 'adintegrate_rt', '', '0')
				");
				$vbulletin->db->show_errors();
			}

			print_form_header('options', 'dooptions', 0, 0, 'optionsform', '100%', 0, 'post');
			echo '<input type="hidden" name="adintegrate_redirect" value="adintegrate_custom" />';
			print_table_start();
			print_table_header($vbphrase['setting_adintegrate_custom'].' '.$i);				
			
			print_description_row($adintegrate_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_onoff_title'], strtolower($vbphrase['setting_adintegrate_custom'].' '.$i)), 0, 99, 'optiontitle', 'left');
			print_yes_no_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_onoff_desc'], strtolower($vbphrase['setting_adintegrate_custom'].' '.$i)).'</span>', 'setting[adintegrate_'.$customx.'_onoff]', $vbulletin->options["$adintegrate_customx_onoff"]);
			$j++;
			
			print_description_row($adintegrate_refresh_onoff_warning.construct_phrase($vbphrase['setting_adintegrate_x_refresh_title'], $vbphrase['setting_adintegrate_custom'].' '.$i), 0, 99, 'optiontitle', 'left');
			print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_refresh_desc'], strtolower($vbphrase['setting_adintegrate_custom'].' '.$i)).'</span>', 'setting[adintegrate_'.$customx.'_refresh]', array(
				'0' => $vbphrase['setting_adintegrate_disabled'],
				'10' => '10',
				'15' => '15',
				'20' => '20',
				'25' => '25',
				'30' => '30',
				'35' => '35',
				'40' => '40',
				'45' => '45',
				'50' => '50',
				'55' => '55',
				'60' => '60 '.construct_phrase($vbphrase['setting_adintegrate_x_minutes'], '1')),
				$vbulletin->options["$adintegrate_customx_refresh"], 0, 1
			);
			$j++;	
			
			print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_timescale_title'], $vbphrase['setting_adintegrate_custom'].' '.$i), 0, 99, 'optiontitle', 'left');
			print_input_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_desc'], $vbphrase['setting_adintegrate_custom'].' '.$i).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_demo'], $addelimiter).'</span><br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_x_timescale_note']).'</span></span>', 'setting[adintegrate_'.$customx.'_timescale]', $vbulletin->options["$adintegrate_customx_timescale"], 0, 40);
			$j++;
		
			print_description_row(construct_phrase($vbphrase['setting_adintegrate_createad_title'], $vbphrase['setting_adintegrate_custom'].' '.$i), 0, 99, 'optiontitle', 'left');
			print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_createad_desc'], $addelimiter).'<br /><br /><span class="col-g" style="font-style:italic;">'.construct_phrase($vbphrase['setting_adintegrate_createad_note']).'</span><br /><br /><input type="button" value="'.$vbphrase['setting_adintegrate_update_preview'].'" onclick="'.$customx.'_adcode_previewing(fetch_object(\'ta_setting[adintegrate_'.$customx.'_adcode]_'.$j.'\').value); return false;" /> <span id="'.$customx.'_preview_working"></span>', 'setting[adintegrate_'.$customx.'_adcode]', $vbulletin->options["$adintegrate_customx_adcode"], 10, 50, 0, 1);	
			print_description_row($vbphrase['setting_adintegrate_adcode_preview'], 0, 99, 'optiontitle', 'center');
			if (!empty($vbulletin->options["$adintegrate_customx_adcode"]))
			{
				$adcode = createad($vbulletin->options["$adintegrate_customx_adcode"]);
			}
			else
			{
				$adcode = $vbphrase['setting_adintegrate_nothing_to_display'];
			}
			print_description_row('<span id="'.$customx.'_adcode_contents">'.$adcode.'</span>', 0, 99, 'alt1', 'center');			
			$j++;

			print_description_row(construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_template_title'], $vbphrase['setting_adintegrate_custom'].' '.$i), 0, 99, 'optiontitle', 'left');
			print_select_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_template_desc'], strtolower($vbphrase['setting_adintegrate_custom'].' '.$i)).'</span>', 'setting['.$adintegrate_customx_template.']',
				$template_array,
				$vbulletin->options["$adintegrate_customx_template"], 0, 1
			);
			$j++;	
			
			print_description_row($adintegrate_autoinsert_warning.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_title'], $vbphrase['setting_adintegrate_custom'].' '.$i), 0, 99, 'optiontitle', 'left');
			print_textarea_row('<span class="smallfont">'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_desc'], strtolower($vbphrase['setting_adintegrate_custom'].'_'.$i)).'<br /><br /><span class="col-i">'.$vbphrase['setting_adintegrate_examples'].'<br />'.construct_phrase($vbphrase['setting_adintegrate_x_autoinsert_demo'], strtolower($vbphrase['setting_adintegrate_custom'].$i)).'</span></span>', 'setting[adintegrate_'.$customx.'_autoinsert]', $vbulletin->options["$adintegrate_customx_autoinsert"], 5, 33, 0, 0);		
			$j++;

			print_submit_row();
			echo '<br />';
			print_table_start();
	
			$i++;
		}
		unset($i);
	}
	
	print_description_row($vbphrase['setting_adintegrate_help'], 0, 99, 'optiontitle', 'left');
	print_description_row('<u><a href="http://redtyger.co.uk/manual.php?product=vB+Ad+Management&amp;version='.urlencode($adintegrate_rt['version']).'" target="_blank">Read the manual</a></u> | <u><a href="http://www.vbulletin.org/forum/showthread.php?t=131150" target="_blank">Request support</a></u> | <u><a href="http://redtyger.co.uk/discuss/projectpost.php?do=addissue&amp;projectid=2&amp;issuetypeid=bug" target="_blank">Report a bug</a></u>', 0, 99, 'alt1 smallfont', 'center');
	
	print_table_footer();
	print_cp_footer();
	exit;
}
?>