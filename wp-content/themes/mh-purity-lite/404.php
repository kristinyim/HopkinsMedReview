<?php get_header(); ?>
<div class="wrapper clearfix">
	<div class="main">
		<section class="content <?php mh_content_class(); ?>">
			<?php mh_before_page_content(); ?>
			<div class="entry sb-widget">
				<p><?php _e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'mh-purity-lite'); ?></p>
				<?php get_search_form(); ?>
			</div>
			<div class="sb-widget home-2 home-wide"><?php
				$instance = array('title' => __('Latest Articles', 'mh-purity-lite'), 'postcount' => '5', 'sticky' => 1);
				$args = array('before_widget' => '<div class="sb-widget">', 'after_widget' => '</div>', 'before_title' => '<h4 class="widget-title">', 'after_title' => '</h4>');
				the_widget('mh_custom_posts_widget', $instance , $args); ?>
			</div>
		</section>
		<?php get_sidebar(); ?>
	</div>
</div>
<?php get_footer(); ?>