<?php
class Route
{
	protected $pattern;
	protected $callable;
	protected $conditions = array();

	protected static $defaultConditions = array();

	protected $params = array();

	protected $router;

	protected $controller = 'home';
	protected $action = 'index';

	public function __construct($pattern, $callable)
	{
		$this->setPattern($pattern);
		$this->setCallable($callable);
		$this->setConditions(self::getDefaultConditions());
	}

	public static function setDefaultConditions(array $defaultConditions)
	{
		self::$defaultConditions = $defaultConditions;
	}

	public static function getDefaultConditions()
	{
		return self::$defaultConditions;
	}

	public function getPattern()
	{
		return $this->pattern;
	}

	public function setPattern($pattern)
	{
		$this->pattern = str_replace(')', ')?', (string)$pattern);
	}

	public function getCallable()
	{
		return $this->callable;
	}

	public function setCallable($callable)
	{
		$this->callable = $callable;

		if (!is_null($callable))
		{
			$action = "index";
			$c = substr_count($callable, "#");
			if ($c == 1)
				list($controller, $action) = explode("#", $callable);
			else if ($c == 0)
				$controller = $callable;

			$this->controller($controller);
			$this->action($action);
		}
	}

	public function controller($controller = null)
	{
		if (!is_null($controller))
			$this->controller = $controller;
		else
			return $this->controller;
	}

	public function action($action = null)
	{
		if (!is_null($action))
			$this->action = $action;
		else
			return $this->action;
	}

	public function getConditions()
	{
		return $this->conditions;
	}

	public function setConditions(array $conditions)
	{
		$this->conditions = $conditions;
	}

	public function getParams()
	{
		return $this->params;
	}

	public function getRouter()
	{
		return $this->router;
	}

	public function setRouter(Router $router)
	{
		$this->router = $router;
	}

	public function matches($resourceUri)
	{
		preg_match_all('@:([\w]+)@', $this->getPattern(), $paramNames, PREG_PATTERN_ORDER);
		$paramNames = $paramNames[0];

		$patternAsRegex = preg_replace_callback('@:[\w]+@', array($this, 'convertPatternToRegex'), $this->getPattern());
		if (substr($this->getPattern(), -1) === '/')
			$patternAsRegex = $patternAsRegex . '?';
		$patternAsRegex = '@^' . $patternAsRegex . '$@';
		if (preg_match($patternAsRegex, $resourceUri, $paramValues))
		{
			array_shift($paramValues);
			if (is_null($this->getCallable()))
			{
				foreach ($paramValues as $index => $value)
				{
					if ($index == "controller")
						$this->controller($value);
					else if ($index == "action")
						$this->action($value);
				}
			}
			foreach ($paramNames as $index => $value)
			{
				$val = substr($value, 1);
				if (isset($paramValues[$val]))
					$this->params[$val] = urldecode($paramValues[$val]);
			}
			return true;
		}
		else
			return false;
	}

	private function convertPatternToRegex($matches)
	{
		$key = str_replace(':', '', $matches[0]);
		if (array_key_exists($key, $this->conditions))
			return '(?P<' . $key . '>' . $this->conditions[$key] . ')';
		else
			return '(?P<' . $key . '>[a-zA-Z0-9_\-\.\!\~\*\\\'\(\)\:\@\&\=\$\+,%]+)';
	}

	public function conditions(array $conditions)
	{
		$this->conditions = array_merge($this->conditions, $conditions);
		return $this;
	}
}
