<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Example controller for Kohana-Diagnostic.
 * The controller extends the Diagnostic module's abstract controller and
 * contains a number of `test_` methods. All methods prefixed by `test_` will be
 * automatically called by the parent class.
 * Each test method should test for a discrete unit of functionality and return a boolean result.
 *
 * @author Ando Roots <ando@sqroot.eu>
 */
class Controller_Maintenance_Diagnostic extends Diagnostic_Controller {

	/**
	 * @var bool Log failed tests to Kohana log file
	 */
	protected $_log_failed_results = TRUE;

	/**
	 * Check connectivity with the database.
	 *
	 * @return bool Test result (success/failure)
	 */
	public function test_database()
	{
		try {
			return (bool) DB::query(Database::SELECT, 'SHOW DATABASES')
				->execute()
				->count();
		} catch (Database_Exception $e) {
			return FALSE;
		}
	}

	/**
	 * Check that the APPPATH/cache folder is writable
	 *
	 * @return bool Test result (success/failure)
	 */
	public function test_cache_filesystem()
	{
		return is_writable(Kohana::$cache_dir);
	}
}