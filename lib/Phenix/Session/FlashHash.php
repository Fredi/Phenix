<?php
class Session_FlashHash
{
	private $used = array();
	private $flashes = array();
	private $now = null;

	public function __set($key, $value)
	{
		$this->keep($key);
		$this->flashes[$key] = $value;
	}

	public function __get($key)
	{
		if (isset($this->flashes[$key]))
			return $this->flashes[$key];
		return null;
	}

	public function keys()
	{
		return array_keys($this->flashes);
	}

	public function toArray()
	{
		return (array)$this->flashes;
	}

	public function now()
	{
		if (is_null($this->now))
			$this->now = new Session_FlashNow($this);
		return $this->now;
	}

	public function keep($key = null)
	{
		return $this->_use($key, false);
	}

	public function discard($key = null)
	{
		return $this->_use($key);
	}

	public function sweep()
	{
		foreach ($this->keys() as $key)
		{
			if (!in_array($key, $this->used))
				$this->used[] = $key;
			else
			{
				unset($this->flashes[$key]);
				$used_k = array_search($key, $this->used);
				unset($this->used[$used_k]);
			}
		}
	}

	protected function _use($key = null, $used = true)
	{
		$a = is_null($key) ? $this->keys() : array($key);

		foreach ($a as $k)
		{
			if ($used)
				$this->used[] = $k;
			else
			{
				if ($used_k = array_search($k, $this->used))
					unset($this->used[$used_k]);
			}
		}

		if (is_null($key))
			return $this->flashes;
		else
			return (isset($this->flashes[$key])) ? $this->flashes[$key] : '';
	}
}

class Session_FlashNow
{
	private $flash;

	public function __construct($flash)
	{
		$this->flash = $flash;
	}

	public function __set($key, $value)
	{
		$this->flash->$key = $value;
		$this->flash->discard($key);
		return $value;
	}

	public function __get($key)
	{
		return $this->flash->$key;
	}
}
