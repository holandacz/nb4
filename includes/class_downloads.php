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
 
class vB_Downloads
{
	var $perpage, $disabled, $reason, $url, $title, $order, $ext, $ixt, $statslatestfiles, $statstopcontributers, $statsmostpopularfiles, $hidesubcats, $includethumb, $smalldesclen, $hidesubcatssub, $downloadsmessage;

	function vB_Downloads()
	{
		global $vbulletin, $db;

		$this->perpage = $vbulletin->options['ecperpage'];
		$this->disabled = $vbulletin->options['ecdisabled'];
		$this->reason = $vbulletin->options['ecreason'];
		$this->url = $vbulletin->options['ecurl'];
		
		if ((strrpos($this->url, '/')+1) != strlen($this->url))
		{
			$this->url .= '/';
		}

		$this->title = $vbulletin->options['ectitle'];
		$this->statslatestfiles = $vbulletin->options['ecmaxlatestfiles'];
		$this->statstopcontributers = $vbulletin->options['ectopcontributors'];
		$this->statsmostpopularfiles = $vbulletin->options['ecmostpopularfiles'];
		$this->hidesubcats = $vbulletin->options['echidesubcats'];
		$this->hidesubcatssub = $vbulletin->options['echidesubcatssub'];
		$this->includethumb = $vbulletin->options['ecincludethumb'];
		$this->smalldesclen = $vbulletin->options['ecsmalldesclen'];
		$this->downloadsmessage = $vbulletin->options['ecdownloadsmessage'];
		$this->renamefiles = $vbulletin->options['ecrenamefiles'];
		$this->allowcomments = $vbulletin->options['ecallowcomments'];
		$this->showcomments = $vbulletin->options['ecshowcomments'];
		$this->allowimages = $vbulletin->options['ecallowimages'];

		if ($vbulletin->options['ecsort'])
		{
			$this->order = '`weight`';
		}
		else
		{
			$this->order = '`name`';
		}
		$this->ext = $vbulletin->options['ecext'];
		$this->ixt = $vbulletin->options['ecixt'];

		$this->stats = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_main");
	}

	function update_counters()
	{
		$this->update_top_contributors();
		$this->update_latest_files();
		$this->update_popular_files();
	}

	function update_counters_all()
	{
			global $db;
			// clear current category numbers
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_cats set files = 0");
			$db->query_write("UPDATE " . TABLE_PREFIX . "user set uploads = 0, comments = 0");
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files set comments = 0");

			$result = $db->query_read("SELECT DISTINCT(`authorid`) AS authorid FROM " . TABLE_PREFIX . "dl_comments");
			while ($comment = $db->fetch_array($result))
			{
				$user = $db->query_first("SELECT COUNT(*) AS comments FROM " . TABLE_PREFIX . "dl_comments WHERE `authorid`=".$db->sql_prepare($comment['authorid']));
				$db->query_write("UPDATE " . TABLE_PREFIX . "user SET `comments`=".$user['comments']." WHERE `userid`=".$db->sql_prepare($comment['authorid']));
			}

			$result = $db->query_read("SELECT DISTINCT(`fileid`) AS fileid FROM " . TABLE_PREFIX . "dl_comments");
			while ($comment = $db->fetch_array($result))
			{
				$file = $db->query_first("SELECT COUNT(*) AS comments FROM " . TABLE_PREFIX . "dl_comments WHERE `fileid`=".$db->sql_prepare($comment['fileid']));
				$db->query_write("UPDATE " . TABLE_PREFIX . "dl_files SET `comments`=".$file['comments']." WHERE `id`=".$db->sql_prepare($comment['fileid']));
			}

			// get file loaded information
			$result = $db->query_read("SELECT `uploaderid`,`category` FROM " . TABLE_PREFIX . "dl_files");
			while ($contrib = $db->fetch_array($result))
			{
				$catid = $contrib['category'];
				$uploaderid = $contrib['uploaderid'];

				$this->modify_filecount($catid, 1);

				$cat = $db->query_first("SELECT COUNT(*) AS filecount FROM " . TABLE_PREFIX . "dl_files WHERE `uploaderid` = ".$uploaderid);
				$db->query_write("UPDATE " . TABLE_PREFIX . "user SET `uploads`=".$cat['filecount']." WHERE `userid`= ".$uploaderid);
				$db->free_result($cat);
			}

			$db->free_result($result);
			// Update the contributors
			$this->update_top_contributors();
			// Update the lastest files
			$this->update_latest_files();
			// Update the popular files
			$this->update_popular_files();

			// update total files count
			$temp = $db->query_first("SELECT COUNT(*) AS `files` FROM " . TABLE_PREFIX . "dl_files");
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `files`=".$db->sql_prepare($temp['files']));

			// update total categories count
			$temp = $db->query_first("SELECT COUNT(*) AS `cats` FROM " . TABLE_PREFIX . "dl_cats");
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `categories`=".$db->sql_prepare($temp['cats']));

			// update total comments count
			$temp = $db->query_first("SELECT COUNT(*) AS `comments` FROM " . TABLE_PREFIX . "dl_comments");
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `comments`=".$db->sql_prepare($temp['comments']));

			$db->free_result($temp);
	}

	function update_latest_files()
	{
		global $db, $vbulletin;

		if ($this->statslatestfiles > 0)
		{
			$statslatestfiles_q = $db->query_read("SELECT name, id FROM " . TABLE_PREFIX . "dl_files WHERE `purgatory`='0' ORDER BY `date` DESC LIMIT ".$this->statslatestfiles.""); // added by Brent to dynamically get the latest files loaded

			while ($latest = $db->fetch_array($statslatestfiles_q)) // for section added by Brent to show a dynamic number of files
			{
				$name = addslashes($latest['name']);
				$id = $latest['id'];
				$url = $vbulletin->options['bburl']."/downloads.php?do=file&amp;id=$id";

				if ($id > 0)
				{
					eval('$dpanel_latest_bits .= "' . fetch_template('downloads_panel_bit') . '";');
				}
			}

			$db->free_result($statslatestfiles_q);
			
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `latestall`='".$dpanel_latest_bits."'");
		}
		else
		{
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `latestall`=''");
		}
	}

	function update_popular_files()
	{
		global $db, $vbulletin;

		if ($this->statsmostpopularfiles > 0)
		{
			$statsmostpopularfiles_q = $db->query_read("SELECT `id`,`name`,`downloads` FROM " . TABLE_PREFIX . "dl_files WHERE `purgatory`='0' ORDER BY `downloads` DESC LIMIT ".$this->statsmostpopularfiles.""); // added by Brent to dynamically get the latest files loaded

			while ($latest = $db->fetch_array($statsmostpopularfiles_q)) // for section added by Brent to show a dynamic number of files
			{ 
				$name = addslashes($latest['name']);
				$id = $latest['id'];
				$url = $vbulletin->options['bburl']."/downloads.php?do=file&amp;id=$id";
				$value = vb_number_format($latest['downloads']);

				if ($id > 0)
				{
					eval('$dpanel_popular_bits .= "' . fetch_template('downloads_panel_bit') . '";');
				}
			}

			$db->free_result($statsmostpopularfiles_q);

			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `popularall`='".$dpanel_popular_bits."'");
		}
		else
		{
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `popularall`=''");
		}
	}

	function update_top_contributors()
	{
		global $db, $vbulletin;

		if ($this->statstopcontributers > 0)
		{
			$statstopcontributers_q = $db->query_read("SELECT `userid`,`username`,`uploads` FROM " . TABLE_PREFIX . "user ORDER BY `uploads` DESC LIMIT ".$this->statstopcontributers.""); // added by Brent to dynamically get the latest files loaded

			while ($latest = $db->fetch_array($statstopcontributers_q)) // for section added by Brent to show a dynamic number of files
			{
				$name = addslashes($latest['username']);
				$id = $latest['userid'];
				$url = $vbulletin->options['bburl']."/member.php?u=$id";
				$value = $latest['uploads'];

				if ($id > 0)
				{
					eval('$dpanel_contrib_bits .= "' . fetch_template('downloads_panel_bit') . '";');
				}
			}

			$db->free_result($statstopcontributers_q);

			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `contriball`='".$dpanel_contrib_bits."'");
		}
		else
		{
			$db->query_write("UPDATE " . TABLE_PREFIX . "dl_main SET `contriball`=''");
		}
	}

	function exclude_cat()
	{
		global $permissions, $vbulletin;

		// check for category permissions
		if ($permissions['ecexcludecatlist'] != '')
		{
			if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['excludecats']))
			{
				$catexclude = "`id` IN (".$permissions['ecexcludecatlist'].") AND";
			}
			else
			{
				$catexclude = "`id` NOT IN (".$permissions['ecexcludecatlist'].") AND";
			}
		}
		else
		{
			$catexclude = '';
		}

		return $catexclude;
	}

	function exclude_files()
	{
			global $permissions, $vbulletin;

			// check for category permissions
			if ($permissions['ecexcludecatlist'] != '')
			{
				if (($permissions['ecdownloadpermissions'] & $vbulletin->bf_ugp['ecdownloadpermissions']['excludecats']))
				{
					$filesexclude = "`category` IN (".$permissions['ecexcludecatlist'].") AND";
				}
				else
				{
					$filesexclude = "`category` NOT IN (".$permissions['ecexcludecatlist'].") AND";
				}
			}
			else
			{
				$filesexclude = '';
			}

			return $filesexclude;
	}

	function build_cat_nav($id)
	{
		if ($id > 0)
		{
			global $db;
			$catexclude = $this->exclude_cat();
			$cat = $db->query_first("SELECT `name`,`id`,`parent` FROM " . TABLE_PREFIX . "dl_cats WHERE ".$catexclude." `id`=".$db->sql_prepare($id));
			return $this->build_cat_nav($cat['parent']) + array('downloads.php?do=cat&amp;id='.$cat['id'] => $cat['name']);
		}
		else
		{
			return array();
		}
	}

	function grab_subcats_by_name_client($id)
	{
		global $vbphrase;

		$subcats = $this->grab_subcats_by_name($id);
		if ($subcats != '')
		{
			$subcats = '<br /><i>'.$vbphrase['ecdownloads_sub_cats'].': '.substr($subcats, 0, -2).'</i>';
		}
		return $subcats;
	}

	function grab_subcats_by_name($id)
	{
		global $db;
		$catexclude = $this->exclude_cat();
		$result = $db->query_read("SELECT `name`,`id` FROM " . TABLE_PREFIX . "dl_cats WHERE ".$catexclude." `parent`=".$db->sql_prepare($id)." ORDER BY ".$this->order);
		while ($subs = $db->fetch_array($result))
		{
			$subcats .= '<a href="downloads.php?do=cat&amp;id='.$subs['id'].'">'.$subs['name'].'</a>, '.$this->grab_subcats_by_name($subs['id']);
		}
		return $subcats;
	}

	function validate_move($start, $destination)
	{
		global $db;
		$cat = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE `id`=".$db->sql_prepare($destination));
		if ($cat['parent'] == 0 OR $cat['parent'] == '')
		{
			return true;
		}
		else if ($cat['parent'] == $start)
		{
			return false;
		}
		else
		{
			return $this->validate_move($start, $cat['parent']);
		}
	}

	function like($data)
	{
		global $db;
		return $db->sql_prepare('%'.$db->escape_string_like($data).'%');
	}

	function construct_select_array($id = 0, $categories = array(), $spacer = '')
	{
		global $db;
		$catexclude = $this->exclude_cat();
		$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE ".$catexclude." `parent` = ".$db->sql_prepare($id)." ORDER BY ".$this->order);
		while ($category = $db->fetch_array($result))
		{
			$categories += array($category['id'] => $spacer.$category['name']);

			if ($category['subs'] > 0)
			{
				$categories += $this->construct_select_array($category['id'], $categories, $spacer."-");
			}
		}
		$db->free_result($result);
		return $categories;
	}

	function unset_subcats($id)
	{
		global $db, $categories;
		$result = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE `parent`=".$db->sql_prepare($id));
		if ($db->num_rows($result) > 0)
		{
			while ($cat = $db->fetch_array($result))
			{
				$this->unset_subcats($cat['id']);
			}
		}
		$db->free_result($result);
		unset($categories[$id]);
	}

	function modify_filecount_delete($id, $n = -1)
	{
		global $db;
		$cat = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE `id` = ".$db->sql_prepare($id));
		if ($cat['parent'] > 0)
		{
			$this->modify_filecount_delete($cat['parent'], -1);
		}
		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_cats SET `files`=`files`+".$n." WHERE `id`= ".$db->sql_prepare($id));
	}

	function modify_filecount($id, $n = 1)
	{
		global $db;
		$cat = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE `id` = ".$db->sql_prepare($id));
		if ($cat['parent'] > 0)
		{
			$this->modify_filecount($cat['parent']);
		}
		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_cats SET `files`=`files`+".$n." WHERE `id`= ".$db->sql_prepare($id));
	}

	function modify_filecount_user($id)
	{
		global $db;
		$cat = $db->query_first("SELECT COUNT(*) AS filecount FROM " . TABLE_PREFIX . "dl_files WHERE `uploaderid` = ".$db->sql_prepare($id));
		$db->query_write("UPDATE " . TABLE_PREFIX . "user SET `uploads`=".$cat['filecount']." WHERE `userid`= ".$db->sql_prepare($id));
		$db->free_result($cat);
	}

	function modify_subcount($id, $n = 1)
	{
		global $db;
		$cat = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "dl_cats WHERE `id` = ".$db->sql_prepare($id));
		if ($cat['parent'] > 0)
		{
			$this->modify_subcount($cat['parent']);
		}
		$db->query_write("UPDATE " . TABLE_PREFIX . "dl_cats SET `subs`=`subs`+".$n." WHERE `id`= ".$db->sql_prepare($id));
	}
}
?>