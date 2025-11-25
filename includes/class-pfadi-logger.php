<?php

/**
 * Logging functionality.
 *
 * @package PfadiManager
 */

/**
 * Handles logging of events and errors.
 */
class Pfadi_Logger {

	/**
	 * The log filename.
	 *
	 * @var string
	 */
	private static $log_file = 'pfadi_debug.log';

	/**
	 * Log a message.
	 *
	 * @param mixed  $message The message to log.
	 * @param string $level   The log level (info, error, etc.).
	 */
	public static function log( $message, $level = 'info' ) {
		// Always log to error_log for standard debugging.
		if ( is_array( $message ) || is_object( $message ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			$message = print_r( $message, true );
		}

		$formatted_message = sprintf( '[Pfadi Manager] [%s] %s', strtoupper( $level ), $message );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $formatted_message );

		// Also log to file.
		self::write_to_file( $message, $level );
	}

	/**
	 * Write a log entry to the log file.
	 *
	 * @param string $message The message to log.
	 * @param string $level   The log level.
	 */
	private static function write_to_file( $message, $level ) {
		$file      = self::get_log_file_path();
		$timestamp = current_time( 'mysql' );
		$entry     = sprintf( "[%s] [%s] %s\n", $timestamp, strtoupper( $level ), $message );

		// Ensure directory exists.
		$dir = dirname( $file );
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
			// Create .htaccess to protect logs.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $dir . '/.htaccess', 'deny from all' );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $file, $entry, FILE_APPEND );
	}

	/**
	 * Get the full path to the log file.
	 *
	 * @return string The log file path.
	 */
	public static function get_log_file_path() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/pfadi-manager-logs/' . self::$log_file;
	}

	/**
	 * Retrieve the last N log lines.
	 *
	 * @param int $lines Number of lines to retrieve.
	 * @return array The log lines.
	 */
	public static function get_logs( $lines = 100 ) {
		$file = self::get_log_file_path();
		if ( ! file_exists( $file ) ) {
			return array();
		}

		// Read file backwards efficiently would be better, but for 100 lines reading all is okay for now
		// or use array_slice with file().
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file
		$content = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		if ( ! $content ) {
			return array();
		}

		return array_slice( $content, -$lines );
	}

	/**
	 * Clear the log file.
	 */
	public static function clear_logs() {
		$file = self::get_log_file_path();
		if ( file_exists( $file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			unlink( $file );
		}
	}
}
