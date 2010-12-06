<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Project Tools 2.0.0 - Licence Number VBP05E32E9
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

if (!class_exists('vB_DataManager'))
{
	exit;
}

/**
* Class to do data save/delete operations for PT issue subscriptiosn.
*
* @package	vBulletin Project Tools
* @date		$Date: 2007-08-06 06:44:36 -0500 (Mon, 06 Aug 2007) $
*/
class vB_DataManager_Pt_IssueSubscribe extends vB_DataManager
{
	/**
	* Array of recognized/required fields and their types
	*
	* @var	array
	*/
	var $validfields = array(
		'issueid'       => array(TYPE_UINT, REQ_YES),
		'userid'        => array(TYPE_UINT, REQ_YES),
		'subscribetype' => array(TYPE_STR,  REQ_YES, 'if (!in_array($data, array("instant", "daily", "weekly", "none"))) { $data = "none"; } return true;')
	);

	/**
	* Information and options that may be specified for this DM
	*
	* @var	array
	*/
	var $info = array();

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'pt_issuesubscribe';

	/**
	* Arrays to store stuff to save to admin-related tables
	*
	* @var	array
	*/
	var $pt_issuesubscribe = array();

	/**
	* Condition for update query
	*
	* @var	array
	*/
	var $condition_construct = array('issueid = %1$d AND userid = %2$d', 'issueid', 'userid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function vB_DataManager_Pt_IssueSubscribe(&$registry, $errtype = ERRTYPE_STANDARD)
	{
		parent::vB_DataManager($registry, $errtype);

		($hook = vBulletinHook::fetch_hook('pt_issuesubscribedata_start')) ? eval($hook) : false;
	}

	/**
	* Any checks to run immediately before saving. If returning false, the save will not take place.
	*
	* @param	boolean	Do the query?
	*
	* @return	boolean	True on success; false if an error occurred
	*/
	function pre_save($doquery = true)
	{
		if ($this->presave_called !== null)
		{
			return $this->presave_called;
		}

		if (!empty($this->pt_issuesubscribe['userid']) OR !empty($this->pt_issuesubscribe['issueid']))
		{
			// we're changing one of these fields, check for dupes
			if ($old = $this->registry->db->query_first("
				SELECT *
				FROM " . TABLE_PREFIX . "pt_issuesubscribe
				WHERE issueid = " . $this->fetch_field('issueid') . "
					AND userid = " . $this->fetch_field('userid')
			))
			{
				// dupe, change to an update of that row
				$this->set_existing($old);
			}
		}

		$return_value = true;
		($hook = vBulletinHook::fetch_hook('pt_issuesubscribedata_presave')) ? eval($hook) : false;

		$this->presave_called = $return_value;
		return $return_value;
	}

	/**
	* Additional data to update after a save call (such as denormalized values in other tables).
	* In batch updates, is executed for each record updated.
	*
	* @param	boolean	Do the query?
	*/
	function post_save_each($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('pt_issuesubscribedata_postsave')) ? eval($hook) : false;

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('pt_issuesubscribedata_delete')) ? eval($hook) : false;
		return true;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:45, Tue Nov 18th 2008
|| # RCS: $Revision: 17793 $
|| ####################################################################
\*======================================================================*/
?>