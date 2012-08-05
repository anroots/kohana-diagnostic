<?php defined('SYSPATH') or die('No direct script access.');
return array(
	/**
	 * @var bool Whether the client controller is accessible from the web
	 * @since 1.1
	 */
	'client_enabled'  => FALSE,

	/**
	 * @var array Actions to perform when a check fails
	 * @since 1.1
	 */
	'on_failure'      => array(

		/**
		 * @var bool Whether to throw HTTP_Exception_500 when a check fails
		 */
		'throw_exception'=> FALSE,

		/**
		 * @var array A list of e-mails to notify about the error
		 * @since 1.1
		 */
		'emails'=> array('test@example.com'),
	),

	/**
	 * @var array A list of URL-s to check. The URL-s should be to the kohana-diagnostic JSON output controller.
	 * Syntax: site alias => URI
	 * @example http://myproject.com/maintenance/diagnostic/check.json
	 * @since 1.1
	 */
	'remotes'         => array(
		'local' => URL::base('http').'maintenance/diagnostic/check.json'
	)
);