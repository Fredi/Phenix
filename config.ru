<?php
/**
 * rackup file
 */

// Load php-rack library
require_once ROOT.DS."vendor".DS."php-rack".DS."lib".DS."Rack.php";

// Load the framework
require_once ROOT.DS."lib".DS."Phoenix".DS."Phoenix.php";


Rack::add("Phoenix", null, Phoenix::getInstance());


Rack::run();