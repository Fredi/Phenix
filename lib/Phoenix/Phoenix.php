<?php
error_reporting(E_ALL | E_STRICT);

spl_autoload_register(array('Phoenix', 'autoload'));

define("PHOENIX_PATH", ROOT.DS."lib".DS."Phoenix");

// Load utility functions
require_once PHOENIX_PATH.DS."utility.functions.php";

/**
 * The core of Phoenix Framework
 */
class Phoenix
{
	private static $instance;

	private $request;
	private $response;
	private $session;

	private $router;

	protected $env;

	private $variables = array();

	public function __construct()
	{
		$this->session = new Session();
	}

	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new self();

		return self::$instance;
	}

	public static function autoload($class)
	{
		$lib = PHOENIX_PATH.DS.str_replace('_', DS, $class).".php";
		if (file_exists($lib))
			require_once $lib;
	}

	function call(&$env)
	{
		$this->env = $env;

		$this->request = new Http_Request($env);
		$this->response = new Http_Response();
		$this->router = new Router();

		$this->run();

		list($code, $headers, $body) = $this->response->finish();
		return array($code, $headers, $body);
	}

	public function setup()
	{
		// Load ApplicationController if it exists
		$app_controller = ROOT.DS."app".DS."controllers".DS."application_controller.php";
		if (file_exists($app_controller))
			require_once $app_controller;

		// Load application routes
		$routes = ROOT.DS."config".DS."routes.php";
		if (file_exists($routes))
			require_once $routes;
	}

	public function run()
	{
		$this->setup();

		if ($this->router->count())
			$this->request->handleRequest();
		else
			$this->response->write("Welcome to Phoenix!");
	}

	public function __get($name)
	{
		if (!isset($this->variables[$name]))
			return null;
		return $this->variables[$name];
	}

	public function __set($name, $value)
	{
		$this->variables[$name] = $value;
	}

	public static function request()
	{
		return self::$instance->request;
	}

	public static function response()
	{
		return self::$instance->response;
	}

	public static function session()
	{
		return self::$instance->session;
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
