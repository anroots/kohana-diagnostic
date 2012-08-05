<?php defined('SYSPATH') or die('No direct script access.');

// Add the route to the module controller - server component
Route::set('kohana-diagnostic-server', 'maintenance/diagnostic/check.json')
	->defaults(array(
	'directory' => 'maintenance',
	'controller'=> 'diagnostic',
	'action'    => 'check'
));

// Add route to the module controller - client component... only if enabled.
if (Kohana::$config->load('diagnostic.client_enabled') === TRUE) {
	Route::set('kohana-diagnostic-client', 'maintenance/diagnostic/run')
		->defaults(array(
		'directory' => 'maintenance',
		'controller'=> 'diagnostic',
		'action'    => 'run'
	));
}