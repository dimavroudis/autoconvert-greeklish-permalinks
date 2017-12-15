<?php

/*
Plugin Name: AutoConvert Greeklish Permalinks
Plugin URI: https://www.dimitrismavroudis.gr/plugins/auto_gr_permalinks
Description: Convert Greek characters to Latin (better known as greeklish). The plugin makes sure that every new permalink is greeklish and offers the option to convert all the old links with greeek characters to greeklish.
Version: 1.0
Author: Dimitris Mavroudis
Author URI: https://www.dimitrismavroudis.gr
*/

register_activation_hook( __FILE__, 'auto_gr_permalinks_install' );
register_deactivation_hook( __FILE__, 'auto_gr_permalinks_uninstall' );
add_action( 'admin_menu', 'auto_gr_permalinks_settings_page' );

function auto_gr_permalinks_install() {}

function auto_gr_permalinks_uninstall() {}

function auto_gr_permalinks_settings_page() {
	add_management_page( 'Generate Greeklish Permalinks', 'Generate Greeklish Permalinks', 'manage_options', __FILE__, 'auto_gr_permalinks_admin_page' );
}

function auto_gr_permalinks_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {

		wp_die( 'Insufficient permissions!' );

	} else { ?>
		<div class="wrap">
			<h1>Generate Greeklish Permalinks</h1>
			<h2>License</h2>
			<p>By using this plugin, you acknowledge that this plugin (and all its source code) is distributed <strong>AS IS</strong> with no implicit or explicit warranty as to the plugin's fitness for any specific purpose, and is released under the <a href="http://www.gnu.org/licenses/gpl-2.0.txt">GPLv2</a> license. It is strongly recommended that you make a backup of your WordPress installation before using this plugin.</p>
			<p>If you agree to the licensing terms above, click the Generate Greeklish Permalinks button now to generate the post slugs. </p>
			<form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ); ?>" method="post"> 
				<table class="form-table">
					<tbody>
						<tr>
							<th>
								<label for="post-type[]">Choose post types to convert</label>
							</th>
							<td>
								<select name="post-type[]" multiple required>
								<?php 
									$args = array(
										'public' => true,
									);

									$post_types = get_post_types( $args, 'names' );
								?>
								<?php foreach ( $post_types as $post_type ) : ?>
									<option value="<?php echo $post_type; ?>">
										<?php echo $post_type; ?>
									</option>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<input name="convert-button" type="hidden" value="1" />
				<button type="submit">Generate Greeklish Permalinks</button>
			</form>
		</div>
		<?php
		if ( isset( $_POST['convert-button'] ) ) {
			$query = new WP_Query( array(
				'post_type'      =>  $_POST['post-type'],
				'posts_per_page' => -1,
			));
			$update_count = 0;
			foreach ( $query->posts as $post ) {
				setlocale(LC_ALL, 'el_GR');
				$current_post_name = urldecode($post->post_name);
				if ( ! auto_gr_permalinks_is_valid_slug( $current_post_name ) ) {
					if ( $update_count === 0 )
						echo 'Permalinks updated:</br><ul>';
					$post_to_update = array();
					$post_to_update['ID'] = $post->ID;
					$post_to_update['post_name'] = auto_gr_permalinks_sanitize_title( $post->post_title );
					wp_update_post( $post_to_update );
					$update_count++;
					echo '<li>' . $current_post_name . '->' . $post_to_update['post_name'] . '</li>';
				}
			}
			echo '</ul><strong>Post slugs successfully generated for ' .  $update_count . ' posts</p></strong>';
		}
	}
}

function auto_gr_permalinks_is_valid_slug( $current_post_name ) {

	$is_valid_slug = true;

	$expressions = array(
		'/[αΑ][ιίΙΊ]/u' => 'e',
		'/[οΟΕε][ιίΙΊ]/u' => 'i',
		'/[αΑ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
		'/[αΑ][υύΥΎ]/u' => 'av',
		'/[εΕ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
		'/[εΕ][υύΥΎ]/u' => 'ev',
		'/[οΟ][υύΥΎ]/u' => 'ou',
		'/(^|\s)[μΜ][πΠ]/u' => '$1b',
		'/[μΜ][πΠ](\s|$)/u' => 'b$1',
		'/[μΜ][πΠ]/u' => 'b',
		'/[νΝ][τΤ]/u' => 'nt',
		'/[τΤ][σΣ]/u' => 'ts',
		'/[τΤ][ζΖ]/u' => 'tz',
		'/[γΓ][γΓ]/u' => 'ng',
		'/[γΓ][κΚ]/u' => 'gk',
		'/[ηΗ][υΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'if$1',
		'/[ηΗ][υΥ]/u' => 'iu',
		'/[θΘ]/u' => 'th',
		'/[χΧ]/u' => 'ch',
		'/[ψΨ]/u' => 'ps',
		'/[αάΑΆ]/u' => 'a',
		'/[βΒ]/u' => 'v',
		'/[γΓ]/u' => 'g',
		'/[δΔ]/u' => 'd',
		'/[εέΕΈ]/u' => 'e',
		'/[ζΖ]/u' => 'z',
		'/[ηήΗΉ]/u' => 'i',
		'/[ιίϊΙΊΪ]/u' => 'i',
		'/[κΚ]/u' => 'k',
		'/[λΛ]/u' => 'l',
		'/[μΜ]/u' => 'm',
		'/[νΝ]/u' => 'n',
		'/[ξΞ]/u' => 'x',
		'/[οόΟΌ]/u' => 'o',
		'/[πΠ]/u' => 'p',
		'/[ρΡ]/u' => 'r',
		'/[σςΣ]/u' => 's',
		'/[τΤ]/u' => 't',
		'/[υύϋΥΎΫ]/u' => 'y',
		'/[φΦ]/iu' => 'f',
		'/[ωώ]/iu' => 'o',
	);

	foreach ( $expressions as $key => $value ) {
		if ( preg_match( $key ,$current_post_name ) ) {
			$is_valid_slug = false;
			break;
		}
	}
	return $is_valid_slug;

}

function auto_gr_permalinks_sanitize_title( $current_post_title ) {

	$expressions = array(
		'/[αΑ][ιίΙΊ]/u' => 'e',
		'/[οΟΕε][ιίΙΊ]/u' => 'i',
		'/[αΑ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
		'/[αΑ][υύΥΎ]/u' => 'av',
		'/[εΕ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
		'/[εΕ][υύΥΎ]/u' => 'ev',
		'/[οΟ][υύΥΎ]/u' => 'ou',
		'/(^|\s)[μΜ][πΠ]/u' => '$1b',
		'/[μΜ][πΠ](\s|$)/u' => 'b$1',
		'/[μΜ][πΠ]/u' => 'b',
		'/[νΝ][τΤ]/u' => 'nt',
		'/[τΤ][σΣ]/u' => 'ts',
		'/[τΤ][ζΖ]/u' => 'tz',
		'/[γΓ][γΓ]/u' => 'ng',
		'/[γΓ][κΚ]/u' => 'gk',
		'/[ηΗ][υΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'if$1',
		'/[ηΗ][υΥ]/u' => 'iu',
		'/[θΘ]/u' => 'th',
		'/[χΧ]/u' => 'ch',
		'/[ψΨ]/u' => 'ps',
		'/[αάΑΆ]/u' => 'a',
		'/[βΒ]/u' => 'v',
		'/[γΓ]/u' => 'g',
		'/[δΔ]/u' => 'd',
		'/[εέΕΈ]/u' => 'e',
		'/[ζΖ]/u' => 'z',
		'/[ηήΗΉ]/u' => 'i',
		'/[ιίϊΙΊΪ]/u' => 'i',
		'/[κΚ]/u' => 'k',
		'/[λΛ]/u' => 'l',
		'/[μΜ]/u' => 'm',
		'/[νΝ]/u' => 'n',
		'/[ξΞ]/u' => 'x',
		'/[οόΟΌ]/u' => 'o',
		'/[πΠ]/u' => 'p',
		'/[ρΡ]/u' => 'r',
		'/[σςΣ]/u' => 's',
		'/[τΤ]/u' => 't',
		'/[υύϋΥΎΫ]/u' => 'y',
		'/[φΦ]/iu' => 'f',
		'/[ωώ]/iu' => 'o',
	);

	$current_post_title = preg_replace( array_keys( $expressions ), array_values( $expressions ), $current_post_title );
	return $current_post_title;

}

add_filter( 'sanitize_title', 'auto_gr_permalinks_sanitize_title' );

?>