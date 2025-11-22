<?php
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );
require_once( PFADI_MANAGER_PATH . 'includes/class-pfadi-db.php' );

echo "Applying database schema updates...\n";
$db = new Pfadi_DB();
$db->create_table();
echo "Done.\n";
