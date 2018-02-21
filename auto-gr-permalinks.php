<?php

/*
Plugin Name: AutoConvert Greeklish Permalinks
Plugin URI: https://github.com/dimavroudis/AutoConvert-Greeklish-Permalink
Description: Convert Greek characters to Latin on all your site's permalinks instantly. Supports: Custom Post Types and Taxonomies, WooCommerce
Version: 1.3.6
Author: Dimitris Mavroudis
Author URI: https://www.dimitrismavroudis.gr
License: GPL2
Text Domain: autoconvert-greeklish-permalinks
Domain Path: /languages
*/

defined('ABSPATH') or die ("Oops! This is a WordPress plugin and should not be called directly.\n");

register_activation_hook( __FILE__, 'auto_gr_permalinks_set_options_on_activation' );

add_action( 'plugins_loaded', 'auto_gr_permalinks_load_plugin_textdomain' );
add_action( 'admin_enqueue_scripts', 'auto_gr_permalinks_admin_css_js' );
add_filter( 'plugin_action_links', 'auto_gr_permalinks_action_links', 10, 5 );

// Load main functionality
require plugin_dir_path( __FILE__ ) . 'includes/auto-gr-permalinks.php';
// Load functions for settings page
require plugin_dir_path( __FILE__ ) . 'includes/auto-gr-permalinks-options.php';

// Load translations
function auto_gr_permalinks_load_plugin_textdomain() {
	load_plugin_textdomain( 'autoconvert-greeklish-permalinks', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
}

// Set custom links in plugin page
function auto_gr_permalinks_action_links( $actions, $plugin_file ) {

	static $plugin;

	if ( ! isset( $plugin ) ) {
		$plugin = plugin_basename( __FILE__ );
	}
	if ( $plugin == $plugin_file ) {
		$settings  = array(
			'settings' => '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=auto_gr_permalinks' ) ) . '">' . _( 'Settings' ) . '</a>',
		);
		$site_link = array(
			'support' => '<a href="https://github.com/dimavroudis/AutoConvert-Greeklish-Permalink" target="_blank">GitHub</a>',
		);
		$actions = array_merge( $site_link, $actions );
		$actions = array_merge( $settings, $actions );
	}

	return $actions;

}

// Set options on activation
function auto_gr_permalinks_set_options_on_activation() {

	// Upgrade 1.3 Options
	if ( get_option( 'auto_gr_permalinks_automatic' ) === false ) {
		add_option ( 'auto_gr_permalinks_automatic', 'enabled' );
	}
	if ( get_option( 'auto_gr_permalinks_dipthongs' ) === false ) {
		add_option ( 'auto_gr_permalinks_diphthongs', 'enabled' );
	}
}

// Update CSS within in Admin
function auto_gr_permalinks_admin_css_js( $hook ) {
	if ( $hook != 'settings_page_auto_gr_permalinks' ) {
		return;
	}
	wp_enqueue_style( 'auto_gr_permalinks_select2_styles', plugins_url( 'select2/select2.min.css', __FILE__ ) );
	wp_enqueue_style( 'auto_gr_permalinks_admin_styles', plugins_url( 'includes/auto-gr-permalinks-admin.css', __FILE__ ) );

	wp_enqueue_script( 'auto_gr_permalinks_select2_js', plugins_url( 'select2/select2.min.js', __FILE__ ) );
}