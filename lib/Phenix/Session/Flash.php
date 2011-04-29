<?php
class Session_Flash
{
	private $key = 'flash';

	public function __construct($key = null)
	{
		if (!is_null($key))
			$this->key = $key;

		if (!isset($_SESSION[$key]) || !$_SESSION[$key] instanceof Session_FlashHash)
			$_SESSION[$key] = new Session_FlashHash();

		$this->session()->sweep();
	}

	private function session()
	{
		return $_SESSION[$this->key];
	}

	public function set($key, $value)
	{
		$this->session()->$key = $value;
	}

	public function get($key)
	{
		return $this->session()->key;
	}

	public function setNow($key, $value)
	{
		$this->session()->now()->$key = $value;
	}

	public function toArray()
	{
		return $this->session()->toArray();
	}
}
