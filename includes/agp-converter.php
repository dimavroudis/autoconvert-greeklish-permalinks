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
class Agp_Converter
{

	/**
	 * All the greek letters and their latin counterparts
	 *
	 * @since    1.0.0
	 * @since    3.4.0 Added polytonic characters
	 * @since    4.1.0 Added more polytonic characters
	 * @access   protected
	 * @var      array $expressions All the greek letters and their latin counterparts
	 */
	protected static $expressions = array(
		'/[ἀἁἈἉᾶἄἅἌἍἆἇἎἏἂἃἊἋᾳᾼᾴᾲᾀᾈᾁᾉᾷᾆᾎᾇᾏᾂᾊᾃᾋὰαάΑΆᾄᾅᾌᾍᾺᾰᾱᾸᾹ]/u'	=> 'a',
		'/[βΒ]/u'      											=> 'v',
		'/[γΓ]/u'      											=> 'g',
		'/[δΔ]/u'      											=> 'd',
		'/[ἐἑἘἙἔἕἜἝἒἓἚἛὲεέΕΈ]/u'    							=> 'e',
		'/[ζΖ]/u'      											=> 'z',
		'/[ἠἡἨἩἤἥἬἭῆἦἧἮἯἢἣἪἫῃῌῄῂᾐᾑᾘᾙᾖᾗᾞᾟᾒᾚᾛὴηήΗΉᾓᾔᾕῇᾜᾝῊ]/u'   	=> 'i',
		'/[θΘ]/u'      											=> 'th',
		'/[ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻῒῗὶιίϊΐΙΊΪΐῐῑῚῘῙ]/u' 				=> 'i',
		'/[κΚ]/u'      											=> 'k',
		'/[λΛ]/u'      											=> 'l',
		'/[μΜ]/u'      											=> 'm',
		'/[νΝ]/u'      											=> 'n',
		'/[ξΞ]/u'     	 										=> 'x',
		'/[ὀὁὈὉὄὅὌὍὂὃὊὋὸοόΟΌῸ]/u'    							=> 'o',
		'/[πΠ]/u'      											=> 'p',
		'/[ρΡ]/u'     											=> 'r',
		'/[σςΣ]/u'     											=> 's',
		'/[τΤ]/u'      											=> 't',
		'/[ὐὑὙὔὕὝῦὖὗὒὓὛὺῒῧυύϋΰΥΎΫῢΰῠῡὟῪῨῩ]/u' 					=> 'y',
		'/[φΦ]/iu'     											=> 'f',
		'/[χΧ]/u'      											=> 'ch',
		'/[ψΨ]/u'      											=> 'ps',
		'/[ὠὡὨὩὤὥὬὭῶὦὧὮὯὢὣὪὫῳῼᾠᾡᾨᾩᾤᾥᾬᾭᾦᾧᾮᾯᾢᾣᾪᾫὼωώῲῷῴ]/iu'  		=> 'o',
	);

	/**
	 * All the greek diphthongs and their latin counterparts
	 *
	 * @since    1.0.0
	 * @since    3.4.0 Added polytonic characters
	 * @access   protected
	 * @var      array $diphthongs All the greek diphthongs and their latin counterparts
	 */
	protected static $diphthongs = array(
		'/[αΑ][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u'                        => 'ai',
		'/[οΟ][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u'                        => 'oi',
		'/[Εε][ἰἱἸἹἴἵἼἽῖἶἷἾἿἲἳἺἻὶιίΙΊ]/u'                        => 'ei',
		'/[αΑ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]([θΘκΚξΞπΠσςΣτTφΦχΧψΨ]|\s|$)/u' => 'af$1',
		'/[αΑ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]/u'                             => 'av',
		'/[εΕ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυύΥΎ]([θΘκΚξΞπΠσςΣτTφΦχΧψΨ]|\s|$)/u' => 'ef$1',
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
		'/[ηΗ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυΥ]([θΘκΚξΞπΠσςΣτTφΦχΧψΨ]|\s|$)/u'   => 'if$1',
		'/[ηΗ][ὐὑὙὔὕὝῦὖὗὒὓὛὺυΥ]/u'                               => 'iu',
	);

	/**
	 * @since    3.0.0
	 * @access   protected
	 * @var      string     Action id
	 */
	protected $action = 'agp_convert';

	public static function is_diphthongs_enabled()
	{
		return get_option('agp_diphthongs') === 'enabled';
	}

	public static function getExpressions()
	{
		if (self::is_diphthongs_enabled()) {
			return array_merge(self::$diphthongs, self::$expressions);
		}

		return self::$expressions;
	}

	/**
	 * Queries the database for posts related to specified post types
	 *
	 * @since    3.1.0
	 * @access   public
	 *
	 * @param array $post_types
	 * @param string $return
	 *
	 * @return array|int
	 */
	public function postQuery($post_types, $return = 'count', $limit = -1)
	{
		global $wpdb;
		$count           = 0;
		$posts_to_update = array();

		if (!empty($post_types)) {
			$sql_post_types = '';
			$isFirst        = true;
			foreach ($post_types as $post_type) {
				if ($isFirst) {
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

			$query = $wpdb->get_results($sql);

			if ($query) {
				foreach ($query as $post) {
					if ($limit > 0 && $count >= $limit) {
						break;
					}
					$slug = urldecode($post->post_name);
					if (!self::isValidSlug($slug)) {
						if ($return === 'object') {
							$new_slug          = Agp_Converter::convertSlug($slug);
							$posts_to_update[] = array(
								'ID'        => $post->ID,
								'post_name' => $new_slug,
							);
						}
						$count++;
					}
				}
			}
		}
		if ($return === 'object') {
			return $posts_to_update;
		} else {
			return $count;
		}
	}

	/**
	 * Queries the database for terms related to specified taxonomies
	 *
	 * @since    3.1.0
	 * @access   public
	 *
	 * @param array $taxonomies
	 * @param string $return
	 *
	 * @return array|int
	 */
	public function termQuery($taxonomies, $return = 'count', $limit = -1)
	{
		global $wpdb;
		$count           = 0;
		$terms_to_update = array();

		if (!empty($taxonomies)) {
			$sql_taxonomies = '';
			$isFirst        = true;
			foreach ($taxonomies as $index => $taxonomy) {
				if ($isFirst) {
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

			$query = $wpdb->get_results($sql);

			if ($query) {
				foreach ($query as $term) {
					if ($limit > 0 && $count >= $limit) {
						break;
					}
					$slug = urldecode($term->slug);
					if (!self::isValidSlug($slug)) {
						if ($return === 'object') {
							$new_slug          = Agp_Converter::convertSlug($slug);
							$terms_to_update[] = array(
								'id'       => $term->term_id,
								'taxonomy' => $term->taxonomy,
								'slug'     => $new_slug,
							);
						}
						$count++;
					}
				}
			}
		}
		if ($return === 'object') {
			return $terms_to_update;
		} else {
			return $count;
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
	public static function isValidSlug($current_post_title)
	{

		$is_valid_slug = true;

		foreach (self::$expressions as $key => $value) {
			if (preg_match($key, $current_post_title)) {
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
	 * @since    3.4.0 Added filter to modify expressions
	 * @access   public
	 *
	 * @param    string $current_slug The current post slug
	 *
	 * @return   string        The converted slug in greeklish
	 */
	public static function convertSlug($current_slug)
	{

		$expressions = apply_filters('agp_convert_expressions', self::getExpressions());

		$current_slug = preg_replace(array_keys($expressions), array_values($expressions), $current_slug);

		return $current_slug;
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
	public static function updateTerm($term_id, $taxonomy, $slug)
	{
		global $wpdb;

		$needs_suffix = true;

		// As of 4.1, duplicate slugs are allowed as long as they're in different taxonomies.
		if (!term_exists($slug) || get_option('db_version') >= 30133 && !get_term_by('slug', $slug, $taxonomy)) {
			$needs_suffix = false;
		}

		if ($needs_suffix) {
			$query = $wpdb->prepare("SELECT slug FROM $wpdb->terms WHERE slug = %s AND term_id != %d", $slug, $term_id);

			if ($wpdb->get_var($query)) {
				$num = 2;
				do {
					$alt_slug = $slug . "-$num";
					$num++;
					$slug_check = $wpdb->get_var($wpdb->prepare("SELECT slug FROM $wpdb->terms WHERE slug = %s", $alt_slug));
				} while ($slug_check);
				$slug = $alt_slug;
			}
		}

		return wp_update_term($term_id, $taxonomy, array('slug' => $slug));
	}
}
