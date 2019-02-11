<?php
/**
 * The view for admin page
 *
 * @since    2.0.0
 *
 */
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

?>
	<div class="wrap agp-plugin">
		<?php
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'permalink_settings';
		?>
		<h1><?php _e( 'Convert Permalinks to Greeklish', 'agp' ) ?></h1>
		<h2 class="nav-tab-wrapper">
			<a href="?page=agp&tab=permalink_settings"
			   class="nav-tab <?php echo $active_tab == 'permalink_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'agp' ); ?></a>
			<a href="?page=agp&tab=generate_permalinks"
			   class="nav-tab <?php echo $active_tab == 'generate_permalinks' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Convert old posts/terms', 'agp' ); ?></a>
		</h2>
		<?php
		if ( $active_tab == 'permalink_settings' ) {
			include_once( 'agp-settings-view.php' );
		} else {
			include_once( 'agp-converter-view.php' );
		} ?>
	</div>
<?php
