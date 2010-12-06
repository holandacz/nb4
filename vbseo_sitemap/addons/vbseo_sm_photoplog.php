<?
	# vbSEO Google Sitemap Generator - PhotoPlog Add On.
	# Written by A v Klinken (http://www.LOSTTalk.net)

	# Full HTTP path to your gallery root (including trailing forwardslash)
	$gallery_url = 'http://www.noblood.org/gallery/';

	# Priority you want to assign to the sitemap pages
	$sitemap_priority = 0.5;

	# How often you wish to flag for updates
	$sitemap_update = 'weekly';

	# PhotoPlog Table Prefix
	#$tableprefix = 'gallery_';

	# URL used for Picture (index.php?n=)
	$fileurl = 'index.php?n=';

	# DON'T EDIT PAST HERE #############################################
	$photos = $db->query_read('SELECT fileid FROM photoplog_fileuploads WHERE moderate = 0');
	while ($photo = $db->fetch_array($photos))
	{
		$modified = $photo['dateline'];

		vbseo_add_url(
			$gallery_url . $fileurl . $photo['fileid'],
			$sitemap_priority, $modified, $sitemap_update
		);
	}
?>