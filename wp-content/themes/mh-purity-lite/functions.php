<?php

/***** Fetch Theme Data & Options *****/

$mh_purity_lite_data = wp_get_theme('mh_purity_lite');
$mh_purity_lite_version = $mh_purity_lite_data['Version'];
$mh_purity_lite_options = get_option('mh_options');

/***** Custom Hooks *****/

function mh_html_class() {
    do_action('mh_html_class');
}
function mh_content_class() {
    do_action('mh_content_class');
}
function mh_sb_class() {
    do_action('mh_sb_class');
}
function mh_before_page_content() {
    do_action('mh_before_page_content');
}
function mh_before_post_content() {
    do_action('mh_before_post_content');
}
function mh_after_post_content() {
    do_action('mh_after_post_content');
}
function mh_loop_content() {
    do_action('mh_loop_content');
}

/***** Enable Shortcodes inside Widgets	*****/

add_filter('widget_text', 'do_shortcode');

/***** Theme Setup *****/

function mh_themes_setup() {
	global $content_width;
	if (!isset($content_width)) {
		$content_width = 650;
	}
	load_theme_textdomain('mh-purity-lite', get_template_directory() . '/languages');
	add_theme_support('custom-header', array('default-image' => get_template_directory_uri() . '/images/logo.png', 'default-text-color' => 'b3b3b3', 'width' => 300, 'height' => 80, 'flex-width' => true, 'flex-height' => true));
	add_theme_support('title-tag');
	add_theme_support('automatic-feed-links');
	add_theme_support('custom-background', array('default-color' => 'ffffff'));
	add_theme_support('post-thumbnails');
	add_theme_support('customize-selective-refresh-widgets');
	add_image_size('content', 650, 276, true);
	add_image_size('featured', 310, 174, true);
	add_image_size('cp_small', 80, 60, true);
	register_nav_menus(array('main_nav' => __('Main Navigation', 'mh-purity-lite')));
}
add_action('after_setup_theme', 'mh_themes_setup');

/***** Load CSS & JavaScript *****/

if (!function_exists('mh_scripts')) {
	function mh_scripts() {
		global $mh_purity_lite_version;
		wp_enqueue_style('mh-google-fonts', "https://fonts.googleapis.com/css?family=Lato:300italic,300,400italic,400,900|Vollkorn:400,400italic", array(), null);
		wp_enqueue_style('mh-font-awesome', get_template_directory_uri() . '/includes/font-awesome.min.css', array(), null);
		wp_enqueue_style('mh-style', get_stylesheet_uri(), array(), $mh_purity_lite_version);
		wp_enqueue_script('mh-scripts', get_template_directory_uri() . '/js/scripts.js', array('jquery'), $mh_purity_lite_version);
		if (is_singular() && comments_open() && get_option('thread_comments') == 1) {
			wp_enqueue_script('comment-reply');
		}
	}
}
add_action('wp_enqueue_scripts', 'mh_scripts');

if (!function_exists('mh_admin_scripts')) {
	function mh_admin_scripts($hook) {
		if ('appearance_page_purity' === $hook || 'widgets.php' === $hook) {
			wp_enqueue_style('mh-admin', get_template_directory_uri() . '/admin/admin.css');
		}
	}
}
add_action('admin_enqueue_scripts', 'mh_admin_scripts');

/***** Register Widget Areas / Sidebars	*****/

if (!function_exists('mh_widgets_init')) {
	function mh_widgets_init() {
		register_sidebar(array('name' => 'Sidebar', 'id' => 'sidebar', 'description' => __('Widget Area (Sidebar left/right) on Posts, Pages and Archives', 'mh-purity-lite'), 'before_widget' => '<div id="%1$s" class="sb-widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="widget-title">', 'after_title' => '</h4>'));
		register_sidebar(array('name' => 'Home 1', 'id' => 'home-1', 'description' => __('Widget Area on Homepage (Content Area)', 'mh-purity-lite'), 'before_widget' => '<div id="%1$s" class="sb-widget home-2 home-wide %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="widget-title">', 'after_title' => '</h4>'));
		register_sidebar(array('name' => 'Home 2', 'id' => 'home-2', 'description' => __('Widget Area on homepage (Sidebar)', 'mh-purity-lite'), 'before_widget' => '<div id="%1$s" class="sb-widget home-3 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="widget-title">', 'after_title' => '</h4>'));
		register_sidebar(array('name' => __('Footer 1', 'mh-purity-lite'), 'id' => 'footer-1', 'description' => __('Widget Area in Footer', 'mh-purity-lite'), 'before_widget' => '<div id="%1$s" class="footer-widget footer-1 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h6 class="footer-widget-title">', 'after_title' => '</h6>'));
		register_sidebar(array('name' => __('Footer 2', 'mh-purity-lite'), 'id' => 'footer-2', 'description' => __('Widget Area in Footer', 'mh-purity-lite'), 'before_widget' => '<div id="%1$s" class="footer-widget footer-2 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h6 class="footer-widget-title">', 'after_title' => '</h6>'));
		register_sidebar(array('name' => __('Footer 3', 'mh-purity-lite'), 'id' => 'footer-3', 'description' => __('Widget Area in Footer', 'mh-purity-lite'), 'before_widget' => '<div id="%1$s" class="footer-widget footer-3 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h6 class="footer-widget-title">', 'after_title' => '</h6>'));
	}
}
add_action('widgets_init', 'mh_widgets_init');

/***** Include Several Functions *****/

if (is_admin()) {
	require_once('admin/admin.php');
}

require_once('includes/mh-functions.php');
require_once('includes/mh-customizer.php');

?>