<?php
class BaseModel extends Model
{
	protected $__fields_with_errors = array();

	public function table_name()
	{
		return self::_get_table_name(get_class($this));
	}

	public function errors()
	{
		return count($this->__fields_with_errors) ? true : false;
	}

	public function fields_with_errors($fields = null)
	{
		if (is_array($fields))
			$this->__fields_with_errors = $fields;
		else
			return $this->__fields_with_errors;
	}
}
