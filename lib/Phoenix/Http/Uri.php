<?php
class Http_Uri
{
	protected static $baseUri;
	protected static $uri;
	protected static $queryString;

	private $env;

	public function __construct(&$env)
	{
		$this->env = $env;
	}

	public function getBaseUri($reload = false)
	{
		if ($reload || is_null(self::$baseUri))
		{
			$requestUri = isset($this->env['REQUEST_URI']) ? $this->env['REQUEST_URI'] : $this->env['PHP_SELF'];
			$scriptName = $this->env['SCRIPT_NAME'];
			$baseUri = strpos($requestUri, $scriptName) === 0 ? $scriptName : str_replace('\\', '/', dirname($scriptName));
			self::$baseUri = rtrim($baseUri, '/');
		}
		return self::$baseUri;
	}

	public function getUri($reload = false)
	{
		if ($reload || is_null(self::$uri))
		{
			$uri = '';
			if (!empty($this->env['PATH_INFO']))
				$uri = $this->env['PATH_INFO'];
			else
			{
				if (isset($this->env['REQUEST_URI']))
					$uri = parse_url((!empty($this->env['HTTPS']) ? 'https' : 'http') . '://' . $this->env['HTTP_HOST'] . $this->env['REQUEST_URI'], PHP_URL_PATH);
				else if (isset($this->env['PHP_SELF']))
					$uri = $this->env['PHP_SELF'];
				else
					$uri = '/';
			}
			if (self::getBaseUri() !== '' && strpos($uri, self::getBaseUri()) === 0)
				$uri = substr($uri, strlen(self::getBaseUri()));

			self::$uri = '/' . ltrim($uri, '/');
		}
		return self::$uri;
	}
}
