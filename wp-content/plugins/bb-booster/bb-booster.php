<?php
/**
 * Plugin Name: Beaver Builder Booster
 * Plugin URI: http://www.wpbeaverbuilder.com
 * Description: Additional functionality for Beaver Builder lite.
 * Version: 1.0.5
 * Author: The Beaver Builder Team
 * Author URI: http://www.wpbeaverbuilder.com
 * Text Domain: bb-booster
 * Domain Path: /languages
 * Copyright: (c) 2016 Beaver Builder
 * License: GPL-2.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
define( 'FL_BUILDER_BOOSTER_DIR', plugin_dir_path( __FILE__ ) );
define( 'FL_BUILDER_BOOSTER_URL', plugins_url( '/', __FILE__ ) );

final class FLBuilderBooster {

	/**
	 * Load languages
	 *
	 * @action plugins_loaded
	 */
	public static function i18n() {

		load_plugin_textdomain( 'bb-booster', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Run booster after bb-plugin
	 *
	 * @action plugins_loaded
	 */
	public static function init() {

		if ( ! class_exists( 'FLBuilder' ) || ! defined( 'FL_BUILDER_LITE' ) || ! FL_BUILDER_LITE ) {

			return;

		}

		if ( method_exists( 'FLBuilder', 'register_templates' ) ) {

			FLBuilder::register_templates(  FL_BUILDER_BOOSTER_DIR . 'templates/templates.dat' );

		}

		if ( false === get_option( 'wpem_done' ) ) {

			add_action( 'admin_notices', array( __CLASS__, 'display_admin_notices' ) );

			return;

		}

		foreach ( glob( FL_BUILDER_BOOSTER_DIR . 'classes/class-fl-builder-booster-*.php' ) as $path ) {

			if ( is_readable( $path ) ) {

				require_once $path;

			}

		}

		add_action( 'wp_ajax_bb_booster_post_previewed_is_editable', array( __CLASS__, 'post_previewed_is_editable' ) );

	}

	/**
	 * Display an admin notice
	 *
	 * @action admin_notices
	 *
	 * @return void
	 */
	public static function display_admin_notices() {

		if ( defined( 'WPEM_DOING_STEPS' ) && WPEM_DOING_STEPS ) {

			return;

		}

		printf(
			'<div class="error"><p>%s</p></div>',
			__( 'The Beaver Builder Booster plugin is not compatible with your host.', 'bb-booster' )
		);

	}

	/**
	 * Check is the currently previewed post is editable
	 */
	public static function post_previewed_is_editable() {

		check_ajax_referer( 'bb_booster_post_previewed_is_editable' );

		$url     = filter_input( INPUT_POST, 'url', FILTER_SANITIZE_STRING );
		$post_id = url_to_postid( $url );

		if ( static::is_post_editable( $post_id ) ) {

			wp_send_json_success();

		}

		wp_send_json_error();

	}

	/**
	 * Check if the currently previewed page/post is editable
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public static function is_post_editable( $post_id ) {

		if ( 0 === $post_id ) {

			return false;

		}

		$post       = get_post( absint( $post_id ) );
		$post_types = FLBuilderModel::get_post_types();
		$user_can   = current_user_can( 'edit_post', $post->ID );

		if ( in_array( $post->post_type, $post_types ) && $user_can ) {

			return true;

		}

	}

}

add_action( 'plugins_loaded', array( 'FLBuilderBooster', 'i18n' ) );
add_action( 'plugins_loaded', array( 'FLBuilderBooster', 'init' ) );
