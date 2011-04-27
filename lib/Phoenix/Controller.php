<?php
class Controller
{
	protected $render_layout = true;

	protected $layout_name = 'application';

	protected $action = '';

	protected $params = array();

	protected $variables = array();

	protected $status = 200;

	public function runAction($action, $params = array())
	{
		$this->action = $action;

		set('current_action', $action);
		set('current_controller', strtolower(str_replace("Controller", "", get_class($this))));

		$this->params = $params;
		$this->set('params', $params);

		$this->beforeFilter();

		$value = null;

		if (method_exists($this, $action))
		{
			@ob_start();
			$value = call_user_func_array(array($this, $action), array($params));
			$value = trim(@ob_get_contents());
			@ob_end_clean();
		}
		else
			throw new ActionNotFoundException($action);

		if ($value === null || empty($value))
			$value = $this->renderAction();

		if ($this->render_layout)
		{
			$this->set('yield', $value);
			$value = $this->renderLayout();
		}

		$this->afterFilter();

		$response = response();
		$response->status($this->status);

		$response->write($value);
	}

	public function renderAction()
	{
		return $this->renderView(get('current_controller').DS.$this->action);
	}

	public function renderLayout()
	{
		return $this->renderView("layouts".DS.$this->layout_name);
	}

	public function renderView($file)
	{
		$file = ROOT.DS."app".DS."views".DS.$file.".phtml";
		if (!file_exists($file))
			throw new TemplateNotFoundException("View file for action '{$this->action}' doen't exist");

		extract($this->variables);

		@ob_start();
		require $file;
		$value = trim(@ob_get_contents());
		@ob_end_clean();

		return $value;
	}

	public function beforeFilter()
	{
	
	}

	public function afterFilter()
	{
	
	}

	public function set($name, $value)
	{
		$this->variables[$name] = $value;
	}
}

class ActionNotFoundException extends Exception {}

