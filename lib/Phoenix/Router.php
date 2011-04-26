<?php
class Router
{
	private $routes;

	private $count = 0;

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
		$this->count++;
        return $route;
    }

	public function count()
	{
		return $this->count;
	}
}

class RouteNotMatchedException extends Exception {}
