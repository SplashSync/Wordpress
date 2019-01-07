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

use Splash\Client\Splash      as Splash;
use Splash\Local\Notifier;
use WC_Product;

/**
 * Wordpress Taximony Data Access
 */
trait HooksTrait
{
    private static $postClass    =   "\\Splash\\Local\\Objects\\Product";
    
    /**
     * Register Product Hooks
     */
    public static function registerHooks()
    {
        // Creation & Update of Products Variation
        add_action('woocommerce_new_product_variation', array( static::$postClass , "created"), 10, 1);
        add_action('woocommerce_update_product_variation', array( static::$postClass , "updated"), 10, 1);
        // Stoks Update of Products & Products Variation
        add_action('woocommerce_product_set_stock', array( static::$postClass , "stockUpdated"), 10, 1);
        add_action('woocommerce_variation_set_stock', array( static::$postClass , "stockUpdated"), 10, 1);
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
