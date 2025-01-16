<?php
/**
 * The template for displaying blocks section.
 *
 * @package Theme_name
 * @since 1.0.0
 */

$acf_post_id = $args['id'] ?? false;

if ( have_rows( 'blocks', $acf_post_id ) ) {
	$counters = array();

	while ( have_rows( 'blocks', $acf_post_id ) ) {
		the_row();

		$layout = get_row_layout();

		if ( ! isset( $counters[ $layout ] ) ) {
			$counters[ $layout ] = 1;
		} else {
			++$counters[ $layout ];
		}

		get_template_part(
			'layouts/blocks/' . $layout . '/template',
			null,
			array(
				'block_id' => $counters[ $layout ],
			)
		);
	}
}
