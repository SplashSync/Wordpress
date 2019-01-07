<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

if (! defined('ABSPATH')) {
    exit;
}

define("SPLASH_SYNC_VERSION", "1.5.0");

// Load plugin class files
require_once('includes/class-splash-wordpress-plugin.php');
require_once('includes/class-splash-wordpress-settings.php');

// Load plugin libraries
require_once('includes/lib/class-wordpress-plugin-template-admin-api.php');

//====================================================================//
// Splash Module & Dependecies Autoloader
require_once(__DIR__ . "/vendor/autoload.php");

/**
 * Returns the main instance of WordPress_Plugin_Template to prevent the need to use globals.
 *
 * @since  1.0.0
 *
 * @return object WordPress_Plugin_Template
 */
function Splash_Plugin()
{
    $instance = Splash_Wordpress_Plugin::instance(__FILE__, SPLASH_SYNC_VERSION);
    $instance->settings = Splash_Wordpress_Settings::instance($instance);

    return $instance;
}

Splash_Plugin();
