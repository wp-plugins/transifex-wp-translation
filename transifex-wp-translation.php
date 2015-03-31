<?php
/**
 * Plugin Name: Transifex WP Translation
 * Plugin URI: http://zanto.org/
 * Description: Translate WordPress sites directly on the page using Transifex Live localization tools.
 * Author: Mucunguzi Ayebare Brooks
 * Author URI: http://zanto.org/
 * Version: 0.2
 * Text Domain: txwt
 * License: GPL2


 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 2, as
 published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Get some constants ready for paths when your plugin grows 
 * 
 */

define( 'TXWT_VERSION', '0.1' );
define( 'TXWT_PATH', dirname( __FILE__ ) );
define ('TXT_VIEWS', TXWT_PATH.'/views/');
define( 'TXWT_PATH_INCLUDES', dirname( __FILE__ ) . '/inc' );
define( 'TXWT_FOLDER', basename( TXWT_PATH ) );
define( 'TXWT_URL', plugins_url() . '/' . TXWT_FOLDER );
define( 'TXWT_URL_INCLUDES', TXWT_URL . '/inc' );

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

define( 'TXWT_NAME', 'Transifex WP Translation' );
define( 'TXWT_REQUIRED_PHP_VERSION', '5.3' );                          // because of get_called_class()
define( 'TXWT_REQUIRED_WP_VERSION',  '3.1' );                          // because of esc_textarea()

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function txwt_requirements_met() {
	global $wp_version;
	//require_once( ABSPATH . '/wp-admin/includes/plugin.php' );		// to get is_plugin_active() early

	if ( version_compare( PHP_VERSION, TXWT_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, TXWT_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	/*
	if ( ! is_plugin_active( 'plugin-directory/plugin-file.php' ) ) {
		return false;
	}
	*/

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function txwt_requirements_error() {
	global $wp_version;

	require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( txwt_requirements_met() ) {
    require_once( __DIR__ . '/inc/functions.php' );
	require_once( __DIR__ . '/classes/class.txwt-base.php' );
	require_once( __DIR__ . '/classes/class.txwt-switcher-widgets.php');

	if ( class_exists( 'TXWT_Base' ) ) {
		$GLOBALS['TXWT'] = new TXWT_Base();
		register_activation_hook(   __FILE__, array( $GLOBALS['TXWT'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['TXWT'], 'deactivate' ) );
	}
	
} else {
	add_action( 'admin_notices', 'txwt_requirements_error' );
}

function TXWT(){
return $GLOBALS['TXWT'];
}
