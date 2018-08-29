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
use WP_Term;

/**
 * @abstract    WooCommerce Product Variants Attribute Values management
 */
trait AttributeValueTrait
{
    
    /**
     * @abstract    Identify Attribute Value Using Multilang Codes
     * @param       string      $Slug       Attribute Group Slug
     * @param       string      $Name       Attribute Name/Code
     * @return      int|bool                Attribute Id (Term Id)
     */
    public function getAttributeByCode($Slug, $Name)
    {
        //====================================================================//
        // Ensure Group Id is Valid
        if (!is_scalar($Slug) || empty($Slug)) {
            return false;
        }
        //====================================================================//
        // Ensure Code is Valid
        if (empty($Name)) {
            return false;
        }
        //====================================================================//
        // Search for this Attribute Group Code
        $Search =   term_exists($Name, $Slug);
        if ($Search) {
            return $Search["term_id"];
        }
        return false;
    }

    /**
     * @abstract    Identify Attribute Value Using Multilang Codes
     * @param       string      $Slug       Attribute Group Slug
     * @param       string      $Value      Attribute Value
     * @return      int|bool               Attribute Id (Term Id)
     */
    public function getAttributeByName($Slug, $Value)
    {
        //====================================================================//
        // Ensure Group Id is Valid
        if (!is_scalar($Slug) || empty($Slug)) {
            return false;
        }
        //====================================================================//
        // Ensure Value is Valid
        if (empty($Value)) {
            return false;
        }
        //====================================================================//
        // Search for this Attribute Value in Taximony
        $WpTerm = $this->getTermByName($Slug, $Value);
        if ($WpTerm != false) {
            return $WpTerm->term_id;
        }
        return false;
    }

    /**
     * @abstract    Search Term Using Multilang Codes
     * @param       string      $Slug       Attribute Group Slug
     * @param       array       $Value      Attribute Value
     * @return      WP_Term|false
     */
    private function getTermByName($Slug, $Value)
    {
        //====================================================================//
        // Search for this Attribute Value in Taximony
        $Taximony   =   wc_attribute_taxonomy_name(str_replace('pa_', '', $Slug));
        $Search = get_terms(array(
            'taxonomy'      => array( $Taximony ),
            'orderby'       => 'id',
            'order'         => 'ASC',
            'hide_empty'    => false,
        ));
        //====================================================================//
        // Check Results
        if (!is_array($Search) || (count($Search) <= 0)) {
            return false;
        }
        //====================================================================//
        // Search in Results
        /** @var WP_Term $Term */
        foreach ($Search as $Term) {
            if (isset($Term->name) && ($Term->name == $this->decodeMultilang($Value))) {
                return $Term;
            }
        }
        return false;
    }
    
    /**
     * @abstract    Identify Attribute Value Using Multilang Names Array
     * @param       string      $Slug       Attribute Group Slug
     * @param       string      $Value      Attribute Value
     * @return      int|false               Attribute Id
     */
    public function addAttributeValue($Slug, $Value)
    {
        //====================================================================//
        // Ensure Slug is Valid
        if (!is_scalar($Slug) || empty($Slug)) {
            return false;
        }
        //====================================================================//
        // Ensure Value is Valid
        $StrValue = $this->decodeMultilang($Value);
        if (empty($StrValue)) {
            return false;
        }
        $Taximony       =   wc_attribute_taxonomy_name(str_replace('pa_', '', $Slug));
        //====================================================================//
        // Create Attribute Group if Not in Taximony
        if (! taxonomy_exists($Taximony)) {
            $AttributeGroupId   =   $this->getAttributeGroupByCode($Slug);
            $AttributeGroup     =   wc_get_attribute($AttributeGroupId);
            register_taxonomy($Taximony, $AttributeGroup->name);
        }
    
        //====================================================================//
        // Create New Attribute Value
        $AttributeId    =   wp_insert_term($StrValue, $Taximony);
        //====================================================================//
        // CREATE Attribute Value
        if (is_wp_error($AttributeId)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to create Product Variant Attribute Value : "
                . $StrValue . " @ " . $Taximony . " | " . $AttributeId->get_error_message()
            );
        }
        /** @var array $AttributeId */
        if (is_array($AttributeId)) {
            return $AttributeId["term_id"];
        }
        return false;
    }
    
    /**
     * @abstract    Assign Attribute Group to Base Product
     * @param       WC_Product  $Product        WooCommerce Base Product
     * @param       string      $Code           Attribute Group Code
     * @param       int         $AttributeId    Attribute Id
     * @return      bool
     */
    public function assignAttribute(&$Product, $Code, $AttributeId)
    {
        //====================================================================//
        // Load Product Attributes
        $Attributes =   $Product->get_attributes();
        //====================================================================//
        // Check if Attribute Group Exists
        if (!isset($Attributes[wc_attribute_taxonomy_name($Code)])) {
            return false;
        }
        //====================================================================//
        // Load Attribute Options
        $Options    =   $Attributes[wc_attribute_taxonomy_name($Code)]->get_options();
        //====================================================================//
        // Check if Attribute Option Exists
        if (in_array($AttributeId, $Options)) {
            return true;
        }
        //====================================================================//
        // Load Attribute Class
        $Attribute  =   get_term($AttributeId);
        //====================================================================//
        // Add Attribute Option
        wp_set_post_terms(
            $Product->get_id(),
            $Attribute->name,
            wc_attribute_taxonomy_name($Code),
            true
        );
        //====================================================================//
        // Update Product Attributes
        $Attributes[wc_attribute_taxonomy_name($Code)]
                ->set_options(array_merge($Options, [$AttributeId]));
        $Product->set_attributes($Attributes);
    }
}
