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
use Splash\Models\Objects\ObjectsTrait;

/**
 * @abstract    WooCommerce Customer Address Object
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Address extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;
    
    // Core Fields
    use \Splash\Local\Objects\Core\WooCommerceObjectTrait;      // Trigger WooCommerce Module Activation
    
    // User Fields
    use \Splash\Local\Objects\Users\HooksTrait;
    
    // Address Traits
    use \Splash\Local\Objects\Address\CRUDTrait;
    use \Splash\Local\Objects\Address\ObjectListTrait;
    use \Splash\Local\Objects\Address\UserTrait;
    use \Splash\Local\Objects\Address\MainTrait;
    
    //====================================================================//
    // Object Definition Parameters
    //====================================================================//
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME            =  "Customer Address";
    
    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION     =  "Wordpress Customer Address Object";
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-envelope-o";

    /**
     *  Object Synchronization Limitations
     *
     *  This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
     */
    protected static $ALLOW_PUSH_CREATED         =  false;       // Allow Creation Of New Local Objects
    protected static $ALLOW_PUSH_UPDATED         =  true;        // Allow Update Of Existing Local Objects
    protected static $ALLOW_PUSH_DELETED         =  false;       // Allow Delete Of Existing Local Objects
    
    /**
     *  Object Synchronization Recommended Configuration
     */
    // Enable Creation Of New Local Objects when Not Existing
    protected static $ENABLE_PUSH_CREATED       =  false;
    // Enable Update Of Existing Local Objects when Modified Remotly
    protected static $ENABLE_PUSH_UPDATED       =  true;
    // Enable Delete Of Existing Local Objects when Deleted Remotly
    protected static $ENABLE_PUSH_DELETED       =  false;
        
    //====================================================================//
    // General Class Variables
    //====================================================================//
    
    protected static $Delivery   =   "shipping";
    protected static $Billing    =   "billing";
    
    protected $AddressType=   null;
    
    /**
     * @abstract    Decode User Id
     *
     * @param       string      $Id               Encoded User Address Id
     *
     * @return      string|null
     */
    protected function decodeUserId($Id)
    {
        //====================================================================//
        // Decode Delivery Ids
        if (strpos($Id, static::$Delivery . "-") === 0) {
            $this->AddressType  = static::$Delivery;
            return substr($Id, strlen(static::$Delivery . "-"));
        }
        //====================================================================//
        // Decode Billing Ids
        if (strpos($Id, static::$Billing . "-") === 0) {
            $this->AddressType  = static::$Billing;
            return substr($Id, strlen(static::$Billing . "-"));
        }
        return null;
    }
    
    /**
     * @abstract    Encode User Delivery Id
     *
     * @param       string      $Id               Encoded User Address Id
     *
     * @return      string
     */
    public static function encodeDeliveryId($Id)
    {
        return static::$Delivery . "-" . $Id;
    }

    /**
     * @abstract    Encode User Billing Id
     *
     * @param       string      $Id               Encoded User Address Id
     *
     * @return      string
     */
    public static function encodeBillingId($Id)
    {
        return static::$Billing . "-" . $Id;
    }
    
    /**
     * @abstract    Encode User Address Field Id
     *
     * @param       string      $Id               Encoded User Address Id
     *
     * @return      string
     */
    protected function encodeFieldId($Id, $Mode = null)
    {
        if ($Mode) {
            return $Mode . "_" . $Id;
        }
        return $this->AddressType . "_" . $Id;
    }
}
