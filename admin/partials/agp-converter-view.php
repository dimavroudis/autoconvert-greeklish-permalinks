<?php

?>
<div>
	<div class="card">
		<h3 class="title"><?php _e( 'Things to know before you start the conversion', 'agp' ); ?></h3>
		<p><?php _e( 'Depending on your server and the amount of permalinks that needs to be converted, the conversion might take a few minutes to complete.', 'agp' ) ?>
			<b><?php _e( 'During that time do not close this window.', 'agp' ) ?></b></p>
		<p><?php _e( 'When all your permalinks are converted, a success message will appear at the top of your page.', 'agp' ) ?></p>
		<p><?php _e( 'If the process takes too long, your server will probably stop the process*. If this happens, do not worry. Just re-run the process to finish the rest of the conversion.', 'agp' ) ?></p>
		<p><i><?php _e( '*Working on a fix for this issue for a future version.', 'agp' ) ?></i></p>

	</div>
	<div class="card">
		<h3 class="title"><?php _e( 'Convert old posts/terms', 'agp' ); ?></h3>
		<form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ); ?>" method="post">
			<table class="form-table">
				<tbody>
				<tr>
					<th>
						<label for="post-type[]"><?php _e( 'Post types to convert', 'agp' ); ?></label>
					</th>
					<td>
						<select title="<?php _e( 'Post types to convert', 'agp' ); ?>"
								name="post-type[]" class="select2"
								data-placeholder="<?php _e( 'Select one or more post types', 'agp' ); ?>"
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
						<label for="taxonomy[]"><?php _e( 'Taxonomies to convert', 'agp' ); ?></label>
					</th>
					<td>
						<select title="<?php _e( 'Taxonomies to convert', 'agp' ); ?>"
								name="taxonomy[]" class="select2"
								data-placeholder="<?php _e( 'Select one or more taxonomies', 'agp' ); ?>"
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
					   value="<?php _e( 'Convert Permalinks', 'agp' ); ?>">
			</p>
		</form>
	</div>
</div>
<script>
	jQuery(function () {
		jQuery('.select2').select2();
	});
</script>