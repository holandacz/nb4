<?php

  /*  
	******************************************************************************
	vB Google Search Cloud v1.0.5
	by NeutralizeR - msxlabs.org, for vBulletin Integration, in May 2008
	with the permission of Dan Fletcher to share @ vbulletin.org
	******************************************************************************  	  
  	Simple Search Cloud
	Written by Dan Fletcher, for buymyscripts.net, in April 2008
	This script does not have resell rights!
	You have the right to use it on your website (personal or commercial), but NOT
	to sell it, or give it away free
	******************************************************************************
  */
  
require_once('./global.php');

class SearchCloud
{
	// Varables you can set (if you want), but the defaults are fine.

	var $m_minFont     = 75;   // The minimum font size. No keywords will be smaller than this. This is a %
	var $m_maxFont     = 150;  // The maximum font size. No keywords will be bigger than this. This is a %
	var $m_averageFont = 100;   // The font size of an average searhced for keyword. This is a %
	var $m_maxEntries  = 50;   // Maximum number of entries displayed in the cloud
	var $m_showCount   = 0;    // Whether counts are shown along with each entry

	var $m_blackList   = array();   // list of disallowed words. An example is commented out below...
	//var $m_blackList   = array("sex","boob","cheatz");   


	function SearchCloud()
	{
	}

	function handlePageHit()
	{
		$domain = $_SERVER['HTTP_HOST'];
	
		$ref = $_SERVER['HTTP_REFERER'];

		$datas = parse_url($ref);
	
		$queryValues = $datas['query'];
		$hostname = $datas['host'];
	
		if (strpos($hostname,".google."))
		{
			parse_str($queryValues,$values);
		
			$search=$values['q'];
			$pageURL = $this->_getURL();

			if (strlen($search)>0)
			{
				$this->_logHit(strtolower($search),$pageURL);		
			}
		}
	}


	function _getURL()
	{
		$s = empty($_SERVER["HTTPS"]) ? ''
			: ($_SERVER["HTTPS"] == "on") ? "s"
			: "";
		$protocol = $this->_strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
			: (":".$_SERVER["SERVER_PORT"]);
		return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
	}
	function _strleft($s1, $s2)
	{
		return substr($s1, 0, strpos($s1, $s2));
	}

	function getMysqlReadyString($a)
	{
		
		if(get_magic_quotes_gpc())
		{
			$tmp = stripslashes($a);
		}
		else
		{
			$tmp = $a;
		}
		return mysql_real_escape_string($tmp);
	}


	function _validKeyword($k)
	{
		if (!(strpos($k,"site:") === FALSE))
		{
			return FALSE;
		}

		foreach ($this->m_blackList as $w)
		{
			if (!(strpos($k,$w) === FALSE))
			{
				return FALSE;
			}
		}


		return TRUE;
	}

	function _logHit($search,$pageURL)
	{
		global $vbulletin;
		$pageURL = $this->getMysqlReadyString($pageURL);
		$search = $this->getMysqlReadyString(trim($search));

		if (!$this->_validKeyword($search))
		{
			return;
		}


		$sql = $vbulletin->db->query_read("SELECT hits FROM " . TABLE_PREFIX . "google_searches where kw='$search'");
		$rec = $sql or die($vbulletin->db->error());

		if ($vbulletin->db->num_rows($rec)==0)
		{
			$sql = $vbulletin->db->query_write("INSERT into " . TABLE_PREFIX . "google_searches VALUES ('$search','$pageURL',1)");
			$rec = $sql or die($vbulletin->db->error());
		}
		else
		{
			$datas = $vbulletin->db->fetch_row($rec);
			$count = $datas[0];
			$count++;
			$sql = $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "google_searches set url='$pageURL',hits=$count where kw='$search'");
			$rec = $sql or die($vbulletin->db->error());
		}

	}


	function showCloud()
	{
		global $vbulletin;
		$maxEntries = $this->m_maxEntries;

		
		$sql = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "google_searches order by hits DESC limit $maxEntries");
		$rec = $sql or die($vbulletin->db->error());

		$numRows = $vbulletin->db->num_rows($rec);
		if ($numRows == 0)
		{
		    return;
		}

		while ($datas = $vbulletin->db->fetch_array($rec))
		{
			$d[] = $datas;
		}


		$minFont=$this->m_minFont;
		$maxFont=$this->m_maxFont;
		$averageFont=$this->m_averageFont;

		usort($d,_cmpKw);


		$count=0;
		foreach ($d as $datas)
		{
		    $sum += $datas['hits'];
		    $count++;
		}

		if ($count == 0)
		{
		    $avg = 1;
		}
		else
		{
		    $avg = $sum / $count;
		}


		foreach ($d as $datas)
		{
			if (!$this->_validKeyword($datas['kw']))
			{
				continue;
			}

			$a = $datas['hits'] / $avg;

			$fontSize = ceil($a * $averageFont);

			if ($fontSize < $minFont)
		    {
				$fontSize = $minFont;
		    }
			if ($fontSize > $maxFont)
		    {
				$fontSize = $maxFont;
		    }
		  
			if ($this->m_showCount)
			{
				$countText = "<span style='font-size:$minFont%'>$datas[hits]</span>";
			}
			else
			{
				$countText = "";
			}
			// Google likes bolded keywords, so we bold the most important ones...
			if ($a > 0.8)
			{
				echo " <b><a href='$datas[url]' style='font-size:$fontSize%'>$datas[kw]</a></b> $countText \n";
			}
			else
			{
			    echo " <a href='$datas[url]' style='font-size:$fontSize%'>$datas[kw]</a> $countText \n";
			}
		}		
	}
};

function _cmpKw($a,$b)
{
	return strcmp($a['kw'],$b['kw']);
}

?>
