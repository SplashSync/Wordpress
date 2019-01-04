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

namespace Splash\Local\Objects\Users;

use Splash\Client\Splash      as Splash;
use Splash\Local\Notifier;
use Splash\Local\Local;

use Splash\Local\Objects\Address;

/**
 * Wordpress Users Hooks
 */
trait HooksTrait
{

    private static $UserClass    =   "\Splash\Local\Objects\ThirdParty";
    
    /**
    *   @abstract     Register Users Hooks
    */
    public static function registeHooks()
    {

        add_action('user_register', [ static::$UserClass , "created"], 10, 1);
        add_action('profile_update', [ static::$UserClass , "updated"], 10, 1);
        add_action('deleted_user', [ static::$UserClass , "deleted"], 10, 1);
    }
    
    public static function created($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Id . ")");
        //====================================================================//
        // Do Commit
        Splash::commit("ThirdParty", $Id, SPL_A_CREATE, "Wordpress", "User Created");
        //====================================================================//
        // Do Commit for User Address
        if (Local::hasWooCommerce() && empty(SPLASH_DEBUG)) {
            Splash::commit("Address", Address::encodeDeliveryId($Id), SPL_A_CREATE, "Wordpress", "User Created");
            Splash::commit("Address", Address::encodeBillingId($Id), SPL_A_CREATE, "Wordpress", "User Created");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }

    public static function updated($Id)
    {
        //====================================================================//
        // Stack Trace
         //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object("ThirdParty")->isLocked()) {
            return;
        }       Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Id . ")");
        //====================================================================//
        // Do Commit
        Splash::commit("ThirdParty", $Id, SPL_A_UPDATE, "Wordpress", "User Updated");
        //====================================================================//
        // Do Commit for User Address
        if (Local::hasWooCommerce() && empty(SPLASH_DEBUG)) {
            Splash::commit("Address", Address::encodeDeliveryId($Id), SPL_A_UPDATE, "Wordpress", "User Updated");
            Splash::commit("Address", Address::encodeBillingId($Id), SPL_A_UPDATE, "Wordpress", "User Updated");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
    
    public static function deleted($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $Id . ")");
        //====================================================================//
        // Do Commit
        Splash::commit("ThirdParty", $Id, SPL_A_DELETE, "Wordpress", "User Deleted");
        //====================================================================//
        // Do Commit for User Address
        if (Local::hasWooCommerce() && empty(SPLASH_DEBUG)) {
            Splash::commit("Address", Address::encodeDeliveryId($Id), SPL_A_DELETE, "Wordpress", "User Deleted");
            Splash::commit("Address", Address::encodeBillingId($Id), SPL_A_DELETE, "Wordpress", "User Deleted");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
