<?php

/*
Plugin Name: AutoConvert Greeklish Permalinks
Plugin URI: https://www.dimitrismavroudis.gr/plugins/auto_gr_permalinks
Description: Convert Greek characters to Latin on all your site's permalinks instantly.
Version: 1.3.1
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


function auto_gr_permalinks_set_options_on_activation() {
	if ( get_option( 'auto_gr_permalinks_automatic' ) === false ) {
		update_option( 'auto_gr_permalinks_automatic', 'enabled' );
	}
	if ( get_option( 'auto_gr_permalinks_dipthongs' ) === 'enabled' ) {
		update_option( 'auto_gr_permalinks_diphthongs', 'enabled' );
	}
}
register_activation_hook( __FILE__, 'auto_gr_permalinks_set_options_on_activation' );

// Update CSS within in Admin
function auto_gr_permalinks_admin_css_js($hook) {
	if($hook != 'settings_page_auto_gr_permalinks') {
		return;
	}
	wp_enqueue_style('auto_gr_permalinks_admin_styles', plugins_url('includes/auto-gr-permalinks-admin.css', __FILE__) );
	wp_enqueue_style('auto_gr_permalinks_select2_styles', plugins_url('select2/select2.min.css', __FILE__) );
	wp_enqueue_script('auto_gr_permalinks_select2_js', plugins_url('select2/select2.min.js', __FILE__));
}
add_action('admin_enqueue_scripts', 'auto_gr_permalinks_admin_css_js');