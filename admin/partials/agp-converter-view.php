<?php

/**
 * The view for converter tab content
 *
 * @since    2.0.0
 *
 */

$log      = get_option('agp_conversion');
$hasStarted   = isset($log['status']) && $log['status'] == 'started';
$disabled = $hasStarted ? 'disabled' : '';

//Populate select fields
$args_post = array('public' => true);
$args_tax  = array('public' => true);

$tax  = get_taxonomies($args_tax, 'objects');
$post = get_post_types($args_post, 'objects');

$post_types = $taxonomies = array();

foreach ($post as $post_type) {
	$value        = $post_type->name;
	$name         = $post_type->labels->name;
	$post_types[] = array('value' => $value, 'name' => $name);
}

foreach ($tax as $taxonomy) {
	$value        = $taxonomy->name;
	$name         = $taxonomy->labels->name;
	$taxonomies[] = array('value' => $value, 'name' => $name);
}
?>
<div>
	<h3><?php _e('Convert old posts/terms', 'agp'); ?></h3>
	<div class="card">
		<form id="converterForm">
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="selectPosts"><?php _e('Post types to convert', 'agp'); ?></label>
						</th>
						<td>
							<select title="<?php _e('Post types to convert', 'agp'); ?>" name="post-types" class="select2" <?php echo $disabled ?> id="selectPosts" data-placeholder="<?php _e('Select one or more post types', 'agp'); ?>" multiple>
								<?php foreach ($post_types as $post_type) { ?>
									<option value="<?php echo esc_attr($post_type['value']); ?>">
										<?php echo $post_type['name']; ?>
									</option>
								<?php } ?>
							</select>
							<div>
								<button class="light-btn" id="selectAllPosts"><?php _e('Select All', 'agp') ?></button>
							</div>
						</td>
					</tr>
					<tr>
						<th>
							<label for="selectTaxonomies"><?php _e('Taxonomies to convert', 'agp'); ?></label>
						</th>
						<td>
							<select title="<?php _e('Taxonomies to convert', 'agp'); ?>" name="taxonomies" class="select2" <?php echo $disabled ?> id="selectTaxonomies" data-placeholder="<?php _e('Select one or more taxonomies', 'agp'); ?>" multiple>
								<?php foreach ($taxonomies as $taxonomy) { ?>
									<option value="<?php echo esc_attr($taxonomy['value']); ?>">
										<?php echo $taxonomy['name']; ?>
									</option>
								<?php } ?>
							</select>
							<div>
								<button class="light-btn" id="selectAllTaxonomies"><?php _e('Select All', 'agp') ?></button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<input name="convert-button" type="hidden" value="1" />
			<p class="submit">
				<input type="button" name="submit" id="submit" class="button button-primary" <?php echo $disabled ?> value="<?php _e('Convert Permalinks', 'agp'); ?>">
			</p>
			<div style="display: none;" id="messageOutput"></div>
		</form>
	</div>
</div>
