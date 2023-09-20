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
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 * @param      string $plugin_path The plugin path of this plugin.
	 */
	public function __construct( $plugin_name, $version, $plugin_path ) {

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
	 * @since    3.2.0
	 * @access   public
	 */
	public function settings_init() {
		register_setting( 'agp', 'agp_automatic' );
		register_setting( 'agp', 'agp_diphthongs' );
		register_setting( 'agp', 'agp_automatic_post' );
		register_setting( 'agp', 'agp_automatic_tax' );

		add_settings_section( 'agp_automatic_options', __( 'Automatic Conversion', 'agp' ), array(
			$this,
			'automatic_options_section',
		), 'agp' );
		add_settings_section( 'agp_custom_automatic_options', '', array(
			$this,
			'custom_automatic_options_section',
		), 'agp' );
		add_settings_section( 'agp_diphthongs_options', __( 'Diphthongs Conversion', 'agp' ), array(
			$this,
			'diphthongs_options_section',
		), 'agp' );

		add_settings_field( 'agp_automatic', __( 'Do you want all new permalinks to be converted to greeklish?', 'agp' ), array(
			$this,
			'automatic_option',
		), 'agp', 'agp_automatic_options' );
		add_settings_field( 'agp_automatic_post', __( 'Which post types to be automatically converted?', 'agp' ), array(
			$this,
			'automatic_posts_option',
		), 'agp', 'agp_custom_automatic_options' );
		add_settings_field( 'agp_automatic_tax', __( 'Which taxonomies to be automatically converted?', 'agp' ), array(
			$this,
			'automatic_taxonomies_option',
		), 'agp', 'agp_custom_automatic_options' );
		add_settings_field( 'agp_diphthongs', __( 'How do you want diphthongs to be converted?', 'agp' ), array(
			$this,
			'diphthongs_option',
		), 'agp', 'agp_diphthongs_options' );
	}

	/**
	 * Adds the admin page content
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function options_page_content() {

		$tabs = array(
			[
				'id'       => 'permalink_settings',
				'name'     => __( 'Settings', 'agp' ),
				'template' => 'agp-settings-view.php',
			],
			[
				'id'       => 'generate_permalinks',
				'name'     => __( 'Convert old posts/terms', 'agp' ),
				'template' => 'agp-converter-view.php',
			],
		);

		include_once( 'partials/agp-admin-view.php' );

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
	 * Prints the settings sections of a page
	 *
	 * @since    3.3.0
	 * @access   public
	 *
	 * @param string $page
	 */
	public function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {

			if ( $section['title'] ) {
				echo "<h3>{$section['title']}</h3>\n";
			}
			echo "<div id='{$section['id']}' class='card'>";

			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '<table class="form-table">';
			do_settings_fields( $page, $section['id'] );
			echo '</table>';
			echo '</div>';
		}
	}

	/**
	 * Adds the customization's section content
	 *
	 * @since    3.2.0
	 * @access   public
	 */
	public function automatic_options_section() {
	}

	/**
	 * Adds the customization's section content
	 *
	 * @since    3.3.0
	 * @access   public
	 */
	public function custom_automatic_options_section() {
		?>
		<p>
			<strong><?php echo __( 'Heads-up!', 'agp' ); ?></strong>
			<?php echo __( 'Choosing post types or taxonomies for automatic conversion will decrease the maximum length of the slugs from 200 characters to about 50, due to limitations from WordPress. Set the settings to "All post types" and "All taxonomies" if you want to keep slugs at 200 characters maximum.', 'agp' ) ?>
		</p>
		<?php
	}

	/**
	 * Adds the customization's section content
	 *
	 * @since    3.2.0
	 * @access   public
	 */
	public function diphthongs_options_section() {
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
	 * Adds the option of which posts types to autoconvert
	 *
	 * @since    3.2.0
	 * @access   public
	 */
	public function automatic_posts_option() {

		include_once( 'partials/agp-automatic-posts-option-view.php' );

	}

	/**
	 * Adds the option of which taxonomies to autoconvert
	 *
	 * @since    3.2.0
	 * @access   public
	 */
	public function automatic_taxonomies_option() {

		include_once( 'partials/agp-automatic-taxonomies-option-view.php' );

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
	 * Callback for wp_unique_post_slug hook
	 * Checks if automatic conversion is enabled and post type is selected and then calls convertSlug function
	 *
	 * @since    3.2.0
	 * @access   public
	 *
	 * @param    string $slug The current slug
	 * @param   int $post_ID
	 * @param   string $post_status
	 * @param   string $post_type
	 *
	 * @return   string        The converted slug in greeklish
	 */
	public function greeklish_post_permalinks( $slug, $post_ID, $post_status, $post_type ) {
		$is_automatic_enabled  = get_option( 'agp_automatic' );
		$post_types_selected   = get_option( 'agp_automatic_post' );
		$is_post_type_selected = false;

		if ( $post_types_selected ) {
			foreach ( $post_types_selected as $post_type_selected ) {
				if ( $post_type === $post_type_selected ) {
					$is_post_type_selected = true;
					break;
				}
				if ( $post_type_selected === 'all_options' ) {
					$is_post_type_selected = true;
					break;
				}
			}
		}
		if ( $is_automatic_enabled && $is_post_type_selected ) {
			$slug = urldecode( $slug );
			$slug = Agp_Converter::convertSlug( $slug );
			$slug = urlencode( $slug );
		}

		return $slug;
	}

	/**
	 * Callback for wp_unique_term_slug hook
	 * Checks if automatic conversion is enabled and taxonomy is selected and then calls convertSlug function
	 *
	 * @since    3.2.0
	 * @access   public
	 *
	 * @param    string $slug The current slug
	 * @param   object $term
	 *
	 * @return   string        The converted slug in greeklish
	 */
	public function greeklish_term_permalinks( $slug, $term ) {
		$is_automatic_enabled = get_option( 'agp_automatic' );
		$taxonomies_selected  = get_option( 'agp_automatic_tax' );
		$taxonomy             = $term->taxonomy;
		$is_taxonomy_selected = false;
		if ( $taxonomies_selected ) {
			foreach ( $taxonomies_selected as $taxonomy_selected ) {
				if ( $taxonomy_selected === 'all_options' ) {
					$is_taxonomy_selected = true;
					break;
				}
				if ( $taxonomy === $taxonomy_selected ) {
					$is_taxonomy_selected = true;
				}
			}
		}
		if ( $is_automatic_enabled && $is_taxonomy_selected ) {
			$slug = urldecode( $slug );
			$slug = Agp_Converter::convertSlug( $slug );
			$slug = urlencode( $slug );
		}

		return $slug;
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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/agp-admin.min.css', array(), $this->version, 'all' );
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
		wp_enqueue_script( 'agp-admin', plugin_dir_url( __FILE__ ) . 'js/admin.min.js', array( 'jquery', 'select2' ), $this->version, true  );
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
	
	/**
	* Marks plugin compatible for WooCommerce HPOS
	*
	* @link https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	* @return void
	*/
   public function declare_hpos_compatability() {
	   if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		   \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->plugin_path, true );
	   }
   }



}