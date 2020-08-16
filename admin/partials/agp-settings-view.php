<?php
/**
 * The template for options tab content
 *
 * @since    3.2.0
 *
 */
?>
<div>
	<form action="options.php" method="post">
		<?php settings_fields( 'agp' ); ?>
		<?php $this->do_settings_sections( 'agp' ); ?>
		<?php submit_button( __( 'Save Settings', 'agp' ) ); ?>
	</form>
</div>