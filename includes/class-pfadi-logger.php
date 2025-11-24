<?php

class Pfadi_Logger {

	private static $log_file = 'pfadi_debug.log';

	public static function log( $message, $level = 'info' ) {
		// Always log to error_log for standard debugging
		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true );
		}

		$formatted_message = sprintf( '[Pfadi Manager] [%s] %s', strtoupper( $level ), $message );
		error_log( $formatted_message );

		// Also log to file
		self::write_to_file( $message, $level );
	}

	private static function write_to_file( $message, $level ) {
		$file      = self::get_log_file_path();
		$timestamp = current_time( 'mysql' );
		$entry     = sprintf( "[%s] [%s] %s\n", $timestamp, strtoupper( $level ), $message );

		// Ensure directory exists
		$dir = dirname( $file );
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
			// Create .htaccess to protect logs
			file_put_contents( $dir . '/.htaccess', 'deny from all' );
		}

		file_put_contents( $file, $entry, FILE_APPEND );
	}

	public static function get_log_file_path() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/pfadi-manager-logs/' . self::$log_file;
	}

	public static function get_logs( $lines = 100 ) {
		$file = self::get_log_file_path();
		if ( ! file_exists( $file ) ) {
			return array();
		}

		// Read file backwards efficiently would be better, but for 100 lines reading all is okay for now
		// or use array_slice with file()
		$content = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		if ( ! $content ) {
			return array();
		}

		return array_slice( $content, -$lines );
	}

	public static function clear_logs() {
		$file = self::get_log_file_path();
		if ( file_exists( $file ) ) {
			unlink( $file );
		}
	}
}
