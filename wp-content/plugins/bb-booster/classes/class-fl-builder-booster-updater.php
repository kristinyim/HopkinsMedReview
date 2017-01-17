<?php

/**
 * Class FLBuilderBoosterUpdater
 */
final class FLBuilderBoosterUpdater {

	/**
	 * FLBuilderBoosterUpdater constructor.
	 */
	public function __construct() {

		if ( ! class_exists( 'FLUpdater' ) ) {

			define( 'FL_UPDATER_DIR', FL_BUILDER_DIR . 'includes/updater/' );

			require_once FL_UPDATER_DIR . 'classes/class-fl-updater.php';

		}

		FLUpdater::add_product(
			array(
				'name'    => __( 'Beaver Builder Booster', 'bb-booster' ),
				'version' => '1.0.5',
				'slug'    => 'bb-booster',
				'type'    => 'plugin',
			)
		);

	}

}

new FLBuilderBoosterUpdater;
