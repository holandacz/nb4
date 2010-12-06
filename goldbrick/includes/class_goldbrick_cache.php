<?php
/**
 * Handles internals of caching media information.
 * 
 * @version			$Revision: 111 $
 * @modifiedby		$LastChangedBy: digitallyepic_siradrian $
 * @lastmodified	$Date: 2007-10-29 15:38:38 -0700 (Mon, 29 Oct 2007) $
 */
class Goldbrick_Cache
{
	/**
	 * vBulletin registry object
	 * @var		vB_Registry
	 */
	private $registry;
	
	/**
	 * Media information array to cache
	 * @var		array
	 */
	private $info;
	
	/**
	 * Fields that are created using preg_match
	 * Sites that do not define a custom set of fields will use the 'global' subset.
	 *
	 * @var		array
	 */
	private $regex_fields = array(
		'global' => array(
			'idregex'	 => array('id',	   'url'),
			'titleregex' => array('title', 'content')
		),
		'custom' => array()
	);
		
	/**
	 * Fields that are formatted using sprintf
	 * Sites that do not define a custom set of fields will use the 'global' subset.
	 * 
	 * @var		array
	 */
	private $format_fields = array(
		'global' => array(
			'srcformat'		 => array('src',	  'id'),
			'thumbformat'	 => array('thumb',	  'id'),
			'flashvarformat' => array('flashvar', 'id')
		),
		'custom' => array()
	);
	
	/**
	 * Valid media extensions
	 * 
	 * @var		array
	 */
	
	private $extensions = array(
		'swf'			=>	array('application/x-shockwave-flash',	'flash',	'swf'),
		'flv'			=>	array('application/x-shockwave-flash',	'flash',	'flv'),
		'mp3'			=>	array('audio/mpeg',						'flash',	'mp3'),
		
		// Quick Time
		'mov'			=>	array('video/quicktime',				'quick_time',	'mov'),
		'mpeg'			=>	array('video/x-mpeg',					'quick_time'),
		'mpg'			=>	array('video/x-mpeg',					'quick_time'),
		'mp4'			=>	array('video/mp4',						'quick_time'),
		
		// Real Media
		'rm'			=>	array('audio/x-pn-realaudio-plugin',	'real_media'),
		'ra'			=>	array('audio/x-pn-realaudio-plugin',	'real_media'),
		'rv'			=>	array('audio/x-pn-realaudio-plugin',	'real_media'),
		'ram'			=>	array('audio/x-pn-realaudio-plugin',	'real_media'),
		
		// Windows Media
		'wma'			=>	array('application/x-mplayer2',			'windows_media'),
		'wav'			=>	array('application/x-mplayer2',			'windows_media'),
		'ogg'			=>	array('application/x-mplayer2',			'windows_media'),
		'ape'			=>	array('application/x-mplayer2',			'windows_media'),
		'mid'			=>	array('application/x-mplayer2',			'windows_media'),
		'midi'			=>	array('application/x-mplayer2',			'windows_media'),
		'asf'			=>	array('application/x-mplayer2',			'windows_media'),
		'asx'			=>	array('application/x-mplayer2',			'windows_media'),
		'wm'			=>	array('application/x-mplayer2',			'windows_media'),
		'wmv'			=>	array('application/x-mplayer2',			'windows_media',	'wmv'),
		'avi'			=>	array('video/avi',						'windows_media'),
		
		// Adobe PDF
		'pdf'			=>	array('application/pdf',				'adobe_pdf'),
		
		// Images
		'gif'			=>	array('image/gif',						'image'),
		'jpg'			=>	array('image/pjpeg',					'image',	'jpg'),
		'jpeg'			=>	array('image/pjpeg',					'image'),
		'bmp'			=>	array('image/bmp',						'image'),
		'png'			=>	array('image/x-png',					'image')
	);
	
	/**
	 * Valid media options
	 * 
	 * @var		array
	 */
	
	private $gb_options = array(
		'title',
		'width',
		'height',
		'autoplay',
		'loop'
	);

	/**
	 * A list of fields for the gb_cache table.
	 * 
	 * Items in $info that are not in this array will be removed before inserting.
	 * Items in $db_fields that are not in $info will throw a fatal error.
	 * 
	 * @var		array
	 */
	private $db_fields = array(
		'width',
		'height',
		'widthpad',
		'title',
		'url',
		'src',
		'flashvar',
		'flashvarextra',
		'thumb',
		'loop',
		'extension',
		'site',
		'profile'
	);
	
	/**
	 * A list of safe variables to import from the site configuration files.
	 * @var		array
	 */
	private $safe_fields = array(
		'info'			=> true, 
		'regex_fields'	=> false, 
		'format_fields' => false
	);
	
	/**
	 * Target URL is already cached
	 * @var		boolean
	 */
	private $existing = false;
	
	/**
	 * A list of "behaviour" setting arrays that use the global/custom structure
	 * @var		array
	 */
	private $behaviours = array('regex_fields', 'format_fields');
	
	/**
	 * Enables debug mode for this class
	 * @var		boolean
	 */
	private $debug;
	
	/**
	 * Sets up reference to registry object.
	 * 
	 * @param	vB_Registry
	 * @param	boolean		Enable debug mode
	 */
	public function __construct(vB_Registry $registry, $debug = false)
	{
		$this->registry = $registry;
		$this->debug	= $debug || defined('GOLDBRICK_DEBUG_CACHE');
	}
		
	
	/**
	 * Takes a URL and returns the information about the media file/host
	 * 
	 * @param	string	URL to parse
	 * @return	array	URL information
	 */
	public function parse_url($url, $gb_options = null)
	{
		if (is_array($gb_options)){
		}

		if ($this->debug)
		{
			goldbrick_debug('Source URL', $url);
		}
		
		if ($existing = $this->check_existing($url))
		{
			$this->info		= $existing;
			$this->existing = true;
			
			if ($this->debug)
			{
				echo '<h2>URL Already Cached!</h2>';
			}
			return $existing;
		}

		if (!$identifier_site = $this->find_host_identifier($url) AND !$identifier_ext = $this->find_valid_extension($url))
		{
			return false;
		}

		if ($identifier_site)
		{
			$identifier = $identifier_site;
			
			$this->load_config_values($identifier, $gb_options);
			
			$this->info['site'] = $identifier;
		}
		
		else if ($identifier_ext)
		{
			$identifier			= $identifier_ext;
			$identifier['url']	= $url;

			$this->load_config_ext_values($identifier, $gb_options);
		}
		
		else
		{
			return false;
		}
		
		$this->info['url']	= $url;
		
		if (!is_array($identifier) AND !$this->is_valid_url($url))
		{
			if ($this->debug) echo '<h2>Invalid URL</h2>';
			return false;
		}

		if (empty($this->info['norequest']) AND $identifier_site)
		{
			$this->info['content'] = $this->open_site($url);
		}

		if (function_exists($function = "goldbrick_hook_{$identifier}_opened"))
		{
			$function($this->info);
		}
		
		if(!is_array($identifier))
		{
			$this->exec_regex_fields(
				$this->get_settings_source($identifier, 'regex')
			);

			$this->exec_format_fields(
				$this->get_settings_source($identifier, 'format')
			);
		}

		if (function_exists($function = "goldbrick_hook_{$identifier}_complete"))
		{
			$function($this->info);
		}
		
		if ($this->info['increase_size'])
		{
			$this->info['widthpad']		= $this->info['width'] + $this->info['increase_size'];
			$this->info['height']		= $this->info['height'] + $this->info['increase_size'];
		}
		
		if ($this->debug)
		{
			goldbrick_debug('Info after processing', $this->info);
			
			foreach ($this->info as $key => $value)
			{
				if (!in_array($key, $this->db_fields))
				{
					unset($this->info[$key]);
				}
			}
			
			goldbrick_debug('Final array to be saved', $this->info);
			
			foreach ($this->db_fields as $field)
			{
				if (!isset($this->info[$field]))
				{
					trigger_error("required field $field is missing", E_USER_ERROR);
				}
			}
			exit;
		}

		return $this->info;
	}
	
	/**
	 * Re-loads a URL, and checks whether or not it is still active.
	 *
	 * @param	string		URL to check
	 * @return	boolean		true if inactive; false otherwise
	 */
	public function is_inactive($url)
	{				
		if (!$identifier = $this->find_host_identifier($url))
		{
			return false;
		}
		
		$this->load_config_values($identifier); 

		$this->info['url']	= $url;
		$this->info['site'] = $identifier;
		
		if (!$this->is_valid_url($url))
		{
			return true;
		}

		$this->info['content'] = $this->open_site($url);
		
		foreach ($this->info['expireregex'] as $regex => $desired_result)
		{
			if (preg_match($regex, $this->info['content']) == $desired_result)
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function	 is_valid_attachment($attach_ext)
	{
		$gb_ext = strtolower(file_extension($attach_ext));
		
		if (array_key_exists($attach_ext, $this->extensions))
		{
			
			if (file_exists(DIR . '/goldbrick/includes/extensions/' . $this->extensions[$attach_ext][1] . '.php'))
			{
				return $this->extensions[$attach_ext];
			}

					
			
		}
		
		
	}
		
	/**
	 * Determines whether or not a posted URL is valid based on the guidelines
	 * in the site config.
	 * 
	 * @param	string		URL
	 * @return	boolean		true = valid ; false = invalid
	 */
	private function is_valid_url($url)
	{
		$settings = $this->get_settings_source($this->info['site'], 'regex');

		if (isset($this->info['validregex']))
		{
			$func  = 'array_values';
			$regex = $this->info['validregex'];
			unset($this->info['validregex']);
		}	

		else if (isset($this->info['idregex']) and $settings['idregex'][1] ==  'url')
		{
			$func  = 'array_keys';
			$regex = $this->info['idregex'];
		}
		
		else
		{
			var_dump($this->info);
			trigger_error("$url has not been validated!", E_USER_ERROR);
		}
		
		if (is_array($regex))
		{
			foreach ($func($regex) as $expression)
			{
				if (preg_match($expression, $url))
				{
					return true;
				}
			}	
			return false;
		}
		
		return preg_match($regex, $url);
	}
	
	/**
	 * Loads configuration values into object context.
	 * 
	 * @param	string		Site identifier
	 */
	protected function load_config_values($identifier, $gb_options)
	{
		$changes = array();
		$data	 = goldbrick_cache_load_config($identifier, array_keys($this->safe_fields),$gb_options);
		
		foreach ($this->safe_fields as $field => $auto)
		{
			if (!isset($data[$field]))
			{
				return;
			}
			
			if ($auto)
			{
				$this->$field = $data[$field];		
			}
			else if (in_array($field, $this->behaviours))
			{
				$this->{$field}['custom'][$identifier] = $data[$field];
			}
			
			if ($this->debug)
			{
				goldbrick_debug("\$$field loaded from config", $data[$field]);
			}
			
		}
		
		
	}
	
	/**
	 * Loads configuration values into object context.
	 * 
	 * @param	string		Site identifier
	 */
	protected function load_config_ext_values($identifier, $gb_options)
	{
		$changes = array();

		$data	 = goldbrick_cache_load_ext_config($identifier, array_keys($this->safe_fields), $gb_options);

		foreach ($this->safe_fields as $field => $auto)
		{
			if (!isset($data[$field]))
			{
				return;
			}
			
			if ($auto)
			{
				$this->$field = $data[$field];		
			}
			else if (in_array($field, $this->behaviours))
			{
				$this->{$field}['custom'][$identifier] = $data[$field];
			}
			
			if ($this->debug)
			{
				goldbrick_debug("\$$field loaded from config", $data[$field]);
			}
			
		}
		
		
	}
	
	/**
	 * Fetches the $type settings for a given site.
	 * If the site defines its own ['custom'] subarray of a setting type, then
	 * it will be used -- if not, then it will fall back on the ['global'] group.
	 * 
	 * @param	string		Site identifier
	 * @param	string		Sub array ('regex' or 'format')
	 */
	private function get_settings_source($identifier, $type = 'regex')
	{
		$key = $type . '_fields';
		
		return	!empty($this->{$key}['custom'][$identifier])
			? $this->{$key}['custom'][$identifier]
			: $this->{$key}['global'];
	}
	
	/**
	 * Executes all regular expression field replacements.
	 * 
	 * $settings contains the settings data used by the site (custom or global) 
	 * which decides:
	 * 
	 * 'key' - $this->info key containing regular expression array
	 * [0]	 - $this->info key to set
	 * [1]	 - $this->info key to use as RE subject
	 * 
	 * $this->info[$regexfield] contains an array: 'regex' => match index, ...
	 * 
	 * @param	array		Regex settings
	 */
	private function exec_regex_fields($settings)
	{
		foreach ($settings as $regexfield => $regexinfo)
		{
			list($realfield, $var) = $regexinfo;

			if ($this->info[$regexfield])
			{
				if (!is_array($this->info[$regexfield]))
				{
					$this->info[$regexfield] = array($this->info[$regexfield]);
				}

				foreach ($this->info[$regexfield] as $regex => $match)
				{
					$matches = null;
					if (preg_match($regex, $this->info[$var], $matches))
					{
						//goldbrick_debug('Passed Regex', $regex, $var, $this->info[$var]);
						
						$this->info[$realfield] = $matches[$match];

						break;
					} else {
						print_r($regex);
						echo 'no match';
					}
					
					if ($this->debug)
					{
						goldbrick_debug('Failed Regex', $regex, $var, $this->info[$var]);
					}
				}
			}
			
			unset($this->info[$regexfield]);
		}		
	}
	
	/**
	 * Executes all field formatting using sprintf.
	 * 
	 * $settings contains the settings data used by the site (custom or global) 
	 * which decides:
	 * 
	 * 'key' - $this->info key containing format string
	 * [0]	 - $this->info key to set
	 * [1]	 - $this->info key to use as Nth value in sprintf...
	 * [2]	 - ...
	 * 
	 * @param	array		Format settings
	 */
	private function exec_format_fields($settings)
	{
		foreach ($settings as $format_input => $formatinfo)
		{
			$format_output = array_shift($formatinfo);
			
			$params = array();
			foreach ($formatinfo as $param)
			{
				$params[] = $this->info[$param];
			}
			
			unset($formatinfo);

			if ($this->info[$format_input])
			{
				array_unshift($params, $this->info[$format_input]);
				$this->info[$format_output] = call_user_func_array('sprintf', $params);
			}
			
			unset($this->info[$format_input]);
		}
	}
	
	
	/**
	 * Extracts the host 'identifier' from a URL.
	 * ex, 'youtube', 'metacafe', etc.
	 * 
	 * @param	string	Source URL
	 * @return	string	host identifier
	 */
	private function find_host_identifier($url)
	{
		$matches = array();
		preg_match('/^(http:\/\/)?([^\/]+)/i', $url, $matches);
		
		$host  = preg_replace('/^([\.a-z0-9]+)/', '$1', str_replace('www.', '', $matches[2]));
		$parts = explode('.', $host);
		
		if (file_exists(DIR . '/goldbrick/includes/sites/' . $parts[1] . '.php'))
		{
			return $parts[1];
		}
		
		if (file_exists(DIR . '/goldbrick/includes/sites/' . $parts[0] . '.php'))
		{
			return $parts[0];
		}
		
		return false;
	}
	
	/**
	 * Check URL for valid extensions.
	 * ex, '.mp3', '.png', etc.
	 * 
	 * @param	string	Source URL
	 * @return	string	host identifier
	 */
	private function find_valid_extension($url)
	{
		$gb_ext = strtolower(file_extension($url));
		
		if (array_key_exists($gb_ext, $this->extensions))
		{
			
			if (file_exists(DIR . '/goldbrick/includes/extensions/' . $this->extensions[$gb_ext][1] . '.php'))
			{
				return $this->extensions[$gb_ext];
			}
		}
	}


	/**
	 * Opens a remote connection and fetches HTML from the page.
	 * 
	 * @param	string	Remote URL
	 * @return	string	HTML Response
	 */
	private function open_site($url)
	{
		if (empty($url))
		{
			$url = unhtmlspecialchars($this->info['link']);
		}
		
		if (function_exists('curl_init'))
		{
			$user_agent		= 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11';
			$header[0]		= "Accept: text/xml,application/xml,application/xhtml+xml,";
			$header[0]		.= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
			$header[]		= "Cache-Control: max-age=0";
			$header[]		= "Connection: keep-alive";
			$header[]		= "Keep-Alive: 300";
			$header[]		= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
			$header[]		= "Accept-Language: en-us,en;q=0.5";
		
			if ($this->info['header'])
			{
				$header[] = $this->info['header'];
			}
			$header[] = "Pragma: "; // browsers keep this blank.
		
			$handle = curl_init();

			curl_setopt($handle, CURLOPT_URL, $url);
			curl_setopt($handle, CURLOPT_USERAGENT, $user_agent);
			curl_setopt($handle, CURLOPT_HTTPHEADER, $header);
			curl_setopt($handle, CURLOPT_REFERER, 'http://www.google.com');
			curl_setopt($handle, CURLOPT_ENCODING, 'gzip,deflate');
			curl_setopt($handle, CURLOPT_AUTOREFERER, true);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
			if ($this->info['postfield'])
			{
				curl_setopt($handle, CURLOPT_POSTFIELDS, $this->info['postfield']);
			}
			
			curl_setopt($handle, CURLOPT_TIMEOUT, 30);
			
			$content = curl_exec($handle);

			curl_close($handle);
		} 
		
		else if (@fclose(@fopen($url, 'r')) and ini_get('allow_url_fopen')) 
		{
			$content = file($url);
			$content = implode('', $content);
		} 
		
		else 
		{
			return false;
		}
		
		return $content;
		
	}
	
	/**
	 * Checks to see if a URL exists in the database
	 * @param	string	URL to check
	 * @return	mixed	URL information array or FALSE on failure
	 */
	private function check_existing($link)
	{	
		$hash = md5($link);
		
		return $this->registry->db->query_first("
			SELECT *
			FROM " . TABLE_PREFIX . "gb_cache
			WHERE hash = '$hash'
		");
	}
	
	/**
	 * Saves the current $cache_media [ $this->info ] array to the database.
	 * 
	 * @param	array		Media information to store
	 * @param	integer		Userid of poster
	 * @param	integer		Postid - 0 when creating a new post
	 * @param	string		Posthash - '' when editing posts
	 */
	public function save($cache, $userid, $postid = 0, $posthash = '')
	{
		if (!$this->existing)
		{
			foreach ($cache as $key => $value)
			{
				if (!in_array($key, $this->db_fields))
				{
					unset($cache[$key]);
				}
			}
			
			/*foreach ($this->db_fields as $field)
			{
				if (!isset($this->info[$field]))
				{
					echo $field;
					trigger_error("required field $field is missing", E_USER_ERROR);
				}
			}*/
			
			$cache['hash']	   = md5($cache['url']);
			$cache['dateline'] = TIMENOW;
		}
		
		$media = array(
			'hash'	   => $cache['hash'],
			'postid'   => $postid,
			'posthash' => $posthash,
			'userid'   => $userid
		);
		
		if (!$this->existing)
		{
			$this->registry->db->query_write(fetch_query_sql($cache, 'gb_cache'));
		}
		$this->registry->db->query_write(fetch_query_sql($media, 'gb_media'));
		
	}
	
	/**
	 * Finalizes the cache data by replacing the posthash with the postid after it
	 * has been posted.
	 * 
	 * @param	integer		Postid
	 * @param	string		Posthash
	 */
	public function set_postid($postid, $posthash)
	{
		$this->registry->db->query_write(fetch_query_sql(
			array(
				'postid'   => $postid,
				'posthash' => ''
			),
			'gb_media',
			"WHERE posthash = '$posthash'"
		));
	}
	
	public function fetch_expired_media($hashes)
	{
		$hashes = implode(',', array_map(
			array($this->registry->db, 'sql_prepare'),
			$hashes
		));
		
		$cutoff = 1;#$this->registry->options['gb_expiration_period'] * 86400;
		
		$result = $this->registry->db->query_read("
			SELECT cache.url, cache.hash, group_concat(media.postid) as postids
			FROM " . TABLE_PREFIX . "gb_cache as cache
			LEFT JOIN " . TABLE_PREFIX . "gb_media as media USING (hash)
			WHERE hash IN ($hashes) and cache.dateline + $cutoff < " . TIMENOW . "
			GROUP BY cache.hash
			ORDER BY cache.dateline ASC
		");
		
		$media = array();
	
		while ($link = $this->registry->db->fetch_array($result))
		{
			$media[] = $link;
		}
		
		if (defined('GOLDBRICK_DEBUG_CLEANUP'))
		{
			goldbrick_debug('fetched expired media', $media);
		}

		$this->registry->db->free_result($result);
		return $media;
	}
	
	/**
	 * Reverts the original posts, and changes [media] back to [url]
	 * 
	 * @param	array		Media records
	 */
	public function revert_posts($cache_records)
	{
		$postids = array();
		
		$find_case	  = 'CASE';
		$replace_case = 'CASE';
		
		foreach ($cache_records as $record)
		{
			if (!$record['postids']) continue; 
			
			$postids	   = array_merge($postids, explode(',', $record['postids']));
			$record['url'] = $this->registry->db->escape_string($record['url']);
			
			$find_case	  .= "\n" . str_repeat("\t", 5);
			$find_case	  .= "WHEN postid IN ($record[postids]) ";
			$find_case	  .= 'THEN \'[media]' . $record['url'] . '[/media]\'';
			
			$replace_case .= "\n" . str_repeat("\t", 5);
			$replace_case .= "WHEN postid IN ($record[postids]) ";
			$replace_case .= 'THEN \'[url]' . $record['url'] . '[/url]\'';
		}
		
		$find_case	  .= "\n" . str_repeat("\t", 4) . 'END';
		$replace_case .= "\n" . str_repeat("\t", 4) . 'END';
		
		$postids = implode(',', $postids);
		
		$this->registry->db->query_write("
			UPDATE " . TABLE_PREFIX . "post
			SET pagetext = replace(
				pagetext, 
				$find_case, 
				$replace_case
			)
			WHERE postid IN ($postids)
		");
		
		$this->registry->db->query_write("
			DELETE FROM " . TABLE_PREFIX . "postparsed
			WHERE postid IN ($postids)
		");
	}
	
	/**
	 * Removes the cache for media that failed the expiration check.
	 * 
	 * @param	array		URL hashes
	 */
	public function remove($hashes)
	{
		$hashes = implode(',', array_map(
			array($this->registry->db, 'sql_prepare'),
			$hashes
		));
		
		// TODO combine below queries
			
		$this->registry->db->query_write("
			DELETE FROM " . TABLE_PREFIX . "gb_cache
			WHERE hash IN ($hashes)
		");
		
		$this->registry->db->query_write("
			DELETE FROM " . TABLE_PREFIX . "gb_media
			WHERE hash in ($hashes)
		");
	}
	
	/**
	 * Updates the last-check (`dateline`) column to the current date for all the
	 * media hashes.
	 * 
	 * @param	array		URL hashes
	 */
	public function bump($hashes)
	{
		$hashes = implode(',', array_map(
			array($this->registry->db, 'sql_prepare'),
			$hashes
		));
			
		$this->registry->db->query_write("
			UPDATE " . TABLE_PREFIX . "gb_cache
			SET dateline = " . TIMENOW . "
			WHERE hash IN ($hashes)
		");
	}
}

?>