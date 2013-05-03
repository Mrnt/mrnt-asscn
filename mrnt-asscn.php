<?php
/**
Plugin Name:	Maurent Association
Plugin URI:		http://maurent.com/
Description:	Define roles/titles for organization members and support additional fields in the user profile.
Version:		0.1.5
Author:			Maurent Software ${date.year}
Author URI:		http://maurent.com
*/

/**
 * Plugin to support creation of member roles and allocating them to users.
 * This plugin is best used with a membership plugin such as PaidMemberships Pro.
 * 
 * ****NB**** 
 * Requires PHP >= 5.3.0
 * @package Maurent Association
 */


define('MRNT_ASSCN_PATH', dirname(__FILE__));
define('MRNT_ASSCN_URI', plugins_url('', __FILE__));
define('MRNT_ASSCN_DB_VERSION', 'mrnt_asscn_db_version');
define('MRNT_ASSCN_DB_VERSNUM', '1.0');
define('MRNT_ASSCN_ADMIN_NOTICE', 'mrnt_asscn_deferred_admin_notices');

/**
 * Store instances of our controllers so that we only create them once and
 * reuse them
 */
$MrntAsscnObjects = array();

if (is_admin()) {

	require(MRNT_ASSCN_PATH.'/helper/debug.php');
	
	add_action( 'admin_notices', 'mrnt_asscn_display_admin_notices');
	
	/**
	 * Install/uninstall
	 */

	// detect if plugin is being updated
	function mrnt_asscn_loaded() {
		if (get_site_option( MRNT_ASSCN_DB_VERSION ) != MRNT_ASSCN_DB_VERSNUM) {
			dispatch(true, 'MrntAsscnInstall', 'activate', null);
		}
	}
	add_action( 'plugins_loaded', 'mrnt_asscn_loaded');
	// activating plugin
	register_activation_hook(__FILE__, function(){return dispatch(true, 'MrntAsscnInstall', 'activate', func_get_args());});

	function mrnt_asscn_uninstall() {
		require(MRNT_ASSCN_PATH.'/controller/admin/mrnt-asscn-install.php');
		$c = new MrntAsscnInstall();
		$c->uninstall();
	}
	// unlike other hooks this cannot accept an anonymous function
	register_uninstall_hook(__FILE__, 'mrnt_asscn_uninstall');

	/**
	 * Config page
	 * Needs to be hooked in early because we are adding a menu item
	 */
	add_action( 'init', function(){return dispatch(true, 'MrntAsscnConfig', null, func_get_args());});

	/**
	 * Handlers for the user admin forms
	 */
	// add our fields to new user form
	add_action( 'admin_head', function(){return dispatch(true, 'MrntAsscnUser', 'add_user', func_get_args());});
	// add our fields to this or other user edit page
	add_action( 'show_user_profile', function(){return dispatch(true, 'MrntAsscnUser', 'edit_user', func_get_args());});
	add_action( 'edit_user_profile', function(){return dispatch(true, 'MrntAsscnUser', 'edit_user', func_get_args());});
	add_action( 'user_contactmethods', function(){return dispatch(true, 'MrntAsscnUser', 'edit_user_contactmethods', func_get_args());}, 1, 1);
	// error check the fields
	add_action( 'user_profile_update_errors', function(){return dispatch(true, 'MrntAsscnUser', 'validate_user', func_get_args());}, 10, 3);
	// allow our fields to be saved
	add_action( 'user_register', function(){return dispatch(true, 'MrntAsscnUser', 'save_user', func_get_args());}, 10, 1);
	add_action( 'personal_options_update', function(){return dispatch(true, 'MrntAsscnUser', 'save_user', func_get_args());}, 10, 1);
	add_action( 'edit_user_profile_update', function(){return dispatch(true, 'MrntAsscnUser', 'save_user', func_get_args());});
	
} else {

	/**
	 * Shortcode support
	 * Hook in at the top because we also provide styling
	 */
	add_action( 'init', function(){return dispatch(false, 'MrntAsscnShortcode', null, func_get_args());} );
}

/**
 * Create an instance of a controller when an action/filter callback is called and 
 * pass off the callback and its arguments to the appropriate method 
 * @param String $class		: our class name
 * @param String $method	: class method
 * @param array $args		: variable number of arguments from callback
 * @return mixed
 */
function dispatch($admin, $controller, $method, &$args) {
	global $MrntAsscnObjects;

	if (!isset($MrntAsscnObjects[$controller])) {
		$file = strtolower(preg_replace("/([a-z]+)([A-Z])/", "$1-$2", $controller)).".php";
		require(MRNT_ASSCN_PATH."/controller/".($admin?'admin/':'').$file);
		if ($method) {
			$object = new $controller();
		} else {
			$object = new $controller($args);
		}
		$MrntAsscnObjects[$controller] = $object;
	} else {
		$object = $MrntAsscnObjects[$controller];
	}
	if ($method) {
		return call_user_func_array(array($object,$method), $args);
	}
}
