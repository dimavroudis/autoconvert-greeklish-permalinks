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
	 * Adds the admin page content
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function options_page_content() {

		include_once( 'partials/agp-admin-view.php' );

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

		$options   = get_option( 'agp_automatic' );
		$automatic = $options === 'enabled' ? 'checked' : '';

		?>
		<label class="agp-switch">
			<input type="hidden" id="agpAutomatic" name="agp_automatic" value="disabled">
			<input type="checkbox" id="agpAutomatic" name="agp_automatic"
				   value="enabled" <?php echo $automatic; ?>>
			<span class="agp-slider round"></span>
		</label>
		<?php

	}

	/**
	 * Adds the Diphthongs conversion option
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function diphthongs_option() {

		$options = get_option( 'agp_diphthongs' );

		?>
		<p>
			<label for="agp_diphthongs_disable">
				<input type="radio" id="agp_diphthongs_disable"
					   name="agp_diphthongs" <?php echo $options !== 'enabled' ? 'checked' : ''; ?>
					   value="disabled"><b><?php _e( 'Simple conversion', 'agp' ) ?></b> <?php _e( 'For example "ει" becomes "ei", "οι" becomes "οi", "μπ" becomes "mp" etc', 'agp' ); ?>
			</label>
		</p>
		<p>
			<label for="agp_diphthongs_enable">
				<input type="radio" id="agp_diphthongs_enable"
					   name="agp_diphthongs" <?php echo $options === 'enabled' ? 'checked' : ''; ?>
					   value="enabled"><label
						for="agp_diphthongs_enable"><b><?php _e( 'Advanced conversion', 'agp' ) ?></b> <?php _e( 'For example "ει", "οι" becomes "i", "μπ" becomes "b" etc', 'agp' ); ?>
				</label>
		</p>

		<?php

	}

	/**
	 * Manages conversion progress notices
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public function admin_notices() {

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
			?>
			<div class="notice notice-info">
				<p><?php echo sprintf( __( 'Permalinks conversion is at %s', 'agp' ), $percentage_txt ); ?></p>
			</div>
			<?php
		}

		//Done
		$is_active = get_transient( 'agp_notice_dismiss' );
		if ( $log['status'] === 'done' && $is_active ) {
			$params  = array_merge( $_GET, array( 'agp_notice_dismiss' => false ) );
			$queries = http_build_query( $params );
			$url     = ( empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?' . $queries;
			?>
			<div class="notice notice-success">
				<p><?php echo '<b>' . __( 'Permalinks conversion is done!', 'agp' ) . '</b> <a style="float:right;" href="' . esc_url( $url ) . '">' . __( 'Dismiss', 'agp' ) . '</a>'; ?></p>
			</div>
			<?php
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
	 *  Converts all post types and taxonomies
	 *
	 * @since    2.0.0
	 * @access   public
	 *
	 * @param    array $post_types
	 * @param    array $taxonomies
	 *
	 * @return   boolean
	 */
	public function convert( $post_types, $taxonomies ) {

		$post_count = $term_count = 0;
		$items      = array();

		if ( ! empty( $post_types ) ) {
			$query = new WP_Query( array(
				'post_type'      => $post_types,
				'posts_per_page' => - 1,
			) );
			foreach ( $query->posts as $post ) {
				setlocale( LC_ALL, 'el_GR' );
				$current_post_name = urldecode( $post->post_name );
				if ( ! Agp_Converter::isValidSlug( $current_post_name ) ) {
					$items[] = $post;
					$post_count ++;
				}
			}
		}

		if ( ! empty( $taxonomies ) ) {
			$terms = get_terms( array(
				'taxonomy'   => $taxonomies,
				'hide_empty' => 0,
			) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					setlocale( LC_ALL, 'el_GR' );
					$current_term_slug = urldecode( $term->slug );
					if ( ! Agp_Converter::isValidSlug( $current_term_slug ) ) {
						$items[] = $term;
						$term_count ++;
					}
				}
			}
		}

		if ( ! empty( $items ) ) {
			$now = new DateTime();
			update_option( 'agp_conversion', array(
				'status'    => 'started',
				'started'   => $now->getTimestamp(),
				'ended'     => '',
				'converted' => array( 'posts' => 0, 'terms' => 0 ),
				'estimated' => array( 'posts' => $post_count, 'terms' => $term_count ),
				'errors'    => array(),

			) );
			set_transient( 'agp_notice_active', true );
			foreach ( $items as $item ) {
				$this->converter->push_to_queue( $item );
			}
			$this->converter->save()->dispatch();

			return true;
		} else {
			return false;
		}

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