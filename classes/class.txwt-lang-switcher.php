<?php
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__) {
    die('Access denied.');
}

if (!class_exists('TXWT_Lang_Switcher')) {

    /**
     * Definition of the language switcher calss

     */
    class TXWT_Lang_Switcher {

        private $wp_query;
        protected $ls_settings;

        /**
         * Constructor        
         */
        function __construct() {
            global $txwt_site_obj;
            $this->get_settings();
            $this->registerHookCallbacks();
        }

        /**
         * Register callbacks for actions and filters
         */
        public function registerHookCallbacks() {

            if (!is_admin()) {
                if ($this->ls_settings['lswitcher_type'] == 0) {
                    if ($this->ls_settings['show_footer_selector']) {
                        add_action('wp_footer', array($this, 'footer_lang_selector'), 19);
                        add_action('txwt_footer_lang_switcher', array($this, 'hor_language_selector'));
                    }

                    if (!empty($this->ls_settings['alt_lang_availability'])) {
                        add_filter('the_content', array($this, 'post_availability'), 100);
                    }
                    add_action('wp_head', array($this, 'custom_switcher_styles'), 20);
                    add_action('drop_down_switcher', array($this, 'drop_down_switcher'));
                    add_action('hor_switcher', array($this, 'hor_language_selector'));
                } else {
                    add_action('wp_head', array($this, 'transifex_switcher_styles'), 20);
                }
            }
        }

        public function get_settings() {

            $settings = TXWT_Plugin_Settings::get_settings();
            $this->ls_settings = $settings['lang_switcher'];
        }

        public function custom_switcher_styles() {
            $colors = array();
            switch ($this->ls_settings['txwt_ls_theme']) {
                case 0:
                    $colors['menu_text'] = $this->ls_settings['custom_theme']['menu_text'];
                    $colors['bg'] = $this->ls_settings['custom_theme']['bg'];
                    $colors['drop_text'] = $this->ls_settings['custom_theme']['drop_text'];
                    $colors['drop_bg'] = $this->ls_settings['custom_theme']['drop_bg'];
                    $colors['hover_bg'] = $this->ls_settings['custom_theme']['hover_bg'];
                    $colors['bb'] = $this->ls_settings['custom_theme']['bb'];
                    break;

                case 1:
                    $colors['menu_text'] = '#FFFFFF';
                    $colors['bg'] = '#4EB2C9';
                    $colors['drop_text'] = '#444444';
                    $colors['drop_bg'] = '#FFFFFF';
                    $colors['hover_bg'] = '#F1F1F1';
                    $colors['bb'] = '#EEEEEE';
                    break;

                case 2:
                    $colors['menu_text'] = '#FFFFFF';
                    $colors['bg'] = '#000000';
                    $colors['drop_text'] = '#FFFFFF';
                    $colors['drop_bg'] = '#111111';
                    $colors['hover_bg'] = '#333333';
                    $colors['bb'] = '#202020';
                    break;
            }

            $css = <<<CSS
			/* Lang Switcher Theme CSS */
			.txt_dd_switcher a.has-dropdown, .txt_dd_switcher li:hover a.has-dropdown {
                 background-color:  {$colors['bg']};
                 color: {$colors['menu_text']};
            }
			.txt_dd_switcher li:hover ul li a:hover {
                  background-color: {$colors['hover_bg']};				  
            }
			.txt_dd_switcher li:hover a {
                background-color: {$colors['drop_bg']};
				color:{$colors['drop_text']};
            }
			.txt_dd_switcher ul li {
                border-bottom: 1px solid {$colors['bb']};
            }
CSS;
            echo "\r\n<style type=\"text/css\">\r\n";
            echo $css;
            echo "\r\n</style>";
            if (!empty($this->ls_settings['txwt_ls_custom_css'])) {
                echo "\r\n<style type=\"text/css\">\r\n";
                echo "/*Custom Language Switcher CSS*/";
                echo $this->ls_settings['txwt_ls_custom_css'];
                echo "\r\n</style>";
            }
        }

        public function transifex_switcher_styles() {
            $colors = array();
            if ($this->ls_settings['lx_ls_customizer']) { // Transifex Default Switcher styles
                $colors['bg'] = $this->ls_settings['tx_ls_color']['bg'];
                $colors['text'] = $this->ls_settings['tx_ls_color']['text'];
                $colors['accent'] = $this->ls_settings['tx_ls_color']['accent'];
                $colors['menu'] = $this->ls_settings['tx_ls_color']['menu'];
                $colors['langs'] = $this->ls_settings['tx_ls_color']['langs'];

                $css = <<<CSS
				.txlive-langselector {
				      background-color: {$colors['bg']} !important;
					  opacity:0.75;
				      color: {$colors['text']}; !important;
			     }
			    .txlive-langselector .txlive-langselector-toggle {
				border-color: {$colors['accent']};!important;
			    }
			    .txlive-langselector-bottomright .txlive-langselector-marker, .txlive-langselector-bottomleft .txlive-langselector-marker {
				     border-bottom-color:{$colors['text']}; !important;
			    }
			    .txlive-langselector-topright .txlive-langselector-marker, .txlive-langselector-topleft .txlive-langselector-marker {
			     	border-top-color: {$colors['text']}; !important;
			    }
			   .txlive-langselector-list {
			    	background-color: {$colors['menu']}; !important;
			    	border-color:{255, 255, 255, 0.5 )}; !important;
			    	color: {$colors['langs']} !important;
			    }
			   .txlive-langselector-list > li:hover {
			    	background-color: rgba( 0, 0, 0, 0.2 ) !important;
			    }
CSS;

                echo "\r\n<style type=\"text/css\">\r\n";
                echo $css;
                echo "\r\n</style>";
            }
        }

        public function drop_down_switcher() {
            $lang_data = $this->get_current_ls();
            $active = $GLOBALS['TXWT']->active_lang;
            $show_flag = $this->ls_settings['elements']['flag'];
            $show_name = $this->ls_settings['elements']['name'];
            $show_code = $this->ls_settings['elements']['code'];
            ?>
            <ul class="txt_dd_switcher">

                <li>
                    <?php
                    $lang_label = '';
                    if ($show_name) {
                        $lang_label = $lang_data[$active]['name'];
                        if ($show_code) {
                            $lang_label.=' (' . strtoupper($active) . ')';
                        }
                    } elseif ($show_code) {
                        $lang_label = strtoupper($active);
                    } elseif (!$show_flag) { // show name if no element of the language switcher was selected
                        $lang_label = $lang_data[$active]['name'];
                    }
                    ?>
                    <a  class="has-dropdown" href="<?php echo $lang_data[$active]['url'] ?>" title="<?php echo $lang_data[$active]['name'] ?>"> <?php echo ($show_flag) ? txwt_get_flag($active) : '' ?> <?php echo $lang_label ?></a>
                    <ul>
                        <?php foreach ($lang_data as $lang_code => $lang_details): ?>
                            <?php $lang_label = '' ?>
                            <?php if ($lang_code == $active)
                                continue; ?>
                            <?php
                            if ($show_name) {
                                $lang_label = $lang_details['name'];
                                if ($show_code) {
                                    $lang_label.=' (' . strtoupper($lang_code) . ')';
                                }
                            } elseif ($show_code) {
                                $lang_label = strtoupper($lang_code);
                            } elseif (!show_flag) {
                                $lang_label = $lang_details['name'];
                            }
                            ?>

                            <li><a href="<?php echo $lang_details['url'] ?>"> <?php echo ($show_flag) ? txwt_get_flag($lang_code) : '' ?>  <?php echo $lang_label ?> </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>


            </ul>

            <?php
        }

        function hor_language_selector() {

            $show_flag = $this->ls_settings['elements']['flag'];
            $show_name = $this->ls_settings['elements']['name'];
            $show_code = $this->ls_settings['elements']['code'];

            $trans_urls = $this->get_current_ls('skip_missing=0');
            if (!empty($trans_urls)) {
                // This is used in display of the footer Language Switcher
                ?>
                <?php $last = end($trans_urls); reset($trans_urls)?>
                <?php foreach ($trans_urls as $lang_code => $lang_details) { ?>
                    <?php
                    $lang_label = '';
                    if ($show_name) {
                        $lang_label = $lang_details['name'];
                        if ($show_code) {
                            $lang_label.=' (' . strtoupper($lang_code) . ')';
                        }
                    } elseif ($show_code) {
                        $lang_label = strtoupper($lang_code);
                    } elseif (!show_flag) {
                        $lang_label = $lang_details['name'];
                    }
                    ?>

                    <?php $active = ($GLOBALS['TXWT']->active_lang == $lang_code) ? true : false; ?>

                    <a rel="alternate" hreflang="<?php echo $lang_code ?>" 
                       href="<?php echo $lang_details['url'] ?>" 
                       class="<?php echo $active ? 'lang_sel_active' : 'lang_sel'; ?>">
                           <?php echo ($show_flag) ? txwt_get_flag($lang_code) : '', ' ', $lang_label ?>
                    </a>        
                    <?php echo ($last['name'] != $lang_details['name'])? txwt_ls_seperator($this->ls_settings['elements']['sep']):''; ?>						

                <?php } ?>


                <?php
            }
        }

        function footer_lang_selector() {
            ?>
            <div id="lang_sel_footer">
                <?php do_action('txwt_footer_lang_switcher'); ?>
            </div>
            <?php
        }

        //$args['skip_missing']
        public function get_current_ls($args=array()) {
            global $wp;

            $defaults = array('skip_missing' => 0);
            $args = wp_parse_args($args, $defaults);
            $current_url = home_url();
            $txwt_ls_array = array();
            if (!$wp->did_permalink) {
                // There could be plugin specific params on the URL, so we need the whole query string
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $query_string = $_SERVER['QUERY_STRING'];
                    $current_url = home_url('?' . $query_string);
                }
            } else {
                $current_url = home_url(add_query_arg(array(), $wp->request));
            }
            $languages = txwt_order_langs($this->ls_settings['language_order'], txwt_get_languages());

            foreach ($languages as $lang_code => $lang_details) {
                $txwt_ls_array[$lang_code] = array('name' => $lang_details['name'], 'url' => $this->add_url_lang($current_url, $lang_code));
            }

            return apply_filters('txwt_ls_array', $txwt_ls_array, $args);
        }

        // Adds the language code to the url.
        function add_url_lang($url, $code) {
            global $blog_id;
            $url_format = $this->ls_settings['lang_url_format'];
            $abshome = preg_replace('@\?lang=' . $code . '@i', '', home_url());

            switch ($url_format) {
                case 0:
                    if (0 === strpos($url, 'https://')) {
                        $abshome = preg_replace('#^http://#', 'https://', $abshome);
                    }
                    if ($abshome == $url)
                        $url .= '/';
                    if (0 !== strpos($url, $abshome . '/' . $code . '/')) {
                        // only replace if it is there already
                        $url = str_replace($abshome, $abshome . '/' . $code, $url);
                    }
                    break;

                case 1:
                    // remove any previous value.
                    $url_array = txwt_decompose_url($url);
                    unset($url_array['query']);
                    unset($url_array['queryvars']['lang']);
                    $url = txwt_regenerate_url($url_array);

                    if (false === strpos($url, '?')) {
                        $url_glue = '?';
                    } else {

                        // special case post preview link
                        $db = debug_backtrace();
                        if (is_admin() && (@$db[6]['function'] == 'post_preview')) {
                            $url_glue = '&';
                        } elseif (isset($_POST['comment']) || defined('zwt_DOING_REDIRECT')) { // will be used for a redirect
                            $url_glue = '&';
                        } else {
                            $url_glue = '&amp;';
                        }
                    }
                    $url .= $url_glue . 'lang=' . $code;
                    break;
					
                case '2':
					$domain = preg_replace( '|https?://|', '', get_option( 'siteurl' ) );
	                if ( $slash = strpos( $domain, '/' ) )
		                $domain = substr( $domain, 0, $slash );
					$url = str_replace($domain, $code.'.'.$domain, $url);   	
                    break;
				
                default:
                    return $url;
            }
            return $url;
        }

        function post_availability($content) {

            $lang_urls = $this->get_current_ls();

            if (!empty($lang_urls)) {
                $active_lang = $GLOBALS['TXWT']->active_lang;
                $trans_urls = '';
                foreach ($lang_urls as $lang_code => $lang_details) {
                    if ($lang_code == $active_lang) {
                        continue;
                    }
                    $trans_urls.='<a href="' . $lang_details['url'] . '">' . $lang_details['name'] . '</a>&nbsp;';
                }
                $alt_lang_style = $this->ls_settings['alt_lang_style'];

                $out = '<div class="' . ((!$alt_lang_style) ? 'txwt_post_langs' : 'txwt_post_langs_plain') . '"><span>' . $this->ls_settings['alt_lang_availability_text'] . ' </span><span>' . $trans_urls . '</span></div>';
                $out = apply_filters('txwt_post_alternative_languages', $out);

                if ($this->ls_settings['alt_lang_position'] == 1) {
                    $content = $out . $content;
                } else {
                    $content = $content . $out;
                }
            }
            return $content;
        }

    }

    // end TXWT_Lang_Switcher class
}
?>