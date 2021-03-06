<?php
class Http_Request extends Request
{
	protected $uri;

	public function __construct(&$env)
	{
		parent::__construct($env);

		$this->uri = new Http_Uri($env);
	}

	public function uri()
	{
		return $this->uri;
	}

	public function getMethod()
	{
		return $this->env['REQUEST_METHOD'];
	}

	public function handleRequest()
	{
		$route = router()->getMatched();

		$class_name = camelize($route->controller().'_controller');

		if (class_exists($class_name))
			$controller = new $class_name();
		else
			throw new ControllerNotFoundException($class_name);

		$action = $route->action();
		$params = array_merge($route->getParams(), $this->params());

		$controller->params = $params;
		$controller->action = $action;

		$this->dispatch($controller, $action, $params);
	}

	private function dispatch(&$controller, $action, $params)
	{
		$controller->beforeAction();

		$output = null;

		if (method_exists($controller, $action))
		{
			ob_start();
			$output = call_user_func_array(array(&$controller, $action), array($params));
			$output = trim(ob_get_clean());
		}
		else
			throw new ActionNotFoundException($action);

		if ($output === null || empty($output))
		{
			$controller->beforeRender();
			$output = $controller->render();
		}

		$controller->afterAction();

		$response = response();
		$response->write($output);
	}
}

class ControllerNotFoundException extends Exception {}

class TemplateNotFoundException extends Exception {}
