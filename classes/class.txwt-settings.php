<?php

class TXWT_Plugin_Settings {

	private static $instance = null;
	private static $settings;
	public static $pages;
	protected static $default_settings;

	/**
	 * Construct me
	 */
	public function __construct() {
		self::$default_settings = self::get_default_settings();
		self::$settings = self::get_settings();
		// register the checkbox
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// register admin pages for the plugin
		add_action( 'admin_menu', array( $this, 'txwt_admin_pages_callback' ) );
		add_action( 'admin_menu', array( $this, 'add_meta_to_pages' ) );
	}

	/**
	 * Provides access to a single instance of a class using the singleton pattern
	 * @return object
	 */
	public static function get_instance() {
		$module = get_called_class();

		if ( !isset( self::$instance ) ) {
			self::$instance = new $module();
		}

		return self::$instance;
	}

	public function admin_init() {
		$this->register_settings();
	}

	/**
	 * 
	 * Callback for registering settings pages
	 * 
	 * This demo registers a custom page for the plugin and a subpage
	 *  
	 */
	public static function txwt_admin_pages_callback() {
		$icon_url = TXWT_URL.'/images/tx-small-logo.png';
		self::$pages[ ] = add_menu_page( __( "Transifex", 'txwt' ), __( "Transifex", 'txwt' ), 'manage_options', 'txwt-plugin', __CLASS__ . '::txwt_plugin_pages', $icon_url );
		self::$pages[ ] = add_submenu_page( 'txwt-plugin', __( "Switcher", 'txwt' ), __( "Switcher", 'txwt' ), 'manage_options', 'txwt-plugin_switcher', __CLASS__ . '::txwt_plugin_pages' );
	}

	public function add_meta_to_pages() {
		foreach ( self::$pages as $page ) {
			add_action( 'load-' . $page, array( $this, 'admin_metaboxes' ) ); //Load page structure
		}
	}

	/**
	 * 
	 * The content of the base page
	 * 
	 */
	public static function txwt_plugin_pages() {
		global $current_screen;
		$active_tab = array( 'db' => 0, 'ls' => 0, 'bu' => 0, 'hp' => 0 );
		switch ( $current_screen->id ) {
			case 'toplevel_page_txwt-plugin':
				$active_tab[ 'db' ] = 1;
				break;
			case 'transifex_page_txwt-plugin_switcher':
				$active_tab[ 'ls' ] = 1;
				break;
			default:
				break;
		}

		$active_tab = apply_filters( 'txwt-active_tab', $active_tab );
		echo TXWT_Base::render_template( TXT_VIEWS . 'stgs-page.php', array( 'settings' => self::$settings, 'screen' => $current_screen->id, 'active_tab' => $active_tab ), 'always' );
	}

	public static function get_settings( $db=false ) {
		if ( !$db && isset( self::$settings ) ) {
			return self::$settings;
		}
		$ls_settings = get_option( 'txwt_ls_stgs', array( ) );
		$tx_settings = get_option( 'txwt_tx_stgs', array( ) );
		$settings = array_merge( (array) $tx_settings, (array) $ls_settings );
		$settings = txwt_merge_atts( self::$default_settings, $settings );

		return $settings;
	}

	/**
	 * Establishes initial values for all settings
	 * @return array
	 */
	protected static function get_default_settings() {
		global $locale;

		$general = array(
			'tx_api_key' => '', //Transifex API Key
			'staging_server' => 0, // prepare your translations on a staging/test server before taking them live to a production server.
			'detect_dynamic_strgs' => 1, // detect  HTML snippets rendered from javascript templates or AJAX calls 
			'autocollect' => 1
		);
		$translation = array(
			'ignore_tags' => array( ), //set of tags that are ignored from string detection
			'ignore_class' => array( ), //exclude DOM elements and their children  by these classes
			'parse_atts' => array( ) //custom attributes that must be automatically translated for the whole website.
		);

		$lang_switcher = array(
			'show_if_translated' => 1, // show language switcher if translation exists
			'lang_url_format' => 1, // how the lang parameter is added to the url
			'alt_lang_availability' => 0, //indicate in post when there is an alternative language available
			'alt_lang_position' => 'below', // where the alternative languge list should be placed 
			'alt_lang_style' => 0,
			'alt_lang_availability_text' => __( 'This article is also available in:', 'txwt' ),
			'show_footer_selector' => 1,
			'language_order' => array( ),
			'elements' => array( 'flag' => 1, 'name' => 1, 'code' => 0, 'sep' => '/' ),
			'txwt_ls_theme' => 0, // use a custom languae switcher theme
			'txwt_ls_custom_css' => '', //custom css for the language switcher
			'use_custom_flags' => 0,
			'custom_flag_url' => '', //use custom flags for switcher
			'custom_flag_ext' => 'png',
			'lswitcher_type' => 0,
			'tx_ls_pos' => 'top-left',
			'tx_ls_pos_id' => '',
			'tx_ls_color' => array( 'accent' => '#006F9F', 'text' => '#FFFFFF', 'bg' => '#000000', 'menu' => '#EAF1F7', 'langs' => '#666666' ),
			'lx_ls_customizer' => 0,
			'custom_theme' => array( 'menu_text' => '#FFFFFF', 'bg' => '#2A5D84', 'drop_text' => '#444444', 'drop_bg' => '#FFFFFF', 'hover_bg' => '#F1F1F1', 'bb' => '#EEEEEE' )
		);

		return array(
			'general' => $general,
			'translation' => $translation,
			'langs' => array( strtolower( substr( $locale, 0, 2 ) ) => array( 'name' => txwt_format_code_lang( $locale ), 'code' => strtolower( substr( $locale, 0, 2 ) ), 'source' => true ) ),
			'lang_switcher' => $lang_switcher,
			'db-version' => TXWT_VERSION,
		);
	}

	public function admin_metaboxes() {
		global $current_screen;
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
		add_meta_box(
		'txwt_seo_meta', 'TRASIFEX LIVE SEO FOR WORDPRESS', array( $this, 'display_meta_boxes' ), $current_screen->id, 'normal', 'high'
		);
	}

	public function display_meta_boxes() {
		?>

		<div style="text-align: center;"><img alt="Transifex SEO plugin" src="<?php echo TXWT_URL . '/images/transifex-seo.png' ?>" style="WIDTH: 100PX;"></div><p>Transifex Live SEO is a complete solution to enable indexing of your transifex live generated content. The plugins follows <a href="https://developers.google.com/webmasters/ajax-crawling/docs/getting-started?csw=1" target="_blank"><b>Search Engine standards for optimising ajax content.</b></a>  Multilingual SEO requirements are done without interfering with your main stream SEO plugin</p><p style="font-weight: bold; font-size: 1em;"><a href="http://plugins.zanto.org/?p=242" target="_blank" class="button">Read More &gt;&gt;</a></p>
		<p style="display: block; text-align: center; font-size: 1.2em; background: #ebebeb; padding: .5em;">MULTILINGUAL SEO</p><a href="http://plugins.zanto.org/?p=242" class="button-primary" target="_blank">Get Transifex Live SEO</a>

		<?php
	}

	/**
	 * Setup the settings
	 * 
	 * Add a single checkbox setting for Active/Inactive and a text field 
	 * just for the sake of our demo
	 * 
	 */
	public function register_settings() {
		register_setting( 'txwt_tx_stgs', 'txwt_tx_stgs', array( $this, 'validate_stgs' ) );
		register_setting( 'txwt_ls_stgs', 'txwt_ls_stgs', array( $this, 'validate_ls_stgs' ) );


		// general section 
		add_settings_section(
		'transifex_gen', // ID used to identify this section and with which to register options
  __( "", 'txwt' ), // Title to be displayed on the administration page
	__CLASS__        . '::markup_section_headers', // Callback used to render the description of the section
	'txwt-plugin'                           // Page on which to add this section of options
		);


		add_settings_field(
		'tx_api_key', // ID used to identify the field throughout the plugin
  __( "Api Key: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin', // The page on which this option will be displayed
	'transifex_gen', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_api-key' )
		);

		add_settings_field(
		'autocollect', // ID used to identify the field throughout the plugin
  __( "Autocollect Strings: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin', // The page on which this option will be displayed
	'transifex_gen', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_autocollect' )
		);

		add_settings_field(
		'detect_dynamic_strgs', // ID used to identify the field throughout the plugin
  __( "Detect Dynamic Strings: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin', // The page on which this option will be displayed
	'transifex_gen', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_detect-dynamic-strgs' )
		);

		add_settings_field(
		'staging_server', // ID used to identify the field throughout the plugin
  __( "Staging Server Mode: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin', // The page on which this option will be displayed
	'transifex_gen', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_staging-server' )
		);


		// Transifex Translation Section
		add_settings_section(
		'translation', // ID used to identify this section and with which to register options
  __( "", 'txwt' ), // Title to be displayed on the administration page
	__CLASS__        . '::markup_section_headers', // Callback used to render the description of the section
	'txwt-plugin'                           // Page on which to add this section of options
		);
		add_settings_field(
		'ignore_tags', // ID used to identify the field throughout the plugin
  __( "Ignore Tags: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin', // The page on which this option will be displayed
	'translation', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_ignore-tags' )
		);

		add_settings_field(
		'ignore_class', // ID used to identify the field throughout the plugin
  __( "Ignore Classes: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin', // The page on which this option will be displayed
	'translation', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_ignore-class' )
		);

		add_settings_field(
		'parse_atts', // ID used to identify the field throughout the plugin
  __( "Parse Attributes: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin', // The page on which this option will be displayed
	'translation', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_parse-atts' )
		);


		// Switcher Section
		add_settings_section(
		'switcher_sec', // ID used to identify this section and with which to register options
  __( "", 'txwt' ), // Title to be displayed on the administration page
	__CLASS__        . '::markup_section_headers', // Callback used to render the description of the section
	'txwt-plugin_switcher'                           // Page on which to add this section of options
		);

		add_settings_field(
		'lswitcher_type', // ID used to identify the field 
  __( "Lang Switcher Type: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_sec', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_lswitcher-type' )
		);

		// Switcher Sub Section
		add_settings_section(
		'switcher_wp', // ID used to identify this section and with which to register options
  __( "", 'txwt' ), // Title to be displayed on the administration page
	__CLASS__        . '::markup_section_headers', // Callback used to render the description of the section
	'txwt-plugin_switcher'                           // Page on which to add this section of options
		);
		add_settings_field(
		'language_order', // ID used to identify the field 
  __( "Language Order : ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_wp', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_language-order' )
		);

		add_settings_field(
		'txwt_ls_theme', // ID used to identify the field 
  __( "Language Switcher Theme: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_wp', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_ls-theme' )
		);

		add_settings_field(
		'lang_switcher_elements', // ID used to identify the field 
  __( "Switcher Elements : ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_wp', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_ls-elements' )
		);

		add_settings_field(
		'lang_url_format', // ID used to identify the field 
  __( "Lang Switcher URL format: ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_wp', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_lang-url-format' )
		);

		add_settings_field(
		'alt_lang_availability', // ID used to identify the field 
  __( "Show post translation links : ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_wp', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_alt-lang-availability' )
		);

		add_settings_field(
		'show_footer_selector', // ID used to identify the field 
  __( "Footer Language Switcher : ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_wp', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_show-footer-selector' )
		);

		add_settings_field(
		'show_footer_selector', // ID used to identify the field 
  __( "Footer Language Switcher : ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_wp', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_show-footer-selector' )
		);


		add_settings_field(
		'use_custom_flags', // ID used to identify the field 
  __( "Custom Flags : ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_wp', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_use-custom-flags' )
		);
		add_settings_field(
		'txwt_ls_custom_css', // ID used to identify the field 
  __( "Custom CSS : ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_wp', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_ls-custom-css' )
		);
		// Transifex Default Switcher Section
		add_settings_section(
		'switcher_tx', // ID used to identify this section and with which to register options
  __( "", 'txwt' ), // Title to be displayed on the administration page
	__CLASS__        . '::markup_section_headers', // Callback used to render the description of the section
	'txwt-plugin_switcher'                           // Page on which to add this section of options
		);

		add_settings_field(
		'switcher_pos', // ID used to identify the field 
  __( "Switcher Position : ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_tx', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_switcher-pos' )
		);

		add_settings_field(
		'switcher_color', // ID used to identify the field 
  __( "Color Scheme : ", 'txwt' ), // The label to the left of the option interface element
	array( $this, 'markup_fields' ), // The name of the function responsible for rendering the option interface
	'txwt-plugin_switcher', // The page on which this option will be displayed
	'switcher_tx', // The name of the section to which this field belongs
	array( 'label_for' => 'txwt_switcher-color' )
		);
	}

	/**
	 * Adds the section introduction text to the Settings page
	 *
	 * @mvc Controller
	 *
	 * @param array $section
	 */
	public static function markup_section_headers( $section ) {
		$tx_switcher = self::$settings[ 'lang_switcher' ][ 'lswitcher_type' ];
		echo TXWT_Base::render_template( TXT_VIEWS . 'txwt-settings/page-settings-section-headers.php', array( 'section' => $section, 'tx_switcher' => $tx_switcher ), 'always' );
	}

	/**
	 * Delivers the markup for settings fields
	 *
	 * @mvc Controller
	 *
	 * @param array $field
	 */
	public function markup_fields( $field ) {
		switch ( $field[ 'label_for' ] ) {
			case 'wpps_field-example1':
				// Do any extra processing here
				break;
		}

		echo TXWT_Base::render_template( TXT_VIEWS . 'txwt-settings/page-settings-fields.php', array( 'settings' => self::$settings, 'field' => $field ), 'always' );
	}

	/**
	 * Validate Settings
	 * 
	 * Filter the submitted data as per your request and return the array
	 * 
	 * @param array $input
	 */
	public function validate_stgs( $input ) {
		//general section
		//sanitize_key might be a better option if we are sure there are no caps in the key
		$input[ 'general' ][ 'tx_api_key' ] = (isset( $input[ 'general' ][ 'tx_api_key' ] )) ? sanitize_text_field( $input[ 'general' ][ 'tx_api_key' ] ) : '';
		$input[ 'general' ][ 'detect_dynamic_strgs' ] = (isset( $input[ 'general' ][ 'detect_dynamic_strgs' ] )) ? 1 : 0;
		$input[ 'general' ][ 'autocollect' ] = (isset( $input[ 'general' ][ 'autocollect' ] )) ? 1 : 0;
		$input[ 'general' ][ 'staging_server' ] = (isset( $input[ 'general' ][ 'staging_server' ] )) ? 1 : 0;

		//translation section
		$input[ 'translation' ][ 'ignore_tags' ] = $this->sanitize_array( $input[ 'translation' ][ 'ignore_tags' ] );
		$input[ 'translation' ][ 'ignore_class' ] = $this->sanitize_array( $input[ 'translation' ][ 'ignore_class' ] );
		$input[ 'translation' ][ 'parse_atts' ] = $this->sanitize_array( $input[ 'translation' ][ 'parse_atts' ] );
		return $input;
	}

	public function validate_ls_stgs( $input ) {
		global $wp_rewrite;

		$default = self::$default_settings[ 'lang_switcher' ];
		$imput_ls = $input[ 'lang_switcher' ]; //shorten the imput by only working with the language switcher array index.
		if ( isset( $imput_ls[ 'lswitcher_type' ] ) ) {
			$val = absint( $imput_ls[ 'lswitcher_type' ] );
			$imput_ls[ 'lswitcher_type' ] = (in_array( $val, array( 0, 1 ) )) ? $val : $default[ 'lswitcher_type' ];
		}

		if ( isset( $imput_ls[ 'language_order' ] ) ) {
			$order_array = explode( ',', $imput_ls[ 'language_order' ] );
			$imput_ls[ 'language_order' ] = $this->sanitize_lang_order( $order_array );
		}

		if ( isset( $imput_ls[ 'lang_url_format' ] ) ) {
			$val = absint( $imput_ls[ 'lang_url_format' ] );
			if ( !$wp_rewrite->using_permalinks() && $val == 0 ) {
				$err_msg = __( 'The permalink scructure does not support language url format chosen. Resorted to default', 'txwt' );
				add_settings_error( 'txwt_ls_stgs', 'lang_url_format', $err_msg, 'error' );
				$imput_ls[ 'lang_url_format' ] = $default[ 'lang_url_format' ];
				;
			} elseif ( $val == 2 && !txwt_allow_subdomain_install() ) {
				$err_msg = __( 'Subdomain langs may not be fully compatible with custom wp-content directories.', 'txwt' );
				add_settings_error( 'txwt_ls_stgs', 'lang_url_format', $err_msg, 'error' );
				$imput_ls[ 'lang_url_format' ] = $default[ 'lang_url_format' ];
			} else {
				$imput_ls[ 'lang_url_format' ] = (in_array( $val, array( 0, 1, 2 ) )) ? $val : $default[ 'lang_url_format' ];
			}
		} else {
			$imput_ls[ 'lang_url_format' ] = $default[ 'lang_url_format' ];
		}

		$imput_ls[ 'alt_lang_availability' ] = isset( $imput_ls[ 'alt_lang_availability' ] ) ? 1 : 0;

		if ( isset( $imput_ls[ 'alt_lang_position' ] ) ) {
			$val = absint( $imput_ls[ 'alt_lang_position' ] );
			$imput_ls[ 'alt_lang_position' ] = (in_array( $val, array( 0, 1 ) )) ? $val : $default[ 'alt_lang_position' ];
		} else {
			$imput_ls[ 'alt_lang_position' ] = $default[ 'alt_lang_position' ];
		}

		if ( isset( $imput_ls[ 'alt_lang_style' ] ) ) {
			$val = absint( $imput_ls[ 'alt_lang_style' ] );
			$imput_ls[ 'alt_lang_style' ] = (in_array( $val, array( 0, 1 ) )) ? $val : $default[ 'alt_lang_style' ];
		} else {
			$imput_ls[ 'alt_lang_style' ] = $default[ 'alt_lang_style' ];
		}

		if ( isset( $imput_ls[ 'alt_lang_availability_text' ] ) && !empty( $imput_ls[ 'alt_lang_availability_text' ] ) ) {
			sanitize_text_field( $imput_ls[ 'alt_lang_availability_text' ] );
		} else {
			$err_msg = __( 'Invalid alternative language text, resorted to default', 'txwt' );
			add_settings_error( 'txwt_ls_stgs', 'alt_lang_availability_text', $err_msg, 'error' );
			$imput_ls[ 'alt_lang_availability_text' ] = $default[ 'alt_lang_availability_text' ];
		}

		$imput_ls[ 'show_footer_selector' ] = (isset( $imput_ls[ 'show_footer_selector' ] )) ? 1 : 0;
		$imput_ls[ 'elements' ][ 'flag' ] = (isset( $imput_ls[ 'elements' ][ 'flag' ] )) ? 1 : 0;
		$imput_ls[ 'elements' ][ 'name' ] = (isset( $imput_ls[ 'elements' ][ 'name' ] )) ? 1 : 0;
		$imput_ls[ 'elements' ][ 'code' ] = (isset( $imput_ls[ 'elements' ][ 'code' ] )) ? 1 : 0;

		if ( isset( $imput_ls[ 'elements' ][ 'sep' ] ) ) {
			$imput_ls[ 'elements' ][ 'sep' ] = in_array( $imput_ls[ 'elements' ][ 'sep' ], array( '', '/', '-', ',' ) ) ? $imput_ls[ 'elements' ][ 'sep' ] : $default[ 'elements' ][ 'sep' ];
		} else {
			$imput_ls[ 'elements' ][ 'sep' ] = $default[ 'elements' ][ 'sep' ];
		}

		$imput_ls[ 'txwt_ls_custom_css' ] = (isset( $imput_ls[ 'txwt_ls_custom_css' ] )) ? sanitize_text_field( $imput_ls[ 'txwt_ls_custom_css' ] ) : '';
		$imput_ls[ 'txwt_ls_theme' ] = (in_array( $imput_ls[ 'txwt_ls_theme' ], array( 0, 1, 2 ) )) ? $imput_ls[ 'txwt_ls_theme' ] : $default[ 'txwt_ls_theme' ];
		$imput_ls[ 'use_custom_flags' ] = (isset( $imput_ls[ 'use_custom_flags' ] )) ? 1 : 0;
		$imput_ls[ 'custom_flag_url' ] = (isset( $imput_ls[ 'custom_flag_url' ] )) ? sanitize_text_field( $imput_ls[ 'custom_flag_url' ] ) : '';

		if ( isset( $imput_ls[ 'custom_flag_ext' ] ) && in_array( $imput_ls[ 'custom_flag_ext' ], array( 'png', 'gif', 'jpg' ) ) ) {
			; //look the other way
		} else {
			$imput_ls[ 'custom_flag_ext' ] = $default[ 'custom_flag_ext' ];
		}
		$imput_ls[ 'tx_ls_pos' ] = (in_array( $imput_ls[ 'tx_ls_pos' ], array( 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'custom' ) )) ? $imput_ls[ 'tx_ls_pos' ] : $default[ 'tx_ls_pos' ];
		$imput_ls[ 'tx_ls_pos_id' ] = sanitize_html_class( $imput_ls[ 'tx_ls_pos_id' ] );
		$imput_ls[ 'lx_ls_customizer' ] = (isset( $imput_ls[ 'lx_ls_customizer' ] )) ? 1 : 0;
		$imput_ls[ 'tx_ls_color' ][ 'accent' ] = $this->sanitize_hex_color( $imput_ls[ 'tx_ls_color' ][ 'accent' ] );
		$imput_ls[ 'tx_ls_color' ][ 'text' ] = $this->sanitize_hex_color( $imput_ls[ 'tx_ls_color' ][ 'text' ] );
		$imput_ls[ 'tx_ls_color' ][ 'bg' ] = $this->sanitize_hex_color( $imput_ls[ 'tx_ls_color' ][ 'bg' ] );
		$imput_ls[ 'tx_ls_color' ][ 'menu' ] = $this->sanitize_hex_color( $imput_ls[ 'tx_ls_color' ][ 'menu' ] );
		$imput_ls[ 'tx_ls_color' ][ 'langs' ] = $this->sanitize_hex_color( $imput_ls[ 'tx_ls_color' ][ 'langs' ] );

		$imput_ls[ 'custom_theme' ][ 'text' ] = $this->sanitize_hex_color( $imput_ls[ 'custom_theme' ][ 'menu_text' ] );
		$imput_ls[ 'custom_theme' ][ 'bg' ] = $this->sanitize_hex_color( $imput_ls[ 'custom_theme' ][ 'bg' ] );
		$imput_ls[ 'custom_theme' ][ 'drop_text' ] = $this->sanitize_hex_color( $imput_ls[ 'custom_theme' ][ 'drop_text' ] );
		$imput_ls[ 'custom_theme' ][ 'drop_bg' ] = $this->sanitize_hex_color( $imput_ls[ 'custom_theme' ][ 'drop_bg' ] );
		$imput_ls[ 'custom_theme' ][ 'hover_bg' ] = $this->sanitize_hex_color( $imput_ls[ 'custom_theme' ][ 'hover_bg' ] );
		$imput_ls[ 'custom_theme' ][ 'bb' ] = $this->sanitize_hex_color( $imput_ls[ 'custom_theme' ][ 'bb' ] );

		return array( 'lang_switcher' => $imput_ls );
	}

	function sanitize_array( $arr ) {
		if ( !is_array( $arr ) ) {
			$arr = explode( ',', $arr );
		}

		if ( empty( $arr ) ) {
			return array( );
		}

		for ( $i = 0; $i < count( $arr ); $i++ ) {
			$arr[ $i ] = sanitize_html_class( $arr[ $i ] );
		}

		return array_filter( $arr );
	}

	public function sanitize_lang_order( $order ) {
		if ( is_array( $order ) && !empty( $order ) ) {
			foreach ( $order as $key => $lang_code ) {
				$order[ $key ] = sanitize_key( $lang_code );
			}
			return $order;
		} else {
			return array( );
		}
	}

	function sanitize_hex_color( $color ) {

		if ( '' === $color )
			return '';

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) )
			return $color;

		return null;
	}

}