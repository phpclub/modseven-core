<?php
// Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize Modseven for testing
// Set up test environment, configuration, etc.

// Define test constants
define('MODSPATH', realpath(__DIR__ . '/../system/') . DIRECTORY_SEPARATOR);
define('APPPATH', realpath(__DIR__ . '/Support/') . DIRECTORY_SEPARATOR);

// Initialize the framework for testing