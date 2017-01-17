<?php

/**
 * @class FLGalleryModule
 */
class FLGalleryModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct()
	{
		parent::__construct(array(
			'name'          	=> __('Gallery', 'bb-booster'),
			'description'   	=> __('Display multiple photos in a gallery view.', 'bb-booster'),
			'category'      	=> __('Advanced Modules', 'bb-booster'),
			'editor_export'  	=> false,
			'partial_refresh'	=> true
		));

		$this->add_styles_scripts();
	}

	/**
	 * @method add_styles_scripts()
	 */
	public function add_styles_scripts()
	{
		$this->add_js('jquery-wookmark');
		$this->add_js('jquery-mosaicflow');

		$override_lightbox = apply_filters( 'fl_builder_override_lightbox', false );
		if ( ! $override_lightbox ) {
			$this->add_js('jquery-magnificpopup');
			$this->add_css('jquery-magnificpopup');	
		}
		else {
			wp_dequeue_script('jquery-magnificpopup');
			wp_dequeue_style('jquery-magnificpopup');
		}
	}

	/**
	 * @method update
	 * @param $settings {object}
	 */
	public function update($settings)
	{
		// Cache the photo data if using the WordPress media library.
		if($settings->source == 'wordpress') {
			$settings->photo_data = $this->get_wordpress_photos();
		}

		return $settings;
	}

	/**
	 * @method get_photos
	 */
	public function get_photos()
	{
		// WordPress
		if($this->settings->source == 'wordpress') {
			return $this->get_wordpress_photos();
		}

		// SmugMug
		if($this->settings->source == 'smugmug') {
			return $this->get_smugmug_photos();
		}
	}

	/**
	 * @method get_wordpress_photos
	 */
	public function get_wordpress_photos()
	{
		$photos     = array();
		$ids        = $this->settings->photos;
		$medium_w   = get_option('medium_size_w');
		$large_w    = get_option('large_size_w');

		if(empty($this->settings->photos)) {
			return $photos;
		}

		foreach($ids as $id) {

			$photo = FLBuilderPhoto::get_attachment_data($id);

			// Use the cache if we didn't get a photo from the id.
			if ( ! $photo ) {
				
				if ( ! isset( $this->settings->photo_data ) ) {
					continue;
				}
				else if ( is_array( $this->settings->photo_data ) ) {
					$photos[ $id ] = $this->settings->photo_data[ $id ];
				}
				else if ( is_object( $this->settings->photo_data ) ) {
					$photos[ $id ] = $this->settings->photo_data->{$id};
				}
				else {
					continue;
				}
			}

			// Only use photos who have the sizes object.
			if(isset($photo->sizes)) {

				// Photo data object
				$data = new stdClass();
				$data->id = $id;
				$data->alt = $photo->alt;
				$data->caption = $photo->caption;
				$data->description = $photo->description;
				$data->title = $photo->title;

				// Collage photo src
				if($this->settings->layout == 'collage') {

					if($this->settings->photo_size < $medium_w && isset($photo->sizes->medium)) {
						$data->src = $photo->sizes->medium->url;
					}
					else if($this->settings->photo_size <= $large_w && isset($photo->sizes->large)) {
						$data->src = $photo->sizes->large->url;
					}
					else {
						$data->src = $photo->sizes->full->url;
					}
				}

				// Grid photo src
				else {

					if(isset($photo->sizes->thumbnail)) {
						$data->src = $photo->sizes->thumbnail->url;
					}
					else {
						$data->src = $photo->sizes->full->url;
					}
				}

				// Photo Link
				if(isset($photo->sizes->large)) {
					$data->link = $photo->sizes->large->url;
				}
				else {
					$data->link = $photo->sizes->full->url;
				}

				// Push the photo data
				$photos[$id] = $data;
			}
		}

		return $photos;
	}

	/**
	 * @method get_smugmug_photos
	 */
	public function get_smugmug_photos()
	{
		$photos = array();

		// Load the feed into a DOM object.
		$feed = @simplexml_load_file($this->settings->feed_url);

		if($feed !== false) {

			// Get the feed data into an array.
			foreach($feed->channel->item as $item) {

				// SmugMug photo sizes.
				$media = array();

				foreach($item->xpath('media:group/media:content') as $media_content) {
					if($media_content['medium'] == 'image') {
						$media[] = array(
							'height'    => $media_content['height'],
							'width'     => $media_content['width'],
							'url'       => $media_content['url']
						);
					}
				}

				// Only continue if we have media.
				if(count($media) > 0) {

					// Photo link
					if(count($media) <= 3) {
						$link = $media[0]['url'];
					}
					else {
						$link = $media[count($media) - 2]['url'];
					}

					// Photo Src
					if($this->settings->layout == 'collage') {
						for($i = count($media) - 1; $i >= 0; $i--) {
							if($this->settings->photo_size <= $media[$i]['width']) {
								$src = $media[$i]['url'];
							}
						}
					}
					else {
						$src = $media[1]['url'];
					}

					// Photo data object.
					$data = new stdClass();
					$data->alt = $item->title;
					$data->caption = $item->title;
					$data->description = $item->title;
					$data->title = $item->title;
					$data->height = $media[count($media) - 1]['height'];
					$data->width = $media[count($media) - 1]['width'];
					$data->link = $link;
					$data->src = $src;

					// Push the photo data.
					array_push($photos, $data);
				}
			}
		}

		return $photos;
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLGalleryModule', array(
	'general'       => array(
		'title'         => __('General', 'bb-booster'),
		'sections'      => array(
			'general'       => array(
				'title'         => '',
				'fields'        => array(
					'layout'        => array(
						'type'          => 'select',
						'label'         => __('Layout', 'bb-booster'),
						'default'       => 'collage',
						'options'       => array(
							'collage'       => __('Collage', 'bb-booster'),
							'grid'          => _x( 'Thumbs', 'Gallery layout: thumbnails.', 'bb-booster' )
						),
						'toggle'        => array(
							'collage'       => array(
								'fields'       => array('photo_size')
							)
						)
					),
					'source'        => array(
						'type'          => 'select',
						'label'         => __('Source', 'bb-booster'),
						'default'       => 'wordpress',
						'options'       => array(
							'wordpress'     => __('Media Library', 'bb-booster'),
							'smugmug'       => 'SmugMug'
						),
						'help'          => __('Pull images from the WordPress media library or a gallery on your SmugMug site by inserting the RSS feed URL from SmugMug. The RSS feed URL can be accessed by using the get a link function in your SmugMug gallery.', 'bb-booster'),
						'toggle'        => array(
							'wordpress'      => array(
								'fields'        => array('photos')
							),
							'smugmug'        => array(
								'fields'        => array('feed_url')
							)
						)
					),
					'photos'        => array(
						'type'          => 'multiple-photos',
						'label'         => __('Photos', 'bb-booster')
					),
					'feed_url'   => array(
						'type'          => 'text',
						'label'         => __('Feed URL', 'bb-booster')
					),
					'photo_size'    => array(
						'type'          => 'select',
						'label'         => __('Photo Size', 'bb-booster'),
						'default'       => '300',
						'options'       => array(
							'200'           => _x( 'Small', 'Photo size.', 'bb-booster' ),
							'300'           => _x( 'Medium', 'Photo size.', 'bb-booster' ),
							'400'           => _x( 'Large', 'Photo size.', 'bb-booster')
						)
					),
					'photo_spacing' => array(
						'type'          => 'text',
						'label'         => __('Photo Spacing', 'bb-booster'),
						'default'       => '20',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px'
					),
					'show_captions' => array(
						'type'          => 'select',
						'label'         => __('Show Captions', 'bb-booster'),
						'default'       => '0',
						'options'       => array(
							'0'             => __('Never', 'bb-booster'),
							'hover'         => __('On Hover', 'bb-booster'),
							'below'         => __('Below Photo', 'bb-booster')
						),
						'help'          => __('The caption pulls from whatever text you put in the caption area in the media manager for each image. The caption is also pulled directly from SmugMug if you have captions set in your gallery.', 'bb-booster')
					),
					'click_action'  => array(
						'type'          => 'select',
						'label'         => __('Click Action', 'bb-booster'),
						'default'       => 'lightbox',
						'options'       => array(
							'none'          => _x( 'None', 'Click action.', 'bb-booster' ),
							'lightbox'      => __('Lightbox', 'bb-booster'),
							'link'          => __('Photo Link', 'bb-booster')
						),
						'preview'       => array(
							'type'          => 'none'
						)
					)
				)
			)
		)
	)
));