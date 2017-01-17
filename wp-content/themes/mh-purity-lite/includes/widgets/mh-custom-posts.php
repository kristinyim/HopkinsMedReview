<?php

/***** MH Custom Posts *****/

class mh_custom_posts_widget extends WP_Widget {
    function __construct() {
		parent::__construct(
			'mh_custom_posts', esc_html_x('MH Custom Posts [lite]', 'widget name', 'mh-purity-lite'),
			array(
				'classname' => 'mh_custom_posts',
				'description' => esc_html__('Custom Posts Widget to display posts based on categories.', 'mh-purity-lite'),
				'customize_selective_refresh' => true
			)
		);
	}
    function widget($args, $instance) {
        extract($args);
        global $post;
        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
        $category = isset($instance['category']) ? $instance['category'] : '';
        $postcount = empty($instance['postcount']) ? '5' : $instance['postcount'];
        $offset = empty($instance['offset']) ? '' : $instance['offset'];
        $excerpt_length = empty($instance['excerpt_length']) ? '110' : $instance['excerpt_length'];
        $sticky = isset($instance['sticky']) ? $instance['sticky'] : 0;

        if ($category) {
        	$cat_url = get_category_link($category);
	        $before_title = $before_title . '<a href="' . esc_url($cat_url) . '" class="widget-title-link">';
	        $after_title = '</a>' . $after_title;
        }

        echo $before_widget;
        if (!empty($title)) { echo $before_title . $title . $after_title; } ?>
        <div class="cp-widget clearfix"> <?php
		$args = array('posts_per_page' => $postcount, 'offset' => $offset, 'cat' => $category, 'ignore_sticky_posts' => $sticky);
		$counter = 1;
		$widget_loop = new WP_Query($args);
		while ($widget_loop->have_posts()) : $widget_loop->the_post();
			if ($counter == 1) : ?>
				<div class="cp-widget-item cp-large cp-count-<?php echo esc_attr($postcount); ?>">
					<div class="cp-large-thumb"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php if (has_post_thumbnail()) { the_post_thumbnail('featured'); } else { echo '<img src="' . get_template_directory_uri() . '/images/noimage_featured.png' . '" alt="No Picture" />'; } ?></a></div>
					<div class="meta clearfix">
						<span class="meta-date"><?php $post_date = get_the_date(); echo $post_date; ?></span>
						<span class="meta-comments"><i class="fa fa-comment-o"></i><?php comments_number('0', '1', '%'); ?></span>
					</div>
					<h3 class="cp-large-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
					<?php mh_excerpt($excerpt_length); ?>
				</div>
				<ul class="cp-list"><?php
			else: ?>
					<li class="cp-widget-item cp-list-item clearfix">
						<div class="cp-small-thumb"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php if (has_post_thumbnail()) { the_post_thumbnail('cp_small'); } else { echo '<img src="' . get_template_directory_uri() . '/images/noimage_cp_small.png' . '" alt="No Picture" />'; } ?></a></div>
						<div class="meta"><?php $post_date = get_the_date(); echo $post_date; ?></div>
						<p class="cp-small-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></p>
					</li> <?php
			endif;
			$counter++;
		endwhile;
		wp_reset_postdata(); ?>
        	</ul>
        </div><?php
        echo $after_widget;
    }
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['category'] = absint($new_instance['category']);
        $instance['postcount'] = absint($new_instance['postcount']);
        $instance['offset'] = absint($new_instance['offset']);
        $instance['excerpt_length'] = absint($new_instance['excerpt_length']);
        $instance['sticky'] = isset($new_instance['sticky']) ? strip_tags($new_instance['sticky']) : '';
        return $instance;
    }
    function form($instance) {
        $defaults = array('title' => '', 'category' => '', 'postcount' => '5', 'offset' => '0', 'excerpt_length' => '110', 'sticky' => 0);
        $instance = wp_parse_args((array) $instance, $defaults); ?>

        <p>
        	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'mh-purity-lite'); ?></label>
			<input class="widefat" type="text" value="<?php echo esc_attr($instance['title']); ?>" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Select a Category:', 'mh-purity-lite'); ?></label>
			<select id="<?php echo $this->get_field_id('category'); ?>" class="widefat" name="<?php echo $this->get_field_name('category'); ?>">
				<option value="0" <?php if (!$instance['category']) echo 'selected="selected"'; ?>><?php _e('All', 'mh-purity-lite'); ?></option>
				<?php
				$categories = get_categories(array('type' => 'post'));
				foreach($categories as $cat) {
					echo '<option value="' . $cat->cat_ID . '"';
					if ($cat->cat_ID == $instance['category']) { echo ' selected="selected"'; }
					echo '>' . $cat->cat_name . ' (' . $cat->category_count . ')';
					echo '</option>';
				}
				?>
			</select>
		</p>
	    <p>
        	<label for="<?php echo $this->get_field_id('postcount'); ?>"><?php _e('Limit Post Number:', 'mh-purity-lite'); ?></label>
			<input class="widefat" type="text" value="<?php echo esc_attr($instance['postcount']); ?>" name="<?php echo $this->get_field_name('postcount'); ?>" id="<?php echo $this->get_field_id('postcount'); ?>" />
	    </p>
	    <p>
        	<label for="<?php echo $this->get_field_id('offset'); ?>"><?php _e('Skip Posts (Offset):', 'mh-purity-lite'); ?></label>
			<input class="widefat" type="text" value="<?php echo esc_attr($instance['offset']); ?>" name="<?php echo $this->get_field_name('offset'); ?>" id="<?php echo $this->get_field_id('offset'); ?>" />
	    </p>
        <p>
        	<label for="<?php echo $this->get_field_id('excerpt_length'); ?>"><?php _e('Excerpt Character Limit:', 'mh-purity-lite'); ?></label>
			<input class="widefat" type="text" value="<?php echo esc_attr($instance['excerpt_length']); ?>" name="<?php echo $this->get_field_name('excerpt_length'); ?>" id="<?php echo $this->get_field_id('excerpt_length'); ?>" />
	    </p>
        <p>
      		<input id="<?php echo $this->get_field_id('sticky'); ?>" name="<?php echo $this->get_field_name('sticky'); ?>" type="checkbox" value="1" <?php checked('1', $instance['sticky']); ?>/>
	  		<label for="<?php echo $this->get_field_id('sticky'); ?>"><?php _e('Ignore Sticky Posts', 'mh-purity-lite'); ?></label>
    	</p>
    	<p>
    		<strong>Info:</strong> <?php _e('This is the lite version of this widget with basic features. If you need more professional features and options, you can upgrade to the premium version of this theme.', 'mh-purity-lite'); ?>
    	</p><?php
    }
}

?>