<?php
class Controller
{
	/**
	 * Name of the controller
	 */
	public $name = null;

	/**
	 *	The action to be executed
	 */
	public $action = null;

	/**
	 * Layout template to render within the action template
	 */
	public $layout = 'application';

	/**
	 * Parameters received in the current request
	 */
	public $params = array();

	/**
	 * Variables to pass to the view
	 */
	public $variables = array();

	/**
	 * Render the layout automatically around the view
	 */
	public $renderLayout = true;

	/**
	 * The name of the view class we will use to render the output
	 */
	public $view = 'View';

	/**
	 * The folder to this controller's views
	 */
	public $viewPath = null;

	/**
	 * File extension of view templates
	 */
	public $ext = '.phtml';

	/**
	 * Output of the requested action
	 */
	public $output = null;

	public function __construct()
	{
		if (is_null($this->name))
			$this->name = strtolower(str_replace("Controller", "", get_class($this)));

		if (is_null($this->viewPath))
			$this->viewPath = underscore($this->name);
	}

	/**
	 * Redirects an action to another without performing a traditional redirect
	 */
	public function setAction($action)
	{
		$this->action = $action;
		$args = func_get_args();
		unset($args[0]);
		return call_user_func_array(array(&$this, $action), $args);
	}

	public function render($action = null, $layout = null, $file = null)
	{
		$viewClass = $this->view;

		// If the class doesn't exist the autoloader will throw an exception
		if (!class_exists($viewClass))
			exit;

		$view = new $viewClass($this);

		$this->output .= $view->render($action, $layout, $file);

		return $this->output;
	}
	public function beforeFilter() {}

	public function afterFilter() {}

	public function set($name, $value)
	{
		$this->variables[$name] = $value;
	}
}

class ActionNotFoundException extends Exception {}

