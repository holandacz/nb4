<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','slideshow');
define('PHOTOPLOG_LEVEL','slideshow');
require_once('./settings.php');

// ######################### Start Slideshow Page #########################
($hook = vBulletinHook::fetch_hook('photoplog_slideshow_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'query';
}

$photoplog_search_pagination_link = '';
$photoplog_nosearch_cats = array();
$photoplog_allow_desc_html = array();

$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_slideshow_thumb'], 0, 1).'link';
$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_slideshow_thumb'], -1, 1).'link';

$photoplog['do_highslide'] = 0;
if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
{
	$photoplog['do_highslide'] = 1;
}

if (!empty($photoplog_ds_catopts))
{
	foreach ($photoplog_ds_catopts AS $photoplog_ds_catid => $photoplog_ds_value)
	{
		$photoplog_categorybit = $photoplog_ds_catopts["$photoplog_ds_catid"]['options'];
		$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);
		$photoplog_do_searches = ($photoplog_catoptions['issearchable']) ? true : false;
		if (!$photoplog_do_searches)
		{
			$photoplog_nosearch_cats[] = intval($photoplog_ds_catid);
		}
		$photoplog_allow_desc_html[$photoplog_ds_catid] = $photoplog_catoptions['allowdeschtml'];
	}
}
if (!empty($photoplog_perm_not_allowed_bits))
{
	$photoplog_nosearch_cats = array_unique(array_merge($photoplog_nosearch_cats, $photoplog_perm_not_allowed_bits));
}

if ($_REQUEST['do'] == 'query')
{
	$photoplog_list_categories_row = $photoplog_list_categories;
	$photoplog_list_categories_row[-1] = $vbphrase['photoplog_all_categories'];
	if (!empty($photoplog_nosearch_cats))
	{
		array_walk($photoplog_list_categories_row, 'photoplog_append_key', '');
		$photoplog_list_categories_row = array_flip(array_diff(array_flip($photoplog_list_categories_row),$photoplog_nosearch_cats));
		array_walk($photoplog_list_categories_row, 'photoplog_remove_key', '');
	}

	$photoplog['select_row'] = $vbphrase['photoplog_not_available'];
	if (!empty($photoplog_list_categories_row))
	{
		$photoplog['select_row'] = "<select name=\"searchcatids[]\" size=\"14\" style=\"width: 100%;\" multiple=\"multiple\">\n";
		$photoplog['select_row'] .= photoplog_select_options($photoplog_list_categories_row, -1);
		$photoplog['select_row'] .= "</select>\n";
	}

	($hook = vBulletinHook::fetch_hook('photoplog_slideshow_form')) ? eval($hook) : false;

	photoplog_output_page('photoplog_slideshow_form', $vbphrase['photoplog_slideshow']);
}

if ($_REQUEST['do'] == 'view')
{
	if (isset($_GET['searchcatidsuri']))
	{
		$_REQUEST['searchcatids'] = explode(',', eregi_replace('[^0-9,-]', '', $_REQUEST['searchcatidsuri']));
	}

	$vbulletin->input->clean_array_gpc('r', array(
		'page' => TYPE_UINT,
		'pp' => TYPE_UINT,
		'searchquery' => TYPE_STR,
		'searchusername' => TYPE_STR,
		'searchcommentoption' => TYPE_BOOL,
		'searchcommentlimit' => TYPE_UINT,
		'searchcatids' => TYPE_ARRAY_INT,
		'searchchildcats' => TYPE_BOOL,
		'searchdateoption' => TYPE_UINT,
		'searchdatelimit' => TYPE_BOOL,
		'searchorderoption' => TYPE_UINT,
		'searchorderlimit' => TYPE_BOOL,
		'searchtype' => TYPE_BOOL
	));

	$photoplog_page_num = $vbulletin->GPC['page'];
	$photoplog_per_page = $vbulletin->GPC['pp'];
	$photoplog_search_query = $vbulletin->GPC['searchquery'];
	$photoplog_search_username = $vbulletin->GPC['searchusername'];
	$photoplog_search_commentoption = $vbulletin->GPC['searchcommentoption'];
	$photoplog_search_commentlimit = $vbulletin->GPC['searchcommentlimit'];
	$photoplog_search_catids = $vbulletin->GPC['searchcatids'];
	$photoplog_search_childcats = $vbulletin->GPC['searchchildcats'];
	$photoplog_search_dateoption = $vbulletin->GPC['searchdateoption'];
	$photoplog_search_datelimit = $vbulletin->GPC['searchdatelimit'];
	$photoplog_search_orderoption = $vbulletin->GPC['searchorderoption'];
	$photoplog_search_orderlimit = $vbulletin->GPC['searchorderlimit'];
	$photoplog_search_type = $vbulletin->GPC['searchtype'];

	if (
		(vbstrlen($photoplog_search_query) > 0 && vbstrlen($photoplog_search_query) < intval($vbulletin->options['minsearchlength']))
			||
		(vbstrlen($photoplog_search_username) > 0 && vbstrlen($photoplog_search_username) < 3)
	)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_too_short']);
	}

	if (vbstrlen($photoplog_search_query) > 0)
	{
		if ($photoplog_search_type)
		{
			$photoplog_search_query_temp_array = array($photoplog_search_query);
		}
		else
		{
			$photoplog_search_query_temp_array = explode(' ', $photoplog_search_query);
		}

		foreach ($photoplog_search_query_temp_array AS $photoplog_search_query_temp_key => $photoplog_search_query_temp_value)
		{
			if (vbstrlen(trim($photoplog_search_query_temp_value)) < intval($vbulletin->options['minsearchlength']))
			{
				unset($photoplog_search_query_temp_array[$photoplog_search_query_temp_key]);
			}
		}

		$photoplog_search_query = trim(implode(' ', $photoplog_search_query_temp_array));

		if (!$photoplog_search_query)
		{
			photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_too_short']);
		}
	}

	$photoplog_search_query_link = urlencode($photoplog_search_query);
	$photoplog_search_query_form1 = htmlspecialchars_uni($photoplog_search_query);
	$photoplog_search_query_form2 = htmlspecialchars_uni($photoplog_search_username);
	$photoplog_search_query_form = '';
	if ($photoplog_search_query_form1 && $photoplog_search_query_form2)
	{
		$photoplog_search_query_form = $photoplog_search_query_form1.'&quot; '.$vbphrase['photoplog_and'].' &quot;'.$photoplog_search_query_form2;
	}
	else if ($photoplog_search_query_form1)
	{
		$photoplog_search_query_form = $photoplog_search_query_form1;
	}
	else if ($photoplog_search_query_form2)
	{
		$photoplog_search_query_form = $photoplog_search_query_form2;
	}

	$photoplog_search_highlight = $photoplog_search_query;
	if (!$vbulletin->options['allowwildcards'])
	{
		$photoplog_search_highlight = preg_replace('#\*+#s','',$photoplog_search_highlight);
	}
	$photoplog_search_highlight_array = array();

	if (vbstrlen($photoplog_search_highlight) > 0 && $photoplog_search_highlight != '*')
	{
		if ($photoplog_search_type)
		{
			$photoplog_search_highlight_keys = array($photoplog_search_highlight);
		}
		else
		{
			$photoplog_search_highlight_keys = explode(' ', $photoplog_search_highlight);
		}

		foreach ($photoplog_search_highlight_keys AS $photoplog_search_highlight_value)
		{
			$photoplog_search_highlight_value = trim($photoplog_search_highlight_value);
			$photoplog_search_highlight_value = str_replace('"', '', $photoplog_search_highlight_value);

			if (vbstrlen($photoplog_search_highlight_value) > 0)
			{
				$photoplog_search_highlight_value = preg_quote(strtolower($photoplog_search_highlight_value), '#');
				$photoplog_search_highlight_value = htmlspecialchars_uni($photoplog_search_highlight_value);
				$photoplog_search_highlight_array[] = str_replace('\\*','.*',$photoplog_search_highlight_value);
			}
		}
	}

	$photoplog_from_chars = array('%','_');
	$photoplog_to_chars = array('\%','\_');
	$photoplog_search_query = str_replace($photoplog_from_chars, $photoplog_to_chars, $photoplog_search_query);
	if ($vbulletin->options['allowwildcards'])
	{
		$photoplog_search_query = str_replace('*','%',$photoplog_search_query);
	}
	else
	{
		$photoplog_search_query = str_replace('*','',$photoplog_search_query);
	}

	$photoplog_nocats_sql = 'WHERE 1=1';
	if (!empty($photoplog_nosearch_cats))
	{
		$photoplog_nocats_sql = "WHERE catid NOT IN (" . implode(",",$photoplog_nosearch_cats) . ")";
	}

	$photoplog_searchquery_sql = '';
	$photoplog_searchquery_sql_temp = array();
	if (vbstrlen($photoplog_search_query) > 0)
	{
		if ($photoplog_search_type)
		{
			$photoplog_search_query_array = array($photoplog_search_query);
		}
		else
		{
			$photoplog_search_query_array = explode(' ', $photoplog_search_query);
		}

		if ($vbulletin->options['photoplog_likequeries_active'])
		{
			foreach ($photoplog_search_query_array AS $photoplog_search_query_value)
			{
				$photoplog_search_query_value = trim($photoplog_search_query_value);
				if (vbstrlen($photoplog_search_query_value) > 0)
				{
					$photoplog_search_query_value = $db->escape_string($photoplog_search_query_value);

					$photoplog_searchquery_sql .= "
						AND (title LIKE '%".$photoplog_search_query_value."%'
						OR description LIKE '%".$photoplog_search_query_value."%'
						OR fielddata LIKE '%".$photoplog_search_query_value."%')
					";
				}
			}
		}
		else
		{
			foreach ($photoplog_search_query_array AS $photoplog_search_query_value)
			{
				$photoplog_search_query_value = trim($photoplog_search_query_value);
				if (vbstrlen($photoplog_search_query_value) > 0)
				{
					$photoplog_search_query_value = str_replace('"', '', $photoplog_search_query_value);
					$photoplog_search_query_value = eregi_replace('[^\\]%', '*', $photoplog_search_query_value);
					$photoplog_search_query_value = $db->escape_string($photoplog_search_query_value);

					if ($photoplog_search_type)
					{
						$photoplog_search_query_value = '"' . $photoplog_search_query_value . '"';
					}

					$photoplog_searchquery_sql_temp[] = "
						MATCH (title, description, fielddata)
						AGAINST ('" . $photoplog_search_query_value . "' IN BOOLEAN MODE)
					";
				}
			}

			if (count($photoplog_searchquery_sql_temp))
			{
				$photoplog_searchquery_sql = "AND (" . implode(' OR ', $photoplog_searchquery_sql_temp) . ")";
			}
		}

		$photoplog_search_pagination_link .= '&amp;searchquery='.$photoplog_search_query_link;
	}
	unset($photoplog_searchquery_sql_temp);

	$photoplog_searchusername_sql = '';
	if (vbstrlen($photoplog_search_username) > 0)
	{
		$photoplog_searchusername_sql = "AND username LIKE '".$db->escape_string(htmlspecialchars_uni($photoplog_search_username))."%'";
		$photoplog_search_pagination_link .= '&amp;searchusername='.urlencode($photoplog_search_username);
	}

	$photoplog_searchcommentoption_sql = '';
	if ($photoplog_search_commentlimit)
	{
		if ($photoplog_search_commentoption) // at most x comments
		{
			$photoplog_search_commentoption_sql = "AND num_comments0 <= ".intval($photoplog_search_commentlimit);
			if (can_administer('canadminforums'))
			{
				$photoplog_search_commentoption_sql = "AND num_comments1 <= ".intval($photoplog_search_commentlimit);
			}
			$photoplog_search_pagination_link .= '&amp;searchcommentoption='.$photoplog_search_commentoption;
		}
		else // at least x comments
		{
			$photoplog_search_commentoption_sql = "AND num_comments0 >= ".intval($photoplog_search_commentlimit);
			if (can_administer('canadminforums'))
			{
				$photoplog_search_commentoption_sql = "AND num_comments1 >= ".intval($photoplog_search_commentlimit);
			}
		}
		$photoplog_search_pagination_link .= '&amp;searchcommentlimit='.$photoplog_search_commentlimit;
	}

	$photoplog_yescats_sql = '';
	$photoplog_yescats_arr1 = array();
	$photoplog_yescats_arr2 = array();
	foreach ($photoplog_search_catids AS $photoplog['catid'])
	{
		$photoplog_yescats_arr1[] = intval($photoplog['catid']);
	}
	if (!empty($photoplog_yescats_arr1) && array_search('-1', $photoplog_yescats_arr1) === false)
	{
		if ($photoplog_search_childcats) // yes memy
		{
			foreach ($photoplog_yescats_arr1 AS $photoplog['catid'])
			{
				$photoplog_yescats_arr2 = array_merge($photoplog_yescats_arr2, array($photoplog['catid']));
				if (isset($photoplog_list_relatives[$photoplog['catid']]))
				{
					$photoplog_yescats_arr2 = array_merge($photoplog_yescats_arr2, $photoplog_list_relatives[$photoplog['catid']]);
				}
			}
			$photoplog_search_pagination_link .= '&amp;searchchildcats='.$photoplog_search_childcats;
		}
		else // no memy
		{
			$photoplog_yescats_arr2 = $photoplog_yescats_arr1;
		}
	}
	unset($photoplog_yescats_arr1);
	if (!empty($photoplog_yescats_arr2))
	{
		$photoplog_yescats_str = implode(",", array_unique($photoplog_yescats_arr2));
		$photoplog_yescats_sql = "AND catid IN (" . $photoplog_yescats_str . ")";
		$photoplog_search_pagination_link .= '&amp;searchcatidsuri='.$photoplog_yescats_str;
	}
	unset($photoplog_yescats_arr2);

	$photoplog_searchdateoption_sql = '';
	if ($photoplog_search_dateoption)
	{
		$photoplog_searchdateoption_cut = TIMENOW - ($photoplog_search_dateoption * 86400);
		if ($photoplog_search_dateoption == '999')
		{
			$photoplog_searchdateoption_cut = $vbulletin->userinfo['lastvisit'];
		}
		$photoplog_searchdateoption_sql = "AND dateline >= ".intval($photoplog_searchdateoption_cut); // newer
		if ($photoplog_search_datelimit) // older
		{
			$photoplog_searchdateoption_sql = "AND dateline <= ".intval($photoplog_searchdateoption_cut);
			$photoplog_search_pagination_link .= '&amp;searchdatelimit='.$photoplog_search_datelimit;
		}
		$photoplog_search_pagination_link .= '&amp;searchdateoption='.$photoplog_search_dateoption;
	}

	$photoplog_searchorderoption_sql = '';
	$photoplog_searchorderoption_dir = ($photoplog_search_orderlimit) ? ' ASC' : ' DESC';
	$photoplog_searchorderoption_bit = (can_administer('canadminforums')) ? '1' : '0';
	// 1:Last Upload Date, 2:Last Comment Date, 3:Number Of Views,
	// 4:Number Of Comments, 5:Number Of Ratings, 6:Username, 7:Category
	switch ($photoplog_search_orderoption)
	{
		case '1':
			$photoplog_searchorderoption_sql = "ORDER BY dateline".$photoplog_searchorderoption_dir.", fileid".$photoplog_searchorderoption_dir;
			break;
		case '2':
			$photoplog_searchorderoption_sql = "ORDER BY last_comment_dateline".$photoplog_searchorderoption_bit.$photoplog_searchorderoption_dir;
			break;
		case '3':
			$photoplog_searchorderoption_sql = "ORDER BY views".$photoplog_searchorderoption_dir;
			break;
		case '4':
			$photoplog_searchorderoption_sql = "ORDER BY num_comments".$photoplog_searchorderoption_bit.$photoplog_searchorderoption_dir;
			break;
		case '5':
			$photoplog_searchorderoption_sql = "ORDER BY num_ratings".$photoplog_searchorderoption_bit.$photoplog_searchorderoption_dir;
			break;
		case '6':
			$photoplog_searchorderoption_sql = "ORDER BY username".$photoplog_searchorderoption_dir;
			break;
		case '7':
			$photoplog_searchorderoption_sql = "ORDER BY catid".$photoplog_searchorderoption_dir;
			break;
		default:
			$photoplog_handled = false;

			($hook = vBulletinHook::fetch_hook('photoplog_slideshow_sortsql')) ? eval($hook) : false;

			if (!$photoplog_handled)
			{
				$photoplog_searchorderoption_sql = "ORDER BY dateline".$photoplog_searchorderoption_dir.", fileid".$photoplog_searchorderoption_dir;
			}
	}
	if ($photoplog_search_orderoption > 1)
	{
		$photoplog_search_pagination_link .= '&amp;searchorderoption='.$photoplog_search_orderoption;
	}
	if ($photoplog_search_orderlimit)
	{
		$photoplog_search_pagination_link .= '&amp;searchorderlimit='.$photoplog_search_orderlimit;
	}
	if ($photoplog_search_type)
	{
		$photoplog_search_pagination_link .= '&amp;searchtype='.$photoplog_search_type;
	}

	$photoplog_maxresults = '';
	if ($vbulletin->options['maxresults'] > 0)
	{
		$photoplog_maxresults = 'LIMIT '.intval($vbulletin->options['maxresults']);
	}

	$photoplog_dateline_sql = '';
	if (!$photoplog_searchquery_sql && !$photoplog_yescats_sql && !$photoplog_searchusername_sql && !$photoplog_searchdateoption_sql)
	{
		$photoplog_thirty_days_ago = TIMENOW - (86400 * 30);
		$photoplog_dateline_sql = 'AND dateline > ' . intval($photoplog_thirty_days_ago);
	}

	$photoplog_file_infos = $db->query_read_slave("SELECT catid, fileid,
		userid, filename,	title, description, fielddata,
		$photoplog_admin_sql4
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		$photoplog_nocats_sql $photoplog_searchquery_sql $photoplog_yescats_sql $photoplog_catid_sql1
		$photoplog_admin_sql1 $photoplog_searchusername_sql $photoplog_search_commentoption_sql 
		$photoplog_searchdateoption_sql $photoplog_dateline_sql $photoplog_searchorderoption_sql
		$photoplog_maxresults
	");

	$photoplog_fid_bits = array();
	$photoplog_last_comment_ids = array();
	$photoplog['fid_title'] = '';
	$photoplog_fid_userid = '';
	$photoplog_fid_filename = '';
	$photoplog_fid_flag = 0;
	$photoplog['slideshow_slidex'] = '';
	$photoplog['slideshow_hrefx'] = '';

	while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
	{
		$photoplog_hidden_bit = 0;

		$photoplog['catid'] = $photoplog_file_info['catid'];
		if (!in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
		{
			$photoplog['category_title'] = htmlspecialchars_uni($vbphrase['photoplog_not_available']);
			$photoplog['category_description'] = $vbphrase['photoplog_not_available'];
			$photoplog_hidden_bit = 1;
		}
		else
		{
			$photoplog['category_title'] = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog['catid']]['title']);
			$photoplog['category_description'] = $photoplog_ds_catopts[$photoplog['catid']]['description'];
			if ($photoplog_ds_catopts[$photoplog['catid']]['displayorder'] == 0)
			{
				$photoplog_hidden_bit = 1;
			}
		}
		if (!$photoplog_allow_desc_html[$photoplog['catid']])
		{
			$photoplog['category_description'] = htmlspecialchars_uni($photoplog['category_description']);
		}

		if (!$photoplog_hidden_bit)
		{
			$photoplog['fileid'] = intval($photoplog_file_info['fileid']);
			$photoplog['userid'] = $photoplog_file_info['userid'];
			$photoplog['filename'] = $photoplog_file_info['filename'];
			$photoplog['title'] = $photoplog_file_info['title'];
			$photoplog['title2'] = $photoplog['title'];
			$photoplog['description'] = $photoplog_file_info['description'];
			$photoplog['description2'] = $photoplog['description'];
			$photoplog_fielddata = $photoplog_file_info['fielddata'];

			if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog['title']) > $vbulletin->options['lastthreadchars'])
			{
				$photoplog['title'] = fetch_trimmed_title($photoplog['title'], $vbulletin->options['lastthreadchars']);
				$photoplog['title']  = photoplog_regexp_text($photoplog['title']);
			}
			$photoplog['title'] = photoplog_process_text($photoplog['title'], $photoplog['catid'], true, false);
			$photoplog['title2'] = photoplog_process_text($photoplog['title2'], $photoplog['catid'], true, false);

			$photoplog_add_dots = false;
			if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog['description']) > $vbulletin->options['lastthreadchars'] * 2)
			{
				$photoplog_add_dots = true;
				$photoplog['description'] = fetch_trimmed_title($photoplog['description'], $vbulletin->options['lastthreadchars'] * 2);
				$photoplog['description']  = photoplog_regexp_text($photoplog['description']);
			}
			$photoplog['description'] = photoplog_process_text($photoplog['description'], $photoplog['catid'], false, $photoplog_add_dots);
			$photoplog['description2'] = photoplog_process_text($photoplog['description2'], $photoplog['catid'], false, false);

			$photoplog_fielddata_str = '';
			if (vbstrlen($photoplog_fielddata) > 0)
			{
				$photoplog_fielddata_arr = unserialize($photoplog_fielddata);
				if (is_array($photoplog_fielddata_arr))
				{
					$photoplog_fielddata_str = implode(' ',$photoplog_fielddata_arr);
				}
			}
			$photoplog_fielddata = photoplog_process_text($photoplog_fielddata_str, $photoplog['catid'], false, false);

			if (!empty($photoplog_search_highlight_array))
			{
				if (!class_exists('vB_Postbit_Post'))
				{
					require_once(DIR . '/includes/class_postbit.php');
					$photoplog_postbit_class =& new vB_Postbit_Post();
				}

				if (!$photoplog_fid_flag)
				{
					$photoplog['fid_title'] = $photoplog['title'];
					$photoplog_fid_userid = $photoplog['userid'];
					$photoplog_fid_filename = $photoplog['filename'];
				}

				$photoplog['title'] = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog['title']);
				$photoplog['title2'] = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog['title2']);
				$photoplog['description'] = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog['description']);
				$photoplog['description2'] = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog['description2']);
				$photoplog_fielddata = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog_fielddata);

				if (eregi('<vb_highlight>',$photoplog['title2']) || eregi('<vb_highlight>',$photoplog['description2']) || eregi('<vb_highlight>',$photoplog_fielddata))
				{
					$photoplog_fid_bits[] = intval($photoplog['fileid']);
					$photoplog_last_comment_ids[$photoplog['fileid']] = intval($photoplog_file_info['last_comment_id']);
					photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);
					if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
					{
						$photoplog['slideshow_slidex'] .= "photoplog_slides[".$photoplog_fid_flag."] = '".$photoplog[$photoplog_hslink1]."';\n";
						$photoplog['slideshow_hrefx'] .= "photoplog_hrefs[".$photoplog_fid_flag."] = '".$photoplog[$photoplog_hslink2]."';\n";
					}
					else
					{
						$photoplog['slideshow_slidex'] .= "photoplog_slides[".$photoplog_fid_flag."] = '".$photoplog['file_mlink']."';\n";
						$photoplog['slideshow_hrefx'] .= "photoplog_hrefs[".$photoplog_fid_flag."] = '".$photoplog['location']."/index.php?".$vbulletin->session->vars[sessionurl]."n=".$photoplog['fileid']."';\n";
					}
					$photoplog_fid_flag ++;
				}
				if ($photoplog_fid_flag && count($photoplog_fid_bits) == 1)
				{
					photoplog_file_link($photoplog_fid_userid, $photoplog_fid_bits[0], $photoplog_fid_filename);
					if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
					{
						$photoplog['slideshow_slide1'] = $photoplog[$photoplog_hslink1];
						$photoplog['slideshow_href1'] = $photoplog[$photoplog_hslink2];
					}
					else
					{
						$photoplog['slideshow_slide1'] = $photoplog['file_mlink'];
						$photoplog['slideshow_href1'] = $photoplog['location'].'/index.php?'.$vbulletin->session->vars[sessionurl].'n='.$photoplog['fileid'];
					}
				}
			}
			else
			{
				if (!$photoplog_fid_flag)
				{
					$photoplog['fid_title'] = $photoplog['title'];
					$photoplog_fid_userid = $photoplog['userid'];
					$photoplog_fid_filename = $photoplog['filename'];
				}

				$photoplog_fid_bits[] = intval($photoplog['fileid']);
				$photoplog_last_comment_ids[$photoplog['fileid']] = intval($photoplog_file_info['last_comment_id']);
				photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);
				if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
				{
					$photoplog['slideshow_slidex'] .= "photoplog_slides[".$photoplog_fid_flag."] = '".$photoplog[$photoplog_hslink1]."';\n";
					$photoplog['slideshow_hrefx'] .= "photoplog_hrefs[".$photoplog_fid_flag."] = '".$photoplog[$photoplog_hslink2]."';\n";
				}
				else
				{
					$photoplog['slideshow_slidex'] .= "photoplog_slides[".$photoplog_fid_flag."] = '".$photoplog['file_mlink']."';\n";
					$photoplog['slideshow_hrefx'] .= "photoplog_hrefs[".$photoplog_fid_flag."] = '".$photoplog['location']."/index.php?".$vbulletin->session->vars[sessionurl]."n=".$photoplog['fileid']."';\n";
				}
				$photoplog_fid_flag ++;

				if ($photoplog_fid_flag && count($photoplog_fid_bits) == 1)
				{
					photoplog_file_link($photoplog_fid_userid, $photoplog_fid_bits[0], $photoplog_fid_filename);
					if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
					{
						$photoplog['slideshow_slide1'] = $photoplog[$photoplog_hslink1];
						$photoplog['slideshow_href1'] = $photoplog[$photoplog_hslink2];
					}
					else
					{
						$photoplog['slideshow_slide1'] = $photoplog['file_mlink'];
						$photoplog['slideshow_href1'] = $photoplog['location'].'/index.php?'.$vbulletin->session->vars[sessionurl].'n='.$photoplog['fileid'];
					}
				}
			}
		}
	}

	$db->free_result($photoplog_file_infos);
	$photoplog['title'] = '';

	$photoplog_search_tot = count($photoplog_fid_bits);

	$photoplog_catbit_info = array();
	$photoplog_catbit_subcats = '';

	if (!$photoplog_search_tot)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_results']);
	}

	$photoplog_navbits = array();
	$photoplog_navbits[$photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q']] = htmlspecialchars_uni($vbphrase['photoplog_photoplog']);
	$photoplog_navbits[$photoplog['location'].'/slideshow.php'.$vbulletin->session->vars['sessionurl_q']] = htmlspecialchars_uni($vbphrase['photoplog_slideshow']);
	$photoplog_navbits[''] = $photoplog['fid_title'];
	if ($photoplog['jsactive'])
	{
		$photoplog_navbits[''] = $vbphrase['photoplog_slides'];
	}

	$photoplog['fid_list'] = implode(",",$photoplog_fid_bits);
	$photoplog['fid_previous'] = 0;
	$photoplog['fid_current'] = $photoplog_fid_bits[0];
	$photoplog['fid_next'] = ($photoplog_search_tot > 1) ? $photoplog_fid_bits[1] : 0;

	$photoplog['fid_button'] = '';
	if ($photoplog_search_tot > 1)
	{
		$photoplog['fid_button'] = "<input class=\"button\" type=\"submit\" name=\"go\" value=\"".$vbphrase['photoplog_next']."\" />";
	}

	$photoplog_search_now = 1;
	$photoplog['fid_locale'] = construct_phrase($vbphrase['photoplog_x_of_y'],$photoplog_search_now,$photoplog_search_tot);

	photoplog_file_link($photoplog_fid_userid, $photoplog['fid_current'], $photoplog_fid_filename);

	($hook = vBulletinHook::fetch_hook('photoplog_slideshow_page')) ? eval($hook) : false;

	photoplog_output_page('photoplog_slideshow_page', $vbphrase['photoplog_slideshow'], '', $photoplog_navbits);
}

if ($_POST['do'] == 'show')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'list' => TYPE_NOHTML,
		'previous' => TYPE_UINT,
		'current' => TYPE_UINT,
		'next' => TYPE_UINT,
		'go' => TYPE_STR
	));

	$photoplog_fid_bits = explode(',', eregi_replace('[^0-9,]', '', $vbulletin->GPC['list']));
	$photoplog_previous = $vbulletin->GPC['previous'];
	$photoplog_current = $vbulletin->GPC['current'];
	$photoplog_next = $vbulletin->GPC['next'];
	$photoplog_go = $vbulletin->GPC['go'];

	$photoplog['fid_title'] = '';
	$photoplog['fid_list'] = '';
	$photoplog['fid_previous'] = 0;
	$photoplog['fid_current'] = 0;
	$photoplog['fid_next'] = 0;
	$photoplog_fid_userid = '';
	$photoplog_fid_filename = '';
	$photoplog['slideshow_slidex'] = '';
	$photoplog['slideshow_hrefx'] = '';

	if (empty($photoplog_fid_bits) || $photoplog_current == 0)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_results']);
	}

	$photoplog['fid_current'] = $photoplog_current;
	if ($photoplog_go == $vbphrase['photoplog_next'])
	{
		$photoplog['fid_current'] = $photoplog_next;
	}
	else if ($photoplog_go == $vbphrase['photoplog_previous'])
	{
		$photoplog['fid_current'] = $photoplog_previous;
	}

	$photoplog_fid_bits_key = array_search($photoplog['fid_current'], $photoplog_fid_bits);
	if ($photoplog_fid_bits_key === false)
	{
		$photoplog['fid_current'] = $photoplog_current;
		$photoplog['fid_next'] = $photoplog_next;
		$photoplog['fid_previous'] = $photoplog_previous;
	}
	else
	{
		$photoplog_fid_bits_key1 = $photoplog_fid_bits_key;
		$photoplog_fid_bits_key2 = $photoplog_fid_bits_key;
		if ($photoplog_fid_bits_key1 > 0)
		{
			$photoplog_fid_bits_key1--;
			$photoplog['fid_previous'] = $photoplog_fid_bits[$photoplog_fid_bits_key1];
		}
		if ($photoplog_fid_bits_key2 < count($photoplog_fid_bits) - 1)
		{
			$photoplog_fid_bits_key2++;
			$photoplog['fid_next'] = $photoplog_fid_bits[$photoplog_fid_bits_key2];
		}
	}

	$photoplog['fid_list'] = implode(",",$photoplog_fid_bits);

	$photoplog['fid_button'] = '';
	if ($photoplog['fid_previous'])
	{
		$photoplog['fid_button'] .= "<input class=\"button\" type=\"submit\" name=\"go\" value=\"".$vbphrase['photoplog_previous']."\" />";
	}
	if ($photoplog['fid_previous'] && $photoplog['fid_next'])
	{
		$photoplog['fid_button'] .= "&nbsp;";
	}
	if ($photoplog['fid_next'])
	{
		$photoplog['fid_button'] .= "<input class=\"button\" type=\"submit\" name=\"go\" value=\"".$vbphrase['photoplog_next']."\" />";
	}

	$photoplog_maxresults = '';
	if ($vbulletin->options['maxresults'] > 0)
	{
		$photoplog_maxresults = 'LIMIT '.intval($vbulletin->options['maxresults']);
	}

	$photoplog_fid_list = implode(',', array_unique(explode(',', intval($photoplog['fid_current']).','.$photoplog['fid_list'])));
	$photoplog_fid_querys = $db->query_read_slave("SELECT catid,title,userid,filename,fileid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid IN (".$photoplog_fid_list.")
		$photoplog_catid_sql1
		$photoplog_admin_sql1
		ORDER BY dateline DESC, fileid DESC
		$photoplog_maxresults
	");
	unset($photoplog_fid_list);

	if (!$db->num_rows($photoplog_fid_querys))
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_results']);
	}

	$photoplog_fid_cnt = 0;
	$photoplog_fid_flag = 0;

	while ($photoplog_fid_query = $db->fetch_array($photoplog_fid_querys))
	{
		if ($photoplog_fid_query['fileid'] == $photoplog['fid_current'])
		{
			$photoplog_fid_flag = 1;
			$photoplog_fid_catid = $photoplog_fid_query['catid'];
			$photoplog['fid_title'] = $photoplog_fid_query['title'];
			$photoplog_fid_userid = $photoplog_fid_query['userid'];
			$photoplog_fid_filename = $photoplog_fid_query['filename'];

			photoplog_file_link($photoplog_fid_userid, $photoplog['fid_current'], $photoplog_fid_filename);
			if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
			{
				$photoplog['slideshow_slide1'] = $photoplog[$photoplog_hslink1];
				$photoplog['slideshow_href1'] = $photoplog[$photoplog_hslink2];
			}
			else
			{
				$photoplog['slideshow_slide1'] = $photoplog['file_mlink'];
				$photoplog['slideshow_href1'] = $photoplog['location'].'/index.php?'.$vbulletin->session->vars[sessionurl].'n='.$photoplog['fid_current'];
			}
		}

		photoplog_file_link($photoplog_fid_query['userid'], $photoplog_fid_query['fileid'], $photoplog_fid_query['filename']);
		if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
		{
			$photoplog['slideshow_slidex'] .= "photoplog_slides[".$photoplog_fid_cnt."] = '".$photoplog[$photoplog_hslink1]."';\n";
			$photoplog['slideshow_hrefx'] .= "photoplog_hrefs[".$photoplog_fid_cnt."] = '".$photoplog[$photoplog_hslink2]."';\n";
		}
		else
		{
			$photoplog['slideshow_slidex'] .= "photoplog_slides[".$photoplog_fid_cnt."] = '".$photoplog['file_mlink']."';\n";
			$photoplog['slideshow_hrefx'] .= "photoplog_hrefs[".$photoplog_fid_cnt."] = '".$photoplog['location']."/index.php?".$vbulletin->session->vars[sessionurl]."n=".$photoplog_fid_query['fileid']."';\n";
		}
		$photoplog_fid_cnt ++;
	}

	$db->free_result($photoplog_fid_querys);

	if (!$photoplog_fid_flag)
	{
		$photoplog_fid_catid = $photoplog_fid_query['catid'];
		$photoplog['fid_title'] = $photoplog_fid_query['title'];
		$photoplog_fid_userid = $photoplog_fid_query['userid'];
		$photoplog_fid_filename = $photoplog_fid_query['filename'];

		photoplog_file_link($photoplog_fid_userid, $photoplog['fid_current'], $photoplog_fid_filename);
		if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
		{
			$photoplog['slideshow_slide1'] = $photoplog[$photoplog_hslink1];
			$photoplog['slideshow_href1'] = $photoplog[$photoplog_hslink2];
		}
		else
		{
			$photoplog['slideshow_slide1'] = $photoplog['file_mlink'];
			$photoplog['slideshow_href1'] = $photoplog['location'].'/index.php?'.$vbulletin->session->vars[sessionurl].'n='.$photoplog['fid_current'];
		}
	}

	if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog['fid_title']) > $vbulletin->options['lastthreadchars'])
	{
		$photoplog['fid_title'] = fetch_trimmed_title($photoplog['fid_title'], $vbulletin->options['lastthreadchars']);
		$photoplog['fid_title']  = photoplog_regexp_text($photoplog['fid_title']);
	}
	$photoplog['fid_title'] = photoplog_process_text($photoplog['fid_title'], $photoplog_fid_catid, true, false);

	$photoplog_search_tot = count($photoplog_fid_bits);
	$photoplog_fid_bits_key = array_search($photoplog['fid_current'], $photoplog_fid_bits);
	$photoplog_search_now = intval($photoplog_fid_bits_key) + 1;
	$photoplog['fid_locale'] = construct_phrase($vbphrase['photoplog_x_of_y'],$photoplog_search_now,$photoplog_search_tot);

	$photoplog_navbits = array();
	$photoplog_navbits[$photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q']] = htmlspecialchars_uni($vbphrase['photoplog_photoplog']);
	$photoplog_navbits[$photoplog['location'].'/slideshow.php'.$vbulletin->session->vars['sessionurl_q']] = htmlspecialchars_uni($vbphrase['photoplog_slideshow']);
	$photoplog_navbits[''] = $photoplog['fid_title'];
	if ($photoplog['jsactive'])
	{
		$photoplog_navbits[''] = $vbphrase['photoplog_slides'];
	}

	photoplog_file_link($photoplog_fid_userid, $photoplog['fid_current'], $photoplog_fid_filename);

	($hook = vBulletinHook::fetch_hook('photoplog_slideshow_page')) ? eval($hook) : false;

	photoplog_output_page('photoplog_slideshow_page', $vbphrase['photoplog_slideshow'], '', $photoplog_navbits);
}

if ($_GET['do'] == 'show')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'slide' => TYPE_UINT
	));

	$photoplog_slide = intval($vbulletin->GPC['slide']); // catid

	$photoplog['fid_title'] = '';
	$photoplog['fid_list'] = '';
	$photoplog['fid_previous'] = 0;
	$photoplog['fid_current'] = 0;
	$photoplog['fid_next'] = 0;
	$photoplog_fid_userid = '';
	$photoplog_fid_filename = '';
	$photoplog['slideshow_slidex'] = '';
	$photoplog['slideshow_hrefx'] = '';

	$photoplog_fid_memy_catids = array($photoplog_slide);
	if ($vbulletin->options['photoplog_nest_thumbs'])
	{
		$photoplog_fid_child_list = array();
		if (isset($photoplog_list_relatives[$photoplog_slide]))
		{
			$photoplog_fid_child_list = $photoplog_list_relatives[$photoplog_slide];
		}
		$photoplog_fid_memy_catids = array_merge(array($photoplog_slide),$photoplog_fid_child_list);
	}

	$photoplog_maxresults = '';
	if ($vbulletin->options['maxresults'] > 0)
	{
		$photoplog_maxresults = 'LIMIT '.intval($vbulletin->options['maxresults']);
	}

	$photoplog_fid_querys = $db->query_read_slave("SELECT fileid,catid,title,userid,filename
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE catid IN (".implode(',',$photoplog_fid_memy_catids).")
		$photoplog_catid_sql1
		$photoplog_admin_sql1
		ORDER BY dateline DESC, fileid DESC
		$photoplog_maxresults
	");
	if (!$db->num_rows($photoplog_fid_querys))
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_results']);
	}

	$photoplog_fid_bits = array();
	$photoplog_fid_cnt = 0;

	while ($photoplog_fid_query = $db->fetch_array($photoplog_fid_querys))
	{
		if (!$photoplog_fid_cnt)
		{
			$photoplog['fid_current'] = intval($photoplog_fid_query['fileid']);
			$photoplog_fid_catid = intval($photoplog_fid_query['catid']);
			$photoplog['fid_title'] = strval($photoplog_fid_query['title']);
			$photoplog_fid_userid = $photoplog_fid_query['userid'];
			$photoplog_fid_filename = $photoplog_fid_query['filename'];

			if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog['fid_title']) > $vbulletin->options['lastthreadchars'])
			{
				$photoplog['fid_title'] = fetch_trimmed_title($photoplog['fid_title'], $vbulletin->options['lastthreadchars']);
				$photoplog['fid_title']  = photoplog_regexp_text($photoplog['fid_title']);
			}
			$photoplog['fid_title'] = photoplog_process_text($photoplog['fid_title'], $photoplog_fid_catid, true, false);

			photoplog_file_link($photoplog_fid_userid, $photoplog['fid_current'], $photoplog_fid_filename);
			if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
			{
				$photoplog['slideshow_slide1'] = $photoplog[$photoplog_hslink1];
				$photoplog['slideshow_href1'] = $photoplog[$photoplog_hslink2];
			}
			else
			{
				$photoplog['slideshow_slide1'] = $photoplog['file_mlink'];
				$photoplog['slideshow_href1'] = $photoplog['location'].'/index.php?'.$vbulletin->session->vars[sessionurl].'n='.$photoplog['fid_current'];
			}
		}

		$photoplog_fid_bits[] = intval($photoplog_fid_query['fileid']);
		photoplog_file_link($photoplog_fid_query['userid'], $photoplog_fid_query['fileid'], $photoplog_fid_query['filename']);
		if ($vbulletin->options['photoplog_highslide_active'] && $photoplog['do_highslide'])
		{
			$photoplog['slideshow_slidex'] .= "photoplog_slides[".$photoplog_fid_cnt."] = '".$photoplog[$photoplog_hslink1]."';\n";
			$photoplog['slideshow_hrefx'] .= "photoplog_hrefs[".$photoplog_fid_cnt."] = '".$photoplog[$photoplog_hslink2]."';\n";
		}
		else
		{
			$photoplog['slideshow_slidex'] .= "photoplog_slides[".$photoplog_fid_cnt."] = '".$photoplog['file_mlink']."';\n";
			$photoplog['slideshow_hrefx'] .= "photoplog_hrefs[".$photoplog_fid_cnt."] = '".$photoplog['location']."/index.php?".$vbulletin->session->vars[sessionurl]."n=".$photoplog_fid_query['fileid']."';\n";
		}
		$photoplog_fid_cnt ++;
	}

	$db->free_result($photoplog_fid_querys);

	// know at least one is in there at this point so find where
	$photoplog_fid_bits_key = array_search($photoplog['fid_current'], $photoplog_fid_bits);

	$photoplog_fid_bits_key1 = $photoplog_fid_bits_key;
	$photoplog_fid_bits_key2 = $photoplog_fid_bits_key;
	if ($photoplog_fid_bits_key1 > 0)
	{
		$photoplog_fid_bits_key1--;
		$photoplog['fid_previous'] = $photoplog_fid_bits[$photoplog_fid_bits_key1];
	}
	if ($photoplog_fid_bits_key2 < count($photoplog_fid_bits) - 1)
	{
		$photoplog_fid_bits_key2++;
		$photoplog['fid_next'] = $photoplog_fid_bits[$photoplog_fid_bits_key2];
	}

	$photoplog['fid_list'] = implode(",",$photoplog_fid_bits);

	$photoplog['fid_button'] = '';
	if ($photoplog['fid_previous'])
	{
		$photoplog['fid_button'] .= "<input class=\"button\" type=\"submit\" name=\"go\" value=\"".$vbphrase['photoplog_previous']."\" />";
	}
	if ($photoplog['fid_previous'] && $photoplog['fid_next'])
	{
		$photoplog['fid_button'] .= "&nbsp;";
	}
	if ($photoplog['fid_next'])
	{
		$photoplog['fid_button'] .= "<input class=\"button\" type=\"submit\" name=\"go\" value=\"".$vbphrase['photoplog_next']."\" />";
	}

	$photoplog_search_tot = count($photoplog_fid_bits);
	$photoplog_search_now = intval($photoplog_fid_bits_key) + 1;
	$photoplog['fid_locale'] = construct_phrase($vbphrase['photoplog_x_of_y'],$photoplog_search_now,$photoplog_search_tot);

	$photoplog_navbits = array();
	$photoplog_navbits[$photoplog['location'].'/index.php'.$vbulletin->session->vars['sessionurl_q']] = htmlspecialchars_uni($vbphrase['photoplog_photoplog']);
	$photoplog_navbits[$photoplog['location'].'/slideshow.php'.$vbulletin->session->vars['sessionurl_q']] = htmlspecialchars_uni($vbphrase['photoplog_slideshow']);
	$photoplog_navbits[''] = $photoplog['fid_title'];
	if ($photoplog['jsactive'])
	{
		$photoplog_navbits[''] = $vbphrase['photoplog_slides'];
	}

	photoplog_file_link($photoplog_fid_userid, $photoplog['fid_current'], $photoplog_fid_filename);

	($hook = vBulletinHook::fetch_hook('photoplog_slideshow_page')) ? eval($hook) : false;

	photoplog_output_page('photoplog_slideshow_page', $vbphrase['photoplog_slideshow'], '', $photoplog_navbits);
}

($hook = vBulletinHook::fetch_hook('photoplog_slideshow_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'query' && $_REQUEST['do'] != 'view' && $_POST['do'] != 'show' && $_GET['do'] != 'show')
{
	photoplog_index_bounce();
}

?>