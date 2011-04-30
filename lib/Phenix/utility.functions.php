<?php
/**
 * Utility functions
 */

/**
 * Try to auto load classes in the registered directories
 */
require_once ROOT.DS."lib".DS."Phenix".DS."AutoLoader.php";

$autoloader = new AutoLoader();
$autoloader->registerDirectories(array(
	ROOT.DS."lib"
));
$autoloader->register();

function autoloader()
{
	global $autoloader;
	if ($autoloader instanceof AutoLoader)
		return $autoloader;
	throw new RuntimeException('The $autoloader variable is not an instance of the AutoLoader class');
}

/**
 * Returns the instance of the Phenix Framework class
 */
function phenix()
{
	return Phenix::getInstance();
}

/**
 * Return a Phenix variable
 */
function get($name, $default = null)
{
	$value = phenix()->$name;
	return is_null($value) ? $default : $value;
}

/**
 * Set a Phenix variable
 */
function set($name, $value)
{
	phenix()->$name = $value;
}

/**
 * Returns the Rack request object
 */
function request()
{
	return Phenix::request();
}

/**
 * Returns the Rack response object
 */
function response()
{
	return Phenix::response();
}

/**
 * Returns the Session object
 */
function session()
{
	return Phenix::session();
}

/**
 * Returns the Route object
 */
function router()
{
	return Phenix::router();
}

/**
* Returns given $lower_case_and_underscored_word as a camelCased word.
* Taken from cakephp (http://cakephp.org)
* Licensed under the MIT License
*
* @param string $lower_case_and_underscored_word Word to camelize
* @return string Camelized word. likeThis.
*/
function camelize($lower_case_and_underscored_word)
{
	return str_replace(" ", "", ucwords(str_replace("_", " ", $lower_case_and_underscored_word)));
}

/**
* Returns an underscore-syntaxed ($like_this_dear_reader) version of the $camel_cased_word.
* Taken from cakephp (http://cakephp.org)
* Licensed under the MIT License
*
* @param string $camel_cased_word Camel-cased word to be "underscorized"
* @return string Underscore-syntaxed version of the $camel_cased_word
*/
function underscore($camel_cased_word)
{
	return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camel_cased_word));
}

/**
* Returns a human-readable string from $lower_case_and_underscored_word,
* by replacing underscores with a space, and by upper-casing the initial characters.
* Taken from cakephp (http://cakephp.org)
* Licensed under the MIT License
*
* @param string $lower_case_and_underscored_word String to be made more readable
* @return string Human-readable string
*/
function humanize($lower_case_and_underscored_word)
{
	return ucwords(str_replace("_", " ", $lower_case_and_underscored_word));
}

/**
 * Load config and routes
 */
function loadConfig()
{
	$default_config = array(
		// Database
		'database' => array(),
		// Log
		'log_enabled' => false,
		'log_class' => null,
		'log_path' => 'log',
		'log_level' => 4,
		// Debug
		'debug' => true,
		// Flash session key
		'flash_key' => 'flash'
	);

	$config_file = ROOT.DS."config".DS."config.php";

	$loaded_config = $default_config;

	if (file_exists($config_file))
	{
		$config = null;
		include($config_file);
		if (is_array($config))
			$loaded_config = array_merge($default_config, $config);
	}

	$routes_file = ROOT.DS."config".DS."routes.php";

	if (file_exists($routes_file))
		include($routes_file);

	return $loaded_config;
}

function flash($type, $message)
{
	Phenix::flash($type, $message);
}

function flashNow($type, $message)
{
	Phenix::flashNow($type, $message);
}