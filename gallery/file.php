<?php

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++      PhotoPlog Copyright © 2001-2007 ThinkDing LLC - All Rights Reserved      ++++++++++++
++++++++++++        This file may not be redistributed in whole or significant part        ++++++++++++
++++++++++++     PhotoPlog Pro is NOT free software :: visit photoplog.com for details     ++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

// ###################### REQUIRE PLOG BACK-END ###########################
define('PHOTOPLOG_THIS_SCRIPT','file');
define('PHOTOPLOG_LEVEL','view');
define('PHOTOPLOG_HTTPD','file');
require_once('./settings.php');

// ############################ Start File Page ###########################
($hook = vBulletinHook::fetch_hook('photoplog_file_start')) ? eval($hook) : false;

function photoplog_hexdec()
{
	global $stylevar;

	$photoplog_r = 255;
	$photoplog_g = 255;
	$photoplog_b = 255;

	if ($stylevar['panel_bgcolor'])
	{
		$photoplog_rgb = str_replace(array('#',';'),'',$stylevar['panel_bgcolor']);

		if (vbstrlen($photoplog_rgb) == 6)
		{
			$photoplog_r = intval(hexdec(substr($photoplog_rgb, 0, 2)));
			$photoplog_g = intval(hexdec(substr($photoplog_rgb, 2, 2)));
			$photoplog_b = intval(hexdec(substr($photoplog_rgb, 4, 2)));
		}
		else if (vbstrlen($photoplog_rgb) == 3)
		{
			$photoplog_r = intval(hexdec(str_repeat(substr($photoplog_rgb, 0, 1), 2)));
			$photoplog_g = intval(hexdec(str_repeat(substr($photoplog_rgb, 1, 1), 2)));
			$photoplog_b = intval(hexdec(str_repeat(substr($photoplog_rgb, 2, 1), 2)));
		}
	}

	return array($photoplog_r,$photoplog_g,$photoplog_b);
}

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'view';
}

if ($_REQUEST['do'] == 'view')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'n' => TYPE_UINT,
		'w' => TYPE_NOHTML
	));

	$photoplog_file_id = $vbulletin->GPC['n'];
	$photoplog_thumb_type = $vbulletin->GPC['w'];

	$photoplog_file_info = $db->query_first_slave("SELECT userid, filename
		FROM " . PHOTOPLOG_PREFIX . "photoplog_fileuploads
		WHERE fileid = ".intval($photoplog_file_id)."
		$photoplog_catid_sql1
		$photoplog_admin_sql1
	");

	if ($photoplog_file_info)
	{
		$photoplog_jpg_quality = intval($vbulletin->options['photoplog_jpg_quality']);

		if ($vbulletin->options['photoplog_frontdrop_img'])
		{
			$vbulletin->options['photoplog_backdrop_img'] = '';
		}

		if ($photoplog_thumb_type == 'l')
		{
			$photoplog_thumb_dir = 'large';
			$photoplog_size_arr = explode(",",str_replace(" ","",$vbulletin->options['photoplog_large_size']));
		}
		else if ($photoplog_thumb_type == 'm')
		{
			$photoplog_thumb_dir = 'medium';
			$photoplog_size_arr = explode(",",str_replace(" ","",$vbulletin->options['photoplog_medium_size']));
		}
		else if ($photoplog_thumb_type == 's')
		{
			$photoplog_thumb_dir = 'small';
			$photoplog_size_arr = explode(",",str_replace(" ","",$vbulletin->options['photoplog_small_size']));
		}
		else if ($photoplog_thumb_type == 'o')
		{
			$photoplog_thumb_dir = ''; //original
			$photoplog_size_arr = array(0,0);
		}
		else
		{
			$photoplog_thumb_dir = $vbulletin->options['photoplog_default_size'];
			$photoplog_size_arr = array(0,0);

			$photoplog_thumb_type = '';
			if (in_array($photoplog_thumb_dir, array('small','medium','large')))
			{
				$photoplog_thumb_type = substr($photoplog_thumb_dir, 0, 1);
				$photoplog_default_size = 'photoplog_'.$photoplog_thumb_dir.'_size';
				$photoplog_size_arr = explode(",",str_replace(" ","",$vbulletin->options["$photoplog_default_size"]));
			}

			if (!$photoplog_thumb_type)
			{
				$photoplog_thumb_type = 'o';
				$photoplog_thumb_dir = '';
			}
		}

		if (
			(!$vbulletin->options['photoplog_backdrop_img'] && $photoplog_thumb_dir == 'small')
				||
			(!$vbulletin->options['photoplog_watermark_img'] && $photoplog_thumb_dir != 'small')
		)
		{
			$photoplog_file_location = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_info['userid']."/".$photoplog_file_info['filename'];

			if ($photoplog_thumb_dir)
			{
				$photoplog_file_location = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_info['userid']."/".$photoplog_thumb_dir."/".$photoplog_file_info['filename'];
			}

			$photoplog_file_check = @getimagesize($photoplog_file_location);

			if (
				$photoplog_file_check === false
					||
				!is_array($photoplog_file_check)
					||
				empty($photoplog_file_check)
					||
				!in_array($photoplog_file_check[2],array(1,2,3))
			)
			{
				header('Content-type: image/gif');
				readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
				exit();
			}
			else
			{
				if ($photoplog_file_check[2] == '1')
				{
					header("Content-type: image/gif");
				}
				else if ($photoplog_file_check[2] == '2')
				{
					header("Content-type: image/jpeg");
				}
				else if ($photoplog_file_check[2] == '3')
				{
					header("Content-type: image/png");
				}
				else
				{
					header('Content-type: image/gif');
					readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
					exit();
				}

				readfile($photoplog_file_location);
				exit();
			}
		}

		$photoplog_file_location = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_info['userid']."/".$photoplog_file_info['filename'];

		$photoplog_file_check = @getimagesize($photoplog_file_location);

		$photoplog_process_copy = 0;

		if (
			$photoplog_file_check === false
				||
			!is_array($photoplog_file_check)
				||
			empty($photoplog_file_check)
				||
			!in_array($photoplog_file_check[2],array(1,2,3))
		)
		{
			header('Content-type: image/gif');
			readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
			exit();
		}
		else
		{
			$photoplog_process_copy = 1;
		}

		$photoplog_use_original = 0;

		if ($photoplog_file_check[2] == '1' && count($photoplog_size_arr) == 2)
		{
			$photoplog_thumb_w = intval($photoplog_size_arr[0]);
			$photoplog_thumb_h = intval($photoplog_size_arr[1]);

			$photoplog_file_contents = @file_get_contents($photoplog_file_location);
			$photoplog_frame_count = count(preg_split('#\x00[\x00-\xFF]\x00\x2C#',$photoplog_file_contents));
			unset($photoplog_file_contents);

			if (
				(
					!$photoplog_thumb_dir
						||
					(
						$photoplog_file_check[0] <= $photoplog_thumb_w
							&&
						$photoplog_file_check[1] <= $photoplog_thumb_h
					)
				)
					&&
				$photoplog_frame_count > 2
			)
			{
				$photoplog_use_original = 1;
			}
		}

		if ($photoplog_use_original)
		{
			header('Content-type: image/gif');
			readfile($photoplog_file_location);
			exit();
		}

		if ($photoplog_thumb_dir)
		{
			$photoplog_file_location = PHOTOPLOG_BWD."/".$vbulletin->options['photoplog_upload_dir']."/".$photoplog_file_info['userid']."/".$photoplog_thumb_dir."/".$photoplog_file_info['filename'];

			$photoplog_file_check = @getimagesize($photoplog_file_location);

			$photoplog_process_copy = 0;

			if (
				$photoplog_file_check === false
					||
				!is_array($photoplog_file_check)
					||
				empty($photoplog_file_check)
					||
				!in_array($photoplog_file_check[2],array(1,2,3))
			)
			{
				header('Content-type: image/gif');
				readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
				exit();
			}
			else
			{
				$photoplog_process_copy = 1;
			}
		}

		if ($photoplog_process_copy)
		{
			$photoplog_img_feed = 0;

			if ($photoplog_file_check[2] == '1')
			{
				if (imagetypes() & IMG_GIF)
				{
					$photoplog_img_feed = @imagecreatefromgif($photoplog_file_location);
				}
			}

			if ($photoplog_file_check[2] == '2')
			{
				if (imagetypes() & IMG_JPG)
				{
					$photoplog_img_feed = @imagecreatefromjpeg($photoplog_file_location);
				}
			}

			if ($photoplog_file_check[2] == '3')
			{
				if (imagetypes() & IMG_PNG)
				{
					$photoplog_img_feed = @imagecreatefrompng($photoplog_file_location);
					@imagealphablending($photoplog_img_feed, false);
					@imagesavealpha($photoplog_img_feed, true);
				}
			}

			$photoplog_na_feed = 0;

			if (!$photoplog_img_feed)
			{
				$photoplog_na_feed = 1;
				$photoplog_file_check[2] = '2';
				$photoplog_img_feed = @imagecreatetruecolor(150,35);
				$photoplog_bg_color = @imagecolorallocate($photoplog_img_feed,255,255,255);
				$photoplog_text_color = @imagecolorallocate($photoplog_img_feed,0,0,0);
				@imagefilledrectangle($photoplog_img_feed,1,1,148,33,$photoplog_bg_color);
				@imagestring($photoplog_img_feed,5,17,9,$vbphrase['photoplog_not_available'],$photoplog_text_color);
			}

			$photoplog_img_width = imagesx($photoplog_img_feed);
			$photoplog_img_height = imagesy($photoplog_img_feed);

			if (
				$vbulletin->options['photoplog_watermark_img']
					&&
				!$photoplog_na_feed
					&&
				($photoplog_thumb_type == 'l' || $photoplog_thumb_type == 'o')
			)
			{
				$photoplog_logo_location = $vbulletin->options['photoplog_watermark_img'];
				$photoplog_logo_check = @getimagesize($photoplog_logo_location);

				if (!(
					$photoplog_logo_check === false
						||
					!is_array($photoplog_logo_check)
						||
					empty($photoplog_logo_check)
						||
					!in_array($photoplog_logo_check[2],array(1,2,3))
				))
				{
					if ($photoplog_logo_check[2] == '1')
					{
						if (imagetypes() & IMG_GIF)
						{
							$photoplog_logo_feed = @imagecreatefromgif($photoplog_logo_location);
						}
					}

					if ($photoplog_logo_check[2] == '2')
					{
						if (imagetypes() & IMG_JPG)
						{
							$photoplog_logo_feed = @imagecreatefromjpeg($photoplog_logo_location);
						}
					}

					if ($photoplog_logo_check[2] == '3')
					{
						if (imagetypes() & IMG_PNG)
						{
							$photoplog_logo_feed = @imagecreatefrompng($photoplog_logo_location);
						}
					}

					if ($photoplog_logo_feed)
					{
						$photoplog_logo_width = imagesx($photoplog_logo_feed);
						$photoplog_logo_height = imagesy($photoplog_logo_feed);

						if (
							$photoplog_logo_width >= $photoplog_img_width
								||
							$photoplog_logo_height * 3 >= $photoplog_img_height
						)
						{
							$photoplog_logo_feed = '';
						}

						switch($vbulletin->options['photoplog_watermark_loc'])
						{
							case 1:
								// upper left
								$photoplog_logo_x = 0;
								$photoplog_logo_y = 0;
								break;
							case 2:
								// upper right
								$photoplog_logo_x = $photoplog_img_width - $photoplog_logo_width;
								$photoplog_logo_y = 0;
								break;
							case 3:
								// lower left
								$photoplog_logo_x = 0;
								$photoplog_logo_y = $photoplog_img_height - $photoplog_logo_height;
								break;
							case 4:
								// lower right
								$photoplog_logo_x = $photoplog_img_width - $photoplog_logo_width;
								$photoplog_logo_y = $photoplog_img_height - $photoplog_logo_height;
								break;
							default:
								// upper left
								$photoplog_logo_x = 0;
								$photoplog_logo_y = 0;
						}
					}

/*
					if ($photoplog_logo_feed && $photoplog_logo_check[2] == '1')
					{
						imagealphablending($photoplog_logo_feed, true);

						$photoplog_trans_color = null;

						$photoplog_mark_feed = imagecreate($photoplog_img_width,
							$photoplog_img_height);

						imagealphablending($photoplog_mark_feed, true);

						imagetruecolortopalette($photoplog_img_feed, 100, 256);
						imagepalettecopy($photoplog_mark_feed, $photoplog_img_feed);

						imagefill($photoplog_mark_feed, 0, 0, $photoplog_trans_color);
						imagecolortransparent($photoplog_mark_feed, $photoplog_trans_color);

						imagecopy($photoplog_mark_feed, $photoplog_img_feed,
							0, 0,
							0, 0, $photoplog_img_width, $photoplog_img_height);

						imagecopy($photoplog_mark_feed, $photoplog_logo_feed,
							$photoplog_logo_x, $photoplog_logo_y,
							0, 0, $photoplog_logo_width, $photoplog_logo_height);
					}
*/

					if ($photoplog_logo_feed && ($photoplog_logo_check[2] == '1' || $photoplog_logo_check[2] == '3'))
					{
						imagealphablending($photoplog_logo_feed, true);

						$photoplog_mark_feed = imagecreatetruecolor($photoplog_img_width,
							$photoplog_img_height);

						$photoplog_rgb = photoplog_hexdec();
						$photoplog_trans_color = imagecolorallocate($photoplog_mark_feed,
							$photoplog_rgb[0], $photoplog_rgb[1], $photoplog_rgb[2]);

						imagefill($photoplog_mark_feed, 0, 0, $photoplog_trans_color);
						imagecolortransparent($photoplog_mark_feed, $photoplog_trans_color);

						imagealphablending($photoplog_mark_feed, true);

						imagecopy($photoplog_mark_feed, $photoplog_img_feed,
							0, 0,
							0, 0, $photoplog_img_width, $photoplog_img_height);

						imagecopy($photoplog_mark_feed, $photoplog_logo_feed,
							$photoplog_logo_x, $photoplog_logo_y,
							0, 0, $photoplog_logo_width, $photoplog_logo_height);
					}

					if ($photoplog_logo_feed && $photoplog_logo_check[2] == '2')
					{
						$photoplog_mark_feed = imagecreatetruecolor($photoplog_img_width,
							$photoplog_img_height);

						$photoplog_rgb = photoplog_hexdec();
						$photoplog_trans_color = imagecolorallocate($photoplog_mark_feed,
							$photoplog_rgb[0], $photoplog_rgb[1], $photoplog_rgb[2]);

						imagefill($photoplog_mark_feed, 0, 0, $photoplog_trans_color);
						imagecolortransparent($photoplog_mark_feed, $photoplog_trans_color);

						imagealphablending($photoplog_mark_feed, true);

						imagecopy($photoplog_mark_feed, $photoplog_img_feed,
							0, 0,
							0, 0, $photoplog_img_width, $photoplog_img_height);

						imagecopy($photoplog_mark_feed, $photoplog_logo_feed,
							$photoplog_logo_x, $photoplog_logo_y,
							0, 0, $photoplog_logo_width, $photoplog_logo_height);
					}

				}
			}

			if (
				$vbulletin->options['photoplog_backdrop_img']
					&&
				!$photoplog_na_feed
					&&
				$photoplog_thumb_type == 's'
					&&
				$photoplog_img_width > 30
					&&
				$photoplog_img_height > 30
			)
			{
				$photoplog_drop_location = $vbulletin->options['photoplog_backdrop_img'];
				$photoplog_drop_check = @getimagesize($photoplog_drop_location);

				if (!(
					$photoplog_drop_check === false
						||
					!is_array($photoplog_drop_check)
						||
					empty($photoplog_drop_check)
						||
					!in_array($photoplog_drop_check[2],array(1,2,3))
				))
				{
					if ($photoplog_drop_check[2] == '1')
					{
						if (imagetypes() & IMG_GIF)
						{
							$photoplog_drop_feed = @imagecreatefromgif($photoplog_drop_location);
						}
					}

					if ($photoplog_drop_check[2] == '2')
					{
						if (imagetypes() & IMG_JPG)
						{
							$photoplog_drop_feed = @imagecreatefromjpeg($photoplog_drop_location);
						}
					}

					if ($photoplog_drop_check[2] == '3')
					{
						if (imagetypes() & IMG_PNG)
						{
							$photoplog_drop_feed = @imagecreatefrompng($photoplog_drop_location);
						}
					}

					if ($photoplog_drop_feed)
					{
						$photoplog_drop_width = imagesx($photoplog_drop_feed);
						$photoplog_drop_height = imagesy($photoplog_drop_feed);
					}

					$photoplog_corner_x = 0;
					$photoplog_corner_y = 0;

					if ($vbulletin->options['photoplog_backdrop_pos'])
					{
						$photoplog_corner_array = explode(",",str_replace(" ","",$vbulletin->options['photoplog_backdrop_pos']));
						$photoplog_corner_x = max(intval($photoplog_corner_array[0] - 1),0);
						$photoplog_corner_y = max(intval($photoplog_corner_array[1] - 1),0);
					}

					if ($photoplog_corner_x > 0)
					{
						$photoplog_corner_x = max(0,floor($photoplog_img_width  / (($photoplog_drop_width / $photoplog_corner_x) - 2)));
					}
					if ($photoplog_corner_y > 0)
					{
						$photoplog_corner_y = max(0,floor($photoplog_img_height  / (($photoplog_drop_height / $photoplog_corner_y) - 2)));
					}

					$photoplog_add_x = 2 * $photoplog_corner_x;
					$photoplog_add_y = 2 * $photoplog_corner_y;

					if ($photoplog_drop_feed && $photoplog_drop_check[2] == '1')
					{
						imagealphablending($photoplog_drop_feed, false);

						$photoplog_trans_color = null;

						$photoplog_back_feed = imagecreate($photoplog_img_width + $photoplog_add_x,
							$photoplog_img_height + $photoplog_add_y);

						imagealphablending($photoplog_back_feed, false);

						imagetruecolortopalette($photoplog_img_feed, 100, 256);
						imagepalettecopy($photoplog_back_feed, $photoplog_img_feed);

						imagefill($photoplog_back_feed, 0, 0, $photoplog_trans_color);
						imagecolortransparent($photoplog_back_feed, $photoplog_trans_color);

						$photoplog_drop_width = imagesx($photoplog_drop_feed);
						$photoplog_drop_height = imagesy($photoplog_drop_feed);

						imagecopyresized($photoplog_back_feed, $photoplog_drop_feed,
							0, 0, 0, 0,
							$photoplog_img_width + $photoplog_add_x, $photoplog_img_height + $photoplog_add_y,
							$photoplog_drop_width, $photoplog_drop_height);

						imagecopy($photoplog_back_feed, $photoplog_img_feed,
							$photoplog_corner_x, $photoplog_corner_y,
							0, 0, $photoplog_img_width, $photoplog_img_height);
					}

					if ($photoplog_drop_feed && $photoplog_drop_check[2] == '2')
					{
						$photoplog_back_feed = imagecreatetruecolor($photoplog_img_width + $photoplog_add_x,
							$photoplog_img_height + $photoplog_add_y);

						$photoplog_drop_width = imagesx($photoplog_drop_feed);
						$photoplog_drop_height = imagesy($photoplog_drop_feed);

						imagecopyresized($photoplog_back_feed, $photoplog_drop_feed,
							0, 0, 0, 0,
							$photoplog_img_width + $photoplog_add_x, $photoplog_img_height + $photoplog_add_y,
							$photoplog_drop_width, $photoplog_drop_height);

						imagecopy($photoplog_back_feed, $photoplog_img_feed,
							$photoplog_corner_x, $photoplog_corner_y,
							0, 0, $photoplog_img_width, $photoplog_img_height);
					}

					if ($photoplog_drop_feed && $photoplog_drop_check[2] == '3')
					{
						imagealphablending($photoplog_drop_feed, false);
						imagesavealpha($photoplog_drop_feed, true);

						$photoplog_back_feed = imagecreatetruecolor($photoplog_img_width + $photoplog_add_x,
							$photoplog_img_height + $photoplog_add_y);

						imagealphablending($photoplog_back_feed, false);
						imagesavealpha($photoplog_back_feed,true);

						$photoplog_drop_width = imagesx($photoplog_drop_feed);
						$photoplog_drop_height = imagesy($photoplog_drop_feed);

						imagecopyresampled($photoplog_back_feed, $photoplog_drop_feed,
							0, 0, 0, 0,
							$photoplog_img_width + $photoplog_add_x, $photoplog_img_height + $photoplog_add_y,
							$photoplog_drop_width, $photoplog_drop_height);

						imagecopymerge($photoplog_back_feed, $photoplog_img_feed,
							$photoplog_corner_x, $photoplog_corner_y,
							0, 0, $photoplog_img_width, $photoplog_img_height, 100);
					}

				}
			}

			if ($photoplog_jpg_quality < 0 || $photoplog_jpg_quality > 100)
			{
				$photoplog_jpg_quality = 75;
			}

			if ($photoplog_mark_feed)
			{
				if ($photoplog_logo_check[2] == '1')
				{
					//header("Content-type: image/gif");
					//@imagegif($photoplog_mark_feed);
					header("Content-type: image/jpeg");
					@imagejpeg($photoplog_mark_feed,'',$photoplog_jpg_quality);
				}
				else if ($photoplog_logo_check[2] == '2')
				{
					header("Content-type: image/jpeg");
					@imagejpeg($photoplog_mark_feed,'',$photoplog_jpg_quality);
				}
				else if ($photoplog_logo_check[2] == '3')
				{
					//header("Content-type: image/png");
					//@imagepng($photoplog_mark_feed);
					header("Content-type: image/jpeg");
					@imagejpeg($photoplog_mark_feed,'',$photoplog_jpg_quality);
				}
				else
				{
					header('Content-type: image/gif');
					readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
				}
			}
			else if ($photoplog_back_feed)
			{
				if ($photoplog_drop_check[2] == '1')
				{
					header("Content-type: image/gif");
					@imagegif($photoplog_back_feed);
				}
				else if ($photoplog_drop_check[2] == '2')
				{
					header("Content-type: image/jpeg");
					@imagejpeg($photoplog_back_feed,'',$photoplog_jpg_quality);
				}
				else if ($photoplog_drop_check[2] == '3')
				{
					header("Content-type: image/png");
					@imagepng($photoplog_back_feed);
				}
				else
				{
					header('Content-type: image/gif');
					readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
				}
			}
			else
			{
				if ($photoplog_file_check[2] == '1')
				{
					header("Content-type: image/gif");
					@imagegif($photoplog_img_feed);
				}
				else if ($photoplog_file_check[2] == '2')
				{
					header("Content-type: image/jpeg");
					@imagejpeg($photoplog_img_feed,'',$photoplog_jpg_quality);
				}
				else if ($photoplog_file_check[2] == '3')
				{
					header("Content-type: image/png");
					@imagepng($photoplog_img_feed);
				}
				else
				{
					header('Content-type: image/gif');
					readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
				}
			}

			@imagedestroy($photoplog_img_feed);
			@imagedestroy($photoplog_logo_feed);
			@imagedestroy($photoplog_drop_feed);
			@imagedestroy($photoplog_mark_feed);
			@imagedestroy($photoplog_back_feed);

		}
		else
		{
			header('Content-type: image/gif');
			readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
			exit();
		}
	}
	else
	{
		header('Content-type: image/gif');
		readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
		exit();
	}
}

($hook = vBulletinHook::fetch_hook('photoplog_file_complete')) ? eval($hook) : false;

if ($_REQUEST['do'] != 'view')
{
	header('Content-type: image/gif');
	readfile(DIR . '/' . $vbulletin->options['cleargifurl']);
	exit();
}

?>