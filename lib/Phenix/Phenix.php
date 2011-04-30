<?php
error_reporting(E_ALL | E_STRICT);

spl_autoload_register(array('Phenix', 'autoload'));

define("PHENIX_PATH", ROOT.DS."lib".DS."Phenix");

define("APP_PATH", ROOT.DS."app");
define("CONTROLLERS_PATH", APP_PATH.DS."controllers");
define("MODELS_PATH", APP_PATH.DS."models");
define("VIEWS_PATH", APP_PATH.DS."views");
define("HELPERS_PATH", APP_PATH.DS."helpers");

define("LOG", ROOT.DS."log");
define("TMP", ROOT.DS."tmp");

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

	private $settings = array();

	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new self();

		return self::$instance;
	}

	public static function autoload($class)
	{
		// Try to load Phenix libraries
		$lib = PHENIX_PATH.DS.str_replace('_', DS, $class).".php";
		if (file_exists($lib))
			require_once($lib);
		// Try to load a controller
		else if (file_exists(CONTROLLERS_PATH.DS.underscore($class).'.php'))
			require_once(CONTROLLERS_PATH.DS.underscore($class).'.php');
		// Try to load a model
		else if (file_exists(MODELS_PATH.DS.strtolower($class).'.php'))
			require_once(MODELS_PATH.DS.strtolower($class).'.php');
		// Try to load a helper
		else if (file_exists(HELPERS_PATH.DS.strtolower($class).'.php'))
			require_once(HELPERS_PATH.DS.strtolower($class).'.php');
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
		if (!isset($_SESSION))
			session_start();

		$this->response = new Http_Response();
		$this->router = new Router();
		$this->session = new Session();

		$config = loadConfig();
		$this->settings = $config;

		if (isset($config['database']['driver']))
		{
			$database = $config['database'];

			require_once(ROOT.DS."vendor".DS."idiorm".DS."idiorm.php");
			require_once(ROOT.DS."vendor".DS."paris".DS."paris.php");

			ORM::configure($database['driver']);

			unset($database['driver']);

			foreach ($database as $key => $val)
				ORM::configure($key, $val);
		}

		$this->flash = new Session_Flash($config['flash_key']);

		if (!is_dir(LOG))
			mkdir(LOG);
		if (!is_dir(TMP))
			mkdir(TMP);
	}

	public function run()
	{
		$this->setup();

		$this->request->handleRequest();
	}

	public static function config($name, $value = null)
	{
		if (func_num_args() === 1)
		{
			if (is_array($name))
				self::$instance->settings = array_merge(self::$instance->settings, $name);
			else
				return in_array($name, array_keys(self::$instance->settings)) ? self::$instance->settings[$name] : null;
		} else
			self::$instance->settings[$name] = $value;
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
