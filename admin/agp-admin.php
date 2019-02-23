<?php

/**
 * The admin-specific functionality of the plugin.
 *
 *
 * @package    Agp
 * @subpackage Agp/admin
 *
 */

class Agp_Admin {

	/**
	 * The plugin path of this plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string $plugin_path The plugin path of this plugin.
	 */
	protected $plugin_path;
	/**
	 * An instance of the converter class
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var Agp_Converter
	 */
	protected $converter;
	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 * @param      string $plugin_path The plugin path of this plugin.
	 */
	public function __construct( $plugin_name, $version, $plugin_path ) {

		$this->converter   = new Agp_Converter();
		$this->plugin_name = $plugin_name;
		$this->plugin_path = $plugin_path;
		$this->version     = $version;

	}

	/**
	 * Adds the option in WordPress Admin menu
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function options_page() {
		add_options_page( __( 'AutoConvert Greeklish Permalinks', 'agp' ), __( 'Convert Greek Permalinks', 'agp' ), 'manage_options', 'agp', array(
			$this,
			'options_page_content',
		) );
	}

	/**
	 * Initialize the settings page
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function settings_init() {
		register_setting( 'agp', 'agp_automatic' );
		register_setting( 'agp', 'agp_diphthongs' );
		add_settings_section( 'agp_custom', '', array( $this, 'custom_section_content' ), 'agp' );
		add_settings_field( 'agp_automatic', __( 'Do you want all new permalinks to be converted to greeklish?', 'agp' ), array(
			$this,
			'automatic_option',
		), 'agp', 'agp_custom' );
		add_settings_field( 'agp_diphthongs', __( 'How do you want diphthongs to be converted?', 'agp' ), array(
			$this,
			'diphthongs_option',
		), 'agp', 'agp_custom' );
	}

	/**
	 * Adds the admin page content
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function options_page_content() {

		$this->on_convert();

		include_once( 'partials/agp-admin-view.php' );

	}

	/**
	 * Initializes conversion on POST
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function on_convert() {
		if ( isset( $_POST['convert-button'] ) ) {

			$posts_type = isset( $_POST['post-types'] ) ? $_POST['post-types'] : array();
			$taxonomy   = isset( $_POST['taxonomies'] ) ? $_POST['taxonomies'] : array();

			$has_posts = $this->converter->prepareData( $posts_type, $taxonomy );

			if ( ! $has_posts ) {
				$message = '<b>' . __( 'All your permalinks were already in greeklish.', 'agp' ) . '</b>';
				echo $this->admin_notice( 'info', $message, true );
			} else {
				$this->converter->dispatch();
				$message = '<b>' . __( 'Permalinks conversion has started in the background.', 'agp' ) . '</b>';
				echo $this->admin_notice( 'success', $message, true );
			}
		}
	}

	/**
	 * Template for admin notices
	 *
	 * @since    3.0.0
	 * @access   public
	 *
	 * @param string $severity
	 * @param string $content
	 * @param bool $is_dismissible
	 *
	 * @return string
	 */
	public function admin_notice( $severity, $content, $is_dismissible = false ) {

		$is_dismissible = $is_dismissible ? 'is-dismissible' : '';

		$html = '<div class="notice notice-' . $severity . ' ' . $is_dismissible . '"><p>' . $content . '</p></div>';

		return $html;

	}

	/**
	 * Adds the customization's section content
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function custom_section_content() {
	}

	/**
	 * Adds the Automatic conversion option
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function automatic_option() {

		include_once( 'partials/agp-automatic-option-view.php' );

	}

	/**
	 * Adds the Diphthongs conversion option
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function diphthongs_option() {

		include_once( 'partials/agp-diphthongs-option-view.php' );

	}

	/**
	 * Manages conversion progress notices
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public function conversion_progress_notice() {

		if ( isset( $_GET['agp_notice_dismiss'] ) ) {
			delete_transient( 'agp_notice_dismiss' );
		}

		$log = get_option( 'agp_conversion' );

		//In progress
		if ( $log && $log['status'] === 'started' ) {

			$count_complete = $log['converted']['posts'] + $log['converted']['terms'];
			$count_estimate = $log['estimated']['posts'] + $log['estimated']['terms'];

			$percentage     = round( $count_complete / $count_estimate * 100 );
			$percentage_txt = $percentage . '%';

			$message = sprintf( __( 'Permalinks conversion is at %s', 'agp' ), $percentage_txt );
			echo $this->admin_notice( 'info', $message );
		}

		//Done
		$is_active = get_transient( 'agp_notice_dismiss' );
		if ( $log['status'] === 'done' && $is_active ) {
			$params  = array_merge( $_GET, array( 'agp_notice_dismiss' => false ) );
			$queries = http_build_query( $params );
			$url     = ( empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?' . $queries;
			$message = '<b>' . __( 'Permalinks conversion is done!', 'agp' ) . '</b> <a style="float:right;" href="' . esc_url( $url ) . '">' . __( 'Dismiss', 'agp' ) . '</a>';
			echo $this->admin_notice( 'success', $message );
		}

	}

	/**
	 * Callback for sanitize_title hook
	 * Checks if automatic conversion is enabled and then calls convertSlug function
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param    string $current_post_title The current post title
	 *
	 * @return   string        The converted slug in greeklish
	 */
	public function sanitize_title_hook( $current_post_title ) {
		if ( get_option( 'agp_automatic' ) === 'enabled' ) {
			$current_post_title = Agp_Converter::convertSlug( $current_post_title );
		}

		return $current_post_title;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @param   string      The hook
	 */
	public function enqueue_styles( $hook ) {
		if ( $hook != 'settings_page_agp' ) {
			return;
		}
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/agp-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @param   string      The hook
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook != 'settings_page_agp' ) {
			return;
		}
		wp_enqueue_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Set custom links in plugins page
	 *
	 * @since    1.0.0
	 *
	 * @param   array $actions
	 * @param   string $plugin_file
	 *
	 * @return  array    $actions
	 */
	public function action_links( $actions, $plugin_file ) {

		if ( $plugin_file === $this->plugin_path ) {
			$settings  = array(
				'settings' => '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=agp&tab=permalink_settings' ) ) . '">' . __( 'Settings', 'agp' ) . '</a>',
			);
			$converter = array(
				'converter' => '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=agp&tab=generate_permalinks' ) ) . '">' . __( 'Convert old permalinks', 'agp' ) . '</a>',
			);
			$actions   = array_merge( $converter, $actions );
			$actions   = array_merge( $settings, $actions );
		}

		return $actions;

	}

}