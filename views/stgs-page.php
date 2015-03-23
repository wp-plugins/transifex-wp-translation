<?php require(ABSPATH . 'wp-admin/options-head.php'); ?>
<?php
$active = 'nav-tab-active';
?>
<div class="wrap">
<h2><?php _e( 'Transifex WP Translation - General', 'txwt' ); ?></h2> 

<div id="icon-plugins" class="icon32"></div>
<?php if(empty($settings['general']['tx_api_key'])):?>
<div class="error updated"> 
<p><strong><a href="?page=txwt-plugin">Your API Key</a> is required to use Transifex Translation Plugin</strong></p> 
</div>
<?php endif;?>

<h2 class="nav-tab-wrapper">
<a href="?page=txwt-plugin" class="nav-tab <?php echo ($active_tab['db'])?$active:'' ?>">General</a>
<a href="?page=txwt-plugin_switcher" class="nav-tab <?php echo ($active_tab['ls'])?$active:'' ?>"> Language Switcher</a>
<?php do_action('txwt-admin_menu', $active_tab, $active) ?>
<a href="?page=toplevel_page_itsec_help" class="nav-tab <?php echo ($active_tab['hp'])?$active:'' ?>"> Help</a>
</h2>
	<br/>
	
	
	<div class="twt-help-page" id="poststuff">
	
	<div class="alert critical" id="empty-langs-err">
	<h3 class="key-status failed"><?php esc_html_e("We are unable to fetch your languages.", 'txwt'); ?></h3>
	<p class="description"><?php printf( __('Make sure your API Key is correct and your firewall ins\'t blocking us refer to <a href="%s" target="_blank">our guide about API Keys</a>.', 'txwt'), 'http://#.com/'); ?></p>
    </div>
	
	<div class="alert critical" id="integrity-err">
	<h3 class="key-status failed"><?php esc_html_e("We are unable to fetch your languages.", 'txwt'); ?></h3>
	<p class="description"><?php printf( __('There seems to be a problem with the integrity of the returned data <a href="%s" target="_blank">please contact support about this</a>.', 'txwt'), 'http://#.com/'); ?></p>
    </div>
	
	<div class="alert success" id="langs-fetched-notice">
	<h3 class="key-status success"><?php _e("Olé ! Languages retrieved successfully.", 'txwt'); ?></h3>
	<p class="description">Languages in your setup: <span class="languages"></span></p>
    </div>	

		<div class="content alignleft">

			<div id="twt-help-content">
					
					<form id="txwt-plugin-form" action="options.php" method="POST">
						
							<?php if($screen == 'toplevel_page_txwt-plugin'):?>
							<?php settings_fields( 'txwt_tx_stgs') ?>
							<?php do_settings_sections( 'txwt-plugin' ); ?>
							<?php elseif($screen == 'transifex_page_txwt-plugin_switcher'):?>
							<?php settings_fields( 'txwt_ls_stgs') ?>
							<?php do_settings_sections( 'txwt-plugin_switcher' ); ?>
							<?php else: ?>
							<?php do_action('txwt-add_settings') ?>
							<?php endif; ?>
							
							<input class="button-primary" type="submit" value="<?php _e( "Save", 'txwt' ); ?>" />
					</form> <!-- end of #dxtemplate-form -->
				
			</div>

			<footer class='twt-footer'>
				<a href="http://zanto.org/support" target="_blank">Plugin Support</a>
			</footer>

		</div>
		<div class="sidebar">
		 <?php do_meta_boxes($screen, 'normal', null); ?>
		</div>
		<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('toplevel_page_txwt-plugin');
	});
	
	 window.liveSettings = {
                    api_key: document.getElementById('tx_api_key').value
                };
	//]]>
</script>
	</div>
	
</div>