<?php
/**
 * The view for converter tab content
 *
 * @since    2.0.0
 *
 */

$log      = get_option( 'agp_conversion' );
$status   = $log['status'] === 'started';
$disabled = $status ? 'disabled' : '';


//Populate select fields
$args_post = array( 'public' => true );
$args_tax  = array( 'public' => true );

$tax  = get_taxonomies( $args_tax, 'objects' );
$post = get_post_types( $args_post, 'objects' );

$post_types = $taxonomies = array();

foreach ( $post as $post_type ) {
	$value        = $post_type->name;
	$name         = $post_type->labels->name;
	$post_types[] = array( 'value' => $value, 'name' => $name );
}

foreach ( $tax as $taxonomy ) {
	$value        = $taxonomy->name;
	$name         = $taxonomy->labels->name;
	$taxonomies[] = array( 'value' => $value, 'name' => $name );
}
?>
<div>
	<h3><?php _e( 'Convert old posts/terms', 'agp' ); ?></h3>
	<div class="card">
		<p><?php _e( 'Depending on your server and the amount of permalinks that needs to be converted, the conversion might take a few minutes to complete.', 'agp' ) ?></p>
		<form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ); ?>" method="post">
			<table class="form-table">
				<tbody>
				<tr>
					<th>
						<label for="selectPosts"><?php _e( 'Post types to convert', 'agp' ); ?></label>
					</th>
					<td>
						<select title="<?php _e( 'Post types to convert', 'agp' ); ?>"
								name="post-types[]" class="select2" <?php echo $disabled ?> id="selectPosts"
								data-placeholder="<?php _e( 'Select one or more post types', 'agp' ); ?>"
								multiple>
							<?php foreach ( $post_types as $post_type ) { ?>
								<option value="<?php echo esc_attr( $post_type['value'] ); ?>">
									<?php echo $post_type['name']; ?>
								</option>
							<?php } ?>
						</select>
						<div>
							<button class="light-btn" id="selectAllPosts"><?php _e( 'Select All', 'agp' ) ?></button>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<label for="selectTaxonomies"><?php _e( 'Taxonomies to convert', 'agp' ); ?></label>
					</th>
					<td>
						<select title="<?php _e( 'Taxonomies to convert', 'agp' ); ?>"
								name="taxonomies[]" class="select2" <?php echo $disabled ?> id="selectTaxonomies"
								data-placeholder="<?php _e( 'Select one or more taxonomies', 'agp' ); ?>"
								multiple>
							<?php foreach ( $taxonomies as $taxonomy ) { ?>
								<option value="<?php echo esc_attr( $taxonomy['value'] ); ?>">
									<?php echo $taxonomy['name']; ?>
								</option>
							<?php } ?>
						</select>
						<div>
							<button class="light-btn"
									id="selectAllTaxonomies"><?php _e( 'Select All', 'agp' ) ?></button>
						</div>
					</td>
				</tr>
				</tbody>
			</table>

			<input name="convert-button" type="hidden" value="1"/>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary"
					<?php echo $disabled ?>
					   value="<?php _e( 'Convert Permalinks', 'agp' ); ?>">
			</p>
		</form>
	</div>

	<?php include_once( 'agp-log-view.php' ); ?>

</div>

<script>
	jQuery(function () {
		jQuery('.select2').select2();
		jQuery("#selectAllPosts").click(function (e) {
			e.preventDefault();
			jQuery("#selectPosts > option").prop("selected", "selected");
			jQuery("#selectPosts").trigger("change");
		});
		jQuery("#selectAllTaxonomies").click(function (e) {
			e.preventDefault();
			jQuery("#selectTaxonomies > option").prop("selected", "selected");
			jQuery("#selectTaxonomies").trigger("change");
		});
	});
</script>