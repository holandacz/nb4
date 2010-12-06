/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

============================================================================================
Backup, backup, backup. Now proceed. :)
============================================================================================

============================================================================================
PhotoPlog Pro NEW Installation
============================================================================================

1) In the ZIP archive find the /photoplog directory, open the PhotoPlog config.php
   file in a TEXT ONLY editor, and set the server path to your main forum directory
   (check with your host if you are not sure of the server path, and if you get a
   blank page when accessing the gallery, the server path is probably not correct)

2) In the ZIP archive find the /forum/includes directory, open the PhotoPlog
   photoplog_prefix.php file in a TEXT ONLY editor, and set a PhotoPlog table prefix
   only if you need to set one (see the comments in the photoplog_prefix.php file !!!)

3) In the ZIP archive again find the /photoplog directory and FTP all the contents inside
   the /photoplog directory into a directory named photoplog on your server, keeping the
   same structure as in the ZIP file

4) Set 777 permissions on the photoplog 'images' directory on your server, as necessary
   (use chmod or FTP to set 777 permissions on the photoplog 'images' directory if needed)

5) In the ZIP archive find the /forum directory and note how the directory structure is the
   same as it is on your vB board, so FTP the files inside the PhotoPlog /forum subdirectories
   into the equivalent vB directories

6) In the ZIP archive find product-photoplog.xml and import the product-photoplog.xml file as
   a product via the vB ACP (vB ACP -> Plugin System -> Manage Products -> Add/Import Product)
   and wait until it is done !!!

7) Go to the vB ACP and refresh your ACP index page (e.g., hit Ctrl-F5 when in the ACP) and
   look for the PhotoPlog group in the side menu

8) Go through all of the PhotoPlog settings in the side menu and set them accordingly, and
   especially make sure to edit the General Settings as these need to be correct

9) Go to the vB ACP Usergroups -> Usergroup Manager -> Edit Usergroup and set the PhotoPlog
   permissions for each usergroup

10) Now add categories via Add New Category in the PhotoPlog side menu and then visit
    http://www.YOUR-SITE.com/YOUR-PHOTOPLOG-DIR/index.php to start uploading files

11) If you happen to upgrade vBulletin, then delete the vbulletin_textedit.js file from inside
    the PhotoPlog /images directory if present (do NOT delete the vbulletin_textedit.js file
    inside the vB /clientscript directory) and PhotoPlog will regenerate the file for you

12) Scroll down to the 'PhotoPlog Pro Comments' section of this README file for other goodies

============================================================================================
PhotoPlog Pro UPGRADE Installation (including from the Lite version)
============================================================================================

1) Revert any changed PhotoPlog templates (vB ACP -> PhotoPlog Pro -> Edit Templates)
   and yes, this needs to be done or else PhotoPlog will not necessarily work correctly
   but :NOTE: check the release thread as it may not be necessary to revert everything

2) Delete the old PhotoPlog ZIP file (do not use anything inside the old file - do not
   follow the old README.txt - nothing - just disregard it all)

3) In the new ZIP archive find the /photoplog directory and FTP all the contents inside the
   /photoplog directory (EXCEPT the PhotoPlog config.php file !!!) into the photoplog directory
   on your server, keeping the same structure as in the ZIP file, overwriting old files

4) In the new ZIP archive find the /forum directory and note how the directory structure is the
   same as it is on your vB board, so FTP all files inside the PhotoPlog /forum subdirectories
   (EXCEPT the photoplog_prefix.php file IF it *already exists* on your server !!!) into the
   equivalent vB directories, overwriting old files

5) In the new ZIP archive find product-photoplog.xml and import the product-photoplog.xml
   file as a product via the vB ACP (do NOT uninstall the old version - do allow overwrite)
   (vB ACP -> Plugin System -> Manage Products -> Add/Import Product)
   and wait until it is done !!!

6) Go to the vB ACP and refresh your ACP index page (e.g., hit Ctrl-F5 when in the ACP) and
   look for the PhotoPlog group in the side menu

7) Go through all of the PhotoPlog settings in the side menu and set them accordingly, and
   especially make sure to edit the General Settings as these need to be correct

8) Go to the vB ACP Usergroups -> Usergroup Manager -> Edit Usergroup and set the PhotoPlog
   permissions for each usergroup

9) Make sure to review the various PhotoPlog settings and usergroup permissions as there
   may be new options available

10) If you happen to upgrade vBulletin, then delete the vbulletin_textedit.js file from inside
    the PhotoPlog /images directory if present (do NOT delete the vbulletin_textedit.js file
    inside the vB /clientscript directory) and PhotoPlog will regenerate the file for you

11) Scroll down to the 'PhotoPlog Pro Comments' section of this README file for other goodies

============================================================================================
PhotoPlog Pro UN-Installation
============================================================================================

1) Go to the vB ACP -> Plugin System -> Manage Products and uninstall the
   "PhotoPlog Pro: The Pro Gallery" product
   (vB ACP -> Plugin System -> Manage Products -> Uninstall "PhotoPlog Pro: The Pro Gallery")

2) Delete all the PhotoPlog files you FTP'd on install from your server (if you have shell
   access carefully try 'rm -r [photoplog directory]' to delete the photoplog directory,
   and remember to delete the PhotoPlog files from your vB /forum subdirectories too)

3) Call admincp/index.php?do=buildbitfields from your browser to ensure its occurrence

============================================================================================
PhotoPlog Pro Comments
============================================================================================

1) Do not mess with the files in the 'images' directory as PhotoPlog 
   considers names to prevent duplicates

2) File types currently uploadable: GIF, JPG, PNG, gif, jpg, png

3) Want thumbnails on forum home and/or member info?

	Use $photoplog[thumbnails] and/or $photoplog[minithumbnails] right after
	$navbar appears in the FORUMHOME and/or MEMBERINFO templates

4) If you accidentally bunk the install/upgrade, redo it and then call the
   following from your browser:

	http://www.domain.com/forum/admincp/index.php?do=buildbitfields

5) Accidentally UN-installing product-photoplog.xml via vB ACP will ruin
   your gallery without a MySQL backup available to reinstate

6) Want a gallery link in postbit with count?

   Edit the vB postbit(_legacy) template:

   Find:
					<div>
						$vbphrase[posts]: $post[posts]
					</div>
   After add:
					<div>
						$vbphrase[photoplog_gallery]:
						<if condition="$post[photoplog_filecount]">
							<a href="/photoplog/index.php?$session[sessionurl]u=$post[userid]">$post[photoplog_filecount]</a>
						<else />
							$post[photoplog_filecount]
						</if>
					</div>

7) Want a gallery comment count in postbit too?

   Edit the vB postbit(_legacy) template:

   Find:
					<div>
						$vbphrase[posts]: $post[posts]
					</div>
   After add:
					<div>
						$vbphrase[photoplog_comments]: $post[photoplog_commentcount]
					</div>

8) Want a thumbnail popup selector of your images under the smilies box when making a forum post?

   Edit the vB editor_smiliebox template, placing the following at the very end of the template:

	<div class="smallfont" align="center">[<a href="$vboptions[photoplog_script_dir]/index.php$session[sessionurl_q]" onclick="openWindow('$vboptions[photoplog_script_dir]/selector.php?$session[sessionurl]e=$editorid', 585, 215, 'selector'); return false;">$vbphrase[photoplog_images]</a>]</div>

   If you get a JavaScript 'permission denied' error in your browser, use this instead where you
   may need to change photoplog in the openWindow link to match with your gallery directory:

	<div class="smallfont" align="center">[<a href="$vboptions[photoplog_script_dir]/index.php$session[sessionurl_q]" onclick="openWindow('/photoplog/selector.php?$session[sessionurl]e=$editorid', 585, 215, 'selector'); return false;">$vbphrase[photoplog_images]</a>]</div>

9) Want newest uploads and comment to appear on the vB search results page?

   Edit the vB search_results template:

      After:

		$navbar

      Add:

		$photoplog[searchinfo]

   Edit the vB STANDARD_ERROR template:

      After:

		<if condition="$navbar">
		$navbar
		<else />
		<br /><br /><br />
		</if>

      Add:

		$photoplog[searchinfo]

10) Want the latest thumb to appear on the vB memberlist with gallery counts?

    Edit the vB memberlist_resultsbit template:

	Find:

	<td class="alt1Active" align="$stylevar[left]" id="u$userinfo[userid]">
		<a href="member.php?$session[sessionurl]u=$userinfo[userid]">$userinfo[musername]</a>
		<if condition="$show['usertitlecol']"><div class="smallfont">$userinfo[usertitle]</div></if>
	</td>

	Replace with:

	<td class="alt2Active">
		<if condition="$photoplog_memberlist_thumbs[$userinfo[userid]]">
			{$photoplog_memberlist_thumbs[$userinfo[userid]]}
		</if>
	</td>
	<td class="alt1Active" align="$stylevar[left]" id="u$userinfo[userid]">
		<a href="member.php?$session[sessionurl]u=$userinfo[userid]">$userinfo[musername]</a>
		<if condition="$show['usertitlecol']"><div class="smallfont">$userinfo[usertitle]</div></if>
		<div class="smallfont">$vbphrase[photoplog_gallery]: 
			<if condition="$photoplog_memberlist_thumbs[$userinfo[userid]]">
				<a href="$photoplog[location]/index.php?$session[sessionurl]u=$userinfo[userid]">$userinfo[photoplog_filecount]</a>
			<else />
				$userinfo[photoplog_filecount]
			</if>
		</div>
		<div class="smallfont">$vbphrase[photoplog_comments]: $userinfo[photoplog_commentcount]</div>
	</td>

    Edit the vB memberlist template:

	Find:

	<td class="thead" align="$stylevar[left]" nowrap="nowrap"><a href="$sorturl&amp;order=ASC&amp;sort=username&amp;pp=$perpage$usergrouplink">$vbphrase[username]</a> $sortarrow[username]</td>

	Above add:

	<td class="thead" nowrap="nowrap" style="width: 1px;">$vbphrase[photoplog_last_upload]</td>

11) Want to be able to use advanced memberlist search to find members with uploaded files and/or comments?

    Edit the vB memberlist_search template:

	Find:

			<fieldset class="fieldset">
				<legend>$vbphrase[post_count]</legend>
				<table cellpadding="0" cellspacing="$stylevar[formspacer]" border="0">
				<tr>
					<td>
						$vbphrase[is_greater_than_or_equal_to]<br />
						<input type="text" class="bginput" size="25" name="postslower" value="" />
					</td>
					<td>
						$vbphrase[is_less_than]<br />
						<input type="text" class="bginput" size="25" name="postsupper" value="" />
					</td>
				</tr>
				</table>
			</fieldset>

	After add:

			<fieldset class="fieldset">
				<legend>$vbphrase[photoplog_gallery_count]</legend>
				<table cellpadding="0" cellspacing="$stylevar[formspacer]" border="0">
				<tr>
					<td>
						$vbphrase[is_greater_than_or_equal_to]<br />
						<input type="text" class="bginput" size="25" name="photoplog_fileslower" value="" />
					</td>
					<td>
						$vbphrase[is_less_than]<br />
						<input type="text" class="bginput" size="25" name="photoplog_filesupper" value="" />
					</td>
				</tr>
				</table>
			</fieldset>

			<fieldset class="fieldset">
				<legend>$vbphrase[photoplog_comment_count]</legend>
				<table cellpadding="0" cellspacing="$stylevar[formspacer]" border="0">
				<tr>
					<td>
						$vbphrase[is_greater_than_or_equal_to]<br />
						<input type="text" class="bginput" size="25" name="photoplog_commentslower" value="" />
					</td>
					<td>
						$vbphrase[is_less_than]<br />
						<input type="text" class="bginput" size="25" name="photoplog_commentsupper" value="" />
					</td>
				</tr>
				</table>
			</fieldset>

    Edit the vB memberlist_search template:

	Find:

							<option value="posts">$vbphrase[post_count]</option>

	After add:

							<option value="photoplog_filessort">$vbphrase[photoplog_gallery_count]</option>
							<option value="photoplog_commentssort">$vbphrase[photoplog_comment_count]</option>

12) Want to show public albums on the member info page?

	Use $photoplog[memberalbums] right after $navbar appears in
	the vB MEMBERINFO template

13) Want a gallery link on the vB navbar?

    Edit the vB navbar template:

	Find:

		<td class="vbmenu_control"><a href="calendar.php$session[sessionurl_q]">$vbphrase[calendar]</a></td>

	After add:

		<td class="vbmenu_control"><a href="/YOUR-PHOTOPLOG-DIR/index.php$session[sessionurl_q]">$vbphrase[photoplog_gallery]</a></td>

14) If merging, pruning, or deleting members, use the PhotoPlog ACP Mass Move/Delete feature to do
    what you want with the uploaded files, and use the vB ACP features to merge, prune, or delete

============================================================================================
Enjoy!
============================================================================================