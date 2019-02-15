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
        $parameters       =     array();

        //====================================================================//
        // Server Identification Parameters
        $parameters["WsIdentifier"]         =   get_option("splash_ws_id", null);
        $parameters["WsEncryptionKey"]      =   get_option("splash_ws_key", null);
        
        //====================================================================//
        // If Expert Mode => Allow Overide of Server Host Address
        if ((get_option("splash_advanced_mode", false)) && !empty(get_option("splash_server_url", null))) {
            $parameters["WsHost"]           =   get_option("splash_server_url", null);
        }
        //====================================================================//
        // If Expert Mode => Allow Overide of Communication Protocol
        if ((get_option("splash_advanced_mode", false)) && !empty(get_option("splash_ws_protocol", null))) {
            $parameters["WsMethod"]         =   get_option("splash_ws_protocol", "NuSOAP");
        }
        
        //====================================================================//
        // Multisites Mode => Overide Soap Host & Path
        if (is_multisite()) {
            $blogDetails    =   get_blog_details();
            if ($blogDetails) {
                $parameters["ServerHost"]  = $blogDetails->domain;
                $parameters["ServerPath"]  = $blogDetails->path;
                $parameters["ServerPath"] .= "wp-content/plugins/splash-connector/vendor/splash/phpcore/soap.php";
            }
        }
        
        return $parameters;
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
        }
        
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
    public function informations($informations)
    {
        //====================================================================//
        // Init Response Object
        $response = $informations;

        //====================================================================//
        // Company Informations
        $response->company          =   get_option("blogname", "...");
        $response->address          =   "N/A";
        $response->zip              =   " ";
        $response->town             =   " ";
        $response->country          =   " ";
        
        if (is_multisite()) {
            $blogDetails            =   get_blog_details();
            $response->www          =   $blogDetails->home;
        } else {
            $response->www          =   get_option("home", "...");
        }
        $response->email            =   get_option("admin_email", "...");
        $response->phone            =   " ";
        
        //====================================================================//
        // Server Logo & Images
        $rawIcoPath                 =   get_attached_file(get_option('site_icon'));
        if (!empty($rawIcoPath)) {
            $response->icoraw           =   Splash::file()->readFileContents($rawIcoPath);
        } else {
            $response->icoraw           =   Splash::file()->readFileContents(
                dirname(dirname(dirname(dirname(__DIR__)))) . "/wp-admin/images/w-logo-blue.png"
            );
        }
        $response->logourl          =   get_site_icon_url();
        
        //====================================================================//
        // Server Informations
        if (is_multisite()) {
            $blogDetails                =   get_blog_details();
            $response->servertype       =   "Wordpress (Multisites)";
            $response->serverurl        =   $blogDetails->siteurl;
        } else {
            $response->servertype       =   "Wordpress";
            $response->serverurl        =   get_option("siteurl", "...");
        }
        
        $response->moduleversion        =   SPLASH_SYNC_VERSION;
        
        return $response;
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
        $parameters       =     array();

        //====================================================================//
        // Urls Must have Http::// prefix
        $parameters["Url_Prefix"]   = "http://www.";
        
        //====================================================================//
        // Server Actives Languages List
        $parameters["Default_Lang"] = Multilang::getDefaultLanguage();
        $parameters["Langs"]        = Multilang::getAvailablelanguages();

        /**
         * Check if WooCommerce is active
         */
        if (static::hasWooCommerce()) {
            //====================================================================//
            // WooCommerce Specific Parameters
            $parameters["Currency"]         = get_woocommerce_currency();
            $parameters["CurrencySymbol"]   = get_woocommerce_currency_symbol();
            $parameters["PriceBase"]        = wc_prices_include_tax() ? "TTC" : "HT";
            $parameters["PricesPrecision"]  = 3;
        }

        return $parameters;
    }
    
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testSequences($name = null)
    {
        switch ($name) {
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

                return array();
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

                return array();
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

                return array();
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

                return array();
            case "List":
//                return array( "Monolangual" );
//                return array( "WpMuPlugin" );
//                return array( "Monolangual", "Multilangual" );
//                return array( "Monolangual", "Multilangual", "WpMuPlugin" );
//                return array("WpMuPlugin" );
//                return array( "WcWithoutTaxes", "ProductVATIncluded" ,"Monolangual", "Multilangual" );
                return array( "WcWithoutTaxes", "ProductVATIncluded" ,"Monolangual", "WpMuPlugin" );
        }
    }
}
