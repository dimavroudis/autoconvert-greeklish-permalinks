<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://mavrou.gr
 * @since             2.0.0
 * @package           Agp
 *
 * @wordpress-plugin
 * Plugin Name:             AutoConvert Greeklish Permalinks
 * Plugin URI:              https://github.com/dimavroudis/AutoConvert-Greeklish-Permalink
 * Description:             Convert Greek characters to Latin on all your site's permalinks instantly.
 * Version:                 4.2.0
 * Author:                  Dimitris Mavroudis
 * Author URI:              http://mavrou.gr/
 * License:                 GPL-2.0+
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:             agp
 * Domain Path:             /languages
 * WC requires at least:    2.0.0
 * WC tested up to:         8.9.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AGP_VERSION', '4.2.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/agp-activator.php
 */
function activate_agp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/agp-activator.php';
	Agp_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_agp' );

//add_action( 'upgrader_process_complete', 'upgrade_agp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/agp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_agp() {

	$plugin_path = plugin_basename( __FILE__ );

	$plugin = new Agp( $plugin_path );
	$plugin->run();

}

run_agp();
