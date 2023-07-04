<?php
// return acf row index from 0
add_filter('acf/settings/row_index_offset', '__return_zero');

// url for blocks preview
add_filter('acfe/flexible/thumbnail', 'adem_layout_thumbnail_url', 10, 3);
function adem_layout_thumbnail_url($thumbnail, $field, $layout)
{
	return get_template_directory_uri() . '/layouts/blocks/' . $layout['name'] . '/preview.jpg';
}
