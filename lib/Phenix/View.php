<?php
class View
{
	/**
	 * Name of the controller
	 */
	public $name = null;

	/**
	 * Action performed
	 */
	public $action;

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
	 * The folder to this controller's views
	 */
	public $viewPath = null;

	/**
	 * File extension of view templates
	 */
	public $ext = '.phtml';

	/**
	 * True when the view has been rendered
	 */
	public $hasRendered = false;

	/**
	 * Output of the requested action
	 */
	public $output = false;

	public function __construct(&$controller)
	{
		if (is_object($controller))
		{
			$this->name = $controller->name;
			$this->action = $controller->action;
			$this->layout = $controller->layout;
			$this->params = $controller->params;
			$this->variables = $controller->variables;
			$this->renderLayout = $controller->renderLayout;
			$this->viewPath = $controller->viewPath;
		}
	}

	public function render($action = null, $layout = null, $file = null)
	{
		if ($this->hasRendered)
			return true;

		if (!is_null($file))
			$action = $file;

		$out = null;

		$file = $this->viewFileName($action);

		$out = $this->_render($file, $this->variables);

		if (is_null($layout))
			$layout = $this->layout;

		if ($out !== false)
		{
			if ($layout && $this->renderLayout)
				$out = $this->renderLayout($out, $layout);
			$this->hasRendered = true;
		}

		return $out;
	}

	public function renderLayout($content, $layout = null)
	{
		$layoutFile = $this->layoutFileName($layout);

		$vars = array_merge($this->variables, array(
			"yield" => $content
		));

		if (!isset($vars['page_title']))
			$vars['page_title'] = humanize($this->name);

		$this->output = $this->_render($layoutFile, $vars);

		return $this->output;
	}

	private function render_partial($name, $vars = array())
	{
		$partialFile = $this->partialFileName($name);

		$vars = array_merge($this->variables, $vars);
		extract($vars);

		require $partialFile;
	}

	public function _render($__viewFile, $__viewVars)
	{
		extract($__viewVars, EXTR_SKIP);

		ob_start();
		include $__viewFile;
		$out = trim(ob_get_clean());

		return $out;
	}

	public function viewFileName($name = null)
	{
		if (is_null($name))
			$name = $this->action;

		$name = str_replace("/", DS, $name);

		$file = VIEWS_PATH.DS.$this->viewPath.DS.$name.$this->ext;

		if (!file_exists($file))
			throw new TemplateNotFoundException($file);

		return $file;
	}

	public function layoutFileName($name = null)
	{
		if (is_null($name))
			$name = $this->layout;

		$name = str_replace("/", DS, $name);

		$file = VIEWS_PATH.DS."layouts".DS.$name.$this->ext;

		if (!file_exists($file))
			throw new TemplateNotFoundException($file);

		return $file;
	}

	public function partialFileName($name = null)
	{
		if (is_null($name))
			throw new InvalidArgumentException("You must specify the partial name");

		if (strpos($name, "/") === false)
			$name = $this->viewPath.DS."_".$name;
		else
		{
			$a = explode("/", $name);
			$a[count($a) - 1] = "_".end($a);
			$name = implode("/", $a);
		}

		$name = str_replace("/", DS, $name);

		$file = VIEWS_PATH.DS.$name.$this->ext;

		if (!file_exists($file))
			throw new TemplateNotFoundException($file);

		return $file;
	}
}
