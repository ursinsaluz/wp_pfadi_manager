<?php
/**
 * Class SampleTest
 *
 * @package Wp_Pfadi_Manager
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	public function test_sample() {
		// Replace this with your actual test code.
		$this->assertTrue( true );
	}

	/**
	 * Test if plugin is active.
	 */
	public function test_plugin_is_active() {
		$this->assertTrue( class_exists( 'Pfadi_Loader' ) );
	}
}
