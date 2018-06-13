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

namespace Splash\Local\Objects\Post;

use Splash\Client\Splash      as Splash;
use Splash\Local\Notifier;

use Splash\Local\Objects\Product\Variants\CoreTrait as Variants;

/**
 * @abstract    Wordpress Taximony Data Access
 */
trait HooksTrait
{

    private static $PostClass    =   "\Splash\Local\Objects\Post";
    
    /**
    *   @abstract     Register Post & Pages, Product Hooks
    */
    static public function registeHooks()
    {

        add_action('save_post', [ static::$PostClass , "updated"], 10, 3);
        add_action('deleted_post', [ static::$PostClass , "deleted"], 10, 3);
    }

    static public function updated($Id, $Post, $Updated)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Id . ")");
        //====================================================================//
        // Check Id is Not Empty
        if (empty($Id)) {
            return;
        }
        //====================================================================//
        // Check Post is Not a Auto-Draft
        if ($Post->post_status == "auto-draft") {
            return;
        }
        //====================================================================//
        // Prepare Commit Parameters
        $Action         =   $Updated ? SPL_A_UPDATE : SPL_A_CREATE;
        $ObjectType     =   self::getSplashType($Post);
        if (!$ObjectType) {
            return;
        }
        
        $Comment        =   $ObjectType .  ($Updated ? " Updated" : " Created") . " on Wordpress";
        //====================================================================//
        // Catch Wc Actions on variable products
        if (($Post->post_type == "product") && did_action('woocommerce_init')) {
            $Id     =   Variants::getIdsForCommit($Id);
        }
        //====================================================================//
        // Check Commit is Allowed
        if (!self::isCommitAllowed($Post->post_type, $ObjectType, $Action)) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit($ObjectType, $Id, $Action, "Wordpress", $Comment);
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
    
    /**
     * @abstract    Detect Splash Object Type Name
     * @param   object $Post
     * @return  boolean|string
     */
    static public function getSplashType($Post)
    {
        switch ($Post->post_type) {
            //====================================================================//
            // Core Wp Objects Types
            case "post":
                return "Post";
            case "page":
                return "Page";
                
            //====================================================================//
            // WooCommerce Objects Types
            case "product":
            case "product_variation":
                return "Product";
            case "shop_order":
                return "Order";
        }
        Splash::log()->deb("Unknown Object Type => " . $Post->post_type);
        return false;
    }
    
    /**
     * @abstract    Detect Splash Object Type Name
     * @param   object $Post
     * @return  boolean|string
     */
    private static function isCommitAllowed($PostType, $ObjectType, $Action)
    {
        //====================================================================//
        // Prevent Commit on Variant Product Create
        if (($PostType == "product")
                && ($Action == SPL_A_CREATE)
                && Splash::object($ObjectType)->isLocked("onVariantCreate")) {
            return false;
        }
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (($Action == SPL_A_UPDATE) && Splash::object($ObjectType)->isLocked()) {
            return false;
        }
        return true;
    }
    
    static public function deleted($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Id . ")");
        
        $post = get_post($Id);
        if ($post->post_type == "post") {
            Splash::commit("Post", $Id, SPL_A_DELETE, "Wordpress", "Post Deleted");
        }
        if ($post->post_type == "page") {
            Splash::commit("Page", $Id, SPL_A_DELETE, "Wordpress", "Page Deleted");
        }
        if ($post->post_type == "product") {
            $Id     =   Variants::getIdsForCommit($Id);
            Splash::commit("Product", $Id, SPL_A_DELETE, "Wordpress", "Product Deleted");
        }
        if ($post->post_type == "product_variation") {
            Splash::commit("Product", $Id, SPL_A_DELETE, "Wordpress", "Product Deleted");
        }
        if ($post->post_type == "shop_order") {
            Splash::commit("Order", $Id, SPL_A_DELETE, "Wordpress", "Order Deleted");
            Splash::commit("Invoice", $Id, SPL_A_DELETE, "Wordpress", "Invoice Deleted");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
