<?php
/**
 * Theme functions.
 *
 * @package Theme_name
 * @since 1.0.0
 */

/**
 * Trims text to the specified length.
 *
 * @param string $text String of text.
 * @param int    $limit Optional. The required number of characters. Default null.
 *
 * @return string The text string of the specified limit and the ellipsis at the end.
 */
function adem_excerpt( $text, $limit = null ) {
	$output = mb_substr( $text, 0, $limit );

	return mb_strlen( $output ) < $limit ? $output : $output . '...';
}

/**
 * Clear phone number removing wrong symbols.
 *
 * @param string $tel Phone number string.
 *
 * @return string The phone string without wrong symbols.
 */
function adem_clear_tel( $tel ) {
	return preg_replace( '/[^0-9+]/', '', $tel );
}

/**
 * Retrieves or display nonce hidden field for forms without ID.
 *
 * @param int|string $action Optional. Action name. Default -1.
 * @param string     $name Optional. Nonce name. Default '_wpnonce'.
 * @param bool       $referer Optional. Whether to set the referer field for validation. Default true.
 * @param bool       $display Optional. Whether to display or return hidden form field. Default true.
 *
 * @return string Nonce field HTML markup.
 */
function adem_wp_nonce_field( $action = - 1, $name = '_wpnonce', $referer = true, $display = true ): string {
	$nonce_field = wp_nonce_field( $action, $name, $referer, false );
	$nonce_field = str_replace( 'id="' . $name . '"', '', $nonce_field );

	if ( $display ) {
		echo $nonce_field;
	}

	return $nonce_field;
}

/**
 * Generates a thumbnail of an image.
 *
 * @param int        $attachment_id Image attachment ID.
 * @param int        $width Desired image width.
 * @param int        $height Desired image height.
 * @param bool|array $crop Optional. Image cropping behavior. If false, the image will be scaled (default).
 *    If true, image will be cropped to the specified dimensions using center positions.
 * @param array      $attr Optional. Attributes for the image markup.
 * @param bool       $return_url Optional. Specifies what the function will return. The HTML of the image or its URL.
 *
 * @return string string HTML img element or string img url.
 */
function adem_dynamic_thumbnail( $attachment_id, $width, $height, $crop = true, $attr = array(), $return_url = false ) {
	$url = wp_get_attachment_url( $attachment_id );

	if ( ! $url ) {
		return '';
	}

	$img_path     = get_attached_file( $attachment_id );
	$info         = pathinfo( $img_path );
	$ext          = $info['extension'];
	$new_img_path = $info['dirname'] . '/' . $info['filename'] . '-' . $width . 'x' . $height . '.' . $ext;
	$new_img_url  = str_replace( $info['basename'], $info['filename'] . '-' . $width . 'x' . $height . '.' . $ext, $url );

	if ( ! file_exists( $new_img_path ) ) {
		$editor = wp_get_image_editor( $img_path );
		if ( ! is_wp_error( $editor ) ) {
			$editor->resize( $width, $height, $crop );
			$editor->save( $new_img_path );
		}
	}

	if ( $return_url ) {
		return $new_img_url;
	}

	$default_attr = array(
		'src' => $new_img_url,
		'alt' => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
	);
	$context      = apply_filters( 'wp_get_attachment_image_context', 'wp_get_attachment_image' );
	$attr         = wp_parse_args( $attr, $default_attr );

	$loading_attr              = $attr;
	$loading_attr['width']     = $width;
	$loading_attr['height']    = $height;
	$loading_optimization_attr = wp_get_loading_optimization_attributes(
		'img',
		$loading_attr,
		$context
	);

	$attr = array_merge( $attr, $loading_optimization_attr );

	if ( empty( $attr['srcset'] ) ) {
		$image_meta = wp_get_attachment_metadata( $attachment_id );

		if ( is_array( $image_meta ) ) {
			$size_array = array( absint( $width ), absint( $height ) );
			$srcset     = wp_calculate_image_srcset( $size_array, $new_img_url, $image_meta, $attachment_id );
			$sizes      = wp_calculate_image_sizes( $size_array, $new_img_url, $image_meta, $attachment_id );

			if ( $srcset && ( $sizes || ! empty( $attr['sizes'] ) ) ) {
				$attr['srcset'] = $srcset;

				if ( empty( $attr['sizes'] ) ) {
					$attr['sizes'] = $sizes;
				}
			}
		}
	}

	$add_auto_sizes = apply_filters( 'wp_img_tag_add_auto_sizes', true );

	if (
		$add_auto_sizes &&
		isset( $attr['loading'] ) &&
		'lazy' === $attr['loading'] &&
		isset( $attr['sizes'] ) &&
		! wp_sizes_attribute_includes_valid_auto( $attr['sizes'] )
	) {
		$attr['sizes'] = 'auto, ' . $attr['sizes'];
	}

	$attr     = array_map( 'esc_attr', $attr );
	$hwstring = image_hwstring( $width, $height );
	$html     = rtrim( "<img $hwstring" );

	foreach ( $attr as $name => $value ) {
		$html .= " $name=" . '"' . $value . '"';
	}

	$html .= ' />';

	return $html;
}
