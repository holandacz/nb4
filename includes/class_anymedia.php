<?php

//	+-----------------------------------------------------------------------+
//	|	Name		AnyMedia BBCode											|
//	|	Package		vBulletin 3.5.4											|
//	|	Version		3.0.4													|
//	|	Author		Crist Chsu Moded by Nix									|
//	|	E-Mail		Crist@vBulletin-Chinese.com								|
//	|	Blog		http://www.QuChao.com									|
//	|	Date		2006-6-7												|
//	|	Link		http://www.vbulletin.org/forum/showthread.php?t=106239	|
//	+-----------------------------------------------------------------------+


// ######################### REQUIRE BACK-END ############################
//require_once('./global.php');
//require_once(DIR . '/includes/class_postbit.php');

/**
 * AnyMedia class
 */
class Anymedia
{
	//	{{{	properties

	/**
	 * vBulletin registry object
	 * @var		object	Reference to registry object
	 */
	var $vbulletin = null;

	/**
	 * Media Infomation Array.
	 * @var		array
	 */
	var $_mediaInfo = array(
		'width' => 0,
		'height' => 0,
		'autoplay' => '',
		'extension' => '',
		'loop' => 0,
		'url' => '',
		'link' => '',
		'mime' => '',
		'type' => '',
		'id' => 0,
		'layout' => 0,
		'extra' => array(),
		'title' => '',
		'text' => '',
		'thumb' =>'',
		'userid' =>'',
		'username' => '',
		'videosite' => 0
	);

	/**
	 * Media type list.
	 * @var		array
	 */
	var $_typeList = array(
		// Adobe Flash
		'swf'			=>	array('application/x-shockwave-flash',	'adobe_flash',	'anymediaadobeflash'),
		'spl'			=>	array('application/futuresplash',		'adobe_flash',	'anymediaadobeflash'),
		'flv'			=>	array('application/x-shockwave-flash',	'adobe_flv',	'anymediaadobeflash'),
		'mp3'			=>	array('audio/mpeg',						'adobe_flash',	'anymediaadobeflash'),
		// Quick Time
		'mov'			=>	array('video/quicktime',				'quick_time',	'anymediaquicktime'),
		'qt'			=>	array('video/quicktime',				'quick_time',	'anymediaquicktime'),
		'mqv'			=>	array('video/quicktime',				'quick_time',	'anymediaquicktime'),
		'mpeg'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mpg'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm1s'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm1v'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm1a'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm75'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm15'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mp2'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mpm'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mpv'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mpa'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'flc'			=>	array('video/flc',						'quick_time',	'anymediaquicktime'),
		'fli'			=>	array('video/flc',						'quick_time',	'anymediaquicktime'),
		'cel'			=>	array('video/flc',						'quick_time',	'anymediaquicktime'),
		'rtsp'			=>	array('application/x-rtsp',				'quick_time',	'anymediaquicktime'),
		'rts'			=>	array('application/x-rtsp',				'quick_time',	'anymediaquicktime'),
		'3gp'			=>	array('video/3gpp',						'quick_time',	'anymediaquicktime'),
		'3gpp'			=>	array('video/3gpp',						'quick_time',	'anymediaquicktime'),
		'3g2'			=>	array('video/3gpp2',					'quick_time',	'anymediaquicktime'),
		'3gp2'			=>	array('video/3gpp2',					'quick_time',	'anymediaquicktime'),
		'sdv'			=>	array('video/sd-video',					'quick_time',	'anymediaquicktime'),
		'amc'			=>	array('application/x-mpeg',				'quick_time',	'anymediaquicktime'),
		'mp4'			=>	array('video/mp4',						'quick_time',	'anymediaquicktime'),
		'sdp'			=>	array('application/sdp',				'quick_time',	'anymediaquicktime'),
		// Real Media
		'rm'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'rmvb'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'ra'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'rv'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'ram'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'smil'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		// Windows Media
		'mp3'			=>	array('application/x-mplayer2',			'mp3',			'anymediawindowsmedia'),
		'wma'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wav'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'ogg'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'ape'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'mid'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'midi'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'asf'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'asx'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wm'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wmv'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wsx'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wax'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wvx'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'avi'			=>	array('video/avi',						'windows_media','anymediawindowsmedia'),
		// Adobe PDF
		'pdf'			=>	array('application/pdf',				'adobe_pdf',	'anymediaadobepdf'),
		'fdf'			=>	array('application/vnd.fdf',			'adobe_pdf',	'anymediaadobepdf'),
		'xfdf'			=>	array('application/vnd.adobe.xfdf',		'adobe_pdf',	'anymediaadobepdf'),
		'xdp'			=>	array('application/vnd.adobe.xdp+xml',	'adobe_pdf',	'anymediaadobepdf'),
		'xfd'			=>	array('application/vnd.adobe.xfd+xml',	'adobe_pdf',	'anymediaadobepdf'),
		// Images
		'gif'			=>	array('image/gif',						'image',		'anymediaimage'),
		'jpg'			=>	array('image/pjpeg',					'image',		'anymediaimage'),
		'jpeg'			=>	array('image/pjpeg',					'image',		'anymediaimage'),
		'bmp'			=>	array('image/bmp',						'image',		'anymediaimage'),
		'png'			=>	array('image/x-png',					'image',		'anymediaimage'),
		'xpm'			=>	array('image/xpm',						'image',		'anymediaimage'),
		// P2P
		'torrent'		=>	array('application/x-bittorrent',		'torrent',		'anymediap2p'),
		'emule'			=>	array('emule',							'emule',		'anymediap2p'),
		'foxy'			=>	array('foxy',							'foxy',			'anymediap2p'),
		'pplive'		=>	array('pplive',							'pplive',		'anymediap2p'),
		// Video Sites
		'google'		=>	array('application/x-shockwave-flash',	'google',		'anymediaflv'),
		'youtube'		=>	array('application/x-shockwave-flash',	'youtube',		'anymediaflv'),
		'mtv'			=>	array('application/x-shockwave-flash',	'mtv',			'anymediaflv'),
		'vsocial'		=>	array('application/x-shockwave-flash',	'vsocial',		'anymediaflv'),
		'ifilm'			=>	array('application/x-shockwave-flash',	'ifilm',		'anymediaflv'),
		'metacafe'		=>	array('application/x-shockwave-flash',	'metacafe',		'anymediaflv'),
		'dailymotion'	=>	array('application/x-shockwave-flash',	'dailymotion',	'anymediaflv'),
		'currenttv'		=>	array('application/x-shockwave-flash',	'currenttv',	'anymediaflv'),
		'vimeo'			=>	array('application/x-shockwave-flash',	'vimeo',		'anymediaflv'),
		'sharkle'		=>	array('application/x-shockwave-flash',	'sharkle',		'anymediaflv'),
		'vidiac'		=>	array('application/x-shockwave-flash',	'vidiac',		'anymediaflv'),
		'myvideo'		=>	array('application/x-shockwave-flash',	'myvideode',	'anymediaflv'),
		'myspace'		=>	array('application/x-shockwave-flash',	'myspace',		'anymediaflv'),
		'bvid'			=>	array('application/x-shockwave-flash',	'bvid',			'anymediaflv'),
		'filecabi'		=>	array('application/x-shockwave-flash',	'filecabi',		'anymediaflv'),
		'pornotube'		=>	array('application/x-shockwave-flash',	'pornotube',	'anymediaflv'),
		'stage6'		=>	array('video/divx',						'stage6',		'anymediaflv'),
		'brightcove'	=>	array('application/x-shockwave-flash',	'brightcove',	'anymediaflv'),
		'photobucket'	=>	array('application/x-shockwave-flash',	'photobucket',	'anymediaflv'),
		'liveleak'		=>	array('application/x-shockwave-flash',	'liveleak',		'anymediaflv'),
		'revver'		=>	array('application/x-shockwave-flash',	'revver',		'anymediaflv'),
		'veoh'			=>	array('application/x-shockwave-flash',	'veoh',			'anymediaflv'),
		'putfile'		=>	array('application/x-shockwave-flash',	'putfile',		'anymediaflv'),
		'sevenload'		=>	array('application/x-shockwave-flash',	'sevenload',	'anymediaflv'),
		'gametrailers'	=>	array('application/x-shockwave-flash',	'gametrailers',	'anymediaflv'),
		'spikedhumor'	=>	array('application/x-shockwave-flash',	'spikedhumor',	'anymediaflv'),
		'streetfire'	=>	array('application/x-shockwave-flash',	'streetfire',	'anymediaflv'),
		'yahoo'			=>	array('application/x-shockwave-flash',	'yahoo',		'anymediaflv'),
		'xtube'			=>	array('application/x-shockwave-flash',	'xtube',		'anymediaflv'),
		'porkolt'		=>	array('application/x-shockwave-flash',	'porkolt',		'anymediaflv'),
		'megarotic'		=>	array('application/x-shockwave-flash',	'megarotic',	'anymediaflv'),
		'youporn'		=>	array('application/x-shockwave-flash',	'youporn',		'anymediaflv'),
		'collegehumor'	=>	array('application/x-shockwave-flash',	'collegehumor',	'anymediaflv'),
		'megavideo'		=>	array('application/x-shockwave-flash',	'megavideo',	'anymediaflv'),
		'apple'			=>	array('video/quicktime',				'apple',		'anymediaquicktime'),
		'espn'			=>	array('application/x-shockwave-flash',	'espn',			'anymediaflv'),
		'godtube'		=>	array('application/x-shockwave-flash',	'godtube',		'anymediaflv'),
		'libero'		=>	array('application/x-shockwave-flash',	'libero',		'anymediaflv')
	);

	/**
	 * Constructor.
	 * @param	object	Reference to registry object
	 * @return	void
	 */
	function Anymedia(& $registry)
	{
		$this->vbulletin =& $registry;
	}

	/**
	 * Destructor.
	 * @return	void
	 */
	function __destruct()
	{
	}

	/**
	 * Fetch the parsed HTML.
	 * @param	string	Code of the media
	 * @param	string	Options of the media
	 * @return	string	HTML representation of the media
	 */
	function fetch(& $text, & $options)
	{
		global $vbulletin, $post, $threadinfo;
		$this->processOptions($text, $options);
		
		if (empty($this->_mediaInfo['extension']))
		{
			$this->_mediaInfo['text'] = $text;
			$checkdb = $this->dbcheck($text);
			if(!empty($checkdb))
			{
				return $this->_mediaInfo;
			}
			$this->processExtension($text);
		}
		$this->processMedia();
		if($this->_mediaInfo['videosite']) {
			$this->dbInsertAM($media);
		}
		
		//$this->create_new_thread();
		return $this->_mediaInfo;
	}
	
	function newfetch(& $text, & $options)
	{
		$this->processOptions($text, $options);
		if (empty($this->_mediaInfo['extension']))
		{
			$this->_mediaInfo['text'] = $text;
			$checkdb = $this->dbcheck($text);
			if(!empty($checkdb))
			{
				return $this->_mediaInfo;
			}
			$this->processExtension($text);
		}
		$this->processMedia();
		return $this->_mediaInfo;
	}
	
	function dbcheck($text)
	{
		global $db;
		$dtext = unhtmlspecialchars($text);
		$findmedia = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "anymedia WHERE link = '" . $db->escape_string($dtext) . "'");
		if(!empty($findmedia))
		{
			$this->_mediaInfo = $findmedia;
			return $this->_mediaInfo;
		} else {
			return false;
		}
	}
	
	// ###################### Start create_new_thread ########################
	/**
	* Creates new thread or gives error and then redirects user
	*
	* @param	string	Title of thread
	* @param	string	Message of post
	* @param	integer	ForumID for thread
	* @param	boolean	Allow guest posts
	*/
	function create_new_thread()
	{
		// set some globals

		global $forumperms, $vbulletin, $vbphrase;
		$title = $this->_mediaInfo['title'];
		$message = 'Def4484agult Messagek';
		$id = 3;
		$guest = false;

		// init some variables

		$fail = 0;
		$errors = array();
		$newpost = array();

		// init post information

		if ($guest AND $vbulletin->userinfo['userid'] == 0)
		{
			$newpost['username'] = $vbphrase['guest'];
		}
		$newpost['title'] = $title;
		$newpost['message'] = $message;
		$newpost['signature'] = '0';
		if ($vbulletin->userinfo['signature'] != '')
		{
			$newpost['signature'] = '1';
		}
		$newpost['parseurl'] = '1';
		$newpost['emailupdate'] = '9999';

		// attempt thread create

		$foruminfo = verify_id('forum', $id, 0, 2);
		if (!$foruminfo['forumid'])
		{
			$fail = 1;
		}
		$forumperms = fetch_permissions($foruminfo['forumid']);
		if (!function_exists('build_new_post'))
		{
			require_once(DIR . '/includes/functions_newpost.php');
		}
		$vbulletin->options['floodchecktime'] = 0;
		
		build_new_post('thread', $foruminfo, array(), array(), $newpost, $errors);
		if (sizeof($errors) > 0)
		{
			$fail = 1;
		}
		/*
		// do redirection

		if (!$fail)
		{
			$vbulletin->url = $vbulletin->options['bburl'] . '/showthread.php?' . $vbulletin->session->vars['sessionurl'] . "p=".$newpost['postid']."#post".$newpost['postid'];
			eval(print_standard_redirect('redirect_postthanks'));
		}
		else
		{
			$vbulletin->url = $vbulletin->options['bburl'];
			eval(print_standard_redirect($vbphrase['error'].': '.$vbphrase['redirecting'],0,1));
		}*/
	}
	

	/**
	 * Fetch forum path.
	 * 
	 */
	function getpath()
	{
			if ($_SERVER['PATH_TRANSLATED'])
			{
				$path = $_SERVER['PATH_TRANSLATED'];
			}
			else if ($_SERVER['SCRIPT_FILENAME'])
			{
				$path = $_SERVER['SCRIPT_FILENAME'];
			}
			else
			{
				return FALSE;
			}
			$c= substr($path, 0, (strlen($path) - 13));
			
			return $c;
	}


	/**
	 * Fetch the parsed attachment.
	 * @param	string	Url to the attachment
	 * @param	string	extension of the attachment
	 * @return	string	HTML representation of the media
	 */
	function attachment(& $id, & $extension)
	{
		$this->_mediaInfo['width'] = $this->vbulletin->options['anymediawidth'];
		$this->_mediaInfo['height'] = $this->vbulletin->options['anymediaheight'];
		$this->_mediaInfo['autoplay'] = iif($this->vbulletin->options['anymediaautoplay'], 'true', 'false');
		$this->_mediaInfo['loop'] = $this->vbulletin->options['anymedialoop'];
		$this->_mediaInfo['extension'] = $extension;
		$this->_mediaInfo['url'] = $this->_mediaInfo['link'] = 'attachment.php?'. $this->vbulletin->session->vars['sessionurl'] . 'attachmentid=' . $id;
		$this->_mediaInfo['id'] = vbrand(1, 1000);
		$this->_mediaInfo['attachment'] = $this->_mediaInfo['extension'];	
		$this->_mediaInfo['download'] = iif(($this->vbulletin->userinfo['permissions']['anymediapermissions'] & $this->vbulletin->bf_ugp_anymediapermissions['candownload']) && $this->vbulletin->options['anymediadownload'], true, false);
		$this->processMedia();
		
		$this->_mediaInfo['url3'] = $this->vbulletin->options['bburl'] . '/' . $this->_mediaInfo['link'];
		return $this->_mediaInfo;
	}
	
	function fetchwww($hurl = '')
	{
		if(empty($hurl))
		{
			$hurl = unhtmlspecialchars($this->_mediaInfo['link']);
		}
		if (function_exists('curl_init'))
		{
			$user_agent = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';
			$handle = curl_init();
			curl_setopt ($handle, CURLOPT_URL, $hurl);
			curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($handle, CURLOPT_USERAGENT, $user_agent);
			
			$content = curl_exec($handle);
			curl_close($handle);
		} elseif (@fclose(@fopen($hurl, "r")) && ini_get('allow_url_fopen')) {
			$content = file($hurl);
			$content = implode("",$content);
		} else {
			return false;
		}
		return $content;
		
	}
	
	function hTittle(& $tmpContent)
	{
		if(preg_match('/<title>(.+)<\/title>/si',$tmpContent,$m))
		{
			unset($tmpContent);
			return $m[1];
		} else {
			unset($tmpContent);
			return FALSE;
		}
	}
	
	function dbInsertAM(& $media)
	{
		global $post, $postinfo, $threadinfo, $vbulletin;
	
		if(!is_null($post['postid'])) {
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "post SET anymedia_yes=1 WHERE postid=$post[postid]");
		} else if(!is_null($postinfo['postid'])) {
			$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "post SET anymedia_yes=1 WHERE postid=$postinfo[postid]");
		} else {
			return false;
		}	
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET anymedia_amount =  anymedia_amount +1 WHERE userid=" . $vbulletin->db->escape_string($vbulletin->userinfo[userid]));
		
		$vbulletin->db->query_write("
		INSERT INTO " . TABLE_PREFIX . "anymedia
			(idM, width, height, extension, link, url, url2, type, thumb, title, userid, username, postid, parentid, threadid, date) 
		VALUES
			(
				'" . $vbulletin->db->escape_string($this->_mediaInfo['idM']) . "',
				'" . $vbulletin->db->escape_string($this->_mediaInfo['width']) . "',
				'" . $vbulletin->db->escape_string($this->_mediaInfo['height']) . "',
				'" . $vbulletin->db->escape_string($this->_mediaInfo['extension']) . "',
				'" . $vbulletin->db->escape_string($this->_mediaInfo['link']) . "',
				'" . $vbulletin->db->escape_string($this->_mediaInfo['url']) . "',
				'" . $vbulletin->db->escape_string($this->_mediaInfo['url2']) . "',
				'" . $vbulletin->db->escape_string($this->_mediaInfo['type']) . "',
				'" . $vbulletin->db->escape_string($this->_mediaInfo['thumb']) . "',
				'" . $vbulletin->db->escape_string($this->_mediaInfo['title']) . "',
				'" . $vbulletin->userinfo['userid'] . "',
				'" . $vbulletin->userinfo['username'] ."',
				'" . $post['postid'] . "',
				'" . $postinfo['parentid'] . "',
				'" . $threadinfo['threadid'] . "',
				" .TIMENOW . "
			)
		");
		$this->_mediaInfo['id'] = $vbulletin->db->insert_id();
	}
		
	/**
	 * Set value for basic options.
	 * @param	string	Code of the media
	 * @param	string	Options of the media
	 * @return	string	HTML representation of the media
	 */
	function processOptions(& $text)
	{
		$optionArray = explode(',', $options);
		$this->_mediaInfo['width'] = iif(isset($optionArray[0]) && !empty($optionArray[0]) && ereg('^[0-9]{1,3}$', $optionArray[0]), $optionArray[0], $this->vbulletin->options['anymediawidth']);
		$this->_mediaInfo['height'] = iif(isset($optionArray[1]) && !empty($optionArray[1]) && ereg('^[0-9]{1,3}$', $optionArray[1]), $optionArray[1], $this->vbulletin->options['anymediaheight']);
		$this->_mediaInfo['autoplay'] = iif($this->vbulletin->options['anymediaautoplay'], 'true', 'false');
		$this->_mediaInfo['loop'] = $this->vbulletin->options['anymedialoop'];
		$this->_mediaInfo['extension'] = iif(isset($optionArray[4]) && !empty($optionArray[4]) && array_key_exists(strtolower($optionArray[4]), $this->_typeList), strtolower($optionArray[4]));
		$this->_mediaInfo['url'] = $this->_mediaInfo['link'] = $text;
		$this->_mediaInfo['id'] = vbrand(1, 1000);
		$this->_mediaInfo['userid'] = $this->vbulletin->userinfo['userid'];
		$this->_mediaInfo['username'] = $this->vbulletin->userinfo['username'];
		$this->_mediaInfo['download'] = iif(($this->vbulletin->userinfo['permissions']['anymediapermissions'] & $this->vbulletin->bf_ugp_anymediapermissions['candownload']) && $this->vbulletin->options['anymediadownload'], true, false);
	}
	
	/**
	 * Auto-detect the extension of the file.
	 * @param	string	Code of the media
	 * @return	string	HTML representation of the media
	 */
	function processExtension(& $text)
	{
		$ptext = parse_url(strtolower($text));
		$ntext = explode('.', $ptext['host']);
		$ftext = $ntext[0];
		if($ntext[1] == 'break')
		{
			$ntext[1] = 'bvid';
		}
		if(array_key_exists($ntext[1], $this->_typeList))
		{
			$this->_mediaInfo['extension'] = $ntext[1];
		} elseif(array_key_exists($ftext, $this->_typeList)) {
			$this->_mediaInfo['extension'] = $ftext;
		} elseif (array_key_exists(strtolower(file_extension($text)), $this->_typeList)) {
			$this->_mediaInfo['extension'] = strtolower(file_extension($text));
			return $this_mediaInfo['extension'];
		} elseif (strpos(strtolower($text), $this->vbulletin->options['bburl'] . '/attachment.php') === 0 && preg_match('/attachmentid=(\d+)/i', $text, $match) && 	$this->vbulletin->options['anymediaattachurl']) {
				$attach = $this->vbulletin->db->query_first("
					SELECT `extension`
					FROM `" . TABLE_PREFIX . "attachment`
					WHERE `attachmentid`= " . $match[1]
				);
				$this->_mediaInfo['extension'] = strtolower($attach['extension']);
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Search Array For key.
	 *
	 */

	/**
	 * Parse media base on the options.
	 * @param	string	Code of the media
	 * @return	string	HTML representation of the media
	 */
	function processMedia()
	{
		$thisMedia = $this->_typeList[$this->_mediaInfo['extension']];
			if (is_array($thisMedia)) {
				if ($this->vbulletin->options[$thisMedia[2]]) {
					eval('$this->' . $thisMedia[1] . '($thisMedia);');
				} else {
					$this->_mediaInfo['type'] = 'unknown';
				}
			} else {
				$this->_mediaInfo['type'] = 'unknown';
			}
	}

	/**
	 * Fetch the remote content.
	 * @param	string	url of the page
	 * @param	string	get the http header?
	 * @return	string	HTML of the page
	 */
	function fetchContent($url, $getHeader = false)
	{
		$content = "";
		if (ini_get('allow_url_fopen') && !$getHeader) {
			//ByFile
			$handle = @fopen($url,"r");
			if(!$handle){
				return false;
			}
			while($buffer = fgets($handle, 4096)) {
			  $content .= $buffer;
			}
			fclose($handle);
			return $content;
		} elseif (function_exists('curl_init')) {
			//ByCurl
			$handle = curl_init();
			curl_setopt ($handle, CURLOPT_URL, $url);
			curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);
			if ($getHeader) {
				curl_setopt ($handle, CURLOPT_HEADER, 1);
				curl_setopt ($handle, CURLOPT_NOBODY, 1);
			}
			$content = curl_exec($handle);
			curl_close($handle);
			return $content;
		} elseif (function_exists('fsockopen')) {
			//BySocket
			if (!($pos = strpos($url, '://'))) {
				return false;
			}
			$host = substr($url, $pos+3, strpos($url, '/', $pos+3) - $pos - 3);
			$uri = substr($url, strpos($url, '/', $pos+3));
			$request = "GET " . $uri . " HTTP/1.0\r\n"
					   ."Host: " . $host . "\r\n"
					   ."Accept: */*\r\n"
					   ."User-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows 98)\r\n"
					   ."\r\n";
			$handle = @fsockopen($host, 80, $errno, $errstr, 30);
			if (!$handle) {
				return false;
			}
			@fputs($handle, $request);
			while (!feof($handle)){
				$content .= fgets($handle, 4096);
			}
			fclose($handle);
			$separator = strpos($content, "\r\n\r\n");
			if($getHeader) {
				if($separator === false) {
					return false;
				} else {
					return substr($content, 0, $separator);
				}
			} else {
				if($separator === false) {
					return $content;
				} else {
					return substr($content, $separator + 4);
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Adobe Flash Video.
	 * @param	array	media info array
	 */
	function adobe_flv(& $mediaArray)
	{
		$this->_mediaInfo['url'] = $this->vbulletin->options['bburl'] . '/players/mediaplayer.swf';
		if($this->_mediaInfo['attachment']) {
			$this->_mediaInfo['url3'] =  urlencode($this->_mediaInfo['url']);
		} else {
			$this->_mediaInfo['url2'] =  urlencode($this->_mediaInfo['url']);
		}
		$this->_mediaInfo['autoplay'] = 'true';
		$this->_mediaInfo['height'] += 20;
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = 'adobe_flash';
		
	}

	/**
	 * Use Official Player.
	 * @param	array	media info array
	 */
	function player(& $mediaArray)
	{
		$this->_mediaInfo['autoplay'] = 'false';
		$this->_mediaInfo['mime'] = 'application/x-shockwave-flash';
		$this->_mediaInfo['type'] = 'adobe_flash';
	}

	/**
	 * Adobe Flash.
	 * @param	array	media info array
	 */
	function adobe_flash(& $mediaArray)
	{
		$this->_mediaInfo['autoplay'] = 'false';
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	/**
	 * divx.
	 * @param	array	media info array
	 */
	function divx_video(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = 'video/divx';
		$this->_mediaInfo['type'] = 'divx_video';
	}

	/**
	 * Quick Time.
	 * @param	array	media info array
	 */
	function quick_time(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	/**
	 * Real Media.
	 * @param	array	media info array
	 */
	function real_media(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	/**
	 * MP3.
	 * @param	array	media info array
	 */
	function mp3(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediamp3player']) {
			$this->_mediaInfo['url'] = $this->vbulletin->options['bburl'] . '/players/mp3player.swf?f=' . $this->_mediaInfo['url'] . '&autoStart=' . iif($this->_mediaInfo['autoplay'] == 'true', 'true', 'false') . '&showDownload=false&repeatPlay=' . iif($this->_mediaInfo['loop'] > 1, 'true', 'false');
			$this->_mediaInfo['autoplay'] = 'true';
			$this->_mediaInfo['loop'] = '1';
			$this->_mediaInfo['height'] = 30;
			$this->_mediaInfo['mime'] = 'application/x-shockwave-flash';
			$this->_mediaInfo['type'] = 'adobe_flash';
			} else {
			$this->_mediaInfo['mime'] = 'application/x-mplayer2';
			$this->_mediaInfo['type'] = 'windows_media';
		}
	}

	/**
	 * Windows Media.
	 * @param	array	media info array
	 */
	function windows_media(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	/**
	 * Adobe PDF.
	 * @param	array	media info array
	 */
	function adobe_pdf(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	/**
	 * Image.
	 * @param	array	media info array
	 */
	function image(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	/**
	 * Torrent.
	 * @param	array	media info array
	 */
	function torrent(& $mediaArray)
	{
		include_once 'bencode.php';
		$content = $this->fetchContent($this->_mediaInfo['url']);
		if (!empty($content)) {
			$bencode = new BEncodeLib();
			$torrent = $bencode->bdecode($content);
			if (is_array($torrent)) {
				if (is_array($torrent['announce-list'])) {
					foreach ($torrent['announce-list'] as $key => $value) {
						$this->_mediaInfo['extra']['announce'] .= $torrent['announce-list'][$key][0] . '<br />';
					}
				} else {
					$this->_mediaInfo['extra']['announce'] = $torrent['announce'];
				}
				$this->_mediaInfo['extra']['created_by'] = $torrent['created by'];
				$this->_mediaInfo['extra']['creation_date'] = iif($torrent['creation date'], vbdate($this->vbulletin->options['dateformat'], $torrent['creation date'], false) . ' <span class="time">' . vbdate($this->vbulletin->options['timeformat'], $torrent['creation date'], false) . '</span>');
				$this->_mediaInfo['extra']['encoding'] = $torrent['encoding'];
				$this->_mediaInfo['extra']['codepage'] = $torrent['codepage'];
				$this->_mediaInfo['extra']['name'] = iif($torrent['info']['name.utf-8'], $torrent['info']['name.utf-8'], $torrent['info']['name']);
				$this->_mediaInfo['extra']['length'] = iif($torrent['info']['length'], vb_number_format($torrent['info']['length'], 1, true));
				$this->_mediaInfo['extra']['piece_length'] = iif($torrent['info']['piece length'], vb_number_format($torrent['info']['piece length'], 1, true));
				$this->_mediaInfo['extra']['publisher'] = iif($torrent['info']['publisher.utf-8'], $torrent['info']['publisher.utf-8'], $torrent['info']['publisher']);
				$this->_mediaInfo['extra']['publisher_url'] = iif($torrent['info']['publisher-url.utf-8'], $torrent['info']['publisher-url.utf-8'], $torrent['info']['publisher-url']);
				if (is_array($torrent['nodes'])) {
					foreach ($torrent['nodes'] as $key => $value) {
						$this->_mediaInfo['extra']['nodes'] .= $torrent['nodes'][$key][0] . ':' . $torrent['nodes'][$key][1] . '<br />';
					}
				}
				if (is_array($torrent['info']['files'])) {
					foreach ($torrent['info']['files'] as $key => $value) {
						if($torrent['info']['files'][$key]['path.utf-8']) {
							$this->_mediaInfo['extra']['files'] .= iif(is_array($torrent['info']['files'][$key]['path.utf-8']), implode('/', $torrent['info']['files'][$key]['path.utf-8']), $torrent['info']['files'][$key]['path.utf-8']) . ' (' . vb_number_format($torrent['info']['files'][$key]['length'], 1, true) . ') <br />';
						} else {
							$this->_mediaInfo['extra']['files'] .= iif(is_array($torrent['info']['files'][$key]['path']), implode('/', $torrent['info']['files'][$key]['path']), $torrent['info']['files'][$key]['path']) . ' (' . vb_number_format($torrent['info']['files'][$key]['length'], 1, true) . ') <br />';
						}
					}
				}
				$this->_mediaInfo['type']='p2p';
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
			$this->_mediaInfo['type'] = 'error';
		}
	}

	/**
	 * Emule.
	 * @param	array	media info array
	 */
	function emule(& $mediaArray)
	{
		$list = explode("\n", $this->_mediaInfo['url']);
		$totalSize = 0;
		foreach ($list as $emule) {
			$emuleTitle = $emuleSize = '';
			$emuleArray = explode('|', $emule);
			$emuleTitle = rawurldecode($emuleArray[2]);
			$emuleSize = vb_number_format($emuleArray[3], 1, true);
			$totalSize += $emuleArray[3];
			if($emuleTitle && $emuleSize) {
				$this->_mediaInfo['extra']['content'] .= '<tr><td align="left" class="alt2" width="80%"><input type="checkbox" name="anymedia_check_' . $this->_mediaInfo['id'] . '" value="' . $emule . '" onClick="anymedia_size(\'' . $this->_mediaInfo['id'] . '\');" checked="checked" /> <a href="' . $emule . '">' . $emuleTitle . '</a></td><td align="center" class="alt1">' . $emuleSize . '<input type="hidden" name="item_anymedia_' . $this->_mediaInfo['id'] . '" value="' . $emuleArray[3] . '" /></td></tr>';
			} else {
				continue;
			}
		}
		if($this->_mediaInfo['extra']['content']) {
			$this->_mediaInfo['extra']['size'] = vb_number_format($totalSize, 1, true);
			$this->_mediaInfo['type'] = 'p2p';
		} else {
			$this->_mediaInfo['type'] = 'error';
		}
	}

	/**
	 * Emule.
	 * @param	array	media info array
	 */
	function foxy(& $mediaArray)
	{
		$list = explode("\n", $this->_mediaInfo['url']);
		$totalSize = 0;
		foreach ($list as $foxy) {
			$foxyTitle = $foxySize = '';
			if(preg_match('/dn=([^(\&|$)]*)/i', $foxy, $match)) {
				$foxyTitle = rawurldecode($match[1]);
			}
			if(preg_match('/fs=(\d+)/i', $foxy, $match)) {
				$foxySize = vb_number_format($match[1], 1, true);
				$totalSize += $match[1];
			}
			if($foxyTitle && $foxySize) {
				$this->_mediaInfo['extra']['content'] .= '<tr><td align="left" class="alt2" width="80%"><input type="checkbox" name="anymedia_check_' . $this->_mediaInfo['id'] . '" value="' . $foxy . '" onClick="anymedia_size(\'' . $this->_mediaInfo['id'] . '\');" checked="checked" /> <a href="' . $foxy . '">' . $foxyTitle . '</a></td><td align="center" class="alt1">' . $foxySize . '<input type="hidden" name="item_anymedia_' . $this->_mediaInfo['id'] . '" value="' . $match[1] . '" /></td></tr>';
			} else {
				continue;
			}
		}
		if($this->_mediaInfo['extra']['content']) {
			$this->_mediaInfo['extra']['size'] = vb_number_format($totalSize, 1, true);
			$this->_mediaInfo['type'] = 'p2p';
		} else {
			$this->_mediaInfo['type'] = 'error';
		}
	}

	/**
	 * PPLive.
	 * @param	array	media info array
	 */
	function pplive(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
		$this->_mediaInfo['height'] += 45;
	}

	/**
	* Google Video.
	* @param    array    media info array
	*/
	function google(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediagoogle'] == '1') {
			if (preg_match('{http://video\.google\.(com|co\.uk|de|ca)/videoplay\?docid=([^(\&|$)]*)}i', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['height'] = '326';
				$this->_mediaInfo['width'] = '400';
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
	        	$this->_mediaInfo['url'] = 'http://video.google.com/googleplayer.swf?docid=' . $match[2];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['videosite'] = 1;
	        	$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Youtube Video.
	 * @param	array	media info array
	 */
	function youtube(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediayoutube'] == '1')
		{
			if(preg_match('{http://\w+\.youtube\.com/watch\?v=([^(\&|$)]*)}i', $this->_mediaInfo['url'], $match) || preg_match('{http://youtube\.com/watch\?v=([^(\&|$)]*)}i', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['height'] = '350';
				$this->_mediaInfo['width'] = '425';
				$this->_mediaInfo['thumb'] = 'http://img.youtube.com/vi/' . $match[1] . '/default.jpg';
				$this->_mediaInfo['url'] = 'http://www.youtube.com/v/' . $match[1];
				 $this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}	
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * MTV Video.
	 * @param	array	media info array
	 */
	function mtv(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediamtv'] == '1')
		{
			if(preg_match('{http://www\.mtv.com/overdrive/\?vid=(\d+)}i', $this->_mediaInfo['url'], $match) || preg_match('{http://www\.mtv.com/overdrive/\?id=(\d+)}i', $this->_mediaInfo['url'], $match) || preg_match('{http://www\.mtv.com/overdrive/\?artist=(\d+\&vid=\d+)}i', $this->_mediaInfo['url'], $amatch))
			{
				
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['height'] = '318';
				$this->_mediaInfo['width'] = '423';
				
				preg_match('{\<meta\s*name="thumbnail"\s*content="/(.+)"\s*/\>}i',$tmpContent,$m);
				$this->_mediaInfo['thumb'] = 'http://www.mtv.com/' . $m[1];
				$this->_mediaInfo['url'] = 'http://www.mtv.com/player/embed/';
				preg_match('{so\.addVariable\(\"CONFIG_URL\",\s*"/(.+)\"}', $tmpContent,$mu);
				if($mu[1]) {
					$this->_mediaInfo['url2'] = 'http://www.mtv.com/' . $mu[1];
					$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $mu[1];
				} else {
					return $this->_mediaInfo['type'] = 'error';
				}
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}	
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * megavideo Video.
	 * @param	array	media info array
	 */
	function megavideo(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediamegavideo'] == '1')
		{
			if(preg_match('{http://(www\.|)megavideo\.com/\?v=([\d\w]*)}i', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				//$title = $this->hTittle($tmpContent);
				//$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['height'] = '351';
				$this->_mediaInfo['width'] = '432';
				preg_match('{fo.addvariable\("videoname","(.+)"\)}i',$tmpContent,$t);
				$this->_mediaInfo['title'] = $t[1];
				preg_match('{\<object\s*width=.*\<param\s*name="movie"\s*value=\"(.+)"\>\<\/param\>\<param\s*name}si',$tmpContent,$e);
				$this->_mediaInfo['url'] = $e[1];
				preg_match("{\<a href=\"\?v=$match[2]\"\>\<IMG SRC=\"(\S+)\.jpg\"}si",$tmpContent,$m);
				$this->_mediaInfo['thumb'] = $m[1] . '.jpg';
				 $this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[2];
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}	
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * Collegehumor Video.
	 * @param	array	media info array
	 */
	function collegehumor(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediacollegehumor'] == '1')
		{
			if(preg_match('{http://www\.collegehumor\.com/video:(\d+)}i', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['url'] = 'http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=' . $match[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				preg_match('{\<link rel\="image_src"\s*href\="(.+)\.(jpg|png)"}i',$tmpContent,$m);
				$this->_mediaInfo['thumb'] = $m[1] . '.' . $m[2];
				$this->_mediaInfo['height'] = '300';
				$this->_mediaInfo['width'] = '400';
				unset($tmpContent);
				$this->_mediaInfo['videosite'] = 1;
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}	
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * apple Trailers.
	 * @param	array	media info array
	 */
	function apple(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaapple'] == '1')
		{
			if(preg_match('{http://www\.apple\.com/trailers/([/\w\d\._-]+)}i', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				preg_match('{http://images\.apple.com/movies/(.+)\.(640|ref)\.mov}i',$tmpContent,$m);
				$this->_mediaInfo['url'] = $m[0];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $m[1];
				unset($tmpContent);
				$this->_mediaInfo['height'] = '284';
				$this->_mediaInfo['width'] = '640';
				$this->_mediaInfo['mime'] = 'video/quicktime';
				$this->_mediaInfo['videosite'] = 1;
				$this->quick_time($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}	
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * vSocial Video.
	 * @param	array	media info array
	 */
	function vsocial(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediavsocial'] == '1')
		{
			if (preg_match('{\?d=([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match) || preg_match('holder', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://static.vsocial.com/flash/ups.swf?d=' . $match[1] .'&a=1&s=false';
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '';
				$this->_mediaInfo['width'] = '';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}			
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * iFilm Video.
	 * @param	array	media info array
	 */
	function ifilm(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaifilm'] == '1')
		{
			if (preg_match('/video\/(\d+)/i', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = 'http://img1.ifilmpro.com/resize/image/stills/films/resize/istd/' . $match[1] . '.jpg?width=130';
				$this->_mediaInfo['url'] = 'http://www.ifilm.com/efp?flvBaseClip=' . $match[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '365';
				$this->_mediaInfo['width'] = '448';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			}	else {
				$this->_mediaInfo['type'] = 'error';
			}
		}	else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * MetaCafe Video.
	 * @param	array	media info array
	 */
	function metacafe(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediametacafe'] == '1') {
			if (preg_match('{/watch\/(\d+)(\S+)\/}i', $this->_mediaInfo['url'], $match) || preg_match('/watch\/(\d+)/i', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://www.metacafe.com/fplayer/' . $match[1] . $match[2] . '.swf';
				$this->_mediaInfo['height'] = '345';
				$this->_mediaInfo['width'] = '400';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * DailyMotion Video.
	 * @param	array	media info array
	 */
	function dailymotion(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediadailymotion'] == '1') {
			if (preg_match('{dailymotion\.com}i', $this->_mediaInfo['url'], $matches) || preg_match('/watch\/(\d+)/i', $this->_mediaInfo['url'], $matches)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				preg_match("{dailymotion\.com/swf/([a-zA-Z0-9]+)\&quot;}i",$tmpContent,$m);
				$this->_mediaInfo['url'] = 'http://www.dailymotion.com/swf/' . $m[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				if (preg_match("{Video (.+) \-.+,}", $title, $matches))
				{
					$this->_mediaInfo['title'] = $matches[1];
				}
				$this->_mediaInfo['height'] = '335';
				$this->_mediaInfo['width'] = '425';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
			$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Current TV Video.
	 * @param	array	media info array
	 */
	function currenttv(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediacurrenttv'] == '1') {
			if (preg_match('{watch\/([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://www.current.tv/studio/vm2/vm2.swf?videoType=vcc&videoID=' . $match[1];
				$this->_mediaInfo['height'] = '400';
				$this->_mediaInfo['width'] = '400';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);		
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Vimeo Video.
	 * @param	array	media info array
	 */
	function vimeo(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediavimeo'] == '1') {
			if (preg_match('/clip:(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://www.vimeo.com/moogaloop.swf?clip_id=' . $match[1];
				$this->_mediaInfo['height'] = '';
				$this->_mediaInfo['width'] = '';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * ESPN Video.
	 * @param	array	media info array
	 */
	function espn(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaespn'] == '1')
		{
			if(preg_match('{http://sports\.espn\.go\.com/broadband/video/videopage\?videoId=(\d+)}i', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				preg_match('{modulenametxt"\>\s*\<img\s*src="http://assets\.espn\.go\.com/broadband/video/images/icon_modulename\.gif"\>(.+)\</span\>\s*\</div\>\s*\<\!--\s*flash\s*check}si', $tmpContent, $m);
				$this->_mediaInfo['title'] = $m[1];
				$this->_mediaInfo['height'] = '361';
				$this->_mediaInfo['width'] = '440';
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://sports.espn.go.com/broadband/player.swf?mediaId=' . $match[1];
				 $this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}	
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Sharkle Video.
	 * @param	array	media info array
	 */
	function sharkle(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediasharkle'] == '1') {
			if (preg_match('/video\/(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://www.sharkle.com/externalPlayer/' . $match[1];
				$this->_mediaInfo['height'] = '310';
				$this->_mediaInfo['width'] = '340';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * vidiac.com Video.  Used to be freevideoblog
	 * @param	array	media info array
	 */
	function vidiac(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediavidiac'] == '1') {
			if (preg_match('{video/([a-zA-Z0-9-]+)}', $this->_mediaInfo['url'], $match) || preg_match('{hottestvideos/\d/([a-zA-Z0-9-]+)}', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://www.vidiac.com/vidiac.swf?video=' . $match[1];
				$this->_mediaInfo['height'] = '352';
				$this->_mediaInfo['width'] = '428';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * MyVideo.De Video.
	 * @param	array	media info array
	 */
	function myvideode(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediamyvideode'] == '1') {
			if (preg_match('/watch\/(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://www.myvideo.de/movie/' . $match[1];
				$this->_mediaInfo['height'] = '406';
				$this->_mediaInfo['width'] = '470';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Myspace Video.
	 * @param	array	media info array
	 */
	function myspace(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediamyspace'] == '1') {
			if (preg_match('/videoid=(\d+)/i', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$hurl = htmlspecialchars_decode($this->_mediaInfo['url']);
				$tmpContent = $this->fetchwww($hurl);
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://lads.myspace.com/videos/vplayer.swf?m=' . $match[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '346';
				$this->_mediaInfo['width'] = '430';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Break Video.
	 * @param	array	media info array
	 */
	function bvid(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediabvids'] == '1') {
			if (preg_match('{break\.com}', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				preg_match("{embed\.break\.com/([a-zA-Z0-9]+)}i",$tmpContent,$m);
				$this->_mediaInfo['url'] = 'http://embed.break.com/' . $m[1];
				$this->_mediaInfo['height'] = '392';
				$this->_mediaInfo['width'] = '464';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
					$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * filecabi.net Video.
	 * @param	array	media info array
	 */
	function filecabi(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediafilecabi'] == '1') {
			if (preg_match('{http://filecabi\.(net|com)/video/([a-zA-Z0-9_-]+)}i', $this->_mediaInfo['url'], $match) || preg_match('{http://www\.filecabi\.(net|com)/video/([a-zA-Z0-9_-]+)}i', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['url'] = 'http://www.filecabi.net/movieplayer.swf?video=http%3A%2F%2Fwww.filecabi.net%2Fplayvideo.php%3Fcid%3D' . $match[2];
				$this->_mediaInfo['thumb'] = 'http://www.filecabi.net/p/' . $match[2] . '.jpg';
				$this->_mediaInfo['height'] = '360';
				$this->_mediaInfo['width'] = '460';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * porntube.com Video.
	 * @param	array	media info array
	 */
	function pornotube(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaporntube'] == '1') {
			if (preg_match('{pornotube\.com/media\.php\?m=(\d+)}', $this->_mediaInfo['url'], $match) || preg_match('{hottestvideos/\d/([a-zA-Z0-9-]+)}', $this->_mediaInfo['url'], $match)) {
				$hurl = $this->_mediaInfo['link'];
				if (function_exists('curl_init'))
				{
					
					$c = $this->getpath();
					$cookie = $c . '/home/devnixco/public_html/anymedia/cookie.txt';
					$handle = curl_init();
					
					curl_setopt ($handle, CURLOPT_URL, $hurl);
					curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
					curl_setopt ($handle, CURLOPT_COOKIEFILE, $cookie);
					curl_setopt ($handle, CURLOPT_COOKIEJAR, $cookie);
					curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);
					curl_setopt($handle, CURLOPT_HEADER, 1);
					

					$content = curl_exec($handle);
					curl_close($handle);
					
				} elseif (@fclose(@fopen($hurl, "r")) && ini_get('allow_url_fopen')) {
					$content = file($hurl);
					$content = implode("",$content);
				} else {
					return $this->_mediaInfo['type'] = 'error';
				}
				$tmpContent = $content;
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				preg_match('{http://pornotube\.com/player/v\.swf\?v=(.+)\[\/MEDIA\]}i',$tmpContent,$m);
				$this->_mediaInfo['url'] = 'http://pornotube.com/player/v.swf?v=' . $m[1];
				unset($content, $tmpContent);
				$this->_mediaInfo['height'] = '';
				$this->_mediaInfo['width'] = '';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * stage6.divx.com Video.
	 * @param	array	media info array
	 */
	function stage6(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediastage6'] == '1') {
			if (preg_match('{stage6\.divx\.com/user/\S+/video/(\d+)\S+}', $this->_mediaInfo['url'], $match) || preg_match('{stage6\.divx\.com/\S+/video/(\d+)\S+}', $this->_mediaInfo['url'], $match) || preg_match('{video\.stage6\.com/(\d+)\S+}', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				if(preg_match('{(Stage6\s.+)-.*Download}i', $title, $m)){
					$this->_mediaInfo['title'] = $m[1];
				} else {
					$this->_mediaInfo['title'] = $title;
				}
				$this->_mediaInfo['thumb'] = 'http://images.stage6.com/video_images/' . $match[1] . 't.jpg';
				$this->_mediaInfo['url'] = 'http://video.stage6.com/' . $match[1] . '/.divx';
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				preg_match('{var\s*dv\s*\=\s*new\s*divx_video\(728,\s*Math\.round\(728\s*\*\s*(\d+)\s*\/\s*(\d+)\)}i', $tmpContent, $size);
				$this->_mediaInfo['height'] = $size[1];
				$this->_mediaInfo['width'] = $size[2];
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->divx_video($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Brightcove Video.
	 * @param	array	media info array
	 */
	function brightcove(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediabrightcove'] == '1') {
			if (preg_match('{brightcove\.com/title\.jsp\?title=([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://www.brightcove.com/playerswf';
				$this->_mediaInfo['url2'] = 'allowFullScreen=true&initVideoId=' . $match[1] . '&servicesURL=http://www.brightcove.com&viewerSecureGatewayURL=https://www.brightcove.com&cdnURL=http://admin.brightcove.com&autoStart=false';
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '412';
				$this->_mediaInfo['width'] = '486';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}	
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * photobucket.com Video.
	 * @param	array	media info array
	 */
	function photobucket(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaphotobucket'] == '1') {
			if (preg_match('{http://\w(\d+)\.photobucket\.com/\w+/([a-zA-Z0-9]+)/(.+)/\?action=view[&amp;]*\w+=([a-zA-Z0-9_-]+)}', $this->_mediaInfo['url'], $matches)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://vid' . $matches[1] . '.photobucket.com/player.swf?file=http://vid' . $matches[1] . '.photobucket.com/albums/' . $matches[2] . '/' . $matches[3] . '/' . $matches[4] . '.flv';
				$this->_mediaInfo['height'] = '361';
				$this->_mediaInfo['width'] = '448';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Revver Video.
	 * @param	array	media info array
	 */
	function revver(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediarevver'] == '1') {
			if (preg_match('{revver\.com/watch/([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://flash.revver.com/player/1.0/player.swf';
				$this->_mediaInfo['url2'] = 'mediaId=' . $match[1] . '&affiliateId=0&allowFullScreen=true';
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '392';
				$this->_mediaInfo['width'] = '480';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * liveleak Video.
	 * @param	array	media info array
	 */
	function liveleak(& $mediaArray)
	{
		if ($this->vbulletin->options['anymedialiveleak'] == '1') {
			if (preg_match('/view\?\i=([a-zA-Z0-9_-]+)/i', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://www.liveleak.com/player.swf?autostart=false&token=' . $match[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '370';
				$this->_mediaInfo['width'] = '450';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Veoh Video.
	 * @param	array	media info array
	 */
	function veoh(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaveoh'] == '1') {
			if (preg_match('{videos/([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://www.veoh.com/videodetails2.swf?permalinkId=' . $match[1] . '&id=anonymous&player=videodetailsembedded&videoAutoPlay=0';
				$this->_mediaInfo['height'] = '438';
				$this->_mediaInfo['width'] = '540';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Veoh Video.
	 * @param	array	media info array
	 */
	function putfile(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaputfile'] == '1') {
			if (preg_match('{media\.putfile\.com/([a-zA-Z0-9_-]+)}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://feat.putfile.com/flow/putfile.swf?videoFile=' . $match[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '349';
				$this->_mediaInfo['width'] = '420';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Sevenload Video.
	 * @param	array	media info array
	 */
	function sevenload(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediasevenload'] == '1') {
			if (preg_match('{http://\w*\.sevenload\.com/videos/([a-zA-Z0-9_-]+)-\w+/}', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				preg_match("{stills\['2'\]\s+=\s?'(.+.jpg)}i",$tmpContent,$m);
				$this->_mediaInfo['thumb'] = $m[1];
				$this->_mediaInfo['url'] = 'http://en.sevenload.com/pl/' . $match[1] . '/425x350/swf';
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				unset($tmpContent);
				$this->_mediaInfo['height'] = '350';
				$this->_mediaInfo['width'] = '425';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
				
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Sevenload Video.
	 * @param	array	media info array
	 */
	function gametrailers(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediagametrailers'] == '1') {
			if (preg_match('{/player/(\d+)\.}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				preg_match('{http://www\.gametrailers\.com/moses/moviesthumbs/(.+).jpg}i',$tmpContent,$m);
				$this->_mediaInfo['thumb'] = 'http://www.gametrailers.com/moses/moviesthumbs/' . $m[1] . '.jpg';				
				$this->_mediaInfo['url'] = 'http://www.gametrailers.com/remote_wrap.php?mid=' . $match[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				unset($tmpContent);
				$this->_mediaInfo['height'] = '409';
				$this->_mediaInfo['width'] = '480';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Spikedhumor Video.
	 * @param	array	media info array
	 */
	function spikedhumor(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaspiked'] == '1') {
			if (preg_match('{spikedhumor\.com/articles/(\d+)/}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.spikedhumor.com/player/vcplayer.swf?file=http://www.spikedhumor.com/videocodes/' . $match[1] . '/data.xml&auto_play=false';
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$pattern = '{http://master01-pcdn\.spikedhumor\.com/' . $match[1] . '/' . $match[1] . '(.+)\.flv}mi';
				preg_match($pattern,$tmpContent,$m);
				$this->_mediaInfo['thumb'] = 'http://master01-pcdn.spikedhumor.com/' . $match[1] . '/' . $match[1] . $m[1] . '.jpg';
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '300';
				$this->_mediaInfo['width'] = '400';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Streetfire Video.
	 * @param	array	media info array
	 */
	function streetfire(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediastreetfire'] == '1') 
		{
			if (preg_match('{/video/([\d\w-]+)}', $this->_mediaInfo['url'], $match) || preg_match('{streetfire\.net/[\d\w]+/0/([\d\w-]+)}', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://videos.streetfire.net/vidiac.swf';
				$this->_mediaInfo['url2'] = 'video=' . $match[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '352';
				$this->_mediaInfo['width'] = '428';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * libero Video.
	 * @param	array	media info array
	 */
	function libero(& $mediaArray)
	{
		if ($this->vbulletin->options['anymedialibero'] == '1')
		{
			if(preg_match('{http://video\.libero\.it/app/play\?id=([a-z0-9]*)}i', $this->_mediaInfo['url'], $match))
			{
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['height'] = '333';
				$this->_mediaInfo['width'] = '400';
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://video.libero.it/static/swf/eltvplayer.swf?id=' . $match[1] . '.flv&ap=0';
				 $this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}	
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * Godtube Video.
	 * @param	array	media info array
	 */
	function godtube(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediagodtube'] == '1') {
			if (preg_match('{http://(|www\.)godtube\.com/view_video\.php.+}i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				preg_match('{embed\s*this\s*video:\</label\>\s*\<input\s*type="text"\s*name.*FlashVars=&quot;(.+)&quot;\s*wmode=}si' ,$tmpContent,$m);
				preg_match('{www.godtube.com/thumb/(.+)\.jpg}si' ,$m[1],$mt);
				$this->_mediaInfo['thumb'] = 'http://images.godtube.com/thumb/' . $mt[1] . '.jpg';
				$this->_mediaInfo['url'] = 'http://godtube.com/flvplayer.swf';
				$this->_mediaInfo['url2'] = $m[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $mt[1];
				$this->_mediaInfo['height'] = '270';
				$this->_mediaInfo['width'] = '330';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
				
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Yahoo Video.
	 * @param	array	media info array
	 */
	function yahoo(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediayahoo'] == '1')
		{
			$tmpContent = $this->fetchwww();
			$title = $this->hTittle($tmpContent);
			$this->_mediaInfo['title'] = $title;
			$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
			preg_match("/<embed src='(.+)'\s*flashvars='(.+)'\s*type=.+<\/embed>/i",$tmpContent,$m);
			$this->_mediaInfo['url'] = $m[1];
			$this->_mediaInfo['url2'] = $m[2];
			$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $m[1];
			$this->_mediaInfo['height'] = '350';
			$this->_mediaInfo['width'] = '425';
			$this->_mediaInfo['videosite'] = 1;
			unset($tmpContent);
			$this->player($mediaArray);
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * Xtube Video.
	 * @param	array	media info array
	 */
	function xtube(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaxtube'] == '1')
		{
			if (preg_match('{(http://\S+\.xtube\.com/.+)}i', $this->_mediaInfo['url'], $match) || preg_match('{http://www.XTube.com/play_re.php\?v=(\S+)}i', $this->_mediaInfo['url'], $match)) {
				
				//$hurl2 = $this->_mediaInfo['link'];
				//$hurl = html_entity_decode($hurl2);
				$hurl = html_entity_decode($this->_mediaInfo['url']);
				$tmpContent = $this->fetchwww($hurl);
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				preg_match('{Embed Video without auto-play \(Big Screen\):\<br\>.+\<embed src="(http://.+)"\s*quality=}si', $tmpContent, $m);
				$this->_mediaInfo['url'] = $m[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $m[1];
				$this->_mediaInfo['height'] = '428';
				$this->_mediaInfo['width'] = '499';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				return $this->_mediaInfo['type'] = 'error';
			}
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	/**
	 * Porkolt Video.
	 * @param	array	media info array
	 */
	function porkolt(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaporkolt'] == '1') 
		{
			if (preg_match('{porkolt\.com\S+-(\d+)\.html}i', $this->_mediaInfo['url'], $match)) {
				$tmpContent = $this->fetchwww();
				$title = $this->hTittle($tmpContent);
				$this->_mediaInfo['title'] = $title;
				$this->_mediaInfo['thumb'] = $this->vbulletin->options['bburl'] . '/anymedia/anyMediaplay.gif';
				$this->_mediaInfo['url'] = 'http://content3.porkolt.com/miniplayer/player.swf?parameters=http://datas3.porkolt.com/datas/' . $match[1];
				$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $match[1];
				$this->_mediaInfo['height'] = '327';
				$this->_mediaInfo['width'] = '400';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * Megarotic Video.
	 * @param	array	media info array
	 */
	function megarotic(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediamegarotic'] == '1')
		{
			$hurl = $this->_mediaInfo['link'];
			if (function_exists('curl_init'))
			{
				$c = $this->getpath();
				$cookie = $c . '/anymedia/cookie.txt';
				$handle = curl_init();
				curl_setopt ($handle, CURLOPT_URL, $hurl);
				curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt ($handle, CURLOPT_COOKIEFILE, $cookie);
				curl_setopt ($handle, CURLOPT_COOKIEJAR, $cookie);
				curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);

				$content = curl_exec($handle);
				curl_close($handle);
			} elseif (@fclose(@fopen($hurl, "r")) && ini_get('allow_url_fopen')) {
				$content = file($hurl);
				$content = implode("",$content);
			} else {
				return $this->_mediaInfo['type'] = 'error';
			}
			preg_match('{\<embed src="http://video\.megarotic\.com/v/([a-z0-9]+)\"}i',$content,$m);
			$this->_mediaInfo['url'] = 'http://video.megarotic.com/v/' . $m[1];
			$this->_mediaInfo['idM'] = $this->_mediaInfo['extension'] . '-' . $m[1];
			preg_match('{http://img1\.megarotic\.com/([\d\w/]+)\.jpg}si', $content, $mp);
			$this->_mediaInfo['thumb'] = 'http://img1.megarotic.com/' . $mp[1] . '.jpg';
			preg_match('{\"videoname\",\"([\w\d\s\._-]+)\"\)}si', $content, $t);
			
			$this->_mediaInfo['title'] = $t[1];
			unset($content);
			$this->_mediaInfo['height'] = '337';
			$this->_mediaInfo['width'] = '424';
			$this->_mediaInfo['videosite'] = 1;
			unset($tmpContent);
			$this->player($mediaArray);
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	/**
	 * YouPorn Video.
	 * @param	array	media info array
	 */
/*	function youporn(& $mediaArray) {
		if($this->vbulletin->options['anymediayouporn']) {
			if(preg_match('{http://download\.youporn\.com/download/([\d\w/_-]*)\.flv}i', $this->_mediaInfo['url'], $match) || preg_match('{http://www.XTube.com/play_re.php\?v=(\S+)}i', $this->_mediaInfo['url'], $match)) {
				# code...
			} else {
				# code...
			}
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
*/




	/**
	 * YouPorn Video.
	 * @param	array	media info array
	 */
	function youporn(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediayouporn'] == '1')
		{
			if (preg_match('{http://download\.youporn\.com/download/([\d\w/_-]*)\.flv}i', $this->_mediaInfo['url'], $match) || preg_match('{http://www.XTube.com/play_re.php\?v=(\S+)}i', $this->_mediaInfo['url'], $match))
			{
				if($gmatch)
				{
					$hurl = $this->_mediaInfo['link'];
					if (function_exists('curl_init'))
					{
						$c = $this->getpath();
						$cookie = $c . '/anymedia/cookie.txt';
						$handle = curl_init();
						curl_setopt ($handle, CURLOPT_URL, $hurl);
						curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
						curl_setopt ($handle, CURLOPT_COOKIEFILE, $cookie);
						curl_setopt ($handle, CURLOPT_COOKIEJAR, $cookie);
						curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);

						$content = curl_exec($handle);
						curl_close($handle);
					} elseif (@fclose(@fopen($hurl, "r")) && ini_get('allow_url_fopen')) {
						$content = file($hurl);
						$content = implode("",$content);
					} else {
						return $this->_mediaInfo['type'] = 'error';
					}
					preg_match('{(<script type="text/javascript" src="http://static.youporn.com/script/swfobject.js"></script>)\s+.*?(<script type="text/javascript">\s+var.*?</script>)}s',$content,$m);
				}	
				//$link = html_entity_decode($m[1]);
				//$this->_mediaInfo['url'] = $m[1];
				$this->_mediaInfo['url'] = $this->vbulletin->options['bburl'] . '/players/mediaplayer.swf';	
				$this->_mediaInfo['link'] = 'http://download.youporn.com/download/' . $match[1] . '.flv';		
				$this->_mediaInfo['height'] = '340';
				$this->_mediaInfo['width'] = '500';
				$this->_mediaInfo['videosite'] = 1;
				unset($tmpContent);
				$this->adobe_flv($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

}
?>