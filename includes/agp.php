<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://mavrou.gr
 * @since      2.0.0
 *
 * @package    Agp
 * @subpackage Agp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      2.0.0
 * @package    Agp
 * @subpackage Agp/includes
 *
 */
class Agp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      Agp_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The plugin path of this plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string $plugin_path The plugin path of this plugin.
	 */
	protected $plugin_path;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.0.0
	 *
	 * @param    string $plugin_path
	 */
	public function __construct( $plugin_path ) {
		$this->version     = defined( 'AGP_VERSION' ) ? AGP_VERSION : '2.0.0';
		$this->plugin_name = 'agp';
		$this->plugin_path = $plugin_path;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->register_rest_api_endpoints();
		$this->upgrade_hook();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Agp_i18n. Defines internationalization functionality.
	 * - Agp_Admin. Defines all hooks for the admin area.
	 * - Agp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/agp-loader.php';

		/**
		 * The class responsible for updating your plugin options.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/agp-upgrade.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/agp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/agp-admin.php';

		/**
		 * The class responsible for conversions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/agp-converter.php';

		/**
		 *  The class responsible for the Rest API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/agp-endpoints.php';


		/**
		 *  The class responsible for the CLI
		 */
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/agp-cli.php';
		}

		$this->loader = new Agp_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Agp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Agp_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    3.2.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Agp_Admin( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_path() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'options_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_init' );

		$this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'action_links', 10, 5 );

		$this->loader->add_action( 'before_woocommerce_init', $plugin_admin, 'declare_hpos_compatability' );

		$post_types_selected = get_option( 'agp_automatic_post' );
		$taxonomies_selected = get_option( 'agp_automatic_tax' );
		if ( ( $post_types_selected && $taxonomies_selected ) && ! ( $post_types_selected[0] === 'all_options' && $taxonomies_selected[0] === 'all_options' ) ) {
			$this->loader->add_filter( 'wp_unique_post_slug', $plugin_admin, 'greeklish_post_permalinks', 10, 4 );
			$this->loader->add_filter( 'wp_unique_term_slug', $plugin_admin, 'greeklish_term_permalinks', 10, 2 );
		} else {
			$this->loader->add_filter( 'sanitize_title', $plugin_admin, 'sanitize_title_hook', 1 );
		}
	}

	/**
	 * Register all rest api endpoints
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function register_rest_api_endpoints() {

		$plugin_endpoints = new Agp_Endpoints( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_path() );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_endpoints, 'localize_scripts' );
		$this->loader->add_action( 'rest_api_init', $plugin_endpoints, 'register_routes' );
		
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the plugin path of the plugin.
	 *
	 * @since     2.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_plugin_path() {
		return $this->plugin_path;
	}

	/**
	 * The code that runs on updating your plugin options.
	 *
	 * @since     2.0.2
	 * @access   private
	 */
	public function upgrade_hook() {
		$plugin_upgrade = new Agp_Upgrade();
		$this->loader->add_action( 'admin_init', $plugin_upgrade, 'upgrade' );
	}

	/**
	 * The code that runs on updating your plugin options.
	 *
	 * @since     2.0.2
	 * @access   private
	 */
	public static function clean() {
		delete_option( 'agp_automatic' );
		delete_option( 'agp_automatic_post' );
		delete_option( 'agp_automatic_tax' );
		delete_option( 'agp_diphthongs' );
		delete_option( 'agp_conversion' );
		delete_transient( 'agp_notice_dismiss' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Agp_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}


}