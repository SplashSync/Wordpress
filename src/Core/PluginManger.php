<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *  @author    Splash Sync <www.splashsync.com>
 *  @copyright 2015-2018 Splash Sync
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 *
 **/

namespace Splash\Local\Core;

/**
 * @abstract    Wordpress PluginManger
 */
trait PluginManger
{
    
    /**
     * @abstract    Enable a Wordpress Plugin
     * @param       string  $plugin     Plugin Name
     */
    protected static function enablePlugin($plugin)
    {
        if (! function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (! is_plugin_active($plugin)) {
            activate_plugin($plugin);
        }
    }
    
    /**
     * @abstract    Disable a Wordpress Plugin
     * @param       string  $plugin     Plugin Name
     */
    protected static function disablePlugin($plugin)
    {
        if (! function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin);
        }
    }
}
