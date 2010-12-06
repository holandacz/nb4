<?php
error_reporting(E_ALL & ~E_NOTICE);
define('ADS_PATH', 'ads/');
define('ADS_LAYOUTS', 'ads/layouts/');
define('ADS_PATH_THUMBS', 'ads/70/');
define('ADS_ADVERTISE', '<a href="http://wiki.noblood.org/Advertising">Become a Sponsor</a>');
class ads {
	public $layouts			= array(
		array('name' => 'full', 'filename' => 'ads_full.html',
			'style' => '
				#ads {text-align:center;padding: 0px; width:162px;}
				#ads h3 {font-size: 11px;font-weight:normal;}
				#ads img {border:none;padding: 1px;}',
			'img_path' => ADS_PATH, 'img_type' => 'png'),

		array('name' => 'list', 'filename' => 'ads_list.html',
			'style' => '
				#ads {text-align:left;padding: 0px;padding-top: 10px;clear:left;}
				#ads.sp div {padding: 1px;}
				#ads h3 {font-size: 12px;font-weight:normal;}
				#ads img {border:none;padding: 1px;float:left;padding-right: 10px;}',
			'img_path' => ADS_PATH, 'img_type' => 'png'),

		array('name' => 'scaled', 'filename' => 'ads_scaled.html',
			'style' => '
				#ads {text-align:center;padding: 0px; width:114px;}
				#ads h3 {font-size: 11px;font-weight:normal;}
				#ads img {border:none;padding: 1px;}',
			'img_path' => ADS_PATH_THUMBS, 'img_type' => 'jpg'),

		array('name' => 'wiki', 'filename' => 'ads_wiki.html',
			'style' => '
				#ads {text-align:center;padding: 0px; width:158px;}
				#ads h3 {font-size: 11px;font-weight:normal;}
				#ads img {border:none;padding: 1px;}',
			'img_path' => ADS_PATH_THUMBS, 'img_type' => 'jpg'),
	);

	public function __construct(){
		foreach($this->layouts as $layout){
			if (!file_exists(ADS_LAYOUTS . $layout['filename'])){
				$this->build_layouts();
				break;
			}
		}
		foreach($this->layouts as $layout){
			$name			= $layout['name'];
			$this->$name	= file_get_contents(ADS_LAYOUTS . $layout['filename']);
		}
/*		if (!is_dir(ADS_PATH)){
			@umask(0);
			if (!@mkdir(ADS_PATH, 0777)){
				print_stop_message('Failed to make directory: ' . ADS_PATH);
			}
		}
		@chmod(ADS_PATH, 0777);
		if (!is_dir(ADS_PATH_THUMBS)){
			@umask(0);
			if (!@mkdir(ADS_PATH_THUMBS, 0777)){
				print_stop_message('Failed to make directory: ' . ADS_PATH_THUMBS);
			}
		}
		@chmod(ADS_PATH_THUMBS, 0777);
*/
	}

	function insert_into_forumdisplay($layout = 'full'){
		global $vbulletin;
		$search_text = '<!-- controls above thread list -->';
		$replace_text = '<!-- table to support left column nav -->
					  <table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
						<tr valign="top"><td>'
						. $this->$layout
						. (isset($vbulletin->options['ain_adsense_code_skyscraper'])
							? $vbulletin->options['ain_adsense_code_skyscraper'] : '')
						. '</td><td width="100%">';
		$replace_text	= addslashes($replace_text);
		$vbulletin->templatecache['FORUMDISPLAY'] = str_replace($search_text, $search_text.$replace_text, $vbulletin->templatecache['FORUMDISPLAY']);

		$search_text = '<!-- / controls below thread list -->';
		$replace_text = '</td></tr></table><!-- / table to support left column nav -->';
		$vbulletin->templatecache['FORUMDISPLAY'] = str_replace($search_text, $search_text.$replace_text, $vbulletin->templatecache['FORUMDISPLAY']);
	}


	function insert_into_forumhome($layout = 'full'){
		global $vbulletin;
		$search_text = '<!-- main -->';
		$replace_text = '<!-- table to support left column nav -->
					  <table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
						<tr valign="top"><td>'
						. $this->$layout
						. (isset($vbulletin->options['ain_adsense_code_skyscraper'])
							? $vbulletin->options['ain_adsense_code_skyscraper'] : '')
						. '</td><td width="100%">';
		$replace_text	= addslashes($replace_text);
		$vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.$replace_text, $vbulletin->templatecache['FORUMHOME']);

		$search_text = '<!-- /main -->';
		$replace_text = '</td></tr></table><!-- / table to support left column nav -->';
		$vbulletin->templatecache['FORUMHOME'] = str_replace($search_text, $search_text.$replace_text, $vbulletin->templatecache['FORUMHOME']);
	}

	function build_layouts()
	{
        exit;

		global $db;
		global $vbulletin;
        $ini    = parse_ini_file('../m/config/config.ini', true);
		//$homeurl	= $vbulletin->options['homeurl'];
		$homeurl	= 'http://www.noblood.org';
		if (!$ads_query = $db->query_read_slave("SELECT * FROM ads_active")){
			print_stop_message('Query of ads_active failed.');
		}

		// load active ads
		$ads		= array();
		while ($ad = $db->fetch_array($ads_query))
			$ads[] 		= $ad;

		foreach($this->layouts as $layout){
			$layout_ads	= array();
			foreach($ads as $ad){
				$img_file	= $layout['img_path'] . $ad['ads_id'] . '.' . $layout['img_type'];

				list($width, $height, $type, $attr) = getimagesize($img_file);

				$url			= $ad['url'];

				if ($layout['name'] == 'list'){
					$title		= '<b>' . $ad['title'] . '</b><br />';
					$title		.= ($ad['ad_copy'] ? "\n" . $ad['ad_copy'] : '<br /><br />');

					$layout_ad			= '<div class="sp">';
					$layout_ad			.= "<a href=\"$url\">";
					$layout_ad			.= "<img src=\"$homeurl/$img_file\" width=$width height=$height />" ;
					$layout_ad			.= "</a>";
					$layout_ad			.= '<p>';
					$t = stripslashes($title);
					if ($pos	= strpos($t, '.'))
						$t = substr($t, 0, $pos) . '...';

					$layout_ad 		.= $t;
					$layout_ad 		.= '</p>';

					$layout_ad			.= "</div>";

				}else{
					$title		= strip_tags($ad['title'] . ($ad['ad_copy'] ? "\n" . $ad['ad_copy'] : ''));
					$layout_ad			= "<a href=\"$url\">";
					$layout_ad			.= "<img src=\"$homeurl/$img_file\" width=$width height=$height title=\"$title\" />" ;
					$layout_ad			.= "</a>";
				}

				$layout_ads[]	= $layout_ad;
			}

			$layout_html		= '<style><!--' . $layout['style'] . '--></style>'
									. '<div id="ads">'
									. implode('<br />', $layout_ads)
									. '<h3>' . ADS_ADVERTISE . '</h3>'
									. '</div>';

			// file_put_contents(ADS_LAYOUTS . $layout['filename'], $layout_html);
		}
	}
}