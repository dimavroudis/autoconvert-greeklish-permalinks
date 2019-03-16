<?php
/**
 * The template for taxonomies selection for automatic conversion
 *
 * @since    3.2.0
 *
 */

$automatic_taxonomies = get_option( 'agp_automatic_tax' );
$taxonomies           = array();

if ( $automatic_taxonomies ) {
	$select_all = false;
	foreach ( $automatic_taxonomies as $selected_taxonomy ) {
		if ( $selected_taxonomy === 'all_options' ) {
			$select_all = true;
		}
	}
} else {
	$select_all = true;
}
$taxonomies[] = array( 'value' => 'all_options', 'name' => __( 'All taxonomies', 'agp' ), 'selected' => $select_all );

$args_tax = array( 'public' => true );
$tax      = get_taxonomies( $args_tax, 'objects' );

foreach ( $tax as $taxonomy ) {
	$value    = $taxonomy->name;
	$name     = $taxonomy->labels->name;
	$selected = false;
if ( $automatic_taxonomies ) {
	foreach ( $automatic_taxonomies as $selected_taxonomy ) {
		if ( $selected_taxonomy === $value ) {
			$selected = true;
			break;
		}
	}
}
	$taxonomies[] = array( 'value' => $value, 'name' => $name, 'selected' => $selected );
}


?>

<select title="<?php _e( 'Taxonomies to convert', 'agp' ); ?>" required
		name="agp_automatic_tax[]" class="select2" id="selectTaxonomies"
		data-placeholder="<?php _e( 'Select one or more taxonomies', 'agp' ); ?>"
		multiple>
	<?php foreach ( $taxonomies as $taxonomy ) { ?>
		<option value="<?php echo esc_attr( $taxonomy['value'] ); ?>" <?php echo $taxonomy['selected'] ? 'selected' : ''; ?>>
			<?php echo $taxonomy['name']; ?>
		</option>
	<?php } ?>
</select>
