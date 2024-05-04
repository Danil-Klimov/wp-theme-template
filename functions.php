<?php
define( 'ADEM_THEME_VERSION', '1.0.0' );

add_action( 'after_setup_theme', 'adem_setup' );
function adem_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'search-form',
		'style',
		'script',
	) );
	add_theme_support( 'editor-styles' );
	add_editor_style();

	register_nav_menus(
		array(
			'menu_main' => 'Основное меню',
		)
	);

// Remove svg filters
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
// Return classic widgets
	add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
	add_filter( 'use_widgets_block_editor', '__return_false' );
// Allow big images
	add_filter( 'big_image_size_threshold', '__return_false' );
// Remove archive title prefix
	add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
}

// Enqueue scripts and styles.
add_action( 'wp_enqueue_scripts', 'adem_scripts' );
function adem_scripts() {
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_enqueue_style( 'fancybox', get_template_directory_uri() . '/assets/vendor/css/fancybox.css', array(), '4.0.27' );
	wp_enqueue_script( 'fancybox', get_template_directory_uri() . '/assets/vendor/js/fancybox.umd.js', array(), '4.0.27', true );
	wp_enqueue_style( 'swiper', get_template_directory_uri() . '/assets/vendor/css/swiper-bundle.min.css', array(), '8.1.5' );
	wp_enqueue_script( 'swiper', get_template_directory_uri() . '/assets/vendor/js/swiper-bundle.min.js', array(), '8.1.5', true );
	wp_enqueue_style( 'adem', get_stylesheet_uri(), array(), ADEM_THEME_VERSION );
	wp_enqueue_script( 'adem', get_template_directory_uri() . '/assets/js/main.min.js', array(), ADEM_THEME_VERSION, true );
	wp_localize_script( 'adem', 'adem_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
}

//	register thumbnails
//	add_image_size('50x80', 50, 80, true);

// excerpt
function adem_excerpt( $limit, $ID = null ) {
	return mb_substr( get_the_excerpt( $ID ), 0, $limit ) . '...';
}

require 'inc/acf.php';
require 'inc/mail.php';
require 'inc/svg.php';
require 'inc/tiny-mce.php';
require 'inc/traffic.php';
