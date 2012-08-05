<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana-diagnostic controller
 *
 * @author Ando Roots <ando@sqroot.eu>
 * @since 1.0
 */
abstract class Diagnostic_Controller extends Controller {

	// Prefix for test methods
	const TEST_METHOD_PREFIX = 'test_';

	// Module version
	const VERSION = '1.1';

	/**
	 * @var array An array of subclass-controller method names to run as tests
	 */
	private $_tests_to_run = array();

	/**
	 * @var int HTTP status code. 200 - all test pass, 500 - at least one failed test
	 */
	private $_status = 200;

	/**
	 * @var array Holds an array of test results: method_name => result
	 */
	private $_test_results = array();

	/**
	 * @var bool Whether to write failed test results to Kohana log files
	 */
	protected $_log_failed_results = TRUE;

	/**
	 * @var array Caches the final output (response body). Will be json-encoded.
	 */
	private $_output = array();

	public function before()
	{
		parent::before();

		// Get the name of the subclass-controller
		$subclass_name = get_called_class();

		// List all methods of that controller
		$methods = get_class_methods($subclass_name);

		foreach ($methods as $method) {

			// The current method begins with the test prefix?
			if (strncmp($method, self::TEST_METHOD_PREFIX, strlen(self::TEST_METHOD_PREFIX)) === 0) {
				$this->_tests_to_run[] = $method;
			}
		}

		if (! count($this->_tests_to_run)) {
			throw new Kohana_Exception('You have to have at least one test method!');
		}
	}

	/**
	 * Add key/value pairs to the final controller output.
	 * These will be json-encoded.
	 *
	 * @param string $key Array key
	 * @param $value Array value
	 * @return Diagnostic_Controller
	 */
	public function add_output($key, $value)
	{
		$this->_output[$key] = $value;
		return $this;
	}

	/**
	 * Get current output.
	 *
	 * @return array
	 */
	public function get_output()
	{
		return $this->_output;
	}

	/**
	 * Run diagnostic checks and echo output in JSON
	 *
	 * @since 1.0
	 */
	public function action_check()
	{
		// Call all test functions and save their output
		foreach ($this->_tests_to_run as $method) {

			$result = (bool) call_user_func(array($this, $method));
			$this->_test_results[$method] = $result;

			// Set overall status to failed when a test fails
			if ($this->_status && ! $result) {
				$this->_status = FALSE;
			}

			// Add failed test result to Kohana log files
			if ($this->_log_failed_results && ! $result) {
				Kohana::$log->add(Log::WARNING, 'Diagnostic test ":name" failed!', array(':name'=> $method));
			}
		}

		// Add output values
		$this->add_output('status', $this->_status ? 200 : 500)
			->add_output('ts', time())
			->add_output('module_version', self::VERSION)
			->add_output('results', $this->_test_results);

		// Set response body
		$this->response->body(json_encode($this->_output));

		// Set response type to JSON
		$this->response->headers('Content-Type', 'application/json');
	}

	/**
	 * Client code - parse the results of defined remote sites (config/diagnostic.php) and report results.
	 * Really simplistic and meant as an example only. Feel free to refactor and add stuff (send a pull request!).
	 * This controller should be called via cron, periodically.
	 * Make sure to configure the script in config/diagnostic.php
	 *
	 * @since 1.1
	 * @throws Kohana_Exception Invalid config
	 * @throws HTTP_Exception_500 Throws HTTP 500 exception when any checks fail
	 */
	public function action_run()
	{
		// Get a list of URI-s to check
		$remotes = Kohana::$config->load('diagnostic.remotes');

		// Get a list of emails to notify on failure
		$emails = Kohana::$config->load('diagnostic.on_failure.emails');

		// Validate config file contents
		if (! Kohana::$config->load('diagnostic.client_enabled') || ! is_array($remotes) || ! count($remotes) || ! count($emails)) {
			throw new Kohana_Exception('Invalid config for kohana-diagnostic.');
		}

		// Will hold check results
		$responses = array();

		// Start checking remotes
		foreach ($remotes as $alias => $remote) {

			// Make a request to the remote
			$response = Request::factory($remote)
				->execute();

			// Check for response validity (HTTP 200 & body is JSON)
			if ($response->status() !== 200 || $response->headers('Content-Type') !== 'application/json') {
				$response[$remote] = FALSE;
				continue;
			}

			// Save remote output
			$responses[$alias] = array('status'                            => $response->status(),
			                           'data'                              => json_decode($response->body())
			);
		}

		// Will stay empty if all sites report OK
		$failed = array();

		// Check responses
		foreach ($responses as $alias=> $response) {

			// All OK with this remote
			if ($response['status'] === 200 && isset($response['data']->status) && $response['data']->status === 200) {
				continue;
			}

			// Save failed result
			$failed[$alias] = $response;
		}

		if (count($failed)) { // If any of the sites failed

			// Build message - this will be mailed
			$message = NULL;

			foreach ($failed as $alias => $data) {
				// Below is just string building code

				$message .= "= $alias =\n";

				if ($data === FALSE || ! isset($data['data']->results)) {
					$message .= "\t* Invalid response\n";
				} else {
					foreach ($data['data']->results as $test_name => $test_result) {
						if ($test_result !== TRUE) {
							$message .= "\t* $test_name: failed\n";
						}
					}
				}
				$message .= "\n\n";
			}
			$message = rtrim($message, "\n");

			// E-mail message to all recipients
			foreach ($emails as $email) {
				mail($email, '[Kohana-Diagnostic] Check failure', $message);
			}

			if (Kohana::$config->load('diagnostic.on_failure.throw_exception')) {
				throw new HTTP_Exception_500('Some checks failed');
			} else {
				return;
			}
		}

		// All OK, exit with no body
	}
}