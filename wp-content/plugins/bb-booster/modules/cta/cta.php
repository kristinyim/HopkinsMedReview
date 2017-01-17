<?php

/**
 * @class FLCtaModule
 */
class FLCtaModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(array(
			'name'          	=> __('Call to Action', 'bb-booster'),
			'description'   	=> __('Display a heading, subheading and a button.', 'bb-booster'),
			'category'      	=> __('Advanced Modules', 'bb-booster'),
			'partial_refresh'	=> true
		));
	}

	/**
	 * @method get_classname
	 */
	public function get_classname()
	{
		$classname = 'fl-cta-wrap fl-cta-' . $this->settings->layout;

		if($this->settings->layout == 'stacked') {
			$classname .= ' fl-cta-' . $this->settings->alignment;
		}

		return $classname;
	}

	/**
	 * @method render_button
	 */
	public function render_button()
	{
		$btn_settings = array(
			'align'             => '',
			'bg_color'          => $this->settings->btn_bg_color,
			'bg_hover_color'    => $this->settings->btn_bg_hover_color,
			'bg_opacity'        => $this->settings->btn_bg_opacity,
			'border_radius'     => $this->settings->btn_border_radius,
			'border_size'       => $this->settings->btn_border_size,
			'font_size'         => $this->settings->btn_font_size,
			'icon'              => $this->settings->btn_icon,
			'icon_position'		=> $this->settings->btn_icon_position,
			'icon_animation'	=> $this->settings->btn_icon_animation,
			'link'              => $this->settings->btn_link,
			'link_nofollow'     => $this->settings->btn_link_nofollow,
			'link_target'       => $this->settings->btn_link_target,
			'padding'           => $this->settings->btn_padding,
			'style'             => $this->settings->btn_style,
			'text'              => $this->settings->btn_text,
			'text_color'        => $this->settings->btn_text_color,
			'text_hover_color'  => $this->settings->btn_text_hover_color,
			'width'             => $this->settings->layout == 'stacked' ? 'auto' : 'full'
		);

		FLBuilder::render_module_html('button', $btn_settings);
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLCtaModule', array(
	'general'       => array(
		'title'         => __('General', 'bb-booster'),
		'sections'      => array(
			'title'         => array(
				'title'         => '',
				'fields'        => array(
					'title'         => array(
						'type'          => 'text',
						'label'         => __('Heading', 'bb-booster'),
						'default'       => __('Ready to find out more?', 'bb-booster'),
						'preview'       => array(
							'type'          => 'text',
							'selector'      => '.fl-cta-title'
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
						'default'       => __('Drop us a line today for a free quote!', 'bb-booster'),
						'preview'       => array(
							'type'          => 'text',
							'selector'      => '.fl-cta-text-content'
						)
					)
				)
			)
		)
	),
	'style'        => array(
		'title'         => __('Style', 'bb-booster'),
		'sections'      => array(
			'structure'     => array(
				'title'         => __('Structure', 'bb-booster'),
				'fields'        => array(
					'layout'        => array(
						'type'          => 'select',
						'label'         => __('Layout', 'bb-booster'),
						'default'       => 'inline',
						'options'       => array(
							'inline'        => __('Inline', 'bb-booster'),
							'stacked'       => __('Stacked', 'bb-booster')
						),
						'toggle'        => array(
							'stacked'       => array(
								'fields'        => array('alignment')
							)
						)
					),
					'alignment'     => array(
						'type'          => 'select',
						'label'         => __('Alignment', 'bb-booster'),
						'default'       => 'center',
						'options'       => array(
							'left'      =>  __('Left', 'bb-booster'),
							'center'    =>  __('Center', 'bb-booster'),
							'right'     =>  __('Right', 'bb-booster')
						)
					),
					'spacing'       => array(
						'type'          => 'text',
						'label'         => __('Spacing', 'bb-booster'),
						'default'       => '0',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px',
						'preview'       => array(
							'type'          => 'css',
							'selector'      => '.fl-module-content',
							'property'      => 'padding',
							'unit'          => 'px'
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
			),
			'colors'        => array(
				'title'         => __('Colors', 'bb-booster'),
				'fields'        => array(
					'text_color'    => array(
						'type'          => 'color',
						'label'         => __('Text Color', 'bb-booster'),
						'default'       => '',
						'show_reset'    => true
					),
					'bg_color'      => array(
						'type'          => 'color',
						'label'         => __('Background Color', 'bb-booster'),
						'default'       => '',
						'show_reset'    => true
					),
					'bg_opacity'    => array(
						'type'          => 'text',
						'label'         => __('Background Opacity', 'bb-booster'),
						'default'       => '100',
						'description'   => '%',
						'maxlength'     => '3',
						'size'          => '5'
					)
				)
			)
		)
	),
	'button'        => array(
		'title'         => __('Button', 'bb-booster'),
		'sections'      => array(
			'btn_text'      => array(
				'title'         => '',
				'fields'        => array(
					'btn_text'      => array(
						'type'          => 'text',
						'label'         => __('Text', 'bb-booster'),
						'default'       => __('Click Here', 'bb-booster'),
						'preview'         => array(
							'type'            => 'text',
							'selector'        => '.fl-button-text'
						)
					),
					'btn_icon'      => array(
						'type'          => 'icon',
						'label'         => __('Icon', 'bb-booster'),
						'show_remove'   => true
					),
					'btn_icon_position' => array(
						'type'          => 'select',
						'label'         => __('Icon Position', 'bb-booster'),
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
			'btn_link'      => array(
				'title'         => __('Button Link', 'bb-booster'),
				'fields'        => array(
					'btn_link'      => array(
						'type'          => 'link',
						'label'         => __('Link', 'bb-booster'),
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'btn_link_target' => array(
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
					'btn_link_nofollow' => array(
						'type'          	=> 'select',
						'label' 	        => __('Link No Follow', 'bb-booster'),
						'default'       => 'no',
						'options' 			=> array(
							'yes' 				=> __('Yes', 'bb-booster'),
							'no' 				=> __('No', 'bb-booster'),
						),
						'preview'       	=> array(
							'type'          	=> 'none'
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
						'default'       => '12',
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
						'description'   => 'px',
						'preview'         => array(
							'type'            => 'css',
							'selector'        => 'a.fl-button',
							'property'        => 'border-radius',
							'unit'            => 'px'
						)
					)
				)
			)
		)
	)
));