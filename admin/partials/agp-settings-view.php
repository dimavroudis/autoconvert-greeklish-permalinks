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
		<?php do_settings_sections( 'agp' ); ?>
		<?php submit_button( __( 'Save Settings', 'agp' ) ); ?>
	</form>

	<script>
		var automaticOption = document.getElementById("agpAutomatic");
		var postsOption = document.getElementById("selectPosts");
		var taxonomiesOption = document.getElementById("selectTaxonomies");

		var postsRow = postsOption.closest('tr');
		var taxonomiesRow = taxonomiesOption.closest('tr');

		toggleOptions();
		automaticOption.addEventListener('click', function () {
			toggleOptions();
		});


		function toggleOptions() {
			if (!automaticOption.checked) {
				postsRow.style.display = 'none';
				postsOption.disabled = true;
				taxonomiesRow.style.display = 'none';
				taxonomiesOption.disabled = true;
			} else {
				postsRow.style.display = 'table-row';
				postsOption.disabled = false;
				taxonomiesRow.style.display = 'table-row';
				taxonomiesOption.disabled = false;
			}
		}

		jQuery(function () {
			jQuery('.select2').select2();
			jQuery(postsOption).on('select2:select', function (e) {
				var data = e.params.data;
				if (data.id === 'all_options') {
					jQuery(postsOption).val('all_options').trigger('change');
				} else {
					var selectall = jQuery(postsOption).find('option[value="all_options"]');
					selectall.prop('selected', false);
					jQuery(postsOption).trigger('change');
				}
			});
			jQuery(taxonomiesOption).on('select2:select', function (e) {
				var data = e.params.data;
				if (data.id === 'all_options') {
					jQuery(taxonomiesOption).val('all_options').trigger('change');
				} else {
					var selectall = jQuery(taxonomiesOption).find('option[value="all_options"]');
					selectall.prop('selected', false);
					jQuery(taxonomiesOption).trigger('change');
				}
			});
		});
	</script>
</div>