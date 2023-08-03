<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

/**
 * Plugin Name: Splash Connector
 * Version: 2.0.6
 * Plugin URI: https://github.com/SplashSync/Wordpress
 * Description: Splash Sync Wordpress plugin.
 * Author: Splash Sync
 * Author URI: http://www.splashsync.com
 * Requires at least: 6.0
 * Tested up to: 6.2
 *
 * Text Domain: wordpress-plugin-template
 * Domain Path: /lang/
 *
 * @package WordPress
 *
 * @author Splash Sync
 *
 * @since 0.0.1
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable PSR1.Files.SideEffects

if (! defined('ABSPATH')) {
    exit;
}

define("SPLASH_SYNC_VERSION", "2.0.6");

// Load plugin class files
require_once('includes/class-splash-wordpress-plugin.php');
require_once('includes/class-splash-wordpress-settings.php');

// Load plugin libraries
require_once('includes/lib/class-wordpress-plugin-template-admin-api.php');

//====================================================================//
// Splash Module & Dependencies Autoloader
require_once(__DIR__."/vendor/autoload.php");

/**
 * Returns the main instance of WordPress_Plugin_Template to prevent the need to use globals.
 *
 * @since  1.0.0
 *
 * @return \Splash_Wordpress_Plugin
 */
function Splash_Plugin()
{
    $instance = \Splash_Wordpress_Plugin::instance(__FILE__, SPLASH_SYNC_VERSION);
    $instance->settings = \Splash_Wordpress_Settings::instance($instance);

    return $instance;
}

Splash_Plugin();
