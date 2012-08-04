<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Trait for basic database connectivity checks for kohana-diagnostic.
 *
 * @author Ando Roots <ando@sqroot.eu>
 */
trait Diagnostic_Trait_Database {

	/**
	 * Test connectivity for all database profiles (defined in config/database.php)
	 *
	 * @since 1.0
	 * @throws Database_Exception
	 * @return bool
	 */
	public function test_database_profiles()
	{
		// Load all defined database profiles
		$profiles = (array) Kohana::$config->load('database');

		if (! count($profiles)) {
			throw new Database_Exception('No database profiles specified');
		}

		// Test connection to each profile
		foreach ($profiles as $name => $settings) {

			// Skip profiles that don't have diagnostic_enabled = TRUE
			if (!array_key_exists('diagnostic_enabled', $settings) || ! $settings['diagnostic_enabled']) {
				continue;
			}

			try {
				DB::query(Database::SELECT, 'SELECT version()')
					->execute($name);
			} catch (Database_Exception $e) {
				return FALSE;
			}
		}
		return TRUE;
	}
}