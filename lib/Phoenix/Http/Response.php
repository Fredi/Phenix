<?php
class Http_Response extends Response
{
	public function status($status = 200)
	{
		$this->status = $status;
	}

	public function headers()
	{
		return $this->headers;
	}

	public function header($key, $value = null)
	{
		if (!is_null($value))
			$this->headers[$key] = $value;

		return isset($this->headers[$key]) ? $this->headers[$key] : null;
	}

	public function clearHeaders()
	{
		$this->headers = array();
	}

	public function clearBody()
	{
		$this->body = array();
		$this->length = 0;
	}
}
