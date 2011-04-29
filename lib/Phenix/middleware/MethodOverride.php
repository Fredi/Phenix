<?php
class MethodOverride
{
	private $http_methods = array("GET", "HEAD", "PUT", "POST", "DELETE", "OPTIONS");

	function __construct(&$app)
	{
		$this->app =& $app;
	}
	
	function call(&$env)
	{
		if ($env['REQUEST_METHOD'] == "POST")
		{
			$req = new Request($env);
			$method = isset($req['_method']) ? $req['_method'] : @$env['HTTP_X_HTTP_METHOD_OVERRIDE'];
			$method = strtoupper($method);
			if (in_array($method, $this->http_methods))
			{
				$env["rack.methodoverride.original_method"] = $env["REQUEST_METHOD"];
				$env["REQUEST_METHOD"] = $method;
			}
		}

		return $this->app->call($env);
	}
}
