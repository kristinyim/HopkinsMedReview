<?php

/**
 * Module loader class for the booster.
 *
 * @since 1.0
 */
final class FLBuilderBoosterModules {

	/**
	 * FLBuilderBoosterModules constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'load_modules' ) );
		add_filter( 'fl_builder_register_module', array( $this, 'disable_modules' ), 10, 2 );

	}

	/**
	 * Loads the booster modules if the modules folder exists.
	 * Modules are added to the booster when it's built for
	 * production, so we need to make sure the modules folder
	 * exists for dev environments.
 	 *
	 * @action init
	 * @since 1.0
	 *
	 * @return void
	 */
	public function load_modules() {

		$modules = array(
			'FLButtonModule'        => 'button',
			'FLCalloutModule'       => 'callout',
			'FLContentSliderModule' => 'content-slider',
			'FLCtaModule'           => 'cta',
			'FLGalleryModule'       => 'gallery',
			'FLHeadingModule'       => 'heading',
			'FLIconModule'          => 'icon',
			'FLPricingTableModule'  => 'pricing-table',
			'FLSeparatorModule'     => 'separator',
			'FLSlideshowModule'     => 'slideshow',
		);

		foreach ( $modules as $class => $slug ) {

			$path = FL_BUILDER_BOOSTER_DIR . "modules/{$slug}/{$slug}.php";

			if ( ! class_exists( $class ) && is_readable( $path ) ) {

				require_once $path;

			}

		}

	}

	/**
	 * Disables modules that are only included to support other
	 * modules such as the Button module for the Callout module.
 	 *
	 * @filter fl_builder_register_module
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function disable_modules( $enabled, $module ) {

		if ( in_array( $module->slug, array( 'button', 'icon' ) ) ) {
			return false;
		}
		
		return $enabled;
	}

}

new FLBuilderBoosterModules;
