<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0.0
 * @package    Agp
 * @subpackage Agp/includes
 *
 */
class Agp_Activator {

	/**
	 * Initialize settings
	 *
	 * @since    2.0.0
	 */
	public static function activate() {
		if ( ! get_option( 'agp_automatic' ) ) {
			update_option( 'agp_automatic', 'enabled' );
		}
		if ( ! get_option( 'agp_diphthongs' ) ) {
			update_option( 'agp_diphthongs', 'enabled' );
		}
	}


}
