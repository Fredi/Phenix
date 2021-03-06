<?php
error_reporting(E_ALL | E_STRICT);

set_error_handler(array('Phenix', 'handleErrors'));

spl_autoload_register(array('Phenix', 'autoload'));

define("PHENIX_PATH", ROOT.DS."lib".DS."Phenix");

define("APP_PATH", ROOT.DS."app");
define("CONTROLLERS_PATH", APP_PATH.DS."controllers");
define("MODELS_PATH", APP_PATH.DS."models");
define("VIEWS_PATH", APP_PATH.DS."views");
define("HELPERS_PATH", APP_PATH.DS."helpers");

define("LOG", ROOT.DS."log");
define("TMP", ROOT.DS."tmp");
define("CACHE", TMP.DS."cache");

define("MIDDLEWARE_PATH", PHENIX_PATH.DS."middleware");

// Load utility functions
require_once PHENIX_PATH.DS."utility.functions.php";

/**
 * The core of Phenix Framework
 */
class Phenix
{
	private static $instance;

	private $request;
	private $response;
	private $session;

	private $router;

	protected $env;

	protected $flash;

	private $variables = array();

	public static function getInstance($loadconfig = false)
	{
		if (!isset(self::$instance))
			self::$instance = new self();

		if ($loadconfig)
			loadConfig();

		return self::$instance;
	}

	public function __construct()
	{
		$this->response = new Http_Response();
		$this->router = new Router();
		$this->session = new Session();
	}

	public static function autoload($class)
	{
		// Try to load Phenix libraries
		$lib = PHENIX_PATH.DS.str_replace('_', DS, $class).".php";
		if (file_exists($lib))
			require_once($lib);
		// Try to load a model
		else if (file_exists(MODELS_PATH.DS.underscore($class).'.php'))
			require_once(MODELS_PATH.DS.underscore($class).'.php');
		// Try to load a controller
		else if (file_exists(CONTROLLERS_PATH.DS.underscore($class).'.php'))
			require_once(CONTROLLERS_PATH.DS.underscore($class).'.php');
		// Try to load a helper
		else if (file_exists(HELPERS_PATH.DS.underscore($class).'.php'))
			require_once(HELPERS_PATH.DS.underscore($class).'.php');
	}

	function call(&$env)
	{
		$this->env = $env;

		$this->request = new Http_Request($env);

		$this->run();

		list($code, $headers, $body) = $this->response->finish();
		return array($code, $headers, $body);
	}

	public function setup()
	{
		if (Config::get('session_autostart'))
			session_start();

		$this->checkSystemFolders();

		if (Config::get('routes_autoload') === true)
		{
			$routes = ROOT.DS."config".DS."routes.php";
			if (file_exists($routes))
				include($routes);
		}

		require_once(ROOT.DS."vendor".DS."idiorm".DS."idiorm.php");
		require_once(ROOT.DS."vendor".DS."paris".DS."paris.php");

		$database = Config::get('database');
		if (isset($database['dsn']))
		{
			ORM::configure($database['dsn']);

			unset($database['dsn']);

			foreach ($database as $key => $val)
				ORM::configure($key, $val);
		}

		if (Config::get('log_enable') === true )
		{
			$logger = Config::get('log_logger');
			if (empty($logger))
				Log::setLogger(new Log_File(Config::get('log_path'), Config::get('log_level')));
			else
				Log::setLogger($logger);
		}

		$this->flash = new Session_Flash(Config::get('flash_key'));
	}

	/**
	 * Create system folders if they doesn't exist
	 */
	private function checkSystemFolders()
	{
		$systemFolders = array(APP_PATH, CONTROLLERS_PATH, MODELS_PATH, VIEWS_PATH, HELPERS_PATH, LOG, TMP, CACHE);
		foreach ($systemFolders as $dir)
			if (!is_dir($dir))
				mkdir($dir);
	}

	public function run()
	{
		$this->setup();

		$this->request->handleRequest();
	}

	public static function handleErrors($errno, $errstr = '', $errfile = '', $errline = '')
	{
		if (!(error_reporting() & $errno))
			return;

		Log::error(sprintf("Message: %s | File: %s | Line: %d | Level: %d", $errstr, $errfile, $errline, $errno));

		// If we are debugging then let php execute its internal error handler
		return (Config::get('debug') !== true);
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

	public static function redirect($url, $status = 301)
	{
		if ($status >= 300 && $status <= 307)
		{
			$response = self::response();
			$response->redirect((string)$url, $status);
			$response->clearBody();
			$response->render();
			exit;
		}
		else
			throw new InvalidArgumentException('Phenix::redirect only accepts HTTP 300-307 status codes.');
	}

	protected static function mapRoute($type, $args)
	{
		if (count($args) < 2)
			$args[] = null;

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

	public static function resources($controller, $path = null)
	{
		$controller = strtolower($controller);
		$path = isset($path) ? $path : "/".$controller;

		$contitions = array('id' => '\d{1,8}');
		self::mapRoute("GET", array($path, $controller."#index"));
		self::mapRoute("GET", array($path."/new", $controller."#add"));
		self::mapRoute("POST", array($path, $controller."#create"));
		self::mapRoute("GET", array($path."/:id", $controller."#show"))->conditions($contitions);
		self::mapRoute("GET", array($path."/:id/edit", $controller."#edit"))->conditions($contitions);
		self::mapRoute("PUT", array($path."/:id", $controller."#update"))->conditions($contitions);
		self::mapRoute("DELETE", array($path."/:id", $controller."#destroy"))->conditions($contitions);
	}

	public static function resource($controller, $path = null)
	{
		$controller = strtolower($controller);
		$path = isset($path) ? $path : "/".$controller;

		self::mapRoute("GET", array($path."/new", $controller."#add"));
		self::mapRoute("POST", array($path, $controller."#create"));
		self::mapRoute("GET", array($path, $controller."#show"));
		self::mapRoute("GET", array($path."/edit", $controller."#edit"));
		self::mapRoute("PUT", array($path, $controller."#update"));
		self::mapRoute("DELETE", array($path, $controller."#destroy"));
	}

	public static function flash($type, $message)
	{
		self::$instance->flash->set($type, $message);
	}

	public static function flashNow($type, $message)
	{
		self::$instance->flash->setNow($type, $message);
	}

	public function getFlash()
	{
		return $this->flash->toArray();
	}
}

class ClassNotFoundException extends Exception {}
