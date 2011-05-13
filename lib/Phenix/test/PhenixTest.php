<?php
require "bootstrap.php";

require ROOT.DS."vendor".DS."php-rack".DS."lib".DS."Rack.php";
require ROOT.DS."lib".DS."Phenix".DS."Phenix.php";

$_SERVER['HTTP_HOST'] = "phenix";
$_SERVER['QUERY_STRING'] = "";
$_SERVER['REMOTE_ADDR'] = "127.0.0.1";
$_SERVER['PHP_SELF'] = "/bootstrap.php";

class HomeController extends ApplicationController
{
	function index() {}
}

class PhenixTest extends PHPUnit_Extensions_OutputTestCase
{
	private function setConfig($config = array())
	{
		Config::set(array(
			// Session
			'session_autostart' => false,
			// Database
			'database' => array(),
			// Log
			'log_enable' => false,
			'log_class' => null,
			'log_path' => '../log',
			'log_level' => 4,
			// Debug
			'debug' => true,
			// Flash session key
			'flash_key' => 'flash',
			// Auto load routes (only disabled for testing)
			'routes_autoload' => true
		));

		if (is_array($config))
			Config::set($config);
	}

	private function setupRackAndRun()
	{
		$this->setConfig(array(
			'routes_autoload' => false
		));

		Rack::add("ExceptionHandler", MIDDLEWARE_PATH.DS."ExceptionHandler.php");
		Rack::add("Phenix", null, Phenix::getInstance());
		Rack::run();
	}

	public function setUp()
	{
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "/";
	}

	public function testPhenixGetInstance()
	{
		$phenix = Phenix::getInstance();
		$this->assertTrue($phenix instanceof Phenix);
	}

	public function testPhenixWithNoRoutes()
	{
		$this->setupRackAndRun();
		$this->assertEquals(Phenix::response()->getStatus(), 404);
	}

	public function testPhenixWithRouteButNoController()
	{
		Phenix::get('/', 'notfound#index');
		$this->setupRackAndRun();
		$this->assertEquals(Phenix::response()->getStatus(), 404);
	}

	public function testPhenixWithRouteAndControllerButNoAction()
	{
		Phenix::router()->cleanRoutes();
		Phenix::get('/', 'home#test');
		$this->setupRackAndRun();
		$this->assertEquals(Phenix::response()->getStatus(), 404);
	}
}
