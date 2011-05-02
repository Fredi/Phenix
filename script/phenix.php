<?php
error_reporting(E_ALL);

if ($argc < 3)
{
	echo "Usage:\n";
	echo "  script/generate [option] [parameters]\n\n";

	echo "Options:\n";
	echo "  controller   # Generates a controller\n";
	echo "  model        # Generates a model\n\n";

	echo "Controller - parameters:\n";
	echo "  each parameter separated with a space\n";
	echo "  creates an action (function) in the controller\n\n";

	echo "Model - no parameters\n\n";

	echo "Example:\n";
	echo "  script/generate controller Products index new edit update destroy\n";

	exit;
}

// remove the script name from the arguments
unset($argv[0]);

// type of generator to execute
$type = array_shift($argv);

generate($type, $argv);

function generate($type, $args = array())
{
	if (!in_array($type, array("controller", "model")))
		die("{$tipo} is not a generator");

	$name = array_shift($args);
	$function = "phenix_generate_{$type}";
	$function($name, $args);
}

function phenix_create_dir($dir)
{
	if (!is_dir($dir))
	{
		if (mkdir($dir, 0775, true))
			echo "  created {$dir}\n";
		else
			echo "  failed {$dir}\n";
	}
	else
		echo "  exists {$dir}\n";
}

function phenix_generate($dest_dir, $dest_file, $content)
{
	$file = "{$dest_dir}/{$dest_file}";

	if (!file_exists($file))
	{
		if (file_put_contents($file, $content) !== false)
			echo "  created {$file}\n";
		else
			echo "  failed {$file}\n";
	}
	else
		echo "  exists {$file}\n";
}

function phenix_generate_controller($name, $actions = array())
{
	$controller_name = $name;
	$controller_class_name = camelize($controller_name . "_controller");
	$helper_class_name = camelize($controller_name ."_helper");

	$controller_dir = "app/controllers";

	$controller_content = "<?php\n";
	$controller_content .= "class {$controller_class_name} extends Controller\n";
	$controller_content .= "{\n";
	foreach ($actions as $action)
	{
		$controller_content .= "\tpublic function {$action}()\n";
		$controller_content .= "\t{\n\t\t\n\t}\n";
	}
	$controller_content .= "}\n";
	phenix_create_dir($controller_dir);
	phenix_generate($controller_dir, "{$controller_name}_controller.php", $controller_content);

	$helpers_dir = "app/helpers";

	$helper_content = "<?php\n";
	$helper_content .= "class {$helper_class_name}\n";
	$helper_content .= "{\n\t\n}\n";
	phenix_create_dir($helpers_dir);
	phenix_generate($helpers_dir, "{$controller_name}_helper.php", $helper_content);

	phenix_create_dir("app/views/{$controller_name}");

	foreach ($actions as $action)
	{
		phenix_generate_view($controller_name, $action);
	}
}

function phenix_generate_view($controller, $action)
{
	$action = $action;
	$controller_name = $controller;
	$controller_class_name = camelize($controller_name . "_controller");

	$view_content = "<h1>{$controller_class_name}#{$action}</h1>\n";
	$view_content .= "<p>Find me in app/views/{$controller_name}/{$action}.phtml</p>";

	phenix_generate("app/views/{$controller_name}", "{$action}.phtml", $view_content);
}

function phenix_generate_model($name, $options = array())
{
	$model_name = $name;
	$model_class_name = camelize($model_name);

	$models_dir = "app/models";

	$model_content = "<?php\n";
	$model_content .= "class {$model_class_name} extends BaseModel\n";
	$model_content .= "{\n\t\n}\n";

	phenix_create_dir($models_dir);
	phenix_generate($models_dir, "{$model_name}.php", $model_content);
}

function camelize($lower_case_and_underscored_word)
{
	return str_replace(" ", "", ucwords(str_replace("_", " ", $lower_case_and_underscored_word)));
}
