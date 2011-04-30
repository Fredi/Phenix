<?php
/**
 * Handle our class auto load
 */
 
if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
 
class AutoLoader
{
	private $directories = array();

	public function getDirectories()
	{
		return $this->directories;
	}

	public function registerDirectories(array $paths)
	{
		foreach ($paths as $path)
			$this->registerDirectory($path);
	}

	public function registerDirectory($path)
	{
		if (!in_array($path, $this->directories))
			$this->directories[] = $path;
	}

	public function register($prepend = false)
	{
		spl_autoload_register(array($this, 'autoload'));
	}

	public function autoload($class)
	{
		if ($file = $this->find($class))
			require $file;
	}

	public function find($class)
	{
		foreach ($this->directories as $dir)
		{
			$file = $dir.DS.$class.'.php';
			if (file_exists($file))
				return $file;

			$file = $dir.DS.str_replace('_', DS, $class).'.php';
			if (file_exists($file))
				return $file;
		}
		return false;
	}
}
