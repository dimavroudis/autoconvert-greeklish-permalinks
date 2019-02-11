<?php
/**
 * The view for diphthongs option
 *
 * @since    3.0.0
 *
 */

$options = get_option( 'agp_diphthongs' );

?>
<p>
	<label for="agp_diphthongs_disable">
		<input type="radio" id="agp_diphthongs_disable"
			   name="agp_diphthongs" <?php echo $options !== 'enabled' ? 'checked' : ''; ?>
			   value="disabled"><b><?php _e( 'Simple conversion', 'agp' ) ?></b> <?php _e( 'For example "ει" becomes "ei", "οι" becomes "οi", "μπ" becomes "mp" etc', 'agp' ); ?>
	</label>
</p>
<p>
	<label for="agp_diphthongs_enable">
		<input type="radio" id="agp_diphthongs_enable"
			   name="agp_diphthongs" <?php echo $options === 'enabled' ? 'checked' : ''; ?>
			   value="enabled"><label
				for="agp_diphthongs_enable"><b><?php _e( 'Advanced conversion', 'agp' ) ?></b> <?php _e( 'For example "ει", "οι" becomes "i", "μπ" becomes "b" etc', 'agp' ); ?>
		</label>
</p>