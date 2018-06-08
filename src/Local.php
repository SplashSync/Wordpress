<?php
/*
 * Copyright (C) 2011-2014  Bernard Paquier       <bernard.paquier@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 *
 *  \Id 	$Id: osws-local-Main.class.php 136 2014-10-12 22:33:28Z Nanard33 $
 *  \version    $Revision: 136 $
 *  \date       $LastChangedDate: 2014-10-13 00:33:28 +0200 (lun. 13 oct. 2014) $
 *  \ingroup    Splash - OpenSource Synchronisation Service
 *  \brief      Core Local Server Definition Class
 *  \class      SplashLocal
 *  \remarks    Designed for Splash Module - Wordpress Plugin
*/

namespace Splash\Local;

use ArrayObject;

use Splash\Core\SplashCore      as Splash;

use Splash\Local\Objects\Core\MultilangTrait as Multilang;

 /**
 *  \class      SplashLocal
 *  \brief      Local Core Management Class
 */
class Local
{

//====================================================================//
// *******************************************************************//
//  MANDATORY CORE MODULE LOCAL FUNCTIONS
// *******************************************************************//
//====================================================================//
    
    /**
     *      @abstract       Return Local Server Parameters as Aarray
     *
     *      THIS FUNCTION IS MANDATORY
     *
     *      This function called on each initialisation of the module
     *
     *      Result must be an array including mandatory parameters as strings
     *         ["WsIdentifier"]         =>>  Name of Module Default Language
     *         ["WsEncryptionKey"]      =>>  Name of Module Default Language
     *         ["DefaultLanguage"]      =>>  Name of Module Default Language
     *
     *      @return         array       $parameters
     */
    public static function parameters()
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
     *      @abstract       Include Local Includes Files
     *
     *      Include here any local files required by local functions.
     *      This Function is called each time the module is loaded
     *
     *      There may be differents scenarios depending if module is
     *      loaded as a library or as a NuSOAP Server.
     *
     *      This is triggered by global constant SPLASH_SERVER_MODE.
     *
     *      @return         bool
     */
    public function includes()
    {
        //====================================================================//
        // When Library is called in server mode ONLY
        //====================================================================//
        if (SPLASH_SERVER_MODE && !defined('DOING_CRON')) {
            
            /** Setup WordPress environment for Remote Actions */
            define('DOING_CRON', true);
            /** Include the bootstrap for setting up WordPress environment */
            include(dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php');
            /** Remote Automatic login */
            wp_set_current_user(get_option("splash_ws_user", null));
        //====================================================================//
        // When Library is called in client mode ONLY
        //====================================================================//
        } else {
            // NOTHING TO DO
        }

        //====================================================================//
        // When Library is called in both clinet & server mode
        //====================================================================//

        // NOTHING TO DO
        
        return true;
    }
           
    /**
     *      @abstract       Return Local Server Self Test Result
     *
     *      THIS FUNCTION IS MANDATORY
     *
     *      This function called during Server Validation Process
     *
     *      We recommand using this function to validate all functions or parameters
     *      that may be required by Objects, Widgets or any other module specific action.
     *
     *      Use Module Logging system & translation tools to return test results Logs
     *
     *      @return         bool    global test result
     */
    public static function selfTest()
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
         **/
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
        if (defined('WP_DEBUG') && WP_DEBUG) {
            Splash::log()->war("Current Server Url : " . Splash::ws()->getServerInfos()["ServerHost"]);
            Splash::log()->war("Current Server Path: " . Splash::ws()->getServerInfos()["ServerPath"]);
        }
        
        Splash::log()->msg("MsgSelfTestOk");
        return true;
    }
    
    /**
     * @abstract    Update Server Informations with local Data
     * @param       ArrayObject     $Informations       Informations Inputs
     * @return      ArrayObject
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
     *      @abstract       Return Local Server Test Parameters as Aarray
     *
     *      THIS FUNCTION IS OPTIONNAL - USE IT ONLY IF REQUIRED
     *
     *      This function called on each initialisation of module's tests sequences.
     *      It's aim is to overide general Tests settings to be adjusted to local system.
     *
     *      Result must be an array including parameters as strings or array.
     *
     *      @see Splash\Tests\Tools\ObjectsCase::settings for objects tests settings
     *
     *      @return         array       $parameters
     */
    public static function testParameters()
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
         **/
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            //====================================================================//
            // WooCommerce Specific Parameters
            $Parameters["Currency"]         = get_woocommerce_currency();
            $Parameters["CurrencySymbol"]   = get_woocommerce_currency_symbol();
            $Parameters["PriceBase"]        = wc_prices_include_tax() ? "TTC" : "HT";
        }
        
        return $Parameters;
    }
    
    /**
     *      @abstract       Return Local Server Test Sequences as Aarray
     *
     *      THIS FUNCTION IS OPTIONNAL - USE IT ONLY IF REQUIRED
     *
     *      This function called on each initialization of module's tests sequences.
     *      It's aim is to list different configurations for testing on local system.
     *
     *      If Name = List, Result must be an array including list of Sequences Names.
     *
     *      If Name = ASequenceName, Function will Setup Sequence on Local System.
     *
     *      @return     array|null       $Sequences
     */
    public static function testSequences($Name = null)
    {
        switch ($Name) {
            case "ProductVATIncluded":
                update_option("woocommerce_prices_include_tax", "yes");
                update_option("splash_multilang", "on");
                return null;
                
            case "Monolangual":
                update_option("woocommerce_prices_include_tax", "no");
                update_option("splash_multilang", "off");
                return null;
            
            case "Multilangual":
                update_option("woocommerce_prices_include_tax", "no");
                update_option("splash_multilang", "on");
                return null;
            
            case "List":
                return array( "ProductVATIncluded" ,"Monolangual", "Multilangual" );
        }
    }
           
    
//====================================================================//
// *******************************************************************//
//  SPECIALS MODULE LOCAL FUNCTIONS
// *******************************************************************//
//====================================================================//
    
    /**
     * @abstract    Check if WooCommerce Plugin is Active
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
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
}
