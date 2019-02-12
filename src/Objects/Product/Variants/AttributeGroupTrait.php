<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Product\Variants;

use Splash\Core\SplashCore      as Splash;
use WC_Product;
use WC_Product_Attribute;

/**
 * Prestashop Product Variants Attribute Values management
 */
trait AttributeGroupTrait
{
    /**
     * Identify Attribute Group Using Multilang Codes
     *
     * @param string $code Attribute Group Code
     *
     * @return false|int Attribute Group Id
     */
    public function getAttributeGroupByCode($code)
    {
        //====================================================================//
        // Ensure Code is Valid
        if (!is_string($code) || empty($code)) {
            return false;
        }
           
        //====================================================================//
        // Search for this Attribute Group Code
        foreach (wc_get_attribute_taxonomies() as $group) {
            if (strtolower($group->attribute_name) == strtolower($code)) {
                return $group->attribute_id;
            }
            if (("pa_" . strtolower($group->attribute_name)) == strtolower($code)) {
                return $group->attribute_id;
            }
        }
        
        return false;
    }

    /**
     * Identify Attribute Group Using Multilang Code Array
     *
     * @param string $code Attribute Group Code
     * @param string $name Attribute Group Name
     *
     * @return false|int Attribute Group Id
     */
    public function addAttributeGroup($code, $name)
    {
        //====================================================================//
        // Ensure Code is Valid
        if (!is_string($code) || empty($code)) {
            return false;
        }
        //====================================================================//
        // Detect Multilang Names
        $realName =  $this->decodeMultilang($name);
        //====================================================================//
        // Ensure Names is Scalar
        if (empty($realName) || !is_scalar($realName)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to create Attribute Group, No Valid Group Name Provided."
            );
        }
        
        //====================================================================//
        // Create New Attribute
        $attributeGroupId   =   wc_create_attribute(array(
            "slug"  =>   $code,
            "name"  =>   $realName
        ));
        
        //====================================================================//
        // CREATE Attribute Group
        if (is_wp_error($attributeGroupId)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to create Product Variant Attribute Group : " . $attributeGroupId->get_error_message()
            );
        }
        
        return $attributeGroupId;
    }
    
    /**
     * Assign Attribute Group to Base Product
     *
     * @param WC_Product $product WooCommerce Base Product
     * @param int        $groupId Attribute Group Id
     * @param string     $code    Attribute Group Code
     *
     * @return bool
     */
    public function assignAttributeGroup($product, $groupId, $code)
    {
        //====================================================================//
        // Load Product Attributes
        $attributes =   $product->get_attributes();
        //====================================================================//
        // Check if Attribute Group Exists
        if (isset($attributes[wc_attribute_taxonomy_name($code)])) {
            return true;
        }
        //====================================================================//
        // Create Attribute Group
        $wcAttribute    =   new WC_Product_Attribute();
        $wcAttribute->set_name(wc_attribute_taxonomy_name($code));
        $wcAttribute->set_id($groupId);
        $wcAttribute->set_visible(true);
        $wcAttribute->set_variation(true);
        //====================================================================//
        // Assign Attribute Group to Product
        $attributes[wc_attribute_taxonomy_name($code)]   =   $wcAttribute;
        $product->set_attributes($attributes);
        $product->save();
               
        return true;
    }
}
