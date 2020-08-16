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
 * @since      3.3.0
 * @package    Agp
 * @subpackage Agp/includes
 *
 */
class Agp_CLI extends WP_CLI_Command
{
	/**
	 * Check how many permalinks are in greek
	 *
	 * ## OPTIONS
	 *
	 *
	 * [--post_types=<post_types>]
	 * : Which post types to check
	 * ---
	 * default: all
	 * ---
	 *
	 * [--taxonomies=<taxonomies>]
	 * : Which taxonomies to check
	 * ---
	 * default: all
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp agp check
	 *     wp agp check --post_types=post,page
	 *     wp agp check --taxonomies=category
	 *     wp agp check --post_types=post,page --taxonomies=category
	 *
	 * @when after_wp_load
	 */
	function check($args, $assoc_args)
	{
		$converter = new Agp_Converter();

		//Get posts types
		$post_types = array();
		if (isset($assoc_args['post_types'])) {
			switch ($assoc_args['post_types']) {
				case 'all':
					$post_types = get_post_types(array('public' => true), 'names');
					break;
				case 'none':
					break;
				default:
					$stripped  = str_replace(' ', '', $assoc_args['post_types']);
					$arg_array = explode(",", $stripped);
					foreach ($arg_array as $post_type) {
						if (!post_type_exists($post_type)) {
							WP_CLI::error(sprintf("'%s' is not a registered post type.", $post_type));

							return;
						} else {
							$post_types[] = $post_type;
						}
					}
			}
		}

		//Get taxonomies
		$taxonomies = array();
		if (isset($assoc_args['taxonomies'])) {
			switch ($assoc_args['taxonomies']) {
				case 'all':
					$taxonomies = get_taxonomies(array('public' => true), 'names');
					break;
				case 'none':
					break;
				default:
					$stripped  = str_replace(' ', '', $assoc_args['taxonomies']);
					$arg_array = explode(",", $stripped);
					foreach ($arg_array as $taxonomy) {
						if (!taxonomy_exists($taxonomy)) {
							WP_CLI::error(sprintf("'%s' is not a registered taxonomy.", $taxonomy));

							return;
						} else {
							$taxonomies[] = $taxonomy;
						}
					}
			}
		}

		$post_count = $converter->postQuery($post_types);
		$term_count = $converter->termQuery($taxonomies);

		$count = $post_count + $term_count;

		if ($count !== 0) {
			WP_CLI::success($post_count . ' posts and ' . $term_count . ' terms are in greek.');
		} elseif (empty($post_types) && empty($taxonomies)) {
			WP_CLI::warning('No post types or taxonomies selected');
		} else {
			WP_CLI::success('All your posts are already in greeklish.');
		}
	}

	/**
	 * Converts permalinks from greek to latin
	 *
	 * ## OPTIONS
	 *
	 *
	 * [--post_types=<post_types>]
	 * : Which post types to convert
	 * ---
	 * default: all
	 * ---
	 *
	 * [--taxonomies=<taxonomies>]
	 * : Which taxonomies to convert
	 * ---
	 * default: all
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp agp convert
	 *     wp agp convert --post_types=post,page
	 *     wp agp convert --taxonomies=category
	 *     wp agp convert --post_types=post,page --taxonomies=category
	 *
	 * @when after_wp_load
	 */
	function convert($args, $assoc_args)
	{

		$count = $post_count = $term_count = 0;
		$converter  = new Agp_Converter();

		//Get posts types
		$post_types = array();
		if (isset($assoc_args['post_types'])) {
			switch ($assoc_args['post_types']) {
				case 'all':
					$post_types = get_post_types(array('public' => true), 'names');
					break;
				case 'none':
					break;
				default:
					$stripped  = str_replace(' ', '', $assoc_args['post_types']);
					$arg_array = explode(",", $stripped);
					foreach ($arg_array as $post_type) {
						if (!post_type_exists($post_type)) {
							WP_CLI::error(sprintf("'%s' is not a registered post type.", $post_type));
							return;
						} else {
							$post_types[] = $post_type;
						}
					}
			}
		}

		//Get taxonomies
		$taxonomies = array();
		if (isset($assoc_args['taxonomies'])) {
			switch ($assoc_args['taxonomies']) {
				case 'all':
					$taxonomies = get_taxonomies(array('public' => true), 'names');
					break;
				case 'none':
					break;
				default:
					$stripped  = str_replace(' ', '', $assoc_args['taxonomies']);
					$arg_array = explode(",", $stripped);
					foreach ($arg_array as $taxonomy) {
						if (!taxonomy_exists($taxonomy)) {
							WP_CLI::error(sprintf("'%s' is not a registered taxonomy.", $taxonomy));

							return;
						} else {
							$taxonomies[] = $taxonomy;
						}
					}
			}
		}

		$posts_to_update = $converter->postQuery($post_types, 'object');
		$terms_to_update = $converter->termQuery($taxonomies, 'object');

		if ($posts_to_update) {
			$count += count($posts_to_update);
		}
		if ($terms_to_update) {
			$count += count($terms_to_update);
		}


		if ($count !== 0) {

			//Make progress bar
			$notify = \WP_CLI\Utils\make_progress_bar('Converting posts and terms', $count);

			//Update posts
			if (!empty($posts_to_update)) {
				foreach ($posts_to_update as $post) {
					$is_converted = wp_update_post($post, true);
					if (!is_wp_error($is_converted)) {
						$post_count++;
					} else {
						WP_CLI::warning($is_converted);
					}
					//increase progress bar
					$notify->tick();
				}
			}

			//Update terms
			if (!empty($terms_to_update)) {
				foreach ($terms_to_update as $term) {
					$is_converted = Agp_Converter::updateTerm($term['id'], $term['taxonomy'], $term['slug']);
					if (!is_wp_error($is_converted)) {
						$term_count++;
					} else {
						WP_CLI::warning($is_converted);
					}
					//increase progress bar
					$notify->tick();
				}
			}

			//Complete progress bar
			$notify->finish();
			WP_CLI::success($post_count . ' posts and ' . $term_count . ' terms converted');
		} elseif (empty($post_types) && empty($taxonomies)) {
			WP_CLI::warning('No post types or taxonomies selected');
		} else {
			WP_CLI::success('All your posts were already in greeklish. No posts or terms to convert.');
		}
	}

	/**
	 * Update options
	 *
	 * ## OPTIONS
	 *
	 *
	 * [--automatic=<automatic>]
	 * : Automatic Conversion option status
	 * ---
	 * options:
	 *   - enabled
	 *   - disabled
	 *
	 * [--diphthongs=<diphthongs>]
	 * : Diphthongs Conversion option status
	 * ---
	 * options:
	 *   - advanced
	 *   - simple
	 *
	 * ---
	 *
	 * [--automatic_posts=<automatic_posts>]
	 * : Which post type to autoconvert
	 * ---
	 *
	 * [--automatic_tax=<automatic_tax>]
	 * : Which taxonomy to autoconvert
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp agp update_options --automatic=enabled
	 *     wp agp update_options --automatic=disabled
	 *     wp agp update_options --automatic_posts=post,page
	 *     wp agp update_options --automatic_posts=all
	 *     wp agp update_options --automatic_posts=none
	 *     wp agp update_options --diphthongs=enabled
	 *     wp agp update_options --diphthongs=disabled
	 *
	 * @when after_wp_load
	 */
	function update_options($args, $assoc_args)
	{
		if (isset($assoc_args['automatic'])) {
			if ($assoc_args['automatic'] === 'enabled' || $assoc_args['automatic'] === 'disabled') {
				update_option('agp_automatic', $assoc_args['automatic']);
				WP_CLI::success('Automatic conversion is now ' . $assoc_args['automatic']);
			}
		}
		if (isset($assoc_args['diphthongs'])) {
			if ($assoc_args['diphthongs'] === 'advanced' || $assoc_args['diphthongs'] === 'simple') {
				update_option('agp_diphthongs', $assoc_args['diphthongs'] === 'advanced' ? 'enabled' : 'disabled');
				WP_CLI::success('Diphthongs conversion is now switched to ' . $assoc_args['diphthongs']);
			}
		}
		if (isset($assoc_args['automatic_posts'])) {
			if ($assoc_args['automatic_posts'] === 'all' || $assoc_args['automatic_posts'] === 'none') {
				$post_types[] = $assoc_args['automatic_posts'] === 'all' ? 'all_options' : 'no_option';
			} else {
				$stripped   = str_replace(' ', '', $assoc_args['automatic_posts']);
				$arg_array  = explode(",", $stripped);
				$post_types = array();
				foreach ($arg_array as $post_type) {
					if (!post_type_exists($post_type)) {
						WP_CLI::error(sprintf("'%s' is not a registered post type.", $post_type));

						return;
					} else {
						$post_types[] = $post_type;
					}
				}
				if (empty($post_types)) {
					$post_types[] = 'no_option';
				}
			}
			update_option('agp_automatic_post', $post_types);
			WP_CLI::success('Post types to be autoconverted from now on: ' . implode(", ", $post_types));
		}
		if (isset($assoc_args['automatic_tax'])) {
			if ($assoc_args['automatic_tax'] === 'all' || $assoc_args['automatic_tax'] === 'none') {
				$taxonomies[] = $assoc_args['automatic_tax'] === 'all' ? 'all_options' : 'no_option';
			} else {
				$stripped   = str_replace(' ', '', $assoc_args['automatic_tax']);
				$arg_array  = explode(",", $stripped);
				$taxonomies = array();
				foreach ($arg_array as $taxonomy) {
					if (!taxonomy_exists($taxonomy)) {
						WP_CLI::error(sprintf("'%s' is not a registered taxonomy.", $taxonomy));

						return;
					} else {
						$taxonomies[] = $taxonomy;
					}
				}
				if (empty($taxonomies)) {
					$taxonomies[] = 'no_option';
				}
			}
			update_option('agp_automatic_tax', $taxonomies);
			WP_CLI::success('Taxonomies to be autoconverted from now on: ' . implode(", ", $taxonomies));
		}
	}

	/**
	 * Get options
	 *
	 * ## OPTIONS
	 *
	 * [--options=<options>]
	 * : Which options to print
	 * ---
	 * default: all
	 * options:
	 *      - all
	 *      - automatic
	 *      - diphthongs
	 *      - automatic_posts
	 *      - automatic_tax
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp agp get_options
	 *     wp agp get_options --options=all
	 *     wp agp get_options --options=automatic
	 *     wp agp get_options --options=diphthongs
	 *
	 * @when after_wp_load
	 */
	function get_options($args, $assoc_args)
	{
		$print_all = false;
		$option    = $assoc_args['options'];
		if ($option === 'all') {
			$print_all = true;
		}
		if ($option === 'automatic' || $print_all) {
			$message = WP_CLI::colorize($print_all ? '%yAutomatic Conversion%n: ' : '');
			WP_CLI::log($message . get_option('agp_automatic'));
		}
		if ($option === 'diphthongs' || $print_all) {
			$message = WP_CLI::colorize($print_all ? '%yDiphthongs Conversion%n: ' : '');
			WP_CLI::log($message . get_option('agp_diphthongs'));
		}
		if ($option === 'automatic_posts' || $print_all) {
			$message = WP_CLI::colorize($print_all ? '%yAutoConvert Post Types%n: ' : '');
			$agp_automatic_post = get_option('agp_automatic_post');
			WP_CLI::log($message . strlen($agp_automatic_post) > 0 ? implode(", ", $agp_automatic_post) : '');
		}
		if ($option === 'automatic_tax' || $print_all) {
			$message = WP_CLI::colorize($print_all ? '%yAutoConvert Taxonomies%n: ' : '');
			$agp_automatic_tax = get_option('agp_automatic_tax');
			WP_CLI::log($message . strlen($agp_automatic_post) > 0 ? implode(", ", $agp_automatic_tax) : '');
		}
	}
}

WP_CLI::add_command('agp', 'Agp_CLI');
