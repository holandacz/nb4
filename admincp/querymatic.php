<?php
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);
 
// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('sql', 'user', 'cpuser','style');
$specialtemplates = array('products');
 
// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminstyles'))
{
    print_cp_no_permission();
}

if (!$vbulletin->debug)
{
	$userids = explode(',', str_replace(' ', '', $vbulletin->config['SpecialUsers']['canrunqueries']));
	if (!in_array($vbulletin->userinfo['userid'], $userids))
	{
		print_stop_message('no_permission_queries');
	}
}

print_cp_header($vbphrase['query_matic']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'list';
}

// ######################################################
// ##################### QUERY USER #####################
// ######################################################

if ($_REQUEST['do'] == 'query_user')
{
  require_once(DIR . '/includes/functions_misc.php');
  switch($_REQUEST['id'])
  {
				case 100: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 1 WHERE NOT(options & 1)";
          break;
				case 101:
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 1 WHERE options & 1";	
          break;
				case 102:
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 2 WHERE NOT(options & 2)";
          break;
				case 103:
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 2 WHERE options & 2";
          break;
				case 104: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 4 WHERE NOT(options & 4)";
          break;
				case 105: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 4 WHERE options & 4";	
          break;
				case 106: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 8 WHERE NOT(options & 8)";
          break;
				case 107:
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 8 WHERE options & 8";	
          break;		
				case 108: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 16 WHERE NOT(options & 16)";
          break;
				case 109:
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 16 WHERE options & 16";
          break;		
				case 110:
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 32 WHERE NOT(options & 32)";	
          break;
				case 111: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 32 WHERE options & 32";	
          break;		
				case 112: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 64 WHERE NOT(options & 64)";	
          break;
				case 113: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 64 WHERE options & 64";	
          break;		
				case 114: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 128 WHERE NOT(options & 128)";	
          break;
				case 115: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 128 WHERE options & 128";	
          break;		
				case 116: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 256 WHERE NOT(options & 256)";	
          break;
				case 117: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 256 WHERE options & 256";	
          break;		
				case 118: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 512 WHERE NOT(options & 512)";	
          break;
				case 119: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 512 WHERE options & 512";	
          break;		
				case 120: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 1024 WHERE NOT(options & 1024)";	
          break;
				case 121: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET SET options=options - 1024 WHERE options & 1024";	
          break;
				case 122: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 2048 WHERE NOT(options & 2048)";	
          break;
				case 123: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 2048 WHERE options & 2048";	
          break;
				case 124: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 4096 WHERE NOT(options & 4096)";	
          break;
				case 125: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 4096 WHERE options & 4096";	
          break;
				case 126: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET pmpopup = 1";	
          break;
				case 127: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET pmpopup = 0";	
          break;
				case 128: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options + 32768 WHERE NOT(options & 32768)";	
          break;
				case 129: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET options=options - 32768 WHERE options & 32768";	
          break;
        case 130: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET threadedmode = 0";	
          break;
        case 131: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET threadedmode = 1";	
          break;
        case 132: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET threadedmode = 2";	
          break;
        case 133: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET showvbcode = 0";	
          break;
        case 134: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET showvbcode = 1";	
          break;
        case 135: 
          $query = "UPDATE " . TABLE_PREFIX . "user SET showvbcode = 2";	
        break;             		
  }
        $db->hide_errors();
				$time_before = microtime();
				$db->query_write($query);
				$time_taken = fetch_microtime_difference($time_before);

				print_form_header('querymatic', 'user');
				print_table_header($vbphrase['vbulletin_message']);
				if ($errornum = $db->errno())
				{
					print_description_row(construct_phrase($vbphrase['an_error_occured_while_attempting_to_run_your_query'], $errornum, nl2br(htmlspecialchars_uni($db->error()))));
				}
				else
				{
					print_description_row(construct_phrase($vbphrase['affected_rows'], vb_number_format($db->affected_rows()), vb_number_format($time_taken, 4)));
				}
				print_table_footer();
        echo '<br />';
        $_REQUEST['do'] = 'user';  					
}

// ######################################################
// ##################### QUERY FORUM ####################
// ######################################################

if ($_REQUEST['do'] == 'query_forum')
{
  require_once(DIR . '/includes/functions_misc.php');
  switch($_REQUEST['id'])
  {
				case 100: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 1 WHERE NOT(options & 1)";	
          break;
				case 101: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 1 WHERE (options & 1)";	
          break;
				case 102: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 2 WHERE NOT(options & 2)";	
          break;
				case 103: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 2 WHERE (options & 2)";	
          break;
				case 104: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 4 WHERE NOT(options & 4)";	
          break;
				case 105: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 4 WHERE (options & 4)";	
          break;
				case 106: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 8 WHERE NOT(options & 8)";	
          break;
				case 107: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 8 WHERE (options & 8)";	
          break;
				case 108: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 16 WHERE NOT(options & 16)";	
          break;
				case 109: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 16 WHERE (options & 16)";	
          break;
				case 110: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 32 WHERE NOT(options & 32)";	
          break;
				case 111: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 32 WHERE (options & 32)";	
          break;
				case 112: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 64 WHERE NOT(options & 64)";	
          break;
				case 113: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 64 WHERE (options & 64)";	
          break;
				case 114: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 128 WHERE NOT(options & 128)";	
          break;
				case 115: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 128 WHERE (options & 128)";	
          break;
				case 116: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 256 WHERE NOT(options & 256)";	
          break;
				case 117: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 256 WHERE (options & 256)";	
          break;
				case 118: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 512 WHERE NOT(options & 512)";	
          break;
				case 119: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 512 WHERE (options & 512)";	
          break;
				case 120: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 1024 WHERE NOT(options & 1024)";	
          break;
				case 121: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 1024 WHERE (options & 1024)";	
          break;
				case 122: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 2048 WHERE NOT(options & 2048)";	
          break;
				case 123: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 2048 WHERE (options & 2048)";	
          break;
				case 124: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 4096 WHERE NOT(options & 4096)";	
          break;
				case 125: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 4096 WHERE (options & 4096)";	
          break;
				case 126: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 8192 WHERE NOT(options & 8192)";	
          break;
				case 127: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 8192 WHERE (options & 8192)";	
          break;
				case 128: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 16384 WHERE NOT(options & 16384)";	
          break;
				case 129: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 16384 WHERE (options & 16384)";	
          break;
				case 130: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 32768 WHERE NOT(options & 32768)";	
          break;
				case 131: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 32768 WHERE (options & 32768)";	
          break;
				case 132: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 65536 WHERE NOT(options & 65536)";	
          break;
				case 133: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 65536 WHERE (options & 65536)";	
          break;
				case 134: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options + 131072 WHERE NOT(options & 131072)";	
          break;
				case 135: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET options=options - 131072 WHERE (options & 131072)";	
          break;
				case 136: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET daysprune = 1";	
          break;
				case 137: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET daysprune = 7";	
          break;
				case 138: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET daysprune = 30";	
          break;
				case 139: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET daysprune = 365";	
          break;
				case 140: 
          $query = "UPDATE " . TABLE_PREFIX . "forum SET daysprune = -1";	
          break;
		
  }
        $db->hide_errors();
				$time_before = microtime();
				$db->query_write($query);
				$time_taken = fetch_microtime_difference($time_before);

				print_form_header('querymatic', 'user');
				print_table_header($vbphrase['vbulletin_message']);
				if ($errornum = $db->errno())
				{
					print_description_row(construct_phrase($vbphrase['an_error_occured_while_attempting_to_run_your_query'], $errornum, nl2br(htmlspecialchars_uni($db->error()))));
				}
				else
				{
					print_description_row(construct_phrase($vbphrase['affected_rows'], vb_number_format($db->affected_rows()), vb_number_format($time_taken, 4)));
				}
				print_table_footer();
        echo '<br />';
        $_REQUEST['do'] = 'forum';  					
}

// ##################### START MODIFY #####################
if ($_REQUEST['do'] == 'forum')
{
	print_table_start();
  print_table_header($vbphrase['query_matic_misc'] . ' - ' .$vbphrase['execute_sql_query']);
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_01'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=100">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=101">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt2" width="70%">'.$vbphrase['query_matic_forum_02'].'</td>';
  echo ' <td class="alt2" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=102">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=103">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_03'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=104">'.$vbphrase['categories'].'</a>] [<a href="querymatic.php?do=query_forum&id=105">'.$vbphrase['forums'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt2" width="70%">'.$vbphrase['query_matic_forum_04'].'</td>';
  echo ' <td class="alt2" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=106">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=107">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_05'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=108">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=109">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt2" width="70%">'.$vbphrase['query_matic_forum_06'].'</td>';
  echo ' <td class="alt2" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=110">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=111">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_07'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=112">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=113">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt2" width="70%">'.$vbphrase['query_matic_forum_08'].'</td>';
  echo ' <td class="alt2" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=114">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=115">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_09'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=116">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=117">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt2" width="70%">'.$vbphrase['query_matic_forum_10'].'</td>';
  echo ' <td class="alt2" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=118">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=119">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_11'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=120">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=121">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt2" width="70%">'.$vbphrase['query_matic_forum_12'].'</td>';
  echo ' <td class="alt2" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=122">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=123">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_13'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=124">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=125">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt2" width="70%">'.$vbphrase['query_matic_forum_14'].'</td>';
  echo ' <td class="alt2" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=126">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=127">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_15'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=128">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=129">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt2" width="70%">'.$vbphrase['query_matic_forum_16'].'</td>';
  echo ' <td class="alt2" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=130">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=131">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_17'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=132">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=133">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt2" width="70%">'.$vbphrase['query_matic_forum_18'].'</td>';
  echo ' <td class="alt2" align="right" width="30%">[<a href="querymatic.php?do=query_forum&id=134">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_forum&id=135">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';  
  echo '<tr>';
  echo ' <td class="alt1" width="70%">'.$vbphrase['query_matic_forum_19'].'</td>';
  echo ' <td class="alt1" align="right" width="30%">
        [<a href="querymatic.php?do=query_forum&id=136">'.$vbphrase['show_threads_from_last_day'].'</a>]<br />
        [<a href="querymatic.php?do=query_forum&id=137">'.$vbphrase['show_threads_from_last_week'].'</a>]<br />
        [<a href="querymatic.php?do=query_forum&id=138">'.$vbphrase['show_threads_from_last_month'].'</a>]<br />
        [<a href="querymatic.php?do=query_forum&id=139">'.$vbphrase['show_threads_from_last_year'].'</a>]<br />
        [<a href="querymatic.php?do=query_forum&id=140">'.$vbphrase['show_all_threads'].'</a>]
        </td>';  
  echo '</tr>';  
    
  print_table_footer(2, '', '', 0); 
}
// ##################### START MODIFY #####################
if ($_REQUEST['do'] == 'user')
{
	print_table_start();
  print_table_header($vbphrase['query_matic_user'] . ' - ' .$vbphrase['execute_sql_query']);
  echo '<tr>';
  echo ' <td class="alt1" width="60%">'.$vbphrase['query_matic_user_01'].'</td>';
  echo ' <td class="alt1" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=100">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=101">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt2" width="60%">'.$vbphrase['query_matic_user_02'].'</td>';
  echo ' <td class="alt2" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=102">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=103">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt1" width="60%">'.$vbphrase['query_matic_user_03'].'</td>';
  echo ' <td class="alt1" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=104">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=105">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt2" width="60%">'.$vbphrase['query_matic_user_04'].'</td>';
  echo ' <td class="alt2" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=106">'.$vbphrase['yes'].'</a>] [<a href="querymatic.php?do=query_user&id=107">'.$vbphrase['no'].'</a>]</td>';  
  echo '</tr>';     
  echo '<tr>';
  echo ' <td class="alt1" width="60%">'.$vbphrase['query_matic_user_05'].'</td>';
  echo ' <td class="alt1" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=108">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=109">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';     
  echo '<tr>';
  echo ' <td class="alt2" width="60%">'.$vbphrase['query_matic_user_06'].'</td>';
  echo ' <td class="alt2" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=110">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=111">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt1" width="60%">'.$vbphrase['query_matic_user_07'].'</td>';
  echo ' <td class="alt1" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=112">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=113">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt2" width="60%">'.$vbphrase['query_matic_user_08'].'</td>';
  echo ' <td class="alt2" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=114">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=115">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt1" width="60%">'.$vbphrase['query_matic_user_09'].'</td>';
  echo ' <td class="alt1" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=116">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=117">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt2" width="60%">'.$vbphrase['query_matic_user_10'].'</td>';
  echo ' <td class="alt2" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=118">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=119">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt2" width="60%">'.$vbphrase['query_matic_user_11'].'</td>';
  echo ' <td class="alt2" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=120">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=121">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>';                      
  echo '<tr>';
  echo ' <td class="alt1" width="60%">'.$vbphrase['query_matic_user_12'].'</td>';
  echo ' <td class="alt1" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=122">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=123">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>'; 
  echo '<tr>';
  echo ' <td class="alt2" width="60%">'.$vbphrase['query_matic_user_13'].'</td>';
  echo ' <td class="alt2" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=124">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=125">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>'; 
  echo '<tr>';
  echo ' <td class="alt1" width="60%">'.$vbphrase['query_matic_user_14'].'</td>';
  echo ' <td class="alt1" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=126">'.$vbphrase['on'].'</a>] [<a href="querymatic.php?do=query_user&id=127">'.$vbphrase['off'].'</a>]</td>';  
  echo '</tr>'; 
  echo '<tr>';
  echo ' <td class="alt2" width="60%">'.$vbphrase['query_matic_user_15'].'</td>';
  echo ' <td class="alt2" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=128">'.$vbphrase['newest_first'].'</a>] [<a href="querymatic.php?do=query_user&id=129">'.$vbphrase['oldest_first'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt1" width="60%">'.$vbphrase['thread_display_mode'].'</td>';
  echo ' <td class="alt1" align="right" width="40%">[<a href="querymatic.php?do=query_user&id=130">'.$vbphrase['linear'].'</a>] [<a href="querymatic.php?do=query_user&id=131">'.$vbphrase['threaded'].'</a>] [<a href="querymatic.php?do=query_user&id=132">'.$vbphrase['hybrid'].'</a>]</td>';  
  echo '</tr>';
  echo '<tr>';
  echo ' <td class="alt2" width="60%">'.$vbphrase['query_matic_user_16'].'</td>';
  echo ' <td class="alt2" align="right" width="40%">
          [<a href="querymatic.php?do=query_user&id=133">'.$vbphrase['do_not_show_editor_toolbar'].'</a>]<br />
          [<a href="querymatic.php?do=query_user&id=134">'.$vbphrase['show_standard_editor_toolbar'].'</a>]<br />
          [<a href="querymatic.php?do=query_user&id=135">'.$vbphrase['show_enhanced_editor_toolbar'].'</a>]<br />
         </td>';  
  echo '</tr>';        
  print_table_footer(2, '', '', 0); 
}

// ######################################################
// ##################### QUERY MISC #####################
// ######################################################

if ($_REQUEST['do'] == 'misc1')
{
require_once(DIR . '/install/mysql-schema.php');
$dbname =& $vbulletin->config['Database']['dbname'];

$tables = $db->query_read("SHOW TABLES");
print_table_start();
print_table_header($vbphrase['query_matic_misc_02']);
while ($table = $db->fetch_array($tables, DBARRAY_NUM))
{
  if (!isset($schema['CREATE']['query'][TABLE_PREFIX . $table[0]]))
	{
	  $say = strlen(TABLE_PREFIX)-1;
	  if ($say<0) 
    {
    $say=0;
    } 
	  $rest = substr(TABLE_PREFIX . $table[0],$say,4);
 		if ($rest == 'blog')
 		{
 			continue;
 		}
 		else
 		{
		print_label_row(TABLE_PREFIX . $table[0]);
		}
	}
}
print_table_break();


print_table_header($vbphrase['query_matic_misc_03']);
$standardtables = array_keys($schema['CREATE']['query']);
foreach ($standardtables AS $table)
{
 	$fields = $db->query("SHOW COLUMNS FROM " . TABLE_PREFIX . "$table");
 	$headershown = false;
 	while ($field = $db->fetch_array($fields))
 	{
 		if (($table == 'userfield' AND substr($field['Field'], 0, 5) == 'field') OR ($table == 'language' AND substr($field['field'], 0, 12) == 'phrasegroup_') OR ($table == 'administrator' AND $field['field'] == 'blogpermissions'))
 		{
 			continue;
 		}
 		if (strpos($schema['CREATE']['query']["$table"], "$field[Field] ") === false)
 		{
 			if (!$headershown)
 			{
 				print_table_header(TABLE_PREFIX . $table);
 				$headershown = true;
 			}
 			print_label_row($field['Field']);
 		}
 	}
}
print_table_footer();
}

// ######################################################
// ##################### QUERY MISC #####################
// ######################################################

if ($_REQUEST['do'] == 'misc2')
{
  require_once(DIR . '/includes/functions_misc.php');
  switch($_REQUEST['id'])
  {
  case 100 :
    $query = "TRUNCATE " . TABLE_PREFIX . "adminlog";
    break;
  case 101 :
    $query = "TRUNCATE " . TABLE_PREFIX . "moderatorlog";
    break;
  case 102 :
    $query = "TRUNCATE " . TABLE_PREFIX . "cronlog";
    break;
  case 103 :
    $query = "TRUNCATE " . TABLE_PREFIX . "cpsession";
    break;
  case 104 :
    $query = "TRUNCATE " . TABLE_PREFIX . "externalcache";
    break;
  case 105 :
    $query = "TRUNCATE " . TABLE_PREFIX . "rsslog";
    break;
  case 106 :
    $query = "TRUNCATE " . TABLE_PREFIX . "session";
    break;            
  }    
        $db->hide_errors();
				$time_before = microtime();
				$db->query_write($query);
				$time_taken = fetch_microtime_difference($time_before);

				print_form_header('querymatic', 'user');
				print_table_header($vbphrase['vbulletin_message']);
				if ($errornum = $db->errno())
				{
					print_description_row(construct_phrase($vbphrase['an_error_occured_while_attempting_to_run_your_query'], $errornum, nl2br(htmlspecialchars_uni($db->error()))));
				}
				else
				{
					print_description_row(construct_phrase($vbphrase['affected_rows'], vb_number_format($db->affected_rows()), vb_number_format($time_taken, 4)));
				}
				print_table_footer();
        echo '<br />';
}
print_cp_footer();
?> 
