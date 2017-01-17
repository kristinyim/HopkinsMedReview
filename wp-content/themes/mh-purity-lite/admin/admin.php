<?php

/***** Theme Info Page *****/

if (!function_exists('mh_purity_lite_theme_info_page')) {
	function mh_purity_lite_theme_info_page() {
		add_theme_page(esc_html__('Welcome to MH Purity lite', 'mh-purity-lite'), esc_html__('Theme Info', 'mh-purity-lite'), 'edit_theme_options', 'purity', 'mh_purity_lite_display_theme_page');
	}
}
add_action('admin_menu', 'mh_purity_lite_theme_info_page');

if (!function_exists('mh_purity_lite_display_theme_page')) {
	function mh_purity_lite_display_theme_page() {
		$theme_data = wp_get_theme(); ?>
		<div class="theme-info-wrap">
			<h1>
				<?php printf(esc_html__('Welcome to %1s %2s', 'mh-purity-lite'), $theme_data->Name, $theme_data->Version); ?>
			</h1>
			<div class="theme-description">
				<?php echo $theme_data->Description; ?>
			</div>
			<hr>
			<div class="theme-links clearfix">
				<p>
					<strong><?php esc_html_e('Important Links:', 'mh-purity-lite'); ?></strong>
					<a href="<?php echo esc_url('https://www.mhthemes.com/themes/mh/purity-lite/'); ?>" target="_blank">
						<?php esc_html_e('Theme Info Page', 'mh-purity-lite'); ?>
					</a>
					<a href="<?php echo esc_url('https://www.mhthemes.com/support/'); ?>" target="_blank">
						<?php esc_html_e('Support Center', 'mh-purity-lite'); ?>
					</a>
					<a href="<?php echo esc_url('https://wordpress.org/support/theme/mh-purity-lite'); ?>" target="_blank">
						<?php esc_html_e('Support Forum', 'mh-purity-lite'); ?>
					</a>
					<a href="<?php echo esc_url('https://www.mhthemes.com/themes/showcase/'); ?>" target="_blank">
						<?php esc_html_e('MH Themes Showcase', 'mh-purity-lite'); ?>
					</a>
					<a href="<?php echo esc_url('https://wordpress.org/support/view/theme-reviews/mh-purity-lite?filter=5'); ?>" target="_blank">
						<?php esc_html_e('Rate this theme', 'mh-purity-lite'); ?>
					</a>
				</p>
			</div>
			<hr>
			<div id="getting-started">
				<h3>
					<?php printf(esc_html__('Getting Started with %s', 'mh-purity-lite'), $theme_data->Name); ?>
				</h3>
				<div class="row clearfix">
					<div class="col-1-2">
						<div class="section">
							<h4>
								<?php esc_html_e('Theme Documentation', 'mh-purity-lite'); ?>
							</h4>
							<p class="about">
								<?php printf(esc_html__('Need any help with configuring %s? The documentation for this theme includes all theme related information that is needed to get your site up and running in no time. In case you have any additional questions, feel free to reach out in the theme support forums on WordPress.org.', 'mh-purity-lite'), $theme_data->Name); ?>
							</p>
							<p>
								<a href="<?php echo esc_url('https://www.mhthemes.com/themes/mh/purity-lite/'); ?>" target="_blank" class="button button-secondary">
									<?php esc_html_e('Visit Documentation', 'mh-purity-lite'); ?>
								</a>
							</p>
						</div>
						<div class="section">
							<h4>
								<?php esc_html_e('Theme Options', 'mh-purity-lite'); ?>
							</h4>
							<p class="about">
								<?php printf(esc_html__('%s supports the Theme Customizer for all theme settings. Click "Customize Theme" to open the Customizer now.',  'mh-purity-lite'), $theme_data->Name); ?>
							</p>
							<p>
								<a href="<?php echo admin_url('customize.php'); ?>" class="button button-secondary">
									<?php esc_html_e('Customize Theme', 'mh-purity-lite'); ?>
								</a>
							</p>
						</div>
						<div class="section">
							<h4>
								<?php esc_html_e('MH Purity Pro', 'mh-purity-lite'); ?>
							</h4>
							<p class="about">
								<?php esc_html_e('If you like the free version of this theme, you will LOVE the full version of MH Purity which includes unique custom widgets, additional features and more useful options to customize your website.', 'mh-purity-lite'); ?>
							</p>
							<p>
								<a href="<?php echo esc_url('https://www.mhthemes.com/themes/mh/purity/'); ?>" target="_blank" class="button button-primary">
									<?php esc_html_e('Upgrade to MH Purity Pro', 'mh-purity-lite'); ?>
								</a>
							</p>
						</div>
					</div>
					<div class="col-1-2">
						<img src="<?php echo get_template_directory_uri(); ?>/screenshot.png" alt="Theme Screenshot" />
					</div>
				</div>
			</div>
			<hr>
			<div id="theme-author">
				<p><?php printf(esc_html__('%1s is proudly brought to you by %2s. If you like %3s: %4s.', 'mh-purity-lite'),
					$theme_data->Name,
					'<a target="_blank" href="https://www.mhthemes.com/" title="MH Themes">MH Themes</a>',
					$theme_data->Name,
					'<a target="_blank" href="https://wordpress.org/support/view/theme-reviews/mh-purity-lite?filter=5" title="MH Purity lite Review">' . esc_html__('Rate this theme', 'mh-purity-lite') . '</a>'); ?>
				</p>
			</div>
		</div> <?php
	}
}

?>