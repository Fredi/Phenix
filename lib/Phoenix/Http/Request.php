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

			$class_file = $route->controller().'_controller';

			$file_path = ROOT.DS."app".DS."controllers".DS.$class_file.".php";
			if (file_exists($file_path))
				require_once $file_path;
			else
				throw new ControllerNotFoundException($file_path);

			$class_name = camelize($class_file);

			if (class_exists($class_name))
				$controller = new $class_name();
			else
				throw new ControllerClassNotFoundException($class_name);

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
		catch (ControllerClassNotFoundException $e)
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

class ControllerClassNotFoundException extends Exception {}

class ViewNotFoundException extends Exception {}
