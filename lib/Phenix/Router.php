<?php
class Router
{
	private $routes;

	public function __construct()
	{
		$this->routes = array(
			'GET' => array(),
			'POST' => array(),
			'PUT' => array(),
			'DELETE' => array()
		);
	}

	public function getMatched()
	{
		$uri = request()->uri()->getUri();
		$method = request()->getMethod();
		foreach ($this->routes[$method] as $route)
		{
			if ($route->matches($uri))
				return $route;
		}

		throw new RouteNotMatchedException($uri);
	}

	public function map($pattern, $callable, $method)
	{
        $route = new Route($pattern, $callable);
        $route->setRouter($this);
        $this->routes[$method][] = $route;
        return $route;
    }
}

class RouteNotMatchedException extends Exception {}
