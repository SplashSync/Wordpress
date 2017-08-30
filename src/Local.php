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
 *  \remarks	Designed for Splash Module - Wordpress Plugin  
*/

namespace Splash\Local;

use Splash\Core\SplashCore      as Splash;

use Splash\Local\Objects\Core\MultilangTrait as Multilang;

 /**
 *	\class      SplashLocal
 *	\brief      Local Core Management Class
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
    public static function Parameters()
    {
        $Parameters       =     array();

        //====================================================================//
        // Server Identification Parameters
        $Parameters["WsIdentifier"]         =   get_option( "splash_ws_id" , Null);   
        $Parameters["WsEncryptionKey"]      =   get_option( "splash_ws_key" , Null);
        
        //====================================================================//
        // If Debug Mode => Allow Overide of Server Host Address
        if ( (get_option( "splash_advanced_mode" , False)) && !empty(get_option( "splash_server_url" , Null)) ) {
            $Parameters["WsHost"]           =   get_option( "splash_server_url" , Null);
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
    public function Includes()
    {

        
        //====================================================================//
        // When Library is called in server mode ONLY
        //====================================================================//
        if ( SPLASH_SERVER_MODE  )
        {

            /** Setup WordPress environment for Remote Actions */
            define( 'DOING_CRON'    , True );
            
            /** Include the bootstrap for setting up WordPress environment */
            include( dirname(dirname(dirname(dirname( __DIR__ )))) . '/wp-load.php' );
            
            /** Remote Automatic login */
            wp_set_current_user ( get_option( "splash_ws_user" , Null) );
          
            
        }

        //====================================================================//
        // When Library is called in client mode ONLY
        //====================================================================//
        else
        {
            // NOTHING TO DO 
        }


        //====================================================================//
        // When Library is called in both clinet & server mode
        //====================================================================//

        // NOTHING TO DO 
        
        return True;
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
    public static function SelfTest()
    {

        //====================================================================//
        //  Load Local Translation File
        Splash::Translator()->Load("ws");          
        Splash::Translator()->Load("main@local");          
        
        //====================================================================//
        //  Verify - Server Identifier Given
        if ( empty(get_option( "splash_ws_id" , Null)) ) {
            return Splash::Log()->Err("ErrSelfTestNoWsId");
        }        
                
        //====================================================================//
        //  Verify - Server Encrypt Key Given
        if ( empty(get_option( "splash_ws_key" , Null)) ) {
            return Splash::Log()->Err("ErrSelfTestNoWsKey");
        }        
        
        //====================================================================//
        //  Verify - User Selected
        if ( empty(get_option( "splash_ws_user" , Null)) ) {
            return Splash::Log()->Err("ErrSelfTestNoUser");
        }  
        
        if ( is_wp_error(get_user_by( "ID" , get_option( "splash_ws_user" , Null)))) {
            return Splash::Log()->War("ErrSelfTestNoUser");
        }        
        
        /**
         * Check if WooCommerce is active
         **/
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            //====================================================================//
            //  Verify - Prices Exclude Tax Warning
            if ( wc_prices_include_tax() ) {
                Splash::Log()->War("You selected to store Products Prices Including Tax. It is highly recommanded to store Product Price without Tax to work with Splash.");
            }        
        }
        
        
        //====================================================================//
        // Check if company name is defined (first install)
//        if (empty($conf->global->MAIN_INFO_SOCIETE_NOM) || empty($conf->global->MAIN_INFO_SOCIETE_COUNTRY))
//        {
//            return Splash::Log()->Err($langs->trans("WarningMandatorySetupNotComplete"));
//        }

        Splash::Log()->Msg("MsgSelfTestOk");
        return True;
    }       
    
    /**
     *  @abstract   Update Server Informations with local Data
     * 
     *  @param     arrayobject  $Informations   Informations Inputs
     * 
     *  @return     arrayobject
     */
    public function Informations($Informations)
    {
        //====================================================================//
        // Init Response Object
        $Response = $Informations;

        //====================================================================//
        // Company Informations
        $Response->company          =   get_option( "blogname" , "...");
        $Response->address          =   "N/A";
        $Response->zip              =   " ";
        $Response->town             =   " ";
        $Response->country          =   " ";
        $Response->www              =   get_option( "home" , "...");
        $Response->email            =   get_option( "admin_email" , "...");
        $Response->phone            =   " ";
        
        //====================================================================//
        // Server Logo & Images
        $Response->icoraw           =   Splash::File()->ReadFileContents(get_attached_file( get_option( 'site_icon' )));
        $Response->logourl          =   get_site_icon_url();
        
        //====================================================================//
        // Server Informations
        $Response->servertype       =   "Wordpress";
        $Response->serverurl        =   get_option( "siteurl" , "...");
        
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
    public static function TestParameters()
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
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            //====================================================================//
            // WooCommerce Specific Parameters
            $Parameters["Currency"]     = get_woocommerce_currency(); 
            $Parameters["CurrencySymbol"]= get_woocommerce_currency_symbol(); 
        }        
        
        return $Parameters;
    }    
                
}

?>
