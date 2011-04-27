<?php
/**
 * A HEAD request should return an empty body
 */
class HeadRequest
{
	function __construct(&$app)
	{
		$this->app =& $app;
	}
	
	function call(&$env)
	{
		if ($env['REQUEST_METHOD'] == "HEAD")
		{
			$env["REQUEST_METHOD"] = "GET";
			$env["rack.methodoverride.original_method"] = "HEAD";
			list($status, $headers, $body) = $this->app->call($env);
			return array($status, $headers, array());
		}
		else
			return $this->app->call($env);
	}
}
