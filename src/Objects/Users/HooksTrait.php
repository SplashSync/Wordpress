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

    private static $userClass    =   "\Splash\Local\Objects\ThirdParty";
    
    /**
     * Register Users Hooks
     */
    public static function registerHooks()
    {

        add_action('user_register', [ static::$userClass , "created"], 10, 1);
        add_action('profile_update', [ static::$userClass , "updated"], 10, 1);
        add_action('deleted_user', [ static::$userClass , "deleted"], 10, 1);
    }
    
    /**
     * User Create Hook Action
     * 
     * @param int $postId
     */
    public static function created($postId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $postId . ")");
        //====================================================================//
        // Do Commit
        Splash::commit("ThirdParty", $postId, SPL_A_CREATE, "Wordpress", "User Created");
        //====================================================================//
        // Do Commit for User Address
        if (Local::hasWooCommerce() && empty(SPLASH_DEBUG)) {
            Splash::commit("Address", Address::encodeDeliveryId((string) $postId), SPL_A_CREATE, "Wordpress", "User Created");
            Splash::commit("Address", Address::encodeBillingId((string) $postId), SPL_A_CREATE, "Wordpress", "User Created");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }

    /**
     * User Updated Hook Action
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
        // Prevent Repeated Commit if Needed
        if (Splash::object("ThirdParty")->isLocked()) {
            return;
        }       
        //====================================================================//
        // Do Commit
        Splash::commit("ThirdParty", $postId, SPL_A_UPDATE, "Wordpress", "User Updated");
        //====================================================================//
        // Do Commit for User Address
        if (Local::hasWooCommerce() && empty(SPLASH_DEBUG)) {
            Splash::commit("Address", Address::encodeDeliveryId((string) $postId), SPL_A_UPDATE, "Wordpress", "User Updated");
            Splash::commit("Address", Address::encodeBillingId((string) $postId), SPL_A_UPDATE, "Wordpress", "User Updated");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
    
    /**
     * User Deleted Hook Action
     * 
     * @param int $postId
     */
    public static function deleted($postId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__ . "(" . $postId . ")");
        //====================================================================//
        // Do Commit
        Splash::commit("ThirdParty", $postId, SPL_A_DELETE, "Wordpress", "User Deleted");
        //====================================================================//
        // Do Commit for User Address
        if (Local::hasWooCommerce() && empty(SPLASH_DEBUG)) {
            Splash::commit("Address", Address::encodeDeliveryId((string) $postId), SPL_A_DELETE, "Wordpress", "User Deleted");
            Splash::commit("Address", Address::encodeBillingId((string) $postId), SPL_A_DELETE, "Wordpress", "User Deleted");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
