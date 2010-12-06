<?php

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
        //global $dir, $site, $config;



        if (stripos(getcwd(),'_lib')){
            include('../includes/config.php');
        }else{
            include('includes/config.php');
        }


        $this->dir      = $dir;
        $this->site     = $site;
        $this->config   = $config;
        $this->build_hospital_sponsors();
		foreach($this->layouts as $layout){
			if (!file_exists($dir['ads_layouts_path'] . $layout['filename'])){
				$this->build_layouts();
				break;
			}
		}
		foreach($this->layouts as $layout){
			$name			= $layout['name'];
			$this->$name	= file_get_contents($dir['ads_layouts_path'] . $layout['filename']);
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

    function build_hospital_sponsors()
    {
        // I know this is redundant
        $homeurl    = 'http://www.noblood.org';

        $conn = mysql_connect(
            'xchg.com',
            'nb',
            'n.2009B'
        );
/*	
        $conn = mysql_connect(
            $this->config['MasterServer']['servername'],
            $this->config['MasterServer']['username'],
            $this->config['MasterServer']['password']
        );
*/
        // $database = $this->config['Database']['xchg_dbname'] = 'x';
	$database = 'x';
        mysql_select_db($database, $conn) or die ("Database not found.");
        $sql = "
SELECT
  `c`.`id`                     AS `id`,
  `c`.`companyname`            AS `name`,
  `c`.`companyabbrev`          AS `companyabbrev`,
  `c`.`department`             AS `department`,
  `c`.`pri_address`            AS `street`,
  `c`.`pri_address_city`       AS `city`,
  `c`.`pri_address_state`      AS `state`,
  `c`.`pri_address_zip`        AS `zip`,
  `c`.`pri_address_country`    AS `country`,
  `countries`.`printable_name` AS `country_name`,
  `c`.`tel_work`               AS `tel1`,
  `c`.`tel_other`              AS `tel2`,
  `c`.`pri_address_lat`        AS `lat`,
  `c`.`pri_address_long`       AS `lng`,
  `c`.`pri_address_mapurl`     AS `mapurl`,
  `c`.`webpage`                AS `url`,
  FIND_IN_SET('sponsor',`c`.`nb_tags`) AS `sponsor`,
  FIND_IN_SET('premium',`c`.`nb_tags`) AS `premium`
FROM (`contacts` `c`
   LEFT JOIN `countries`
     ON ((`countries`.`iso` = `c`.`pri_address_country`)))
WHERE (FIND_IN_SET('hospital',`c`.`nb_tags`)
       AND FIND_IN_SET('publish',`c`.`nb_tags`)
       AND FIND_IN_SET('premium',`c`.`nb_tags`))
ORDER BY `c`.`pri_address_city`,`c`.`pri_address_state`,`c`.`companyname`
        ";

        $rows = mysql_query($sql) or die("Failed hospital_sponsors Query");


        $hospital_sponsors_html = '
<!-- hospital_sponsors -->
<div id="hospital_sponsors_r">
  <div class="header"> <a href="http://wiki.noblood.org/Advertising"> <img class="question_mark" src="styles/main/imgs/modules/question_mark.gif" alt="Advertising on noblood." /> </a> </div>

<div class="box">
<ul>';

$citystate = '';
while ($sponsor = mysql_fetch_assoc($rows)){

    if ($sponsor['city'] != $citystate){
        $hospital_sponsors_html .= $citystate == '' ? '' : '</li>';
        $hospital_sponsors_html .= '<li class="smallfont">';
        $hospital_sponsors_html .= '<b>' . $sponsor['city'] . '</b> ';
        $citystate = $sponsor['city'];
    }else{
        $hospital_sponsors_html .= ', ';
    }

    $hospital_sponsors_html .= '<a href="' . $sponsor['url'] .
    '" target = "_blank" rel="nofollow" title="Please visit ' . $sponsor['name'] . ' located in ' .
    $sponsor['city'] . ' ' . $sponsor['state'] .'">';
    $hospital_sponsors_html .= $sponsor['companyabbrev'] . '</a>';

}
        $hospital_sponsors_html  .= '</li></ul>
</div>
  <div class="footer"> <a href="hospitals.php"> <img class="question_mark" src="styles/main/imgs/modules/plus.gif" alt="Complete Bloodless Medicine and Surgery Hospitals Directory" /> </a> </div>
</div>
<!-- / hospital_sponsors -->
';

        file_put_contents($dir['ads_layouts_path'] . 'ads_hospital_sponsors.html', $hospital_sponsors_html);





    }



	function build_layouts()
	{
		$homeurl	= $this->dir['home_url'];

        $conn = mysql_connect(
            'xchg.com',
            'nb',
            'n.2009B'
        );
/*
        $conn = mysql_connect(
            $this->config['MasterServer']['servername'],
            $this->config['MasterServer']['username'],
            $this->config['MasterServer']['password']
        );
        $database = $this->config['Database']['xchg_dbname'] = 'x';
*/
	$database = 'x';
        mysql_select_db($database, $conn) or die ("Database not found.");

        $sql = "
SELECT
  `a`.`id`                  AS `ads_id`,
  `a`.`contact_id`          AS `contact_id`,
  `a`.`publish`             AS `publish`,
  `a`.`width`               AS `width`,
  `a`.`height`              AS `height`,
  `a`.`beg_date`            AS `beg_date`,
  `a`.`end_date`            AS `end_date`,
  `a`.`title`               AS `title`,
  `a`.`ad_copy`             AS `ad_copy`,
  `a`.`url`                 AS `url`,
  `c`.`companyname`         AS `companyname`,
  `c`.`companyname`         AS `name`,
  FIND_IN_SET('hospital',`c`.`nb_tags`) AS `is_hospital`,
  0                         AS `is_general`,
  FIND_IN_SET('organization',`c`.`nb_tags`) AS `is_org`,
  FIND_IN_SET('other_company',`c`.`nb_tags`) AS `is_healthcare`,
  FIND_IN_SET('published',`c`.`nb_tags`) AS `is_published`,
  FIND_IN_SET('sponsor',`c`.`nb_tags`) AS `is_sponsor`,
  FIND_IN_SET('premium',`c`.`nb_tags`) AS `is_premium`,
  `c`.`companyabbrev`       AS `companyabbrev`,
  `c`.`department`          AS `department`,
  `c`.`pri_address`         AS `business_street`,
  `c`.`pri_address_city`    AS `business_city`,
  `c`.`pri_address_city`    AS `city`,
  `c`.`pri_address_state`   AS `business_state`,
  `c`.`pri_address_state`   AS `state`,
  `c`.`pri_address_country` AS `business_country`,
  `c`.`pri_address_zip`     AS `business_postalcode`,
  `c`.`tel_work`            AS `business_telephone`,
  `c`.`tel_other`           AS `business_telephone2`,
  `c`.`notes`               AS `body`,
  `c`.`pri_address_mapurl`  AS `mapURL`,
  `c`.`pri_address_lat`     AS `lat`,
  `c`.`pri_address_long`    AS `lng`
FROM `nb_ads` `a`
   LEFT JOIN `contacts` `c`
     ON ((`a`.`id` = `c`.`id`))
WHERE `a`.`publish`
ORDER BY (`a`.`width` * `a`.`height`) DESC, `a`.`beg_date`
        ";
//echo $sql;
        $rows = mysql_query($sql);

		// load active ads
		$ads		= array();
        while ($ad = mysql_fetch_assoc($rows)){
			$ads[] 		= $ad;
        }
		foreach($this->layouts as $layout){
			$layout_ads	= array();
			foreach($ads as $ad){
                $img_file    = $this->dir['ads_path'] . $ad['ads_id'] . '.' . $layout['img_type'];
				$img_url	= $this->dir['ads_url'] . $ad['ads_id'] . '.' . $layout['img_type'];

				list($width, $height, $type, $attr) = getimagesize($img_file);

				$url			= $ad['url'];

				if ($layout['name'] == 'list'){
					$title		= '<b>' . $ad['title'] . '</b><br />';
					$title		.= ($ad['ad_copy'] ? $ad['ad_copy'] : '<br /><br />');

					$layout_ad			= '<div class="sp">';
					$layout_ad			.= "<a href=\"$url\">";
					$layout_ad			.= "<img src=\"$img_file\" width=$width height=$height />" ;
					$layout_ad			.= "</a>";
					$layout_ad			.= '<p>';
					$t = stripslashes($title);
					if ($pos	= strpos($t, '.'))
						$t = substr($t, 0, $pos) . '...';

					$layout_ad 		.= $t;
					$layout_ad 		.= '</p>';

					$layout_ad			.= "</div>";

				}else{
					$title		= strip_tags($ad['title'] . ($ad['ad_copy'] ? " - " . $ad['ad_copy'] : ''));
					$layout_ad			= "<a href=\"$url\">";
					$layout_ad			.= "<img src=\"$img_url\" width=$width height=$height title=\"$title\" />" ;
					$layout_ad			.= "</a>";
				}

				$layout_ads[]	= $layout_ad;
			}

			$layout_html		= '<style><!--' . $layout['style'] . '--></style>'
									. '<div id="ads">'
									. implode('<br />', $layout_ads)
									. '<h3>' . ADS_ADVERTISE . '</h3>'
									. '</div>';

			file_put_contents(ADS_LAYOUTS . $layout['filename'], $layout_html);
		}
	}
}
$ad = new ads();
