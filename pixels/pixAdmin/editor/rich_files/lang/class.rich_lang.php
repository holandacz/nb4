<?php
//language class
class rich_lang{
	var $lang;					//current language
	var $default_lang = 'en';	//default language
	var $lang_data;				//text data in the current language

	//constructor
	function rich_lang($lang='en'){
		$this->load_lang($lang);
	}

	//load new language
	function load_lang($lang='en'){
		$path_prefix = dirname(__FILE__).'/';

		$this->lang = $lang;

		//get language
		@include($path_prefix.'rich_lang_'.$this->lang.'.inc.php');

		//the language not found
		if(!isset($rich_lang)){
			//get default language
			@include($path_prefix.'rich_lang_'.$this->default_lang.'.inc.php');
		}

		$this->lang_data = $rich_lang; //store language data
  
	}

	//get language item (string or array of strings)
	function item($name){
		return @$this->lang_data[$name];
	}
}
?>