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

class Agp_Upgrade
{

	/**
	 * Check previous version, call upgrades and update version
	 *
	 * @since    3.2.0
	 */
	public function upgrade()
	{

		$version_old = get_option('agp_version');
		$version_new = AGP_VERSION;
		if (version_compare($version_old, $version_new, '<')) {
			//Upgrades
			self::upgrade_v2($version_old);
			self::upgrade_v2_0_2($version_old);
			self::upgrade_v3_2_0($version_old);
			//Update version
			update_option('agp_version', $version_new);
		}
	}

	/**
	 * Upgrade option from 1.3.* to 2.0.0+
	 *
	 * @since    3.2.0
	 *
	 * @param string $version_old
	 */
	public static function upgrade_v2($version_old)
	{
		if (!$version_old) {
			if (get_option('auto_gr_permalinks_automatic')) {
				update_option('agp_automatic', get_option('auto_gr_permalinks_automatic', 'disabled'));
			}
			if (get_option('auto_gr_permalinks_diphthongs')) {
				update_option('agp_diphthongs', get_option('auto_gr_permalinks_diphthongs', 'disabled'));
			}
			delete_option('auto_gr_permalinks_automatic');
			delete_option('auto_gr_permalinks_diphthongs');
			delete_option('auto_gr_permalinks_dipthongs');
		}
	}

	/**
	 * Upgrade option to 2.0.2+
	 *
	 * @since    3.2.0
	 *
	 * @param string $version_old
	 */
	public static function upgrade_v2_0_2($version_old)
	{
		if ($version_old === '2.0.0' || $version_old === '2.0.1') {
			if (!get_option('agp_automatic')) {
				update_option('agp_automatic', 'enabled');
			}
			if (!get_option('agp_diphthongs')) {
				update_option('agp_diphthongs', 'disabled');
			}
		}
	}

	/**
	 * Upgrade options to 3.2.0+
	 *
	 * @since    3.2.0
	 *
	 * @param string $version_old
	 */
	public static function upgrade_v3_2_0($version_old)
	{
		if (version_compare($version_old, '3.2.0', '<')) {
			if (!get_option('agp_automatic_post')) {
				update_option('agp_automatic_post', array('all_options'));
			}
			if (!get_option('agp_automatic_tax')) {
				update_option('agp_automatic_tax', array('all_options'));
			}
		}
	}

	/**
	 * Upgrade options to 4.0.0
	 *
	 * @since    4.0.0
	 *
	 * @param string $version_old
	 */
	public static function upgrade_v4_0_0($version_old)
	{
		if (version_compare($version_old, '4.0.0', '<')) {
			delete_option('agp_conversion');
		}
	}
}
