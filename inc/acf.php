<?php
/**
 * ACF and ACF Extended settings
 *
 * @package Theme_name
 * @since 1.0.0
 */

// Return acf row index from 0.
add_filter( 'acf/settings/row_index_offset', '__return_zero' );

add_filter( 'acfe/flexible/thumbnail', 'adem_layout_thumbnail_url', 10, 3 );
/**
 * Set url for blocks preview.
 *
 * @param int|string $thumbnail  Thumbnail ID/URL.
 * @param array      $field      Field settings.
 * @param array      $layout     Layout settings.
 */
function adem_layout_thumbnail_url( $thumbnail, $field, $layout ) {
	return get_template_directory_uri() . '/layouts/blocks/' . $layout['name'] . '/preview.jpg';
}

add_action( 'acf/init', 'adem_acf_register_options_pages' );
/**
 * Registers option pages.
 */
function adem_acf_register_options_pages() {
	if ( function_exists( 'acf_add_options_page' ) ) {
		$theme_options = acf_add_options_page(
			array(
				'page_title'      => 'Настройки темы',
				'menu_title'      => 'Настройки темы',
				'menu_slug'       => 'theme-options',
				'capability'      => 'edit_posts',
				'position'        => 64,
				'update_button'   => 'Обновить',
				'updated_message' => 'Настройки обновлены',
			)
		);
	}
}

// TODO времянка. ACFE 0.9.0.5: Fix compatibility with clone on ACF 6.3.2.
add_action( 'acf/init', 'adem_acfe_fix_clone', 100 );
function adem_acfe_fix_clone() {
	$instance = acf_get_instance( 'acfe_field_clone' );
	remove_action( 'wp_ajax_acf/fields/clone/query', array( $instance, 'ajax_query' ), 5 );
}

// TODO времянка ACFE 0.9.0.5: Fix compatibility with fields on ACF 6.3.2.
add_action( 'acf/input/admin_print_footer_scripts', 'adem_acfe_fix_form_fields' );
function adem_acfe_fix_form_fields() {
	?>
	<script>
		(function($){

			if(typeof acf === 'undefined' || typeof acfe === 'undefined'){
				return;
			}

			new acf.Model({
				filters: {
					'select2_ajax_data/action=acfe/form/map_field_groups_ajax':      'ajaxData',
					'select2_ajax_data/action=acfe/form/map_field_ajax':             'ajaxData',
					'select2_ajax_data/action=acf/fields/acfe_taxonomy_terms/query': 'ajaxData',
				},

				ajaxData: function(ajaxData, data, $el, field, select){
					ajaxData.nonce = acf.get('nonce');
					return ajaxData;
				},
			});

		})(jQuery);
	</script>
	<?php
}
