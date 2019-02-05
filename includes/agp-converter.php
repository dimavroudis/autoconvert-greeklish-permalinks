<?php

/**
 * The main functionality of the plugin - All the conversion functions
 *
 *
 * @package    Agp
 * @subpackage Agp/admin
 *
 */

class Agp_Converter {

	/**
	 * All the greek letters and their latin counterparts
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $expressions All the greek letters and their latin counterparts
	 */
	protected $expressions;
	/**
	 * All the greek diphthongs and their latin counterparts
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $diphthongs All the greek diphthongs and their latin counterparts
	 */
	protected $diphthongs;
	/**
	 * Counts posts converted
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      int $post_count Counts posts converted
	 */
	protected $post_count;
	/**
	 * Counts terms converted
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      int $term_count All the greek diphthongs and their latin counterparts
	 */
	protected $term_count;
	/**
	 * All the errors that came up on posts conversion
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $post_errors All the errors that came up on posts conversion
	 */
	protected $post_errors;
	/**
	 * All the errors that came up on terms conversion
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $term_errors All the errors that came up on terms conversion
	 */
	protected $term_errors;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 *
	 */
	public function __construct() {

		$this->expressions = array(
			'/[αάΑΆ]/u'    => 'a',
			'/[βΒ]/u'      => 'v',
			'/[γΓ]/u'      => 'g',
			'/[δΔ]/u'      => 'd',
			'/[εέΕΈ]/u'    => 'e',
			'/[ζΖ]/u'      => 'z',
			'/[ηήΗΉ]/u'    => 'i',
			'/[θΘ]/u'      => 'th',
			'/[ιίϊΐΙΊΪ]/u' => 'i',
			'/[κΚ]/u'      => 'k',
			'/[λΛ]/u'      => 'l',
			'/[μΜ]/u'      => 'm',
			'/[νΝ]/u'      => 'n',
			'/[ξΞ]/u'      => 'x',
			'/[οόΟΌ]/u'    => 'o',
			'/[πΠ]/u'      => 'p',
			'/[ρΡ]/u'      => 'r',
			'/[σςΣ]/u'     => 's',
			'/[τΤ]/u'      => 't',
			'/[υύϋΰΥΎΫ]/u' => 'y',
			'/[φΦ]/iu'     => 'f',
			'/[χΧ]/u'      => 'ch',
			'/[ψΨ]/u'      => 'ps',
			'/[ωώ]/iu'     => 'o',
		);
		$this->diphthongs  = array(
			'/[αΑ][ιίΙΊ]/u'                             => 'e',
			'/[οΟΕε][ιίΙΊ]/u'                           => 'i',
			'/[αΑ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
			'/[αΑ][υύΥΎ]/u'                             => 'av',
			'/[εΕ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
			'/[εΕ][υύΥΎ]/u'                             => 'ev',
			'/[οΟ][υύΥΎ]/u'                             => 'ou',
			'/(^|\s)[μΜ][πΠ]/u'                         => '$1b',
			'/[μΜ][πΠ](\s|$)/u'                         => 'b$1',
			'/[μΜ][πΠ]/u'                               => 'b',
			'/[νΝ][τΤ]/u'                               => 'nt',
			'/[τΤ][σΣ]/u'                               => 'ts',
			'/[τΤ][ζΖ]/u'                               => 'tz',
			'/[γΓ][γΓ]/u'                               => 'ng',
			'/[γΓ][κΚ]/u'                               => 'gk',
			'/[ηΗ][υΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u'   => 'if$1',
			'/[ηΗ][υΥ]/u'                               => 'iu',
		);
		$this->term_count  = $this->post_count = 0;
		$this->term_errors = $this->post_errors = array();

	}

	/**
	 *  Converts all post types and taxonomies
	 *
	 * @since    2.0.0
	 * @access   public
	 *
	 * @param    array|boolean $post_types
	 * @param    array|boolean $taxonomies
	 *
	 */
	public function convertAll( $post_types, $taxonomies ) {
		if ( $post_types ) {
			$this->convertPosts( $post_types );
		}
		if ( $taxonomies ) {
			$this->convertTerms( $taxonomies );
		}
	}

	/**
	 *  Converts post types
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    array $post_types
	 *
	 */
	private function convertPosts( $post_types ) {
		$query = new WP_Query( array(
			'post_type'      => $post_types,
			'posts_per_page' => - 1,
		) );
		foreach ( $query->posts as $post ) {
			setlocale( LC_ALL, 'el_GR' );
			$current_post_name = urldecode( $post->post_name );
			if ( ! $this->isValidSlug( $current_post_name ) ) {
				$post_to_update              = array();
				$post_to_update['ID']        = $post->ID;
				$post_to_update['post_name'] = $this->convertSlug( $post->post_title );
				$is_converted                = wp_update_post( $post_to_update, true );
				if ( ! is_wp_error( $is_converted ) ) {
					$this->post_count ++;
				} else {
					$errors_message      = $is_converted->get_error_message();
					$this->post_errors[] = [ 'post_id' => $post->ID, 'message' => $errors_message ];
				}
			}
		}
	}

	/**
	 * Check if the slug provided is valid (greeklish) or needs conversion
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param    string $current_post_title The current post title
	 *
	 * @return   boolean
	 */
	public function isValidSlug( $current_post_title ) {

		$is_valid_slug = true;

		foreach ( $this->expressions as $key => $value ) {
			if ( preg_match( $key, $current_post_title ) ) {
				$is_valid_slug = false;
				break;
			}
		}

		return $is_valid_slug;

	}

	/**
	 *  Converts the slug to greeklish
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param    string $current_slug The current post slug
	 *
	 * @return   string        The converted slug in greeklish
	 */
	public function convertSlug( $current_slug ) {

		$diphthongs_status = get_option( 'agp_diphthongs' ) === 'enabled';

		if ( $diphthongs_status ) {
			$expressions = array_merge( $this->diphthongs, $this->expressions );
		} else {
			$expressions = $this->expressions;
		}

		$current_slug = preg_replace( array_keys( $expressions ), array_values( $expressions ), $current_slug );

		return $current_slug;

	}

	/**
	 *  Converts terms
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    array $taxonomies
	 *
	 */
	private function convertTerms( $taxonomies ) {
		$terms = get_terms( array(
			'taxonomy'   => $taxonomies,
			'hide_empty' => 0,
		) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				setlocale( LC_ALL, 'el_GR' );
				$current_term_slug = urldecode( $term->slug );
				if ( ! $this->isValidSlug( $current_term_slug ) ) {
					$new_term_slug = $this->convertSlug( $term->name );
					$is_converted  = $this->updateTerm( $term->term_id, $term->taxonomy, $new_term_slug );
					if ( ! is_wp_error( $is_converted ) ) {
						$this->term_count ++;
					} else {
						$errors_message      = $is_converted->get_error_message();
						$this->term_errors[] = [
							'term_id'  => $term->term_id,
							'taxonomy' => $term->taxonomy,
							'message'  => $errors_message,
						];
					}
				}
			}
		}
	}

	/**
	 *  Updates terms/taxonomies
	 *
	 *  Manages WpError response
	 *
	 * @since    2.0.0
	 * @access   public
	 *
	 * @param    string $term_id
	 * @param    string $taxonomy
	 * @param    string $new_slug
	 *
	 * @return   boolean|WP_Error
	 */
	public function updateTerm( $term_id, $taxonomy, $new_slug ) {
		$args     = array(
			'slug' => $new_slug,
		);
		$termData = wp_update_term( $term_id, $taxonomy, $args );
		if ( ! is_wp_error( $termData ) ) {
			return true;
		} elseif ( $termData->get_error_code() === 'duplicate_term_slug' ) {
			$new_slug = $new_slug . '-2';

			return $this->updateTerm( $term_id, $taxonomy, $new_slug );
		} else {
			return $termData;
		}
	}

	/**
	 * @return int
	 */
	public function getPostCount() {
		return $this->post_count;
	}

	/**
	 * @return int
	 */
	public function getTermCount() {
		return $this->term_count;
	}

	/**
	 * @return array
	 */
	public function getTermErrors() {
		return $this->term_errors;
	}

	/**
	 * @return array
	 */
	public function getPostErrors() {
		return $this->post_errors;
	}

}