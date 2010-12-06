/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 2.0.0
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

function issueattach_submit()
{
	if (!vB_Editor || !vB_Editor['vB_Editor_QR'])
	{
		// nothing to check against
		return true;
	}

	if (stripcode(vB_Editor['vB_Editor_QR'].get_editor_contents(), vB_Editor['vB_Editor_QR'].wysiwyg_mode) != '')
	{
		if (confirm(vbphrase['reply_text_sure_submit_attach']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	return true;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:45, Tue Nov 18th 2008
|| # RCS: $Revision: 17794 $
|| ####################################################################
\*======================================================================*/