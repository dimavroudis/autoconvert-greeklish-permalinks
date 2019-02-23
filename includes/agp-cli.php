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
 * The wp-cli commands for autoconvert
 *
 * @since      3.1.0
 * @package    Agp
 * @subpackage Agp/includes
 *
 */
class Agp_CLI extends WP_CLI_Command {
	/**
	 * Check how many permalinks are in greek
	 *
	 * ## OPTIONS
	 *
	 *
	 * [--post_type=<post_type>]
	 * : Which post type to check
	 * ---
	 * default: all
	 * ---
	 *
	 * [--taxonomy=<taxonomy>]
	 * : Which taxonomy to check
	 * ---
	 * default: all
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp agp check
	 *     wp agp check --post_type=post
	 *     wp agp check --taxonomy=category
	 *     wp agp check --post_type=post --taxonomy=category
	 *
	 * @when after_wp_load
	 */
	function check( $args, $assoc_args ) {
		$converter = new Agp_Converter();

		//Get posts types
		if ( $assoc_args['post_type'] === 'all' ) {
			$post_types = get_post_types( array( 'public' => true ), 'names' );
		} elseif ( $assoc_args['post_type'] === 'none' ) {
			$post_types = array();
		} else {
			if ( ! post_type_exists( $assoc_args['post_type'] ) ) {
				WP_CLI::error( sprintf( "'%s' is not a registered post type.", $assoc_args['post_type'] ) );

				return;
			} else {
				$post_types = array( $assoc_args['post_type'] );
			}
		}

		//Get taxonomies
		if ( $assoc_args['taxonomy'] === 'all' ) {
			$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		} elseif ( $assoc_args['taxonomy'] === 'none' ) {
			$taxonomies = array();
		} else {
			if ( ! taxonomy_exists( $assoc_args['taxonomy'] ) ) {
				WP_CLI::error( sprintf( "'%s' is not a registered taxonomy.", $assoc_args['taxonomy'] ) );

				return;
			} else {
				$taxonomies = array( $assoc_args['taxonomy'] );
			}
		}

		$post_count = $converter->postQuery( $post_types );
		$term_count = $converter->termQuery( $taxonomies );

		$count = $post_count + $term_count;

		if ( $count !== 0 ) {
			WP_CLI::success( $post_count . ' posts and ' . $term_count . ' terms are in greek.' );
		} elseif ( empty( $post_types ) && empty( $taxonomies ) ) {
			WP_CLI::warning( 'No post types or taxonomies selected' );
		} else {
			WP_CLI::success( 'All your posts are already in greeklish.' );
		}

	}

	/**
	 * Converts permalinks from greek to latin
	 *
	 * ## OPTIONS
	 *
	 *
	 * [--post_type=<post_type>]
	 * : Which post type to convert
	 * ---
	 * default: all
	 * ---
	 *
	 * [--taxonomy=<taxonomy>]
	 * : Which taxonomy to convert
	 * ---
	 * default: all
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp agp convert
	 *     wp agp convert --post_type=post
	 *     wp agp convert --taxonomy=category
	 *
	 * @when after_wp_load
	 */
	function convert( $args, $assoc_args ) {

		$post_count = $term_count = 0;
		$converter  = new Agp_Converter();

		//Get posts types
		if ( $assoc_args['post_type'] === 'all' ) {
			$post_types = get_post_types( array( 'public' => true ), 'names' );
		} elseif ( $assoc_args['post_type'] === 'none' ) {
			$post_types = array();
		} else {
			if ( ! post_type_exists( $assoc_args['post_type'] ) ) {
				WP_CLI::error( sprintf( "'%s' is not a registered post type.", $assoc_args['post_type'] ) );

				return;
			} else {
				$post_types = array( $assoc_args['post_type'] );
			}
		}

		//Get taxonomies
		if ( $assoc_args['taxonomy'] === 'all' ) {
			$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		} elseif ( $assoc_args['taxonomy'] === 'none' ) {
			$taxonomies = array();
		} else {
			if ( ! taxonomy_exists( $assoc_args['taxonomy'] ) ) {
				WP_CLI::error( sprintf( "'%s' is not a registered taxonomy.", $assoc_args['taxonomy'] ) );

				return;
			} else {
				$taxonomies = array( $assoc_args['taxonomy'] );
			}
		}

		$posts_to_update = $converter->postQuery( $post_types, 'convert' );
		$terms_to_update = $converter->termQuery( $taxonomies, 'convert' );

		$count = count( $posts_to_update ) + count( $terms_to_update );

		if ( $count !== 0 ) {

			//Make progress bar
			$notify = \WP_CLI\Utils\make_progress_bar( 'Converting posts and terms', $count );

			//Update posts
			if ( ! empty( $posts_to_update ) ) {
				foreach ( $posts_to_update as $post ) {
					$is_converted = wp_update_post( $post, true );
					if ( ! is_wp_error( $is_converted ) ) {
						$post_count ++;
					} else {
						WP_CLI::warning( $is_converted );
					}
					//increase progress bar
					$notify->tick();
				}
			}

			//Update terms
			if ( ! empty( $terms_to_update ) ) {
				foreach ( $terms_to_update as $term ) {
					$is_converted = Agp_Converter::updateTerm( $term['id'], $term['taxonomy'], $term['slug'] );
					if ( ! is_wp_error( $is_converted ) ) {
						$term_count ++;
					} else {
						WP_CLI::warning( $is_converted );
					}
					//increase progress bar
					$notify->tick();
				}
			}

			//Complete progress bar
			$notify->finish();
			WP_CLI::success( $post_count . ' posts and ' . $term_count . ' terms converted' );

		} elseif ( empty( $post_types ) && empty( $taxonomies ) ) {
			WP_CLI::warning( 'No post types or taxonomies selected' );
		} else {
			WP_CLI::success( 'All your posts were already in greeklish. No posts or terms to convert.' );
		}

	}
}

WP_CLI::add_command( 'agp', 'Agp_CLI' );