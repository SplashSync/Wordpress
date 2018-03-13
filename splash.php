<?php
/*
 * Plugin Name: Splash Connector
 * Version: 1.3.1
 * Plugin URI: https://github.com/SplashSync/Wordpress
 * Description: Splash Sync Wordpress plugin.
 * Author: Splash Sync
 * Author URI: http://www.splashsync.com
 * Requires at least: 4.0
 * Tested up to: 4.9
 *
 * Text Domain: wordpress-plugin-template
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Splash Sync
 * @since 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define("SPLASH_SYNC_VERSION", "1.3.1");

// Load plugin class files
require_once( 'includes/class-splash-wordpress-plugin.php' );
require_once( 'includes/class-splash-wordpress-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-wordpress-plugin-template-admin-api.php' );

//====================================================================//
// Splash Module & Dependecies Autoloader
require_once( __DIR__ . "/vendor/autoload.php");


/**
 * Returns the main instance of WordPress_Plugin_Template to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WordPress_Plugin_Template
 */
function Splash_Plugin () {
	$instance = Splash_Wordpress_Plugin::instance( __FILE__, SPLASH_SYNC_VERSION );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Splash_Wordpress_Settings::instance( $instance );
	}

	return $instance;
}

Splash_Plugin();