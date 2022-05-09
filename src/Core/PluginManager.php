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

namespace Splash\Local\Core;

/**
 * WordPress PluginManger
 */
trait PluginManager
{
    /**
     * Check if a Plugin is Active
     *
     * @param string $pluginCode Plugin Root Class Name (i.e 'woocommerce/woocommerce.php')
     *
     * @return bool
     */
    public static function isActivePlugin(string $pluginCode): bool
    {
        //====================================================================//
        // Check at Network Level
        if (is_multisite()) {
            /** @var string[] $siteWidePlugins */
            $siteWidePlugins = get_site_option('active_sitewide_plugins');
            if (array_key_exists($pluginCode, $siteWidePlugins)) {
                return true;
            }
        }
        //====================================================================//
        // Check at Site Level
        /** @var string[] $sitePlugins */
        $sitePlugins = apply_filters('active_plugins', get_option('active_plugins'));

        return in_array($pluginCode, $sitePlugins, true);
    }

    /**
     * Check if WooCommerce Plugin is Active
     *
     * @return bool
     */
    public static function hasWooCommerce(): bool
    {
        return self::isActivePlugin("woocommerce/woocommerce.php");
    }

    /**
     * Check if WooCommerce Booking Plugin is Active
     *
     * @return bool
     */
    public static function hasWooCommerceBooking(): bool
    {
        return self::isActivePlugin("woocommerce-bookings/woocommerce-bookings.php");
    }

    /**
     * Check if Dokan Plugin is Active
     *
     * @return bool
     */
    public static function hasDokan(): bool
    {
        return
            self::isActivePlugin("dokan-lite/dokan.php")
            || self::isActivePlugin("dokan/dokan.php")
        ;
    }

    /**
     * Check if Extra Product Options Plugin is Active
     *
     * @return bool
     */
    public static function hasExtraProductOptions(): bool
    {
        return self::isActivePlugin('woo-extra-product-options/woo-extra-product-options.php');
    }

    /**
     * Check if WpMultilang Plugin is Active
     *
     * @return bool
     */
    public static function hasWpMultilang(): bool
    {
        return self::isActivePlugin('wp-multilang/wp-multilang.php');
    }

    /**
     * Check if Wpml Plugin is Active
     *
     * @return bool
     */
    public static function hasWpml(): bool
    {
        return self::isActivePlugin("sitepress-multilingual-cms/sitepress.php");
    }

    /**
     * Check if Wpml for WooCommerce Plugin is Active
     *
     * @return bool
     */
    public static function hasWooCommerceWpml(): bool
    {
        return self::isActivePlugin("woocommerce-multilingual/wpml-woocommerce.php");
    }

    /**
     * Check if Wholesale Prices for WooCommerce by Wholesale Suite Plugin is Active
     *
     * @return bool
     */
    public static function hasWooWholesalePrices(): bool
    {
        return self::isActivePlugin("woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php");
    }

    /**
     * Check if WooCommerce PDF Invoices by WP Overnight Plugin is Active
     *
     * @return bool
     */
    public static function hasWooPdfInvoices(): bool
    {
        return self::isActivePlugin("woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php");
    }

    /**
     * Enable a Wordpress Plugin
     *
     * @param string $plugin Plugin Name
     *
     * @return void
     */
    protected static function enablePlugin(string $plugin)
    {
        if (! function_exists('activate_plugin')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        if (! is_plugin_active($plugin)) {
            activate_plugin($plugin);
        }
    }

    /**
     * Disable a Wordpress Plugin
     *
     * @param string $plugin Plugin Name
     *
     * @return void
     */
    protected static function disablePlugin(string $plugin)
    {
        if (! function_exists('activate_plugin')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin);
        }
    }
}
