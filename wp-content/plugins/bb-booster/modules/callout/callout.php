<?php

/**
 * @class FLCalloutModule
 */
class FLCalloutModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(array(
			'name'          	=> __('Callout', 'bb-booster'),
			'description'   	=> __('A heading and snippet of text with an optional link, icon and image.', 'bb-booster'),
			'category'      	=> __('Advanced Modules', 'bb-booster'),
			'partial_refresh'	=> true
		));
	}

	/**
	 * @method update
	 * @param $settings {object}
	 */
	public function update($settings)
	{
		// Cache the photo data.
		if(!empty($settings->photo)) {

			$data = FLBuilderPhoto::get_attachment_data($settings->photo);

			if($data) {
				$settings->photo_data = $data;
			}
		}

		return $settings;
	}

	/**
	 * @method delete
	 */
	public function delete()
	{
		// Delete photo module cache.
		if($this->settings->image_type == 'photo' && !empty($this->settings->photo_src)) {
			$module_class = get_class(FLBuilderModel::$modules['photo']);
			$photo_module = new $module_class();
			$photo_module->settings = new stdClass();
			$photo_module->settings->photo_source = 'library';
			$photo_module->settings->photo_src = $this->settings->photo_src;
			$photo_module->settings->crop = $this->settings->photo_crop;
			$photo_module->delete();
		}
	}

	/**
	 * @method get_classname
	 */
	public function get_classname()
	{
		$classname = 'fl-callout fl-callout-' . $this->settings->align;

		if($this->settings->image_type == 'photo') {
			$classname .= ' fl-callout-has-photo fl-callout-photo-' . $this->settings->photo_position;
		}
		else if($this->settings->image_type == 'icon') {
			$classname .= ' fl-callout-has-icon fl-callout-icon-' . $this->settings->icon_position;
		}

		return $classname;
	}

	/**
	 * @method render_title
	 */
	public function render_title()
	{
		echo '<' . $this->settings->title_tag . ' class="fl-callout-title">';

		$this->render_image('left-title');

		echo '<span>';

		if(!empty($this->settings->link)) {
			echo '<a href="' . $this->settings->link . '" target="' . $this->settings->link_target . '" class="fl-callout-title-link">';
		}

		echo $this->settings->title;

		if(!empty($this->settings->link)) {
			echo '</a>';
		}

		echo '</span>';

		$this->render_image('right-title');

		echo '</' . $this->settings->title_tag . '>';
	}

	/**
	 * @method render_text
	 */
	public function render_text()
	{
		global $wp_embed;
		
		echo '<div class="fl-callout-text">' . wpautop( $wp_embed->autoembed( $this->settings->text ) ) . '</div>';
	}

	/**
	 * @method render_link
	 */
	public function render_link()
	{
		if($this->settings->cta_type == 'link') {
			echo '<a href="' . $this->settings->link . '" target="' . $this->settings->link_target . '" class="fl-callout-cta-link">' . $this->settings->cta_text . '</a>';
		}
	}

	/**
	 * @method render_button
	 */
	public function render_button()
	{
		if($this->settings->cta_type == 'button') {

			$btn_settings = array(
				'align'             => '',
				'bg_color'          => $this->settings->btn_bg_color,
				'bg_hover_color'    => $this->settings->btn_bg_hover_color,
				'bg_opacity'        => $this->settings->btn_bg_opacity,
				'border_radius'     => $this->settings->btn_border_radius,
				'border_size'       => $this->settings->btn_border_size,
				'font_size'         => $this->settings->btn_font_size,
				'icon'              => $this->settings->btn_icon,
				'icon_position'     => $this->settings->btn_icon_position,
				'icon_animation'	=> $this->settings->btn_icon_animation,
				'link'              => $this->settings->link,
				'link_nofollow'		=> $this->settings->link_nofollow,
				'link_target'       => $this->settings->link_target,
				'padding'           => $this->settings->btn_padding,
				'style'             => $this->settings->btn_style,
				'text'              => $this->settings->cta_text,
				'text_color'        => $this->settings->btn_text_color,
				'text_hover_color'  => $this->settings->btn_text_hover_color,
				'width'             => $this->settings->btn_width
			);

			echo '<div class="fl-callout-button">';
			FLBuilder::render_module_html('button', $btn_settings);
			echo '</div>';
		}
	}

	/**
	 * @method render_image
	 */
	public function render_image($position)
	{
		if($this->settings->image_type == 'photo' && $this->settings->photo_position == $position) {

			if(empty($this->settings->photo)) {
				return;
			}

			$photo_data = FLBuilderPhoto::get_attachment_data($this->settings->photo);

			if(!$photo_data) {
				$photo_data = $this->settings->photo_data;
			}

			$photo_settings = array(
				'align'         => 'center',
				'crop'          => $this->settings->photo_crop,
				'link_target'   => $this->settings->link_target,
				'link_type'     => 'url',
				'link_url'      => $this->settings->link,
				'photo'         => $photo_data,
				'photo_src'     => $this->settings->photo_src,
				'photo_source'  => 'library'
			);

			echo '<div class="fl-callout-photo">';
			FLBuilder::render_module_html('photo', $photo_settings);
			echo '</div>';
		}
		else if($this->settings->image_type == 'icon' && $this->settings->icon_position == $position) {

			$icon_settings = array(
				'bg_color'       => $this->settings->icon_bg_color,
				'bg_hover_color' => $this->settings->icon_bg_hover_color,
				'color'          => $this->settings->icon_color,
				'exclude_wrapper'=> true,
				'hover_color'    => $this->settings->icon_hover_color,
				'icon'           => $this->settings->icon,
				'link'           => $this->settings->link,
				'link_target'    => $this->settings->link_target,
				'size'           => $this->settings->icon_size,
				'text'           => '',
				'three_d'        => $this->settings->icon_3d
			);

			FLBuilder::render_module_html('icon', $icon_settings);
		}
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLCalloutModule', array(
	'general'       => array(
		'title'         => __('General', 'bb-booster'),
		'sections'      => array(
			'title'         => array(
				'title'         => '',
				'fields'        => array(
					'title'         => array(
						'type'          => 'text',
						'label'         => __('Heading', 'bb-booster'),
						'preview'       => array(
							'type'          => 'text',
							'selector'      => '.fl-callout-title'
						)
					)
				)
			),
			'text'          => array(
				'title'         => __('Text', 'bb-booster'),
				'fields'        => array(
					'text'          => array(
						'type'          => 'editor',
						'label'         => '',
						'media_buttons' => false,
						'wpautop'		=> false,
						'preview'       => array(
							'type'          => 'text',
							'selector'      => '.fl-callout-text'
						)
					)
				)
			)
		)
	),
	'style'         => array(
		'title'         => __('Style', 'bb-booster'),
		'sections'      => array(
			'overall_structure' => array(
				'title'         => __('Structure', 'bb-booster'),
				'fields'        => array(
					'align'         => array(
						'type'          => 'select',
						'label'         => __('Overall Alignment', 'bb-booster'),
						'default'       => 'left',
						'options'       => array(
							'center'        => __('Center', 'bb-booster'),
							'left'          => __('Left', 'bb-booster'),
							'right'         => __('Right', 'bb-booster')
						),
						'help'          => __('The alignment that will apply to all elements within the callout.', 'bb-booster'),
						'preview'       => array(
							'type'          => 'none'
						)
					)
				)
			),
			'title_structure' => array(
				'title'         => __( 'Heading Structure', 'bb-booster' ),
				'fields'        => array(
					'title_tag'     => array(
						'type'          => 'select',
						'label'         => __('Heading Tag', 'bb-booster'),
						'default'       => 'h3',
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
						'label'             => __('Heading Custom Size', 'bb-booster'),
						'default'           => '24',
						'maxlength'         => '3',
						'size'              => '4',
						'description'       => 'px'
					)
				)
			)
		)
	),
	'image'         => array(
		'title'         => __('Image', 'bb-booster'),
		'sections'      => array(
			'general'       => array(
				'title'         => '',
				'fields'        => array(
					'image_type'    => array(
						'type'          => 'select',
						'label'         => __('Image Type', 'bb-booster'),
						'default'       => 'photo',
						'options'       => array(
							'none'          => _x( 'None', 'Image type.', 'bb-booster' ),
							'photo'         => __('Photo', 'bb-booster'),
							'icon'          => __('Icon', 'bb-booster')
						),
						'toggle'        => array(
							'none'          => array(),
							'photo'         => array(
								'sections'      => array('photo')
							),
							'icon'          => array(
								'sections'      => array('icon', 'icon_colors', 'icon_structure')
							)
						)
					)
				)
			),
			'photo'         => array(
				'title'         => __('Photo', 'bb-booster'),
				'fields'        => array(
					'photo'         => array(
						'type'          => 'photo',
						'label'         => __('Photo', 'bb-booster')
					),
					'photo_crop'    => array(
						'type'          => 'select',
						'label'         => __('Crop', 'bb-booster'),
						'default'       => '',
						'options'       => array(
							''              => _x( 'None', 'Photo Crop.', 'bb-booster' ),
							'landscape'     => __('Landscape', 'bb-booster'),
							'panorama'      => __('Panorama', 'bb-booster'),
							'portrait'      => __('Portrait', 'bb-booster'),
							'square'        => __('Square', 'bb-booster'),
							'circle'        => __('Circle', 'bb-booster')
						)
					),
					'photo_position' => array(
						'type'          => 'select',
						'label'         => __('Position', 'bb-booster'),
						'default'       => 'above-title',
						'options'       => array(
							'above-title'   => __('Above Heading', 'bb-booster'),
							'below-title'   => __('Below Heading', 'bb-booster'),
							'left'          => __('Left of Text and Heading', 'bb-booster'),
							'right'         => __('Right of Text and Heading', 'bb-booster')
						)
					)
				)
			),
			'icon'          => array(
				'title'         => __('Icon', 'bb-booster'),
				'fields'        => array(
					'icon'          => array(
						'type'          => 'icon',
						'label'         => __('Icon', 'bb-booster')
					),
					'icon_position' => array(
						'type'          => 'select',
						'label'         => __('Position', 'bb-booster'),
						'default'       => 'left-title',
						'options'       => array(
							'above-title'   => __('Above Heading', 'bb-booster'),
							'below-title'   => __('Below Heading', 'bb-booster'),
							'left-title'    => __( 'Left of Heading', 'bb-booster' ),
							'right-title'   => __( 'Right of Heading', 'bb-booster' ),
							'left'          => __('Left of Text and Heading', 'bb-booster'),
							'right'         => __('Right of Text and Heading', 'bb-booster')
						)
					)
				)
			),
			'icon_colors'   => array(
				'title'         => __('Icon Colors', 'bb-booster'),
				'fields'        => array(
					'icon_color'    => array(
						'type'          => 'color',
						'label'         => __('Color', 'bb-booster'),
						'show_reset'    => true
					),
					'icon_hover_color' => array(
						'type'          => 'color',
						'label'         => __('Hover Color', 'bb-booster'),
						'show_reset'    => true,
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'icon_bg_color' => array(
						'type'          => 'color',
						'label'         => __('Background Color', 'bb-booster'),
						'show_reset'    => true
					),
					'icon_bg_hover_color' => array(
						'type'          => 'color',
						'label'         => __('Background Hover Color', 'bb-booster'),
						'show_reset'    => true,
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'icon_3d'       => array(
						'type'          => 'select',
						'label'         => __('Gradient', 'bb-booster'),
						'default'       => '0',
						'options'       => array(
							'0'             => __('No', 'bb-booster'),
							'1'             => __('Yes', 'bb-booster')
						)
					)
				)
			),
			'icon_structure' => array(
				'title'         => __('Icon Structure', 'bb-booster'),
				'fields'        => array(
					'icon_size'     => array(
						'type'          => 'text',
						'label'         => __('Size', 'bb-booster'),
						'default'       => '30',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
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
						'help'          => __('The link applies to the entire module. If choosing a call to action type below, this link will also be used for the text or button.', 'bb-booster'),
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'link_target'   => array(
						'type'          => 'select',
						'label'         => __('Link Target', 'bb-booster'),
						'default'       => '_self',
						'options'       => array(
							'_self'         => __('Same Window', 'bb-booster'),
							'_blank'        => __('New Window', 'bb-booster')
						),
						'preview'       => array(
							'type'          => 'none'
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
							'link'          => __('Text', 'bb-booster'),
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
						'label'         => __('Text', 'bb-booster'),
						'default'		=> __('Read More', 'bb-booster'),
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
						'default'       => '',
						'show_reset'    => true
					),
					'btn_bg_hover_color' => array(
						'type'          => 'color',
						'label'         => __('Background Hover Color', 'bb-booster'),
						'default'       => '',
						'show_reset'    => true,
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'btn_text_color' => array(
						'type'          => 'color',
						'label'         => __('Text Color', 'bb-booster'),
						'default'       => '',
						'show_reset'    => true
					),
					'btn_text_hover_color' => array(
						'type'          => 'color',
						'label'         => __('Text Hover Color', 'bb-booster'),
						'default'       => '',
						'show_reset'    => true,
						'preview'       => array(
							'type'          => 'none'
						)
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
					'btn_width'     => array(
						'type'          => 'select',
						'label'         => __('Button Width', 'bb-booster'),
						'default'       => 'auto',
						'options'       => array(
							'auto'          => _x( 'Auto', 'Width.', 'bb-booster' ),
							'full'          => __('Full Width', 'bb-booster')
						)
					),
					'btn_font_size' => array(
						'type'          => 'text',
						'label'         => __('Font Size', 'bb-booster'),
						'default'       => '14',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
					),
					'btn_padding'   => array(
						'type'          => 'text',
						'label'         => __('Padding', 'bb-booster'),
						'default'       => '10',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
					),
					'btn_border_radius' => array(
						'type'          => 'text',
						'label'         => __('Round Corners', 'bb-booster'),
						'default'       => '4',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
					)
				)
			)
		)
	)
));