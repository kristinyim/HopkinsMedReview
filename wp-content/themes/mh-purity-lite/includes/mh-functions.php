<?php

/***** Page Title Output *****/

if (!function_exists('mh_page_title_output')) {
	function mh_page_title_output() {
		if (!is_front_page()) {
			echo '<header class="post-header">' . "\n";
				echo '<h1 class="entry-title">';
					mh_page_title();
				echo '</h1>' . "\n";
			echo '</header>' . "\n";
		}
	}
}
add_action('mh_before_page_content', 'mh_page_title_output');

if (!function_exists('mh_page_title')) {
	function mh_page_title() {
		if (is_home()) {
			echo get_the_title(get_option('page_for_posts', true));
		} elseif (is_author()) {
			global $author;
			$user_info = get_userdata($author);
			echo __('Articles by ', 'mh-purity-lite') . esc_attr($user_info->display_name);
		} elseif (is_category() || is_tax()) {
			echo single_cat_title("", false);
		} elseif (is_tag()) {
			echo single_tag_title("", false);
		} elseif (is_search()) {
			echo __('Search Results for ', 'mh-purity-lite') . get_search_query();
		} elseif (is_day()) {
			echo get_the_date();
		} elseif (is_month()) {
			echo get_the_date('F Y');
		} elseif (is_year()) {
			echo get_the_date('Y');
		} elseif (is_404()) {
			echo __('Page not found (404)', 'mh-purity-lite');
		} else {
			echo get_the_title();
		}
	}
}

/***** Featured Image on Posts *****/

if (!function_exists('mh_featured_image')) {
	function mh_featured_image() {
		global $post;
		if (has_post_thumbnail()) {
			$thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(), 'content');
			$caption_text = get_post(get_post_thumbnail_id())->post_excerpt;
			$attachment_page = get_attachment_link(get_post_thumbnail_id());
			echo "\n" . '<div class="post-thumbnail">' . "\n";
				echo '<a href="' . $attachment_page . '"><img src="' . $thumbnail[0] . '" alt="' . get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true) . '" title="' . get_post(get_post_thumbnail_id())->post_title . '" /></a>' . "\n";
				if ($caption_text) {
					echo '<span class="wp-caption-text">' . $caption_text . '</span>' . "\n";
				}
			echo '</div>' . "\n";
		}
	}
}

/***** Pagination for paginated Posts *****/

if (!function_exists('mh_posts_pagination')) {
	function mh_posts_pagination($content) {
		if (is_singular() && is_main_query()) {
			$content .= wp_link_pages(array('before' => '<div class="pagination clear">', 'after' => '</div>', 'link_before' => '<span class="pagelink">', 'link_after' => '</span>', 'nextpagelink' => __('&raquo;', 'mh-purity-lite'), 'previouspagelink' => __('&laquo;', 'mh-purity-lite'), 'pagelink' => '%', 'echo' => 0));
		}
		return $content;
	}
}
add_filter('the_content', 'mh_posts_pagination', 1);

/***** Post / Image Navigation *****/

if (!function_exists('mh_postnav')) {
	function mh_postnav() {
		global $post;
		$parent_post = get_post($post->post_parent);
		$attachment = is_attachment();
		$previous = ($attachment) ? $parent_post : get_adjacent_post(false, '', true);
		$next = get_adjacent_post(false, '', false);

		if (!$next && !$previous)
		return;

		if ($attachment) {
			$attachments = get_children(array('post_type' => 'attachment', 'post_mime_type' => 'image', 'post_parent' => $parent_post->ID));
			$count = count($attachments);
		}
		echo '<nav class="post-nav-wrap clearfix" role="navigation">' . "\n";
			if ($previous || $attachment) {
				echo '<div class="post-nav left">' . "\n";
					if ($attachment) {
						if ($count == 1) {
							$permalink = get_permalink($parent_post);
							echo '<a href="' . $permalink . '">' . __('&larr; Back to article', 'mh-purity-lite') . '</a>';
						} else {
							previous_image_link('%link', __('&larr; Previous image', 'mh-purity-lite'));
						}
					} else {
						previous_post_link('%link', __('&larr; Previous article', 'mh-purity-lite'));
					}
				echo '</div>' . "\n";
			}
			if ($next || $attachment) {
				echo '<div class="post-nav post-nav-next right">' . "\n";
					if ($attachment) {
						next_image_link('%link', __('Next image &rarr;', 'mh-purity-lite'));
					} else {
						next_post_link('%link', __('Next article &rarr;', 'mh-purity-lite'));
					}
				echo '</div>' . "\n";
			}
		echo '</nav>' . "\n";
	}
}
add_action('mh_after_post_content', 'mh_postnav');

/***** Loop Output *****/

if (!function_exists('mh_loop')) {
	function mh_loop() {
		if (have_posts()) {
			while (have_posts()) : the_post();
				get_template_part('loop', get_post_format());
			endwhile;
			mh_pagination();
		} else {
			get_template_part('content', 'none');
		}
	}
}
add_action('mh_loop_content', 'mh_loop');

/***** Logo / Header Image Fallback *****/

if (!function_exists('mh_logo')) {
	function mh_logo() {
		$header_img = get_header_image();
		$title = get_bloginfo('name');
		$desc = get_bloginfo('description');
		echo '<a href="' . esc_url(home_url('/')) . '" title="' . esc_attr(get_bloginfo('name')) . '" rel="home">' . "\n";
		echo '<div class="logo-wrap" role="banner">' . "\n";
		if ($header_img) {
			echo '<img class="header-image" src="' . esc_url($header_img) . '" height="' . get_custom_header()->height . '" width="' . get_custom_header()->width . '" alt="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
		}
		if (display_header_text()) {
			$header_img ? $logo_pos = 'logo-overlay' : $logo_pos = 'logo-text';
			$text_color = get_header_textcolor();
			if ($text_color != get_theme_support('custom-header', 'default-text-color')) {
				echo '<style type="text/css" id="mh-header-css">';
					echo '.logo-name, .logo-desc { color: #' . esc_attr($text_color) . '; }';
				echo '</style>' . "\n";
			}
			echo '<div class="logo ' . $logo_pos . '">' . "\n";
			if ($title) {
				echo '<h1 class="logo-name">' . esc_attr($title) . '</h1>' . "\n";
			}
			if ($desc) {
				echo '<h2 class="logo-desc">' . esc_attr($desc) . '</h2>' . "\n";
			}
			echo '</div>' . "\n";
		}
		echo '</div>' . "\n";
		echo '</a>' . "\n";
	}
}

/***** Custom Excerpts *****/

if (!function_exists('mh_trim_excerpt')) {
	function mh_trim_excerpt($text = '') {
		$raw_excerpt = $text;
		if ('' == $text) {
			$text = get_the_content('');
			$text = do_shortcode($text);
			$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]>', $text);
			$excerpt_length = apply_filters('excerpt_length', '200');
			$excerpt_more = apply_filters('excerpt_more', ' [...]');
			$text = wp_trim_words($text, $excerpt_length, $excerpt_more);
		}
		return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
	}
}
remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'mh_trim_excerpt');

if (!function_exists('mh_excerpt')) {
	function mh_excerpt($excerpt_length = '110') {
		global $post;
		$mh_purity_lite_options = mh_purity_lite_theme_options();
		$permalink = get_permalink($post->ID);
		$excerpt_more = empty($mh_purity_lite_options['excerpt_more']) ? '[...]' : $mh_purity_lite_options['excerpt_more'];
		$excerpt = get_the_excerpt();
		if (!has_excerpt()) {
			$excerpt = substr($excerpt, 0, $excerpt_length);
			$excerpt = substr($excerpt, 0, strrpos($excerpt, ' '));
		}
		echo '<div class="mh-excerpt">' . wp_kses_post($excerpt) . ' <a href="' . $permalink . '" title="' . the_title_attribute('echo=0') . '">' . esc_attr($excerpt_more) . '</a></div>' . "\n";
	}
}

/***** Enable Custom Excerpts for Pages *****/

if (!function_exists('mh_excerpts_pages')) {
	function mh_excerpts_pages() {
		add_post_type_support('page', 'excerpt');
	}
}
add_action('init', 'mh_excerpts_pages');

/***** Custom Commentlist *****/

if (!function_exists('mh_comments')) {
	function mh_comments($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment; ?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
			<div id="comment-<?php comment_ID(); ?>">
				<div class="vcard clearfix">
					<?php echo get_avatar($comment->comment_author_email, 60); ?>
					<span class="comment-author"><?php echo get_comment_author_link(); ?></span>
					<a class="comment-time meta" href="<?php echo esc_url(get_comment_link($comment->comment_ID)) ?>"><?php printf(__('%1$s at %2$s', 'mh-purity-lite'), get_comment_date(),  get_comment_time()) ?></a><br>
					<div class="comment-reply">
					<?php if (comments_open() && $args['max_depth']!=$depth) { ?>
						<?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
					<?php } ?>
						<?php edit_comment_link(__('Edit', 'mh-purity-lite'),'  ','') ?>
					</div>
				</div>
				<?php if ($comment->comment_approved == '0') : ?>
					<div class="comment-info"><?php _e('Your comment is awaiting moderation.', 'mh-purity-lite') ?></div>
				<?php endif; ?>
				<div class="comment-text">
					<?php comment_text() ?>
				</div>
			</div><?php
	}
}

/***** Custom Comment Fields *****/

if (!function_exists('mh_comment_fields')) {
	function mh_comment_fields($fields) {
		$commenter = wp_get_current_commenter();
		$req = get_option('require_name_email');
		$aria_req = ($req ? " aria-required='true'" : '');
		$fields =  array(
			'author'	=>	'<p class="comment-form-author"><label for="author">' . __('Name ', 'mh-purity-lite') . '</label>' . ($req ? '<span class="required">*</span>' : '') . '<br/><input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" size="30"' . $aria_req . ' /></p>',
			'email' 	=>	'<p class="comment-form-email"><label for="email">' . __('Email ', 'mh-purity-lite') . '</label>' . ($req ? '<span class="required">*</span>' : '' ) . '<br/><input id="email" name="email" type="text" value="' . esc_attr($commenter['comment_author_email']) . '" size="30"' . $aria_req . ' /></p>',
			'url' 		=>	'<p class="comment-form-url"><label for="url">' . __('Website', 'mh-purity-lite') . '</label><br/><input id="url" name="url" type="text" value="' . esc_attr($commenter['comment_author_url']) . '" size="30" /></p>'
		);
		return $fields;
	}
}
add_filter('comment_form_default_fields', 'mh_comment_fields');

/***** Pagination *****/

if (!function_exists('mh_pagination')) {
	function mh_pagination() {
		if (get_the_posts_pagination()) {
			echo '<div class="mh-loop-pagination clearfix">';
				the_posts_pagination(array(
					'mid_size' => 1,
					'prev_text' => esc_html__('&laquo;', 'mh-purity-lite'),
					'next_text' => esc_html__('&raquo;', 'mh-purity-lite'),
				));
			echo '</div>';
		}
	}
}

/***** Add CSS classes to content container *****/

if (!function_exists('mh_content_css')) {
	function mh_content_css() {
		$mh_purity_lite_options = mh_purity_lite_theme_options();
		if (isset($mh_purity_lite_options['sb_position']) && $mh_purity_lite_options['sb_position'] == 'left') {
			$float = 'right';
		} else {
			$float = 'left';
		}
		echo $float;
	}
}
add_action('mh_content_class', 'mh_content_css');

/***** Add CSS classes to sidebar container *****/

if (!function_exists('mh_sb_css')) {
	function mh_sb_css($sb_pos = '') {
		$mh_purity_lite_options = mh_purity_lite_theme_options();
		if (isset($mh_purity_lite_options['sb_position']) && $mh_purity_lite_options['sb_position'] == 'left') {
			$sb_pos = 'sb-left';
		} else {
			$sb_pos = 'sb-right';
		}
		echo $sb_pos;
	}
}
add_action('mh_sb_class', 'mh_sb_css');

/***** Add Featured Image Size to Media Gallery Selection *****/

if (!function_exists('mh_custom_image_size_choose')) {
	function mh_custom_image_size_choose($sizes) {
		$custom_sizes = array('content' => 'Featured Image');
		return array_merge($sizes, $custom_sizes);
	}
}
add_filter('image_size_names_choose', 'mh_custom_image_size_choose');

/***** Add CSS3 Media Queries Support for older versions of IE *****/

function mh_purity_lite_media_queries() {
	echo '<!--[if lt IE 9]>' . "\n";
	echo '<script src="' . get_template_directory_uri() . '/js/css3-mediaqueries.js"></script>' . "\n";
	echo '<![endif]-->' . "\n";
}
add_action('wp_head', 'mh_purity_lite_media_queries');


/***** Register Widgets *****/

function register_mh_widgets() {
	register_widget('mh_custom_posts_widget');
	register_widget('mh_slider_widget');
}
add_action('widgets_init', 'register_mh_widgets');

/***** Include Widgets *****/

require_once('widgets/mh-custom-posts.php');
require_once('widgets/mh-slider.php');

?>