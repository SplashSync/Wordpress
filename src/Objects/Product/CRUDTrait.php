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

namespace Splash\Local\Objects\Product;

use Splash\Core\SplashCore      as Splash;

/**
 * Wordpress Page, Post, Product CRUD Functions
 */
trait CRUDTrait
{
    use \Splash\Local\Objects\Post\CRUDTrait;                   // Objects CRUD
    
    /**
     * @abstract    Load Request Object
     *
     * @param       int   $Id               Object id
     *
     * @return      mixed
     */
    public function load($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $Post           =       get_post($Id);
        //====================================================================//
        // Load WooCommerce Product Object
        $Product        =       wc_get_product($Id);
        if ($Product) {
            $this->Product  =       $Product;
        }
        if (is_wp_error($Post)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load " . self::$NAME . " (" . $Id . ")."
            );
        }
        //====================================================================//
        // Load WooCommerce Parent Product Object
        $this->loadParent();
        return $Post;
    }
    
    /**
     * @abstract    Create a New Product Variation
     * @return      object|false
     */
    public function create()
    {
        //====================================================================//
        // Check is New Product is Variant Product
        if (!isset($this->in["attributes"]) || empty($this->in["attributes"])) {
            $this->in["post_title"] =       $this->in["base_title"];
            return $this->createPost();
        }
        //====================================================================//
        // Check Required Fields
        if (empty($this->in["base_title"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "base_title");
        }
        //====================================================================//
        // Search for Base Product (Same Title)
        $BaseProductId  =   $this->getBaseProduct($this->in["base_title"]);
        //====================================================================//
        // Base Product Not Found
        if (!$BaseProductId) {
            $this->lock("onVariantCreate");
            $this->in["post_title"] =       $this->in["base_title"];
            $BaseProduct            =       $this->createPost();
            $BaseProductId          =       $BaseProduct->ID;
            wp_set_object_terms($BaseProductId, 'variable', 'product_type');
            $this->unLock("onVariantCreate");
        }
        //====================================================================//
        // Create Product Variant
        $Variant = array(
            'post_title'  => $this->decodeMultilang($this->in["base_title"]),
            'post_parent' => $BaseProductId,
            'post_status' => 'publish',
            'post_name'   => $this->decodeMultilang($this->in["base_title"]),
            'post_type'   => 'product_variation'
        );
        // Creating the product variation
        $VariantId = wp_insert_post($Variant);
        if (is_wp_error($VariantId)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Create Product variant. " . $VariantId->get_error_message()
            );
        }
        return $this->load($VariantId);
    }
        
    /**
     * @abstract    Search for Base Product by Name
     * @param       string      $Name       Input Product Name without Options Array
     * @return      int|null    Product Id
     */
    public function getBaseProduct($Name)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        $DecodedName    =   $this->decodeMultilang($Name);
        //====================================================================//
        // Check Decoded Name is String
        if (!is_scalar($DecodedName) || empty($DecodedName)) {
            return null;
        }
        //====================================================================//
        // Load From DataBase
        $RawData = get_posts([
            'post_type'     =>      $this->postType,
            'post_status'   =>      'any',
            's'             =>      $DecodedName,
        ]);
        //====================================================================//
        // For Each Result
        foreach ($RawData as $Product) {
            //====================================================================//
            // Check if Name is Same
            if ($Product->post_title == $DecodedName) {
                return $Product->ID;
            }
        }
        return null;
    }
    
    /**
     * @abstract    Update Request Object
     *
     * @param       array   $Needed         Is This Update Needed
     *
     * @return      int|false
     */
    public function update($Needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Update User Object
        if ($Needed) {
            $Result = wp_update_post($this->object);
            if (is_wp_error($Result)) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Unable to Update " . $this->postType . ". " . $Result->get_error_message()
                );
            }
        }
        
        //====================================================================//
        // Update Base Object
        if ($this->isToUpdate("BaseObject")) {
            $Result = wp_update_post($this->BaseObject);
            if (is_wp_error($Result)) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Unable to Update " . $this->postType . ". " . $Result->get_error_message()
                );
            }
        }
        
        return (int) $this->object->ID;
    }
    
    /**
     * @abstract    Delete requested Object
     *
     * @param       int     $Id     Object Id.  If NULL, Object needs to be created.
     *
     * @return      bool
     */
    public function delete($Id = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $Post           =       get_post($Id);
        if (is_wp_error($Post)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load " . self::$NAME . " (" . $Id . ")."
            );
        }
        if (empty($Post)) {
            return true;
        }
        //====================================================================//
        // Delete Object
        $Result = wp_delete_post($Id);
        if (is_wp_error($Result)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Delete " . $this->postType . ". " . $Result->get_error_message()
            );
        }
        //====================================================================//
        // Also Delete Parent if No More Childrens
        if ($Post->post_parent) {
            if (count(wc_get_product($Post->post_parent)->get_children()) == 0) {
                $this->delete($Post->post_parent);
            }
        }
        return true;
    }
}
