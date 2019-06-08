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

	<script>
		var automaticSwitch = document.getElementById("agpAutomatic");
		var automaticOptions = [document.getElementById("selectPosts"), document.getElementById("selectTaxonomies")];

		toggleOptions();
		automaticSwitch.addEventListener('click', function () {
			toggleOptions();
		});

		jQuery(function () {
			jQuery('.select2').select2();
			automaticOptions.forEach(function (element) {
				jQuery(element).on('select2:select', function (e) {
					var data = e.params.data;
					if (data.id === 'all_options' || data.id === 'no_options') {
						jQuery(element).val(data.id).trigger('change');
					} else {
						jQuery(element).find('option[value="all_options"]').prop('selected', false);
						jQuery(element).find('option[value="no_options"]').prop('selected', false);
						jQuery(element).trigger('change');
					}
				});
			});
		});

		function toggleOptions() {
			var customAutoSection = document.getElementById('agp_custom_automatic_options');
			if (!automaticSwitch.checked) {
				customAutoSection.style.display = 'none';
				automaticOptions.forEach(function (element) {
					element.disabled = true;
				});
			} else {
				customAutoSection.style.display = 'block';
				automaticOptions.forEach(function (element) {
					element.disabled = false;
				});
			}
		}
	</script>
</div>