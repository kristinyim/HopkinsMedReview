<?php
$mh_purity_lite_options = mh_purity_lite_theme_options();
$excerpt_length = empty($mh_purity_lite_options['excerpt_length']) ? '110' : $mh_purity_lite_options['excerpt_length'];
?>
<article <?php post_class(); ?>>
	<div class="loop-wrap clearfix">
		<div class="loop-thumb">
			<a href="<?php the_permalink(); ?>">
				<?php if (has_post_thumbnail()) { the_post_thumbnail('featured'); } else { echo '<img src="' . get_template_directory_uri() . '/images/noimage_featured.png' . '" alt="No Picture" />'; } ?>
			</a>
		</div>
		<header class="loop-data">
			<div class="loop-meta">
				<span class="loop-date"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php $post_date = get_the_date(); echo $post_date; ?></a></span>
				<span class="loop-comments"><i class="fa fa-comment-o"></i><?php comments_number('0', '1', '%'); ?></span>
			</div>
			<h3 class="loop-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
		</header>
		<?php mh_excerpt($excerpt_length); ?>
	</div>
</article>