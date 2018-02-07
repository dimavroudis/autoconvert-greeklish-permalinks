<?php

/*
Plugin Name: AutoConvert Greeklish Permalinks
Plugin URI: https://www.dimitrismavroudis.gr/plugins/auto_gr_permalinks
Description: Convert Greek characters to Latin (better known as greeklish). The plugin makes sure that every new permalink is greeklish and offers the option to convert all the old links with greeek characters to greeklish.
Version: 1.1.2
Author: Dimitris Mavroudis
Author URI: https://www.dimitrismavroudis.gr
*/

if ( is_admin() ) {
	require plugin_dir_path( __FILE__ ) . 'includes/auto-gr-permalinks-options.php';
}

require plugin_dir_path( __FILE__ ) . 'includes/auto-gr-permalinks.php';

add_filter( 'plugin_action_links', 'auto_gr_permalinks_action_links', 10, 5 );

function auto_gr_permalinks_action_links( $actions, $plugin_file ) {

	static $plugin;

	if ( ! isset( $plugin ) ) {

		$plugin = plugin_basename( __FILE__ );

	}
	if ( $plugin == $plugin_file ) {

		$settings = array(
			'settings' => '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=auto_gr_permalinks' ) ) . '">' . _('Settings') . '</a>',
		);
		$site_link = array(
			'support' => '<a href="https://github.com/dimavroudis/AutoConvert-Greeklish-Permalink" target="_blank">GitHub</a>',
		);

		$actions = array_merge( $site_link, $actions );
		$actions = array_merge( $settings, $actions );

	}
	return $actions;

}
