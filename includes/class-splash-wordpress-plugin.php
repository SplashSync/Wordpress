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

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Splash_Wordpress_Plugin
{
    /**
     * Settings class object
     *
     * @var object
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $settings;

    /**
     * The version number.
     *
     * @var string
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     *
     * @var string
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     *
     * @var string
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     *
     * @var string
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     *
     * @var string
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     *
     * @var string
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     *
     * @var string
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * @var WordPress_Plugin_Template_Admin_API
     */
    public $admin;

    /**
     * The single instance of Splash_Plugin.
     *
     * @var self
     *
     * @access  private
     *
     * @since   1.0.0
     */
    private static $_instance;

    /**
     * Constructor function.
     *
     * @access  public
     *
     * @since   1.0.0
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     *
     * @param mixed $file
     * @param mixed $version
     */
    public function __construct($file = '', $version = SPLASH_SYNC_VERSION)
    {
        if (! defined('ABSPATH')) {
            exit;
        }

        $this->_version = $version;
        $this->_token = 'splash-wordpress-plugin';

        // Load plugin environment variables
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir).'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        register_activation_hook($this->file, array( $this, 'install' ));

        // Load API for generic admin functions
        if (is_admin()) {
            $this->admin = new WordPress_Plugin_Template_Admin_API();
        }

        // Handle localisation
        $this->load_plugin_textdomain();
        add_action('init', array( $this, 'load_localisation' ), 0);

        //====================================================================//
        // Handle Objects Commit
        //====================================================================//
        // Pages & Posts
        \Splash\Local\Objects\Post::registerHooks();
        \Splash\Local\Objects\ThirdParty::registerHooks();
        \Splash\Local\Objects\Product::registerHooks();
        \Splash\Local\Objects\Order::registerHooks();

        //====================================================================//
        // Handle User Messages
        //====================================================================//
        \Splash\Local\Notifier::registerHooks();
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Load plugin localisation
     *
     * @access  public
     *
     * @since   1.0.0
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function load_localisation()
    {
        load_plugin_textdomain('splash-wordpress-plugin', false, dirname(plugin_basename($this->file)).'/lang/');
    }

    /**
     * Load plugin textdomain
     *
     * @access  public
     *
     * @since   1.0.0
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function load_plugin_textdomain()
    {
        $domain = 'splash-wordpress-plugin';

        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo');
        load_plugin_textdomain($domain, false, dirname(plugin_basename($this->file)).'/lang/');
    }

    /**
     * Main WordPress_Plugin_Template Instance
     *
     * Ensures only one instance of WordPress_Plugin_Template is loaded or can be loaded.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @see WordPress_Plugin_Template()
     *
     * @param mixed $file
     * @param mixed $version
     *
     * @return Splash_Wordpress_Plugin
     */
    public static function instance($file = '', $version = SPLASH_SYNC_VERSION)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }

        return self::$_instance;
    }

    /**
     * Installation. Runs on activation.
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public function install()
    {
        $this->_log_version_number();
    }

    /**
     * Log the plugin version number.
     *
     * @access  public
     *
     * @since   1.0.0
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    private function _log_version_number()
    {
        update_option($this->_token.'_version', $this->_version);
    }
}
