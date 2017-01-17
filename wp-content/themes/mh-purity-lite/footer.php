<?php if (is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3')) { ?>
<footer class="footer clearfix">
	<?php if (is_active_sidebar('footer-1')) { ?>
		<div class="col-1-3 footer-widget-area">
			<?php dynamic_sidebar('footer-1'); ?>
		</div>
	<?php } ?>
	<?php if (is_active_sidebar('footer-2')) { ?>
		<div class="col-1-3 footer-widget-area">
			<?php dynamic_sidebar('footer-2'); ?>
		</div>
	<?php } ?>
	<?php if (is_active_sidebar('footer-3')) { ?>
		<div class="col-1-3 footer-widget-area">
			<?php dynamic_sidebar('footer-3'); ?>
		</div>
	<?php } ?>
</footer>
<?php } ?>
<div class="copyright-wrap">
	<p class="copyright"><?php printf(__('Copyright &copy; %1$s | MH Purity <em>lite</em> WordPress Theme by %2$s', 'mh-purity-lite'), date("Y"), '<a href="' . esc_url('https://www.mhthemes.com/') . '" rel="nofollow">MH Themes</a>'); ?></p>
</div>
</div>
<?php wp_footer(); ?>
</body>
</html>