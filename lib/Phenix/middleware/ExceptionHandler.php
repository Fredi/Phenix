<?php
define("RESCUES_TEMPLATE_PATH", MIDDLEWARE_PATH.DS."templates".DS."rescues");

if (!defined("CONSIDER_ALL_REQUESTS_LOCAL"))
	define("CONSIDER_ALL_REQUESTS_LOCAL", false);

/**
 * This middleware rescues any exception returned by the application and renders
 * nice exception pages if it's being rescued locally.
 *
 * Code ported from Rails
 * https://github.com/rails/rails/blob/master/actionpack/lib/action_dispatch/middleware/show_exceptions.rb
 */
class ExceptionHandler
{
	private $rescue_responses = array(
		"RouteNotMatchedException"    => "not_found",
		"ControllerNotFoundException" => "not_found",
		"ActionNotFoundException"     => "not_found",
		"ClassNotFoundException"      => "not_found"
	);

	private $rescue_templates = array(
		"TemplateNotFoundException" => "missing_template",
		"RouteNotMatchedException"  => "routing_error",
		"ActionNotFoundException"   => "unknown_action"
	);

	function __construct(&$app)
	{
		$this->app =& $app;
	}
	
	function call(&$env)
	{
		$exception = null;
		try
		{
			list($status, $headers, $body) = $this->app->call($env);
		}
		catch (Exception $exception)
		{
			return $this->render_exception($env, $exception);
		}

		return array($status, $headers, $body);
	}

	private function render_exception($env, $exception)
	{
		try
		{
			Log::error($exception);

			$request = new Request($env);
			if (CONSIDER_ALL_REQUESTS_LOCAL || $request->isLocal())
				return $this->rescue_action_locally($request, $exception);
			else
				return $this->rescue_action_in_public($exception);
		}
		catch (Exception $exception)
		{
			error_log("Error during the failsafe response: ".(string)$exception);
			return $this->default_error();
		}
	}

	private function rescue_action_locally($request, $exception)
	{
		$class_name = get_class($exception);
		$template = (isset($this->rescue_templates[$class_name])) ? $this->rescue_templates[$class_name] : "diagnostics";
		$template = $template.".phtml";
		@ob_start();
		require RESCUES_TEMPLATE_PATH.DS."layout.phtml";
		$body = @ob_get_clean();
		return $this->render($this->status_code($exception), $body);
	}

	private function rescue_action_in_public($exception)
	{
		$status = $this->status_code($exception);
		$file = ROOT.DS."public".DS.$status.".html";
		if (file_exists($file))
			return $this->render($status, file_get_contents($file));

		if ($status == 500)
			return $this->default_error();
		else
			return $this->render($status, '');
	}

	private function status_code($exception)
	{
		$class = get_class($exception);
		$status_name = (isset($this->rescue_responses[$class])) ? $this->rescue_responses[$class] : 'internal_server_error';
		$status = array_search(humanize($status_name), Rack::http_status_codes());
		return ($status !== false) ? $status : 500;
	}

	private function render($status, $body)
	{
		return array($status, array('Content-Type' => 'text/html', 'Content-Length' => strlen($body)), array($body));
	}

	private function default_error()
	{
		$body = "<html><head><title>500 Internal Server Error</title></head><body>" .
				"<h1>500 Internal Server Error</h1>If you are the administrator of this " .
				"website, then please read this web application's log file and/or the web " .
				"server's log file to find out what went wrong.</body></html>";
		return $this->render(500, $body);
	}
}
