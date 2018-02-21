<?php

function auto_gr_permalinks_settings_init() {
	register_setting( 'auto_gr_permalinks', 'auto_gr_permalinks_automatic' );
	register_setting( 'auto_gr_permalinks', 'auto_gr_permalinks_diphthongs' );

	add_settings_section( 'auto_gr_permalinks_main', __( 'Automatic Conversion', 'autoconvert-greeklish-permalinks' ), 'auto_gr_permalinks_main_cb', 'auto_gr_permalinks' );
	add_settings_field( 'auto_gr_permalinks_automatic', __( 'Automatic conversion of new posts/terms to greeklish', 'autoconvert-greeklish-permalinks' ), 'auto_gr_permalinks_automatic_cb', 'auto_gr_permalinks', 'auto_gr_permalinks_main' );

	add_settings_section( 'auto_gr_permalinks_custom', __( 'Customization', 'autoconvert-greeklish-permalinks' ), 'auto_gr_permalinks_custom_cb', 'auto_gr_permalinks' );
	add_settings_field( 'auto_gr_permalinks_diphthongs', __( 'Do you want diphthongs to be converted?', 'autoconvert-greeklish-permalinks' ), 'auto_gr_permalinks_diphthongs_cb', 'auto_gr_permalinks', 'auto_gr_permalinks_custom' );
}

add_action( 'admin_init', 'auto_gr_permalinks_settings_init' );

function auto_gr_permalinks_custom_cb() {
}

function auto_gr_permalinks_main_cb() {
}

function auto_gr_permalinks_automatic_cb() {
	$options   = get_option( 'auto_gr_permalinks_automatic' );
	$automatic = $options === 'enabled' ? 'checked' : '';
	?>
	<label class="switch">
		<input type="checkbox" id="auto_gr_permalinks_automatic" name="auto_gr_permalinks_automatic"
			   value="enabled" <?php echo $automatic; ?>>
		<span class="slider round"></span>
	</label>
	<?php
}

function auto_gr_permalinks_diphthongs_cb() {
	$options    = get_option( 'auto_gr_permalinks_diphthongs' );
	$diphthongs = $options === 'enabled' ? 'checked' : '';
	?>
	<label class="switch">
		<input type="checkbox" id="auto_gr_permalinks_diphthongs" name="auto_gr_permalinks_diphthongs"
			   value="enabled" <?php echo $diphthongs; ?>>
		<span class="slider round"></span>
	</label>

	<span style="margin-left: 10px;"><?php _e( 'For example "ει", "οι" becomes "i", "μπ" becomes "b" etc' , 'autoconvert-greeklish-permalinks'); ?></span>
	<?php
}

add_action( 'admin_menu', 'auto_gr_permalinks_settings_page' );


function auto_gr_permalinks_settings_page() {
	add_options_page( __( 'AutoConvert Greeklish Permalinks', 'autoconvert-greeklish-permalinks' ), __( 'AutoConvert Greeklish Permalinks', 'autoconvert-greeklish-permalinks' ), 'manage_options', 'auto_gr_permalinks', 'auto_gr_permalinks_admin_page' );
}

function auto_gr_permalinks_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	?>
	<div class="wrap">
		<?php
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'permalink_settings';
		?>
		<h2 class="nav-tab-wrapper">
			<a href="?page=auto_gr_permalinks&tab=permalink_settings"
			   class="nav-tab <?php echo $active_tab == 'permalink_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'autoconvert-greeklish-permalinks' ); ?></a>
			<a href="?page=auto_gr_permalinks&tab=generate_permalinks"
			   class="nav-tab <?php echo $active_tab == 'generate_permalinks' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Convert old posts/terms', 'autoconvert-greeklish-permalinks' ); ?></a>
		</h2>
		<?php
		if ( $active_tab == 'permalink_settings' ) { ?>
			<div>
				<form action="options.php" method="post">
					<?php settings_fields( 'auto_gr_permalinks' ); ?>
					<?php do_settings_sections( 'auto_gr_permalinks' ); ?>
					<?php submit_button( __( 'Save Settings', 'autoconvert-greeklish-permalinks' ) ); ?>
				</form>
			</div>
		<?php } else { ?>
			<div>
				<form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ); ?>" method="post">
					<table class="form-table">
						<tbody>
						<tr>
							<th>
								<label for="post-type[]"><?php _e( 'Post types to convert', 'autoconvert-greeklish-permalinks' ); ?></label>
							</th>
							<td>
								<select title="<?php _e( 'Post types to convert', 'autoconvert-greeklish-permalinks' ); ?>"
										name="post-type[]" class="select2"
										data-placeholder="<?php _e( 'Select one or more post types', 'autoconvert-greeklish-permalinks' ); ?>"
										multiple>
									<?php
									$args       = array(
										'public' => true,
									);
									$post_types = get_post_types( $args, 'names' );
									?>
									<?php
									foreach ( $post_types as $post_type ) {
										$obj = get_post_type_object( $post_type );
										?>
										<option value="<?php esc_attr_e( $post_type ); ?>">
											<?php echo $obj->labels->name; ?>
										</option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<label for="taxonomy[]"><?php _e( 'Taxonomies to convert', 'autoconvert-greeklish-permalinks' ); ?></label>
							</th>
							<td>
								<select title="<?php _e( 'Taxonomies to convert', 'autoconvert-greeklish-permalinks' ); ?>"
										name="taxonomy[]" class="select2"
										data-placeholder="<?php _e( 'Select one or more taxonomies', 'autoconvert-greeklish-permalinks' ); ?>"
										multiple>
									<?php
									$args       = array(
										'public' => true,
									);
									$taxonomies = get_taxonomies( $args, 'objects' );
									foreach ( $taxonomies as $taxonomy ) { ?>
										<option value="<?php esc_attr_e( $taxonomy->name ); ?>">
											<?php echo $taxonomy->labels->name; ?>
										</option>
									<?php } ?>
								</select>
							</td>
						</tr>
						</tbody>
					</table>
					<input name="convert-button" type="hidden" value="1"/>
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary"
							   value="<?php _e( 'Convert Permalinks', 'autoconvert-greeklish-permalinks' ); ?>">
					</p>
				</form>
			</div>
			<script>
				jQuery(function () {
					jQuery('.select2').select2();
				});
			</script>
		<?php } ?>
	</div>
	<?php
	if ( isset( $_POST['convert-button'] ) ) {
		$update_count_posts = 0;
		$update_count_terms = 0;
		if ( isset( $_POST['post-type'] ) ) {
			$query = new WP_Query( array(
				'post_type'      => $_POST['post-type'],
				'posts_per_page' => - 1,
			) );

			foreach ( $query->posts as $post ) {
				setlocale( LC_ALL, 'el_GR' );
				$current_post_name = urldecode( $post->post_name );
				if ( ! auto_gr_permalinks_is_valid_slug( $current_post_name ) ) {
					if ( ! $update_count_posts ) {
						echo '<h4>' . __( 'Permalinks updated', 'autoconvert-greeklish-permalinks' ) . ':</h4><pre style="width:100%;max-width:800px;max-height:250px;overflow-y:scroll;background: white;border: 1px solid #e0e0e0;padding:  8px 14px;">';
					}
					$post_to_update              = array();
					$post_to_update['ID']        = $post->ID;
					$post_to_update['post_name'] = auto_gr_permalinks_sanitize_title( $post->post_title );
					wp_update_post( $post_to_update );
					$update_count_posts ++;
					echo $current_post_name . '<br/>';
				}
			}
		}
		if ( isset( $_POST['taxonomy'] ) ) {
			$terms = get_terms( array(
				'taxonomy'   => $_POST['taxonomy'],
				'hide_empty' => 0,
			) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					setlocale( LC_ALL, 'el_GR' );
					$current_term_slug = urldecode( $term->slug );
					if ( ! auto_gr_permalinks_is_valid_slug( $current_term_slug ) ) {
						if ( ! $update_count_posts && ! $update_count_terms ) {
							echo '<h4>' . __( 'Permalinks updated', 'autoconvert-greeklish-permalinks' ) . ':</h4><pre style="width:100%;max-width:800px;max-height:250px;overflow-y:scroll;background: white;border: 1px solid #e0e0e0;padding:  8px 14px;">';
						}
						$new_term_slug = auto_gr_permalinks_sanitize_title( $term->name );
						$args          = array(
							'slug' => $new_term_slug,
						);
						wp_update_term( $term->term_id, $term->taxonomy, $args );
						$update_count_terms ++;
						echo $current_term_slug . '<br/>';
					}
				}
			}

		}
		$posts_txt = $update_count_posts == 1 ? __( 'post' , 'autoconvert-greeklish-permalinks') : __( 'posts' , 'autoconvert-greeklish-permalinks');
		$terms_txt = $update_count_terms == 1 ? __( 'term' , 'autoconvert-greeklish-permalinks') : __( 'terms' , 'autoconvert-greeklish-permalinks');
		$text_format = __( 'Permalinks successfully generated for %s %s and %s %s', 'autoconvert-greeklish-permalinks');
		echo '</pre><p><strong>' . sprintf($text_format , $update_count_posts, $posts_txt, $update_count_terms,  $terms_txt) . '</strong></p>';
	}
}

