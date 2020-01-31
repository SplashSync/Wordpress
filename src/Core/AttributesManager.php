<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Core;

use Splash\Core\SplashCore      as Splash;
use stdClass;
use WC_Product;
use WC_Product_Attribute;
use WP_Error;
use WP_Term;

/**
 * WooCommerce Product Attributes Manager
 */
class AttributesManager
{
    use \Splash\Local\Objects\Core\MultilangTrait;

    //====================================================================//
    // ATTRIBUTES GROUPS MAMAGEMENT
    //====================================================================//

    /**
     * Identify Attribute Group Using Code
     *
     * @param string $code Attribute Group Code
     *
     * @return null|stdClass Attribute Group Id
     */
    public static function getGroupByCode($code)
    {
        //====================================================================//
        // Ensure Code is Valid
        if (!is_string($code) || empty($code)) {
            return null;
        }
        //====================================================================//
        // Search for this Attribute Group Code
        foreach (wc_get_attribute_taxonomies() as $group) {
            if (strtolower($group->attribute_name) == strtolower($code)) {
                return wc_get_attribute($group->attribute_id);
            }
            if (("pa_".strtolower($group->attribute_name)) == strtolower($code)) {
                return wc_get_attribute($group->attribute_id);
            }
        }

        return null;
    }

    /**
     * Create a New Attribute Group with Code & Names Array
     *
     * @param string $code  Attribute Group Code
     * @param array  $names Attribute Group Names (IsoCodes Indexed Names)
     *
     * @return null|false|stdClass Attribute Group Id
     */
    public static function addGroup($code, $names)
    {
        //====================================================================//
        // Ensure Code is Valid
        if (!is_string($code) || empty($code)) {
            return false;
        }
        //====================================================================//
        // Ensure Names is an Array
        if (empty($names) || !is_array($names)) {
            return Splash::log()->errTrace("Unable to create Attribute Group, No Valid Group Names Provided.");
        }
        //====================================================================//
        // Create New Attribute
        $attributeGroupId = wc_create_attribute(array(
            "slug" => $code,
            "name" => self::applyMultilangArray("", $names)
        ));
        //====================================================================//
        // CREATE Attribute Group
        if (is_wp_error($attributeGroupId) || ($attributeGroupId instanceof WP_Error)) {
            return Splash::log()->errTrace(
                "Unable to create Product Variant Attribute Group : "
                .$attributeGroupId->get_error_message()
            );
        }

        return wc_get_attribute($attributeGroupId);
    }

    /**
     * Update Attribute Group with Names Array
     *
     * @param stdClass $group Attribute Group
     * @param array    $names Attribute Group Names (IsoCodes Indexed Names)
     *
     * @return bool
     */
    public static function updateGroup($group, $names)
    {
        //====================================================================//
        // Ensure Names is an Array
        if (empty($names) || !is_array($names)) {
            return Splash::log()->errTrace("Unable to create Attribute Group, No Valid Group Names Provided.");
        }
        //====================================================================//
        // Update Available Languages Names
        $newGroupName = self::applyMultilangArray($group->name, $names);
        //====================================================================//
        // No Changes => Exit
        if ($newGroupName == $group->name) {
            return true;
        }
        //====================================================================//
        // Update Attribute
        $attributeGroupId = wc_update_attribute($group->id, array(
            "slug" => $group->slug,
            "name" => $newGroupName
        ));
        if (is_wp_error($attributeGroupId)) {
            return Splash::log()->errTrace(
                "Unable to Update Product Variant Attribute Group : "
                .$attributeGroupId->get_error_message()
            );
        }

        return true;
    }

    /**
     * Assign Attribute Group to Base Product
     *
     * @param WC_Product $product WooCommerce Base Product
     * @param stdClass   $group   Attribute Group
     * @param string     $code    Attribute Group Code
     *
     * @return bool
     */
    public static function assignGroupToProduct($product, $group, $code)
    {
        //====================================================================//
        // Load Product Attributes
        $attributes = $product->get_attributes();
        //====================================================================//
        // Check if Attribute Group Exists
        if (isset($attributes[wc_attribute_taxonomy_name($code)])) {
            return true;
        }
        //====================================================================//
        // Create Attribute Group
        $wcAttribute = new WC_Product_Attribute();
        $wcAttribute->set_name(wc_attribute_taxonomy_name($code));
        $wcAttribute->set_id($group->id);
        $wcAttribute->set_visible(true);
        $wcAttribute->set_variation(true);
        //====================================================================//
        // Assign Attribute Group to Product
        $attributes[wc_attribute_taxonomy_name($code)] = $wcAttribute;
        $product->set_attributes($attributes);
        $product->save();

        return true;
    }

    //====================================================================//
    // ATTRIBUTES VALUES MAMAGEMENT
    //====================================================================//

    /**
     * Identify Attribute Value Using Multilang Codes
     *
     * @param string $slug Attribute Group Slug
     * @param string $name Attribute Name/Code
     *
     * @return bool|WP_Term
     */
    public static function getValueByCode($slug, $name)
    {
        //====================================================================//
        // Ensure Group Id is Valid
        if (!is_scalar($slug) || empty($slug)) {
            return false;
        }
        //====================================================================//
        // Ensure Code is Valid
        if (empty($name)) {
            return false;
        }
        //====================================================================//
        // Search for this Attribute Group Code
        $search = term_exists($name, $slug);
        if (!is_array($search)) {
            return false;
        }
        $wpTerm = get_term($search["term_id"]);
        if ($wpTerm instanceof WP_Term) {
            return $wpTerm;
        }

        return false;
    }

    /**
     * Identify Attribute Value Using Multilang Codes
     *
     * @param string $slug  Attribute Group Slug
     * @param string $value Attribute Value
     *
     * @return false|WP_Term
     */
    public static function getValueByName($slug, $value)
    {
        //====================================================================//
        // Ensure Group Id is Valid
        if (!is_scalar($slug) || empty($slug)) {
            return false;
        }
        //====================================================================//
        // Ensure Value is Valid
        if (empty($value)) {
            return false;
        }
        //====================================================================//
        // Search for this Attribute Value in Taximony
        $wpTerm = self::getTermByName($slug, $value);
        if ($wpTerm instanceof WP_Term) {
            return $wpTerm;
        }

        return false;
    }

    /**
     * Identify Attribute Value Using Multilang Names Array
     *
     * @param string $slug  Attribute Group Slug
     * @param array  $names Attribute Group Names (IsoCodes Indexed Names)
     *
     * @return false|WP_Term
     */
    public static function addValue($slug, $names)
    {
        //====================================================================//
        // Validate Inputs
        if (false == self::isValidValue($slug, $names)) {
            return false;
        }
        //====================================================================//
        // Encode Taximony Name
        $taximony = wc_attribute_taxonomy_name(str_replace('pa_', '', $slug));
        //====================================================================//
        // Create Attribute Group if Not in Taximony
        if (! taxonomy_exists($taximony)) {
            $attributeGroup = self::getGroupByCode($slug);
            if ($attributeGroup) {
                register_taxonomy($taximony, $attributeGroup->name);
            }
        }
        //====================================================================//
        // Create New Attribute Value
        $attributeId = wp_insert_term(
            self::applyMultilangArray("", $names),
            $taximony,
            array("slug" => $names[self::getDefaultLanguage()])
        );
        //====================================================================//
        // CREATE Attribute Value
        if (is_wp_error($attributeId) || ($attributeId instanceof WP_Error)) {
            return Splash::log()->errTrace(
                " Unable to create Product Attribute Value : "
                .self::applyMultilangArray("", $names)." @ ".$taximony
                ." | ".$attributeId->get_error_message()
            );
        }
        if (!is_array($attributeId)) {
            return false;
        }
        $wpTerm = get_term($attributeId["term_id"]);
        if ($wpTerm instanceof WP_Term) {
            return $wpTerm;
        }

        return false;
    }

    /**
     * Assign Attribute Group to Base Product
     *
     * @param WC_Product $product     WooCommerce Base Product
     * @param string     $code        Attribute Group Code
     * @param int        $attributeId Attribute Id
     *
     * @return bool
     */
    public static function assignValue(&$product, $code, $attributeId)
    {
        //====================================================================//
        // Load Product Attributes
        $attributes = $product->get_attributes();
        //====================================================================//
        // Check if Attribute Group Exists
        if (!isset($attributes[wc_attribute_taxonomy_name($code)])) {
            return false;
        }
        //====================================================================//
        // Load Attribute Options
        $options = $attributes[wc_attribute_taxonomy_name($code)]->get_options();
        //====================================================================//
        // Check if Attribute Option Exists
        if (in_array($attributeId, $options, true)) {
            return true;
        }
        //====================================================================//
        // Load Attribute Class
        $attribute = get_term($attributeId);
        if (!($attribute instanceof WP_Term)) {
            return false;
        }
        //====================================================================//
        // Add Attribute Option
        wp_set_post_terms(
            $product->get_id(),
            $attribute->name,
            wc_attribute_taxonomy_name($code),
            true
        );
        //====================================================================//
        // Update Product Attributes
        $attributes[wc_attribute_taxonomy_name($code)]
            ->set_options(array_merge($options, array($attributeId)));
        $product->set_attributes($attributes);

        return true;
    }

    //====================================================================//
    // ATTRIBUTES VALUES MAMAGEMENT (PRIVATE METHODS)
    //====================================================================//

    /**
     * Validate Attribute Value Slug & Names before Write
     *
     * @param string $slug  Attribute Group Slug
     * @param array  $names Attribute Group Names (IsoCodes Indexed Names)
     *
     * @return bool
     */
    private static function isValidValue($slug, $names)
    {
        //====================================================================//
        // Ensure Slug is Valid
        if (!is_scalar($slug) || empty($slug)) {
            return false;
        }
        //====================================================================//
        // Ensure Names is an Array
        if (empty($names) || !is_array($names)) {
            return Splash::log()->errTrace("Unable to create Attribute, No Valid Names Provided.");
        }
        //====================================================================//
        // Ensure Default Name is scalar
        if (!isset($names[self::getDefaultLanguage()]) || !is_scalar($names[self::getDefaultLanguage()])) {
            return Splash::log()->errTrace("Unable to create Attribute, No Default Name Provided.");
        }

        return true;
    }

    /**
     * Search Term Using Multilang Codes
     *
     * @param string $slug  Attribute Group Slug
     * @param string $value Attribute Value
     *
     * @return false|WP_Term
     */
    private static function getTermByName($slug, $value)
    {
        //====================================================================//
        // Search for this Attribute Value in Taximony
        $taximony = wc_attribute_taxonomy_name(str_replace('pa_', '', $slug));
        $search = get_terms(array(
            'taxonomy' => array( $taximony ),
            'orderby' => 'id',
            'order' => 'ASC',
            'hide_empty' => false,
        ));
        //====================================================================//
        // Check Results
        if (!is_array($search) || (count($search) <= 0)) {
            return false;
        }
        //====================================================================//
        // Search in Results
        /** @var WP_Term $term */
        foreach ($search as $term) {
            //====================================================================//
            // Safety Check
            if (!isset($term->name)) {
                continue;
            }
            //====================================================================//
            // Search for Value Name in default Language
            if (self::encodeMultilang($term->name) == $value) {
                return $term;
            }
        }

        return false;
    }
}
