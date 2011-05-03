<?php
require "bootstrap.php";

require ROOT.DS."lib".DS."Phenix".DS."Phenix.php";

class PhenixTest extends PHPUnit_Extensions_OutputTestCase
{
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
}
