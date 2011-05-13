<?php
/**
 * Simple class to hold configuration data
 */
class Config
{
	protected static $config = array();

	public static function get($key)
	{
		return (isset(self::$config[$key])) ? self::$config[$key] : null;
	}

	public static function set($key, $value = null)
	{
		if (func_num_args() === 1 && is_array($key))
			self::$config = array_merge(self::$config, $key);
		else
			self::$config[$key] = $value;
	}
}
