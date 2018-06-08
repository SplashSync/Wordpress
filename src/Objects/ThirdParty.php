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
 *  @copyright 2015-2017 Splash Sync
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 *
 **/
                    
namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;

use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * @abstract    Wordpress Customer Object
 */
class ThirdParty extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    
    // Post Fields
    use \Splash\Local\Objects\Post\CustomTrait;                 // Custom Fields
    
    // User Fields
    use \Splash\Local\Objects\Users\CRUDTrait;
    use \Splash\Local\Objects\Users\ObjectListTrait;
    use \Splash\Local\Objects\Users\CoreTrait;
    use \Splash\Local\Objects\Users\MainTrait;
    use \Splash\Local\Objects\Users\MetaTrait;
    use \Splash\Local\Objects\Users\AddressTrait;
    use \Splash\Local\Objects\Users\HooksTrait;
    
    //====================================================================//
    // Object Definition Parameters
    //====================================================================//
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME            =  "ThirdParty";
    
    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION     =  "Woocommerce Customer Object";
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-user";
      
    /**
     *  Object Synchronization Recommended Configuration
     */
    protected static $ENABLE_PUSH_CREATED       =  false;        // Enable Creation Of New Local Objects when Not Existing
       
    //====================================================================//
    // General Class Variables
    //====================================================================//
    
    var $User_Role = "customer";
    
    /**
     *      @abstract   Return name of this Object Class
     */
    public function getName()
    {
        /**
         * Check if WooCommerce is active
         **/
        if (!Splash::local()->hasWooCommerce()) {
            return __("User");
        }
        return self::Trans(static::$NAME);
    }

    /**
     *      @abstract   Return Description of this Object Class
     */
    public function getDesc()
    {
        /**
         * Check if WooCommerce is active
         **/
        if (!Splash::local()->hasWooCommerce()) {
            return "Wordpress User Object";
        }
        return self::Trans(static::$DESCRIPTION);
    }
}
