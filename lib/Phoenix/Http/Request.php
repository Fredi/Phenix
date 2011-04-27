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

		if (!file_exists(CONTROLLERS_PATH.DS.underscore($class_name).'.php'))
			throw new ControllerNotFoundException($class_name);

		if (class_exists($class_name))
			$controller = new $class_name();
		else
			throw new ControllerNotFoundException($class_name);

		$controller->runAction($route->action(), $route->getParams());
	}
}

class ControllerNotFoundException extends Exception {}

class TemplateNotFoundException extends Exception {}
