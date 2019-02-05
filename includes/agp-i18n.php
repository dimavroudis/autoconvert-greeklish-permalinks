<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://mavrou.gr
 * @since      1.0.0
 *
 * @package    Agp
 * @subpackage Agp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Agp
 * @subpackage Agp/includes
 *
 */
class Agp_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( 'agp', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );

	}


}
