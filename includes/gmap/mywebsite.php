<?php defined('SYSPATH') OR die('No direct access allowed.');
class Mywebsite_Controller extends Template_Controller {
	public function __construct()
	{
		parent::__construct();



		//$this->session = Session::instance();
		//$this->auth = new Auth;
		// Check to see if the request is a HXR call
		if (request::is_ajax())
		{
			// Send the 403 header
			header('HTTP/1.1 403 Forbidden');
			//$t = new View('blank');
			return;
		}
/*
		if (Kohana::config_load('cw', true))
			$this->cfg  = Kohana::config('cw');
*/
/*		if (IN_PRODUCTION === FALSE)
		{
			$this->profiler = new Profiler;
		}*/
		$t	=& $this->template;
		$t->header         = new View('components/header');
		$t->footer         = new View('components/footer');

		//$this->db = Database::instance();
	}
	function load_errors($form, $getErrorStack = null){
		$errors	= array();
		foreach(array_keys($form) as $key)
			$errors[$key]	= '';

		if ($getErrorStack)
			foreach($getErrorStack as $field => $msgs)
				$errors[$field]			= implode('<br/>', $msgs) . '<br />';

		return $errors;
	}

} // End Template_Controller