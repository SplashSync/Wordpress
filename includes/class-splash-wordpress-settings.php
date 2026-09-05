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
        add_action('admin_enqueue_scripts', array( \Splash\Local\Admin\SettingsLayout::class, 'enqueueAssets' ));

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
        $settingsLink = '<a href="options-general.php?page='.$this->parent->_token.'_settings">'.__('Settings', 'splash-wordpress-plugin').'</a>';
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
            $currentTab = '';
            if (isset($_POST['tab']) && $_POST['tab']) {
                $currentTab = $_POST['tab'];
            } else {
                if (isset($_GET['tab']) && $_GET['tab']) {
                    $currentTab = $_GET['tab'];
                }
            }
            // Resolve tab to its sections (fallback to first tab)
            $tabs = $this->settings_tabs();
            if (!isset($tabs[$currentTab])) {
                $currentTab = (string) array_key_first($tabs);
            }
            $tabSections = $tabs[$currentTab]['sections'];

            foreach ($this->settings as $section => $data) {
                if (!in_array($section, $tabSections, true)) {
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

                    // Build field label: title + description below (input side stays clean)
                    $fieldLabel = $field['label'];
                    if (!empty($field['description'])) {
                        $fieldLabel .= '<p class="spl-wp-field__help">'.$field['description'].'</p>';
                    }
                    $fieldArgs = $field;
                    $fieldArgs['description'] = '';

                    // Add field to page
                    add_settings_field($field['id'], $fieldLabel, array( $this->parent->admin, 'display_field' ), $this->parent->_token.'_settings', $section, array( 'field' => $fieldArgs, 'prefix' => $this->base ));
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
        $html = '<p class="spl-wp-card__desc"> '.$this->settings[ $section['id'] ]['description'].'</p>'."\n";
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
        $layout = \Splash\Local\Admin\SettingsLayout::class;

        // Check current tab
        $tab = '';
        if (isset($_GET['tab']) && $_GET['tab']) {
            $tab .= $_GET['tab'];
        }

        // Build page HTML
        $html = '<div class="wrap spl-wp-wrap" id="'.$this->parent->_token.'_settings">'."\n";
        $html .= '<h1 class="screen-reader-text">'.__('Splash Sync', 'splash-wordpress-plugin').'</h1>'."\n";
        $html .= $layout::getHeader();
        $html .= '<hr class="wp-header-end" />'."\n";

        // Module Self-Tests Notices (all tabs)
        $html .= $this->renderSelftests();

        // Show page tabs
        $html .= $this->renderTabs($tab);

        if ('infos' == $tab) {
            // Informations tab: module infos & logs cards
            $html .= $layout::getGridOpen();
            $html .= $this->renderInfo();
            $html .= $this->renderLogs();
            $html .= $this->renderDebug();
            $html .= $layout::getGridClose();
        } else {
            // Settings form for current tab: one card per section
            $page = $this->parent->_token.'_settings';
            $html .= '<form method="post" action="options.php" enctype="multipart/form-data">'."\n";

            ob_start();
            settings_fields($page);
            $html .= ob_get_clean();

            $html .= $layout::getGridOpen();
            global $wp_settings_sections;
            foreach ((array) ($wp_settings_sections[$page] ?? array()) as $section) {
                $description = $this->settings[$section['id']]['description'] ?? '';
                $html .= $layout::getCardOpen($section['title'], $description);
                ob_start();
                echo '<table class="form-table" role="presentation">';
                do_settings_fields($page, $section['id']);
                echo '</table>';
                $html .= ob_get_clean();
                $html .= $layout::getCardClose();
            }
            $html .= $layout::getGridClose();

            $html .= '<p class="submit">'."\n";
            $html .= '<input type="hidden" name="tab" value="'.esc_attr($tab).'" />'."\n";
            $html .= '<input name="Submit" type="submit" class="button-primary" value="'.esc_attr(__('Save Settings', 'splash-wordpress-plugin')).'" />'."\n";
            $html .= '</p>'."\n";
            $html .= '</form>'."\n";
        }
        $html .= '</div>'."\n";

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

        $html .= '<a href="'.esc_url($tablink).'" class="nav-tab">'.$tabname.'</a>';

        return $html;
    }

    /**
     * Settings Tabs Definitions: each Tab groups one or more Sections (Cards)
     *
     * @return array
     */
    private function settings_tabs()
    {
        return array(
            'connection' => array(
                'title' => __('Connection', 'splash-wordpress-plugin'),
                'sections' => array('connection', 'advanced'),
            ),
            'products' => array(
                'title' => __('Products', 'splash-wordpress-plugin'),
                'sections' => array('products'),
            ),
            'orders' => array(
                'title' => __('Orders', 'splash-wordpress-plugin'),
                'sections' => array('orders'),
            ),
            'users' => array(
                'title' => __('Users', 'splash-wordpress-plugin'),
                'sections' => array('users'),
            ),
            'contents' => array(
                'title' => __('Contents', 'splash-wordpress-plugin'),
                'sections' => array('contents'),
            ),
        );
    }

    /**
     * Render Settings Page Navigation Tabs (Sections + Informations)
     *
     * @param string $currentTab Current Selected Tab Slug
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function renderTabs($currentTab)
    {
        if (!is_array($this->settings)) {
            return '';
        }
        // Build Tabs List: Settings Tabs + Informations
        $tabs = array();
        foreach ($this->settings_tabs() as $slug => $data) {
            $tabs[$slug] = $data['title'];
        }
        $tabs['infos'] = __('Informations', 'splash-wordpress-plugin');

        $html = '<h2 class="nav-tab-wrapper">'."\n";
        $count = 0;
        foreach ($tabs as $slug => $title) {
            // Set tab class
            $class = 'nav-tab';
            $isActive = $currentTab ? ($slug == $currentTab) : (0 == $count);
            if ($isActive) {
                $class .= ' nav-tab-active';
            }
            // Set tab link
            $tablink = add_query_arg(array( 'tab' => sanitize_text_field($slug) ));
            if (isset($_GET['settings-updated'])) {
                $tablink = remove_query_arg('settings-updated', $tablink);
            }
            // Output tab
            $html .= '<a href="'.esc_url($tablink).'" class="'.esc_attr($class).'">'.esc_html($title).'</a>'."\n";
            ++$count;
        }
        $html .= '</h2>'."\n";

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
                    'description' => __('Unique Identifier for this website on Splash Servers.', 'splash-wordpress-plugin'),
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
        $settings['products'] = array(
            'title' => __('Products', 'splash-wordpress-plugin'),
            'description' => __('Configuration of Products synchronization.', 'splash-wordpress-plugin'),
            'fields' => array(
                array(
                    'id' => 'cf_product',
                    'label' => __('Custom Fields', 'splash-wordpress-plugin'),
                    'description' => sprintf(__('Enable Custom Fields for Products. Limited to the first %d custom fields.', 'splash-wordpress-plugin'), \Splash\Local\Dictionary\CustomFields::getLimit()),
                    'type' => 'checkbox',
                    'default' => '1'
                ),
                array(
                    'id' => 'custom_fields_limit',
                    'label' => __('Custom Fields Limit', 'splash-wordpress-plugin'),
                    'description' => sprintf(__('Maximum number of custom fields exposed per object type. Raise it if your site defines many custom fields (ACF & similar). Default: %d.', 'splash-wordpress-plugin'), \Splash\Local\Dictionary\CustomFields::MAX_FIELDS),
                    'type' => 'number',
                    'default' => \Splash\Local\Dictionary\CustomFields::MAX_FIELDS,
                    'placeholder' => (string) \Splash\Local\Dictionary\CustomFields::MAX_FIELDS
                ),
            )
        );
        $settings['orders'] = array(
            'title' => __('Orders & Invoices', 'splash-wordpress-plugin'),
            'description' => __('Configuration of Orders & Invoices synchronization.', 'splash-wordpress-plugin'),
            'fields' => array(
                array(
                    'id' => 'item_meta_names',
                    'label' => __('Options in Item Names', 'splash-wordpress-plugin'),
                    'description' => __('Append order item meta data (options) to item names, in parentheses.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                    'default' => 'on'
                ), array(
                    'id' => 'cf_order',
                    'label' => __('Orders Custom Fields', 'splash-wordpress-plugin'),
                    'description' => sprintf(__('Enable Custom Fields for Orders. Limited to the first %d custom fields.', 'splash-wordpress-plugin'), \Splash\Local\Dictionary\CustomFields::getLimit()),
                    'type' => 'checkbox',
                    'default' => '0'
                ), array(
                    'id' => 'cf_invoice',
                    'label' => __('Invoices Custom Fields', 'splash-wordpress-plugin'),
                    'description' => sprintf(__('Enable Custom Fields for Invoices. Limited to the first %d custom fields.', 'splash-wordpress-plugin'), \Splash\Local\Dictionary\CustomFields::getLimit()),
                    'type' => 'checkbox',
                    'default' => '0'
                ), array(
                    'id' => 'sync_order_shipping',
                    'label' => __('Enable Delivery Addresses Synchronization', 'splash-wordpress-plugin'),
                    'description' => __('Expose delivery addresses entered on each order as read-only Address objects. These are the orders own addresses, not the customers account addresses.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                ), array(
                    'id' => 'sync_order_billing',
                    'label' => __('Enable Billing Addresses Synchronization', 'splash-wordpress-plugin'),
                    'description' => __('Expose billing addresses entered on each order as read-only Address objects. These are the orders own addresses, not the customers account addresses.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                ),
            )
        );
        $settings['users'] = array(
            'title' => __('Users', 'splash-wordpress-plugin'),
            'description' => __('Configuration of Customers synchronization.', 'splash-wordpress-plugin'),
            'fields' => array(
                array(
                    'id' => 'no_user_shipping',
                    'label' => __('Disable Shipping Addresses Synchronization', 'splash-wordpress-plugin'),
                    'description' => __('Customers shipping addresses become invisible for Splash: no listing, no reading, no commits.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                ), array(
                    'id' => 'no_user_billing',
                    'label' => __('Disable Billing Addresses Synchronization', 'splash-wordpress-plugin'),
                    'description' => __('Customers billing addresses become invisible for Splash: no listing, no reading, no commits.', 'splash-wordpress-plugin'),
                    'type' => 'checkbox',
                ),
            )
        );
        $settings['contents'] = array(
            'title' => __('Contents', 'splash-wordpress-plugin'),
            'description' => __('Configuration of Users, Posts & Pages synchronization.', 'splash-wordpress-plugin'),
            'fields' => array(
                array(
                    'id' => 'cf_post',
                    'label' => __('Posts Custom Fields', 'splash-wordpress-plugin'),
                    'description' => sprintf(__('Enable Custom Fields for Posts. Limited to the first %d custom fields.', 'splash-wordpress-plugin'), \Splash\Local\Dictionary\CustomFields::getLimit()),
                    'type' => 'checkbox',
                    'default' => '0'
                ), array(
                    'id' => 'cf_page',
                    'label' => __('Pages Custom Fields', 'splash-wordpress-plugin'),
                    'description' => sprintf(__('Enable Custom Fields for Pages. Limited to the first %d custom fields.', 'splash-wordpress-plugin'), \Splash\Local\Dictionary\CustomFields::getLimit()),
                    'type' => 'checkbox',
                    'default' => '0'
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
                    'description' => __('Protocol to use for Webservice communication. Generic PHP SOAP is highly recommended.', 'splash-wordpress-plugin'),
                    'type' => 'select',
                    'options' => array("NuSOAP" => "NuSOAP Librairie", "SOAP" => "Generic PHP SOAP" ),
                    'default' => 'NuSOAP'
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
        $html = \Splash\Local\Admin\SettingsLayout::getCardOpen(__('Informations', 'splash-wordpress-plugin'));
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
        $html .= '</tbody></table>';
        $html .= \Splash\Local\Admin\SettingsLayout::getCardClose();

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

        $html = \Splash\Local\Admin\SettingsLayout::getCardOpen(__('Logs', 'splash-wordpress-plugin'));
        $html .= $htmlLog;
        $html .= \Splash\Local\Admin\SettingsLayout::getCardClose();

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
