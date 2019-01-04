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

/**
 * @abstract    Wordpress Taximony Data Access
 */
trait HooksTrait
{

    private static $PostClass    =   "\Splash\Local\Objects\Product";
    
    /**
    *   @abstract     Register Product Hooks
    */
    public static function registeHooks()
    {
        // Creation & Update of Products Variation
        add_action('woocommerce_new_product_variation', [ static::$PostClass , "created"], 10, 1);
        add_action('woocommerce_update_product_variation', [ static::$PostClass , "updated"], 10, 1);
        // Stoks Update of Products & Products Variation
        add_action('woocommerce_product_set_stock', [ static::$PostClass , "stockUpdated"], 10, 1);
        add_action('woocommerce_variation_set_stock', [ static::$PostClass , "stockUpdated"], 10, 1);
    }

    public static function created($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Id . ")");
        //====================================================================//
        // Prepare Commit Parameters
        $ObjectType     =   "Product";
        $Comment        =   $ObjectType .  " Variant Created on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object($ObjectType)->isLocked()) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit($ObjectType, $Id, SPL_A_CREATE, "Wordpress", $Comment);
        //====================================================================//
        // Do Commit for Deleted Parent Id
        Splash::commit($ObjectType, wc_get_product($Id)->get_parent_id(), SPL_A_DELETE, "Wordpress", $Comment);
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
    
    public static function updated($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Id . ")");
        //====================================================================//
        // Prepare Commit Parameters
        $ObjectType     =   "Product";
        $Comment        =   $ObjectType .  " Variant Updated on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object($ObjectType)->isLocked()) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit($ObjectType, $Id, SPL_A_UPDATE, "Wordpress", $Comment);
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
    
    


    public static function stockUpdated($Product)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Product->get_id() . ")");
        //====================================================================//
        // Prepare Commit Parameters
        $ObjectType     =   "Product";
        $Comment        =   $ObjectType .  " Updated on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object($ObjectType)->isLocked($Product->get_id())) {
            return;
        }
        //====================================================================//
        // Filter Variants Base Products from Commit
        if (self::isBaseProduct($Product->get_id())) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit($ObjectType, $Product->get_id(), SPL_A_UPDATE, "Wordpress", $Comment);
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
