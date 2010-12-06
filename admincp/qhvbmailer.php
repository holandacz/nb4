<?php
/*
Please do not remove this Copyright notice.
As you see this is a free script accordingly under GPL laws and you will see
a license attached accordingly to GNU.
You may NOT rewrite another hack and release it to the vBulletin.org community
or another website using this code without our permission, So do NOT try to release a hack using any part of our code without written permission from us!
Copyright 2007, BlogToRank.com
QH vbMailer v2.1
December 2, 2007
*/
/*======================================================================*\
|| #################################################################### ||
|| # Copyright QH  vbMailer, www.BlogToRank.com, All rights Reserved! # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
global $vbulletin;

require_once(DIR . '/includes/qhvbmailer_functions.php');
require_once(DIR . '/includes/adminfunctions_language.php');
require_once(DIR . '/includes/charts/charts.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminsettings'))
{
	print_cp_no_permission();
}

// ######################## GET PHRASES ###################################
$tps = fetch_custom_phrases(0, 'qhvbmailer_templates');
foreach($tps as $tp){
	$template_phrases[$tp['varname']] = $tp['text'];
}

//
if(
$_GET['do'] == "manage_templates" && $_GET['act'] == 2 ||
$_GET['do'] == "manage_autosenders" && $_GET['act'] == 2 ||
$_GET['do'] == "manage_newsletters" && $_GET['act'] == 2
){
	?>
	<script language="javascript" type="text/javascript" src="../includes/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
	<script language="javascript" type="text/javascript">
		tinyMCE.init({
			theme : "advanced",
			mode : "exact",
			elements : "elm1",
			extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],a[href|target|name]",
			theme_advanced_buttons2_add_before : "fontselect,fontsizeselect",
			debug : false
		});
	</script>
	<?php
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
if(!isset($_GET['act'])){
	if(isset($_POST['act'])){
		$_GET['act'] = $_POST['act'];
	} else {
		$_GET['act'] = 1;
	}
}

print_cp_header('QuantumHacks vbMailer');
if($_GET['do'] == 'manage_attachments'){
	if($_GET['act'] == 1){
		print_form_header('qhvbmailer', 'manage_attachments', true, true, '', '50%');
		print_table_header('Add Attachment');
		construct_hidden_code('act', '2');
		print_upload_row('Browse...', 'attachment_file');
		print_submit_row('Upload');
		
		$select_options[0] = "---Select---";
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_attachments ORDER BY created DESC";
		$attachments = $db->query_read_slave($sql);
		while($attachment = $db->fetch_array($attachments)){
			$select_options[$attachment['id']] = $attachment['filename'];
		}
		
		print_form_header('qhvbmailer', 'manage_attachments', false, true, '', '50%');
		print_table_header('Delete Attachment');
		construct_hidden_code('act', '3');
		print_select_row('Choose an Attachment', 'attachment_id', $select_options);
		print_submit_row('Delete');
	} elseif($_GET['act'] == 2){
		if($_FILES['attachment']['error'] == 0){
			if(!file_exists("./qhvbmailer_attachments/" . $_FILES['attachment_file']['name'])){
				if(move_uploaded_file($_FILES['attachment_file']['tmp_name'], "./qhvbmailer_attachments/" . $_FILES['attachment_file']['name'])){
					$db->query_write("INSERT INTO " . TABLE_PREFIX . "qhvbmailer_attachments(created, filename) VALUES('" . TIMENOW . "', '" . $db->escape_string($_FILES['attachment_file']['name']) . "')");
					print_cp_message('Attachment uploaded successfully!', 'qhvbmailer.php?do=manage_attachments', 1);
				} else {
					print_cp_message('Error uploading file: cannot move file!', 'qhvbmailer.php?do=manage_attachments', 2);
				}
			} else {
				print_cp_message('Error uploading file: file exists!', 'qhvbmailer.php?do=manage_attachments', 2); 
			}
		} else {
			print_cp_message('Error uploading file: code ' . $_FILES['attachment']['error'], 'qhvbmailer.php?do=manage_attachments', 2);
		}
	} elseif($_GET['act'] == 3){
		$id = $vbulletin->input->clean_gpc('p', 'attachment_id', TYPE_UINT);
		if($id > 0){
			$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_attachments WHERE id='" . $id . "'";
			if($attachment = $db->query_first($sql)){
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "qhvbmailer_attachments WHERE id='" . $id . "'");
				$db->query_write("UPDATE " . TABLE_PREFIX . "qhvbmailer_templates SET attachment_id='0' WHERE attachment_id='" . $id . "'");
				if(unlink($vbulletin->options['qhvbmailer_sitepath'] . '/qhvbmailer_attachments/' . $attachment['filename'])){
					print_cp_message('Attachment deleted!', 'qhvbmailer.php?do=manage_attachments', 2);
				} else {
					print_cp_message('Attachment removed from database, but the file could not be deleted!', 'qhvbmailer.php?do=manage_attachments', 2);
				}
			} else {
				print_cp_message('Attachment cannot be deleted! Invalid ID: ' . $id, 'qhvbmailer.php?do=manage_attachments', 2);
			}
		} else {
			print_cp_message('Attachment cannot be deleted! Invalid ID: ' . $id, 'qhvbmailer.php?do=manage_attachments', 2);
		}
	}
} elseif($_GET['do'] == 'manage_templates'){
	if($_GET['act'] == 1){
		print_form_header('qhvbmailer', 'manage_templates');
		print_table_header('Manage Templates');
		print_description_row('<table width="100%" border="0"><tr><td width="88%"><b>Subject</b></td><td width="6%"><b>Edit</b></td><td width="6%"><b>Delete</b></td></tr></table>');
		
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE orderr < 1 AND nl_every='0' ORDER BY created";
		$templates = $db->query_read_slave($sql);
		if($db->num_rows($templates) > 0){
			while($template = $db->fetch_array($templates)){
				print_description_row('<table width="100%" border="0"><tr><td width="88%">' . $template_phrases[$template['id'] . "_" . $template['varname'] . "_subject"] . '</td><td width="6%"><a href="qhvbmailer.php?do=manage_templates&act=2&template_id=' . $template['id'] . '">Edit</a></td><td width="6%"><a href="qhvbmailer.php?do=manage_templates&act=4&template_id=' . $template['id'] . '">Delete</a></td></tr></table>');
			}
		} else {
			print_description_row('<center><b>There are no templates to display.</b></center>');
		}
		
		print_submit_row('Refresh', '');
	} elseif($_GET['act'] == 2){
		$template_id = $vbulletin->input->clean_gpc('g', 'template_id', TYPE_UINT);
		if($template_id > 0){
			$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $template_id . "'";
			$template = $db->query_first($sql);
		}
		
		$select_options[0] = 'No Attachment';
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_attachments ORDER BY created DESC";
		$attachments = $db->query_read_slave($sql);
		while($attachment = $db->fetch_array($attachments)){
			$select_options[$attachment['id']] = $attachment['filename'];
		}
		
		print_form_header('qhvbmailer', 'manage_templates', false, true);
		print_table_header('Add/Edit Template');
		construct_hidden_code('act', '3');
		construct_hidden_code('template_id', $template['id']);
		print_input_row('Varname', 'varname', $template['varname']);
		print_input_row('Subject', 'subject', $template_phrases[$template['id'] . "_" . $template['varname'] . "_subject"]);
		print_select_row('Attachment', 'attachment_id', $select_options, $template['attachment_id']);
		print_textarea_row('Text Body', 'text', $template_phrases[$template['id'] . "_" . $template['varname'] . "_text"], 8, 80);
		print_textarea_row('HTML Body', 'html', $template_phrases[$template['id'] . "_" . $template['varname'] . "_html"], 24, 80, false, false, '', false, 'elm1');
		print_submit_row('Add/Edit Template');
	} elseif($_GET['act'] == 3){
		$vbulletin->input->clean_array_gpc('p', array(
			'template_id' => TYPE_UINT,
			'varname' => TYPE_STR,
			'subject' => TYPE_STR,
			'attachment_id' => TYPE_INT,
			'text' => TYPE_STR,
			'html' => TYPE_STR
		));
		if($vbulletin->GPC['template_id'] > 0){ //Edit
			$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $vbulletin->GPC['template_id'] . "'";
			$template = $db->query_first($sql);
			
			if($db->query_write("UPDATE " . TABLE_PREFIX . "qhvbmailer_templates SET attachment_id='" . $vbulletin->GPC['attachment_id'] . "' WHERE id='" . $vbulletin->GPC['template_id'] . "'")){
				$db->query_write("UPDATE " . TABLE_PREFIX . "phrase SET text='" . $db->escape_string($vbulletin->GPC['subject']) . "' WHERE varname='" . $template['id'] . "_" . $template['varname'] . "_subject'");
				$db->query_write("UPDATE " . TABLE_PREFIX . "phrase SET text='" . $db->escape_string($vbulletin->GPC['text']) . "' WHERE varname='" . $template['id'] . "_" . $template['varname'] . "_text'");
				$db->query_write("UPDATE " . TABLE_PREFIX . "phrase SET text='" . $db->escape_string($vbulletin->GPC['html']) . "' WHERE varname='" . $template['id'] . "_" . $template['varname'] . "_html'");
				print_cp_message('Template edited!', 'qhvbmailer.php?do=manage_templates', 1);
			} else {
				print_cp_message('Error editing template!', 'qhvbmailer.php?do=manage_templates', 2);
			}
		} else {                                //Add
			if($db->query_write("INSERT INTO " . TABLE_PREFIX . "qhvbmailer_templates(varname, created, attachment_id) VALUES('" . $db->escape_string($vbulletin->GPC['varname']) . "', '" . TIMENOW . "','" . $vbulletin->GPC['attachment_id'] . "')")){
				$inserted_id = $db->insert_id();
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "phrase(varname, fieldname, text, product) VALUES('" . $inserted_id . "_" . $db->escape_string($vbulletin->GPC['varname']) . "_subject', 'qhvbmailer_templates', '" . $db->escape_string($vbulletin->GPC['subject']) . "', 'qhvbmailer')");
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "phrase(varname, fieldname, text, product) VALUES('" . $inserted_id . "_" . $db->escape_string($vbulletin->GPC['varname']) . "_text', 'qhvbmailer_templates', '" . $db->escape_string($vbulletin->GPC['text']) . "', 'qhvbmailer')");
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "phrase(varname, fieldname, text, product) VALUES('" . $inserted_id . "_" . $db->escape_string($vbulletin->GPC['varname']) . "_html', 'qhvbmailer_templates', '" . $db->escape_string($vbulletin->GPC['html']) . "', 'qhvbmailer')");
				print_cp_message('Template added!', 'qhvbmailer.php?do=manage_templates', 1);
			} else {
				print_cp_message('Error adding template!', 'qhvbmailer.php?do=manage_templates', 2);
			}
		}
	} elseif($_GET['act'] == 4){
		$template_id = $vbulletin->input->clean_gpc('g', 'template_id', TYPE_UINT);
		if($template_id > 0){
			$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $vbulletin->GPC['template_id'] . "'";
			$template = $db->query_first($sql);
			
			if($db->query_write("DELETE FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $template_id . "'")){
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname='" . $template_id . "_" . $template['varname'] . "_subject'");
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname='" . $template_id . "_" . $template['varname'] . "_text'");
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname='" . $template_id . "_" . $template['varname'] . "_html'");
				print_cp_message('Template deleted!', 'qhvbmailer.php?do=manage_templates', 1);
			} else {
				print_cp_message('Error deleting template!', 'qhvbmailer.php?do=manage_templates', 2);
			}
		} else {
			print_cp_message('Template ID must be greater than zero!', 'qhvbmailer.php?do=manage_templates', 2);
		}
	}
} elseif($_GET['do'] == 'manage_autosenders'){
	if($_GET['act'] == 1){
		print_form_header('qhvbmailer', 'manage_autosenders');
		print_table_header('Manage Autosenders');
		print_description_row('<table width="100%" border="0"><tr><td width="8%"><b>Order</b></td><td width="80%"><b>Subject</b></td><td width="6%"><b>Edit</b></td><td width="6%"><b>Delete</b></td></tr></table>');
		
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE orderr >= 1 ORDER BY orderr";
		$autosenders = $db->query_read_slave($sql);
		if($db->num_rows($autosenders) > 0){
			while($autosender = $db->fetch_array($autosenders)){
				print_description_row('<table width="100%" border="0"><tr><td width="8%">' . $autosender['orderr'] . '</td><td width="80%">' . $template_phrases["ar_" . $autosender['id'] . "_" . $autosender['varname'] . "_subject"] . '</td><td width="6%"><a href="qhvbmailer.php?do=manage_autosenders&act=2&template_id=' . $autosender['id'] . '">Edit</a></td><td width="6%"><a href="qhvbmailer.php?do=manage_autosenders&act=4&template_id=' . $autosender['id'] . '">Delete</a></td></tr></table>');
			}
		} else {
			print_description_row('<center><b>There are no autosenders to display.</b></center>');
		}
		
		print_submit_row('Refresh', '');
	} elseif($_GET['act'] == 2){
		$template_id = $vbulletin->input->clean_gpc('g', 'template_id', TYPE_UINT);
		if($template_id > 0){
			$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $template_id . "'";
			$autosender = $db->query_first($sql);
		}
		
		$select_options[0] = 'No Attachment';
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_attachments ORDER BY created DESC";
		$attachments = $db->query_read_slave($sql);
		while($attachment = $db->fetch_array($attachments)){
			$select_options[$attachment['id']] = $attachment['filename'];
		}
		
		print_form_header('qhvbmailer', 'manage_autosenders', false, true);
		print_table_header('Add/Edit Autosender');
		construct_hidden_code('act', '3');
		construct_hidden_code('template_id', $autosender['id']);
		print_input_row('Varname', 'varname', $autosender['varname']);
		print_input_row('Order', 'orderr', $autosender['orderr']);
		print_input_row('Subject', 'subject', $template_phrases["ar_" . $autosender['id'] . "_" . $autosender['varname'] . "_subject"]);
		print_select_row('Attachment', 'attachment_id', $select_options, $autosender['attachment_id']);
		print_textarea_row('Text Body', 'text', $template_phrases["ar_" . $autosender['id'] . "_" . $autosender['varname'] . "_text"], 8, 80);
		print_textarea_row('HTML Body', 'html', $template_phrases["ar_" . $autosender['id'] . "_" . $autosender['varname'] . "_html"], 24, 80, false, false, '', false, 'elm1');
		print_submit_row('Add/Edit Autosender');
	} elseif($_GET['act'] == 3){
		$vbulletin->input->clean_array_gpc('p', array(
			'template_id' => TYPE_UINT,
			'varname' => TYPE_STR,
			'orderr' => TYPE_UNUM,
			'subject' => TYPE_STR,
			'attachment_id' => TYPE_INT,
			'text' => TYPE_STR,
			'html' => TYPE_STR
		));
		if($vbulletin->GPC['template_id'] > 0){ //Edit
			$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $vbulletin->GPC['template_id'] . "'";
			$autosender = $db->query_first($sql);
			
			if($db->query_write("UPDATE " . TABLE_PREFIX . "qhvbmailer_templates SET varname='" . $db->escape_string($vbulletin->GPC['varname']) . "', orderr='" . $vbulletin->GPC['orderr'] . "', attachment_id='" . $vbulletin->GPC['attachment_id'] . "' WHERE id='" . $vbulletin->GPC['template_id'] . "'")){
				$db->query_write("UPDATE " . TABLE_PREFIX . "phrase SET text='" . $db->escape_string($vbulletin->GPC['subject']) . "' WHERE varname='ar_" . $autosender['id'] . "_" . $autosender['varname'] . "_subject'");
				$db->query_write("UPDATE " . TABLE_PREFIX . "phrase SET text='" . $db->escape_string($vbulletin->GPC['text']) . "' WHERE varname='ar_" . $autosender['id'] . "_" . $autosender['varname'] . "_text'");
				$db->query_write("UPDATE " . TABLE_PREFIX . "phrase SET text='" . $db->escape_string($vbulletin->GPC['html']) . "' WHERE varname='ar_" . $autosender['id'] . "_" . $autosender['varname'] . "_html'");
				print_cp_message('Autosender edited!', 'qhvbmailer.php?do=manage_autosenders', 1);
			} else {
				print_cp_message('Error editing autosender!', 'qhvbmailer.php?do=manage_autosenders', 2);
			}
		} else {                                //Add
			if($db->query_write("INSERT INTO " . TABLE_PREFIX . "qhvbmailer_templates(varname, created, orderr, attachment_id) VALUES('" . $db->escape_string($vbulletin->GPC['varname']) . "', '" . TIMENOW . "', '" . $vbulletin->GPC['orderr'] . "', '" . $vbulletin->GPC['attachment_id'] . "')")){
				$inserted_id = $db->insert_id();
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "phrase(varname, fieldname, text, product) VALUES('ar_" . $inserted_id . "_" . $db->escape_string($vbulletin->GPC['varname']) . "_subject', 'qhvbmailer_templates', '" . $db->escape_string($vbulletin->GPC['subject']) . "', 'qhvbmailer')");
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "phrase(varname, fieldname, text, product) VALUES('ar_" . $inserted_id . "_" . $db->escape_string($vbulletin->GPC['varname']) . "_text', 'qhvbmailer_templates', '" . $db->escape_string($vbulletin->GPC['text']) . "', 'qhvbmailer')");
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "phrase(varname, fieldname, text, product) VALUES('ar_" . $inserted_id . "_" . $db->escape_string($vbulletin->GPC['varname']) . "_html', 'qhvbmailer_templates', '" . $db->escape_string($vbulletin->GPC['html']) . "', 'qhvbmailer')");
				print_cp_message('Autosender added!', 'qhvbmailer.php?do=manage_autosenders', 1);
			} else {
				print_cp_message('Error adding autosender!', 'qhvbmailer.php?do=manage_autosenders', 2);
			}
		}
	} elseif($_GET['act'] == 4){
		$template_id = $vbulletin->input->clean_gpc('g', 'template_id', TYPE_UINT);
		if($template_id > 0){
			$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $vbulletin->GPC['template_id'] . "'";
			$autosender = $db->query_first($sql);
			
			if($db->query_write("DELETE FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $template_id . "'")){
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname='ar_" . $template_id . "_" . $autosender['varname'] . "_subject'");
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname='ar_" . $template_id . "_" . $autosender['varname'] . "_text'");
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname='ar_" . $template_id . "_" . $autosender['varname'] . "_html'");
				print_cp_message('Autosender deleted!', 'qhvbmailer.php?do=manage_autosenders', 1);
			} else {
				print_cp_message('Error deleting autosender!', 'qhvbmailer.php?do=manage_autosenders', 2);
			}
		} else {
			print_cp_message('Autosender ID must be greater than zero!', 'qhvbmailer.php?do=manage_autosenders', 2);
		}
	}
} elseif($_GET['do'] == 'manage_newsletters'){
	if($_GET['act'] == 1){
		print_form_header('qhvbmailer', 'manage_newsletters');
		print_table_header('Manage Newsletters');
		print_description_row('<table width="100%" border="0"><tr><td width="88%"><b>Subject</b></td><td width="6%"><b>Edit</b></td><td width="6%"><b>Delete</b></td></tr></table>');
		
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE orderr < 1 AND nl_every > 0 ORDER BY created";
		$templates = $db->query_read_slave($sql);
		if($db->num_rows($templates) > 0){
			while($template = $db->fetch_array($templates)){
				print_description_row('<table width="100%" border="0"><tr><td width="88%">' . $template_phrases["nl_" . $template['id'] . "_" . $template['varname'] . "_subject"] . '</td><td width="6%"><a href="qhvbmailer.php?do=manage_newsletters&act=2&template_id=' . $template['id'] . '">Edit</a></td><td width="6%"><a href="qhvbmailer.php?do=manage_newsletters&act=4&template_id=' . $template['id'] . '">Delete</a></td></tr></table>');
			}
		} else {
			print_description_row('<center><b>There are no templates to display.</b></center>');
		}
		
		print_submit_row('Refresh', '');
	} elseif($_GET['act'] == 2){
		$template_id = $vbulletin->input->clean_gpc('g', 'template_id', TYPE_UINT);
		if($template_id > 0){
			$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $template_id . "'";
			$template = $db->query_first($sql);
		}
		
		$select_options[0] = 'No Attachment';
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_attachments ORDER BY created DESC";
		$attachments = $db->query_read_slave($sql);
		while($attachment = $db->fetch_array($attachments)){
			$select_options[$attachment['id']] = $attachment['filename'];
		}
		
		for($i = 1; $i < 100; $i++){
			$select_options_days[$i] = $i;
		}
		
		print_form_header('qhvbmailer', 'manage_newsletters', false, true);
		print_table_header('Add/Edit Newsletter');
		construct_hidden_code('act', '3');
		construct_hidden_code('template_id', $template['id']);
		print_input_row('Varname', 'varname', $template['varname']);
		print_select_row('Send Every (amount of days)', 'nl_every', $select_options_days, $template['nl_every']);
		print_input_row('Subject', 'subject', $template_phrases["nl_" . $template['id'] . "_" . $template['varname'] . "_subject"]);
		print_select_row('Attachment', 'attachment_id', $select_options, $template['attachment_id']);
		print_textarea_row('Text Body', 'text', $template_phrases["nl_" . $template['id'] . "_" . $template['varname'] . "_text"], 8, 80);
		print_textarea_row('HTML Body', 'html', $template_phrases["nl_" . $template['id'] . "_" . $template['varname'] . "_html"], 24, 80, false, false, '', false, 'elm1');
		print_submit_row('Add/Edit Newsletter');
	} elseif($_GET['act'] == 3){
		$vbulletin->input->clean_array_gpc('p', array(
			'template_id' => TYPE_UINT,
			'varname' => TYPE_STR,
			'nl_every' => TYPE_UINT,
			'subject' => TYPE_STR,
			'attachment_id' => TYPE_INT,
			'text' => TYPE_STR,
			'html' => TYPE_STR
		));
		if($vbulletin->GPC['nl_every'] > 0){
			if($vbulletin->GPC['template_id'] > 0){ //Edit
				$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $vbulletin->GPC['template_id'] . "'";
				$template = $db->query_first($sql);
				
				if($db->query_write("UPDATE " . TABLE_PREFIX . "qhvbmailer_templates SET nl_every='" . $vbulletin->GPC['nl_every'] . "', attachment_id='" . $vbulletin->GPC['attachment_id'] . "' WHERE id='" . $vbulletin->GPC['template_id'] . "'")){
					$db->query_write("UPDATE " . TABLE_PREFIX . "phrase SET text='" . $db->escape_string($vbulletin->GPC['subject']) . "' WHERE varname='nl_" . $template['id'] . "_" . $template['varname'] . "_subject'");
					$db->query_write("UPDATE " . TABLE_PREFIX . "phrase SET text='" . $db->escape_string($vbulletin->GPC['text']) . "' WHERE varname='nl_" . $template['id'] . "_" . $template['varname'] . "_text'");
					$db->query_write("UPDATE " . TABLE_PREFIX . "phrase SET text='" . $db->escape_string($vbulletin->GPC['html']) . "' WHERE varname='nl_" . $template['id'] . "_" . $template['varname'] . "_html'");
					print_cp_message('Newsletter edited!', 'qhvbmailer.php?do=manage_newsletters', 1);
				} else {
					print_cp_message('Error editing newsletter!', 'qhvbmailer.php?do=manage_newsletters', 2);
				}
			} else {                                //Add
				if($db->query_write("INSERT INTO " . TABLE_PREFIX . "qhvbmailer_templates(varname, created, nl_every, attachment_id) VALUES('" . $db->escape_string($vbulletin->GPC['varname']) . "', '" . TIMENOW . "', '" . $vbulletin->GPC['nl_every'] . "', '" . $vbulletin->GPC['attachment_id'] . "')")){
					$inserted_id = $db->insert_id();
					$db->query_write("INSERT INTO " . TABLE_PREFIX . "phrase(varname, fieldname, text, product) VALUES('nl_" . $inserted_id . "_" . $db->escape_string($vbulletin->GPC['varname']) . "_subject', 'qhvbmailer_templates', '" . $db->escape_string($vbulletin->GPC['subject']) . "', 'qhvbmailer')");
					$db->query_write("INSERT INTO " . TABLE_PREFIX . "phrase(varname, fieldname, text, product) VALUES('nl_" . $inserted_id . "_" . $db->escape_string($vbulletin->GPC['varname']) . "_text', 'qhvbmailer_templates', '" . $db->escape_string($vbulletin->GPC['text']) . "', 'qhvbmailer')");
					$db->query_write("INSERT INTO " . TABLE_PREFIX . "phrase(varname, fieldname, text, product) VALUES('nl_" . $inserted_id . "_" . $db->escape_string($vbulletin->GPC['varname']) . "_html', 'qhvbmailer_templates', '" . $db->escape_string($vbulletin->GPC['html']) . "', 'qhvbmailer')");
					print_cp_message('Newsletter added!', 'qhvbmailer.php?do=manage_newsletters', 1);
				} else {
					print_cp_message('Error adding newsletter!', 'qhvbmailer.php?do=manage_newsletters', 2);
				}
			}
		} else {
			print_cp_message('The send every field must be greater than zero!', 'qhvbmailer.php?do=manage_newsletters', 2);
		}
	} elseif($_GET['act'] == 4){
		$template_id = $vbulletin->input->clean_gpc('g', 'template_id', TYPE_UINT);
		if($template_id > 0){
			$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $vbulletin->GPC['template_id'] . "'";
			$template = $db->query_first($sql);
			
			if($db->query_write("DELETE FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $template_id . "'")){
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname='nl_" . $template_id . "_" . $template['varname'] . "_subject'");
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname='nl_" . $template_id . "_" . $template['varname'] . "_text'");
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname='nl_" . $template_id . "_" . $template['varname'] . "_html'");
				print_cp_message('Newsletter deleted!', 'qhvbmailer.php?do=manage_newsletters', 1);
			} else {
				print_cp_message('Error deleting newsletter!', 'qhvbmailer.php?do=manage_newsletters', 2);
			}
		} else {
			print_cp_message('Template ID must be greater than zero!', 'qhvbmailer.php?do=manage_newsletters', 2);
		}
	}
} elseif($_GET['do'] == 'compose_email'){
	if($_GET['act'] == 1){
		print_form_header('qhvbmailer', 'compose_email');
		print_table_header('Compose Email');
		construct_hidden_code('act', '2');
		
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE orderr < 1 ORDER BY created DESC";
		$templates = $db->query_read_slave($sql);
		while($template = $db->fetch_array($templates)){
			$select_options_templates[$template['id']] = $template_phrases[$template['id'] . "_" . $template['varname'] . "_subject"];
		}
		
		$select_options_usergroups[0] = 'All Users';
		$sql = "SELECT * FROM " . TABLE_PREFIX . "usergroup";
		$usergroups = $db->query_read_slave($sql);
		while($usergroup = $db->fetch_array($usergroups)){
			$select_options_usergroups[$usergroup['usergroupid']] = $usergroup['title'];
		}
		
		print_select_row('What to send', 'template_id', $select_options_templates);
		print_time_row('When to send', 'date', TIMENOW);
		print_select_row('Who to send to', 'send_to', $select_options_usergroups);
		print_submit_row('Continue', '');
	} elseif($_GET['act'] == 2){
		$vbulletin->input->clean_array_gpc('p', array(
			'template_id' => TYPE_UINT,
			'date' => TYPE_ARRAY,
			'send_to' => TYPE_UINT
		));
		
		if($vbulletin->GPC['template_id'] > 0){
			$send_when = mktime($vbulletin->GPC['date'][hour], $vbulletin->GPC['date'][minute], '00', $vbulletin->GPC['date'][month], $vbulletin->GPC['date'][day], $vbulletin->GPC['date'][year]);
			if($db->query_write("INSERT INTO " . TABLE_PREFIX . "qhvbmailer_campaigns(created, send_when, send_to, template_id) VALUES('" . TIMENOW . "', '" . $send_when . "', '" . $vbulletin->GPC['send_to'] . "', '" . $vbulletin->GPC['template_id'] . "')")){
				print_cp_message('Campaign scheduled!', 'qhvbmailer.php?do=manage_campaigns', 1);
			} else {
				print_cp_message('Error scheduling campaign!', 'qhvbmailer.php?do=compose_email', 2);
			}
		} else {
			print_cp_message('Template ID must be greater than zero!', 'qhvbmailer.php?do=compose_email', 2);
		}
	}
} elseif($_GET['do'] == 'manage_campaigns'){
	if($_GET['act'] == 1){
		print_form_header('qhvbmailer', 'manage_campaigns');
		print_table_header('Review Campaigns');
		print_description_row('<b>Please Note:</b> <i>Sent campaigns appear in green. Campaigns that haven\'t been sent appear in red.</i>');
		print_description_row('<table width="100%" border="0"><tr><td width="22%"><b>Subject</b></td><td width="29%"><b>Date/Time</b></td><td width="8%"><b>Sent</b></td><td width="8%"><b>Failed</b></td><td width="15%"><b>Unique Reads</b></td><td width="12%"><b>Full Stats</b></td><td width="8%"><b>Delete</b></tr></table>');
		
		$sql = "SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_campaigns ORDER BY created DESC";
		$campaigns = $db->query_read_slave($sql);
		while($campaign = $db->fetch_array($campaigns)){
			$template = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "qhvbmailer_templates WHERE id='" . $campaign['template_id'] . "'");
			$sent = countTracks($campaign['id'], 'sent');
			$failed = countTracks($campaign['id'], 'failed');
			$unique_reads = countTracks($campaign['id'], 'unique_read');
			if($campaign['send_switch'] == 1){
				$color = 'green';
			} else {
				$color = 'red';
			}
			print_description_row('<table width="100%" border="0"><tr><td width="22%"><font color="' . $color . '">' . $template_phrases[$template['id'] . "_" . $template['varname'] . "_subject"] . '</font></td><td width="29%">' . date("m-d-Y h:i:s A", $campaign['send_when']) . '</td><td width="8%">' . $sent . '</td><td width="8%">' . $failed . '</td><td width="15%">' . $unique_reads . '</td><td width="12%"><a href="qhvbmailer.php?do=manage_campaigns&act=2&campaign_id=' . $campaign['id'] . '">Full Stats</a></td><td width="8%"><a href="qhvbmailer.php?do=manage_campaigns&act=3&campaign_id=' . $campaign['id'] . '">Delete</a></tr></table>');
		}
		
		print_submit_row('Refresh', '');
	} elseif($_GET['act'] == 2){
		$vbulletin->input->clean_array_gpc('g', array(
			'campaign_id' => TYPE_UINT
		));
		$campaign_id = $vbulletin->GPC['campaign_id'];
	
		if($campaign_id > 0){
			$sent = countTracks($campaign_id, 'sent');
			$failed = countTracks($campaign_id, 'failed');
			$unqread = countTracks($campaign_id, 'unique_read');
			$read = countTracks($campaign_id, 'read');
			$links = countTracks($campaign_id, 'link');
			
			print_form_header('qhvbmailer', 'manage_campaigns');
			print_table_header('Campaign Statistics');
			if(file_exists(DIR . '/includes/charts/chart.php')){
				print_description_row('<center>' . InsertChart('../includes/charts/charts.swf', '../includes/charts/charts_library', '../includes/charts/chart.php?sent=' . $sent . '&failed=' . $failed . '&unqread=' . $unqread . '&read=' . $read . '&links=' . $links, 400, 320, 'FFFFFF', true) . '</center>');
			} else {
				print_cp_message('No charts file!', 'qhvbmailer.php?do=manage_campaigns', 2);
			}
			print_submit_row('Back', '');
		} else {
			print_cp_message('Campaign ID must be greater than zero!', 'qhvbmailer.php?do=manage_campaigns', 2);
		}
	} elseif($_GET['act'] == 3){
		$vbulletin->input->clean_array_gpc('g', array(
			'campaign_id' => TYPE_UINT
		));
		
		if($vbulletin->GPC['campaign_id'] > 0){
			$db->query_write("DELETE FROM " . TABLE_PREFIX . "qhvbmailer_campaigns WHERE id='" . $vbulletin->GPC['campaign_id'] . "'");
			print_cp_message('Campaign deleted!', 'qhvbmailer.php?do=manage_campaigns', 1);
		} else {
			print_cp_message('Campaign ID must be greater than zero!', 'qhvbmailer.php?do=manage_campaigns', 2);
		}
	}
} else {
	print_cp_message("Invalid page: " . $_GET['do']);
}
echo "<br><center>Copyright <a href=\"http://www.blogtorank.com/\" target=\"_blank\">BlogToRank</a> 2007. All rights reserved.</center>";
/*
Please do not remove this Copyright notice.
As you see this is a free script accordingly under GPL laws and you will see
a license attached accordingly to GNU.
You may NOT rewrite another hack and release it to the vBulletin.org community
or another website using this code without our permission, So do NOT try to release a hack using any part of our code without written permission from us!
Copyright 2007, BlogToRank.com
QH vbMailer v2.1
December 2, 2007
*/
/*======================================================================*\
|| #################################################################### ||
|| # Copyright QH  vbMailer, www.BlogToRank.com, All rights Reserved! # ||
|| #################################################################### ||
\*======================================================================*/
?>