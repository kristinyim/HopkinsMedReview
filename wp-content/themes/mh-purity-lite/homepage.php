<?php /* Template Name: Homepage */ ?>
<?php get_header(); ?>
<div class="wrapper hp clearfix">
	<?php if (is_active_sidebar('home-1') || is_active_sidebar('home-2')) : ?>
	<div class="home-wrap clearfix">
		<?php if (is_active_sidebar('home-1')) { ?>
			<div class="content <?php mh_content_class(); ?>">
			    <?php dynamic_sidebar('home-1'); ?>
			</div>
		<?php } ?>
		<?php if (is_active_sidebar('home-2')) { ?>
		    <div class="hp-sidebar <?php mh_sb_class(); ?>">
			    <?php dynamic_sidebar('home-2'); ?>
			</div>
		<?php } ?>
	</div>
	<?php endif; ?>
</div>
<?php get_footer(); ?>