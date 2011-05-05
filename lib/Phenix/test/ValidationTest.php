<?php
require "bootstrap.php";

require ROOT.DS."lib".DS."Phenix".DS."Validation.php";
require ROOT.DS."lib".DS."Phenix".DS."utility.functions.php";

class ValidationTest extends PHPUnit_Framework_TestCase
{
	private $validation;

	public function setUp()
	{
		$this->validation = new Validation('');
	}

	public function testValidPresenceOf()
	{
		$this->validation->validates_presence_of("bar", "field");
		$this->assertFalse($this->validation->field_with_error("field"));
	}

	public function testInvalidPresenceOf()
	{
		$this->validation->validates_presence_of("", "field");
		$this->assertTrue($this->validation->field_with_error("field"));
	}

	public function testNullPresenceOf()
	{
		$this->validation->validates_presence_of(null, "field");
		$this->assertTrue($this->validation->field_with_error("field"));
	}

	public function testValidExclusionOf()
	{
		$this->validation->validates_exclusion_of("test", "field", array("in" => array("test2", "test3")));
		$this->assertFalse($this->validation->field_with_error("field"));
	}

	public function testInvalidExclusionOf()
	{
		$this->validation->validates_exclusion_of("test", "field", array("in" => array("test", "test1")));
		$this->assertTrue($this->validation->field_with_error("field"));

		$this->validation->validates_exclusion_of("test1", "field2", array("in" => array("test", "test1")));
		$this->assertTrue($this->validation->field_with_error("field2"));
	}

	public function testValidFormatOf()
	{
		$this->validation->validates_format_of("12345", "field", array("with" => '/^[0-9]{5}$/'));
		$this->assertFalse($this->validation->field_with_error("field"));

		$this->validation->validates_format_of("abcde", "field2", array("with" => "/^[a-e]{5}$/"));
		$this->assertFalse($this->validation->field_with_error("field2"));
	}

	public function testInvalidFormatOf()
	{
		$this->validation->validates_format_of("12a34", "field", array("with" => '/^[0-9]{5}$/'));
		$this->assertTrue($this->validation->field_with_error("field"));

		$this->validation->validates_format_of("abcdef", "field2", array("with" => "/^[a-e]{5}$/"));
		$this->assertTrue($this->validation->field_with_error("field2"));
	}

	public function testValidInclusionOf()
	{
		$this->validation->validates_inclusion_of("test", "field", array("in" => array("test", "test2")));
		$this->assertFalse($this->validation->field_with_error("field"));
	}

	public function testInvalidInclusionOf()
	{
		$this->validation->validates_inclusion_of("test", "field", array("in" => array("test1", "test2")));
		$this->assertTrue($this->validation->field_with_error("field"));

		$this->validation->validates_inclusion_of("test1", "field2", array("in" => array("test", "test2")));
		$this->assertTrue($this->validation->field_with_error("field2"));
	}

	public function testValidLengthOf()
	{
		$this->validation->validates_length_of("test", "field", array("min" => 4));
		$this->assertFalse($this->validation->field_with_error("field"));

		$this->validation->validates_length_of("testing", "field2", array("max" => 7));
		$this->assertFalse($this->validation->field_with_error("field2"));

		$this->validation->validates_length_of("test", "field3", array("in" => array(2, 5)));
		$this->assertFalse($this->validation->field_with_error("field3"));

		$this->validation->validates_length_of("foo", "field4", array("is" => 3));
		$this->assertFalse($this->validation->field_with_error("field4"));
	}

	public function testInvalidLengthOf()
	{
		$this->validation->validates_length_of("test", "field", array("min" => 5));
		$this->assertTrue($this->validation->field_with_error("field"));

		$this->validation->validates_length_of("testing", "field2", array("max" => 5));
		$this->assertTrue($this->validation->field_with_error("field2"));

		$this->validation->validates_length_of("test", "field3", array("in" => array(5, 10)));
		$this->assertTrue($this->validation->field_with_error("field3"));

		$this->validation->validates_length_of("foo", "field4", array("is" => 5));
		$this->assertTrue($this->validation->field_with_error("field4"));
	}

	public function testValidNumericalityOf()
	{
		$this->validation->validates_numericality_of(123, "field");
		$this->assertFalse($this->validation->field_with_error("field"));

		$this->validation->validates_numericality_of(123.50, "field2");
		$this->assertFalse($this->validation->field_with_error("field2"));

		$this->validation->validates_numericality_of(12345, "field3", array("only_integer" => true));
		$this->assertFalse($this->validation->field_with_error("field3"));
	}

	public function testInvalidNumericalityOf()
	{
		$this->validation->validates_numericality_of("123ab", "field");
		$this->assertTrue($this->validation->field_with_error("field"));

		$this->validation->validates_numericality_of(123.50, "field2", array("only_integer" => true));
		$this->assertTrue($this->validation->field_with_error("field2"));
	}

	/**
	 * TODO
	 */
	public function testValidUniquenessOf() {}

	/**
	 * TODO
	 */
	public function testInvalidUniquenessOf() {}

	public function testValidWithFunctionOf()
	{
		$this->validation->validates_with_function_of("ok", "field", array("function" => "custom_validation"));
		$this->assertFalse($this->validation->field_with_error("field"));
	}

	public function testInvalidWithFunctionOf()
	{
		$this->validation->validates_with_function_of("not ok", "field", array("function" => "custom_validation"));
		$this->assertTrue($this->validation->field_with_error("field"));
	}
}

function custom_validation($value)
{
	return ($value == "ok");
}
