<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Richard Heyes <richard@phpguru.org>                         |
// +----------------------------------------------------------------------+

class Mail_mimeDecode {
	var $_input;
	var $_header;
	var $_body;
	var $_error;
	var $_include_bodies;
	var $_decode_bodies;
	var $_decode_headers;
	var $_crlf;
	
	function Mail_mimeDecode($input = '', $crlf = "\r\n") {
		$this->_crlf = $crlf;
		list($header, $body) = $this->_splitBodyHeader($input);
		
		$this->_input			= $input;
		$this->_header			= $header;
		$this->_body			= $body;
		$this->_decode_bodies	= false;
		$this->_include_bodies	= true;
	}
	
	function decode($params = null) {		
		if (!isset($this) and isset($params['input'])) {
			if (isset($params['crlf'])) {
				$obj = new Mail_mimeDecode($params['input'], $params['crlf']);
			} else {
				$obj = new Mail_mimeDecode($params['input']);
			}
			$structure = $obj->decode($params);
		} elseif (!isset($this)) {
			return $this->raiseError('Called statically and no input given');
		} else {
			$this->_include_bodies	= isset($params['include_bodies'])	? $params['include_bodies']	: false;
			$this->_decode_bodies	= isset($params['decode_bodies'])	? $params['decode_bodies']	: false;
			$this->_decode_headers	= isset($params['decode_headers'])	? $params['decode_headers']	: false;
			$structure = $this->_decode($this->_header, $this->_body);
			if($structure === false) {
				$structure = $this->raiseError($this->_error);
			}
		}
		return $structure;
	}

	function _decode($headers, $body, $default_ctype = 'text/plain') {
		$return = new stdClass;
		$headers = $this->_parseHeaders($headers);
		
		foreach ($headers as $value) {
			if (isset($return->headers[strtolower($value['name'])]) and !is_array($return->headers[strtolower($value['name'])])) {
				$return->headers[strtolower($value['name'])]	= array($return->headers[strtolower($value['name'])]);
				$return->headers[strtolower($value['name'])][]	= $value['value'];
			} elseif (isset($return->headers[strtolower($value['name'])])) {
				$return->headers[strtolower($value['name'])][]	= $value['value'];
			} else {
				$return->headers[strtolower($value['name'])]	= $value['value'];
			}
		}

		reset($headers);
		while (list($key, $value) = each($headers)) {
			$headers[$key]['name'] = strtolower($headers[$key]['name']);
			switch ($headers[$key]['name']) {
				case 'content-type':
					$content_type = $this->_parseHeaderValue($headers[$key]['value']);
					if (preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]+)/i', $content_type['value'], $regs)) {
						$return->ctype_primary		= $regs[1];
						$return->ctype_secondary	= $regs[2];
					}
					if (isset($content_type['other'])) {
						while (list($p_name, $p_value) = each($content_type['other'])) {
							$return->ctype_parameters[$p_name] = $p_value;
						}
					}
					break;

				case 'content-disposition';
					$content_disposition	= $this->_parseHeaderValue($headers[$key]['value']);
					$return->disposition	= $content_disposition['value'];
					if (isset($content_disposition['other'])) {
						while (list($p_name, $p_value) = each($content_disposition['other'])) {
							$return->d_parameters[$p_name] = $p_value;
						}
					}
					break;

				case 'content-transfer-encoding':
					$content_transfer_encoding = $this->_parseHeaderValue($headers[$key]['value']);
					break;
			}
		}
		
		if (isset($content_type)) {
			switch (strtolower($content_type['value'])) {
				case 'text/plain':
					$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
					$this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
					break;

				case 'text/html':
					$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
					$this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
					break;

				case 'multipart/digest':
				case 'multipart/alternative':
				case 'multipart/related':
				case 'multipart/mixed':
					if(!isset($content_type['other']['boundary'])){
						$this->_error = 'No boundary found for ' . $content_type['value'] . ' part';
						return false;
					}

					$default_ctype = (strtolower($content_type['value']) === 'multipart/digest') ? 'message/rfc822' : 'text/plain';

					$parts = $this->_boundarySplit($body, $content_type['other']['boundary']);
					for ($i = 0; $i < count($parts); $i++) {
						list($part_header, $part_body) = $this->_splitBodyHeader($parts[$i]);
						$part = $this->_decode($part_header, $part_body, $default_ctype);
						if($part === false)
						$part = $this->raiseError($this->_error);
						$return->parts[] = $part;
					}
					break;

				case 'message/rfc822':
					$obj = new Mail_mimeDecode($body, $this->_crlf);
					$return->parts[] = $obj->decode(array('include_bodies' => $this->_include_bodies));
					unset($obj);
					break;

				default:
					$this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $content_transfer_encoding['value']) : $body) : null;
					break;
			}
		} else {
			$ctype = explode('/', $default_ctype);
			$return->ctype_primary		= $ctype[0];
			$return->ctype_secondary	= $ctype[1];
			$this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body) : $body) : null;
		}

		return $return;
	}
	
	function _splitBodyHeader($input) {
		$pos = strpos($input, $this->_crlf . $this->_crlf);
		if ($pos === false) {
			$this->_error = 'Could not split header and body';
			return false;
		}
		$header	= substr($input, 0, $pos);
		$body	= substr($input, $pos+(2*strlen($this->_crlf)));
		return array($header, $body);
	}

	function _parseHeaders($input) {
		if ($input !== '') {
			$input		= preg_replace('/' . $this->_crlf . "(\t| )/", ' ', $input);
			$headers	= explode($this->_crlf, trim($input));
			
			foreach ($headers as $value) {
				$hdr_name	= substr($value, 0, $pos = strpos($value, ':'));
				$hdr_value	= substr($value, $pos+1);
				$return[]	= array(
				'name'  => $hdr_name,
				'value' => $this->_decode_headers ? $this->_decodeHeader($hdr_value) : $hdr_value
				);
			}
		} else {
			$return = array();
		}
		return $return;
	}
	
	function _parseHeaderValue($input) {
		if (($pos = strpos($input, ';')) !== false) {
			$return['value'] = trim(substr($input, 0, $pos));
			$input = trim(substr($input, $pos+1));
			
			if (strlen($input) > 0) {
				preg_match_all('/(([[:alnum:]]+)="?([^"]*)"?\s?;?)+/i', $input, $matches);
				
				for ($i = 0; $i < count($matches[2]); $i++) {
					$return['other'][strtolower($matches[2][$i])] = $matches[3][$i];
				}
			}
		} else {
			$return['value'] = trim($input);
		}
		return $return;
	}
	
	function _boundarySplit($input, $boundary) {
		$tmp = explode('--'.$boundary, $input);
		for ($i=1; $i<count($tmp)-1; $i++) {
			$parts[] = $tmp[$i];
		}
		return $parts;
	}
	
	function _decodeHeader($input) {
		$input = preg_replace('/(=\?[^?]+\?(Q|B)\?[^?]*\?=)( |' . "\t|" . $this->_crlf . ')+=\?/', '\1=?', $input);
		
		while (preg_match('/(=\?([^?]+)\?(Q|B)\?([^?]*)\?=)/', $input, $matches)) {
			$encoded	= $matches[1];
			$charset	= $matches[2];
			$encoding	= $matches[3];
			$text		= $matches[4];
			
			switch ($encoding) {
				case 'B':
					$text = base64_decode($text);
					break;

				case 'Q':
					$text = str_replace('_', ' ', $text);
					preg_match_all('/=([A-F0-9]{2})/', $text, $matches);
					foreach($matches[1] as $value) {
						$text = str_replace('='.$value, chr(hexdec($value)), $text);
					}
					break;
			}

			$input = str_replace($encoded, $text, $input);
		}
		
		return $input;
	}

	function _decodeBody($input, $encoding = '7bit') {
		switch ($encoding) {
			case '7bit':
				return $input;
				break;
			
			case 'quoted-printable':
				return $this->_quotedPrintableDecode($input);
				break;
			
			case 'base64':
				return base64_decode($input);
				break;
			
			default:
				return $input;	
		}
	}

	function _quotedPrintableDecode($input) {
		$input = preg_replace("/=\r?\n/", '', $input);
		if (preg_match_all('/=[A-Z0-9]{2}/', $input, $matches)) {
			$matches = array_unique($matches[0]);
			foreach ($matches as $value) {
				$input = str_replace($value, chr(hexdec(substr($value,1))), $input);
			}
		}
		return $input;
	}
}

function parse_output (&$obj, &$parts) {
	if (!empty($obj->parts)) {
		for($i=0; $i<count($obj->parts); $i++)
		parse_output($obj->parts[$i], $parts);
	} else {
		$ctype = $obj->ctype_primary.'/'.$obj->ctype_secondary;
		$ctype = strtolower($ctype);
		switch ($ctype){
			case 'text/plain':
				if (!empty($obj->disposition) AND $obj->disposition == 'attachment') {
					$parts['attachments'][] = array(
													'data' => $obj->body,
													'filename' => $obj->d_parameters['filename'],
													'filename2' => $obj->ctype_parameters['name'],
													'type' => $obj->ctype_primary,
													'encoding' => $obj->headers['content-transfer-encoding']
													);
				} else {
					$parts['text'][] = $obj->body;
				}
				break;

			case 'text/html':
				if (!empty($obj->disposition) AND $obj->disposition == 'attachment') {
					$parts['attachments'][] = array(
													'data' => $obj->body,
													'filename' => $obj->d_parameters['filename'],
													'filename2' => $obj->ctype_parameters['name'],
													'type' => $obj->ctype_primary,
													'encoding' => $obj->headers['content-transfer-encoding']
													);
				} else {
					$parts['html'][] = $obj->body;
				}
				break;

			default:
				$parts['attachments'][] = array(
												'data' => $obj->body,
												'filename' => $obj->d_parameters['filename'],
												'filename2' => $obj->ctype_parameters['name'],
												'type' => $obj->ctype_primary,
												'headers' => $obj->headers
												);

		}
	}
	$parts['headers'] = $obj->headers;
}

?>
