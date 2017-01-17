<?php

/**
 * @class FLIconModule
 */
class FLIconModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(array(
			'name'          	=> __('Icon', 'bb-booster'),
			'description'   	=> __('Display an icon and optional title.', 'bb-booster'),
			'category'      	=> __('Advanced Modules', 'bb-booster'),
			'editor_export' 	=> false,
			'partial_refresh'	=> true
		));
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLIconModule', array(
	'general'       => array( // Tab
		'title'         => __('General', 'bb-booster'), // Tab title
		'sections'      => array( // Tab Sections
			'general'       => array( // Section
				'title'         => '', // Section Title
				'fields'        => array( // Section Fields
					'icon'          => array(
						'type'          => 'icon',
						'label'         => __('Icon', 'bb-booster')
					)
				)
			),
			'link'          => array(
				'title'         => __('Link', 'bb-booster'),
				'fields'        => array(
					'link'          => array(
						'type'          => 'link',
						'label'         => __('Link', 'bb-booster'),
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
					)
				)
			),
			'text'          => array(
				'title'         => 'Text',
				'fields'        => array(
					'text'          => array(
						'type'          => 'editor',
						'label'         => '',
						'media_buttons' => false
					)
				)
			)
		)
	),
	'style'         => array( // Tab
		'title'         => __('Style', 'bb-booster'), // Tab title
		'sections'      => array( // Tab Sections
			'colors'        => array( // Section
				'title'         => __('Colors', 'bb-booster'), // Section Title
				'fields'        => array( // Section Fields
					'color'         => array(
						'type'          => 'color',
						'label'         => __('Color', 'bb-booster'),
						'show_reset'    => true
					),
					'hover_color' => array(
						'type'          => 'color',
						'label'         => __('Hover Color', 'bb-booster'),
						'show_reset'    => true,
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'bg_color'      => array(
						'type'          => 'color',
						'label'         => __('Background Color', 'bb-booster'),
						'show_reset'    => true
					),
					'bg_hover_color' => array(
						'type'          => 'color',
						'label'         => __('Background Hover Color', 'bb-booster'),
						'show_reset'    => true,
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'three_d'       => array(
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
			'structure'     => array( // Section
				'title'         => __('Structure', 'bb-booster'), // Section Title
				'fields'        => array( // Section Fields
					'size'          => array(
						'type'          => 'text',
						'label'         => __('Size', 'bb-booster'),
						'default'       => '30',
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
					)
				)
			),
			'r_structure'	=>	array( 
				'title'			=>	__('Mobile Structure', 'bb-booster'),
				'fields'		=>	array(
					'r_align'		=> array(
						'type'			=> 'select',
						'label'			=> __('Alignment', 'bb-booster'),
						'default'		=> 'default',
						'options'		=> array(
							'default'		=> __('Default', 'bb-booster'),
							'custom'		=> __('Custom', 'bb-booster'),
						),
						'toggle'		=> array(
							'custom'		=> array(
								'fields'		=> array('r_custom_align')
							)
						)
					),
					'r_custom_align'	=> array(
						'type'				=> 'select',
						'label'				=> __('Custom Alignment', 'bb-booster'),
						'default'			=> 'left',
						'options'			=> array(
							'left'				=> __('Left', 'bb-booster'),
							'center'			=> __('Center', 'bb-booster'),
							'right'				=> __('Right', 'bb-booster')
						)
					)
				)
			)
		)
	)
));