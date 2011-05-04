<?php
class BaseModel extends Model
{
	protected $__filters = array();
	protected $__validations = array();
	protected $__fields_with_errors = array();

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
	public function beforeSave() {}

	public function save($data = array())
	{
		if (!empty($data))
		{
			foreach ($data as $field => $value)
				$this->$field = $value;
		}

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
				if ($type == "confirmation")
				{
					$confirmation = $field."_confirmation";
					$validation->$function($this->$field, $this->$confirmation, $field, $options);
				}
				else
					$validation->$function($this->$field, $field, $options);
			}
		}

		$this->__fields_with_errors = $validation->fields_with_errors();

		return !$validation->errors();
	}
}
