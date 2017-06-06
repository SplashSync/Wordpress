<?php

if ( ! defined( 'ABSPATH' ) ) exit;

use Splash\Client\Splash;

class Splash_Wordpress_Settings {

	/**
	 * The single instance of Splash_Settings.
	 * @var 	object
	 * @access      private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access      public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'splash_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
            $this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
            $page = add_options_page( 
                    __( 'Splash Sync', 'splash-wordpress-plugin' ) , 
                    __( 'Splash Sync', 'splash-wordpress-plugin' ) , 
                    'manage_options' , 
                    $this->parent->_token . '_settings' ,  
                    array( $this, 'settings_page' ) 
                );
            add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'wordpress-splash-plugin' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

                $Users = array();
                foreach ( get_users(array( 'role__in' => array('administrator'))) as $User ) {
                    $Users[$User->ID]    =  $User->display_name;
                }         
            
		$settings['connection'] = array(
			'title'					=> __( 'Connection', 'splash-wordpress-plugin' ),
			'description'                           => __( 'These parameters are provided when you create a new Server on our website.', 'splash-wordpress-plugin' ),
			'fields'				=> array(
				array(
					'id' 			=> 'ws_id',
					'label'			=> __( 'Identifier' , 'splash-wordpress-plugin' ),
					'description'           => __( 'Unique Identifier for this website on Splash Servers (8 Char Max). ', 'splash-wordpress-plugin' ),
					'type'			=> 'text',
					'default'		=>  '',
					'placeholder'           =>  ''
				),
				array(
					'id' 			=> 'ws_key',
					'label'			=> __( 'Encryption Key' , 'splash-wordpress-plugin' ),
					'description'           => __( 'Unique Encryption Key', 'splash-wordpress-plugin' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'           =>  ''
				),
				array(
					'id' 			=> 'ws_user',
					'label'			=> __( 'User' , 'splash-wordpress-plugin' ),
					'description'           => __( 'User to use for Webservice transactions', 'splash-wordpress-plugin' ),
					'type'			=> 'select',
					'options'		=> $Users,
				),                            
			)
		);

		$settings['advanced'] = array(
			'title'					=> __( 'Advanced', 'splash-wordpress-plugin' ),
			'description'			=> __( 'These are some advanced parameters. Only use them uppon our request. Warning: your server may not work anymore!', 'splash-wordpress-plugin' ),
			'fields'				=> array(
				array(
					'id' 			=> 'advanced_mode',
					'label'			=> __( 'Enable', 'splash-wordpress-plugin' ),
					'description'           => __( 'Enable advanced mode. ', 'splash-wordpress-plugin' ),
					'type'			=> 'checkbox',
					'default'		=> '0'
				),
				array(
					'id' 			=> 'server_url',
					'label'			=> __( 'Server Url' , 'splash-wordpress-plugin' ),
					'description'           => __( 'Only modify uppon our request! Default value : www.splashsync.com/ws/soap.', 'splash-wordpress-plugin' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'           =>  'www.splashsync.com/ws/soap'
				),
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Plugin Settings' , 'wordpress-plugin-template' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}
                                
				$html .= '</h2>' . "\n";
			}
                        

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";
                        
				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'splash-wordpress-plugin' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

                
                $html   .=  $this->render_selftests();
                $html   .=  $this->render_info();
                $html   .=  $this->render_logs();
                $html   .=  $this->render_debug();
                
		echo $html;
	}

	/**
	 * Main WordPress_Plugin_Template_Settings Instance
	 *
	 * Ensures only one instance of WordPress_Plugin_Template_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WordPress_Plugin_Template()
	 * @return Main WordPress_Plugin_Template_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

	/**
	 * Init Splash Module & Perform Self-tests
	 *
	 * @since 0.0.1
	 */
	public function render_selftests () {
            
            $html  = "";
            
            //====================================================================//
            // Execute Splash Module Selftest
            if ( Splash::SelfTest() ) {
                // Dipslay Notifications
                $html   .=  '<div class="notice notice-success is-dismissible">';
                $html   .=  '<p>' . __( 'Self-Tests Passed !', 'splash-wordpress-plugin' ) . '</p>';
                $html   .=  '</div>';
            } else {
                // Dipslay Notifications
                $html   .=  '<div class="notice notice-error is-dismissible">';
                $html   .=  '<p>' . __( 'Self-Tests Failled... Please Check, your configuration.', 'splash-wordpress-plugin' ) . '</p>';
                $html   .=  '</div>';
                // Dipslay Self-Test Log
                $html   .= "<br><br>";
                
            }
                        
            return $html;
	}

	/**
	 * Render Splash Module Informations Tab
	 *
	 * @since 0.0.1
	 */
	public function render_info_tab () {
            
            $html  = "";
            
            $tab_link = add_query_arg( array( 'tab' => "infos" ) );
            $tab_name = __( 'Informations', 'splash-wordpress-plugin' );

            $html .= '<a href="' . $tab_link . '" class="nav-tab">' . $tab_name . '</a>';
                        
            return $html;
	}        
        
	/**
	 * Render Splash Module Informations
	 *
	 * @since 0.0.1
	 */
	public function render_info () {
            
            $html   =  "<h2>" . __( 'Informations', 'splash-wordpress-plugin' ) . "</h2>";
            $html   .=  '<table class="wp-list-table widefat" width="100%"><tbody>';
            
            //====================================================================//
            // List Objects
            //====================================================================//
            $Objects   =   Splash::Objects();
            $html   .=  '  <tr class="pair">';
            $html   .=  '      <td width="60%">' . __( 'Available Objects', 'splash-wordpress-plugin' ) . '</td>';
            $html   .=  '      <td>';
            foreach ($Objects as $Object) {
                $html   .=      $Object . ", ";                
            }
            $html   .=  '      </td>';
            $html   .=  '  </tr>';
            
            //====================================================================//
            // List Widgets
            //====================================================================//
            $Widgets   =   Splash::Widgets();
            $html   .=  '  <tr class="pair">';
            $html   .=  '      <td width="60%">' . __( 'Available Widgets', 'splash-wordpress-plugin' ) . '</td>';
            $html   .=  '      <td><ul>';
            foreach ($Widgets as $Widget) {
                $html   .=   "<li>" . $Widget . "</li>";
            }
            $html   .=  '      </ul></td>';
            $html   .=  '  </tr>';
            
            //====================================================================//
            // Splash Server Ping
            //====================================================================//
            $html   .=  '  <tr class="impair">';
            $html   .=  '      <td width="60%">' . __( 'Splash Server Ping Test', 'splash-wordpress-plugin' ) . '</td>';
            if ( Splash::Ping() ) {
                $html   .=  '      <td style="color: green;">' . Splash::Log()->GetHtmlLog(True) . '</td>';
            } else {
                $html   .=  '      <td style="color: red;">' . Splash::Log()->GetHtmlLog(True) . '</td>';
            }
            $html   .=  '  </tr>';
            
            //====================================================================//
            // Splash Server Connect
            //====================================================================//
            $html   .=  '  <tr class="impair">';
            $html   .=  '      <td width="60%">' . __( 'Splash Server Connect Test', 'splash-wordpress-plugin' ) . '</td>';
            if ( Splash::Connect() ) {
                $html   .=  '      <td style="color: green;">' . Splash::Log()->GetHtmlLog(True) . '</td>';
            } else {
                $html   .=  '      <td style="color: red;">' . Splash::Log()->GetHtmlLog(True) . '</td>';
            }
            
            $html   .=  '  </tr>';
            $html   .=  '</tbody></table">';

            
            return $html;
	}      
        
        
                
	/**
	 * Render Splash Module Logs
	 *
	 * @since 0.0.1
	 */
	public function render_logs () {
            
            
            $HtmlLog = Splash::Log()->GetHtmlLog(True);
        
            if ( empty($HtmlLog) ) {
                return "";
            } 
            
            $html   =  '<table class="wp-list-table widefat" width="100%"><tbody>';
            $html   .=  "   <tr><td width='100%'>";
            $html   .=          Splash::Log()->GetHtmlLog(True);
            $html   .=  "   </td></tr>";
            $html   .=  '</tbody></table">';
            
            return $html;
        }
            
	/**
	 * Render Splash Module Debug
	 *
	 * @since 0.0.1
	 */
	public function render_debug () {
            
            /**
             * Check if Kint Debugger is active
             **/
            if ( !in_array( 'kint-debugger/kint-debugger.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                return "";
            }
            //====================================================================//
            // Users
//            d( get_users([
//                'role__in'     =>      ['administrator'],
//                ]) );
//            
//            d( get_option( "splash_ws_user" , Null) );
//            d( get_user_by( "ID" , 1 ) );
            
            //====================================================================//
            // Products
//            d( get_posts([
//                'post_type'         =>      'product',
//                'post_status'       =>      [ 'draft' , 'publish' , 'pending', 'private'],
//                ]) );
//            d( get_posts([
//                'post_type'         =>      'attachment',
////                'post_status'       =>      [ 'draft' , 'publish' , 'pending', 'private'],
//                ]) );
            
//            d(wp_upload_dir());
//            d(get_post(16));
//            d( get_post_meta(16) );
//            $Meta = get_post_meta(16);
//            d($Meta["_wp_attachment_metadata"]);
//            d(unserialize($Meta["_wp_attachment_metadata"][0]));
//            d( get_attached_media( 'image' , 2) );
//            d( get_attached_media( 'image' , 16) );
//            d( get_post(2) );
//            d( get_product(14) );
//            d( get_product(14)->get_image_id() );
//            d( get_product(14)->get_gallery_image_ids() );
//            d( get_product(16)->get_gallery_image_ids() );
//            d( get_product(16)->set_gallery_image_ids( array() ) );
//            d( get_product(16)->save() );
//            d( get_product(16)->get_gallery_image_ids() );
            
//            $image_ids = [ "19" , "23" ];
//        $image_ids = wp_parse_id_list( $image_ids );
//
////        if ( $this->get_object_read() ) {
//            $image_ids = array_filter( $image_ids, 'wp_attachment_is_image' );
////        }
//        d($image_ids);
//        d( get_product(16)->set_prop( 'gallery_image_ids', $image_ids ) );
//
//        $this->set_prop( 'gallery_image_ids', $image_ids );            
        
//            d( get_product(14) );
//            d( pll_get_post_translations( 14 ) );
//            d( pll_get_post_language( 14 ) );
//            d( pll_get_post_language( 19 ) );
//            d( get_product(19)->get_tax_class() );
//            d( WC_Tax::get_rates( get_product(19)->get_tax_class() )  );
            
            
//            d( get_post_meta(19) );
//            d( get_post_meta(14) );
            
            
//            d( wp_get_current_user() );
//            echo "<PRE>" . print_r(get_post(10), True) . "</PRE>";
            
//            $Post = get_post(10);
            
//            $Post = get_post(10);
            
//            d( get_post(32) );
//            d( get_post_custom(32) );
//            d( get_post_custom_keys(32) );
//            d( get_post_custom_values(32) );
//            d( get_user_by( "ID" , get_post(32)->post_author) );
//            d(get_posts(['post_type' => 'post']));

//            d(get_posts(['post_type' => 'page']));
//            d(wp_count_posts('page'));
//            d($Post->__get("post_title"));
//
//            d($Post);
//            
//            d(WP_Post::get_instance(10));
//            
//            d(WP_Post::get_instance(10)->post_title);
//            
//            d(get_post_field("post_title", 10 ));

//            d( new WP_Post() );
            
            
//            d(wp_insert_post(["post_type" => "page"]));
//            d(wp_update_post( get_post(10) ));
//            
//            $Result = add_pages_page(
//                __( 'My Plugin Posts Page', 'textdomain' ),
//                __( 'My Plugin', 'textdomain' ),
//                'read',
//                'my-unique-identifier',
//                'wpdocs_my_plugin_function'
//            );
//            d($Result);
            
            return $html;            
        }                
}

