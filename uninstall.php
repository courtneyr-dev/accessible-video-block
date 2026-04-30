<?php
/**
 * Fired when the plugin is uninstalled.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load the Uninstall class directly — no autoloader at uninstall time.
require_once __DIR__ . '/includes/Uninstall.php';

\CourtneyR\AccessibleVideoBlock\Uninstall::run();
