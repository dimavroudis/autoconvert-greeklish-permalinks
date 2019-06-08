<?php
/**
 * The template for post types selection for automatic conversion
 *
 * @since    3.2.0
 *
 */
$automatic_posts = get_option( 'agp_automatic_post' );
$post_types      = array();

$select_all  = false;
$select_none = false;

if ( $automatic_posts ) {
	foreach ( $automatic_posts as $selected_post_type ) {
		if ( $selected_post_type === 'all_options' ) {
			$select_all = true;
		}
		if ( $selected_post_type === 'no_options' ) {
			$select_none = true;
		}
	}
} else {
	$select_all = true;
}
$post_types[] = array( 'value' => 'all_options', 'name' => __( 'All post types', 'agp' ), 'selected' => $select_all );

$args_post = array( 'public' => true );
$post      = get_post_types( $args_post, 'objects' );

foreach ( $post as $post_type ) {
	$value    = $post_type->name;
	$name     = $post_type->labels->name;
	$selected = false;
	if ( $automatic_posts ) {
		foreach ( $automatic_posts as $selected_post ) {
			if ( $selected_post === $value ) {
				$selected = true;
				break;
			}
		}
	}
	$post_types[] = array( 'value' => $value, 'name' => $name, 'selected' => $selected );
}

$post_types[] = array( 'value' => 'no_options', 'name' => __( 'No post types', 'agp' ), 'selected' => $select_none );

?>
<select title="<?php _e( 'Post types to convert', 'agp' ); ?>" required
		name="agp_automatic_post[]" class="select2" id="selectPosts"
		data-placeholder="<?php _e( 'Select one or more post types', 'agp' ); ?>"
		multiple>
	<?php foreach ( $post_types as $post_type ) { ?>
		<option value="<?php echo esc_attr( $post_type['value'] ); ?>" <?php echo $post_type['selected'] ? 'selected' : ''; ?>>
			<?php echo $post_type['name']; ?>
		</option>
	<?php } ?>
</select>
