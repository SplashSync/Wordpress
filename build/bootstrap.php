<?php
/**
 * Bootstrap the plugin unit testing environment. Customize 'active_plugins'
 * setting below to point to your main plugin file.
 *
 * Requires WordPress Unit Tests (http://unit-test.svn.wordpress.org/trunk/).
 *
 * @package wordpress-plugin-tests
 */

// Add this plugin to WordPress for activation so it can be tested.
$GLOBALS['wp_tests_options'] = array(
//    'active_plugins' => array( "splash-connector/splash.php" ),
	'active_plugins' => array( "splash-connector/splash.php", "woocommerce/woocommerce.php" ),
);


/** Setup WordPress environment for Remote Actions */
//define( 'DOING_CRON'    , True );
define( 'WP_ADMIN'    , True );

if( false == getenv( 'WP_TESTS_DIR' )) {
    putenv( 'WP_TESTS_DIR=../../../../tests/phpunit');
}

// If the wordpress-tests repo location has been customized (and specified
// with WP_TESTS_DIR), use that location. This will most commonly be the case
// when configured for use with Travis CI.

// Otherwise, we'll just assume that this plugin is installed in the WordPress
// SVN external checkout configured in the wordpress-tests repo.

if( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
    require getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/bootstrap.php';
} else {
    require '../../../../tests/phpunit/includes/bootstrap.php';
}

//====================================================================//
// Setup Php Specific Settings
error_reporting(E_ERROR);    

//====================================================================//
// Setup Splash Module
update_option("splash_ws_id", "12345678");
update_option("splash_ws_key", "001234567800");
update_option("splash_ws_user", "1");
update_option("splash_multilang", "off");
