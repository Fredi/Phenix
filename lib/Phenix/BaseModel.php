<?php
class BaseModel extends Model
{
	protected $__filters = array();
	protected $__validations = array();
	protected $__fields_with_errors = array();

	protected $__attr_accessible = array();

	public static function create($data = null)
	{
		$class = get_called_class();
		$model = BaseModel::factory($class)->create();

		return $model->set_data($data);
	}

	public static function find_one($id = null)
	{
		$class = get_called_class();
		return BaseModel::factory($class)->find_one($id);
	}

	public static function all()
	{
		$class = get_called_class();
		return BaseModel::factory($class)->find_many();
	}

	public function table_name()
	{
		return self::_get_table_name(get_class($this));
	}

	public function errors()
	{
		return count($this->__fields_with_errors) ? true : false;
	}

	public function fields_with_errors()
	{
		return $this->__fields_with_errors;
	}

	public function beforeFilter() {}
	public function beforeValidation() {}
	public function beforeCreate() {}
	public function beforeUpdate() {}
	public function beforeSave() {}

	public function save($data = array())
	{
		$this->set_data($data);

		$this->beforeFilter();

		// Filter all data
		foreach ($this->as_array() as $field => $value)
		{
			if (isset($this->__filters[$field]))
				$this->$field = FilterInput::clean($this->$field, $this->__filters[$field]);
			else
				$this->$field = FilterInput::clean($this->$field);
		}

		$this->beforeValidation();

		if ($this->validate())
		{
			if (is_null($this->id))
				$this->beforeCreate();
			else
				$this->beforeUpdate();

			$this->beforeSave();

			return parent::save();
		}

		return $this;
	}

	public function validate()
	{
		if (empty($this->__validations))
			return true;

		$validation = new Validation($this->table_name());

		foreach ($this->__validations as $field => $types)
		{
			foreach ($types as $type => $options)
			{
				if ($validation->field_with_error($field))
					break;

				$function = "validates_{$type}_of";

				$validation->$function($this->$field, $field, $options);
			}
		}

		$this->__fields_with_errors = $validation->fields_with_errors();

		return !$validation->errors();
	}

	public function set_data($data = array())
	{
		if (!empty($data))
		{
			foreach ($data as $field => $value)
			{
				if ((!empty($this->__attr_accessible) && isset($this->__attr_accessible[$field])) 
					|| empty($this->__attr_accessible))
					$this->$field = $value;
			}
		}

		return $this;
	}
}

if (!function_exists('get_called_class'))
{
	function get_called_class()
	{
		$bt = debug_backtrace();
		$lines = file($bt[1]['file']);
		preg_match('/([a-zA-Z0-9\_]+)::'.$bt[1]['function'].'/',
			$lines[$bt[1]['line']-1],
			$matches);
		return $matches[1];
	}
}
