<?php if ( 'transifex_gen' == $section['id'] ) : ?>

	<h2>Transifex Settings.</h2>

<?php elseif ( 'translation' == $section['id'] ) : ?>

	<h2>Translation Settings</h2>

<?php elseif ( 'switcher_sec' == $section['id'] ) : ?>

	<h2>Language Switcher Settings</h2>
	
<?php elseif ( 'switcher_tx' == $section['id'] ) : ?>

	<h3 class="<?php echo ($tx_switcher)? '': 'inactive-sec' ?>" id="<?php echo esc_attr($section['id']) ?>" >Transifex Switcher Settings.</h3>

<?php elseif ( 'switcher_wp' == $section['id'] ) : ?>

	<h3 class="<?php echo ($tx_switcher)? 'inactive-sec': '' ?>" id="<?php echo esc_attr($section['id']) ?>" >Custom WP Switcher Settings.</h3>

<?php endif; ?>


