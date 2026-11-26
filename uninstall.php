<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// The plugin is not loaded during uninstall, so load the class that owns the cleanup.
require_once __DIR__ . '/includes/agp.php';

Agp::clean();
