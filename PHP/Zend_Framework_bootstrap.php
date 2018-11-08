<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// check for the existence of the environment variable or die
if (!isset($_SERVER['ENVIRONMENT'])) {
	die(
   			'Apache environment variable (SetEnv) not set. Configuration cannot be ' .
   			'loaded. See documentation or source code for examples.'
   	);
}

$env = strtolower($_SERVER['ENVIRONMENT']);

if ($env == 'production') {
	define('PRODUCTION', true);
} else {
	define('PRODUCTION', false);
}

if (!empty($env)) {
	define('APPLICATION_ENV', $env);
} else {
	define('APPLICATION_ENV', 'production');
}

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    '/usr/bin',
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

//start session
Zend_Session::start();

$application->bootstrap()
            ->run();