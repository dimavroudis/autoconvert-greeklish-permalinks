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
	 * @since    3.2.0 Added polytonic characters
	 * @access   protected
	 * @var      array $expressions All the greek letters and their latin counterparts
	 */
	protected static $expressions = array(
		'/[ἀἁἈἉᾶἄἅἌἍἆἇἎἏἂἃἊἋᾳᾼᾴᾲᾀᾈᾁᾉᾷᾆᾎᾇᾏᾂᾊᾃᾋὰαάΑΆ]/u'	=> 'a',
		'/[βΒ]/u'      									=> 'v',
		'/[γΓ]/u'      									=> 'g',
		'/[δΔ]/u'      									=> 'd',
		'/[ἐἑἘἙἔἕἜἝἒἓἚἛὲεέΕΈ]/u'    					=> 'e',
		'/[ζΖ]/u'      									=> 'z',
		'/[ἠἡἨἩἤἥἬἭῆἦἧἮἯἢἣἪἫῃῌῄῂᾐᾑᾘᾙᾖᾗᾞᾟᾒᾒᾚᾛὴηήΗΉ]/u'   => 'i',
		'/[θΘ]/u'      									=> 'th',
		'/[ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻῒῗὶιίϊΐΙΊΪ]/u' 				=> 'i',
		'/[κΚ]/u'      									=> 'k',
		'/[λΛ]/u'      									=> 'l',
		'/[μΜ]/u'      									=> 'm',
		'/[νΝ]/u'      									=> 'n',
		'/[ξΞ]/u'     	 								=> 'x',
		'/[ὀὁὈὉὄὅὌὍὂὃὊὋὸοόΟΌ]/u'    					=> 'o',
		'/[πΠ]/u'      									=> 'p',
		'/[ρΡ]/u'     									=> 'r',
		'/[σςΣ]/u'     									=> 's',
		'/[τΤ]/u'      									=> 't',
		'/[ὐὑὙὔὕὝῦὖὗὒὓὛὺῒῧυύϋΰΥΎΫ]/u' 					=> 'y',
		'/[φΦ]/iu'     									=> 'f',
		'/[χΧ]/u'      									=> 'ch',
		'/[ψΨ]/u'      									=> 'ps',
		'/[ὠὡὨὩὤὥὬὭῶὦὧὮὯὢὣὪὫῳῼᾠᾡᾨᾩᾤᾥᾬᾭᾦᾧᾮᾯᾢᾣᾪᾫὼωώ]/iu'  => 'o',
	);

	/**
	 * All the greek diphthongs and their latin counterparts
	 *
	 * @since    1.0.0
	 * @since    3.2.0 Added polytonic characters
	 * @access   protected
	 * @var      array $diphthongs All the greek diphthongs and their latin counterparts
	 */
	protected static $diphthongs = array(
		'/[αΑ][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u'                        => 'ai',
		'/[οΟ][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u'                        => 'oi',
		'/[Εε][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u'                        => 'ei',
		'/[αΑ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
		'/[αΑ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u'                             => 'av',
		'/[εΕ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
		'/[εΕ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u'                             => 'ev',
		'/[οΟ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u'                             => 'ou',
		'/(^|\s)[μΜ][πΠ]/u'                         			 => '$1b',
		'/[μΜ][πΠ](\s|$)/u'                         			 => 'b$1',
		'/[μΜ][πΠ]/u'                               			 => 'b',
		'/[νΝ][τΤ]/u'                               			 => 'nt',
		'/[τΤ][σΣ]/u'                               			 => 'ts',
		'/[τΤ][ζΖ]/u'                               			 => 'tz',
		'/[γΓ][γΓ]/u'                               			 => 'ng',
		'/[γΓ][κΚ]/u'                               			 => 'gk',
		'/[ηΗ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u'   => 'if$1',
		'/[ηΗ][υΥ]/u'                               			 => 'iu',
	);

	/**
	 * @since    3.0.0
	 * @access   protected
	 * @var      string     Action id
	 */
	protected $action = 'agp_convert';

	/**
	 * Queries the database for the items to convert, adds them on the queue, saves and initializes logger
	 *
	 * @since    3.1.0
	 * @access   public
	 *
	 * @param    array $post_types
	 * @param    array $taxonomies
	 *
	 * @return   boolean
	 */
	public function prepareData( $post_types, $taxonomies ) {

		$post_count = $this->postQuery( $post_types, 'queue' );
		$term_count = $this->termQuery( $taxonomies, 'queue' );

		if ( $post_count || $term_count ) {
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
	 * Queries the database for posts related to specified post types
	 *
	 * @since    3.1.0
	 * @access   public
	 *
	 * @param array $post_types
	 * @param string $callback
	 *
	 * @return array|int|boolean
	 */
	public function postQuery( $post_types, $callback = 'count' ) {
		global $wpdb;
		$count           = 0;
		$posts_to_update = array();

		if ( ! empty( $post_types ) ) {
			$sql_post_types = '';
			$isFirst        = true;
			foreach ( $post_types as $post_type ) {
				if ( $isFirst ) {
					$isFirst = false;
				} else {
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

			$query = $wpdb->get_results( $sql );

			if ( $query ) {
				foreach ( $query as $post ) {
					$slug = urldecode( $post->post_name );
					if ( ! self::isValidSlug( $slug ) ) {
						if ( $callback === 'queue' ) {
							$this->pushToQueue( 'post', $post );
						}
						if ( $callback === 'convert' ) {
							$new_slug          = Agp_Converter::convertSlug( $slug );
							$posts_to_update[] = array(
								'ID'        => $post->ID,
								'post_name' => $new_slug,
							);
						} else {
							$count ++;
						}
					}
				}
			}
			if ( $callback === 'convert' ) {
				return $posts_to_update;
			}

			return $count;
		} else {
			return false;
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
	public static function isValidSlug( $current_post_title ) {

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
	 * Pushes items to queue
	 *
	 * @since    3.1.0
	 * @access   public
	 *
	 * @param string $type
	 * @param object $item
	 *
	 * @return int
	 */
	private function pushToQueue( $type, $item ) {
		$item = (object) array_merge( array( 'type' => $type ), (array) $item );
		$this->push_to_queue( $item );

		return true;
	}

	/**
	 *  Converts the slug to greeklish
	 *
	 * @since    1.0.0
	 * @since    3.2.0 Added filter to modify expressions
	 * @access   public
	 *
	 * @param    string $current_slug The current post slug
	 *
	 * @return   string        The converted slug in greeklish
	 */
	public static function convertSlug( $current_slug ) {

		$diphthongs_enabled = get_option( 'agp_diphthongs' ) === 'enabled';

		if ( $diphthongs_enabled ) {
			$expressions = array_merge( self::$diphthongs, self::$expressions );
		} else {
			$expressions = self::$expressions;
		}

		$expressions = apply_filters( self::$action . '_expressions', $expressions );

		$current_slug = preg_replace( array_keys( $expressions ), array_values( $expressions ), $current_slug );

		return $current_slug;

	}

	/**
	 * Queries the database for terms related to specified taxonomies
	 *
	 * @since    3.1.0
	 * @access   public
	 *
	 * @param array $taxonomies
	 * @param string $callback
	 *
	 * @return array|int|boolean
	 */
	public function termQuery( $taxonomies, $callback = 'count' ) {
		global $wpdb;
		$count           = 0;
		$terms_to_update = array();
		if ( ! empty( $taxonomies ) ) {
			$sql_taxonomies = '';
			$isFirst        = true;
			foreach ( $taxonomies as $index => $taxonomy ) {
				if ( $isFirst ) {
					$isFirst = false;
				} else {
					$sql_taxonomies .= ', ';
				}
				$sql_taxonomies .= "'$taxonomy'";
			}

			$sql = "SELECT t.term_id, t.slug, tt.taxonomy
				FROM $wpdb->terms AS t 
				INNER JOIN $wpdb->term_taxonomy AS tt
				ON t.term_id = tt.term_id
				WHERE tt.taxonomy IN ($sql_taxonomies)";

			$query = $wpdb->get_results( $sql );

			if ( $query ) {
				foreach ( $query as $term ) {
					$slug = urldecode( $term->slug );
					if ( ! self::isValidSlug( $slug ) ) {
						if ( $callback === 'queue' ) {
							$this->pushToQueue( 'term', $term );
						}
						if ( $callback === 'convert' ) {
							$new_slug          = Agp_Converter::convertSlug( $slug );
							$terms_to_update[] = array(
								'id'       => $term->term_id,
								'taxonomy' => $term->taxonomy,
								'slug'     => $new_slug,
							);
						} else {
							$count ++;
						}

					}
				}
			}
			if ( $callback === 'convert' ) {
				return $terms_to_update;
			}

			return $count;
		} else {
			return false;
		}
	}

	/**
	 * Save queue
	 *
	 * @since 3.0.0
	 * @access   public
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

		if ( $item->type === 'post' ) {
			$slug         = urldecode( $item->post_name );
			$new_slug     = self::convertSlug( $slug );
			$post         = array(
				'ID'        => $item->ID,
				'post_name' => $new_slug,
			);
			$is_converted = wp_update_post( $post, true );

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
			$slug         = urldecode( $item->slug );
			$new_slug     = self::convertSlug( $slug );
			$is_converted = self::updateTerm( $item->term_id, $item->taxonomy, $new_slug );

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
	 *  Updates terms/taxonomies
	 *
	 *  Manages WpError response
	 *
	 * @since    3.1.0
	 * @access   private
	 *
	 * @param    string $term_id
	 * @param    string $taxonomy
	 * @param    string $slug
	 *
	 * @return   array|WP_Error
	 */
	public static function updateTerm( $term_id, $taxonomy, $slug ) {
		global $wpdb;

		$needs_suffix = true;

		// As of 4.1, duplicate slugs are allowed as long as they're in different taxonomies.
		if ( ! term_exists( $slug ) || get_option( 'db_version' ) >= 30133 && ! get_term_by( 'slug', $slug, $taxonomy ) ) {
			$needs_suffix = false;
		}

		if ( $needs_suffix ) {
			$query = $wpdb->prepare( "SELECT slug FROM $wpdb->terms WHERE slug = %s AND term_id != %d", $slug, $term_id );

			if ( $wpdb->get_var( $query ) ) {
				$num = 2;
				do {
					$alt_slug = $slug . "-$num";
					$num ++;
					$slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM $wpdb->terms WHERE slug = %s", $alt_slug ) );
				} while ( $slug_check );
				$slug = $alt_slug;
			}
		}

		return wp_update_term( $term_id, $taxonomy, array( 'slug' => $slug ) );
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