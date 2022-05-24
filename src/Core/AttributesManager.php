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

namespace Splash\Local\Core;

use Splash\Core\SplashCore      as Splash;
use Splash\Local\Objects\Core\MultiLangTrait;
use stdClass;
use WC_Product;
use WC_Product_Attribute;
use WC_Product_Variable;
use WP_Error;
use WP_Term;

/**
 * WooCommerce Product Attributes Manager
 */
class AttributesManager
{
    use MultiLangTrait;

    //====================================================================//
    // ATTRIBUTES GROUPS MANAGEMENT
    //====================================================================//

    /**
     * Identify Attribute Group Using Code
     *
     * @param string $code Attribute Group Code
     *
     * @return null|stdClass Attribute Group ID
     */
    public static function getGroupByCode(string $code): ?stdClass
    {
        //====================================================================//
        // Ensure Code is Valid
        if (empty($code)) {
            return null;
        }
        //====================================================================//
        // Convert Group Code
        $attrCode = trim(remove_accents(strtolower($code)));
        //====================================================================//
        // Search for this Attribute Group Code
        foreach (wc_get_attribute_taxonomies() as $group) {
            //====================================================================//
            // Convert Attribute Group Name
            $groupName = trim(remove_accents(strtolower($group->attribute_name)));
            //====================================================================//
            // Compare Attribute Group Name
            if ($groupName == $attrCode) {
                return wc_get_attribute($group->attribute_id);
            }
            if (("pa_".$groupName) == $attrCode) {
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
     * @return null|false|stdClass Attribute Group ID
     */
    public static function addGroup(string $code, array $names)
    {
        //====================================================================//
        // Ensure Code is Valid
        if (empty($code)) {
            return false;
        }
        //====================================================================//
        // Ensure Names is an Array
        if (empty($names)) {
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
    public static function updateGroup(stdClass $group, array $names): bool
    {
        //====================================================================//
        // Ensure Names is an Array
        if (empty($names)) {
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
    public static function assignGroupToProduct(WC_Product $product, stdClass $group, string $code): bool
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

    /**
     * Identify Custom Attribute Group Using Code
     *
     * @param WC_Product_Variable $parent Parent Product
     * @param string              $code   Attribute Group Code
     *
     * @return null|string
     */
    public static function getGroupNameFromParent(WC_Product_Variable $parent, string $code): ?string
    {
        //====================================================================//
        // Ensure Code is Valid
        if (empty($code)) {
            return null;
        }
        //====================================================================//
        // Load Attributes
        $parentAttrs = $parent->get_attributes();
        if (!is_array($parentAttrs) || !isset($parentAttrs[$code])) {
            return $code;
        }
        $attribute = $parentAttrs[$code];
        if (!($attribute instanceof WC_Product_Attribute)) {
            return $code;
        }

        return $attribute->get_name();
    }

    //====================================================================//
    // ATTRIBUTES VALUES MANAGEMENT
    //====================================================================//

    /**
     * Identify Attribute Value Using Multi-lang Codes
     *
     * @param string $slug Attribute Group Slug
     * @param string $name Attribute Name/Code
     *
     * @return null|WP_Term
     */
    public static function getValueByCode(string $slug, string $name): ?WP_Term
    {
        //====================================================================//
        // Ensure Group Id is Valid
        if (empty($slug)) {
            return null;
        }
        //====================================================================//
        // Ensure Code is Valid
        if (empty($name)) {
            return null;
        }
        //====================================================================//
        // Search for this Attribute Group Code
        $search = term_exists($name, $slug);
        if (!is_array($search)) {
            return null;
        }
        $wpTerm = get_term($search["term_id"]);
        if ($wpTerm instanceof WP_Term) {
            return $wpTerm;
        }

        return null;
    }

    /**
     * Identify Attribute Value Using Multi-lang Codes
     *
     * @param string          $slug  Attribute Group Slug
     * @param string|string[] $value Attribute Value
     *
     * @return null|WP_Term
     */
    public static function getValueByName(string $slug, $value): ?WP_Term
    {
        //====================================================================//
        // Ensure Group Id is Valid
        if (empty($slug)) {
            return null;
        }
        //====================================================================//
        // Ensure Value is Valid
        if (!is_scalar($value) || empty($value)) {
            return null;
        }
        //====================================================================//
        // Search for this Attribute Value in Taxonomy
        $wpTerm = self::getTermByName($slug, trim($value));
        if ($wpTerm instanceof WP_Term) {
            return $wpTerm;
        }

        return null;
    }

    /**
     * Identify Attribute Value Using Multi-lang Names Array
     *
     * @param string $slug  Attribute Group Slug
     * @param array  $names Attribute Group Names (IsoCodes Indexed Names)
     *
     * @return null|WP_Term
     */
    public static function addValue(string $slug, array $names): ?WP_Term
    {
        //====================================================================//
        // Validate Inputs
        if (!self::isValidValue($slug, $names)) {
            return null;
        }
        //====================================================================//
        // Encode Taxonomy Name
        $taxonomy = wc_attribute_taxonomy_name(str_replace('pa_', '', $slug));
        //====================================================================//
        // Create Attribute Group if Not in Taxonomy
        if (! taxonomy_exists($taxonomy)) {
            $attributeGroup = self::getGroupByCode($slug);
            if ($attributeGroup) {
                register_taxonomy($taxonomy, $attributeGroup->name);
            }
        }
        //====================================================================//
        // Create New Attribute Value
        $attributeId = wp_insert_term(
            self::applyMultilangArray("", $names),
            $taxonomy,
            array("slug" => $names[self::getDefaultLanguage()])
        );
        //====================================================================//
        // CREATE Attribute Value
        if (is_wp_error($attributeId) || ($attributeId instanceof WP_Error)) {
            Splash::log()->errTrace(
                " Unable to create Product Attribute Value : "
                .self::applyMultilangArray("", $names)." @ ".$taxonomy
                ." | ".$attributeId->get_error_message()
            );

            return null;
        }
        if (!is_array($attributeId)) {
            return null;
        }
        $wpTerm = get_term($attributeId["term_id"]);
        if ($wpTerm instanceof WP_Term) {
            return $wpTerm;
        }

        return null;
    }

    /**
     * Assign Attribute Group to Base Product
     *
     * @param WC_Product_Variable $product     WooCommerce Base Product
     * @param string              $code        Attribute Group Code
     * @param int                 $attributeId Attribute ID
     *
     * @return bool
     */
    public static function assignValue(WC_Product_Variable $product, string $code, int $attributeId): bool
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
    // ATTRIBUTES VALUES MANAGEMENT (PRIVATE METHODS)
    //====================================================================//

    /**
     * Validate Attribute Value Slug & Names before Write
     *
     * @param string $slug  Attribute Group Slug
     * @param array  $names Attribute Group Names (IsoCodes Indexed Names)
     *
     * @return bool
     */
    private static function isValidValue(string $slug, array $names): bool
    {
        //====================================================================//
        // Ensure Slug is Valid
        if (empty($slug)) {
            return false;
        }
        //====================================================================//
        // Ensure Names is an Array
        if (empty($names)) {
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
     * Search Term Using Multi-lang Codes
     *
     * @param string $slug  Attribute Group Slug
     * @param string $value Attribute Value
     *
     * @return null|WP_Term
     */
    private static function getTermByName(string $slug, string $value): ?WP_Term
    {
        //====================================================================//
        // Search for this Attribute Value in Taxonomy
        $taxonomy = wc_attribute_taxonomy_name(str_replace('pa_', '', $slug));
        $search = get_terms(array(
            'taxonomy' => array( $taxonomy ),
            'orderby' => 'id',
            'order' => 'ASC',
            'hide_empty' => false,
        ));
        //====================================================================//
        // Check Results
        if (!is_array($search) || (count($search) <= 0)) {
            return null;
        }
        //====================================================================//
        // Search in Results
        /** @var WP_Term $term */
        foreach ($search as $term) {
            //====================================================================//
            // Safety Check
            if (empty($term->name)) {
                continue;
            }
            //====================================================================//
            // Search for Value Name in default Language
            if (self::encodeMultiLang($term->name) == $value) {
                return $term;
            }
        }

        return null;
    }
}
