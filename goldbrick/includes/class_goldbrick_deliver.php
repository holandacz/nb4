<?php
/**
 * Handles internals for delivering media
 * 
 * @version     	$Revision: 111 $
 * @modifiedby  	$LastChangedBy: digitallyepic_nix $
 * @lastmodified	$Date: 2007-10-29 15:38:38 -0700 (Mon, 29 Oct 2007) $
 */
class Goldbrick_Deliver
{
	/**
	 * vBulletin registry object
	 * @var		vB_Registry
	 */
	private $registry;
	
	/**
	 * Loaded media from cache
	 * @var		array
	 */
	private $media;

	/**
	 * Enables debug mode for this class
	 * @var		boolean
	 */
	private $debug;
	
	/**
	 * List of expired cache records
	 * @var		array
	 */
	private $expired;
	
	/**
	 * Sets up reference to registry object.
	 * 
	 * @param	vB_Registry
	 * @param	boolean		Enable debug mode
	 */
	public function __construct(vB_Registry $registry, $debug = false)
	{
		$this->registry = $registry;
		$this->debug    = $debug || defined('GOLDBRICK_DEBUG_DELIVER');
		$this->expired  = array();
	}
	
	/**
	 * Sets the post ids to load media for
	 * 
	 * @param	array		List of postids
	 */
	public function set_postids($postids)
	{
		if (!is_array($postids) or empty($postids))
		{
			trigger_error('set_postids() requires a populated array', E_USER_ERROR);
			exit;
		}
		
		if ($this->debug)
		{
			goldbrick_debug('PostIDs', $postids);
		}
		
		$this->media = $this->fetch_media_from_cache(implode(',', $postids));	
		
		if ($this->debug)
		{
			goldbrick_debug('Fetched Media', $this->media);
		}
	}
	
	/**
	 * Delivers the HTML for a given media tag.
	 * This is the BBCode callback function (wrapped in a public callback, rather).
	 * 
	 * @param	string		URL to deliver
	 * @param	string		Options to customize delivery
	 * 
	 * @return	string		HTML output
	 */
	public function deliver($url, $options)
	{
		global $vbphrase, $stylevar;

		$url = unhtmlspecialchars($url);		
		
		if (!$info = $this->media[$url])
		{
			if ($this->debug)
			{
				goldbrick_debug('Media Cache', $this->media);
				goldbrick_debug('Requested URL', $url);
				
				trigger_error('URL not pre-cached!', E_USER_WARNING);
			}
		
			$url = htmlspecialchars_uni($url);
			return "<a href=\"$url\" target=\"_blank\">$url</a>";
		}
		
		$info['unique']  = substr($info['hash'], 0, 8);

		if ($info['site'] !== 0)
		{
			//$info['profile'] = $this->get_config_profile($info['site']);
		}
		
		else
		{
			$info['profile'] = $this->get_config_ext_profile($info['profile']);
		}
		
		
		if (is_integer($url))
		{
			$info = array_merge($info, $this->parse_media_options($options));
		}
		eval('$content = "' . fetch_template('gb_player') . '";');
		
		if ($this->debug)
		{
			goldbrick_debug('Delivering Media', $url);
			echo $content . '<hr />';
		}
		
		$cutoff = 1;#$this->registry->options['gb_expiration_period'] * 86400;
		
		// cleanup
		if ($info['dateline'] + $cutoff < TIMENOW)
		{
			if (empty($this->expired))
			{
				goldbrick_inject_plugin(
					'global_complete',
					"require_once(DIR . '/goldbrick/plugins/global_complete.php');"
				);
			}
			$this->expired[] = md5($url);
		}

		return $content;
	}
	
	/**
	 * Fetches all media associated with $postids from the cache.
	 * 
	 * @param	string		Comma-separated list of post ids
	 * @return	array		Media records
	 */
	private function fetch_media_from_cache($postids)
	{
		$result = $this->registry->db->query_read("
			SELECT cache.*
			FROM " . TABLE_PREFIX . "gb_media
			LEFT JOIN " . TABLE_PREFIX . "gb_cache as cache using (hash)
			WHERE postid IN ($postids)
		");

		$records = array();
		while ($record = $this->registry->db->fetch_array($result))
		{
			$records[$record['url']] = $record;
		}

		$this->registry->db->free_result($record);
		return $records;
	}
	
	/**
	 * Parses the user-input options and customizes $the media based on it.
	 * 
	 * @param	string		User-input options (from bbCode)
	 * @return	array		Parsed options
	 */
	private function parse_media_options($options)
	{
		return array();
		
		// TODO work in progress
		$final_options = array();
		$options = preg_split('/[\s,]+/', array_map('strtolower', $options), -1, PREG_SPLIT_NO_EMPTY);
		
		// Compatibility Mode (Anonymous)
		if (count($options) == 5)
		{
			$final_options['width']     = (int)    $options[0];
			$final_options['height']    = (int)    $options[1];
			$final_options['autoplay']  = (bool)   $options[2];
			$final_options['loop']      = (int)    $options[3];
			$final_options['extension'] = (string) $options[4]; 
			
			return $final_options;
		}
		
		$matches = null;
		$final_options['autoplay'] = in_array('autoplay', $options, true);
		
		foreach ($options as $option)
		{			
			if (preg_match('/^([0-9]+)x([0-9]+)$/', $option, $matches))
			{
				$final_options['width']  = (int) $matches[1];
				$final_options['height'] = (int) $matches[2];
			}
			
			else if ($option == 'autoplay')
			{
				$final_options['autoplay'] = true;
			}
			
			else if (strpos($option, 'loop') !== false)
			{
				$final_options['loop'] = intval(substr($option, 4));
			}
			
			else if ($option[0] == '.')
			{
				$final_options['extension'] = substr($option, 1);
			}
		}	
		
		// TODO lots of sanitization!

		return $final_options;
	}
	
	/**
	 * Loads configuration values into object context.
	 * 
	 * @param	string		Site identifier
	 */
	protected function get_config_profile($identifier)
	{
		$data = goldbrick_cache_load_config($identifier, array('info'));
		return $data['info']['profile'];
	}
	
	protected function get_config_ext_profile($identifier)
	{
		$data = goldbrick_cache_load_ext_config($identifier, array('info'));
		return $data['info']['profile'];
	}
	
	/**
	 * Gets a comma separated string of the expired hashes.
	 * 
	 * @return	string		Comma delimited hashes
	 */
	public function get_expired_hashes()
	{
		return implode(',', $this->expired);
	}
}

?>