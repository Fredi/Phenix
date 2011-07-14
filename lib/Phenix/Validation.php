<?php 
class Validation
{
	private $model;

	private $fields_with_errors = array();

	public function __construct($model)
	{
		$this->model = $model;
	}

	public function errors()
	{
		return (!empty($this->fields_with_errors)) ? true : false;
	}

	public function fields_with_errors()
	{
		return $this->fields_with_errors;
	}

	public function field_with_error($field)
	{
		return isset($this->fields_with_errors[$field]);
	}

	/**
	 * Validates fields with values
	 *
	 * Example to use in your model:
	 * protected $__validations = array("name" => array("presence" => array()));
	 *
	 * If you want a custom message:
	 * protected $__validations = array("name" => array("presence" => array("message" => "You must enter your name")));
	 */
	public function validates_presence_of($value, $fieldName, $options = array())
	{
		if (!isset($options['message']))
			$options['message'] = humanize($fieldName) . ' is required.';

		if (empty($value))
			$this->fields_with_errors[$fieldName] = $options['message'];
	}

	/**
	 * Validates the if the user set an unnaceptable value
	 *
	 * Example to use in your model:
	 * protected $__validations = array("username" => array("exclusion" => array("in" => array("admin", "superuser"), "message" => "Username not allowed")));
	 */
	public function validates_exclusion_of($value, $fieldName, $options = array())
	{
		if (@$options['allow_null'] && ($value == null))
			return true;

		if (!isset($options['in']))
			$options['in'] = array();

		if (!isset($options['message']))
			$options['message'] = humanize($fieldName) . ' should not be one of the following: ' . join(',',$options['in']) . '.';

		if (in_array($value, $options['in']))
			$this->fields_with_errors[$fieldName] = $options['message'];
	}

	/**
	 * Validates using a regular expression
	 *
	 * Example to use in your model:
	 * protected $__validations = array("email" => array("format" => array("with" => "/\A([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})\Z/i")));
	 */
	public function validates_format_of($value, $fieldName, $options = array())
	{
		if  (@$options['allow_null'] && ($value == null))
			return true;

		if (!isset($options['message']))
			$options['message'] = humanize($fieldName) . ' has an invalid format.';

		if (!isset($options['with']))
			$options['with'] = '//';

		if (!preg_match($options['with'],$value))
			$this->fields_with_errors[$fieldName] = $options['message'];
	}

	/**
	 * Validates if the value is one of the values you want
	 *
	 * Example to use in your model:
	 * protected $__validations = array("gender" => array("inclusion", array("in" => array("m", "f"))));
	 */
	public function validates_inclusion_of($value, $fieldName, $options = array())
	{
		if (@$options['allow_null'] && ($value == null))
			return true;

		if (!isset($options['in']))
			$options['in'] = array();

		if (!isset($options['message']))
			$options['message'] = humanize($fieldName) . ' should be one of the following: ' . join(',',$options['in']) . '.';

		if (!in_array($value, $options['in']))
			$this->fields_with_errors[$fieldName] = $options['message'];
	}

	/**
	 * Validates the length of a value
	 *
	 * Examples to use in your model:
	 * protected $__validations = array("name" => array("length" => array("min" => 3, "message" => "Does your name has less then 3 letters?")));
	 * protected $__validations = array("name" => array("length" => array("max" => 50, "message" => "Less than 50 characters")));
	 * protected $__validations = array("username" => array("length" => array("in" => array(6, 20), "message" => "Username must have at minimum 6 characters and maximum 20")));
	 * protected $__validations = array("year" => array("length" => array("is" => 4, "message" => "Year with 4 digits")));
	 */
	public function validates_length_of($value, $fieldName, $options = array())
	{
		if (@$options['allow_null'] && ($value == null))
			return true;

		if (!isset($options['message']))
			$options['message'] = humanize($fieldName) . ' has the wrong length.';

		$len = strlen($value);

		if (isset($options['max']) && $len > $options['max'])
			$this->fields_with_errors[$fieldName] = $options['message'];
		elseif (isset($options['min']) && $len < $options['min'])
			$this->fields_with_errors[$fieldName] = $options['message'];
		elseif (isset($options['in']) && is_array($options['in']))
		{
			if ($len < $options['in'][0] || $len > $options['in'][1])
				$this->fields_with_errors[$fieldName] = $options['message'];
		}
		elseif (isset($options['is']) && $len != $options['is'])
			$this->fields_with_errors[$fieldName] = $options['message'];
	}

	/**
	 * Validates if the value is numeric
	 *
	 * Example to use in your model:
	 * protected $__validations = array("price" => array("numericality" => array()));
	 */
	public function validates_numericality_of($value, $fieldName, $options = array())
	{
		if (@$options['allow_null'] && ($value == null))
			return true;

		if (!isset($options['only_integer']))
			$options['only_integer'] = false;

		if (!isset($options['message']))
		{
			if ($options['only_integer'])
				$options['message'] = humanize($fieldName) . ' should be an integer.';
			else
				$options['message'] = humanize($fieldName) . ' should be a number.';
		}

		if (!is_numeric($value) || ($options['only_integer'] && !is_int($value)))
			$this->fields_with_errors[$fieldName] = $options['message'];
	}

	/**
	 * Validates if the value already exists in your table
	 *
	 * Example to use in your model:
	 * protected $__validations = array("email" => array("uniqueness" => array()));
	 */
	public function validates_uniqueness_of($value, $fieldName, $options = array())
	{
		if (@$options['allow_null'] && ($value == null))
			return true;

		if (!isset($options['message']))
			$options['message'] = humanize($fieldName) . ' is already taken.';

		if (Model::factory($this->model)->where($fieldName, $value)->find_one())
			$this->fields_with_errors[$fieldName] = $options['message'];
	}

	/**
	 * Validates the value using an existing function that return true if valid or false if invalid
	 *
	 * Example to use in your model:
	 * protected $__validations = array("text" => array("with_function", array("function" => "some_function", "message" => "Something went wrong")));
	 */
	public function validates_with_function_of($value, $fieldName, $options = array())
	{
		if (!isset($options['message']))
			$options['message'] = humanize($fieldName) . ' is invalid.';

		if (!isset($options['function']) || !is_callable($options['function']))
		{
			$this->fields_with_errors[$fieldName] = $options['message'];
			return;
		}

		$function = $options['function'];

		if (!$function($value))
			$this->fields_with_errors[$fieldName] = $options['message'];
	}
}
