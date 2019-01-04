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

namespace Splash\Local\Objects\Order;

use Splash\Client\Splash      as Splash;
use Splash\Local\Notifier;

/**
 * Wordpress Users Hooks
 */
trait HooksTrait
{

    private static $OrderClass    =   "\Splash\Local\Objects\Order";
    
    /**
    *   @abstract     Register Users Hooks
    */
    public static function registeHooks()
    {

        add_action('woocommerce_before_order_object_save', [ static::$OrderClass , "updated"], 10, 1);
    }

    public static function updated($Order)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Order->get_id() . ")");
        //====================================================================//
        // Check Id is Not Empty
        if (empty($Order->get_id())) {
            return;
        }
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object("Order")->isLocked()) {
            return;
        }       Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Order->get_id() . ")");
        //====================================================================//
        // Do Commit
        Splash::commit("Order", $Order->get_id(), SPL_A_UPDATE, "Wordpress", "Wc Order Updated");
        Splash::commit("Invoice", $Order->get_id(), SPL_A_UPDATE, "Wordpress", "Wc Invoice Updated");
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
