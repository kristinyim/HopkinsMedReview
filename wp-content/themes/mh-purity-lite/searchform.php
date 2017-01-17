<form role="search" method="get" id="searchform" action="<?php echo esc_url(home_url()); ?>/">
    <fieldset>
	<input type="text" value="" name="s" id="s" />
	<input type="submit" id="searchsubmit" value="<?php esc_attr_e('Search', 'mh-purity-lite'); ?>" />
    </fieldset>
</form>