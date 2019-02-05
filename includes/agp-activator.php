<?php

/**
 * Fired during plugin activation
 *
 * @link       https://mavrou.gr
 * @since      2.0.0
 *
 * @package    Agp
 * @subpackage Agp/includes
 */

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

		if (! get_option('agp_version') ) {
			self::upgrade_v2();
		}

		if ( ! get_option( 'agp_automatic' ) ) {
			update_option( 'agp_automatic', 'enabled' );
		}
		if ( ! get_option( 'agp_diphthongs' ) ) {
			update_option( 'agp_diphthongs', 'disabled' );
		}
		update_option( 'agp_version', defined( 'AGP_VERSION' ) ? AGP_VERSION : '2.0.0' );
	}

	/**
	 * Upgrade option from 1.3.* to 2.0.0
	 *
	 * @since    2.0.0
	 */
	private static function upgrade_v2() {
		if ( get_option( 'auto_gr_permalinks_automatic' ) ) {
			update_option( 'agp_automatic', get_option( 'auto_gr_permalinks_automatic', 'disabled' ) );
		}
		if ( get_option( 'auto_gr_permalinks_diphthongs' ) ) {
			update_option( 'agp_diphthongs', get_option( 'auto_gr_permalinks_diphthongs', 'disabled' ) );
		}
		delete_option( 'auto_gr_permalinks_automatic' );
		delete_option( 'auto_gr_permalinks_diphthongs' );
		delete_option( 'auto_gr_permalinks_dipthongs' );
	}

}
