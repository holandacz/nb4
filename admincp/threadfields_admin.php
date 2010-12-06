<head>
	<script type="text/javascript">
	<!--
	function js_threadfields_jump(threadfieldsinfo) {
			action = eval("document.cpform.f" + threadfieldsinfo + ".options[document.cpform.f" + threadfieldsinfo + ".selectedIndex].value");
		if (action != '') {
			switch (action) {
				case 'edit':
					page = "threadfields_admin.php?do=edit&fieldid=";
					break;
				case 'delete':
					page = "threadfields_admin.php?do=delete&fieldid=";
					break;
			}
			document.cpform.reset();
			jumptopage = page + threadfieldsinfo;
			window.location = jumptopage;
		} else {
			alert('');
		}
	}
	//-->
	</script>
</head>
<?php
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);
 
// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style');
$specialtemplates = array('products');
 
// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');
 
print_cp_header();
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'main';
}

// #######################################################################
// ######################## START MAIN  SCRIPT ###########################
// #######################################################################
if ($_REQUEST['do'] == 'main')
{
print_form_header('threadfields_admin', 'update');
print_table_header('Extra Threadfields', 4);
print_cells_row(array('Field Info', 'Forums', 'Options', 'Controls'), 1, 'tcat');
 
 	$threadfield_get = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "thread_fields_admin
		ORDER BY fieldid DESC
	");
	
	while ($threadfield = $db->fetch_array($threadfield_get))
	{
				$cell[1] .= "<a href=\"threadfields_admin.php?do=edit&fieldid=$threadfield[fieldid]\"><b>$threadfield[title]</b></a> <br /> <div class=\"smallfont\">$threadfield[description]</div>";
				
				unset($forumslist);
				
				if($threadfield['forums']){
				
					// Get forums where this is applied
					$forums = $db->query_read("SELECT title, forumid
									FROM " . TABLE_PREFIX . "forum
									WHERE find_in_set(forumid,'" . $threadfield['forums'] . "')
									ORDER BY title ASC
								");
					
					while($forum = $db->fetch_array($forums)){
					
						$forumslist .= '<div><a target=\"new\" href="' . $vbulletin->options['bburl'] . '/forumdisplay.php?f= ' . $forum['forumid'] . '">' . $forum[title] . '</a></div>';
					
					}
				
				} else {
				
						$forumslist .= '<div><a target=\"new\" href="' . $vbulletin->options['bburl'] . '/' . $vbulletin->options['forumhome'] . '.php">All Forums</a></div>';
						
				}
				
				$cell[2] .= "<div align=\"left\" class=\"smallfont\">$forumslist</div>";
				
				unset($options);
				if($threadfield['search']){
				
					$options[] = "Searchable";
				
				}

				if($threadfield['required'] == 'yes'){
				
					$options[] = "Required";
				
				}
				
				if($threadfield['staffedit']){
				
					$options[] = "Staff-Edit Only";
				
				}
				
				if(is_array($options)){
				
					$options = implode(', ', $options);
				
				}
				
				$cell[3] .= $options;
							
				$cell[4] .= "\n\t<div style=\"float: right;\">
<select class=\"bginput\" name=\"f$threadfield[fieldid]\">
<option value=\"edit\">Edit</option>
<option value=\"delete\">Delete</option></select>
<input type=\"button\" class=\"button\" value=\"".$vbphrase['go']."\" onclick=\"js_threadfields_jump($threadfield[fieldid]);\" />
</div>";

				print_cells_row($cell);
				unset($cell);
		
	}
}


// #######################################################################
// ######################## START ADD FIELD #############################
// #######################################################################
if ($_REQUEST['do'] == 'add')
{
print_form_header('threadfields_admin', 'update');
	echo "<input type=\"hidden\" name=\"update_what\" value=\"add\">";
	print_table_header("Add A Field");
	print_input_row("Field Name<dfn>This is the name of the field you'd like to have.</dfn>", 'add_title');
	print_radio_row('Field Required<dfn>Force users to have to fill out this field.</dfn>', 'add_required', array( 'yes' => 'yes', 'no' => 'no' ),no);  
	print_radio_row('Field Searchable<dfn>Would you like this field to be searchable when a user is browsing through the forum (forumdisplay.php) where this field is applied? Selecting yes, will apply this field to the "Search this forum" section of the applied forums.</dfn>', 'add_search', array( '1' => 'yes', '0' => 'no' ),no);  
	print_radio_row('Field Only Editable By Staff<dfn>Normal users will not be able to edit this field.</dfn>', 'add_staffedit', array( '1' => 'yes', '0' => 'no' ),0);  
	print_textarea_row("Field Description<dfn>This is the description for the field.</dfn>", 'add_desc');
 	print_select_row('Field Type', 'add_type', array('text' => 'Text', 'radio' => 'Radio', 'select' => 'Drop Down Select',  'checkbox' => 'Checkbox List'));  
	print_input_row("Field Size<dfn>This only applies for text fields.</dfn>", 'add_size');
	print_textarea_row("Field Options<dfn>This is only if you picked a radio, drop down select, or checkbox field type. <br><br><b>Note:</b> Separate each option with a new-line (carriage return).</dfn>", 'add_options', $threadfield['options']);
	print_input_row("Forums To Include This In<dfn>Please enter the ID's of the forums in which you'd like to have these options included in, seperated by a comma. Leave this blank for it to be in all forums. <br /><cite><b>Ex:</b> 1,2,3</cite></dfn>", 'add_forums');
	print_input_row("Display Number<dfn>This is the display number for your field. Fields are arranged by order of display number. Enter 0 if you do not want this field to show automatically in the Forum Index, Forum Display, And Postbit templates.</dfn>", 'add_display', $threadfield['display']);
	print_submit_row("Submit!");  
} 

// #######################################################################
// ######################## START EDIT FIELD ############################
// #######################################################################
if ($_REQUEST['do'] == 'edit')
{
print_form_header('threadfields_admin', 'update');
$fieldid = $_REQUEST['fieldid'];

 	$threadfield = $db->query_first("
		SELECT *
		FROM " . TABLE_PREFIX . "thread_fields_admin
		WHERE fieldid = $fieldid
		LIMIT 1
	");

	echo "<input type=\"hidden\" name=\"update_fieldid\" value=\"$threadfield[fieldid]\">";
	echo "<input type=\"hidden\" name=\"update_what\" value=\"edit\">";
	print_table_header("Edit: $threadfield[title]");
	print_input_row("Field Name<dfn>This is the name of the field you'd like to have.</dfn>", 'update_title', $threadfield['title']);
	print_radio_row('Field Required<dfn>Force users to have to fill out this field.</dfn>', 'update_required', array( 'yes' => 'yes', 'no' => 'no' ),$threadfield[required]);  
	print_radio_row('Field Searchable<dfn>Field Searchable<dfn>Would you like this field to be searchable when a user is browsing through the forum (forumdisplay.php) where this field is applied? Selecting yes, will apply this field to the "Search this forum" section of the applied forums.</dfn>', 'update_search', array( '1' => 'yes', '0' => 'no' ),$threadfield[search]);  
	print_radio_row('Field Only Editable By Staff<dfn>Normal users will not be able to edit this field.</dfn>', 'update_staffedit', array( '1' => 'yes', '0' => 'no' ),$threadfield[staffedit]);  
	print_textarea_row("Field Description<dfn>This is the description for the field.</dfn>", 'update_desc', $threadfield['description']);
 	print_select_row('Field Type', 'update_type', array('text' => 'Text', 'radio' => 'Radio', 'select' => 'Drop Down Select',  'checkbox' => 'Checkbox List'),$threadfield[type]);  
	print_input_row("Field Size<dfn>This only applies for text fields.</dfn>", 'update_size', $threadfield['size']);
	print_textarea_row("Field Options<dfn>This is only if you picked a radio, drop down select, or checkbox field type. <br><br><b>Note:</b> Separate each option with a new-line (carriage return).</dfn>", 'update_options', $threadfield['options']);
	print_input_row("Forums To Include This In<dfn>Please enter the ID's of the forums in which you'd like to have these options included in, seperated by a comma. Leave this blank for it to be in all forums. <br /><cite><b>Ex:</b> 1,2,3</cite></dfn>", 'update_forums', $threadfield['forums']);
	print_input_row("Display Number<dfn>This is the display number for your field. Fields are arranged by order of display number.</dfn>", 'update_display', $threadfield['display']);
	print_submit_row("Save!");  

}

if ($_REQUEST['do'] == 'update')
{

// #######################################################################
// ###################### START UPDATE EDITED ############################
// #######################################################################
if ($_POST['update_what'] == 'edit') 
	{
$fieldid= $_POST['update_fieldid'];
$title= addslashes($_POST['update_title']);
$required= addslashes($_POST['update_required']);
$search= addslashes($_POST['update_search']);
$staffedit= addslashes($_POST['update_staffedit']);
$desc= addslashes($_POST['update_desc']);
$type= $_POST['update_type'];
$size= addslashes($_POST['update_size']);
$options= addslashes($_POST['update_options']);
$forums= addslashes($_POST['update_forums']);
$display= addslashes($_POST['update_display']);

			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread_fields_admin
										SET	title = '$title',
											description = '$desc',
											type = '$type',
											size = '$size',
											options= '$options',
											forums = '$forums',
											display = '$display',
											required = '$required',
											search = '$search',
											staffedit = '$staffedit'
											WHERE fieldid = $fieldid
   										");
			


	define('CP_REDIRECT', 'threadfields_admin.php');
	print_stop_message('extra_threadfields_x_saved', $title);
	}
// #######################################################################
// ###################### START UPDATE ADDED ############################
// #######################################################################
if ($_POST['update_what'] == 'add') 
	{
$title= addslashes($_POST['add_title']);
$required= addslashes($_POST['add_required']);
$search= addslashes($_POST['add_search']);
$staffedit= addslashes($_POST['add_staffedit']);
$desc= addslashes($_POST['add_desc']);
$type=$_POST['add_type'];
$size= addslashes($_POST['add_size']);
$options= addslashes($_POST['add_options']);
$forums= addslashes($_POST['add_forums']);
$display= addslashes($_POST['add_display']);

			$vbulletin->db->query_write("INSERT " . TABLE_PREFIX . "thread_fields_admin
										SET	title = '$title',
											description = '$desc',
											type = '$type',
											size = '$size',
											options= '$options',
											forums = '$forums',
											display = '$display',
											required = '$required',
											search = '$search',
											staffedit = '$staffedit'
   										");
		$vbulletin->GPC['fieldid'] = $db->insert_id();
		$db->query_write("ALTER TABLE " . TABLE_PREFIX . "thread ADD field{$vbulletin->GPC['fieldid']} varchar(100) NOT NULL");
		$db->query_write("OPTIMIZE TABLE " . TABLE_PREFIX . "thread");

	define('CP_REDIRECT', 'threadfields_admin.php');
	print_stop_message('extra_threadfields_x_added', $title);
	}


}

// #######################################################################
// ###################### START DELETE FIELD ###########################
// #######################################################################
if ($_REQUEST['do'] == 'delete')
{
$fieldid = $_REQUEST['fieldid'];

			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "thread_fields_admin
								              WHERE fieldid = '$fieldid'
								              LIMIT 1	
   										");	
			$vbulletin->db->query_write("ALTER TABLE `". TABLE_PREFIX ."thread` DROP `field$fieldid`");	

	define('CP_REDIRECT', 'threadfields_admin.php');
	print_stop_message(extra_threadfields_deleted);
}



	
	print_table_footer();
 
print_cp_footer();
?> 