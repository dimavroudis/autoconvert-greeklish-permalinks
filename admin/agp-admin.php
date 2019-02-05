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
	 * Instance of Converter to be used in admin
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      object $converter Instance of Converter to be used in admin
	 */
	private $converter;

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

		if ( isset( $_POST['convert-button'] ) ) {

			$posts_type = isset( $_POST['post-type'] ) ? $_POST['post-type'] : false;
			$taxonomy   = isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : false;

			$this->converter->convertAll( $posts_type, $taxonomy );

			$posts_count = $this->converter->getPostCount();
			$terms_count = $this->converter->getTermCount();

			if ( $posts_count || $terms_count ) {
				$posts_txt   = $posts_count == 1 ? __( 'post', 'agp' ) : __( 'posts', 'agp' );
				$terms_txt   = $terms_count == 1 ? __( 'term', 'agp' ) : __( 'terms', 'agp' );
				$text_format = __( 'Permalinks successfully generated for <b>%d %s</b> and <b>%d %s</b>', 'agp' );
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo sprintf( $text_format, $posts_count, $posts_txt, $terms_count, $terms_txt ); ?></p>
				</div>
				<?php
			} else {
				?>
				<div class="notice notice-info is-dismissible">
					<p><?php _e( '<b>No permalink was converted.</b> All your permalinks were already in latin characters.', 'agp' ) ?></p>
				</div>
				<?php
			}
			if ( ! empty( $this->converter->getPostErrors() ) ) {
				foreach ( $this->converter->getPostErrors() as $error ) {
					$text_format = __( 'The post "<a href="%s">%s</a>" was not converted. %s', 'agp' );
					$post_id     = $error['post_id'];
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo sprintf( $text_format, get_edit_post_link( $post_id ), get_the_title( $post_id ), $error['message'] ); ?></p>
					</div>
					<?php
				}
			}
			if ( ! empty( $this->converter->getTermErrors() ) ) {
				foreach ( $this->converter->getTermErrors() as $error ) {
					$text_format = __( 'The term "<a href="%s">%s</a>" was not converted. %s', 'agp' );
					$term        = get_term( $error['term_id'], $error['taxonomy'] );
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo sprintf( $text_format, get_edit_term_link( $error['term_id'], $error['taxonomy'] ), $term->name, $error['message'] ); ?></p>
					</div>
					<?php
				}
			}
		}

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
		//TODO: Add some intro text or not. Still thinking about it...
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
			$current_post_title = $this->converter->convertSlug( $current_post_title );
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
	public function enqueue_styles() {

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
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );

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