<?php
/*
 * Copyright (C) 2017   Splash Sync       <contact@splashsync.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

namespace Splash\Local\Objects\Product;

use Splash\Client\Splash      as Splash;
use Splash\Local\Notifier;
use WC_Product;

/**
 * Wordpress Taximony Data Access
 */
trait HooksTrait
{

    private static $postClass    =   "\Splash\Local\Objects\Product";
    
    /**
    * Register Product Hooks
    */
    public static function registerHooks()
    {
        // Creation & Update of Products Variation
        add_action('woocommerce_new_product_variation', [ static::$postClass , "created"], 10, 1);
        add_action('woocommerce_update_product_variation', [ static::$postClass , "updated"], 10, 1);
        // Stoks Update of Products & Products Variation
        add_action('woocommerce_product_set_stock', [ static::$postClass , "stockUpdated"], 10, 1);
        add_action('woocommerce_variation_set_stock', [ static::$postClass , "stockUpdated"], 10, 1);
    }

    /**
     * WooCommerce Product Created Hook
     *  
     * @param int $postId
     * 
     * @return void
     */
    public static function created($postId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $postId . ")");
        //====================================================================//
        // Prepare Commit Parameters
        $objectType     =   "Product";
        $comment        =   $objectType .  " Variant Created on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object($objectType)->isLocked()) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit($objectType, $postId, SPL_A_CREATE, "Wordpress", $comment);
        //====================================================================//
        // Do Commit for Deleted Parent Id
        Splash::commit($objectType, wc_get_product($postId)->get_parent_id(), SPL_A_DELETE, "Wordpress", $comment);
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
    
    /**
     * WooCommerce Product Variant Updated Hook
     * 
     * @param int $postId
     * 
     * @return void
     */
    public static function updated($postId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $postId . ")");
        //====================================================================//
        // Prepare Commit Parameters
        $objectType     =   "Product";
        $comment        =   $objectType .  " Variant Updated on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object($objectType)->isLocked()) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit($objectType, $postId, SPL_A_UPDATE, "Wordpress", $comment);
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
    
    /**
     * WooCommerce Product Variant Updated Hook
     * 
     * @param WC_Product $product
     * 
     * @return void
     */
    public static function stockUpdated($product)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $product->get_id() . ")");
        //====================================================================//
        // Prepare Commit Parameters
        $objectType     =   "Product";
        $comment        =   $objectType .  " Updated on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object($objectType)->isLocked($product->get_id())) {
            return;
        }
        //====================================================================//
        // Filter Variants Base Products from Commit
        if (self::isBaseProduct($product->get_id())) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit($objectType, $product->get_id(), SPL_A_UPDATE, "Wordpress", $comment);
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
