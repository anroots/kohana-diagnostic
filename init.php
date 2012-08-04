<?php defined('SYSPATH') or die('No direct script access.');

// Add the route to the module controller
Route::set('kohana-diagnostic', 'maintenance/diagnostic/check.json')
	->defaults(array(
	'directory' => 'maintenance',
	'controller'=> 'diagnostic',
	'action'    => 'check'
));