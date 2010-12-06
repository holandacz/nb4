<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once('./global.php');
//define('SUBSCRIBE_FORUMS_TABLE', '_sub1');
define('SUBSCRIBE_FORUMS_TABLE', 'subscribeforum');

if (strtolower($_REQUEST['do']) == 'subscribe')
{
	$force	= isset($_REQUEST['force']) && strtolower($_REQUEST['force'])=='yes' ? true : false;
	$userid	= isset($_REQUEST['userid']) && is_numeric($_REQUEST['userid']) ? $_REQUEST['userid'] : 0;

	subscribe_to_forum(array(4,6,10,16,20,25), $userid, $force);
}

function subscribe_to_forum($membergroupids, $userid = 0, $force = false){
	global $db;

	//build where clause for groups
	$grps = array();
	foreach($membergroupids as $grp){
		$grps[] = 'find_in_set("'. $grp . '", u.membergroupids)';
	}
	//not using grps above
$where_grps = '
u.usergroupid = 5 ||
u.usergroupid = 6 ||
u.usergroupid = 7 ||
u.usergroupid = 11 ||
u.usergroupid = 12 ||
u.usergroupid = 20 ||
u.usergroupid = 21 ||
u.usergroupid = 22 ||
find_in_set("10", u.membergroupids)||
find_in_set("16", u.membergroupids)||
find_in_set("25", u.membergroupids)
';



	if ($force){

		$useridWhere = $userid ? 'u.userid=' . $userid . ' AND ' : '';
		$sql = '
			SELECT forumid
			FROM forum
			WHERE (ei_default_to_pros =1)
		';
		echo $sql . '<br/><br/>';
		$db->query_read_slave($sql);
		if ($forumids_result = $db->query_read_slave($sql)){
			while ($forumids_rec = $db->fetch_array($forumids_result))
			{
				$forumids[]			= $forumids_rec['forumid'];
			}
		}



		$sql = '
		UPDATE ' . TABLE_PREFIX . SUBSCRIBE_FORUMS_TABLE . ' AS s
		INNER JOIN ' . TABLE_PREFIX . 'user AS u ON (u.userid = s.userid)
		SET emailupdate = 1
		WHERE (' . $useridWhere . '(find_in_set(s.forumid, "' . implode(',', $forumids) . '")) AND (!emailBad OR emailBad IS NULL) AND (' .
		$where_grps .
		'))
		';
		echo $sql . '<br/><br/>';
		$db->query_write($sql);
	}

	$useridWhere = $userid ? 'u.userid=' . $userid . ' OR ' : '';
	$sql = '
		INSERT IGNORE INTO ' . TABLE_PREFIX . SUBSCRIBE_FORUMS_TABLE . ' (userid, forumid, emailupdate)
		SELECT DISTINCT
			u.userid,
			f.forumid,
			1
		FROM
			' . TABLE_PREFIX . 'user AS u,
			' . TABLE_PREFIX . 'forum AS f
			LEFT JOIN ' . TABLE_PREFIX . SUBSCRIBE_FORUMS_TABLE . ' AS s ON (f.forumid = s.forumid)
		WHERE ((' . $useridWhere . '
			(' .
			$where_grps .
			')) AND f.ei_default_to_pros =1
		)';

			/*(!emailBad OR emailBad IS NULL) AND*/
		echo $sql . '<br/><br/>';
		$db->query_write($sql);


		echo "done!";
}
?>
