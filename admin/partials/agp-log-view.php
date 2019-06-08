<?php
/**
 * The view for conversion reports
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

	$started  = date_i18n( 'l d M Y', $log['started'] );
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
	<h3><?php echo __( 'Last conversion report', 'agp' ) ?></h3>
	<div class="card card-success" id="report">

		<p>
			<b><?php echo $started; ?></b> ( <?php echo sprintf( __( 'Duration: %s', 'agp' ), $duration ); ?> )
		</p>
		<p>
			<?php echo sprintf( __( 'Permalinks successfully converted for %d/%d %s and %d/%d %s.', 'agp' ), $posts_count_complete, $posts_count_estimate, $posts_txt, $terms_count_complete, $terms_count_estimate, $terms_txt ); ?>
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
		<?php } else { ?>
			<p>
				<?php echo __( 'No errors reported.', 'agp' ); ?>
			</p>
		<?php } ?>
	</div>
	<?php
}