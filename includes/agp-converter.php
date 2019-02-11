<?php

/**
 * The class responsible for conversion
 *
 * @package    Agp
 * @subpackage Agp/includes
 *
 * @since    3.0.0
 *
 */
class Agp_Converter extends WP_Background_Process {

	/**
	 * All the greek letters and their latin counterparts
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $expressions All the greek letters and their latin counterparts
	 */
	protected static $expressions = array(
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

	/**
	 * All the greek diphthongs and their latin counterparts
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $diphthongs All the greek diphthongs and their latin counterparts
	 */
	protected static $diphthongs = array(
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

	/**
	 * @since    3.0.0
	 * @access   protected
	 * @var      string     Action id
	 */
	protected $action = 'agp_convert';

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
	public static function isValidSlug( $current_post_title ) {
		setlocale( LC_ALL, 'el_GR' );
		$current_post_title = urldecode( $current_post_title );

		$is_valid_slug = true;

		foreach ( self::$expressions as $key => $value ) {
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
	public static function convertSlug( $current_slug ) {

		setlocale( LC_ALL, 'el_GR' );
		$current_slug = urldecode( $current_slug );

		$diphthongs_status = get_option( 'agp_diphthongs' ) === 'enabled';

		if ( $diphthongs_status ) {
			$expressions = array_merge( self::$diphthongs, self::$expressions );
		} else {
			$expressions = self::$expressions;
		}

		$current_slug = preg_replace( array_keys( $expressions ), array_values( $expressions ), $current_slug );

		return $current_slug;

	}

	/**
	 *  Updates terms/taxonomies
	 *
	 *  Manages WpError response
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    string $term_id
	 * @param    string $taxonomy
	 * @param    string $new_slug
	 *
	 * @return   boolean|WP_Error
	 */
	public static function updateTerm( $term_id, $taxonomy, $new_slug ) {
		$args     = array(
			'slug' => $new_slug,
		);
		$termData = wp_update_term( $term_id, $taxonomy, $args );
		if ( ! is_wp_error( $termData ) ) {
			return true;
		} elseif ( $termData->get_error_code() === 'duplicate_term_slug' ) {
			$new_slug = $new_slug . '-2';

			return self::updateTerm( $term_id, $taxonomy, $new_slug );
		} else {
			return $termData;
		}
	}

	/**
	 * Queries the database for the items to convert, adds them on the queue, saves and initializes logger
	 *
	 * @since    2.0.0
	 * @access   public
	 *
	 * @param    array $post_types
	 * @param    array $taxonomies
	 *
	 * @return   boolean
	 */
	public function prepareData( $post_types, $taxonomies ) {

		global $wpdb;
		$post_count = $term_count = 0;

		if ( ! empty( $post_types ) ) {
			$sql_post_types = '';
			foreach ( $post_types as $index => $post_type ) {
				if ( $index != 0 ) {
					$sql_post_types .= 'OR ';
				}
				$sql_post_types .= "p.post_type =  '$post_type' ";
			}

			$sql = "SELECT ID, post_name
				FROM $wpdb->posts as p
				WHERE 1=1 AND ($sql_post_types)
				AND (p.post_status = 'publish'
				OR p.post_status = 'future'
				OR p.post_status = 'draft'
				OR p.post_status = 'pending'
				OR p.post_status = 'private')";

			$post_query = $wpdb->get_results( $sql );

			if ( $post_query ) {
				foreach ( $post_query as $post ) {
					if ( ! self::isValidSlug( $post->post_name ) ) {
						$post = (object) array_merge( array( 'type' => 'post' ), (array) $post );
						$this->push_to_queue( $post );
						$post_count ++;
					}
				}
			}
		}

		if ( ! empty( $taxonomies ) ) {
			$sql_taxonomies = '';
			foreach ( $taxonomies as $index => $taxonomy ) {
				if ( $index != 0 ) {
					$sql_taxonomies .= ', ';
				}
				$sql_taxonomies .= "'$taxonomy'";
			}

			$sql = "SELECT t.term_id, t.slug, tt.taxonomy
				FROM $wpdb->terms AS t 
				INNER JOIN $wpdb->term_taxonomy AS tt
				ON t.term_id = tt.term_id
				WHERE tt.taxonomy IN ($sql_taxonomies)";

			// The Term Query
			$term_query = $wpdb->get_results( $sql );

			foreach ( $term_query as $term ) {
				if ( ! self::isValidSlug( $term->slug ) ) {
					$term = (object) array_merge( array( 'type' => 'term' ), (array) $term );
					$this->push_to_queue( $term );
					$term_count ++;
				}
			}
		}

		if ( $post_count !== 0 || $term_count !== 0 ) {
			$now = time();
			update_option( 'agp_conversion', array(
				'status'    => 'started',
				'started'   => $now,
				'ended'     => '',
				'converted' => array( 'posts' => 0, 'terms' => 0 ),
				'estimated' => array( 'posts' => $post_count, 'terms' => $term_count ),
				'errors'    => array(),
			) );
			set_transient( 'agp_notice_active', true );
			$this->save();

			return true;
		} else {
			return false;
		}

	}

	/**
	 * Save queue
	 *
	 * @return $this
	 */
	public function save() {

		if ( ! empty( $this->data ) ) {
			$chuncks = array_chunk( $this->data, 1000, true );
			foreach ( $chuncks as $chunck ) {
				$key = $this->generate_key();
				update_site_option( $key, $chunck );
			}
		}

		return $this;
	}

	/**
	 * Background Conversion Process
	 *
	 * @since    3.0.0
	 * @access   protected
	 *
	 * @param    stdClass $item Post or term to convert
	 *
	 * @return mixed
	 */
	protected function task( $item ) {

		$log = get_option( 'agp_conversion' );
		error_log( print_r( $item, true ) );
		if ( $item->type === 'post' ) {
			$post_to_update              = array();
			$post_to_update['ID']        = $item->ID;
			$post_to_update['post_name'] = self::convertSlug( $item->post_name );
			$is_converted                = wp_update_post( $post_to_update, true );
			error_log( $is_converted );
			if ( ! is_wp_error( $is_converted ) ) {
				$log['converted']['posts'] ++;
			} else {
				$error_message   = $is_converted->get_error_message();
				$error           = [
					'type'    => 'post',
					'id'      => $item->ID,
					'message' => $error_message,
				];
				$log['errors'][] = $error;
			}
		}

		if ( $item->type === 'term' ) {
			$new_term_slug = self::convertSlug( $item->slug );
			$is_converted  = self::updateTerm( $item->term_id, $item->taxonomy, $new_term_slug );

			if ( ! is_wp_error( $is_converted ) ) {
				$log['converted']['terms'] ++;
			} else {
				$error_message   = $is_converted->get_error_message();
				$error           = [
					'type'    => 'term',
					'id'      => $item->term_id,
					'message' => $error_message,
				];
				$log['errors'][] = $error;
			}
		}

		update_option( 'agp_conversion', $log );

		return false;
	}

	/**
	 * Complete
	 *
	 * @since    3.0.0
	 * @access   protected
	 */
	protected function complete() {
		parent::complete();

		$log = get_option( 'agp_conversion' );

		$now           = time();
		$log['status'] = 'done';
		$log['ended']  = $now;

		update_option( 'agp_conversion', $log );

	}

}