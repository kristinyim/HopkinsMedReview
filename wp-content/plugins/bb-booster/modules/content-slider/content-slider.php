<?php

/**
 * @class FLContentSliderModule
 */
class FLContentSliderModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(array(
			'name'          	=> __('Content Slider', 'bb-booster'),
			'description'   	=> __('Displays multiple slides with an optional heading and call to action.', 'bb-booster'),
			'category'      	=> __('Advanced Modules', 'bb-booster'),
			'partial_refresh'	=> true
		));

		$this->add_css('jquery-bxslider');
		$this->add_js('jquery-bxslider');
	}

	/**
	 * @method render_background
	 */
	public function render_background($slide)
	{
		// Background photo
		if($slide->bg_layout == 'photo' && !empty($slide->bg_photo_src)) {
			echo '<div class="fl-slide-bg-photo" style="background-image: url(' . $slide->bg_photo_src . ');"></div>';
		}
		// Background video
		elseif($slide->bg_layout == 'video' && !empty($slide->bg_video)) {
			echo '<div class="fl-slide-bg-video">' . $slide->bg_video . '</div>';
		}
		
		// Background link
		if(!empty($slide->link) && ($slide->bg_layout == 'photo' || $slide->bg_layout == 'color')) {
			echo '<a class="fl-slide-bg-link" href="' . $slide->link . '" target="' . $slide->link_target. '"></a>';
		}
	}

	/**
	 * @method render_content
	 */
	public function render_content($slide)
	{
		global $wp_embed;
	
		if($slide->content_layout == 'none' || $slide->bg_layout == 'video') {
			return;
		}
		
		echo '<div class="fl-slide-content-wrap">';
		echo '<div class="fl-slide-content">';

		if(!empty($slide->title)) {
			echo '<' . $slide->title_tag . ' class="fl-slide-title">' . $slide->title . '</' . $slide->title_tag . '>';
		}
		if(!empty($slide->text)) {
			echo '<div class="fl-slide-text">' . wpautop( $wp_embed->autoembed( $slide->text ) ) . $this->render_link($slide) . '</div>';
		}

		$this->render_button($slide);

		echo '</div>';
		echo '</div>';
	}

	/**
	 * @method render_media
	 */
	public function render_media($slide)
	{
		if($slide->content_layout == 'none' || $slide->bg_layout == 'video') {
			return;
		}
		
		// Photo
		if($slide->content_layout == 'photo' && !empty($slide->fg_photo_src)) {
			
			$alt = get_post_meta($slide->fg_photo, '_wp_attachment_image_alt', true);
			
			echo '<div class="fl-slide-photo-wrap">';
			echo '<div class="fl-slide-photo">';
			
			if(!empty($slide->link)) {
				echo '<a href="' . $slide->link . '" target="' . $slide->link_target. '">';
			}
			
			echo '<img class="fl-slide-photo-img wp-image-' . $slide->fg_photo . '" src="' . $slide->fg_photo_src . '" alt="' . esc_attr( $alt ) . '" />';
			
			if(!empty($slide->link)) {
				echo '</a>';
			}
			
			echo '</div>';
			echo '</div>';
		}
		// Video
		elseif($slide->content_layout == 'video' && !empty($slide->fg_video)) {
			echo '<div class="fl-slide-photo-wrap">';
			echo '<div class="fl-slide-photo">' . $slide->fg_video . '</div>';
			echo '</div>';
		}
	}

	/**
	 * @method render_mobile_media
	 */
	public function render_mobile_media($slide)
	{
		if($slide->bg_layout == 'video') {
			return;
		}
		
		// Photo
		if($slide->content_layout == 'photo') {

			$src = '';
			$alt = '';

			if($slide->r_photo_type == 'main' && !empty($slide->fg_photo_src)) {
				$id  = $slide->fg_photo;
				$src = $slide->fg_photo_src;
				$alt = get_post_meta($slide->bg_photo, '_wp_attachment_image_alt', true);
			}
			else if($slide->r_photo_type == 'another' && !empty($slide->r_photo_src)) {
				$id  = $slide->r_photo;
				$src = $slide->r_photo_src;
				$alt = get_post_meta($slide->r_photo, '_wp_attachment_image_alt', true);
			}

			if(!empty($src)) {
				echo '<div class="fl-slide-mobile-photo">';
				echo '<img class="fl-slide-mobile-photo-img wp-image-' . $id . '" src="' . $src . '" alt="' . esc_attr( $alt ) . '" />';
				echo '</div>';
			}
		}
		// Video
		elseif($slide->content_layout == 'video' && !empty($slide->fg_video)) {
			echo '<div class="fl-slide-mobile-photo">' . $slide->fg_video . '</div>';
		}
		// BG Photo
		elseif($slide->bg_layout == 'photo') {

			$src = '';
			$alt = '';

			if($slide->r_photo_type == 'main' && !empty($slide->bg_photo_src)) {
				$id  = $slide->bg_photo;
				$src = $slide->bg_photo_src;
				$alt = get_post_meta($slide->bg_photo, '_wp_attachment_image_alt', true);
			}
			else if($slide->r_photo_type == 'another' && !empty($slide->r_photo_src)) {
				$id  = $slide->r_photo;
				$src = $slide->r_photo_src;
				$alt = get_post_meta($slide->r_photo, '_wp_attachment_image_alt', true);
			}

			if(!empty($src)) {
				echo '<div class="fl-slide-mobile-photo">';
				echo '<img class="fl-slide-mobile-photo-img wp-image-' . $id . '" src="' . $src . '" alt="' . esc_attr( $alt ) . '" />';
				echo '</div>';
			}
		}
	}

	/**
	 * @method render_link
	 */
	public function render_link($slide)
	{
		if($slide->cta_type == 'link') {
			return '<a href="' . $slide->link . '" target="' . $slide->link_target . '" class="fl-slide-cta-link">' . $slide->cta_text . '</a>';
		}
	}

	/**
	 * @method render_button
	 */
	public function render_button($slide)
	{
		if($slide->cta_type == 'button') {
			
			if ( ! isset( $slide->btn_style ) ) {
				$slide->btn_style = 'flat';
			}

			$btn_settings = array(
				'align'             => '',
				'bg_color'          => $slide->btn_bg_color,
				'bg_hover_color'    => $slide->btn_bg_hover_color,
				'bg_opacity'        => isset( $slide->btn_bg_opacity ) ? $slide->btn_bg_opacity : 0,
				'border_radius'     => $slide->btn_border_radius,
				'border_size'       => isset( $slide->btn_border_size ) ? $slide->btn_border_size : 2,
				'font_size'         => $slide->btn_font_size,
				'icon'              => isset( $slide->btn_icon ) ? $slide->btn_icon : '',
				'icon_position'     => isset( $slide->btn_icon_position ) ? $slide->btn_icon_position : 'before',
				'icon_animation'    => isset( $slide->btn_icon_animation ) ? $slide->btn_icon_animation : 'before',
				'link'              => $slide->link,
				'link_nofollow'		=> isset( $slide->link_nofollow ) ? $slide->link_nofollow : 'no',
				'link_target'       => $slide->link_target,
				'padding'           => $slide->btn_padding,
				'style'             => ( isset( $slide->btn_3d ) && $slide->btn_3d ) ? 'gradient' : $slide->btn_style,
				'text'              => $slide->cta_text,
				'text_color'        => $slide->btn_text_color,
				'text_hover_color'  => $slide->btn_text_hover_color,
				'width'             => 'auto'
			);

			echo '<div class="fl-slide-cta-button">';
			FLBuilder::render_module_html('button', $btn_settings);
			echo '</div>';
		}
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLContentSliderModule', array(
	'general'       => array(
		'title'         => __('General', 'bb-booster'),
		'sections'      => array(
			'general'       => array(
				'title'         => '',
				'fields'        => array(
					'height'        => array(
						'type'          => 'text',
						'label'         => __('Height', 'bb-booster'),
						'default'       => '400',
						'maxlength'     => '4',
						'size'          => '5',
						'description'   => 'px',
						'help'          => __('This setting is the minimum height of the content slider. Content will expand the height automatically.', 'bb-booster')
					),
					'auto_play'     => array(
						'type'          => 'select',
						'label'         => __('Auto Play', 'bb-booster'),
						'default'       => '1',
						'options'       => array(
							'0'             => __('No', 'bb-booster'),
							'1'             => __('Yes', 'bb-booster')
						),
						'toggle'        => array(
							'1'             => array(
								'fields'        => array('play_pause')
							)
						)
					),
					'delay'         => array(
						'type'          => 'text',
						'label'         => __('Delay', 'bb-booster'),
						'default'       => '5',
						'maxlength'     => '4',
						'size'          => '5',
						'description'   => _x( 'seconds', 'Value unit for form field of time in seconds. Such as: "5 seconds"', 'bb-booster' )
					),
					'loop'          => array(
						'type'          => 'select',
						'label'         => __('Loop', 'bb-booster'),
						'default'       => 'true',
						'options'       => array(
							'false'            	=> __('No', 'bb-booster'),
							'true'				=> __('Yes', 'bb-booster'),
						)
					),
					'transition'    => array(
						'type'          => 'select',
						'label'         => __('Transition', 'bb-booster'),
						'default'       => 'slide',
						'options'       => array(
							'horizontal'    => _x( 'Slide', 'Transition type.', 'bb-booster' ),
							'fade'          => __( 'Fade', 'bb-booster' )
						)
					),
					'speed'         => array(
						'type'          => 'text',
						'label'         => __('Transition Speed', 'bb-booster'),
						'default'       => '0.5',
						'maxlength'     => '4',
						'size'          => '5',
						'description'   => _x( 'seconds', 'Value unit for form field of time in seconds. Such as: "5 seconds"', 'bb-booster' )
					),
					'play_pause'    => array(
						'type'          => 'select',
						'label'         => __('Show Play/Pause', 'bb-booster'),
						'default'       => '0',
						'options'       => array(
							'0'             => __('No', 'bb-booster'),
							'1'             => __('Yes', 'bb-booster')
						)
					),
					'arrows'       => array(
						'type'          => 'select',
						'label'         => __('Show Arrows', 'bb-booster'),
						'default'       => '0',
						'options'       => array(
							'0'             => __('No', 'bb-booster'),
							'1'             => __('Yes', 'bb-booster')
						)
					),
					'dots'          => array(
						'type'          => 'select',
						'label'         => __('Show Dots', 'bb-booster'),
						'default'       => '1',
						'options'       => array(
							'0'             => __('No', 'bb-booster'),
							'1'             => __('Yes', 'bb-booster')
						)
					)
				)
			),
			'advanced'      => array(
				'title'         => __('Advanced', 'bb-booster'),
				'fields'        => array(
					'max_width'     => array(
						'type'          => 'text',
						'label'         => __('Max Content Width', 'bb-booster'),
						'default'       => '1100',
						'maxlength'     => '4',
						'size'          => '5',
						'description'   => 'px',
						'help'          => __('The max width that the content area will be within your slides.', 'bb-booster')
					)
				)
			)
		)
	),
	'slides'       => array(
		'title'         => __('Slides', 'bb-booster'),
		'sections'      => array(
			'general'       => array(
				'title'         => '',
				'fields'        => array(
					'slides'        => array(
						'type'          => 'form',
						'label'         => __('Slide', 'bb-booster'),
						'form'          => 'content_slider_slide', // ID from registered form below
						'preview_text'  => 'label', // Name of a field to use for the preview text
						'multiple'      => true
					)
				)
			)
		)
	)
));

/**
 * Register the slide settings form.
 */
FLBuilder::register_settings_form('content_slider_slide', array(
	'title' => __('Slide Settings', 'bb-booster'),
	'tabs'  => array(
		'general'        => array( // Tab
			'title'         => __('General', 'bb-booster'), // Tab title
			'sections'      => array( // Tab Sections
				'general'       => array(
					'title'     => '',
					'fields'    => array(
						'label'         => array(
							'type'          => 'text',
							'label'         => __('Slide Label', 'bb-booster'),
							'help'          => __('A label to identify this slide on the Slides tab of the Content Slider settings.', 'bb-booster')
						)
					)
				),
				'background' => array(
					'title'     => __('Background Layout', 'bb-booster'),
					'fields'    => array(
						'bg_layout'     => array(
							'type'          => 'select',
							'label'         => __('Type', 'bb-booster'),
							'default'       => 'photo',
							'help'          => __('This setting is for the entire background of your slide.', 'bb-booster'),
							'options'       => array(
								'photo'         => __('Photo', 'bb-booster'),
								'video'         => __('Video', 'bb-booster'),
								'color'         => __('Color', 'bb-booster'),
								'none'          => _x( 'None', 'Background type.', 'bb-booster' )
							),
							'toggle'        => array(
								'photo'         => array(
									'fields'        => array('bg_photo'),
									'sections'      => array('content', 'text')
								),
								'color'         => array(
									'fields'        => array('bg_color'),
									'sections'      => array('content', 'text')
								),
								'video'         => array(
									'fields'        => array('bg_video')
								),
								'none'          => array(
									'sections'      => array('content', 'text')
								)
							)
						),
						'bg_photo'      => array(
							'type'          => 'photo',
							'label'         => __('Background Photo', 'bb-booster')
						),
						'bg_color'      => array(
							'type'          => 'color',
							'label'         => __('Background Color', 'bb-booster'),
							'show_reset'    => true
						),
						'bg_video'      => array(
							'type'          => 'textarea',
							'label'         => __('Background Video Code', 'bb-booster'),
							'rows'          => '6'
						)
					)
				),
				'content'      => array(
					'title'         => __('Content Layout', 'bb-booster'),
					'fields'        => array(
						'content_layout' => array(
							'type'          => 'select',
							'label'         => __('Type', 'bb-booster'),
							'default'       => 'none',
							'help'          => __('This allows you to add content over or in addition to the background selection above. The location of the content layout can be selected in the style tab.', 'bb-booster'),
							'options'       => array(
								'text'          => __('Text', 'bb-booster'),
								'photo'         => __('Text &amp; Photo', 'bb-booster'),
								'video'         => __('Text &amp; Video', 'bb-booster'),
								'none'          => _x( 'None', 'Content type.', 'bb-booster' )
							),
							'toggle'        => array(
								'text'          => array(
									'fields'        => array('title', 'text'),
									'sections'      => array('text')
								),
								'photo'         => array(
									'fields'        => array('title', 'text', 'fg_photo'),
									'sections'      => array('text')
								),
								'video'         => array(
									'fields'        => array('title', 'text', 'fg_video'),
									'sections'      => array('text')
								)
							)
						),
						'fg_photo'      => array(
							'type'          => 'photo',
							'label'         => __('Photo', 'bb-booster')
						),
						'fg_video'      => array(
							'type'          => 'textarea',
							'label'         => __('Video Embed Code', 'bb-booster'),
							'rows'          => '6'
						),
						'title'         => array(
							'type'          => 'text',
							'label'         => __('Heading', 'bb-booster')
						),
						'text'          => array(
							'type'          => 'editor',
							'media_buttons' => false,
							'wpautop'		=> false,
							'rows'          => 16
						)
					)
				)
			)
		),
		'style'         => array( // Tab
			'title'         => __('Style', 'bb-booster'), // Tab title
			'sections'      => array( // Tab Sections
				'title'         => array(
					'title'         => __('Heading', 'bb-booster'),
					'fields'        => array(
						'title_tag'     => array(
							'type'          => 'select',
							'label'         => __('Heading Tag', 'bb-booster'),
							'default'       => 'h2',
							'options'       => array(
								'h1'            => 'h1',
								'h2'            => 'h2',
								'h3'            => 'h3',
								'h4'            => 'h4',
								'h5'            => 'h5',
								'h6'            => 'h6'
							)
						),
						'title_size'    => array(
							'type'          => 'select',
							'label'         => __('Heading Size', 'bb-booster'),
							'default'       => 'default',
							'options'       => array(
								'default'       =>  __('Default', 'bb-booster'),
								'custom'        =>  __('Custom', 'bb-booster')
							),
							'toggle'        => array(
								'custom'        => array(
									'fields'        => array('title_custom_size')
								)
							)
						),
						'title_custom_size' => array(
							'type'              => 'text',
							'label'             => __('Heading Size', 'bb-booster'),
							'default'           => '24',
							'maxlength'         => '3',
							'size'              => '4',
							'description'       => 'px'
						)
					)
				),
				'text_position' => array(
					'title'         => __('Text Position', 'bb-booster'),
					'fields'        => array(
						'text_position' => array(
							'type'          => 'select',
							'label'         => __('Position', 'bb-booster'),
							'default'       => 'top-left',
							'help'          => __('The position will move the content layout selections left, right or center over the background of the slide.', 'bb-booster'),
							'options'       => array(
								'left'          => __('Left', 'bb-booster'),
								'center'        => __('Center', 'bb-booster'),
								'right'         => __('Right', 'bb-booster')
							)
						),
						'text_width'   => array(
							'type'          => 'text',
							'label'         => __('Width', 'bb-booster'),
							'default'       => '50',
							'description'   => '%',
							'maxlength'     => '3',
							'size'          => '5'
						),
						'text_margin_top' => array(
							'type'          => 'text',
							'label'         => __('Top Margin', 'bb-booster'),
							'default'       => '60',
							'description'   => 'px',
							'maxlength'     => '4',
							'size'          => '5'
						),
						'text_margin_bottom' => array(
							'type'          => 'text',
							'label'         => __('Bottom Margin', 'bb-booster'),
							'default'       => '60',
							'description'   => 'px',
							'maxlength'     => '4',
							'size'          => '5'
						),
						'text_margin_left' => array(
							'type'          => 'text',
							'label'         => __('Left Margin', 'bb-booster'),
							'default'       => '60',
							'description'   => 'px',
							'maxlength'     => '4',
							'size'          => '5'
						),
						'text_margin_right' => array(
							'type'          => 'text',
							'label'         => __('Right Margin', 'bb-booster'),
							'default'       => '60',
							'description'   => 'px',
							'maxlength'     => '4',
							'size'          => '5'
						)
					)
				),
				'text_style'    => array(
					'title'         => __('Text Colors', 'bb-booster'),
					'fields'        => array(
						'text_color'    => array(
							'type'          => 'color',
							'label'         => __('Text Color', 'bb-booster'),
							'default'       => 'ffffff',
							'show_reset'    => true
						),
						'text_shadow'   => array(
							'type'          => 'select',
							'label'         => __('Text Shadow', 'bb-booster'),
							'default'       => '0',
							'options'       => array(
								'0'             => __('No', 'bb-booster'),
								'1'             => __('Yes', 'bb-booster')
							)
						),
						'text_bg_color'    => array(
							'type'          => 'color',
							'label'         => __('Text Background Color', 'bb-booster'),
							'help'          => __('The color applies to the overlay behind text over the background selections.', 'bb-booster'),
							'show_reset'    => true
						),
						'text_bg_opacity' => array(
							'type'          => 'text',
							'label'         => __('Text Background Opacity', 'bb-booster'),
							'default'       => '70',
							'maxlength'     => '3',
							'size'          => '4',
							'description'   => '%'
						),
						'text_bg_height' => array(
							'type'          => 'select',
							'label'         => __('Text Background Height', 'bb-booster'),
							'default'       => 'auto',
							'help'          => __('Auto will allow the overlay to fit however long the text content is. 100% will fit the overlay to the top and bottom of the slide.', 'bb-booster'),
							'options'       => array(
								'auto'          => _x( 'Auto', 'Background height.', 'bb-booster' ),
								'100%'          => '100%'
							)
						)
					)
				)
			)
		),
		'cta'           => array(
			'title'         => __('Call To Action', 'bb-booster'),
			'sections'      => array(
				'link'          => array(
					'title'         => __('Link', 'bb-booster'),
					'fields'        => array(
						'link'          => array(
							'type'          => 'link',
							'label'         => __('Link', 'bb-booster'),
							'help'          => __('The link applies to the entire slide. If choosing a call to action type below, this link will also be used for the text or button.', 'bb-booster')
						),
						'link_target'   => array(
							'type'          => 'select',
							'label'         => __('Link Target', 'bb-booster'),
							'default'       => '_self',
							'options'       => array(
								'_self'         => __('Same Window', 'bb-booster'),
								'_blank'        => __('New Window', 'bb-booster')
							)
						),
						'link_nofollow'          => array(
							'type'          => 'select',
							'label'         => __('Link No Follow', 'bb-booster'),
							'default'       => 'no',
							'options' 		=> array(
								'yes' 			=> __('Yes', 'bb-booster'),
								'no' 			=> __('No', 'bb-booster'),
							),
							'preview'       => array(
								'type'          => 'none'
							)
						)
					)
				),
				'cta'           => array(
					'title'         => __('Call to Action', 'bb-booster'),
					'fields'        => array(
						'cta_type'      => array(
							'type'          => 'select',
							'label'         => __('Type', 'bb-booster'),
							'default'       => 'none',
							'options'       => array(
								'none'          => _x( 'None', 'Call to action.', 'bb-booster' ),
								'link'          => __('Link', 'bb-booster'),
								'button'        => __('Button', 'bb-booster')
							),
							'toggle'        => array(
								'none'          => array(),
								'link'          => array(
									'fields'        => array('cta_text')
								),
								'button'        => array(
									'fields'        => array('cta_text', 'btn_icon', 'btn_icon_position', 'btn_icon_animation'),
									'sections'      => array('btn_style', 'btn_colors', 'btn_structure')
								)
							)
						),
						'cta_text'      => array(
							'type'          => 'text',
							'label'         => __('Text', 'bb-booster')
						),
						'btn_icon'      => array(
							'type'          => 'icon',
							'label'         => __('Button Icon', 'bb-booster'),
							'show_remove'   => true
						),
						'btn_icon_position' => array(
							'type'          => 'select',
							'label'         => __('Button Icon Position', 'bb-booster'),
							'default'       => 'before',
							'options'       => array(
								'before'        => __('Before Text', 'bb-booster'),
								'after'         => __('After Text', 'bb-booster')
							)
						),
						'btn_icon_animation' => array(
						'type'          => 'select',
						'label'         => __('Icon Visibility', 'bb-booster'),
						'default'       => 'disable',
						'options'       => array(
							'disable'        => __('Always Visible', 'bb-booster'),
							'enable'         => __('Fade In On Hover', 'bb-booster')
						)
					)
					)
				),
				'btn_colors'     => array(
					'title'         => __('Button Colors', 'bb-booster'),
					'fields'        => array(
						'btn_bg_color'  => array(
							'type'          => 'color',
							'label'         => __('Background Color', 'bb-booster'),
							'default'       => 'f7f7f7',
							'show_reset'    => true
						),
						'btn_bg_hover_color' => array(
							'type'          => 'color',
							'label'         => __('Background Hover Color', 'bb-booster'),
							'show_reset'    => true
						),
						'btn_text_color' => array(
							'type'          => 'color',
							'label'         => __('Text Color', 'bb-booster'),
							'default'       => '333333',
							'show_reset'    => true
						),
						'btn_text_hover_color' => array(
							'type'          => 'color',
							'label'         => __('Text Hover Color', 'bb-booster'),
							'show_reset'    => true
						)
					)
				),
				'btn_style'     => array(
					'title'         => __('Button Style', 'bb-booster'),
					'fields'        => array(
						'btn_style'     => array(
							'type'          => 'select',
							'label'         => __('Style', 'bb-booster'),
							'default'       => 'flat',
							'options'       => array(
								'flat'          => __('Flat', 'bb-booster'),
								'gradient'      => __('Gradient', 'bb-booster'),
								'transparent'   => __('Transparent', 'bb-booster')
							),
							'toggle'        => array(
								'transparent'   => array(
									'fields'        => array('btn_bg_opacity', 'btn_bg_hover_opacity', 'btn_border_size')
								)
							)
						),
						'btn_border_size' => array(
							'type'          => 'text',
							'label'         => __('Border Size', 'bb-booster'),
							'default'       => '2',
							'description'   => 'px',
							'maxlength'     => '3',
							'size'          => '5',
							'placeholder'   => '0'
						),
						'btn_bg_opacity' => array(
							'type'          => 'text',
							'label'         => __('Background Opacity', 'bb-booster'),
							'default'       => '0',
							'description'   => '%',
							'maxlength'     => '3',
							'size'          => '5',
							'placeholder'   => '0'
						),
						'btn_bg_hover_opacity' => array(
							'type'          => 'text',
							'label'         => __('Background Hover Opacity', 'bb-booster'),
							'default'       => '0',
							'description'   => '%',
							'maxlength'     => '3',
							'size'          => '5',
							'placeholder'   => '0'
						),
						'btn_button_transition' => array(
							'type'          => 'select',
							'label'         => __('Transition', 'bb-booster'),
							'default'       => 'disable',
							'options'       => array(
								'disable'        => __('Disabled', 'bb-booster'),
								'enable'         => __('Enabled', 'bb-booster')
							)
						)
					)  
				),
				'btn_structure' => array(
					'title'         => __('Button Structure', 'bb-booster'),
					'fields'        => array(
						'btn_font_size' => array(
							'type'          => 'text',
							'label'         => __('Font Size', 'bb-booster'),
							'default'       => '16',
							'maxlength'     => '3',
							'size'          => '4',
							'description'   => 'px'
						),
						'btn_padding'   => array(
							'type'          => 'text',
							'label'         => __('Padding', 'bb-booster'),
							'default'       => '14',
							'maxlength'     => '3',
							'size'          => '4',
							'description'   => 'px'
						),
						'btn_border_radius' => array(
							'type'          => 'text',
							'label'         => __('Border Radius', 'bb-booster'),
							'default'       => '6',
							'maxlength'     => '3',
							'size'          => '4',
							'description'   => 'px'
						)
					)
				)
			)
		),
		'mobile'        => array(
			'title'         => _x( 'Mobile', 'Module settings form tab. Display on mobile devices.', 'bb-booster' ),
			'sections'      => array(
				'r_photo'       => array(
					'title'         => __('Mobile Photo', 'bb-booster'),
					'fields'        => array(
						'r_photo_type'  => array(
							'type'          => 'select',
							'label'         => __('Type', 'bb-booster'),
							'default'       => 'main',
							'help'          => __('You can choose a different photo that the slide will change to on mobile devices or no photo if desired.', 'bb-booster'),
							'options'       => array(
								'main'          => __('Use Main Photo', 'bb-booster'),
								'another'       => __('Choose Another Photo', 'bb-booster'),
								'none'          => __('No Photo', 'bb-booster')
							),
							'toggle'        => array(
								'another'       => array(
									'fields'        => array('r_photo')
								)
							)
						),
						'r_photo'    => array(
							'type'          => 'photo',
							'label'         => __('Photo', 'bb-booster')
						)
					)
				),
				'r_text_style'   => array(
					'title'         => __('Mobile Text Colors', 'bb-booster'),
					'fields'        => array(
						'r_text_color'  => array(
							'type'          => 'color',
							'label'         => __('Text Color', 'bb-booster'),
							'default'       => 'ffffff',
							'show_reset'    => true
						),
						'r_text_bg_color' => array(
							'type'          => 'color',
							'label'         => __('Text Background Color', 'bb-booster'),
							'default'       => '333333',
							'show_reset'    => true
						)
					)
				)
			)
		)
	)
));