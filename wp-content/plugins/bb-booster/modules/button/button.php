<?php

/**
 * @class FLButtonModule
 */
class FLButtonModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(array(
			'name'          	=> __('Button', 'bb-booster'),
			'description'   	=> __('A simple call to action button.', 'bb-booster'),
			'category'      	=> __('Basic Modules', 'bb-booster'),
			'partial_refresh'	=> true
		));
	}

	/**
	 * @method enqueue_scripts
	 */
	public function enqueue_scripts()
	{
		if($this->settings && $this->settings->click_action == 'lightbox') {
			$this->add_js('jquery-magnificpopup');
			$this->add_css('jquery-magnificpopup');
		}
	}

	/**
	 * @method update
	 */
	public function update( $settings )
	{
		// Remove the old three_d setting.
		if ( isset( $settings->three_d ) ) {
			unset( $settings->three_d );
		}
		
		return $settings;
	}

	/**
	 * @method get_classname
	 */
	public function get_classname()
	{
		$classname = 'fl-button-wrap';

		if(!empty($this->settings->width)) {
			$classname .= ' fl-button-width-' . $this->settings->width;
		}
		if(!empty($this->settings->align)) {
			$classname .= ' fl-button-' . $this->settings->align;
		}
		if(!empty($this->settings->icon)) {
			$classname .= ' fl-button-has-icon';
		}

		return $classname;
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLButtonModule', array(
	'general'       => array(
		'title'         => __('General', 'bb-booster'),
		'sections'      => array(
			'general'       => array(
				'title'         => '',
				'fields'        => array(
					'text'          => array(
						'type'          => 'text',
						'label'         => __('Text', 'bb-booster'),
						'default'       => __('Click Here', 'bb-booster'),
						'preview'         => array(
							'type'            => 'text',
							'selector'        => '.fl-button-text'
						)
					),
					'icon'          => array(
						'type'          => 'icon',
						'label'         => __('Icon', 'bb-booster'),
						'show_remove'   => true
					),
					'icon_position' => array(
						'type'          => 'select',
						'label'         => __('Icon Position', 'bb-booster'),
						'default'       => 'before',
						'options'       => array(
							'before'        => __('Before Text', 'bb-booster'),
							'after'         => __('After Text', 'bb-booster')
						)
					),
					'icon_animation' => array(
						'type'          => 'select',
						'label'         => __('Icon Visibility', 'bb-booster'),
						'default'       => 'disable',
						'options'       => array(
							'disable'        => __('Always Visible', 'bb-booster'),
							'enable'         => __('Fade In On Hover', 'bb-booster')
						)
					),	
					'click_action' => array(
						'type' 			=> 'select',
						'label'         => __('Click Action', 'bb-booster'),
						'default' 		=> 'link',
						'options' 		=> array(
							'link' 			=> __('Link', 'bb-booster'),
							'lightbox' 		=> __('Lightbox', 'bb-booster')
						),
						'toggle'  => array(
							'link'		=> array(
								'sections' => array('link') 
							),
							'lightbox'	=> array(
								'sections' => array('lightbox')
							)
						)	
					)
				)
			),
			'link'          => array(
				'title'         => __('Link', 'bb-booster'),
				'fields'        => array(
					'link'          => array(
						'type'          => 'link',
						'label'         => __('Link', 'bb-booster'),
						'placeholder'   => __( 'http://www.example.com', 'bb-booster' ),
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
			'lightbox'	=> array(
				'title'		=> __('Lightbox Content', 'bb-booster'),
				'fields'        => array(
					'lightbox_content_type' => array(
						'type' 				=> 'select',
						'label' 			=> __('Content Type', 'bb-booster'),
						'default' 			=> 'html',
						'options' 			=> array(
							'html' 				=> __('HTML', 'bb-booster'),
							'video' 			=> __('Video', 'bb-booster')
						),
						'preview'       	=> array(
							'type'          	=> 'none'
						),
						'toggle' 		=> array(
							'html'			=> array(
								'fields' 		=> array('lightbox_content_html') 
							),
							'video'			=> array( 
								'fields' 		=> array('lightbox_video_link') 
							)
						)
					),
					'lightbox_content_html'	=> array(
						'type'          		=> 'code',
						'editor'        		=> 'html',
						'label'         		=> '',
						'rows'          		=> '19',
						'preview'       		=> array(
							'type'          		=> 'none'
						)
					),
					'lightbox_video_link' => array(
						'type'          => 'text',
						'label'         => __('Video Link', 'bb-booster'),
						'placeholder'   => 'https://vimeo.com/122546221',
						'preview'       => array(
							'type'          => 'none'
						)
					)
				)
			)
		)
	),
	'style'         => array(
		'title'         => __('Style', 'bb-booster'),
		'sections'      => array(
			'colors'        => array(
				'title'         => __('Colors', 'bb-booster'),
				'fields'        => array(
					'bg_color'      => array(
						'type'          => 'color',
						'label'         => __('Background Color', 'bb-booster'),
						'default'       => '',
						'show_reset'    => true
					),
					'bg_hover_color' => array(
						'type'          => 'color',
						'label'         => __('Background Hover Color', 'bb-booster'),
						'default'       => '',
						'show_reset'    => true,
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'text_color'    => array(
						'type'          => 'color',
						'label'         => __('Text Color', 'bb-booster'),
						'default'       => '',
						'show_reset'    => true
					),
					'text_hover_color' => array(
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
			'style'         => array(
				'title'         => __('Style', 'bb-booster'),
				'fields'        => array(
					'style'         => array(
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
								'fields'        => array('bg_opacity', 'bg_hover_opacity', 'border_size')
							)
						)
					),
					'border_size'   => array(
						'type'          => 'text',
						'label'         => __('Border Size', 'bb-booster'),
						'default'       => '2',
						'description'   => 'px',
						'maxlength'     => '3',
						'size'          => '5',
						'placeholder'   => '0'
					),
					'bg_opacity'    => array(
						'type'          => 'text',
						'label'         => __('Background Opacity', 'bb-booster'),
						'default'       => '0',
						'description'   => '%',
						'maxlength'     => '3',
						'size'          => '5',
						'placeholder'   => '0'
					),
					'bg_hover_opacity'    => array(
						'type'          => 'text',
						'label'         => __('Background Hover Opacity', 'bb-booster'),
						'default'       => '0',
						'description'   => '%',
						'maxlength'     => '3',
						'size'          => '5',
						'placeholder'   => '0'
					),
					'button_transition'         => array(
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
			'formatting'    => array(
				'title'         => __('Structure', 'bb-booster'),
				'fields'        => array(
					'width'         => array(
						'type'          => 'select',
						'label'         => __('Width', 'bb-booster'),
						'default'       => 'auto',
						'options'       => array(
							'auto'          => _x( 'Auto', 'Width.', 'bb-booster' ),
							'full'          => __('Full Width', 'bb-booster'),
							'custom'        => __('Custom', 'bb-booster')
						),
						'toggle'        => array(
							'auto'          => array(
								'fields'        => array('align')
							),
							'full'          => array(),
							'custom'        => array(
								'fields'        => array('align', 'custom_width')
							)
						)
					),
					'custom_width'  => array(
						'type'          => 'text',
						'label'         => __('Custom Width', 'bb-booster'),
						'default'       => '200',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
					),
					'align'         => array(
						'type'          => 'select',
						'label'         => __('Alignment', 'bb-booster'),
						'default'       => 'left',
						'options'       => array(
							'center'        => __('Center', 'bb-booster'),
							'left'          => __('Left', 'bb-booster'),
							'right'         => __('Right', 'bb-booster')
						)
					),
					'font_size'     => array(
						'type'          => 'text',
						'label'         => __('Font Size', 'bb-booster'),
						'default'       => '16',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
					),
					'padding'       => array(
						'type'          => 'text',
						'label'         => __('Padding', 'bb-booster'),
						'default'       => '12',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
					),
					'border_radius' => array(
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