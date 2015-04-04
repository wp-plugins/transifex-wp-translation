<?php

/**
 * 
 * The plugin base class - the root of all WP goods!
 * 
 * @Mucunguzi Ayebare
 *
 */
class TXWT_Base {

	/**
	 * 
	 * Assign everything as a call from within the constructor
	 */
	private $settings;
	private $active_lang;
	private $source_lang;
	protected static $readableProperties = array( 'active_lang', 'settings', 'source_lang' );  // These should really be constants, but PHP doesn't allow class constants to be arrays

	public function __construct() {
		$this->registerHookCallbacks();
	}

	/**
	 * Public getter for protected variables
	 * @param string $variable
	 * @return mixed
	 */
	public function __get( $variable ) {
		if ( in_array( $variable, self::$readableProperties ) )
			return $this->$variable;
		else
			throw new Exception( __METHOD__ . " error: $" . $variable . " doesn't exist or isn't readable." );
	}

	public function registerHookCallbacks() {

		// Add earlier execution as it needs to occur before admin page display
		add_action( 'init', array( $this, 'init' ), 5 );
		// Translation-ready
		add_action( 'plugins_loaded', array( $this, 'txwt_add_textdomain' ) );

		if ( !is_admin() ) {
			add_action( 'wp_head', array( $this, 'meta_generator_tag' ) );
			add_filter( 'option_rewrite_rules', array( $this, 'rewrite_rules_filter' ) );
			add_filter( 'parse_request', array( $this, 'add_lang_var' ) );
		} else {
			add_action( 'wp_ajax_txwt_store_langs', array( $this, 'txwt_store_langs' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'txwt_add_admin_JS' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'txwt_add_admin_CSS' ) );
		}
	}

	public function init() {
		$this->register_settings();
		wp_register_script( 'transifex.js', '//cdn.transifex.com/live.js', array( ), '1.0', false ); //used both in the back and front end. enqueued only where its needed.

		if ( !is_admin() ) {
			$this->activate_lang_switcher();

			add_action( 'wp_head', array( $this, 'transifex_live_script' ), 50 );
			add_action( 'wp_enqueue_scripts', array( $this, 'txwt_front_JS' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'txwt_add_CSS' ) );
		}

		$active_lang = $this->get_default_language();
		$lang_array = $this->settings[ 'langs' ];
		$s = isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] == 'on' ? 's' : '';
		$request = 'http' . $s . '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
		$home = get_option( 'home' );
		if ( $s ) {
			$home = preg_replace( '#^http://#', 'https://', $home );
		}
		$url_parts = parse_url( $home );
		$blog_path = !empty( $url_parts[ 'path' ] ) ? $url_parts[ 'path' ] : '';

		switch ( $this->settings[ 'lang_switcher' ][ 'lang_url_format' ] ) {
			case 0:
				$path = str_replace( $home, '', $request );

				$parts = explode( '?', $path );
				$path = $parts[ 0 ];
				$exp = explode( '/', trim( $path, '/' ) );

				if ( array_key_exists( $exp[ 0 ], $lang_array ) ) {
					$active_lang = $exp[ 0 ];
				}

				break;
			case 1:
				if ( isset( $_GET[ 'lang' ] ) && array_key_exists( $_GET[ 'lang' ], $lang_array ) ) {
					$active_lang = $_GET[ 'lang' ];
				}
				break;

			case 2:
				$site_url = get_site_url();
				$domain_arr = explode( '.', $site_url, 2 );
				if ( array_key_exists( $domain_arr[ 0 ], $lang_array ) ) {
					$active_lang = $domain_arr[ 0 ];
				}
				break;
			default:
				break;
		}
		$this->active_lang = $active_lang;
	}

	/**
	 * 
	 * Adding JavaScript scripts
	 * 
	 * Loading existing scripts from wp-includes or adding custom ones
	 * 
	 */
	public function txwt_front_JS() {
		// load custom JSes and put them in footer
		$switcher_position = $this->settings[ 'lang_switcher' ][ 'tx_ls_pos' ];

		$script_params = array(
			'api_key' => $this->settings[ 'general' ][ 'tx_api_key' ],
			'picker_pos' => ($switcher_position == 'custom') ? $this->settings[ 'lang_switcher' ][ 'tx_ls_pos_id' ] : $switcher_position,
			'dynamic' => $this->settings[ 'general' ][ 'detect_dynamic_strgs' ],
			'autocollect' => $this->settings[ 'general' ][ 'autocollect' ],
			'staging' => $this->settings[ 'general' ][ 'staging_server' ],
			'parse_attr' => $this->settings[ 'translation' ][ 'parse_atts' ],
			'ignore_tags' => $this->settings[ 'translation' ][ 'ignore_tags' ],
			'ignore_class' => $this->settings[ 'translation' ][ 'ignore_class' ]
		);

		wp_register_script( 'txwt_front.js', TXWT_URL . '/js/transifex.js', array( 'jquery', 'transifex.js' ), '1.0', false );
		wp_enqueue_script( 'txwt_front.js' );
		wp_localize_script( 'txwt_front.js', 'TXWT', $script_params );
	}

	/**
	 *
	 * Adding JavaScript scripts for the admin pages only
	 *
	 * Loading existing scripts from wp-includes or adding custom ones
	 *
	 */
	public function txwt_add_admin_JS( $hook_suffix ) {
		if ( strpos( $hook_suffix, 'txwt-plugin' ) ) {
			$script_params = array(
				'flag_dir' => str_replace( content_url(), '', get_stylesheet_directory_uri() ) . '/flags/',
				'api_key' => $this->settings[ 'general' ][ 'tx_api_key' ],
				'sudomain_langs' => (txwt_allow_subdomain_install()) ? 1 : 0,
				'empty_key' => __( 'Please provide an API Key before you can fetch languages', 'txwt' ),
				'save_key' => __( 'Please Save the API Key / Page settings before you can fetch languages', 'txwt' )
			);

			wp_enqueue_script( 'txwt-admin', TXWT_URL . '/js/admin-stgs.js', array( 'jquery-ui-sortable', 'wp-color-picker', 'transifex.js' ), '1.0', false );
			wp_localize_script( 'transifex.js', 'TXWT', $script_params );
		}
	}

	/**
	 * 
	 * Add CSS styles
	 * 
	 */
	public function txwt_add_CSS() {
		if ( !defined( 'TXWT_NO_CSS' ) ) {
			wp_register_style( 'txwt-switcher', TXWT_URL . '/css/lang-switcher.css', array( ), '1.0', 'screen' );
			wp_enqueue_style( 'txwt-switcher' );
		}
	}

	/**
	 *
	 * Add admin CSS styles - available only on admin
	 *
	 */
	public function txwt_add_admin_CSS( $hook ) {

		if ( strpos( $hook, 'txwt' ) ) {
			wp_register_style( 'txwt_help_page', TXWT_URL . '/css/help-page.css' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'txwt_help_page' );
		}
	}

	public function transifex_live_script() {
		?> 
		<script type="text/javascript">
			window.liveSettings = {
				api_key: TXWT.api_key,
		<?php if ( $this->settings[ 'lang_switcher' ][ 'lswitcher_type' ] == 1 ): ?>
					picker: TXWT.picker_pos,
		<?php endif; ?>
				detectlang: true,
				dynamic: TXWT.dynamic,
				autocollect: TXWT.autocollect,
				staging: TXWT.staging,
				parse_attr: TXWT.parse_attr,
				ignore_tags: TXWT.ignore_tags,
				ignore_class: TXWT.ignore_class
			};
		</script>

		<?php
	}

	/**
	 * Initialize the Settings class
	 * 
	 * Register a settings section with a field for a secure WordPress admin option creation.
	 * 
	 */
	public function register_settings() {
		global $TXWT_Plugin_Settings;
		require_once( TXWT_PATH . '/classes/class.txwt-settings.php' );
		$TXWT_Plugin_Settings = TXWT_Plugin_Settings::get_instance();
		$this->settings = TXWT_Plugin_Settings::get_settings();
		$this->source_lang = $this->get_source_lang( $this->settings[ 'langs' ] );
	}

	public function get_default_language() {
		return 'en';
	}

	public function get_source_lang( $langs ) {
		global $locale;
		if ( !empty( $langs ) ) {
			foreach ( $langs as $lang ) {
				if ( isset( $lang[ 'source' ] ) )
					return $lang[ 'code' ];
			}
		}
		return strtolower( substr( $locale, 0, 2 ) );
	}

	public function activate_lang_switcher() {
		global $TXWT_Lang_Switcher;
		require_once( TXWT_PATH . '/classes/class.txwt-lang-switcher.php' );
		$TXWT_Lang_Switcher = new TXWT_Lang_Switcher();
	}

	/**
	 * Add textdomain for plugin
	 */
	public function txwt_add_textdomain() {
		load_plugin_textdomain( 'txwt', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	public function meta_generator_tag() {
		printf( '<meta name="generator" content="Transifex WP Translation ver:%s " />' . PHP_EOL, TXWT_VERSION );
	}

	/**
	 * Callback for saving  AJAX option
	 */
	public function txwt_store_langs() {
		check_ajax_referer( 'txwt-fetch-langs', '_ajxwpnonce' );
		if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'txwt_store_langs' ) {
			if ( is_array( $_POST[ 'langs' ] ) && !empty( $_POST[ 'langs' ] ) ) {
				$langs = array( );
				$setup_langs = '';
				foreach ( $_POST[ 'langs' ] as $lang_details ) {
					if ( !isset( $lang_details[ 'name' ] ) || !isset( $lang_details[ 'code' ] ) ) {
						echo 'err_2';
						die();
					} else {
						$langs[ $lang_details[ 'code' ] ] = $lang_details;
						$setup_langs.= $lang_details[ 'name' ] . '(' . $lang_details[ 'code' ] . ') &nbsp;';
					}
				}
				echo $setup_langs;
			} else {
				echo 'err_1';
				die();
			}
		}
		$tx_settings = get_option( 'txwt_tx_stgs' );
		$tx_settings[ 'langs' ] = $langs;
		update_option( 'txwt_tx_stgs', $tx_settings );
		die();
	}

//@todo add lang negotiation type filter
	function rewrite_rules_filter( $value ) {
		$val_add = array( );
		$code = $this->active_lang;
		foreach ( (array) $value as $k => $v ) {
			$val_add[ $code . '/' . $k ] = $v;
		}
		$val_add[ $code . '/?$' ] = 'index.php';
		$value = array_merge( $val_add, $value );
		return $value;
	}

//@todo add lang negotiation type filter
	function add_lang_var( $query ) {
		$query->query_vars[ 'twt_lang' ] = $this->active_lang;
	}

	/**
	 * Render a template
	 *
	 * Allows parent/child themes to override the markup by placing the a file named basename( $default_template_path ) in their root folder,
	 * and also allows plugins or themes to override the markup by a filter. Themes might prefer that method if they place their templates
	 * in sub-directories to avoid cluttering the root folder. In both cases, the theme/plugin will have access to the variables so they can
	 * fully customize the output.
	 *
	 * @mvc @model
	 *
	 * @param  string $default_template_path The path to the template, relative to the plugin's `views` folder
	 * @param  array  $variables             An array of variables to pass into the template's scope, indexed with the variable name so that it can be extract()-ed
	 * @param  string $require               'once' to use require_once() | 'always' to use require()
	 * @return string
	 */
	public static function render_template( $template_path = false, $variables = array( ), $require = 'once' ) {
		do_action( 'tfwt_render_template_pre', $template_path, $variables );

		$template_path = apply_filters( 'tfwt_template_path', $template_path );

		if ( is_file( $template_path ) ) {
			extract( $variables );
			ob_start();

			if ( 'always' == $require ) {
				require( $template_path );
			} else {
				require_once( $template_path );
			}

			$template_content = apply_filters( 'tfwt_template_content', ob_get_clean(), $template_path, $variables );
		} else {
			$template_content = '';
		}

		do_action( 'tfwt_render_template_post', $template_path, $variables, $template_content );
		return $template_content;
	}

	/**
	 * Register activation hook
	 *
	 */
	function activate() {
		// do something on activation
	}

	/**
	 * Register deactivation hook
	 *
	 */
	function deactivate() {
		// do something when deactivated
	}

}