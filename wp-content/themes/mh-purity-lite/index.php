<?php get_header(); ?>
<div class="wrapper clearfix">
	<div class="content <?php mh_content_class(); ?>">
		<?php mh_before_page_content(); ?>
		<?php if (category_description()) : ?>
			<div class="cat-desc">
				<?php echo category_description(); ?>
			</div>
		<?php endif; ?>
		<?php mh_loop_content(); ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>