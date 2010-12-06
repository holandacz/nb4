<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','search');
define('PHOTOPLOG_LEVEL','search');
require_once('./settings.php');

// ########################### Start Search Page ##########################
($hook = vBulletinHook::fetch_hook('photoplog_search_start')) ? eval($hook) : false;

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'query';
}

$photoplog_search_pagination_link = '';
$photoplog_nosearch_cats = array();
$photoplog_allow_desc_html = array();
if (!empty($photoplog_ds_catopts))
{
	foreach ($photoplog_ds_catopts AS $photoplog_ds_catid => $photoplog_ds_value)
	{
		$photoplog_categorybit = $photoplog_ds_catopts[$photoplog_ds_catid]['options'];
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

	($hook = vBulletinHook::fetch_hook('photoplog_search_form')) ? eval($hook) : false;

	photoplog_output_page('photoplog_search_form', $vbphrase['photoplog_search']);
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

	$photoplog['block_cols'] = intval($vbulletin->options['photoplog_block_cols']);
	$photoplog['block_width'] = '';

	if ($photoplog['block_cols'] < 1)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_results']);
	}
	$photoplog['block_width'] = intval(100 / $photoplog['block_cols']);

	if (
		(!$photoplog_search_username && vbstrlen($photoplog_search_query) < intval($vbulletin->options['minsearchlength']))
			||
		(!$photoplog_search_query && vbstrlen($photoplog_search_username) < 3)
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
			$photoplog_searchorderoption_sql = "ORDER BY dateline".$photoplog_searchorderoption_dir;
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

			($hook = vBulletinHook::fetch_hook('photoplog_search_sortsql')) ? eval($hook) : false;

			if (!$photoplog_handled)
			{
				$photoplog_searchorderoption_sql = "ORDER BY dateline".$photoplog_searchorderoption_dir;
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

	$photoplog_file_infos = $db->query_read_slave("SELECT catid, fileid, title, description, fielddata,
			$photoplog_admin_sql4
			FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
			$photoplog_nocats_sql $photoplog_searchquery_sql $photoplog_yescats_sql $photoplog_catid_sql1
			$photoplog_admin_sql1 $photoplog_searchusername_sql $photoplog_search_commentoption_sql
			$photoplog_searchdateoption_sql $photoplog_maxresults
	");

	$photoplog_fid_bits = array();
	$photoplog_last_comment_ids = array();

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
			$photoplog['title'] = $photoplog_file_info['title'];
			$photoplog['description'] = $photoplog_file_info['description'];
			$photoplog_fielddata = $photoplog_file_info['fielddata'];

			$photoplog['title'] = photoplog_process_text($photoplog['title'], $photoplog['catid'], true, false);
			$photoplog['description'] = photoplog_process_text($photoplog['description'], $photoplog['catid'], false, false);

			$photoplog_fielddata_str = '';
			if (vbstrlen($photoplog_fielddata) > 0)
			{
				$photoplog_fielddata_arr = unserialize($photoplog_fielddata);
				if (is_array($photoplog_fielddata_arr))
				{
					foreach ($photoplog_fielddata_arr AS $photoplog_fielddata_arr_key => $photoplog_fielddata_arr_val)
					{
						if (is_array($photoplog_fielddata_arr_val))
						{
							$photoplog_fielddata_arr[$photoplog_fielddata_arr_key] = implode(' ',$photoplog_fielddata_arr_val);
						}
					}
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

				$photoplog['title'] = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog['title']);
				$photoplog['description'] = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog['description']);
				$photoplog_fielddata = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog_fielddata);

				if (eregi('<vb_highlight>',$photoplog['title']) || eregi('<vb_highlight>',$photoplog['description']) || eregi('<vb_highlight>',$photoplog_fielddata))
				{
					$photoplog_fid_bits[] = intval($photoplog['fileid']);
					$photoplog_last_comment_ids[$photoplog['fileid']] = intval($photoplog_file_info['last_comment_id']);
				}
			}
			else if (vbstrlen($photoplog_search_username) > 0)
			{
				$photoplog_fid_bits[] = intval($photoplog['fileid']);
				$photoplog_last_comment_ids[$photoplog['fileid']] = intval($photoplog_file_info['last_comment_id']);
			}
		}
	}

	$db->free_result($photoplog_file_infos);

	$photoplog_search_tot = count($photoplog_fid_bits);

	$photoplog_catbit_info = array();
	$photoplog_catbit_subcats = '';

	if (!$photoplog_search_tot)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_results']);
	}

	$photoplog_numcat_thumbs = intval($vbulletin->options['photoplog_numcat_thumbs']);

	if ($photoplog_numcat_thumbs < 1)
	{
		photoplog_output_page('photoplog_error_page',$vbphrase['photoplog_error'],$vbphrase['photoplog_no_results']);
	}

	sanitize_pageresults($photoplog_search_tot, $photoplog_page_num, $photoplog_per_page, $photoplog_numcat_thumbs, $photoplog_numcat_thumbs);
	$photoplog_search_limit_lower = ($photoplog_page_num - 1) * $photoplog_per_page;

	if ($photoplog_search_limit_lower < 0)
	{
		$photoplog_search_limit_lower = 0;
	}

	$photoplog_search_limit_lower = intval($photoplog_search_limit_lower);
	$photoplog_per_page = intval($photoplog_per_page);

	$photoplog['fid_list'] = implode(",",$photoplog_fid_bits);
	$photoplog_last_comment_ids_list = "0";
	if ($photoplog_last_comment_ids)
	{
		$photoplog_last_comment_ids_list = implode(",",$photoplog_last_comment_ids);
	}
	$photoplog_file_infos = $db->query_read_slave("SELECT catid, fileid, userid, username, title,
			description, filename, filesize, dimensions, dateline, views, moderate,
		$photoplog_admin_sql4
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid IN (".$photoplog['fid_list'].")
		$photoplog_catid_sql1
		$photoplog_admin_sql1
		$photoplog_searchorderoption_sql
		LIMIT $photoplog_search_limit_lower,$photoplog_per_page
	");

	$photoplog_sort_url = $photoplog['location'].'/search.php?'.$vbulletin->session->vars['sessionurl'].'do=view'.$photoplog_search_pagination_link;
	$photoplog['pagenav'] = construct_page_nav($photoplog_page_num, $photoplog_per_page, $photoplog_search_tot, $photoplog_sort_url, '');
	$photoplog_last_comment_infos = $db->query_read_slave("SELECT fileid,
			userid, username, title, dateline, commentid
		FROM " . PHOTOPLOG_PREFIX . "photoplog_ratecomment
		WHERE commentid IN (".$photoplog_last_comment_ids_list.")
		$photoplog_catid_sql2
		$photoplog_admin_sql2
		AND comment != ''
		ORDER BY dateline DESC
	");

	$photoplog_comments_array = array();

	while ($photoplog_last_comment_info = $db->fetch_array($photoplog_last_comment_infos))
	{
		$photoplog_comments_fileid = $photoplog_last_comment_info['fileid'];
		if (!isset($photoplog_comments_array[$photoplog_comments_fileid]))
		{
			$photoplog_comments_array[$photoplog_comments_fileid] = array(
				'photoplog_comment_fileid' => $photoplog_last_comment_info['fileid'],
				'photoplog_comment_userid' => $photoplog_last_comment_info['userid'],
				'photoplog_comment_username' => $photoplog_last_comment_info['username'],
				'photoplog_comment_title' => $photoplog_last_comment_info['title'],
				'photoplog_comment_dateline' => $photoplog_last_comment_info['dateline'],
				'photoplog_comment_commentid' => $photoplog_last_comment_info['commentid']
			);
		}
	}

	$db->free_result($photoplog_last_comment_infos);

	$photoplog_cnt_bits = 0;
	$photoplog['file_bits'] = '';
	$photoplog['block_bits'] = '';
	$photoplog['inlineform'] = 0;
	$photoplog['inlinecanedit'] = 0;
	$photoplog['inlinecandelete'] = 0;

	$photoplog_hslink1 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], 0, 1).'link';
	$photoplog_hslink2 = 'file_'.substr($vbulletin->options['photoplog_highslide_small_thumb'], -1, 1).'link';

	$photoplog['do_highslide'] = 0;
	if ($photoplog_hslink1 != 'file_nlink' && $photoplog_hslink2 != 'file_nlink')
	{
		$photoplog['do_highslide'] = 1;
	}

	while ($photoplog_file_info = $db->fetch_array($photoplog_file_infos))
	{
		$photoplog_hidden_bit = 0;

		$photoplog['catid'] = $photoplog_file_info['catid'];
		if (!in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
		{
			$photoplog['category_title'] = $vbphrase['photoplog_not_available'];
			$photoplog_hidden_bit = 1;
		}
		else
		{
			$photoplog['category_title'] = htmlspecialchars_uni($photoplog_ds_catopts[$photoplog['catid']]['title']);
			if ($photoplog_ds_catopts[$photoplog['catid']]['displayorder'] == 0)
			{
				$photoplog_hidden_bit = 1;
			}
		}

		if (!$photoplog_hidden_bit)
		{
			$photoplog_cnt_bits++;

			$photoplog['catid'] = $photoplog_file_info['catid'];

			$photoplog['do_comments'] = 0;
			if (in_array($photoplog['catid'],array_keys($photoplog_ds_catopts)))
			{
				$photoplog_categorybit = $photoplog_ds_catopts[$photoplog['catid']]['options'];
				$photoplog_catoptions = convert_bits_to_array($photoplog_categorybit, $photoplog_categoryoptions);

				$photoplog['do_comments'] = ($photoplog_catoptions['allowcomments']) ? 1 : 0;
			}

			$photoplog['fileid'] = $photoplog_file_info['fileid'];
			$photoplog['userid'] = $photoplog_file_info['userid'];
			$photoplog['username'] = $photoplog_file_info['username'];
			$photoplog['title'] = $photoplog_file_info['title'];
			$photoplog['description'] = $photoplog_file_info['description'];
			$photoplog['filename'] = $photoplog_file_info['filename'];
			$photoplog['filesize'] = vb_number_format($photoplog_file_info['filesize'],1,true);
			$photoplog['dimensions'] = $photoplog_file_info['dimensions'];
			$photoplog['date'] = vbdate($vbulletin->options['dateformat'],$photoplog_file_info['dateline'],true);
			$photoplog['time'] = vbdate($vbulletin->options['timeformat'],$photoplog_file_info['dateline']);
			$photoplog['views'] = $photoplog_file_info['views'];
			$photoplog['moderate'] = $photoplog_file_info['moderate'];

			if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog['title']) > $vbulletin->options['lastthreadchars'])
			{
				$photoplog['title'] = fetch_trimmed_title($photoplog['title'], $vbulletin->options['lastthreadchars']);
				$photoplog['title']  = photoplog_regexp_text($photoplog['title']);
			}
			$photoplog['title'] = photoplog_process_text($photoplog['title'], $photoplog['catid'], true, false);

			$photoplog_add_dots = false;
			if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog['description']) > $vbulletin->options['lastthreadchars'] * 2)
			{
				$photoplog_add_dots = true;
				$photoplog['description'] = fetch_trimmed_title($photoplog['description'], $vbulletin->options['lastthreadchars'] * 2);
				$photoplog['description']  = photoplog_regexp_text($photoplog['description']);
			}
			$photoplog['description'] = photoplog_process_text($photoplog['description'], $photoplog['catid'], false, $photoplog_add_dots);

			if (!empty($photoplog_search_highlight_array) && vbstrlen($photoplog_search_query) > 0)
			{
				if (!class_exists('vB_Postbit_Post'))
				{
					require_once(DIR . '/includes/class_postbit.php');
					$photoplog_postbit_class =& new vB_Postbit_Post();
				}

				$photoplog['title'] = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog['title']);
				$photoplog['title'] = preg_replace('#<vb_highlight>(.*)</vb_highlight>#siU', '<span class="highlight">$1</span>', $photoplog['title']);

				$photoplog['description'] = preg_replace('#(^|>)([^<]+)(?=<|$)#seU', "\$photoplog_postbit_class->process_highlight_postbit('\\2', \$photoplog_search_highlight_array, '\\1')", $photoplog['description']);
				$photoplog['description'] = preg_replace('#<vb_highlight>(.*)</vb_highlight>#siU', '<span class="highlight">$1</span>', $photoplog['description']);
			}

			$photoplog_comment_count = 0;
			$photoplog_comment_pagenum = '';

			if ($photoplog_file_info['num_comments'])
			{
				$photoplog_comment_count = intval($photoplog_file_info['num_comments']);
				$photoplog_comment_pagenum = '&amp;page='.ceil($photoplog_file_info['num_comments'] / 5);
			}

			$photoplog['comment_raw_average'] = $vbphrase['photoplog_none'];
			$photoplog['comment_img_average'] = 0;

			if ($photoplog_file_info['ave_ratings'])
			{
				$photoplog['comment_raw_average'] = sprintf("%.2f",round($photoplog_file_info['ave_ratings'],2));
				if ($photoplog['comment_raw_average'] == '0.00')
				{
					$photoplog['comment_raw_average'] = $vbphrase['photoplog_none'];
				}
				$photoplog['comment_img_average'] = intval(round($photoplog_file_info['ave_ratings'],0));
			}
			$photoplog_comment_userid = '';
			$photoplog_comment_username = '';
			$photoplog_comment_title = '';
			$photoplog_comment_date = '';
			$photoplog_comment_time = '';
			$photoplog_comment_commentid = '';
			$photoplog_comment_page = '';

			if (isset($photoplog_comments_array[$photoplog['fileid']]))
			{
				$photoplog_comment_userid = $photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_userid'];
				$photoplog_comment_username = $photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_username'];
				$photoplog_comment_title = $photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_title'];
				$photoplog_comment_date = vbdate($vbulletin->options['dateformat'],$photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_dateline'],true);
				$photoplog_comment_time = vbdate($vbulletin->options['timeformat'],$photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_dateline']);
				$photoplog_comment_commentid = '#comment'.$photoplog_comments_array[$photoplog['fileid']]['photoplog_comment_commentid'];
				$photoplog_comment_page = $photoplog_comment_pagenum.$photoplog_comment_commentid;
			}

			if ($vbulletin->options['lastthreadchars'] != 0 && vbstrlen($photoplog_comment_title) > $vbulletin->options['lastthreadchars'])
			{
				$photoplog_comment_title = fetch_trimmed_title($photoplog_comment_title, $vbulletin->options['lastthreadchars']);
				$photoplog_comment_title = photoplog_regexp_text($photoplog_comment_title);
			}
			$photoplog_comment_title = photoplog_process_text($photoplog_comment_title, $photoplog['catid'], true, false);

			$photoplog_inline_perm = array();
			$photoplog_inline_perm['caneditownfiles'] = 0;
			$photoplog_inline_perm['candeleteownfiles'] = 0;
			$photoplog_inline_perm['caneditotherfiles'] = 0;
			$photoplog_inline_perm['candeleteotherfiles'] = 0;

			if (isset($photoplog_inline_bits[$photoplog['catid']]))
			{
				$photoplog_inline_perm = convert_bits_to_array($photoplog_inline_bits[$photoplog['catid']], $photoplog_categoryoptpermissions);
			}

			$photoplog['inlinebox'] = 0;
			if (
				(($photoplog_inline_perm['caneditownfiles'] || $photoplog_inline_perm['candeleteownfiles']) && $vbulletin->userinfo['userid'] == $photoplog['userid'])
					||
				$photoplog_inline_perm['caneditotherfiles'] || $photoplog_inline_perm['candeleteotherfiles']
			)
			{
				$photoplog['inlinebox'] = 1;
				$photoplog['inlineform'] = 1;
				if (($photoplog_inline_perm['caneditownfiles'] && $vbulletin->userinfo['userid'] == $photoplog['userid']) || $photoplog_inline_perm['caneditotherfiles'])
				{
					$photoplog['inlinecanedit'] = 1;
					$photoplog['select_row'] = photoplog_inline_select_row();
				}
				if (($photoplog_inline_perm['candeleteownfiles'] && $vbulletin->userinfo['userid'] == $photoplog['userid']) || $photoplog_inline_perm['candeleteotherfiles'])
				{
					$photoplog['inlinecandelete'] = 1;
				}
			}
			unset($photoplog_inline_perm);

			$photoplog['hscnt'] = intval($photoplog['hscnt']) + 1;

			photoplog_file_link($photoplog['userid'], $photoplog['fileid'], $photoplog['filename']);

			if ($vbulletin->options['photoplog_display_type'] == 1)
			{
				($hook = vBulletinHook::fetch_hook('photoplog_search_blockbit')) ? eval($hook) : false;

				eval('$photoplog[\'block_bits\'] .= "' . fetch_template('photoplog_block_bit') . '";');
				if ($photoplog['block_cols'] && ($photoplog_cnt_bits % $photoplog['block_cols'] == 0))
				{
					$photoplog['block_bits'] .= '</tr><tr>';
				}
			}
			else
			{
				($hook = vBulletinHook::fetch_hook('photoplog_search_filebit')) ? eval($hook) : false;

				eval('$photoplog[\'file_bits\'] .= "' . fetch_template('photoplog_file_bit') . '";');
			}
		}
	}

	$db->free_result($photoplog_file_infos);

	$photoplog['block_bits'] = eregi_replace(preg_quote("</tr><tr>")."$","",$photoplog['block_bits']);

	if ($photoplog_cnt_bits && $photoplog['block_cols'] && $vbulletin->options['photoplog_display_type'] == 1)
	{
		$photoplog_cnt_bits_temp = $photoplog_cnt_bits;
		while ($photoplog_cnt_bits_temp % $photoplog['block_cols'] != 0)
		{
			$photoplog['block_bits'] .= "<td class=\"alt1\" align=\"left\" valign=\"bottom\" width=\"".$photoplog['block_width']."%\">&nbsp;</td>";
			$photoplog_cnt_bits_temp++;
		}
		unset($photoplog_cnt_bits_temp);
	}

	if (!$photoplog_cnt_bits)
	{
		if ($vbulletin->options['photoplog_display_type'] == 1)
		{
			$photoplog['block_bits'] = '<td colspan="'.$photoplog['block_cols'].'" class="alt2">'.$vbphrase['photoplog_not_available'].'</td>';
		}
		else
		{
			$photoplog['file_bits'] = '<tr><td colspan="6" class="alt2">'.$vbphrase['photoplog_not_available'].'</td></tr>';
		}
	}

	$photoplog_search_results_phrase = ($photoplog_search_tot == 1) ? $vbphrase['photoplog_search_result'] : $vbphrase['photoplog_search_results'];
	$vbphrase[photoplog_file_list] = $photoplog_search_tot.' '.$photoplog_search_results_phrase.' '.$vbphrase['photoplog_on'].' &quot;'.$photoplog_search_query_form.'&quot;';

	if ($vbulletin->options['photoplog_display_type'] == 1)
	{
		($hook = vBulletinHook::fetch_hook('photoplog_search_blocklist')) ? eval($hook) : false;

		photoplog_output_page('photoplog_block_list', $vbphrase['photoplog_search_results']);
	}
	else
	{
		($hook = vBulletinHook::fetch_hook('photoplog_search_filelist')) ? eval($hook) : false;

		photoplog_output_page('photoplog_file_list', $vbphrase['photoplog_search_results']);
	}
}

($hook = vBulletinHook::fetch_hook('photoplog_search_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'query' && $_REQUEST['do'] != 'view')
{
	photoplog_index_bounce();
}

?>