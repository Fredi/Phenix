<?php
error_reporting(E_ALL | E_STRICT);

spl_autoload_register(array('Phoenix', 'autoload'));

define("PHOENIX_PATH", ROOT.DS."lib".DS."Phoenix");

/**
 * The core of Phoenix Framework
 */
class Phoenix
{
	static private $instance;

	private $request;
	private $response;

	protected $env;

	public static function getInstance()
	{
		if (!isset($instance))
			self::$instance = new self();

		return self::$instance;
	}

	public static function autoload($class)
	{
		$lib = PHOENIX_PATH.DS.str_replace('_', DS, $class).".php";
		if (file_exists($lib));
			require_once $lib;
	}

	function call(&$env)
	{
		$this->env = $env;

		$this->request = new Http_Request($env);
		$this->response = new Http_Response();

		$this->run();

		list($code, $headers, $body) = $this->response->finish();
		return array($code, $headers, $body);
	}

	public function run()
	{
		$this->response->write("Welcome to Phoenix");
	}
}
