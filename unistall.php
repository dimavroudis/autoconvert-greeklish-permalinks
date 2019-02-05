<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( 'agp_automatic' );
delete_option( 'agp_diphthongs' );
delete_option( 'agp_conversion' );
delete_transient( 'agp_notice_dismiss' );
