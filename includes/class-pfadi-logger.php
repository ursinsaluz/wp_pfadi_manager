<?php

class Pfadi_Logger {

	public static function log( $message, $level = 'info' ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true );
		}

		$log_entry = sprintf( '[Pfadi Manager] [%s] %s', strtoupper( $level ), $message );
		error_log( $log_entry );
	}
}
