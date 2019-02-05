<?php

/**
 * All the upgrade functions.
 *
 *
 * @since      2.0.2
 * @package    Agp
 * @subpackage Agp/includes
 *
 */

class Agp_Upgrade {

	/**
	 * Check previous version, call upgrades and update version
	 *
	 * @since    2.0.2
	 */
	public function upgrade() {

		$version = get_option( 'agp_version' );
		if ( ! $version ) {
			self::upgrade_v2();
		} elseif ( $version === '2.0.0' || $version === '2.0.1' ) {
			self::upgrade_v2_0_2();
		}

		if ( $version !== AGP_VERSION || ! $version ) {
			update_option( 'agp_version', defined( 'AGP_VERSION' ) ? AGP_VERSION : '2.0.0' );
		}

	}

	/**
	 * Upgrade option from 1.3.* to 2.0.0+
	 *
	 * @since    2.0.2
	 */
	public static function upgrade_v2() {
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

	/**
	 * Upgrade option to 2.0.2+
	 *
	 * @since    2.0.2
	 */
	public static function upgrade_v2_0_2() {

		if ( ! get_option( 'agp_automatic' ) ) {
			update_option( 'agp_automatic', 'enabled' );
		}
		if ( ! get_option( 'agp_diphthongs' ) ) {
			update_option( 'agp_diphthongs', 'disabled' );
		}
	}
}