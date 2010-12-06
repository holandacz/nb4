<?php
# Zoints Thread Tags System 
#
# Copyright 2006 Zoints Inc.
# This code may not be redistributed without prior written consent.
#
if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

/**
 * The Zoints Tags Class
 * 
 * Deals with managing tags (adding, removing, preparing, loading) in order
 * to simplify distributed code
 *
 * @package zointstags
 * @author Zoints
 */
class zointstags_tags
{
	/**
	 * Encountered an error during processing?
	 * @var bool
	 */
	var $error = false;
	/**
	 * Array of all the tags that have been added
	 * @var array
	 */
	var $tags = array();
	
	/**
	 * Ignore Errors for postings.php
	 * @var bool
	 */
	var $postings = false;
	
	/**
	 * The Stopwords to strip out
	 * @var array
	 */
	var $stopwords = array();
	/**
	 * The Character Replacements
	 * @var array
	 */
	var $chars = array();
	
	/**
	 * The vBulletin Object
	 * @var obj
	 */
	var $vbulletin = null;
	
	/**
	 * Initialize Data
	 *
	 * Take the vbulletin object, aggregate it to the object, and 
	 * set the stopwords and character replacements with new $options
	 * access
	 *
	 * @param obj &$vbulletin the vBulletin Object
	 * @return void
	 */
	function init(&$vbulletin)
	{
		$this->vbulletin =& $vbulletin;
		$this->set_stopwords($vbulletin->options['zointstags_stopwords']);
		$this->set_char_replacements($vbulletin->options['zointstags_char_replacement']);
	}
	
	
	/**
	 * Set the Stopwords
	 *
	 * Stopwords are words that should not appear in tags. They 
	 * are being passed as a string, and processed here into an
	 * array.
	 *
	 * @param string $stopwords The stopwords string from the vBulletin options
	 * @return void
	 */
	function set_stopwords($stopwords)
	{
		if (empty($stopwords))
		{
			return;
		}
		
		$words = preg_split("#[\n\r,]+#", $stopwords, -1, PREG_SPLIT_NO_EMPTY);
		if (count($words))
		{
			$words = array_map(array(&$this, 'clean'), $words);
			$words = array_filter($words);
			
			$this->stopwords = $words;
		}
		
	}
	
	/**
	 * Set the character replacements
	 *
	 * Replace certain characters with others
	 *
	 * @param string $stopwords The stopwords string from the vBulletin options
	 * @return void
	 */
	function set_char_replacements($char_replacements)
	{

		if (empty($char_replacements))
		{
			return;
		}
		
		$pairs = preg_split("[\r\n]", $char_replacements, -1, PREG_SPLIT_NO_EMPTY);
		if (count($pairs))
		{
			foreach ($pairs as $pair)
			{
				list ($find, $replace) = explode(' ', $pair, 2);
				if (!empty($find) AND !empty($replace))
				{
					$this->chars[$find] = $replace;
				}
			}
		}
	}
	
	/**
	 * Add a tag
	 * 
	 * Takes the tag, cleans it, checks it, and then adds it
	 *
	 * @param string $tag The raw user text input
	 * @return string The shiny new cleaned tag (or '' if nonexistent / entirely of stopwords)
	 */
	function add($tag)
	{
		if ($this->vbulletin->options['zointstags_maximum_tags'] AND count($this->tags) > $this->vbulletin->options['zointstags_maximum_tags'])
		{
			$this->error = 2;
			return '';
		}
	
		$tag = $this->clean($tag);
		if (empty($tag))
		{
			return '';
		}
		
		$tag = $this->checktag($tag);
		
		if (empty($tag))
		{
			return '';
		}
		
		if (!in_array($tag, $this->tags))
		{
			$this->tags[] = $tag;
		}
		
		return $tag;
	}
	
	/**
	 * Check the Tag
	 *
	 * Checks the tag to make sure it's within the allowed
	 * character/word length, and strips out any stopwords
	 * that are contained in it
	 *
	 * Sets $this->error if an error was encountered
	 *
	 * @param string $tag A tag
	 * @return string The tag minus any stopwords contained (may be empty)
	 */
	function checktag($tag)
	{
		if (!is_array($this->stopwords))
		{
			$this->stopwords = array();
		}
		$words = preg_split("#[\s]+#", $tag, -1, PREG_SPLIT_NO_EMPTY);
		
		if (count($this->stopwords))
		{
			$changed = false;
			foreach ($words as $k => $word)
			{
				if (in_array($word, $this->stopwords))
				{
					unset($words[$k]);
					$changed = true;
				}
			}
			
			$tag = implode(' ', $words);
		}
		
		$error = false;
		if (count($words) > 3)
		{
			# too many words per tag
			$error = true;
		}
		
		foreach ($words as $word)
		{
			if (vbstrlen($word) > 20)
			{
				# word in tag too long
				$error = true;
			}
		}
		
		if ($error)
		{
			$this->error = 1;
			if ($this->postings)
			{
				return '';
			}
		}
		
		return $tag;
	}
	
	/**
	 * Clean a string into a tag
	 * 
	 * Takes the parameter and then strips out any unwanted characters
	 * (such as punctuation)
	 *
	 * @param string $tag raw text
	 * @return string The cleaned tag
	 */
	function clean($tag)
	{
		foreach ($this->chars as $find => $replace)
		{
			$tag = str_replace($find, $replace, $tag);
		}
		
		$tag = preg_replace(array("#['\"´`]#", "#[^\w\s]#", "#\s+#"), array('', '', ' '), strtolower($tag));
		
		if (strlen($tag) <= 2)
		{
			return '';
		}
		
		return $tag;
	}
	
	/**
	 * Parse raw text containing multiple tags
	 *
	 * Takes the form input form the user and then converts it into separate tags
	 *
	 * @param string $tags raw form input
	 * @return string cleaned list of tags
	 */
	function parse($tags)
	{
		if (empty($tags))
		{
			return '';
		}
	
		$tags = str_replace('-', ' ', $tags);
	
		$tags = preg_split('#,#', strtolower($tags), -1, PREG_SPLIT_NO_EMPTY);
		$tags = array_unique(array_map('trim',$tags));
		
		if (!count($tags))
		{
			return '';
		}
		
		foreach ($tags as $tag)
		{
			$this->add($tag);
		}
		
		return implode(', ', $this->tags);
	}
	
	/**
	 * Save the added tags to the database
	 *
	 * Takes all the tags that have been added so far and saves it into
	 * the database for safekeeping, and also assigns it to the specific thread
	 *
	 * @param int $threadid The threadid these tags should be linked to
	 * @return void
	 */
	function save($threadid)
	{
		if ($this->error)
		{
			return;
		}
		$threadid = intval($threadid);
		$this->vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "zoints_tag WHERE threadid = " . $threadid);
		if (count($this->tags))
		{
			sort($this->tags);
		
			$bits = array();
			foreach ($this->tags as $tag)
			{
				$bits[] = "($threadid, '" . $this->vbulletin->db->escape_string($tag) . "')";
			}
			
			$this->vbulletin->db->query_write("
				INSERT INTO " . TABLE_PREFIX . "zoints_tag
					(threadid, tag)
				VALUES " . implode(',', $bits) . "
			");
			$this->vbulletin->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "zoints_tag_update
					(threadid, dateline)
				VALUES (
					$threadid,
					" . TIMENOW . "
			)");
		}
	}
	
	/**
	 * Load tags from database
	 * 
	 * Load all the tags relevant to a given thread and then return
	 * a comma separated list of them
	 *
	 * @param int $threadid The threadid whose tags should be gathered
	 * @param bool $implode true to return comma separated list
	 * @return string comma separated list of tags
	 */
	function load_tags($threadid, $implode = true)
	{
		$threadid = intval($threadid);
		$tags = array();
		$db_tags = $this->vbulletin->db->query_read("
			SELECT * FROM " . TABLE_PREFIX . "zoints_tag WHERE threadid = " . intval($threadid) . " ORDER BY tag
		");
		while ($tag = $this->vbulletin->db->fetch_array($db_tags))
		{
			$tags[] = $tag['tag'];
		}
		$this->tags = $tags;
		return ($implode ? implode(', ', $tags) : $tags);
	}
}

/*************************************************

Snoopy - the PHP net client
Author: Monte Ohrt <monte@ispi.net>
Copyright (c): 1999-2000 ispi, all rights reserved
Version: 1.01

 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

You may contact the author of Snoopy by e-mail at:
monte@ispi.net

Or, write to:
Monte Ohrt
CTO, ispi
237 S. 70th suite 220
Lincoln, NE 68510

The latest version of Snoopy can be obtained from:
http://snoopy.sourceforge.net/

*************************************************/

class ZointsTagsSnoopy
{
	/**** Public variables ****/
	
	/* user definable vars */

	var $host			=	"";		// host name we are connecting to
	var $port			=	80;					// port we are connecting to
	
	var $agent			=	"Snoopy v1.2.3 (Zoints Tags)";	// agent we masquerade as
	var	$rawheaders		=	array();

	// http accept types
	var $accept			=	"image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*";
	
	var $results		=	"";					// where the content is put
		
	var $error			=	"";					// error messages sent here
	var	$response_code	=	"";					// response code returned from server
	var	$headers		=	array();			// headers returned from server sent here
	var	$maxlength		=	500000;				// max return data length (body)
	var $read_timeout	=	10;
												// set to 0 to disallow timeouts
	var $timed_out		=	false;				// if a read operation timed out
	var	$status			=	0;					// http request status

	/**** Private variables ****/	
	
	var	$_maxlinelen	=	4096;				// max line length (headers)
	
	var $_httpversion	=	"HTTP/1.0";			// default http request version
	var $_submit_method	=	"POST";				// default submit method
	var $_submit_type	=	"application/x-www-form-urlencoded";	// default submit type
	var $_mime_boundary	=   "";					// MIME boundary for multipart/form-data submit type
	var $_redirectaddr	=	false;				// will be set if page fetched is a redirect
	var $_redirectdepth	=	0;					// increments on an http redirect
	
	var $_isproxy		=	false;				// set if using a proxy server
	var $_fp_timeout	=	5;					// timeout for socket connection



/*======================================================================*\
	Function:	submit
	Purpose:	submit an http form
	Input:		$URI	the location to post the data
				$formvars	the formvars to use.
					format: $formvars["var"] = "val";
	Output:		$this->results	the text output from the post
\*======================================================================*/

	function submit($URI, $formvars="")
	{
		unset($postdata);
		
		$postdata = $this->_prepare_post_body($formvars);
			
		$URI_PARTS = parse_url($URI);
		if (empty($URI_PARTS["query"]))
			$URI_PARTS["query"] = '';
		if (empty($URI_PARTS["path"]))
			$URI_PARTS["path"] = '';

		switch(strtolower($URI_PARTS["scheme"]))
		{
			case "http":
				$this->host = $URI_PARTS["host"];
				if(!empty($URI_PARTS["port"]))
					$this->port = $URI_PARTS["port"];
				if($this->_connect($fp))
				{
					$path = $URI_PARTS["path"].($URI_PARTS["query"] ? "?".$URI_PARTS["query"] : "");
					$this->_httprequest($path, $fp, $URI, $this->_submit_method, $this->_submit_type, $postdata);
					$this->_disconnect($fp);
				}
				else
				{
					return false;
				}
				return true;					
				break;

				
			default:
				// not a valid protocol
				$this->error = 'Invalid protocol "'.$URI_PARTS["scheme"].'"\n';
				return false;
				break;
		}		
		return true;
	}



/*======================================================================*\
	Private functions
\*======================================================================*/
	

/*======================================================================*\
	Function:	_httprequest
	Purpose:	go get the http data from the server
	Input:		$url		the url to fetch
				$fp			the current open file pointer
				$URI		the full URI
				$body		body contents to send if any (POST)
	Output:		
\*======================================================================*/
	
	function _httprequest($url,$fp,$URI,$http_method,$content_type="",$body="")
	{
		$cookie_headers = '';
			
		$URI_PARTS = parse_url($URI);
		if(empty($url))
			$url = "/";
		$headers = $http_method." ".$url." ".$this->_httpversion."\r\n";		

		if(!empty($this->host) && !isset($this->rawheaders['Host'])) {
			$headers .= "Host: ".$this->host;
			if(!empty($this->port))
				$headers .= ":".$this->port;
			$headers .= "\r\n";
		}
		
		if(!empty($this->rawheaders))
		{
			if(!is_array($this->rawheaders))
				$this->rawheaders = (array)$this->rawheaders;
			while(list($headerKey,$headerVal) = each($this->rawheaders))
				$headers .= $headerKey.": ".$headerVal."\r\n";
		}
		if(!empty($content_type)) {
			$headers .= "Content-type: $content_type";
			$headers .= "\r\n";
		}
		if(!empty($body))	
			$headers .= "Content-length: ".strlen($body)."\r\n";

		

		$headers .= "\r\n";
		
		// set the read timeout if needed
		if ($this->read_timeout > 0)
			socket_set_timeout($fp, $this->read_timeout);
		$this->timed_out = false;
		
		fwrite($fp,$headers.$body,strlen($headers.$body));
		
		$this->_redirectaddr = false;
		unset($this->headers);
						
		while($currentHeader = fgets($fp,$this->_maxlinelen))
		{
			if ($this->read_timeout > 0 && $this->_check_timeout($fp))
			{
				$this->status=-100;
				return false;
			}
				
			if($currentHeader == "\r\n")
				break;
						
			// if a header begins with Location: or URI:, set the redirect
			if(preg_match("/^(Location:|URI:)/i",$currentHeader))
			{
				// get URL portion of the redirect
				preg_match("/^(Location:|URI:)[ ]+(.*)/i",chop($currentHeader),$matches);
				// look for :// in the Location header to see if hostname is included
				if(!preg_match("|\:\/\/|",$matches[2]))
				{
					// no host in the path, so prepend
					$this->_redirectaddr = $URI_PARTS["scheme"]."://".$this->host.":".$this->port;
					// eliminate double slash
					if(!preg_match("|^/|",$matches[2]))
							$this->_redirectaddr .= "/".$matches[2];
					else
							$this->_redirectaddr .= $matches[2];
				}
				else
					$this->_redirectaddr = $matches[2];
			}
		
			if(preg_match("|^HTTP/|",$currentHeader))
			{
                if(preg_match("|^HTTP/[^\s]*\s(.*?)\s|",$currentHeader, $status))
				{
					$this->status= $status[1];
                }				
				$this->response_code = $currentHeader;
			}
				
			$this->headers[] = $currentHeader;
		}

		$results = '';
		do {
    		$_data = fread($fp, $this->maxlength);
    		if (strlen($_data) == 0) {
        		break;
    		}
    		$results .= $_data;
		} while(true);

		if ($this->read_timeout > 0 && $this->_check_timeout($fp))
		{
			$this->status=-100;
			return false;
		}
		
		$this->results = $results;
		
		return true;
	}



/*======================================================================*\
	Function:	_check_timeout
	Purpose:	checks whether timeout has occurred
	Input:		$fp	file pointer
\*======================================================================*/

	function _check_timeout($fp)
	{
		if ($this->read_timeout > 0) {
			$fp_status = socket_get_status($fp);
			if ($fp_status["timed_out"]) {
				$this->timed_out = true;
				return true;
			}
		}
		return false;
	}

/*======================================================================*\
	Function:	_connect
	Purpose:	make a socket connection
	Input:		$fp	file pointer
\*======================================================================*/
	
	function _connect(&$fp)
	{
		$host = $this->host;
		$port = $this->port;
	
		$this->status = 0;
		
		if($fp = fsockopen(
					$host,
					$port,
					$errno,
					$errstr,
					$this->_fp_timeout
					))
		{
			// socket connection succeeded

			return true;
		}
		else
		{
			// socket connection failed
			$this->status = $errno;
			switch($errno)
			{
				case -3:
					$this->error="socket creation failed (-3)";
				case -4:
					$this->error="dns lookup failure (-4)";
				case -5:
					$this->error="connection refused or timed out (-5)";
				default:
					$this->error="connection failed (".$errno.")";
			}
			return false;
		}
	}
/*======================================================================*\
	Function:	_disconnect
	Purpose:	disconnect a socket connection
	Input:		$fp	file pointer
\*======================================================================*/
	
	function _disconnect($fp)
	{
		return(fclose($fp));
	}

	
/*======================================================================*\
	Function:	_prepare_post_body
	Purpose:	Prepare post body according to encoding type
	Input:		$formvars  - form variables
	Output:		post body
\*======================================================================*/
	
	function _prepare_post_body($formvars)
	{
		settype($formvars, "array");
		$postdata = '';

		if (count($formvars) == 0 )
			return;
		
		reset($formvars);
		while(list($key,$val) = each($formvars)) {
			if (is_array($val) || is_object($val)) {
				while (list($cur_key, $cur_val) = each($val)) {
					$postdata .= urlencode($key)."[]=".urlencode($cur_val)."&";
				}
			} else
				$postdata .= urlencode($key)."=".urlencode($val)."&";
		}

		return $postdata;
	}
}
?>