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

namespace Splash\Local;

use ArrayObject;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Core\PluginManger;
use Splash\Local\Objects\Core\MultilangTrait as Multilang;
use Splash\Models\LocalClassInterface;

/**
 * Splash Local Core Management Class fro WordPress
 */
class Local implements LocalClassInterface
{
    use PluginManger;
    
    //====================================================================//
    // *******************************************************************//
    //  MANDATORY CORE MODULE LOCAL FUNCTIONS
    // *******************************************************************//
    //====================================================================//
    
    /**
     * {@inheritdoc}
     */
    public function parameters()
    {
        $Parameters       =     array();

        //====================================================================//
        // Server Identification Parameters
        $Parameters["WsIdentifier"]         =   get_option("splash_ws_id", null);
        $Parameters["WsEncryptionKey"]      =   get_option("splash_ws_key", null);
        
        //====================================================================//
        // If Expert Mode => Allow Overide of Server Host Address
        if ((get_option("splash_advanced_mode", false)) && !empty(get_option("splash_server_url", null))) {
            $Parameters["WsHost"]           =   get_option("splash_server_url", null);
        }
        //====================================================================//
        // If Expert Mode => Allow Overide of Communication Protocol
        if ((get_option("splash_advanced_mode", false)) && !empty(get_option("splash_ws_protocol", null))) {
            $Parameters["WsMethod"]         =   get_option("splash_ws_protocol", "NuSOAP");
        }
        
        //====================================================================//
        // Multisites Mode => Overide Soap Host & Path
        if (is_multisite()) {
            $BlogDetails    =   get_blog_details();
            $Parameters["ServerHost"]         =   $BlogDetails->domain;
            $Parameters["ServerPath"]         =   $BlogDetails->path;
            $Parameters["ServerPath"]        .=   "wp-content/plugins/splash-connector/vendor/splash/phpcore/soap.php";
        }
        
        return $Parameters;
    }
    
    /**
     * {@inheritdoc}
     */
    public function includes()
    {
        //====================================================================//
        // When Library is called in server mode ONLY
        //====================================================================//
        if (!empty(SPLASH_SERVER_MODE) && !defined('DOING_CRON')) {
            /** Setup WordPress environment for Remote Actions */
            define('DOING_CRON', true);
            /** Include the bootstrap for setting up WordPress environment */
            include(dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php');
            /** Remote Automatic login */
            wp_set_current_user(get_option("splash_ws_user", null));
            //====================================================================//
        // When Library is called in client mode ONLY
        //====================================================================//
        }
        // NOTHING TO DO
        
        //====================================================================//
        // When Library is called in both clinet & server mode
        //====================================================================//

        // NOTHING TO DO
        
        return true;
    }
           
    /**
     * {@inheritdoc}
     */
    public function selfTest()
    {
        //====================================================================//
        //  Load Local Translation File
        Splash::translator()->load("ws");
        Splash::translator()->load("main@local");
        
        //====================================================================//
        //  Verify - Server Identifier Given
        if (empty(get_option("splash_ws_id", null))) {
            return Splash::log()->err("ErrSelfTestNoWsId");
        }
                
        //====================================================================//
        //  Verify - Server Encrypt Key Given
        if (empty(get_option("splash_ws_key", null))) {
            return Splash::log()->err("ErrSelfTestNoWsKey");
        }
        
        //====================================================================//
        //  Verify - User Selected
        if (empty(get_option("splash_ws_user", null))) {
            return Splash::log()->err("ErrSelfTestNoUser");
        }
        
        if (is_wp_error(get_user_by("ID", get_option("splash_ws_user", null)))) {
            return Splash::log()->war("ErrSelfTestNoUser");
        }
        
        /**
         * Check if WooCommerce is active
         */
        if (self::hasWooCommerce()) {
            //====================================================================//
            //  Verify - Prices Exclude Tax Warning
            if (wc_prices_include_tax()) {
                Splash::log()->war(
                    "You selected to store Products Prices Including Tax. "
                        . "It is highly recommanded to store Product Price without Tax to work with Splash."
                );
            }
        }
                
        //====================================================================//
        // Debug Mode => Display Host & Path Infos
        if (defined('WP_DEBUG') && !empty(WP_DEBUG)) {
            Splash::log()->war("Current Server Url : " . Splash::ws()->getServerInfos()["ServerHost"]);
            Splash::log()->war("Current Server Path: " . Splash::ws()->getServerInfos()["ServerPath"]);
        }
        
        Splash::log()->msg("MsgSelfTestOk");

        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function informations($Informations)
    {
        //====================================================================//
        // Init Response Object
        $Response = $Informations;

        //====================================================================//
        // Company Informations
        $Response->company          =   get_option("blogname", "...");
        $Response->address          =   "N/A";
        $Response->zip              =   " ";
        $Response->town             =   " ";
        $Response->country          =   " ";
        
        if (is_multisite()) {
            $BlogDetails            =   get_blog_details();
            $Response->www          =   $BlogDetails->home;
        } else {
            $Response->www          =   get_option("home", "...");
        }
        $Response->email            =   get_option("admin_email", "...");
        $Response->phone            =   " ";
        
        //====================================================================//
        // Server Logo & Images
        $RawIcoPath                 =   get_attached_file(get_option('site_icon'));
        if (!empty($RawIcoPath)) {
            $Response->icoraw           =   Splash::file()->readFileContents($RawIcoPath);
        } else {
            $Response->icoraw           =   Splash::file()->readFileContents(
                dirname(dirname(dirname(dirname(__DIR__)))) . "/wp-admin/images/w-logo-blue.png"
            );
        }
        $Response->logourl          =   get_site_icon_url();
        
        //====================================================================//
        // Server Informations
        if (is_multisite()) {
            $BlogDetails                =   get_blog_details();
            $Response->servertype       =   "Wordpress (Multisites)";
            $Response->serverurl        =   $BlogDetails->siteurl;
        } else {
            $Response->servertype       =   "Wordpress";
            $Response->serverurl        =   get_option("siteurl", "...");
        }
        
        $Response->moduleversion        =   SPLASH_SYNC_VERSION;
        
        return $Response;
    }
    
    //====================================================================//
    // *******************************************************************//
    //  OPTIONNAl CORE MODULE LOCAL FUNCTIONS
    // *******************************************************************//
    //====================================================================//
    
    /**
     * {@inheritdoc}
     */
    public function testParameters()
    {
        //====================================================================//
        // Init Parameters Array
        $Parameters       =     array();

        //====================================================================//
        // Urls Must have Http::// prefix
        $Parameters["Url_Prefix"]   = "http://www.";
        
        //====================================================================//
        // Server Actives Languages List
        $Parameters["Langs"]        = Multilang::getAvailablelanguages();

        /**
         * Check if WooCommerce is active
         */
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true)) {
            //====================================================================//
            // WooCommerce Specific Parameters
            $Parameters["Currency"]         = get_woocommerce_currency();
            $Parameters["CurrencySymbol"]   = get_woocommerce_currency_symbol();
            $Parameters["PriceBase"]        = wc_prices_include_tax() ? "TTC" : "HT";
        }
        
        return $Parameters;
    }
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testSequences($Name = null)
    {
        switch ($Name) {
            case "WcWithoutTaxes":
                // Setup Plugins
                self::enablePlugin("woocommerce/woocommerce.php");
                self::disablePlugin("wp-multilang/wp-multilang.php");
                $GLOBALS['wp_tests_options'] = array(
                    'active_plugins' => array( "splash-connector/splash.php", "woocommerce/woocommerce.php" ),
                );
                // Setup Options
                update_option("woocommerce_prices_include_tax", "yes");
                update_option("woocommerce_calc_taxes", "no");
                update_option("splash_multilang", "on");

                return null;
            case "ProductVATIncluded":
                // Setup Plugins
                self::enablePlugin("woocommerce/woocommerce.php");
                self::disablePlugin("wp-multilang/wp-multilang.php");
                $GLOBALS['wp_tests_options'] = array(
                    'active_plugins' => array( "splash-connector/splash.php", "woocommerce/woocommerce.php" ),
                );
                // Setup Options
                update_option("woocommerce_prices_include_tax", "yes");
                update_option("woocommerce_calc_taxes", "yes");
                update_option("splash_multilang", "on");

                return null;
            case "Monolangual":
                // Setup Plugins
                self::enablePlugin("woocommerce/woocommerce.php");
                self::disablePlugin("wp-multilang/wp-multilang.php");
                $GLOBALS['wp_tests_options'] = array(
                    'active_plugins' => array( "splash-connector/splash.php", "woocommerce/woocommerce.php" ),
                );
                // Setup Options
                update_option("woocommerce_prices_include_tax", "no");
                update_option("woocommerce_calc_taxes", "yes");
                update_option("splash_multilang", null);

                return null;
            case "Multilangual":
                // Setup Plugins
                self::enablePlugin("woocommerce/woocommerce.php");
                self::disablePlugin("wp-multilang/wp-multilang.php");
                $GLOBALS['wp_tests_options'] = array(
                    'active_plugins' => array( "splash-connector/splash.php", "woocommerce/woocommerce.php" ),
                );
                // Setup Options
                update_option("woocommerce_prices_include_tax", "no");
                update_option("woocommerce_calc_taxes", "yes");
                update_option("splash_multilang", "on");

                return null;
            case "WpMuPlugin":
                // Setup Plugins
                self::enablePlugin("woocommerce/woocommerce.php");
                self::enablePlugin("wp-multilang/wp-multilang.php");
                $GLOBALS['wp_tests_options'] = array(
                    'active_plugins' => array(
                        "splash-connector/splash.php", "woocommerce/woocommerce.php", "wp-multilang/wp-multilang.php" ),
                );
                // Setup Options
                update_option("woocommerce_prices_include_tax", "no");
                update_option("woocommerce_calc_taxes", "yes");
                update_option("splash_multilang", "on");

                return null;
            case "List":
//                return array( "Monolangual", "Multilangual" );
                return array( "Monolangual", "Multilangual", "WpMuPlugin" );
//                return array("WpMuPlugin" );
//                return array( "WcWithoutTaxes", "ProductVATIncluded" ,"Monolangual", "Multilangual" );
//                return array( "WcWithoutTaxes", "ProductVATIncluded" ,"Monolangual", "Multilangual", "WpMuPlugin" );
        }
    }
           
    //====================================================================//
    // *******************************************************************//
    //  SPECIALS MODULE LOCAL FUNCTIONS
    // *******************************************************************//
    //====================================================================//
    
    /**
     * Check if WooCommerce Plugin is Active
     *
     * @return  bool
     */
    public static function hasWooCommerce()
    {
        //====================================================================//
        // Check at Network Level
        if (is_multisite()) {
            if (array_key_exists('woocommerce/woocommerce.php', get_site_option('active_sitewide_plugins'))) {
                return true;
            }
        }
        
        //====================================================================//
        // Check at Site Level
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true);
    }
    
    /**
     * Check if WooCommerce Plugin is Active
     *
     * @return  bool
     */
    public static function hasWooCommerceBooking()
    {
        return self::isActivePlugin("woocommerce-bookings/woocommerce-bookings.php");
    }
}
