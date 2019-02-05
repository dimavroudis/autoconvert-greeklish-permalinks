<?php
/**
 * The template for conversion reports
 *
 * @since    3.0.0
 *
 */

if ( $log && $log['status'] === 'done' ) {

	$posts_count_complete = $log['converted']['posts'];
	$terms_count_complete = $log['converted']['terms'];

	$posts_count_estimate = $log['estimated']['posts'];
	$terms_count_estimate = $log['estimated']['terms'];

	$errors = $log['errors'];

	$posts_txt = $posts_count_estimate == 1 ? __( 'post', 'agp' ) : __( 'posts', 'agp' );
	$terms_txt = $terms_count_estimate == 1 ? __( 'term', 'agp' ) : __( 'terms', 'agp' );

	$seconds = $log['ended'] - $log['started'];
	$hours   = floor( $seconds / 3600 );
	$mins    = floor( $seconds / 60 % 60 );
	$secs    = floor( $seconds % 60 );

	$started  = date( 'd/m/Y H:i:s', $log['started'] );
	$ended    = date( 'd/m/Y H:i:s', $log['ended'] );
	$duration = sprintf( '%02d:%02d:%02d', $hours, $mins, $secs );

	$has_errors = ! empty( $errors );

	if ( $has_errors ) {
		$errors_txt = array();
		foreach ( $errors as $error ) {
			if ( $error['type'] === 'post' ) {
				$post_id      = $error['id'];
				$link         = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $post_id ), get_the_title( $post_id ) );
				$errors_txt[] = sprintf( __( 'The post %s was not converted. %s', 'agp' ), $link, __( $error['message'] ) );
			} elseif ( $error['type'] === 'term' ) {
				$term         = get_term( $error['id'] );
				$link         = sprintf( '<a href="%s">%s</a>', get_edit_term_link( $term->term_id ), $term->name );
				$errors_txt[] = sprintf( __( 'The term %s was not converted. %s', 'agp' ), $link, __( $error['message'] ) );
			}

		}
	}

	?>
	<div class="card" id="report">
		<h2><?php echo __( 'Report of last conversion', 'agp' ) ?></h2>
		<p>
			<?php echo sprintf( __( 'Started at %s and finished at %s.', 'agp' ), $started, $ended ); ?>
			<br>
			<?php echo sprintf( __( 'Duration: %s', 'agp' ), $duration ); ?>
		</p>
		<p>
			<?php echo sprintf( __( 'Permalinks successfully converted %d/%d %s and %d/%d %s.', 'agp' ), $posts_count_complete, $posts_count_estimate, $posts_txt, $terms_count_complete, $terms_count_estimate, $terms_txt ); ?>
		</p>

		<?php if ( $has_errors ) { ?>
			<p>
				<b><?php echo __( 'The following errors occurred:', 'agp' ); ?></b>
			</p>
			<ul>
				<?php foreach ( $errors_txt as $error ) {
					echo '<li>' . $error . '</li>';
				} ?>
			</ul>
		<?php } ?>
	</div>
	<?php
}