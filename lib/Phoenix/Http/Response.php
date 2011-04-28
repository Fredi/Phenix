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

	public function sendHeaders()
	{
		foreach ($this->headers as $k => $v)
		{
			if (is_int($k))
				header($v, true);
			else
				header("{$k}: {$v}", true);
		}
	}

	public function render()
	{
		$statuses = Rack::http_status_codes();
		$status_message = $statuses[$this->status];

		$this->headers[] = "HTTP/1.1 {$this->status} {$status_message}";
		$this->headers['Status'] = "{$this->status} {$status_message}";

		$this->sendHeaders();

		$body = join("\r\n", (array)$this->body);

		$split_ary = str_split($body, 8192);

		foreach ($split_ary as $one_item)
		{
			echo $one_item;
		}
	}
}
