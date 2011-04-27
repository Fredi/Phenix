<?php
/**
 * Utility functions
 */

/**
 * Returns the instance of Phoenix Framework class
 */
function phoenix()
{
	return Phoenix::getInstance();
}

/**
 * Return a Phoenix variable
 */
function get($name, $default = null)
{
	$value = phoenix()->$name;
	return is_null($value) ? $default : $value;
}

/**
 * Set a Phoenix variable
 */
function set($name, $value)
{
	phoenix()->$name = $value;
}

/**
 * Returns the Rack request object
 */
function request()
{
	return Phoenix::request();
}

/**
 * Returns the Rack response object
 */
function response()
{
	return Phoenix::response();
}

/**
 * Returns the Session object
 */
function session()
{
	return Phoenix::session();
}

/**
 * Returns the Route object
 */
function router()
{
	return Phoenix::router();
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
		// View
		'view_class' => 'View'
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