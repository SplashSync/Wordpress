<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

// phpcs:disable PSR1.Files.SideEffects

// Add this plugin to WordPress for activation so it can be tested.
//$GLOBALS['wp_tests_options'] = array(
//    'active_plugins' => array( "splash-connector/splash.php", "woocommerce/woocommerce.php" ),
//);



/** Setup WordPress environment for Remote Actions */
//define('DOING_CRON'    , True );
define('WP_ADMIN', true);
define('SPLASH_SERVER_MODE', true);

ini_set("memory_limit", "-1");

//include(dirname(__DIR__).'/vendor/autoload.php');

\Splash\Client\Splash::local()->includes();

include( ABSPATH . 'wp-admin/includes/post.php' );
include( ABSPATH . 'wp-admin/includes/user.php' );
include( ABSPATH . 'wp-admin/includes/image.php' );

//include(dirname(dirname(dirname(dirname(__DIR__)))).'/wp-load.php');

//if (false == getenv('WP_TESTS_DIR')) {
//    putenv('WP_TESTS_DIR=../../../../tests/phpunit');
//}

// If the wordpress-tests repo location has been customized (and specified
// with WP_TESTS_DIR), use that location. This will most commonly be the case
// when configured for use with Travis CI.

// Otherwise, we'll just assume that this plugin is installed in the WordPress
// SVN external checkout configured in the wordpress-tests repo.

//====================================================================//
// Setup Php Specific Settings
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set("display_errors", "1");

//$_SERVER["REQUEST_METHOD"] = "";

//if (false !== getenv('WP_DEVELOP_DIR')) {
//    require getenv('WP_DEVELOP_DIR').'/tests/phpunit/includes/bootstrap.php';
//} else {
//    require dirname(dirname(dirname(dirname(dirname(__DIR__))))).'/tests/phpunit/includes/bootstrap.php';
//}


//====================================================================//
// Setup Splash Module
//update_option("splash_ws_id", "12345678");
//update_option("splash_ws_key", "001234567800");
//update_option("splash_ws_user", "1");
//update_option("splash_multilang", "off");

//====================================================================//
// Setup WooCommerce Module
update_option("woocommerce_currency", "EUR");

//====================================================================//
// Setup Wp Multilang Module
$wpm_languages = array(
    'en' => array(
        'enable' => 1,
        'locale' => 'en_US',
        'name' => 'English (US)',
        'translation' => 'en_US',
        'date' => '',
        'time' => '',
        'flag' => 'us.png',
    ),
    'fr' => array(
        'enable' => 1,
        'locale' => 'fr_FR',
        'name' => 'Français',
        'translation' => 'fr_FR',
        'date' => '',
        'time' => '',
        'flag' => 'fr.png',
    ),
);

update_option("wpm_site_language", "en");
update_option("wpm_use_redirect", "no");
update_option("wpm_use_prefix", "no");
update_option("wpm_show_untranslated_strings", "yes");
update_option("wpm_uninstall_translations", "no");
//update_option("wpm_version", "2.2.5");
//update_option("wpm_db_version", "2.2.5");
update_option("wpm_languages", $wpm_languages);

//====================================================================//
// Create Product Categories
wp_insert_term("Category A", "product_cat");
wp_insert_term("Category B", "product_cat");
wp_insert_term("Category C", "product_cat");
wp_insert_term("Category D", "product_cat");

//====================================================================//
// Splash Module & Dependencies Autoloader
if (!defined("SPL_PROTOCOL")) {
    require_once(dirname(__DIR__)."/vendor/splash/phpcore/inc/Splash.Inc.php");
}
