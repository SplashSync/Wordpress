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

namespace Splash\Local\Objects\Product;

use Splash\Core\SplashCore      as Splash;

/**
 * Wordpress Page, Post, Product CRUD Functions
 */
trait CRUDTrait
{
    use \Splash\Local\Objects\Post\CRUDTrait;                   // Objects CRUD
    
    /**
     * Load Request Object
     *
     * @param int $postId Object id
     *
     * @return mixed
     */
    public function load($postId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $post           =       get_post($postId);
        //====================================================================//
        // Load WooCommerce Product Object
        $wcProduct        =       wc_get_product($postId);
        if ($wcProduct) {
            $this->product  =       $wcProduct;
        }
        if (is_wp_error($post)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load " . self::$NAME . " (" . $postId . ")."
            );
        }
        //====================================================================//
        // Load WooCommerce Parent Product Object
        $this->loadParent();

        return $post;
    }
    
    /**
     * Create a New Product Variation
     *
     * @return false|object
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
        $baseProductId  =   $this->getBaseProduct($this->in["base_title"]);
        //====================================================================//
        // Base Product Not Found
        if (!$baseProductId) {
            $this->lock("onVariantCreate");
            $this->in["post_title"] =       $this->in["base_title"];
            $baseProduct            =       $this->createPost();
            $baseProductId          =       $baseProduct->ID;
            wp_set_object_terms($baseProductId, 'variable', 'product_type');
            $this->unLock("onVariantCreate");
        }
        //====================================================================//
        // Create Product Variant
        $variant = array(
            'post_title'  => $this->decodeMultilang($this->in["base_title"]),
            'post_parent' => $baseProductId,
            'post_status' => 'publish',
            'post_name'   => $this->decodeMultilang($this->in["base_title"]),
            'post_type'   => 'product_variation'
        );
        // Creating the product variation
        $variantId = wp_insert_post($variant);
        if (is_wp_error($variantId)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Create Product variant. " . $variantId->get_error_message()
            );
        }

        return $this->load($variantId);
    }
        
    /**
     * Search for Base Product by Name
     *
     * @param string $name Input Product Name without Options Array
     *
     * @return null|int Product Id
     */
    public function getBaseProduct($name)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        $decodedName    =   $this->decodeMultilang($name);
        //====================================================================//
        // Check Decoded Name is String
        if (!is_scalar($decodedName) || empty($decodedName)) {
            return null;
        }
        //====================================================================//
        // Load From DataBase
        $rawData = get_posts(array(
            'post_type'     =>      $this->postType,
            'post_status'   =>      'any',
            's'             =>      $decodedName,
        ));
        //====================================================================//
        // For Each Result
        foreach ($rawData as $wcProduct) {
            //====================================================================//
            // Check if Name is Same
            if ($wcProduct->post_title == $decodedName) {
                return $wcProduct->ID;
            }
        }

        return null;
    }
    
    /**
     * Update Request Object
     *
     * @param array $needed Is This Update Needed
     *
     * @return false|string
     */
    public function update($needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Update User Object
        if ($needed) {
            $result = wp_update_post($this->object);
            if (is_wp_error($result)) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Unable to Update " . $this->postType . ". " . $result->get_error_message()
                );
            }
        }
        
        //====================================================================//
        // Update Base Object
        if ($this->isToUpdate("baseObject")) {
            $result = wp_update_post($this->baseObject);
            if (is_wp_error($result)) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Unable to Update " . $this->postType . ". " . $result->get_error_message()
                );
            }
        }
        
        return (string) $this->object->ID;
    }
    
    /**
     * Delete requested Object
     *
     * @param int $postId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    public function delete($postId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $post           =       get_post($postId);
        if (is_wp_error($post)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load " . self::$NAME . " (" . $postId . ")."
            );
        }
        if (empty($post)) {
            return true;
        }
        //====================================================================//
        // Delete Object
        $result = wp_delete_post($postId);
        if (is_wp_error($result)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Delete " . $this->postType . ". " . $result->get_error_message()
            );
        }
        //====================================================================//
        // Also Delete Parent if No More Childrens
        if ($post->post_parent) {
            if (0 == count(wc_get_product($post->post_parent)->get_children())) {
                $this->delete($post->post_parent);
            }
        }

        return true;
    }
}
