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

namespace Splash\Local\Objects\Product;

use Exception;
use Splash\Client\Splash      as Splash;
use Splash\Local\Notifier;
use Splash\Local\Objects\Product;
use WC_Product;

/**
 * WordPress Taxonomy Data Access
 */
trait HooksTrait
{
    /**
     * @var string
     */
    private static string $postClass = "\\Splash\\Local\\Objects\\Product";

    /**
     * Register Product Hooks
     *
     * @return void
     */
    public static function registerHooks()
    {
        //====================================================================//
        // Setup Product Variant Created Hook
        $createVariantCall = array( self::$postClass , "created");
        if (is_callable($createVariantCall)) {
            add_action('woocommerce_new_product_variation', $createVariantCall, 10, 1);
        }
        //====================================================================//
        // Setup Product Variant Updated Hook
        $updateVariantCall = array( self::$postClass , "updated");
        if (is_callable($updateVariantCall)) {
            add_action('woocommerce_update_product_variation', $updateVariantCall, 10, 1);
        }
        //====================================================================//
        // Setup Product Stock Updated Hook
        $updateStockCall = array( self::$postClass , "stockUpdated");
        if (is_callable($updateStockCall)) {
            add_action('woocommerce_product_set_stock', $updateStockCall, 10, 1);
            add_action('woocommerce_variation_set_stock', $updateStockCall, 10, 1);
        }
    }

    /**
     * WooCommerce Product Created Hook
     *
     * @param int|string $postId
     *
     * @throws Exception
     *
     * @return void
     */
    public static function created($postId): void
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Prepare Commit Parameters
        $objectType = "Product";
        $comment = $objectType." Variant Created on Wordpress";
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
        /** @var WC_Product $wcProduct */
        $wcProduct = wc_get_product($postId);
        Splash::commit(
            $objectType,
            Product::getMultiLangMaster($wcProduct->get_parent_id()),
            SPL_A_DELETE,
            "Wordpress",
            $comment
        );
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }

    /**
     * WooCommerce Product Variant Updated Hook
     *
     * @param int|string $postId
     *
     * @throws Exception
     *
     * @return void
     */
    public static function updated($postId): void
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Prepare Commit Parameters
        $objectType = "Product";
        $comment = $objectType." Variant Updated on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object($objectType)->isLocked()) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit(
            $objectType,
            Product::getMultiLangMaster((int) $postId),
            SPL_A_UPDATE,
            "Wordpress",
            $comment
        );
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }

    /**
     * WooCommerce Product Variant Updated Hook
     *
     * @param WC_Product $product
     *
     * @throws Exception
     *
     * @return void
     */
    public static function stockUpdated(WC_Product $product): void
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Prepare Commit Parameters
        $objectType = "Product";
        $comment = $objectType." Updated on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object($objectType)->isLocked((string) $product->get_id())) {
            return;
        }
        //====================================================================//
        // Filter Variants Base Products from Commit
        if (self::isBaseProduct($product->get_id())) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit(
            $objectType,
            Product::getMultiLangMaster($product->get_id()),
            SPL_A_UPDATE,
            "Wordpress",
            $comment
        );
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
