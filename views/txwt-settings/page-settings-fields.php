<?php
/*
 * General Section
 */
?>
<?php do_action ('transifex_admin') ?>

<?php if ( 'txwt_api-key' == $field['label_for'] ) : ?>
	<input type="text" id="tx_api_key" name="<?php esc_attr_e( 'txwt_tx_stgs[general][tx_api_key]' ); ?>" class="regular-text" value="<?php echo esc_attr( $settings['general']['tx_api_key'] ); ?>" />
    <p style="display:inline-block">
	<a class="button-primary" id="fetch_langs" href="#">Fetch Languages</a><span class="spinner"></span>
       <?php $ajax_nonce = wp_create_nonce("txwt-fetch-langs"); ?>
       <input type="hidden" value="<?php echo $ajax_nonce ?>" name="_ajxwpnonce" id="txwt_fetch_langs">	
	</p>
	<p class="desc label">API Key found in the Transifex Live JavaScript integration code. Please provide it before attempt to fetch languages.</p>
<?php endif; ?>

<?php if ( 'txwt_autocollect' == $field['label_for'] ) : ?>
	<label><input type="checkbox" id="tx_autocollect" name="<?php esc_attr_e( 'txwt_tx_stgs[general][autocollect]' ); ?>" <?php checked( 1, $settings['general']['autocollect'], 1 ); ?>/>Auto Collect Strings</label><br/>
    <p class="desc label">Automatically identify new strings as the users browse the website and send them back to Transifex.</p>
<?php endif; ?>

<?php if ( 'txwt_detect-dynamic-strgs' == $field['label_for'] ) : ?>
	<label><input type="checkbox" id="tx_detect_dynamic_strgs" name="<?php esc_attr_e( 'txwt_tx_stgs[general][detect_dynamic_strgs]' ); ?>" <?php checked( 1, $settings['general']['detect_dynamic_strgs'], 1 ); ?>/> Detect Dynamic Strings</label><br/>
    <p class="desc label">Enable this to detect dynamic content that must be translated too, such as popups or content loaded from AJAX calls.</p>
<?php endif; ?>

<?php if ( 'txwt_staging-server' == $field['label_for'] ) : ?>
	<label><input type="checkbox" id="tx_staging_server" name="<?php esc_attr_e( 'txwt_tx_stgs[general][staging_server]' ); ?>" <?php checked( 1, $settings['general']['staging_server'], 1 ); ?>/> Enable Staging server </label><br/>
    <p class="desc label">
	Use this as a staging/test server before taking translations live to a production server.</p>
<?php endif; ?>

<?php
/*
 * Transifex Translation Section
 */
?>

<?php if ( 'txwt_ignore-tags' == $field['label_for'] ) : ?>
	<input type="text" class="full-width-input" id="ignore_tags" name="<?php esc_attr_e( 'txwt_tx_stgs[translation][ignore_tags]' ); ?>"  value="<?php echo implode(',',$settings['translation']['ignore_tags']) ?>" /> 
    <p class="description">Set of tags that are ignored from string detection comma separated e.g "pre, img".</p>
<?php endif; ?>

<?php if ( 'txwt_ignore-class' == $field['label_for'] ) : ?>
	<input type="text" class="full-width-input" id="ignore_class" name="<?php esc_attr_e( 'txwt_tx_stgs[translation][ignore_class]' ); ?>"  value="<?php echo implode(',',$settings['translation']['ignore_class']) ?>" /> 
    <p class="description">CSS classes whose DOM elements and their children should be excluded comma separated e.g "class-name1, class-name2",</p>
<?php endif; ?>

<?php if ( 'txwt_parse-atts' == $field['label_for'] ) : ?>
	<input type="text" class="full-width-input" id="parse_atts" name="<?php esc_attr_e( 'txwt_tx_stgs[translation][parse_atts]' ); ?>"  value="<?php echo implode(',',$settings['translation']['parse_atts']) ?>" /> 
    <p class="description"> Custom defined attributes that must be automatically translated for the whole website comma separated e.g "tipsy-title, data-name"</p>
<?php endif; ?>

<?php
/*
 * Language Switcher Section
 */
?>

<?php if ( 'txwt_lswitcher-type' == $field['label_for'] ) : ?>
	<label><input type="radio"  value="0" class="wpurl_switcher" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][lswitcher_type]' ); ?>"  <?php checked(0,$settings['lang_switcher']['lswitcher_type'],1) ?> /> WP URL Language Switcher</label>
 	<br/>
	<label><input type="radio"  value="1" class="default_switcher" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][lswitcher_type]' ); ?>"  <?php checked(1,$settings['lang_switcher']['lswitcher_type'],1) ?> /> Transifex JavaScript Switcher (Not recomended)</label>
	<p class="desc label description">See <a href="http://plugins.zanto.org/?p=244" target="">guidelines on swicher type </a> to choose.</p>
<?php endif; ?>

<?php if ( 'txwt_language-order' == $field['label_for'] ) : ?>
<?php $ordered_langs = txwt_order_langs($settings['lang_switcher']['language_order'], $settings['langs']) ?>
	<ul id="sortable">
	<?php foreach($ordered_langs as $lang_details):?>
	<li class="button" id="<?php echo $lang_details['code'] ?>"><?php echo $lang_details['name'] ?></li>
	<?php endforeach;?>
	</ul>
	 <input type="hidden"  name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][language_order]' ); ?>" id="lang_order" value="<?php echo implode(',',$settings['lang_switcher']['language_order']) ?>" />
<?php endif; ?>

<?php if ( 'txwt_lang-url-format' == $field['label_for'] ) : ?>
    <div class="lang-url-formats">
	<label><input type="radio"  value='0' name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][lang_url_format]' ); ?>"  <?php checked(0,$settings['lang_switcher']['lang_url_format'],1) ?> /> Add language to Directories </label>
	<br/>
	<label><input type="radio"  value='1' name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][lang_url_format]' ); ?>"  <?php checked(1,$settings['lang_switcher']['lang_url_format'],1) ?> /> Add language Parameter to URL </label>
 	<div id="subdomain-langs">
	<label><input type="radio"  value='2' name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][lang_url_format]' ); ?>"  <?php checked(2,$settings['lang_switcher']['lang_url_format'],1) ?> /> Add language as subdomain to URL </label>
    <p style="display:none">Oops! Language Subdomains may not work properly because your WordPress install is in a directory!</p>
    </div>
	<p class="description">See <a href="http://plugins.zanto.org/?p=244">documentation</a> on language URL formats</p>
	<div>
<?php endif; ?>

<?php if ( 'txwt_alt-lang-availability' == $field['label_for'] ) : ?>
	<label><input type="checkbox"  name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][alt_lang_availability]' ); ?>"  <?php checked(1,$settings['lang_switcher']['alt_lang_availability'],1) ?> /> Show post translation links </label>
	<p>
	<span>position</span><br>
	<select name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][alt_lang_position]' ); ?>">
	<option value='0' <?php selected(0,$settings['lang_switcher']['alt_lang_position'],1) ?>>Below Post</option>
	<option value='1' <?php selected(1,$settings['lang_switcher']['alt_lang_position'],1) ?>>Above Post</option>
	</select >
	</p><p>
	<span>Translation Links Style</span><br/>
	<select name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][alt_lang_style]' ); ?>">
	<option value="0" <?php selected(0,$settings['lang_switcher']['alt_lang_style'],1) ?>>Default</option>
	<option value="1" <?php selected(1,$settings['lang_switcher']['alt_lang_style'],1) ?>>Plain</option>
	</select >	
	</p>
	<p>
	<span>Text for alternative languages for posts</span><br>
	<input type="text"  name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][alt_lang_availability_text]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['lang_switcher']['alt_lang_availability_text'] ); ?>" />
	</p>
<?php endif; ?>

<?php if ( 'txwt_show-footer-selector' == $field['label_for'] ) : ?>
	<label><input type="checkbox"  name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][show_footer_selector]' ); ?>"  <?php checked(1,$settings['lang_switcher']['show_footer_selector'],1) ?> /> Show footer Language Switcher </label>
	
<?php endif; ?>

<?php if ( 'txwt_ls-elements' == $field['label_for'] ) : ?>

	<label><input type="checkbox"  name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][elements][flag]' ); ?>"  <?php checked(1,$settings['lang_switcher']['elements']['flag'],1) ?> /> Flag </label> &nbsp;
	<label><input type="checkbox"  name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][elements][name]' ); ?>"  <?php checked(1,$settings['lang_switcher']['elements']['name'],1) ?> /> Language Name </label>&nbsp;
	<label><input type="checkbox"  name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][elements][code]' ); ?>"  <?php checked(1,$settings['lang_switcher']['elements']['code'],1) ?> /> Language Code </label> &nbsp;
      <p><br/>
	  <select name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][elements][sep]' ); ?>">
	  <option value= "" <?php selected('',$settings['lang_switcher']['elements']['sep'],1)?> >NON</option>
	  <option value= "/" <?php selected('/',$settings['lang_switcher']['elements']['sep'],1)?>>Slash (/)</option>
	  <option value="-" <?php selected('-',$settings['lang_switcher']['elements']['sep'],1)?>>Dash (-)</option>
	  <option value="," <?php selected(',',$settings['lang_switcher']['elements']['sep'],1)?>>Comma (,)</option>
	  
	  </select> Language Separator </label></p>
	  <p class="description"> The language separator is applied to languages in horizontal view for the language switcher widget and footer language selector</p>
<?php endif; ?>

<?php if ( 'txwt_ls-custom-css' == $field['label_for'] ) : ?>
	<label>Custom CSS</label>
	<p>
	<textarea  autocomplete="off" placehoder=".lang_switcher{position: relative;}" cols="50" rows="4" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][txwt_ls_custom_css]' ); ?>" ><?php echo $settings['lang_switcher']['txwt_ls_custom_css']; ?></textarea>
	</p>
<?php endif; ?>

<?php if ( 'txwt_ls-theme' == $field['label_for'] ) : ?>
	<select id="txwt-ls_theme" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][txwt_ls_theme]' ); ?>" > 
	<option value="0" <?php selected(0,$settings['lang_switcher']['txwt_ls_theme'],1) ?>>Custom</option>
	<option value="1" <?php selected(1,$settings['lang_switcher']['txwt_ls_theme'],1) ?>>Light</option>
	<option value="2" <?php selected(2,$settings['lang_switcher']['txwt_ls_theme'],1) ?>>Dark</option>	
	</select>
	
	<div class="txwt-customizer">
	<div class="txwt-color-picker"><label>Background</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][custom_theme][bg]' ); ?>"  value="<?php echo $settings['lang_switcher']['custom_theme']['bg'] ?>" />  </div>
	<div class="txwt-color-picker"><label>Menu Text</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][custom_theme][menu_text]' ); ?>"  value="<?php echo $settings['lang_switcher']['custom_theme']['menu_text'] ?>" /> </div>
	<div class="txwt-color-picker"><label>Drop Text</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][custom_theme][drop_text]' ); ?>"  value="<?php echo $settings['lang_switcher']['custom_theme']['drop_text'] ?>" /> </div>
	<div class="txwt-color-picker"><label>Drop Background</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][custom_theme][drop_bg]' ); ?>"  value="<?php echo $settings['lang_switcher']['custom_theme']['drop_bg'] ?>" />  </div>
	<div class="txwt-color-picker"><label>Drop Hover</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][custom_theme][hover_bg]' ); ?>"  value="<?php echo $settings['lang_switcher']['custom_theme']['hover_bg'] ?>" />  </div>	
	<div class="txwt-color-picker"><label>Bottom Border</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][custom_theme][bb]'); ?>"  value="<?php echo $settings['lang_switcher']['custom_theme']['bb'] ?>" /> </div>
	</div>

<?php endif; ?>

<?php if ( 'txwt_use-custom-flags' == $field['label_for'] ) : ?>
<?php $dir =  str_replace(content_url(), '', get_stylesheet_directory_uri()); ?>
<?php $custom_flg_url=(!empty($settings['lang_switcher']['custom_flag_url']))? $settings['lang_switcher']['custom_flag_url']:$dir.'/flags/'; ?>

	<label><input type="checkbox"  id="use_custom_flags" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][use_custom_flags]' ); ?>"  <?php checked(1,$settings['lang_switcher']['use_custom_flags'],1) ?> /> Use Custom Flags </label>
    <p class="custom_flags"><b>Enter a flag directory from your theme</b> <br/>
	<input type="text" id="txwt-flag-dir" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][custom_flag_url]' ); ?>"  value="<?php echo $custom_flg_url ?>"> 
	<select name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][custom_flag_ext]' ); ?>"> 
	<option value="png" "<?php selected('png', $settings['lang_switcher']['custom_flag_ext'], 1 ) ?>"> .PNG</option>
	<option value="gif" "<?php selected('gif', $settings['lang_switcher']['custom_flag_ext'], 1 ) ?>"> .GIF</option>
	<option value="jpg" "<?php selected('jpg', $settings['lang_switcher']['custom_flag_ext'], 1 ) ?>"> .JPG</option>
     </select>
	 	&nbsp;
	<input type="button" value="Default Directory" class="button" id="default_dir">
	</p>
<?php endif; ?>

<?php if ( 'txwt_switcher-pos' == $field['label_for'] ) : ?>
  
   <div class="tx_default_pos">
	<label><input type="radio"  value="top-left" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_pos]' ); ?>"  <?php checked('top-left',$settings['lang_switcher']['tx_ls_pos'],1) ?> /> Top Left </label> &nbsp
	<label><input type="radio"  value="top-right" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_pos]' ); ?>"  <?php checked('top-right',$settings['lang_switcher']['tx_ls_pos'],1) ?> /> Top Right </label> &nbsp
	<label><input type="radio"  value="bottom-left" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_pos]' ); ?>"  <?php checked('bottom-left',$settings['lang_switcher']['tx_ls_pos'],1) ?> /> Bottom Left </label> &nbsp
	<label><input type="radio"  value="bottom-right" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_pos]' ); ?>"  <?php checked('bottom-right',$settings['lang_switcher']['tx_ls_pos'],1) ?> /> Bottom Right </label>
   </div>
    <p><label><input type="checkbox" class="tx_switcher_pos" value="custom" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_pos]' ); ?>"  <?php checked('custom',$settings['lang_switcher']['tx_ls_pos'],1) ?> /> Custom </label></p>
   <div class="tx_custom_pos">
   <input type="text" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_pos_id]' ); ?>" value= "<?php echo $settings['lang_switcher']['tx_ls_pos_id'] ?>" /> 
   <p class="description">The element id to place a select element Switcher Menu .</p>
   </div>
<?php endif; ?>

<?php if ( 'txwt_switcher-color' == $field['label_for'] ) : ?>
   <p>
   
   	<label><input type="checkbox" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][lx_ls_customizer]' ); ?>"  <?php checked(1,$settings['lang_switcher']['lx_ls_customizer'],1) ?> /> Enable </label>

   </p>
	<div class="txwt-color-picker"><label>Accent</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_color][accent]' ); ?>"  value="<?php echo $settings['lang_switcher']['tx_ls_color']['accent'] ?>" /> </div>
	<div class="txwt-color-picker"><label>Text</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_color][text]' ); ?>"  value="<?php echo $settings['lang_switcher']['tx_ls_color']['text'] ?>" />  </div>
	<div class="txwt-color-picker"><label>Background</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_color][bg]' ); ?>"  value="<?php echo $settings['lang_switcher']['tx_ls_color']['bg'] ?>" /> </div>
	<div class="txwt-color-picker"><label>Menu </label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_color][menu]' ); ?>"  value="<?php echo $settings['lang_switcher']['tx_ls_color']['menu'] ?>" />  </div>
	<div class="txwt-color-picker"><label>Languages</label><input type="text" class="colorpick" name="<?php esc_attr_e( 'txwt_ls_stgs[lang_switcher][tx_ls_color][langs]'); ?>"  value="<?php echo $settings['lang_switcher']['tx_ls_color']['langs'] ?>" /> </div>

<?php endif; ?>