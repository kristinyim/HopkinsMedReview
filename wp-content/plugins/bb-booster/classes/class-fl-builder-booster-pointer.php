<?php

/**
 * Class that handles showing a tooltip/pointer on the admin bar
 *
 * Class FLBuilderBoosterTooltip
 */
final class FLBuilderBoosterPointer {

	/**
	 * Array of pointers
	 *
	 * @var array
	 */
	private $pointers = [];

	/**
	 * FLBuilderBoosterPointer constructor
	 */
	public function __construct() {

		add_action( 'wp'  ,               array( $this, 'register_pointer' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}

	/**
	 * Register all pointers on init for i18n
	 *
	 * @action wp
	 */
	public function register_pointer() {

		$pointer_id = 'bb_booster_admin_bar';

		if ( is_admin() || $this->is_dismissed( $pointer_id ) ) {

			remove_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			return;

		}

		$post_type = get_post_type_object( get_post_type() );

		if ( empty( $post_type->name ) ) {

			return;

		}

		$post_type_label = ! empty( $post_type->labels->singular_name ) ? $post_type->labels->singular_name : $post_type->name;

		$this->pointers[] = array(
			'id'      => $pointer_id,
			'target'  => '#wpadminbar #wp-admin-bar-fl-builder-frontend-edit-link',
			'cap'     => 'edit_posts',
			'options' => array(
				'content'  => wp_kses_post(
					sprintf(
						'<h3>%s</h3><p style="margin:13px 0;">%s</p>',
						__( 'Page Builder', 'bb-booster' ),
						sprintf(
							_x( 'Click here to edit content on this %s using an easy drag-and-drop builder.', '%s is the singular post type name (e.g. page)', 'bb-booster' ),
							strtolower( $post_type_label )
						)
					)
				),
				'position' => array(
					'edge'  => 'top',
					'align' => 'left',
				),
			),
		);

	}

	/**
	 * Enqueue script for showing tooltip/pointer
	 *
	 * @action wp_enqueue_scripts
	 */
	public function enqueue_scripts() {

		$pointers = array();

		foreach ( $this->pointers as $pointer ) {

			if ( current_user_can( $pointer['cap'] ) ) {

				$pointers[] = $pointer;

			}

		}

		if ( ! $pointers ) {

			return;

		}

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'wp-pointer' );

		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_script(
			'bb-booster-pointers',
			FL_BUILDER_BOOSTER_URL . "assets/js/pointers{$suffix}.js",
			array( 'jquery', 'wp-pointer' ),
			'0.0.1',
			true
		);

		$js_vars = array(
			'pointers' => $pointers,
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
		);

		wp_localize_script( 'bb-booster-pointers', 'bb_booster', $js_vars );

	}

	/**
	 * Check if a pointer has been dismissed by the current user
	 *
	 * @param  string $pointer_id
	 *
	 * @return bool
	 */
	private function is_dismissed( $pointer_id ) {

		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		return in_array( $pointer_id, $dismissed );

	}

}

new FLBuilderBoosterPointer;
