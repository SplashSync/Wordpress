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

namespace Splash\Local\Objects\Users;

use Splash\Client\Splash      as Splash;
use Splash\Local\Local;
use Splash\Local\Notifier;
use Splash\Local\Objects\Address;

/**
 * Wordpress Users Hooks
 */
trait HooksTrait
{
    private static $userClass    =   "\\Splash\\Local\\Objects\\ThirdParty";
    
    /**
     * Register Users Hooks
     */
    public static function registerHooks()
    {
        add_action('user_register', array( static::$userClass , "created"), 10, 1);
        add_action('profile_update', array( static::$userClass , "updated"), 10, 1);
        add_action('deleted_user', array( static::$userClass , "deleted"), 10, 1);
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
            Splash::commit(
                "Address",
                Address::encodeDeliveryId((string) $postId),
                SPL_A_CREATE,
                "Wordpress",
                "User Created"
            );
            Splash::commit(
                "Address",
                Address::encodeBillingId((string) $postId),
                SPL_A_CREATE,
                "Wordpress",
                "User Created"
            );
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
            Splash::commit(
                "Address",
                Address::encodeDeliveryId((string) $postId),
                SPL_A_UPDATE,
                "Wordpress",
                "User Updated"
            );
            Splash::commit(
                "Address",
                Address::encodeBillingId((string) $postId),
                SPL_A_UPDATE,
                "Wordpress",
                "User Updated"
            );
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
            Splash::commit(
                "Address",
                Address::encodeDeliveryId((string) $postId),
                SPL_A_DELETE,
                "Wordpress",
                "User Deleted"
            );
            Splash::commit(
                "Address",
                Address::encodeBillingId((string) $postId),
                SPL_A_DELETE,
                "Wordpress",
                "User Deleted"
            );
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
