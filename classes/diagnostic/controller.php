<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana-diagnostic controller
 *
 * @author Ando Roots <ando@sqroot.eu>
 * @version 1.0
 */
abstract class Diagnostic_Controller extends Controller {

	// Prefix for test methods
	const TEST_METHOD_PREFIX = 'test_';

	// Module version
	const VERSION = '1.0';

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
}