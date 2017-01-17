<article <?php post_class(); ?>>
	<header class="post-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<p class="meta post-meta clearfix">
			<span class="updated meta-date"><i class="fa fa-calendar"></i><?php echo get_the_date(); ?></span>
			<span class="vcard author meta-author"><span class="fn"><i class="fa fa-user"></i><?php the_author_posts_link(); ?></span></span>
			<span class="meta-tags"><i class="fa fa-tag"></i><?php the_category(', '); ?></span>
			<span class="meta-comments"><i class="fa fa-comment-o"></i><?php comments_number('0', '1', '%'); ?></span>
		</p>
	</header>
	<div class="entry clearfix">
		<?php mh_featured_image(); ?>
		<?php the_content(); ?>
	</div>
	<?php the_tags('<div class="post-tags meta clearfix"><p class="meta-tags"><i class="fa fa-tag"></i>', ', ', '</p></div>'); ?>
</article>