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

namespace Splash\Local\Objects\Product\Variants;

use Splash\Core\SplashCore      as Splash;

use WC_Product;
use WC_Product_Attribute;

/**
 * @abstract    Prestashop Product Variants Attribute Values management
 */
trait AttributeGroupTrait
{
    
    /**
     * @abstract    Identify Attribute Group Using Multilang Codes
     * @param       string      $Code   Attribute Group Code
     * @return      int|false           Attribute Group Id
     */
    public function getAttributeGroupByCode($Code)
    {
        //====================================================================//
        // Ensure Code is Valid
        if (!is_string($Code) || empty($Code)) {
            return false;
        }
           
        //====================================================================//
        // Search for this Attribute Group Code
        foreach (wc_get_attribute_taxonomies() as $Group) {
            if (strtolower($Group->attribute_name) == strtolower($Code)) {
                return $Group->attribute_id;
            }
            if (("pa_" . strtolower($Group->attribute_name)) == strtolower($Code)) {
                return $Group->attribute_id;
            }
        }
        
        return false;
    }

    /**
     * @abstract    Identify Attribute Group Using Multilang Code Array
     * @param       string      $Code       Attribute Group Code
     * @param       string      $Name       Attribute Group Name
     * @return      int|false               Attribute Group Id
     */
    public function addAttributeGroup($Code, $Name)
    {
        //====================================================================//
        // Ensure Code is Valid
        if (!is_string($Code) || empty($Code)) {
            return false;
        }
        //====================================================================//
        // Detect Multilang Names
        $RealName =  $this->decodeMultilang($Name);
        //====================================================================//
        // Ensure Names is Scalar
        if (empty($RealName) || !is_scalar($RealName)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to create Attribute Group, No Valid Group Name Provided."
            );
        }
        
        //====================================================================//
        // Create New Attribute
        $AttributeGroupId   =   wc_create_attribute(array(
            "slug"  =>   $Code,
            "name"  =>   $RealName
        ));
        
        //====================================================================//
        // CREATE Attribute Group
        if (is_wp_error($AttributeGroupId)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to create Product Variant Attribute Group : " . $AttributeGroupId->get_error_message()
            );
        }
        
        return $AttributeGroupId;
    }
    
    /**
     * @abstract    Assign Attribute Group to Base Product
     * @param       WC_Product  $Product    WooCommerce Base Product
     * @param       int         $GroupId    Attribute Group Id
     * @param       string      $Code       Attribute Group Code
     * @return      bool
     */
    public function assignAttributeGroup($Product, $GroupId, $Code)
    {
        //====================================================================//
        // Load Product Attributes
        $Attributes =   $Product->get_attributes();
        //====================================================================//
        // Check if Attribute Group Exists
        if (isset($Attributes[wc_attribute_taxonomy_name($Code)])) {
            return true;
        }
        //====================================================================//
        // Create Attribute Group
        $WcAttribute    =   new WC_Product_Attribute();
        $WcAttribute->set_name(wc_attribute_taxonomy_name($Code));
        $WcAttribute->set_id($GroupId);
        $WcAttribute->set_visible(true);
        $WcAttribute->set_variation(true);
        //====================================================================//
        // Assign Attribute Group to Product
        $Attributes[wc_attribute_taxonomy_name($Code)]   =   $WcAttribute;
        $Product->set_attributes($Attributes);
        $Product->save();
               
        return true;
    }
}
