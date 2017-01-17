<?php
if (post_password_required()) { ?>
	<p class="no-comments"><?php echo __('This post is password protected. Enter the password to view comments.', 'mh-purity-lite'); ?></p><?php
	return;
}
$comments_by_type = separate_comments($comments);
if (have_comments()) {
	if (!empty($comments_by_type['comment'])) {
		$comment_count = count($comments_by_type['comment']);
		($comment_count !== 1) ? $comment_text = __('Comments', 'mh-purity-lite') : $comment_text = __('Comment', 'mh-purity-lite'); ?>
		<h4 class="widget-title"><?php echo $comment_count . ' Comments'; ?></h4>
		<ol class="commentlist">
			<?php echo wp_list_comments('callback=mh_comments&type=comment'); ?>
		</ol><?php
	}
	if (get_comments_number() > get_option('comments_per_page')) { ?>
		<div class="comments-pagination">
			<?php paginate_comments_links(array('prev_text' => __('&laquo;', 'mh-purity-lite'), 'next_text' => __('&raquo;', 'mh-purity-lite'))); ?>
		</div><?php
	}
	if (!empty($comments_by_type['pings'])) {
		$pings = $comments_by_type['pings'];
		$ping_count = count($comments_by_type['pings']); ?>
		<h4 class="widget-title"><?php echo $ping_count . ' ' . __('Trackbacks & Pingbacks', 'mh-purity-lite'); ?></h4>
		<ol class="pinglist">
        <?php foreach ($pings as $ping) { ?>
			<li class="pings"><i class="fa fa-link"></i><?php echo get_comment_author_link($ping); ?></li>
        <?php } ?>
        </ol><?php
	}
	if (!comments_open()) { ?>
		<p class="no-comments"><?php _e('Comments are closed.', 'mh-purity-lite'); ?></p><?php
	}
}
if (comments_open()) {
	$custom_args = array(
    	'title_reply' => __('Leave a comment', 'mh-purity-lite'),
        'comment_notes_before' => '<p class="comment-notes">' . __('Your email address will not be published.', 'mh-purity-lite') . '</p>',
        'comment_notes_after'  => '',
        'comment_field' => '<p class="comment-form-comment"><label for="comment">' . __('Comment', 'mh-purity-lite') . '</label><br/><textarea id="comment" name="comment" cols="45" rows="5" aria-required="true"></textarea></p>');
	comment_form($custom_args);
}
?>