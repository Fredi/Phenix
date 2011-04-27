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
		try
		{
			$route = router()->getMatched();

			$class_name = camelize($route->controller().'_controller');

			if (class_exists($class_name))
				$controller = new $class_name();
			else
				throw new ControllerNotFoundException($class_name);

			$controller->runAction($route->action(), $route->getParams());
		}
		catch (RouteNotMatchedException $e)
		{
			Phoenix::notFound($e);
		}
		catch (ControllerNotFoundException $e)
		{
			Phoenix::notFound($e);
		}
		catch (ViewNotFoundException $e)
		{
			Phoenix::notFound($e);
		}
		catch (Exception $e)
		{
			Phoenix::error($e);
		}
	}
}

class ControllerNotFoundException extends Exception {}

class TemplateNotFoundException extends Exception {}
