<?php
add_filter( 'tiny_mce_before_init', 'tmce_change_toolbar' );
function tmce_change_toolbar( $args ) {
	$style_formats            = [
		[
			'title'    => 'Ненумерованный список',
			'selector' => 'ul',
			'classes'  => 'st-ul',
		],
		[
			'title' => 'Стиль текста',
			'items' => [
				[
					'title'    => 'Regular',
					'selector' => 'ul, ol, a, p, span',
					'styles'   => [
						'font-weight' => '400',
					],
				],
				[
					'title'    => 'Medium',
					'selector' => 'ul, ol, a, p, span',
					'styles'   => [
						'font-weight' => '500',
					],
				],
				[
					'title'    => 'Extra Bold',
					'selector' => 'ul, ol, a, p, span',
					'styles'   => [
						'font-weight' => '800',
					],
				],
			]
		]
	];
	$args['fontsize_formats'] = '6px 8px 10px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px';
	$args['font_formats']     = 'Manrope=Manrope,sans-serif;Furore=Furore,sans-serif';
	$args['style_formats']    = json_encode( $style_formats );

	return $args;
}

add_action( 'after_setup_theme', 'tmce_setup' );
function tmce_setup() {
	add_action( 'init', 'tmce_buttons' );
	function tmce_buttons() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		if ( get_user_option( 'rich_editing' ) !== 'true' ) {
			return;
		}

		add_filter( 'mce_external_plugins', 'tmce_add_buttons' );
		function tmce_add_buttons( $buttons_array ) {
			$buttons_config_path             = get_template_directory_uri() . '/tmce-button.js';
			$buttons_array['custom_buttons'] = $buttons_config_path;

			return $buttons_array;
		}

		add_filter( 'mce_buttons', 'tmce_register_buttons' );
		function tmce_register_buttons( $buttons ) {
			array_push( $buttons, 'custom_buttons' );

			return $buttons;
		}
	}
}
