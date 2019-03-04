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

namespace Splash\Local\Core;

/**
 * Wordpress PluginManger
 */
trait PluginManger
{
    /**
     * Check if a Plugin is Active
     *
     * @param string $pluginCode Pluging Root Class Name (i.e 'woocommerce/woocommerce.php')
     *
     * @return bool
     */
    public static function isActivePlugin($pluginCode)
    {
        //====================================================================//
        // Check at Network Level
        if (is_multisite()) {
            if (array_key_exists($pluginCode, get_site_option('active_sitewide_plugins'))) {
                return true;
            }
        }
        //====================================================================//
        // Check at Site Level
        return in_array($pluginCode, apply_filters('active_plugins', get_option('active_plugins')), true);
    }

    /**
     * Check if WooCommerce Plugin is Active
     *
     * @return bool
     */
    public static function hasWooCommerce()
    {
        return self::isActivePlugin("woocommerce/woocommerce.php");
    }

    /**
     * Check if WooCommerce Plugin is Active
     *
     * @return bool
     */
    public static function hasWooCommerceBooking()
    {
        return self::isActivePlugin("woocommerce-bookings/woocommerce-bookings.php");
    }

    /**
     * Enable a Wordpress Plugin
     *
     * @param string $plugin Plugin Name
     */
    protected static function enablePlugin($plugin)
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
     */
    protected static function disablePlugin($plugin)
    {
        if (! function_exists('activate_plugin')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin);
        }
    }
}
