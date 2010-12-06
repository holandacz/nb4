<?php

/* DownloadsII 5.0.8 by CyberRanger & Jelle
 *
 * ecDownloads 4.1 by Ronin of EliteCoders.org
 *
 *	Human License (non-binding, for the actual license please view licence.txt)
 *	Attribution-NonCommercial-ShareAlike 2.5
 *	You are free:
 *	   * to copy, distribute, display, and perform the work
 * 	   * to make derivative works
 *
 *	Under the following conditions:
 *		by: Attribution. You must attribute the work in the manner specified by the author or licensor.
 *		nc: Noncommercial. You may not use this work for commercial purposes.
 *		sa: Share Alike. If you alter, transform, or build upon this work, you may distribute the resulting work only under a license identical to this one.
 *
 *		* For any reuse or distribution, you must make clear to others the license terms of this work.
 *		* Any of these conditions can be waived if you get permission from the copyright holder.
 */

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once('./includes/class_downloads.php');
require_once('./includes/functions_newpost.php');
$dl = new vB_Downloads();
$categories = $dl->construct_select_array(0, array(0 => 'None'), false);

// ########################################################
// ################## category Functions ##################
// ########################################################
if ($_GET['do'] == 'category')
{
	print_cp_header('Manage Download Categories');
	print_form_header('downloadadmin', 'doaddcat');
	print_table_header('Add a Download category');
	print_input_row('Name<dfn>This will become the name of the category.</dfn>', 'name', '');
	print_input_row('Description<dfn>This is a description of the files in the category.</dfn>', 'desc', '');
	print_input_row('Abbreviation<dfn>This is the abbreviated name of the category.</dfn>', 'abbr', '');
	print_select_row('Parent category <dfn>If you want this category to be a sub-category, select the category you want it to be a sub-category of.</dfn>', 'parent', $categories);
	print_input_row('Weight<dfn>This is the weight used in sorting your categories.</dfn>', 'weight', '');
	print_input_row('Image<dfn>This is the image used for this category.  Enter the complete url, eg http://www.mysite.com/images/myimage.gif. (Optional, max length of 250 characters)</dfn>', 'catimage', '');
	print_submit_row('Add category', 0);
	
	print_form_header('downloadadmin', 'editcat');
	print_table_header('Edit a Download category');
	print_select_row('Edit category<dfn>Select the category you wish to edit.</dfn>', 'edit', $categories);
	print_submit_row('Edit category', 0);
	
	print_form_header('downloadadmin', 'dodelcat');
	print_table_header('Delete a Download category');
	unset($categories[0]);
	print_select_row('Delete category<dfn>Select the category you wish to delete.</dfn>', 'delete', $categories);
	$categories[0] = 'Delete';
	print_select_row('Delete/Move Files<dfn>Chose to delete or move files to a different category (this includes subcategories).</dfn>', 'destination', $categories);
	print_yes_no_row('Confirm Delete<dfn>You must confirm the delete, you will not be asked again.</dfn>', 'confirm', '');
	print_submit_row('Delete category', 0);

	print_cp_footer();
}

// ########################################################
// #################### Do Add category ###################
// ########################################################
if ($_POST['do'] == 'doaddcat')
{
	if ($_POST['name'] == '' OR $_POST['abbr'] == '')
	{
		print_stop_message('ecdownloads_category_info_missing');
	} 
	else 
	{
		if ($_POST['parent'] > 0)
		{
			$dl->modify_subcount($_POST['parent'], 1);
			$isSubcat = true;
		}
		$db->query_write("INSERT INTO " . TABLE_PREFIX . "dl_cats(`name`, `abbr`, `description`, `parent`, `weight`, `catimage`) VALUES(".$db->sql_prepare($_POST['name']).", ".$db->sql_prepare($_POST['abbr']).", ".$db->sql_prepare($_POST['desc']).", ".$db->sql_prepare($_POST['parent']).", ".$db->sql_prepare($_POST['weight']).", ".$db->sql_prepare($_POST['catimage']).")");
		if ($db->insert_id() > 0)
		{
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `categories` = `categories`+1");
			define('CP_REDIRECT', "downloadadmin.php?do=category");
			print_stop_message('ecdownloads_category_added');
		}
		else
		{
			if ($isSubcat)
			{
				$dl->modify_subcount($_POST['parent'], -1);
			}
			print_stop_message('ecdownloads_category_add_failed');
		}
	}
}

// ########################################################
// ################### Do Edit Cat Form ###################
// ########################################################
if ($_POST['do'] == 'editcat')
{
	$cat = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE `id`=".$db->sql_prepare($_POST['edit']));
	$dl->unset_subcats($cat['id']);

	print_cp_header('Edit a Download category');
	print_form_header('downloadadmin', 'doeditcat');
	print_table_header('Edit a Download category');
	print_input_row('Name<dfn>This will become the name of the category.</dfn>', 'name', $cat['name']);
	print_input_row('Abbreviation<dfn>This will become category abreviation.</dfn>', 'abbr', $cat['abbr']);
	print_input_row('Description<dfn>This is a description of the files in the category.</dfn>', 'desc', $cat['description']);
	print_select_row('Parent category <dfn>If you want this category to be a sub-category, select the category you want it to be a sub-category of.</dfn>', 'parent', $categories, $cat['parent']);

	$subcats = $dl->grab_subcats_by_name($cat['id']);
	if ($subcats != '')
	{
		$subcats = substr($subcats,0,-2);
	}
	else
	{
		$subcats = 'None';
	}

	print_label_row('<input type="hidden" name="cid" value="'.$cat['id'].'" /><input type="hidden" name="pid" value="'.$cat['parent'].'" />'.'Subcats<dfn>Categories that are subcategories of this category.</dfn>',$subcats);

	print_input_row('Weight<dfn>This is the weight used in sorting your categories.</dfn>', 'weight', $cat['weight']);
	print_input_row('Image<dfn>This is the image used for this category.  Enter the complete url, eg http://www.mysite.com/images/myimage.gif. (Optional, max length of 250 characters)</dfn>', 'catimage', $cat['catimage']);
	print_submit_row('Edit category', 0);

	print_cp_footer();
}

// ########################################################
// ################### Do Edit category ###################
// ########################################################
if ($_POST['do'] == 'doeditcat')
{
	if ($_POST['cid'] == '')
	{
		print_stop_message('ecdownloads_no_cat_to_edit');
	}
	else
	{
		if ($_POST['parent'] != $_POST['pid'])
		{
			$isDifferent = true;
			if ($_POST['pid'] > 0)
			{
				$dl->modify_subcount($_POST['pid'], -1);
			}
			if ($_POST['parent'] > 0)
			{
				$dl->modify_subcount($_POST['parent'], 1);
			}
		}
		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_cats SET 
			`name`=".$db->sql_prepare($_POST['name']).", 
			`abbr`=".$db->sql_prepare($_POST['abbr']).", 
			`description`=".$db->sql_prepare($_POST['desc']).", 
			`parent`=".$db->sql_prepare($_POST['parent']).",
			`catimage`=".$db->sql_prepare($_POST['catimage']).",
			`weight`=".$db->sql_prepare($_POST['weight'])." 
			WHERE `id`=".$db->sql_prepare($_POST['cid']));
		if ($db->affected_rows() > 0)
		{
			define('CP_REDIRECT', "downloadadmin.php?do=category");
			print_stop_message('ecdownloads_category_edited');
		}
		else
		{
			print_stop_message('ecdownloads_category_not_edited');
		}
	}
}

// ########################################################
// #################### Do Delete Cat #####################
// ########################################################
if ($_POST['do'] == "dodelcat")
{
	if (!$_POST['confirm'])
	{
		print_stop_message('ecdownloads_did_not_confirm');
	}
	else if ($_POST['delete'] == '' OR $_POST['delete'] == 0)
	{
		print_stop_message('ecdownloads_cant_delete_air');
	}
	else if ($_POST['delete'] == $_POST['destination'])
	{
		print_stop_message('ecdownloads_cant_move_into_self');
	}
	else if (!$dl->validate_move($_POST['delete'],$_POST['destination']))
	{
		print_stop_message('ecdownloads_cant_move_into_subcat');
	}
	else
	{
		$cat = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE `id`=".$db->sql_prepare($_POST['delete']));
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_cats WHERE `id`=".$db->sql_prepare($_POST['delete']));
		if ($db->affected_rows() > 0)
		{
			if ($_POST['destination'] == 0)
			{
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_cats WHERE `parent`=".$db->sql_prepare($_POST['delete']));
			}
			else
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_cats SET `parent`=".$db->sql_prepare($_POST['destination'])." WHERE `parent`=".$db->sql_prepare($_POST['delete']));
			}

			$dl->modify_subcount($cat['parent'], -$db->affected_rows()+$cat['subs']);
			$dl->modify_subcount($_POST['destination'], $db->affected_rows()+$cat['subs']);

			if ($_POST['destination'] == 0)
			{
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "dl_files WHERE `category`=".$db->sql_prepare($_POST['delete']));
			}
			else
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `category`=".$db->sql_prepare($_POST['destination'])." WHERE `category`=".$db->sql_prepare($_POST['delete']));
			}

			$dl->modify_filecount($cat['parent'], -$db->affected_rows()+$cat['files']);
			$dl->modify_filecount($_POST['destination'], $db->affected_rows()+$cat['files']);

			$dl->update_counters();

			if ($_POST['destination'] == 0)
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `files` = `files`-".$db->sql_prepare($cat['files']));
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `categories` = `categories`-".$db->sql_prepare($cat['subs']+1));
				define('CP_REDIRECT', "downloadadmin.php?do=category");
				print_stop_message('ecdownloads_category_deleted');
			}
			else
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `categories` = `categories`-1");
				define('CP_REDIRECT', "downloadadmin.php?do=category");
				print_stop_message('ecdownloads_category_deleted');
			}
		}
		else
		{
			print_stop_message('ecdownloads_nothing_to_delete');
		}
	}
}

// ########################################################
// ##################### Start Import #####################
// ########################################################
if ($_REQUEST['do'] == 'import')
{
	print_cp_header('Import Files');
	print_form_header('downloadadmin', 'preimport');
	print_table_header('Import Files');
	print_input_row('Directory<dfn>Absolute path to the download directory.<br />The absolute path to this script is '.substr($_SERVER['SCRIPT_FILENAME'], 0, 1+strrpos($_SERVER['SCRIPT_FILENAME'], "/")).'<br />Include trailing slash.</dfn>', 'dir', '');
	print_submit_row('Import Files', 0);
	print_cp_footer();
}

// ########################################################
// #################### Prepare Import ####################
// ########################################################
if ($_POST['do'] == 'preimport')
{
	print_cp_header('Import Files');
	print_form_header('downloadadmin', 'doimport');
	print_table_header('Import Files', 7);

	$categories[0] = '-----------';
	foreach ($categories AS $cat_key => $cat_value)
	{
		$category_select .= '<option value="'.$cat_key.'">'.$cat_value.'</option>';
	}

	$class = fetch_row_bgclass();
	echo '<tr><td class="'.$class.'" rowspan="2"><input type="hidden" name="dir" value="'.$_POST['dir'].'" /><b>All Files</b><br /><i>(not filled out)</i></td><td class="'.$class.'"><b>File Name</b></td><td class="'.$class.'"><b>Author</b></td><td class="'.$class.'"><b>File Desc</b></td><td class="'.$class.'"><b>Category</b></td><td class="'.$class.'"><b>Pinned</b></td><td class="'.$class.'"><b>Import</b></td></tr>';
	echo '<tr><td class="'.$class.'"><input type="text" size="20" name="dname[0]" /></td><td class="'.$class.'"><input type="text" size="20" name="author[0]" /></td><td class="'.$class.'"><input type="text" size="20" name="desc[0]" /></td><td class="'.$class.'"><select name="category[0]">'.$category_select.'</select></td><td class="'.$class.'"><select name="pinned[0]"><option value="-1">-----</option><option value="0">No</option><option value="1">Yes</option></select></td><td class="'.$class.'"><input type="checkbox" name="allbox" title="Check / Uncheck All" onclick="js_check_all(this.form)" /></td></tr>';

	if ($handle = opendir($_POST['dir']))
	{
		$files = array();

		while (false !== ($file = readdir($handle)))
		{
			if (is_file($_POST['dir'].$file) AND strstr("|".str_replace(" ","|",$dl->ext)."|",strtolower(substr($file, strrpos($file, '.')+1))))
			{
				array_push($files, $file);
			}
		}
		closedir($handle);
		sort($files);

		foreach ($files AS $file)
		{
			$file = str_replace(array("[","]"),array("(openbracket)","(closebracket)"),$file);
			$class = fetch_row_bgclass();
			echo '<tr><td class="'.$class.'">'.$file.'</td><td class="'.$class.'"><input type="text" size="20" name="dname['.$file.']" /></td><td class="'.$class.'"><input type="text" size="20" name="author['.$file.']" /></td><td class="'.$class.'"><input type="text" size="20" name="desc['.$file.']" /></td><td class="'.$class.'"><select name="category['.$file.']">'.$category_select.'</select></td><td class="'.$class.'"><select name="pinned['.$file.']"><option value="-1">-----</option><option value="0">No</option><option value="1">Yes</option></select></td><td class="'.$class.'"><input type="checkbox" name="import['.$file.']" value="1" /></td></tr>';
		}
	}

	print_submit_row('Import Files', 0, 7);
	print_cp_footer();
}

// ########################################################
// ####################### Do Import ######################
// ########################################################
if ($_POST['do'] == 'doimport')
{
	$success = array();
	$category_errors = array();
	$dname_errors = array();
	$file_errors = array();

	if ($_POST['author'][0] != '')
	{
		$authors = explode(";",$_POST['author'][0]);
		foreach ($authors AS $key => $value)
		{
			$author = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE `username`=".$db->sql_prepare(trim($value)));
			if ($author['userid'] > 0)
			{
				$authors[$key] = '<a href="member.php?u='.$author['userid'].'">'.trim($value).'</a>';
			}
			else
			{
				$authors[$key] = trim($value);
			}
			$_POST['_author'][0] = implode(", ",$authors);
		}
	}
	$_POST['desc'][0] = convert_url_to_bbcode($_POST['desc'][0]);

	foreach ($_POST['import'] AS $file => $null)
	{
		$_file = str_replace(array("(openbracket)","(closebracket)"),array("[","]"),stripslashes($file));
		
		if ($_POST['category'][$file] == 0)
		{
			if ($_POST['category'][0] == 0)
			{
				array_push($category_errors, $file);
				continue;
			}
			else
			{
				$_POST['category'][$file] = $_POST['category'][0];
			}
		}
		if ($_POST['dname'][$file] == '')
		{
			if ($_POST['dname'][0] == '')
			{
				$_POST['dname'][$file] = substr($_file, 0, strrpos(stripslashes($_file), '.'));
			}
			else
			{
				$_POST['dname'][$file] = $_POST['dname'][0];
			}
		}
		if ($_POST['author'][$file] == '')
		{
			if ($_POST['author'][0] != '')
			{
				$_POST['author'][$file] = $_POST['author'][0];
				$_POST['_author'][$file] = $_POST['_author'][0];
			}
		}
		else
		{
			$authors = explode(";",$_POST['author'][$file]);
			foreach ($authors AS $key => $value)
			{
				$author = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE `username`=".$db->sql_prepare(trim($value)));
				if ($author['userid'] > 0)
				{
					$authors[$key] = '<a href="member.php?u='.$author['userid'].'">'.trim($value).'</a>';
				}
				else
				{
					$authors[$key] = trim($value);
				}
				$_POST['_author'][$file] = implode(", ",$authors);
			}
		}
		if ($_POST['desc'][$file] == '')
		{
			$_POST['desc'][$file] = $_POST['desc'][0];
		}
		else
		{
			$_POST['desc'][$file] = convert_url_to_bbcode($_POST['desc'][$file]);
		}
		if ($_POST['pinned'][$file] == -1)
		{
			if ($_POST['pinned'][0] != -1)
			{
				$_POST['pinned'][$file] = $_POST['pinned'][0];	
			}
		}

		$_POST['size'][$file] = filesize($_POST['dir'].stripslashes($_file));
		$_POST['newfilename'][$file] = (TIMENOW%100000).'-'.stripslashes($_file);

		if (is_readable($_POST['dir'].stripslashes($_file)))
		{
			@copy($_POST['dir'].stripslashes($_file), $dl->url.$_POST['newfilename'][$file]);
			
			if (file_exists($dl->url.$_POST['newfilename'][$file]))
			{
				$db->query_write("INSERT INTO " . TABLE_PREFIX . "dl_files (`name`, `description`, `author`, `_author`, `uploader`, `uploaderid`, `url`, `date`, `category`, `size`, `pin`)
									VALUES(".
										$db->sql_prepare($_POST['dname'][$file]).", ".
										$db->sql_prepare($_POST['desc'][$file]).", ".
										$db->sql_prepare($_POST['author'][$file]).", ".
										$db->sql_prepare($_POST['_author'][$file]).", ".
										$db->sql_prepare($vbulletin->userinfo['username']).", ".
										$db->sql_prepare($vbulletin->userinfo['userid']).", ".
										$db->sql_prepare($_POST['newfilename'][$file]).", ".
										$db->sql_prepare(TIMENOW).", ".
										$db->sql_prepare($_POST['category'][$file]).", ".
										$db->sql_prepare($_POST['size'][$file]).", ".
										$db->sql_prepare($_POST['pinned'][$file]).
									")"
								);			
				array_push($success, '<a href="../downloads.php?do=file&amp;id='.$db->insert_id().'">'.stripslashes($file).'</a>');
			}
			else
			{
				array_push($file_errors, $file);
			}
		}
		else
		{
			array_push($file_errors, $file);
		}
	}

	$dl->update_counters_all();

	print_cp_header('Import Files');
	print_form_header('downloadadmin', 'import');
	print_table_header('Import Files', 1);

	$class = fetch_row_bgclass();
	echo '<tr><td class="'.$class.'">The following files were successfully imported: '.implode(", ",$success).'</td></tr>';

	if (sizeof($category_errors) > 0)
	{
		$class = fetch_row_bgclass();
		echo '<tr><td class="'.$class.'">Could not import the following files because no category was supplied: '.implode(", ",$category_errors).'</td></tr>';
	}
	if (sizeof($dname_errors) > 0)
	{
		$class = fetch_row_bgclass();
		echo '<tr><td class="'.$class.'">Could not import the following files because no file name was supplied: '.implode(", ",$dname_errors).'</td></tr>';
	}
	if (sizeof($file_errors) > 0)
	{
		$class = fetch_row_bgclass();
		echo '<tr><td class="'.$class.'">Could not import the following files because the import file could not be read or the export file could not be written to: '.implode(", ",$file_errors).'</td></tr>';
	}
	
	print_submit_row('Import More Files', 0, 1);
	print_cp_footer();
}

// ########################################################
// ###################### Downloads #######################
// ########################################################
// ########################################################
// Thanks EvilHawk!
if ($_GET['do'] == 'downloads')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'perpage'    => TYPE_UINT,
	'pagenumber' => TYPE_UINT,
	));

	$vbulletin->GPC['perpage'] = 25;

	$result = $db->query_first("SELECT COUNT(`id`) AS downloads FROM " . TABLE_PREFIX . "dl_downloads");
	$logs = $result['downloads'];
	$totalpages = ceil($result['downloads'] / $vbulletin->GPC['perpage']);

	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];

	$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_downloads ORDER BY `id` DESC LIMIT $startat, " . $vbulletin->GPC['perpage'] . "    ");
	if ($db->num_rows($result))
	{
		if ($vbulletin->GPC['pagenumber'] != 1)
		{
			$prv = $vbulletin->GPC['pagenumber'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" value=\"&laquo; " . $vbphrase['first_page'] . "\" tabindex=\"1\" onclick=\"window.location='downloadadmin.php?do=downloads&amp;page=1'\" />";
			$prevpage = "<input type=\"button\" class=\"button\" value=\"&lt; " . $vbphrase['prev_page'] . "\" tabindex=\"1\" onclick=\"window.location='downloadadmin.php?do=downloads&amp;page=$prv'\" />";
		}

		if ($vbulletin->GPC['pagenumber'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['pagenumber'] + 1;
			$nextpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['next_page'] . " &gt;\" tabindex=\"1\" onclick=\"window.location='downloadadmin.php?do=downloads&amp;page=$nxt'\" />";
			$lastpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['last_page'] . " &raquo;\" tabindex=\"1\" onclick=\"window.location='downloadadmin.php?do=downloads&amp;page=$totalpages'\" />";
		}
	}
	$page = $vbulletin->GPC['pagenumber'];

	print_cp_header('Downloads');
	print_table_start('downloadadmin');
	print_table_header("Downloads - Total log entries: $logs - Page: $page of $totalpages", 5);

	echo '<tr><td class="thead">User</td><td class="thead">File</td><td class="thead">Date</td><td class="thead">File Size</td><td class="thead">IP Address</td></tr>';

	while ($download = $db->fetch_array($result))
	{
		$class = fetch_row_bgclass();
		echo '<tr><td class="'.$class.'"><a href="user.php?' . $vbulletin->session->vars['sessionurl'] . 'do=edit&amp;u='.$download['userid'].'">'.$download['user'].'</a></td><td class="'.$class.'"><a href="../downloads.php?do=file&amp;id='.$download['fileid'].'">'.$download['file'].'</a></td><td class="'.$class.'"><span class="smallfont">'.vbdate($vbulletin->options['logdateformat'], $download['time']).'</span></td><td class="'.$class.'">'.vb_number_format($download['filesize'], 0, true).'</td><td class="'.$class.'"><span class="smallfont"><a href="usertools.php?' . $vbulletin->session->vars['sessionurl'] . 'do=gethost&amp;ip=' . $download['clientip'] . '">' . $download['clientip'] . '</a></span></td></tr>';
	}

	print_table_footer(5, "$firstpage $prevpage &nbsp; $nextpage $lastpage");

	print_cp_footer();
}

?>