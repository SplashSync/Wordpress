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

// phpcs:disable PSR1.Files.SideEffects

define('WP_ADMIN', true);
define('SPLASH_SERVER_MODE', true);

ini_set("memory_limit", "-1");

include(dirname(__DIR__, 4).'/wp-load.php');

//====================================================================//
// Setup WooCommerce Module
update_option("woocommerce_currency", "EUR");

//====================================================================//
// Setup Wp Multi-lang Module
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
require_once(ABSPATH.'wp-admin/includes/plugin.php');
require_once(ABSPATH.'wp-admin/includes/post.php');
require_once(ABSPATH.'wp-admin/includes/user.php');
require_once(ABSPATH.'wp-admin/includes/image.php');
require_once(dirname(__DIR__).'/includes/class-splash-wordpress-plugin.php');
require_once(dirname(__DIR__).'/includes/class-splash-wordpress-settings.php');
