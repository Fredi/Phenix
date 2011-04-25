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

	private $router;

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
		$this->router = new Router($this->request);

		$this->run();

		list($code, $headers, $body) = $this->response->finish();
		return array($code, $headers, $body);
	}

	public function run()
	{
		$this->response->write("Welcome to Phoenix");
	}

	public static function router()
	{
		return self::$instance->router;
	}

	protected static function mapRoute($type, $args)
	{
		if (count($args) < 2)
			throw new InvalidArgumentException('Pattern and callable are required to create a route');

		$pattern = array_shift($args);
		$callable = array_pop($args);
		$route = self::router()->map($pattern, $callable, $type);

		return $route;
	}

	public static function get()
	{
		$args = func_get_args();
		return self::mapRoute("GET", $args);
	}

	public static function post()
	{
		$args = func_get_args();
		return self::mapRoute("POST", $args);
	}

	public static function put()
	{
		$args = func_get_args();
		return self::mapRoute("PUT", $args);
	}

	public static function delete()
	{
		$args = func_get_args();
		return self::mapRoute("DELETE", $args);
	}
}
