<?php

if (!defined('ADEM_THEME_VERSION')) {
	// Replace the version number of the theme on each release.
	define('ADEM_THEME_VERSION', '1.0.0');
}

add_action('after_setup_theme', 'adem_setup');
if (!function_exists('adem_setup')) {
	function adem_setup()
	{
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_theme_support('html5');
		add_theme_support('editor-styles');
		add_editor_style();

		register_nav_menus(
			array(
				'menu_main' => 'Основное меню',
			)
		);
	}

	//	register thumbnails
//	add_image_size('50x80', 50, 80, true);
}

// Enqueue scripts and styles.
add_action('wp_enqueue_scripts', 'adem_scripts');
function adem_scripts()
{
	wp_enqueue_style('fancybox', get_template_directory_uri() . '/assets/vendor/css/fancybox.css', array(), '4.0.27');
	wp_enqueue_script('fancybox', get_template_directory_uri() . '/assets/vendor/js/fancybox.umd.js', array(), '4.0.27', true);
	wp_enqueue_style('swiper', get_template_directory_uri() . '/assets/vendor/css/swiper-bundle.min.css', array(), '8.1.5');
	wp_enqueue_script('swiper', get_template_directory_uri() . '/assets/vendor/js/swiper-bundle.min.js', array(), '8.1.5', true);
	wp_enqueue_style('adem', get_stylesheet_uri(), array(), ADEM_THEME_VERSION);
	wp_enqueue_script('adem', get_template_directory_uri() . '/assets/js/main.min.js', array(), ADEM_THEME_VERSION, true);
	wp_localize_script('adem', 'adem_ajax', array('url' => admin_url('admin-ajax.php')));
}

// Return classic widgets
add_filter('gutenberg_use_widgets_block_editor', '__return_false');
add_filter('use_widgets_block_editor', '__return_false');

// disable scale images
add_filter('big_image_size_threshold', '__return_false');

// excerpt
function adem_excerpt($limit, $ID = null)
{
	return mb_substr(get_the_excerpt($ID), 0, $limit) . '...';
}

require 'inc/acf.php';
require 'inc/mail.php';
require 'inc/svg.php';
require 'inc/tiny-mce.php';
require 'inc/traffic.php';
