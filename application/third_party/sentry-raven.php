<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!defined('__DIR__')) {
	define('__DIR__', dirname(__FILE__));
}

$CI =& get_instance();

$_SERVER['APP_ENV'] = ENVIRONMENT;

// /application/third_party/raven-php/lib/Raven/Autoloader.php
require_once __DIR__ . '/raven-php/lib/Raven/Autoloader.php';
Raven_Autoloader::register();

$this->config->load('sentry');

if ($CI->config->item('sentry_enabled') !== false)
{
	$client = new Raven_Client($CI->config->item('sentry_client_id'));

	$client->tags_context(array(
		'Environment' => ENVIRONMENT,
		'php_version' => phpversion(),
	));

	$client->extra_context(array(
		// dump the session
		'session' => $CI->session->all_userdata(),

		// dump last SQL Query
		'last_query' => $CI->db->last_query(),

		// dump all querys
		'queries' => $CI->db->queries,
	));

	// Install error handlers and shutdown function to catch fatal errors
	$error_handler = new Raven_ErrorHandler($client);
	$error_handler->registerExceptionHandler();
	$error_handler->registerErrorHandler();
	$error_handler->registerShutdownFunction();
}

function sendSentryMessage($message) {
	$CI =& get_instance();

	$client = new Raven_Client($CI->config->item('sentry_client_id'));
	$client->getIdent($client->captureMessage($message));
	$client->context->clear();
}
