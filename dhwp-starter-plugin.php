<?php
/*
Plugin Name: Digital Hill Starter Plugin
Plugin URI: https://github.com/aarondigitalhill/starterplugin
Description: A plugin starting point for plugin development and testing
Version: 1.0.12
Author: Digital Hill Multimedia
Author URI: http://www.digitalhill.com
License: Proprietary
Text Domain: dhwp-starter-plugin
*/



//include functions and classes
foreach(glob(plugin_dir_path(__FILE__).'includes/*.php') as $f) include_once($f);

//include widgets
foreach(glob(plugin_dir_path(__FILE__).'includes/widgets/*.php') as $w) include_once($w);

//include shortcodes
foreach(glob(plugin_dir_path(__FILE__).'includes/shortcodes/*.php') as $s) include_once($s);

DevLog::add('Logging works');

/**
 * Add settings fields
 */
function dhwp_starter_settings_init() {

	register_setting( 'dhwp_starter_settings', 'dhwp_starter_settings' );

	add_settings_section(
		'dhwp_starter_section',
		'',
		'',
		'dhwp_starter_settings'
	);

	add_settings_field(
		'dhwp_starter_field',
		__( 'Example Field Data', 'dhwp-starter-plugin' ),
		'dhwp_starter_field_render',
		'dhwp_starter_settings',
		'dhwp_starter_section'
	);
}
add_action( 'admin_init', 'dhwp_starter_settings_init' );

/**
 * Renders the field in the options form
 */
function dhwp_starter_field_render() {
	$field = 'dhwp_starter_field';
	$value = dhwp_starter_get_settings($field);
	echo "<input type='text' name='dhwp_starter_settings[{$field}]' value='{$value}' />";
}

/**
 * Makes it quicker to get the settings and lets you determine defaults
 * @param mixed $setting Leave blank to return all settings in an array; include a field name to return that field
 * @return mixed
 */
function dhwp_starter_get_settings($setting=''){
	$defaults = array(
		'dhwp_starter_field'=>'Default data',
		);
	$settings = get_option( 'dhwp_starter_settings', $defaults);
	if(array_key_exists($setting,$settings)){
		return $settings[$setting];
	} elseif($setting<>'') {
		return false;
	} else {
		return $settings;
	}
}


/**
 * Add CMS menu items
 */
function dhwp_add_menu(){
	//see https://developer.wordpress.org/resource/dashicons/ for icon options
	add_menu_page('DHWP Starter', 'DHWP Starter', 'manage_options', __FILE__, 'dhwp_render_home', 'dashicons-welcome-widgets-menus',7);
	add_submenu_page( __FILE__, 'Starter Settings', 'Starter Settings', 'manage_options', __FILE__.'/settings', 'dhwp_render_settings' );

}
add_action( 'admin_menu', 'dhwp_add_menu' );


/**
 * Enqueue admin scripts and styles for CMS.
 */
function dhwp_admin_enqueue_scripts() {
	//wp_enqueue_style( 'dhwp-starter-plugin-css', plugins_url('assets/css/cms-style.css',__FILE__),array(),'1.0.0' );
	//wp_enqueue_script( 'dhwp-starter-plugin-js', plugins_url('assets/js/cms-script.js',__FILE__),array(),'1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'dhwp_admin_enqueue_scripts' );


/**
 * Enqueue front-end scripts and styles
 */
function dhwp_enqueue_scripts() {
	//wp_enqueue_style( 'dhwp-starter-plugin-css', plugins_url('assets/css/style.css',__FILE__),array(),'1.0.0' );
	//wp_enqueue_script( 'dhwp-starter-plugin-js', plugins_url('assets/js/script.js',__FILE__),array(),'1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'dhwp_enqueue_scripts' );



/**
 * Manage specific pages (see menu items above)
 */
function dhwp_render_home(){
	include(plugin_dir_path(__FILE__).'dhwp-admin.php');
}

/**
 * Manage specific pages (see menu items above)
 */
function dhwp_render_settings(){
	include(plugin_dir_path(__FILE__).'dhwp-settings.php');
}

if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/class-dhwp-license-admin.php';
    new DHWP_License_Admin();
}

require_once __DIR__ . '/includes/plugin-update-checker/plugin-update-checker.php';

// Build update checker instance
$updateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://github.com/aarondigitalhill/starterplugin/', // GitHub repo URL
    __FILE__, // Main plugin file
    'dhwp-starter-plugin' // Plugin slug
);

// Enable release assets with specific pattern
$updateChecker->getVcsApi()->enableReleaseAssets('/starterplugin.*\.zip$/');