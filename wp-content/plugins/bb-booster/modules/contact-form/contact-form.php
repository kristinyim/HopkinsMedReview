<?php

/**
 * @class FLHtmlModule
 */
class FLContactFormModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(array(
			'name'           	=> __('Contact Form', 'bb-booster'),
			'description'    	=> __('A very simple contact form.', 'bb-booster'),
			'category'       	=> __('Advanced Modules', 'bb-booster'),
			'editor_export'  	=> false,
			'partial_refresh'	=> true
		));

		add_action('wp_ajax_fl_builder_email', array($this, 'send_mail'));
		add_action('wp_ajax_nopriv_fl_builder_email', array($this, 'send_mail'));
	}
    
	/**
	 * @method send_mail
	 */
	public function send_mail() {    
	    global $fl_contact_from_name, $fl_contact_from_email;

		// Get the contact form post data
    	$node_id			= isset( $_POST['node_id'] ) ? sanitize_text_field( $_POST['node_id'] ) : false;
    	$template_id    	= isset( $_POST['template_id'] ) ? sanitize_text_field( $_POST['template_id'] ) : false;
		$template_node_id   = isset( $_POST['template_node_id'] ) ? sanitize_text_field( $_POST['template_node_id'] ) : false;

		$subject 			= (isset($_POST['subject']) ? $_POST['subject'] : __('Contact Form Submission', 'bb-booster'));
		$mailto 			= get_option('admin_email');
		
		if ( $node_id ) {

			// Get the module settings.
			if ( $template_id ) {
				$post_id  = FLBuilderModel::get_node_template_post_id( $template_id );
				$data	  = FLBuilderModel::get_layout_data( 'published', $post_id );
				$settings = $data[ $template_node_id ]->settings;
			}
			else {
				$module   = FLBuilderModel::get_module( $node_id );
				$settings = $module->settings;
			}

			if ( isset($settings->mailto_email) && !empty($settings->mailto_email) ) {
				$mailto   = $settings->mailto_email;
			}

			$fl_contact_from_email = (isset($_POST['email']) ? sanitize_email($_POST['email']) : null);
			$fl_contact_from_name = (isset($_POST['name']) ? $_POST['name'] : null);
			
			add_filter('wp_mail_from', 'FLContactFormModule::mail_from');
			add_filter('wp_mail_from_name', 'FLContactFormModule::from_name');
	        
			// Build the email
			$template = "";

			if (isset($_POST['name']))  $template .= "Name: $_POST[name] \r\n";
			if (isset($_POST['email'])) $template .= "Email: $_POST[email] \r\n";
			if (isset($_POST['phone'])) $template .= "Phone: $_POST[phone] \r\n";

			$template .= __('Message', 'bb-booster') . ": \r\n" . $_POST['message'];

			// Double check the mailto email is proper and send
			if ($mailto) {
				wp_mail($mailto, $subject, $template);
				die('1');
			} else {
				die($mailto);
			}
		}
	}

	static public function mail_from($original_email_address) {
		global $fl_contact_from_email;
		return ($fl_contact_from_email != '') ? $fl_contact_from_email : $original_email_address;
	}

	static public function from_name($original_name) {
		global $fl_contact_from_name;
		return ($fl_contact_from_name != '') ? $fl_contact_from_name : $original_name;
	}
	
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLContactFormModule', array(
	'general'       => array(
		'title'         => __('General', 'bb-booster'),
		'sections'      => array(
			'general'       => array(
				'title'         => '',
				'fields'        => array(
					'mailto_email'     => array(
						'type'          => 'text',
						'label'         => __('Send To Email', 'bb-booster'),
						'default'       => '',
						'placeholder'   => __('example@mail.com', 'bb-booster'),
						'help'          => __('The contact form will send to this e-mail. Defaults to the admin email.', 'bb-booster'),
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'name_toggle'   => array(
						'type'          => 'select',
						'label'         => __('Name Field', 'bb-booster'),
						'default'       => 'show',
						'options'       => array(
							'show'      => __('Show', 'bb-booster'),
							'hide'      => __('Hide', 'bb-booster'),
						)
					),
					'subject_toggle'   => array(
						'type'          => 'select',
						'label'         => __('Subject Field', 'bb-booster'),
						'default'       => 'hide',
						'options'       => array(
							'show'      => __('Show', 'bb-booster'),
							'hide'      => __('Hide', 'bb-booster'),
						)
					),
					'email_toggle'   => array(
						'type'          => 'select',
						'label'         => __('Email Field', 'bb-booster'),
						'default'       => 'show',
						'options'       => array(
							'show'      => __('Show', 'bb-booster'),
							'hide'      => __('Hide', 'bb-booster'),
						)
					),
					'phone_toggle'   => array(
						'type'          => 'select',
						'label'         => __('Phone Field', 'bb-booster'),
						'default'       => 'hide',
						'options'       => array(
							'show'      => __('Show', 'bb-booster'),
							'hide'      => __('Hide', 'bb-booster'),
						)
					)
				)
			),
			'success'       => array(
				'title'         => __( 'Success', 'bb-booster' ),
				'fields'        => array(
					'success_action' => array(
						'type'          => 'select',
						'label'         => __( 'Success Action', 'bb-booster' ),
						'options'       => array(
							'none'          => __( 'None', 'bb-booster' ),
							'show_message'  => __( 'Show Message', 'bb-booster' ),
							'redirect'      => __( 'Redirect', 'bb-booster' )
						),
						'toggle'        => array(
							'show_message'       => array(
								'fields'        => array( 'success_message' )
							),
							'redirect'      => array(
								'fields'        => array( 'success_url' )
							)
						),
						'preview'       => array(
							'type'             => 'none'  
						)
					),
					'success_message' => array(
						'type'          => 'editor',
						'label'         => '',
						'media_buttons' => false,
						'rows'          => 8,
						'default'       => __( 'Thanks for your message! We’ll be in touch soon.', 'bb-booster' ),
						'preview'       => array(
							'type'             => 'none'  
						)
					),
					'success_url'  => array(
						'type'          => 'link',
						'label'         => __( 'Success URL', 'bb-booster' ),
						'preview'       => array(
							'type'             => 'none'  
						)
					)
				)
			)
		)
	),
	'button'        => array(
		'title'         => __( 'Button', 'bb-booster' ),
		'sections'      => array(
			'btn_general'   => array(
				'title'         => '',
				'fields'        => array(
					'btn_text'      => array(
						'type'          => 'text',
						'label'         => __( 'Button Text', 'bb-booster' ),
						'default'       => __( 'Send', 'bb-booster' )
					),
					'btn_icon'      => array(
						'type'          => 'icon',
						'label'         => __( 'Button Icon', 'bb-booster' ),
						'show_remove'   => true
					),
					'btn_icon_position' => array(
						'type'          => 'select',
						'label'         => __('Icon Position', 'bb-booster'),
						'default'       => 'after',
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
				'title'         => __( 'Button Colors', 'bb-booster' ),
				'fields'        => array(
					'btn_bg_color'  => array(
						'type'          => 'color',
						'label'         => __( 'Background Color', 'bb-booster' ),
						'default'       => '',
						'show_reset'    => true
					),
					'btn_bg_hover_color' => array(
						'type'          => 'color',
						'label'         => __( 'Background Hover Color', 'bb-booster' ),
						'default'       => '',
						'show_reset'    => true,
						'preview'       => array(
							'type'          => 'none'
						)
					),
					'btn_text_color' => array(
						'type'          => 'color',
						'label'         => __( 'Text Color', 'bb-booster' ),
						'default'       => '',
						'show_reset'    => true
					),
					'btn_text_hover_color' => array(
						'type'          => 'color',
						'label'         => __( 'Text Hover Color', 'bb-booster' ),
						'default'       => '',
						'show_reset'    => true,
						'preview'       => array(
							'type'          => 'none'
						)
					)
				)
			),
			'btn_style'     => array(
				'title'         => __( 'Button Style', 'bb-booster' ),
				'fields'        => array(
					'btn_style'     => array(
						'type'          => 'select',
						'label'         => __( 'Style', 'bb-booster' ),
						'default'       => 'flat',
						'options'       => array(
							'flat'          => __( 'Flat', 'bb-booster' ),
							'gradient'      => __( 'Gradient', 'bb-booster' ),
							'transparent'   => __( 'Transparent', 'bb-booster' )
						),
						'toggle'        => array(
							'transparent'   => array(
								'fields'        => array( 'btn_bg_opacity', 'btn_bg_hover_opacity', 'btn_border_size' )
							)
						)
					),
					'btn_border_size' => array(
						'type'          => 'text',
						'label'         => __( 'Border Size', 'bb-booster' ),
						'default'       => '2',
						'description'   => 'px',
						'maxlength'     => '3',
						'size'          => '5',
						'placeholder'   => '0'
					),
					'btn_bg_opacity' => array(
						'type'          => 'text',
						'label'         => __( 'Background Opacity', 'bb-booster' ),
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
				'title'         => __( 'Button Structure', 'bb-booster' ),
				'fields'        => array(
					'btn_width'     => array(
						'type'          => 'select',
						'label'         => __('Width', 'bb-booster'),
						'default'       => 'auto',
						'options'       => array(
							'auto'          => _x( 'Auto', 'Width.', 'bb-booster' ),
							'full'          => __('Full Width', 'bb-booster')
						)
					),
					'btn_align'    	=> array(
						'type'          => 'select',
						'label'         => __('Alignment', 'bb-booster'),
						'default'       => 'left',
						'options'       => array(
							'left'          => __('Left', 'bb-booster'),
							'center'		=> __('Center', 'bb-booster'),
							'right'         => __('Right', 'bb-booster'),
						)
					),
					'btn_font_size' => array(
						'type'          => 'text',
						'label'         => __( 'Font Size', 'bb-booster' ),
						'default'       => '14',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
					),
					'btn_padding'   => array(
						'type'          => 'text',
						'label'         => __( 'Padding', 'bb-booster' ),
						'default'       => '10',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
					),
					'btn_border_radius' => array(
						'type'          => 'text',
						'label'         => __( 'Round Corners', 'bb-booster' ),
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

