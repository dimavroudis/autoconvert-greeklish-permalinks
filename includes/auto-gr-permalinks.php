<?php

function auto_gr_permalinks_is_valid_slug( $current_post_name ) {

	$is_valid_slug = true;

	$expressions = array(
		'/[αάΑΆ]/u'   => 'a',
		'/[βΒ]/u'     => 'v',
		'/[γΓ]/u'     => 'g',
		'/[δΔ]/u'     => 'd',
		'/[εέΕΈ]/u'   => 'e',
		'/[ζΖ]/u'     => 'z',
		'/[ηήΗΉ]/u'   => 'i',
		'/[θΘ]/u'     => 'th',
		'/[ιίϊΙΊΪ]/u' => 'i',
		'/[κΚ]/u'     => 'k',
		'/[λΛ]/u'     => 'l',
		'/[μΜ]/u'     => 'm',
		'/[νΝ]/u'     => 'n',
		'/[ξΞ]/u'     => 'x',
		'/[οόΟΌ]/u'   => 'o',
		'/[πΠ]/u'     => 'p',
		'/[ρΡ]/u'     => 'r',
		'/[σςΣ]/u'    => 's',
		'/[τΤ]/u'     => 't',
		'/[υύϋΥΎΫ]/u' => 'y',
		'/[φΦ]/iu'    => 'f',
		'/[χΧ]/u'     => 'ch',
		'/[ψΨ]/u'     => 'ps',
		'/[ωώ]/iu'    => 'o',
	);

	foreach ( $expressions as $key => $value ) {
		if ( preg_match( $key, $current_post_name ) ) {
			$is_valid_slug = false;
			break;
		}
	}

	return $is_valid_slug;

}

function auto_gr_permalinks_sanitize_title( $current_post_title ) {

	$diphthongs = array(
		'/[αΑ][ιίΙΊ]/u'                             => 'e',
		'/[οΟΕε][ιίΙΊ]/u'                           => 'i',
		'/[αΑ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
		'/[αΑ][υύΥΎ]/u'                             => 'av',
		'/[εΕ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
		'/[εΕ][υύΥΎ]/u'                             => 'ev',
		'/[οΟ][υύΥΎ]/u'                             => 'ou',
		'/(^|\s)[μΜ][πΠ]/u'                         => '$1b',
		'/[μΜ][πΠ](\s|$)/u'                         => 'b$1',
		'/[μΜ][πΠ]/u'                               => 'b',
		'/[νΝ][τΤ]/u'                               => 'nt',
		'/[τΤ][σΣ]/u'                               => 'ts',
		'/[τΤ][ζΖ]/u'                               => 'tz',
		'/[γΓ][γΓ]/u'                               => 'ng',
		'/[γΓ][κΚ]/u'                               => 'gk',
		'/[ηΗ][υΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u'   => 'if$1',
		'/[ηΗ][υΥ]/u'                               => 'iu',
	);

	$expressions = array(
		'/[αάΑΆ]/u'   => 'a',
		'/[βΒ]/u'     => 'v',
		'/[γΓ]/u'     => 'g',
		'/[δΔ]/u'     => 'd',
		'/[εέΕΈ]/u'   => 'e',
		'/[ζΖ]/u'     => 'z',
		'/[ηήΗΉ]/u'   => 'i',
		'/[θΘ]/u'     => 'th',
		'/[ιίϊΙΊΪ]/u' => 'i',
		'/[κΚ]/u'     => 'k',
		'/[λΛ]/u'     => 'l',
		'/[μΜ]/u'     => 'm',
		'/[νΝ]/u'     => 'n',
		'/[ξΞ]/u'     => 'x',
		'/[οόΟΌ]/u'   => 'o',
		'/[πΠ]/u'     => 'p',
		'/[ρΡ]/u'     => 'r',
		'/[σςΣ]/u'    => 's',
		'/[τΤ]/u'     => 't',
		'/[υύϋΥΎΫ]/u' => 'y',
		'/[φΦ]/iu'    => 'f',
		'/[χΧ]/u'     => 'ch',
		'/[ψΨ]/u'     => 'ps',
		'/[ωώ]/iu'    => 'o',
	);

	$diphthongs_status = get_option( 'auto_gr_permalinks_diphthongs' ) === 'enabled';

	if ( $diphthongs_status ) {
		$expressions = array_merge( $diphthongs, $expressions );
	}

	$current_post_title = preg_replace( array_keys( $expressions ), array_values( $expressions ), $current_post_title );

	return $current_post_title;

}

if ( get_option( 'auto_gr_permalinks_automatic' ) === 'enabled' ) {
	add_filter( 'sanitize_title', 'auto_gr_permalinks_sanitize_title', 1 );
} else {
	remove_filter( 'sanitize_title', 'auto_gr_permalinks_sanitize_title', 1 );
}