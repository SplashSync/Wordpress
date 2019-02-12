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
use WP_Term;

/**
 * WooCommerce Product Variants Attribute Values management
 */
trait AttributeValueTrait
{
    /**
     * Identify Attribute Value Using Multilang Codes
     *
     * @param string $slug Attribute Group Slug
     * @param string $name Attribute Name/Code
     *
     * @return bool|int Attribute Id (Term Id)
     */
    public function getAttributeByCode($slug, $name)
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
        $search =   term_exists($name, $slug);
        if ($search) {
            return $search["term_id"];
        }

        return false;
    }

    /**
     * Identify Attribute Value Using Multilang Codes
     *
     * @param string $slug  Attribute Group Slug
     * @param string $salue Attribute Value
     *
     * @return bool|int Attribute Id (Term Id)
     */
    public function getAttributeByName($slug, $salue)
    {
        //====================================================================//
        // Ensure Group Id is Valid
        if (!is_scalar($slug) || empty($slug)) {
            return false;
        }
        //====================================================================//
        // Ensure Value is Valid
        if (empty($salue)) {
            return false;
        }
        //====================================================================//
        // Search for this Attribute Value in Taximony
        $wpTerm = $this->getTermByName($slug, $salue);
        if (false != $wpTerm) {
            return $wpTerm->term_id;
        }

        return false;
    }
    
    /**
     * Identify Attribute Value Using Multilang Names Array
     *
     * @param string $slug  Attribute Group Slug
     * @param string $value Attribute Value
     *
     * @return false|int Attribute Id
     */
    public function addAttributeValue($slug, $value)
    {
        //====================================================================//
        // Ensure Slug is Valid
        if (!is_scalar($slug) || empty($slug)) {
            return false;
        }
        //====================================================================//
        // Ensure Value is Valid
        $strValue = $this->decodeMultilang($value, self::getDefaultLanguage());
        if (empty($strValue)) {
            return false;
        }
        $taximony       =   wc_attribute_taxonomy_name(str_replace('pa_', '', $slug));
        //====================================================================//
        // Create Attribute Group if Not in Taximony
        if (! taxonomy_exists($taximony)) {
            $attributeGroupId   =   $this->getAttributeGroupByCode($slug);
            $attributeGroup     =   wc_get_attribute($attributeGroupId);
            register_taxonomy($taximony, $attributeGroup->name);
        }
        //====================================================================//
        // Create New Attribute Value
        $attributeId    =   wp_insert_term($strValue, $taximony, array("slug" => $value));
        //====================================================================//
        // CREATE Attribute Value
        if (is_wp_error($attributeId)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to create Product Variant Attribute Value : "
                . $strValue . " @ " . $taximony . " | " . $attributeId->get_error_message()
            );
        }
        /** @var array $attributeId */
        if (is_array($attributeId)) {
            return $attributeId["term_id"];
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
    public function assignAttribute(&$product, $code, $attributeId)
    {
        //====================================================================//
        // Load Product Attributes
        $attributes =   $product->get_attributes();
        //====================================================================//
        // Check if Attribute Group Exists
        if (!isset($attributes[wc_attribute_taxonomy_name($code)])) {
            return false;
        }
        //====================================================================//
        // Load Attribute Options
        $options    =   $attributes[wc_attribute_taxonomy_name($code)]->get_options();
        //====================================================================//
        // Check if Attribute Option Exists
        if (in_array($attributeId, $options, true)) {
            return true;
        }
        //====================================================================//
        // Load Attribute Class
        $attribute  =   get_term($attributeId);
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
    }

    /**
     * Search Term Using Multilang Codes
     *
     * @param string $slug  Attribute Group Slug
     * @param array  $value Attribute Value
     *
     * @return false|WP_Term
     */
    private function getTermByName($slug, $value)
    {
        //====================================================================//
        // Search for this Attribute Value in Taximony
        $taximony   =   wc_attribute_taxonomy_name(str_replace('pa_', '', $slug));
        $search = get_terms(array(
            'taxonomy'      => array( $taximony ),
            'orderby'       => 'id',
            'order'         => 'ASC',
            'hide_empty'    => false,
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
            if (isset($term->name) && ($term->name == $value)) {
                return $term;
            }
        }

        return false;
    }
}
