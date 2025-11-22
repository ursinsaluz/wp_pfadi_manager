<?php
require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/wp-load.php' );
echo "Admin Email: " . get_option( 'admin_email' ) . "\n";
