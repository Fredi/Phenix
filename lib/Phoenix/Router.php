<?php
class Router
{
	private $request;

	private $routes;
	private $matched;

	public function __construct(Http_Request $request)
	{
		$this->request = $request;
		$this->routes = array(
			'GET' => array(),
			'POST' => array(),
			'PUT' => array(),
			'DELETE' => array()
		);
	}

	public function getMatched($reload = false)
	{
		if ($reload || is_null($this->matched))
		{
			$this->matched = array();
			$method = $this->request->getMethod();
			foreach ($this->routes[$method] as $route)
			{
				if ($route->matches($this->request->uri()->getUri()))
					$this->matched[] = $route;
			}
		}
		return $this->matched;
	}

	public function map($pattern, $callable, $method)
	{
        $route = new Route($pattern, $callable);
        $route->setRouter($this);
        $this->routes[$method][] = $route;
        return $route;
    }
}
