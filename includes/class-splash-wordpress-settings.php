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

use Splash\Client\Splash;

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Splash_Wordpress_Settings
{
    /**
     * The main plugin object.
     *
     * @var object
     *
     * @access      public
     *
     * @since   1.0.0
     */
    public $parent;

    /**
     * Prefix for plugin settings.
     *
     * @var string
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $base = '';

    /**
     * Available settings for plugin.
     *
     * @var array
     *
     * @access  public
     *
     * @since   1.0.0
     */
    public $settings = array();

    /**
     * The single instance of Splash_Settings.
     *
     * @var object
     *
     * @access      private
     *
     * @since   1.0.0
     */
    private static $_instance;

    /**
     * @SuppressWarnings(PHPMD.ExitExpression)
     *
     * @param mixed $parent
     */
    public function __construct($parent)
    {
        if (! defined('ABSPATH')) {
            exit;
        }

        $this->parent = $parent;

        $this->base = 'splash_';

        // Initialise settings
        add_action('init', array( $this, 'init_settings' ), 11);

        // Register plugin settings
        add_action('admin_init', array( $this, 'register_settings' ));

        // Add settings page to menu
        add_action('admin_menu', array( $this, 'add_menu_item' ));

        // Add settings link to plugins page
        add_filter('plugin_action_links_'.plugin_basename($this->parent->file), array( $this, 'add_settings_link' ));
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->parent->_version);
    } // End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->parent->_version);
    } // End __wakeup()

    /**
     * Initialise settings
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function init_settings()
    {
        $this->settings = $this->settings_fields();
    }

    /**
     * Add settings page to admin menu
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function add_menu_item()
    {
        add_options_page(
            __('Splash Sync', 'splash-wordpress-plugin'),
            __('Splash Sync', 'splash-wordpress-plugin'),
            'manage_options',
            $this->parent->_token.'_settings',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Add settings link to plugin list table
     *
     * @param array $links Existing links
     *
     * @return array Modified links
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function add_settings_link($links)
    {
        $settingsLink = '<a href="options-general.php?page='.$this->parent->_token.'_settings">'.__('Settings', 'wordpress-splash-plugin').'</a>';
        array_push($links, $settingsLink);

        return $links;
    }

    /**
     * Register plugin settings
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function register_settings()
    {
        if (is_array($this->settings)) {
            // Check posted/selected tab
            $currentSection = '';
            if (isset($_POST['tab']) && $_POST['tab']) {
                $currentSection = $_POST['tab'];
            } else {
                if (isset($_GET['tab']) && $_GET['tab']) {
                    $currentSection = $_GET['tab'];
                }
            }

            foreach ($this->settings as $section => $data) {
                if ($currentSection && $currentSection != $section) {
                    continue;
                }

                // Add section to page
                add_settings_section($section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token.'_settings');

                foreach ($data['fields'] as $field) {
                    // Validation callback for field
                    $validation = '';
                    if (isset($field['callback'])) {
                        $validation = $field['callback'];
                    }

                    // Register field
                    $optionName = $this->base.$field['id'];
                    register_setting($this->parent->_token.'_settings', $optionName, $validation);

                    // Add field to page
                    add_settings_field($field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token.'_settings', $section, array( 'field' => $field, 'prefix' => $this->base ));
                }

                if (! $currentSection) {
                    break;
                }
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @param mixed $section
     */
    public function settings_section($section)
    {
        $html = '<p> '.$this->settings[ $section['id'] ]['description'].'</p>'."\n";
        echo $html;
    }

    /**
     * Load settings page content
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function settings_page()
    {
        // Build page HTML
        $html = '<div class="wrap" id="'.$this->parent->_token.'_settings">'."\n";
        $html .= '<h2>'.__('Plugin Settings', 'wordpress-plugin-template').'</h2>'."\n";

        $tab = '';
        if (isset($_GET['tab']) && $_GET['tab']) {
            $tab .= $_GET['tab'];
        }

        // Show page tabs
        if (is_array($this->settings) && 1 < count($this->settings)) {
            $html .= '<h2 class="nav-tab-wrapper">'."\n";

            $count = 0;
            foreach ($this->settings as $section => $data) {
                // Set tab class
                $class = 'nav-tab';
                if (! isset($_GET['tab'])) {
                    if (0 == $count) {
                        $class .= ' nav-tab-active';
                    }
                } else {
                    if (isset($_GET['tab']) && $section == $_GET['tab']) {
                        $class .= ' nav-tab-active';
                    }
                }

                // Set tab link
                $tablink = add_query_arg(array( 'tab' => $section ));
                if (isset($_GET['settings-updated'])) {
                    $tablink = remove_query_arg('settings-updated', $tablink);
                }

                // Output tab
                $html .= '<a href="'.$tablink.'" class="'.esc_attr($class).'">'.esc_html($data['title']).'</a>'."\n";

                ++$count;
            }

            $html .= '</h2>'."\n";
        }

        $html .= '<form method="post" action="options.php" enctype="multipart/form-data">'."\n";

        // Get settings fields
        ob_start();
        settings_fields($this->parent->_token.'_settings');
        do_settings_sections($this->parent->_token.'_settings');
        $html .= ob_get_clean();

        $html .= '<p class="submit">'."\n";
        $html .= '<input type="hidden" name="tab" value="'.esc_attr($tab).'" />'."\n";
        $html .= '<input name="Submit" type="submit" class="button-primary" value="'.esc_attr(__('Save Settings', 'splash-wordpress-plugin')).'" />'."\n";
        $html .= '</p>'."\n";
        $html .= '</form>'."\n";
        $html .= '</div>'."\n";

        $html .= $this->renderSelftests();
        $html .= $this->renderInfo();
        $html .= $this->renderLogs();
        $html .= $this->renderDebug();

        echo $html;
    }

    /**
     * Main WordPress_Plugin_Template_Settings Instance
     *
     * Ensures only one instance of WordPress_Plugin_Template_Settings is loaded or can be loaded.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @see WordPress_Plugin_Template()
     *
     * @param mixed $parent
     *
     * @return self
     */
    public static function instance($parent)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($parent);
        }

        return self::$_instance;
    } // End instance()

    /**
     * Render Splash Module Informations Tab
     *
     * @since 0.0.1
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function render_info_tab()
    {
        $html = "";

        $tablink = add_query_arg(array( 'tab' => "infos" ));
        $tabname = __('Informations', 'splash-wordpress-plugin');

        $html .= '<a href="'.$tablink.'" class="nav-tab">'.$tabname.'</a>';

        return $html;
    }

    /**
     * Build settings fields
     *
     * @return array Fields to be displayed on settings page
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    private function settings_fields()
    {
        $users = array();
        foreach (get_users(array( 'role__in' => array('administrator'))) as $user) {
            $users[$user->ID] = $user->display_name;
        }
        $settings['connection'] = array(
            'title' => __('Connection', 'splash-wordpress-plugin'),
            'description' => __('These parameters are provided when you create a new Server on our website.', 'splash-wordpress-plugin'),
            'fields' => array(
                array(
                    'id' => 'ws_id',
                    'label' => __('Identifier', 'splash-wordpress-plugin'),
                    'description' => __('Unique Identifier for this website on Splash Servers (8 Char Max). ', 'splash-wordpress-plugin'),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => ''
                ),
                array(
                    'id' => 'ws_key',
                    'label' => __('Encryption Key', 'splash-wordpress-plugin'),
                    'description' => __('Unique Encryption Key', 'splash-wordpress-plugin'),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => ''
                ),
                array(
                    'id' => 'ws_user',
                    'label' => __('User', 'splash-wordpress-plugin'),
                    'description' => __('User to use for Webservice transactions', 'splash-wordpress-plugin'),
                    'type' => 'select',
                    'options' => $users,
                ),
            )
        );
        $settings['advanced'] = array(
            'title' => __('Advanced', 'splash-wordpress-plugin'),
            'description' => __('These are some advanced parameters. Only use them uppon our request. Warning: your server may not work anymore!', 'splash-wordpress-plugin'),
            'fields' => array(
                array(
                    'id' => 'advanced_mode',
                    'label' => __('Enable', 'splash-wordpress-plugin'),
                    'description' => __('Enable advanced mode. ', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                    'default' => '0'
                ),
                array(
                    'id' => 'server_url',
                    'label' => __('Server Url', 'splash-wordpress-plugin'),
                    'description' => __('Only modify uppon our request! Default value : www.splashsync.com/ws/soap.', 'splash-wordpress-plugin'),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => 'www.splashsync.com/ws/soap'
                ),
                array(
                    'id' => 'ws_protocol',
                    'label' => __('Protocol', 'splash-wordpress-plugin'),
                    'description' => __('Protocol to use for Webservice communication', 'splash-wordpress-plugin'),
                    'type' => 'select',
                    'options' => array("NuSOAP" => "NuSOAP Librairie", "SOAP" => "Generic PHP SOAP" ),
                    'default' => 'NuSOAP'
                ),
                array(
                    'id' => 'cf_product',
                    'label' => __('Products'),
                    'description' => __('Enable Custom Fields for Products.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                    'default' => '1'
                ), array(
                    'id' => 'cf_order',
                    'label' => __('Orders'),
                    'description' => __('Enable Custom Fields for Orders.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                    'default' => '0'
                ), array(
                    'id' => 'cf_invoice',
                    'label' => __('Invoices'),
                    'description' => __('Enable Custom Fields for Invoices.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                    'default' => '0'
                ), array(
                    'id' => 'cf_post',
                    'label' => __('Posts'),
                    'description' => __('Enable Custom Fields for Posts.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                    'default' => '0'
                ), array(
                    'id' => 'cf_page',
                    'label' => __('Pages'),
                    'description' => __('Enable Custom Fields for Pages.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                    'default' => '0'
                ),
            )
        );

        return apply_filters($this->parent->_token.'_settings_fields', $settings);
    }

    /**
     * Init Splash Module & Perform Self-tests
     *
     * @since 0.0.1
     */
    private function renderSelftests()
    {
        $html = "";

        //====================================================================//
        // Execute Splash Module Selftest
        if (Splash::selfTest()) {
            // Dipslay Notifications
            $html .= '<div class="notice notice-success is-dismissible">';
            $html .= '<p>'.__('Self-Tests Passed !', 'splash-wordpress-plugin').'</p>';
            $html .= '</div>';
        } else {
            // Dipslay Notifications
            $html .= '<div class="notice notice-error is-dismissible">';
            $html .= '<p>'.__('Self-Tests Failled... Please Check, your configuration.', 'splash-wordpress-plugin').'</p>';
            $html .= '</div>';
            // Dipslay Self-Test Log
            $html .= "<br><br>";
        }

        return $html;
    }

    /**
     * Render Splash Module Informations
     *
     * @since 0.0.1
     */
    private function renderInfo()
    {
        $html = "<h2>".__('Informations', 'splash-wordpress-plugin')."</h2>";
        $html .= '<table class="wp-list-table widefat" width="100%"><tbody>';

        //====================================================================//
        // List Objects
        //====================================================================//
        $objects = Splash::objects();
        $html .= '  <tr class="pair">';
        $html .= '      <td width="30%">'.__('Available Objects', 'splash-wordpress-plugin').'</td>';
        $html .= '      <td>';
        foreach ($objects as $object) {
            $html .= $object.", ";
        }
        $html .= '      </td>';
        $html .= '  </tr>';

        //====================================================================//
        // List Widgets
        //====================================================================//
        $widgets = Splash::widgets();
        $html .= '  <tr class="pair">';
        $html .= '      <td width="30%">'.__('Available Widgets', 'splash-wordpress-plugin').'</td>';
        $html .= '      <td><ul>';
        foreach ($widgets as $widget) {
            $html .= "<li>".$widget."</li>";
        }
        $html .= '      </ul></td>';
        $html .= '  </tr>';

        //====================================================================//
        // Splash Server Ping
        //====================================================================//
        $html .= '  <tr class="impair">';
        $html .= '      <td width="30%">'.__('Splash Server Ping Test', 'splash-wordpress-plugin').'</td>';
        if (Splash::ping()) {
            $html .= '      <td style="color: green;">'.Splash::log()->getHtmlLog(true).'</td>';
        } else {
            $html .= '      <td style="color: red;">'.Splash::log()->getHtmlLog(true).'</td>';
        }
        $html .= '  </tr>';

        //====================================================================//
        // Splash Server Connect
        //====================================================================//
        $html .= '  <tr class="impair">';
        $html .= '      <td width="30%">'.__('Splash Server Connect Test', 'splash-wordpress-plugin').'</td>';
        if (Splash::connect()) {
            $html .= '      <td style="color: green;">'.Splash::log()->getHtmlLog(true).'</td>';
        } else {
            $html .= '      <td style="color: red;">'.Splash::log()->getHtmlLog(true).'</td>';
        }

        $html .= '  </tr>';
        $html .= '</tbody></table">';

        return $html;
    }

    /**
     * Render Splash Module Logs
     *
     * @since 0.0.1
     */
    private function renderLogs()
    {
        $htmlLog = Splash::log()->getHtmlLog(true);

        if (empty($htmlLog)) {
            return "";
        }

        $html = '<table class="wp-list-table widefat" width="100%"><tbody>';
        $html .= "   <tr><td width='100%'>";
        $html .= Splash::log()->getHtmlLog(true);
        $html .= "   </td></tr>";
        $html .= '</tbody></table">';

        return $html;
    }

    /**
     * Render Splash Module Debug
     *
     * @since 0.0.1
     */
    private function renderDebug()
    {
        /**
         * Check if Kint Debugger is active
         */
        if (!in_array('kint-debugger/kint-debugger.php', apply_filters('active_plugins', get_option('active_plugins')), true)) {
            return "";
        }

        return "";
    }
}
