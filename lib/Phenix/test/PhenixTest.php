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
	private function setupRackAndRun()
	{
		Phenix::config('config_file', 'no_existing_file');
		Phenix::config('routes_file', 'no_existing_file');
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

	public function testPhenixConfigSet()
	{
		Phenix::config('foo', 'bar');
		$this->assertEquals(Phenix::config('foo'), 'bar');
	}

	public function testPhenixConfigDoesntExist()
	{
		$this->assertNull(Phenix::config('bar'));
	}

	public function testPhenixConfigWithArray()
	{
		Phenix::config(array(
			'one' => '1',
			'two' => '2'
		));
		$this->assertEquals(Phenix::config('one'), '1');
		$this->assertEquals(Phenix::config('two'), '2');
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
