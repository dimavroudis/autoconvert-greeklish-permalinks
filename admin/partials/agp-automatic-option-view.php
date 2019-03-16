<?php
/**
 * The view for automatic conversion option
 *
 * @since    3.0.0
 *
 */

$options   = get_option( 'agp_automatic' );
$automatic = $options === 'enabled' ? 'checked' : '';

?>
<label class="agp-switch">
	<input type="hidden" name="agp_automatic" value="disabled">
	<input type="checkbox" id="agpAutomatic" name="agp_automatic"
		   value="enabled" <?php echo $automatic; ?>>
	<span class="agp-slider round"></span>
</label>