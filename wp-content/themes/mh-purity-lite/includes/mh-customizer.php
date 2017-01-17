<?php

function mh_purity_lite_customize_register($wp_customize) {

	/***** Add Custom Control Functions *****/

	class MH_Purity_Lite_Upgrade extends WP_Customize_Control {
        public function render_content() {  ?>
        	<p class="mh-upgrade-thumb">
        		<img src="<?php echo get_template_directory_uri(); ?>/images/mh_purity.png" />
        	</p>
        	<p class="customize-control-title mh-upgrade-title">
        		<?php esc_html_e('MH Purity Pro', 'mh-purity-lite'); ?>
        	</p>
        	<p class="textfield mh-upgrade-text">
        		<?php esc_html_e('If you like the free version of this theme, you will LOVE the full version of MH Purity which includes unique custom widgets, additional features and more useful options to customize your website.', 'mh-purity-lite'); ?>
			</p>
			<p class="customize-control-title mh-upgrade-title">
        		<?php esc_html_e('Additional Features:', 'mh-purity-lite'); ?>
        	</p>
        	<ul class="mh-upgrade-features">
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Options to modify color scheme', 'mh-purity-lite'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Several additional widget areas', 'mh-purity-lite'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('Additional custom widgets', 'mh-purity-lite'); ?>
	        	</li>
	        	<li class="mh-upgrade-feature-item">
	        		<?php esc_html_e('and more...', 'mh-purity-lite'); ?>
	        	</li>
        	</ul>
			<p class="mh-button mh-upgrade-button">
				<a href="https://www.mhthemes.com/themes/mh/purity/" target="_blank" class="button button-secondary">
					<?php esc_html_e('Upgrade to MH Purity Pro', 'mh-purity-lite'); ?>
				</a>
			</p>
			<p class="mh-button">
				<a href="https://www.mhthemes.com/themes/showcase/" target="_blank" class="button button-secondary">
					<?php esc_html_e('MH Themes Showcase', 'mh-purity-lite'); ?>
				</a>
			</p>
			<p class="mh-button">
				<a href="https://www.mhthemes.com/themes/mh/purity-lite/" target="_blank" class="button button-secondary">
					<?php esc_html_e('Theme Documentation', 'mh-purity-lite'); ?>
				</a>
			</p>
			<p class="mh-button">
				<a href="https://wordpress.org/support/theme/mh-purity-lite" target="_blank" class="button button-secondary">
					<?php esc_html_e('Support Forum', 'mh-purity-lite'); ?>
				</a>
			</p><?php
        }
    }

    /***** Add Panels *****/

	$wp_customize->add_panel('mh_theme_options', array('title' => esc_html__('Theme Options', 'mh-purity-lite'), 'description' => '', 'capability' => 'edit_theme_options', 'theme_supports' => '', 'priority' => 1));

	/***** Add Sections *****/

	$wp_customize->add_section('mh_purity_lite_general', array('title' => esc_html__('General', 'mh-purity-lite'), 'priority' => 1, 'panel' => 'mh_theme_options'));
	$wp_customize->add_section('mh_purity_lite_upgrade', array('title' => esc_html__('More Features', 'mh-purity-lite'), 'priority' => 2, 'panel' => 'mh_theme_options'));

    /***** Add Settings *****/

    $wp_customize->add_setting('mh_options[excerpt_length]', array('default' => 110, 'type' => 'option', 'sanitize_callback' => 'mh_sanitize_integer'));
    $wp_customize->add_setting('mh_options[excerpt_more]', array('default' => '[...]', 'type' => 'option', 'sanitize_callback' => 'mh_sanitize_text'));
    $wp_customize->add_setting('mh_options[sb_position]', array('default' => 'right', 'type' => 'option', 'sanitize_callback' => 'mh_sanitize_select'));
	$wp_customize->add_setting('mh_options[premium_version_upgrade]', array('default' => '', 'type' => 'option', 'sanitize_callback' => 'esc_attr'));

    /***** Add Controls *****/

    $wp_customize->add_control('excerpt_length', array('label' => esc_html__('Excerpt Length in Characters', 'mh-purity-lite'), 'section' => 'mh_purity_lite_general', 'settings' => 'mh_options[excerpt_length]', 'priority' => 1, 'type' => 'text'));
    $wp_customize->add_control('excerpt_more', array('label' => esc_html__('Custom Excerpt More Text', 'mh-purity-lite'), 'section' => 'mh_purity_lite_general', 'settings' => 'mh_options[excerpt_more]', 'priority' => 2, 'type' => 'text'));
    $wp_customize->add_control('sb_position', array('label' => esc_html__('Position of default Sidebar', 'mh-purity-lite'), 'section' => 'mh_purity_lite_general', 'settings' => 'mh_options[sb_position]', 'priority' => 3, 'type' => 'select', 'choices' => array('left' => esc_html__('Left', 'mh-purity-lite'), 'right' => esc_html__('Right', 'mh-purity-lite'))));
	$wp_customize->add_control(new MH_Purity_Lite_Upgrade($wp_customize, 'premium_version_upgrade', array('section' => 'mh_purity_lite_upgrade', 'settings' => 'mh_options[premium_version_upgrade]', 'priority' => 1)));
}
add_action('customize_register', 'mh_purity_lite_customize_register');

/***** Data Sanitization *****/

function mh_sanitize_text($input) {
    return wp_kses_post(force_balance_tags($input));
}
function mh_sanitize_integer($input) {
    return strip_tags($input);
}
function mh_sanitize_checkbox($input) {
    if ($input == 1) {
        return 1;
    } else {
        return '';
    }
}
function mh_sanitize_select($input) {
    $valid = array(
        'left' => esc_html__('Left', 'mh-purity-lite'),
        'right' => esc_html__('Right', 'mh-purity-lite'),
    );
    if (array_key_exists($input, $valid)) {
        return $input;
    } else {
        return '';
    }
}

/***** Return Theme Options / Set Default Options *****/

if (!function_exists('mh_purity_lite_theme_options')) {
	function mh_purity_lite_theme_options() {
		$theme_options = wp_parse_args(
			get_option('mh_options', array()),
			mh_purity_lite_default_options()
		);
		return $theme_options;
	}
}

if (!function_exists('mh_purity_lite_default_options')) {
	function mh_purity_lite_default_options() {
		$default_options = array(
			'excerpt_length' => 110,
			'excerpt_more' => '[...]',
			'sb_position' => 'right'
		);
		return $default_options;
	}
}

/***** Enqueue Customizer CSS *****/

function mh_purity_lite_customizer_css() {
	wp_enqueue_style('mh-customizer', get_template_directory_uri() . '/admin/customizer.css', array());
}
add_action('customize_controls_print_styles', 'mh_purity_lite_customizer_css');

?>