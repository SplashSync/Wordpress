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

namespace Splash\Local;

use ArrayObject;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Core\PluginManager;
use Splash\Local\Objects\Core\MultiLangTrait;
use Splash\Models\LocalClassInterface;

/**
 * Splash Local Core Management Class for WordPress
 */
class Local implements LocalClassInterface
{
    use PluginManager;
    use MultiLangTrait;

    //====================================================================//
    // *******************************************************************//
    //  MANDATORY CORE MODULE LOCAL FUNCTIONS
    // *******************************************************************//
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function parameters(): array
    {
        $parameters = array();

        //====================================================================//
        // Server Identification Parameters
        $parameters["WsIdentifier"] = \get_option("splash_ws_id", null);
        $parameters["WsEncryptionKey"] = \get_option("splash_ws_key", null);

        //====================================================================//
        // If Expert Mode => Allow Override of Server Host Address
        if ((\get_option("splash_advanced_mode", false)) && !empty(\get_option("splash_server_url", null))) {
            $parameters["WsHost"] = \get_option("splash_server_url", null);
        }

        //====================================================================//
        // If Expert Mode => Allow Override of Communication Protocol
        if ((\get_option("splash_advanced_mode", false)) && !empty(\get_option("splash_ws_protocol", null))) {
            //====================================================================//
            // Allow Override of Communication Protocol
            $parameters["WsMethod"] = \get_option("splash_ws_protocol", "NuSOAP");
        }

        //====================================================================//
        // Setup Custom Json Configuration Path to (../wp-content/plugins/splash.json)
        $pluginsHomePath = dirname(__DIR__, 2);
        $parameters["ConfiguratorPath"] = $pluginsHomePath."/splash.json";
        //====================================================================//
        // Setup Extensions Path
        $parameters["ExtensionsPath"] = array(
            $pluginsHomePath."/splash-advancepack/src",
            $pluginsHomePath."/splash-extensions",
        );
        //====================================================================//
        // Multi-sites Mode => Override Soap Host & Path
        if (\is_multisite()) {
            $blogDetails = \get_blog_details();
            if ($blogDetails) {
                $parameters["ServerHost"] = $blogDetails->domain;
                $parameters["ServerPath"] = $blogDetails->path;
                $parameters["ServerPath"] .= "wp-content/plugins/splash-connector/vendor/splash/phpcore/soap.php";
            }
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function includes(): bool
    {
        //====================================================================//
        // When Library is called in server mode ONLY
        //====================================================================//
        if (Splash::isServerMode() && !defined('DOING_CRON')) {
            /** Setup WordPress environment for Remote Actions */
            define('DOING_CRON', true);
            /** Include the bootstrap for setting up WordPress environment */
            include(dirname(__DIR__, 4).'/wp-load.php');
            /** Remote Automatic login */
            /** @var null|int $userId */
            $userId = get_option("splash_ws_user", null);
            wp_set_current_user((int) $userId);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function selfTest(): bool
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
        /** @var null|int $userId */
        $userId = get_option("splash_ws_user", null);
        if (empty($userId)) {
            return Splash::log()->err("ErrSelfTestNoUser");
        }
        if (is_wp_error(get_user_by("ID", $userId))) {
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
                        ."It is highly recommended to store Product Price without Tax to work with Splash."
                );
            }
        }
        //====================================================================//
        // Debug Mode => Display Host & Path Infos
        /** @phpstan-ignore-next-line */
        if (defined('WP_DEBUG') && !empty(WP_DEBUG)) {
            Splash::log()->war("Current Server Url : ".Splash::ws()->getServerInfos()["ServerHost"]);
            Splash::log()->war("Current Server Path: ".Splash::ws()->getServerInfos()["ServerPath"]);
        }
        //====================================================================//
        //  Display Detected & Activated Plugins
        $this->selfTestPlugins();

        Splash::log()->msg("MsgSelfTestOk");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function informations(ArrayObject $informations): ArrayObject
    {
        //====================================================================//
        // Init Response Object
        $response = $informations;

        //====================================================================//
        // Company Informations
        $response->company = get_option("blogname", "...");
        $response->address = "N/A";
        $response->zip = " ";
        $response->town = " ";
        $response->country = " ";

        if (is_multisite()) {
            $blogDetails = get_blog_details();
            $response->www = $blogDetails ? $blogDetails->home : get_option("home", "...");
        } else {
            $response->www = get_option("home", "...");
        }
        $response->email = get_option("admin_email", "...");
        $response->phone = " ";

        //====================================================================//
        // Server Logo & Images
        /** @var int $rawIcoId */
        $rawIcoId = get_option('site_icon');
        $rawIcoPath = get_attached_file($rawIcoId);
        if (!empty($rawIcoPath)) {
            $response->icoraw = Splash::file()->readFileContents($rawIcoPath);
        } else {
            $response->icoraw = Splash::file()->readFileContents(
                dirname(__DIR__, 4)."/wp-admin/images/w-logo-blue.png"
            );
        }
        $response->logourl = get_site_icon_url();

        //====================================================================//
        // Server Informations
        if (is_multisite()) {
            $blogDetails = get_blog_details();
            if ($blogDetails) {
                $response->servertype = "Wordpress (Multisites)";
                $response->serverurl = $blogDetails->siteurl;
            }
        } else {
            $response->servertype = "Wordpress";
            $response->serverurl = get_option("siteurl", "...");
        }

        $response->moduleversion = SPLASH_SYNC_VERSION;

        return $response;
    }

    //====================================================================//
    // *******************************************************************//
    //  OPTIONAl CORE MODULE LOCAL FUNCTIONS
    // *******************************************************************//
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function testParameters(): array
    {
        //====================================================================//
        // Init Parameters Array
        $parameters = array();

        //====================================================================//
        // Urls Must have Http::// prefix
        $parameters["Url_Prefix"] = "http://www.";

        //====================================================================//
        // Server Actives Languages List
        $parameters["Default_Lang"] = self::getDefaultLanguage();
        $parameters["Langs"] = self::getAvailablelanguages();

        /**
         * Check if WooCommerce is active
         */
        if (static::hasWooCommerce()) {
            //====================================================================//
            // WooCommerce Specific Parameters
            $parameters["Currency"] = get_woocommerce_currency();
            $parameters["CurrencySymbol"] = get_woocommerce_currency_symbol();
            $parameters["PriceBase"] = wc_prices_include_tax() ? "TTC" : "HT";
            $parameters["PricesPrecision"] = 2;
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testSequences(string $name = null): array
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
                return array( "WcWithoutTaxes", "ProductVATIncluded" ,"Monolangual", "WpMuPlugin" );
        }

        return array();
    }

    /**
     * Display Activated Plugins Messages
     */
    public function selfTestPlugins(): void
    {
        /**
         * Check if WooCommerce is active
         */
        if (self::hasWooCommerce()) {
            Splash::log()->msg("WooCommerce plugin detected");
        }

        /**
         * Check if Wp Multi-lang is active
         */
        if (self::hasWpMultilang()) {
            Splash::log()->msg("Wp Multi-lang plugin detected");
        }

        /**
         * Check if Dokan is active
         */
        if (self::hasDokan()) {
            Splash::log()->msg("Dokan plugin detected");
        }

        /**
         * Check if Wpml is active
         */
        if (self::hasWpml()) {
            /**
             * Check if Wpml for WooCommerce is active
             */
            self::hasWooCommerceWpml()
                ? Splash::log()->msg("Wpml & Wpml for WooCommerce plugin detected")
                : Splash::log()->msg("Wpml plugin detected")
            ;
        }

        /**
         * Check if Wholesale Prices for WooCommerce by Wholesale is active
         */
        if (self::hasWooWholesalePrices()) {
            Splash::log()->msg("Wholesale Prices for WooCommerce by Wholesale plugin detected");
        }

        /**
         * Check if Wholesale Prices for WooCommerce by Wholesale is active
         */
        if (self::hasWooPdfInvoices()) {
            Splash::log()->msg("WooCommerce PDF Invoices by WP Overnight plugin detected");
        }
    }
}
