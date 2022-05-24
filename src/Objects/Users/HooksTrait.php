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

namespace Splash\Local\Objects\Users;

use Exception;
use Splash\Client\Splash      as Splash;
use Splash\Local\Local;
use Splash\Local\Notifier;
use Splash\Local\Objects\Address;

/**
 * WordPress Users Hooks
 */
trait HooksTrait
{
    /**
     * @var string
     */
    private static string $userClass = "\\Splash\\Local\\Objects\\ThirdParty";

    /**
     * Register Users Hooks
     *
     * @return void
     */
    public static function registerHooks(): void
    {
        //====================================================================//
        // Setup User Created Hook
        $createCall = array( self::$userClass , "created");
        if (is_callable($createCall)) {
            add_action('user_register', $createCall, 10, 1);
        }
        //====================================================================//
        // Setup User Updated Hook
        $updateCall = array( self::$userClass , "updated");
        if (is_callable($updateCall)) {
            add_action('profile_update', $updateCall, 10, 1);
        }
        //====================================================================//
        // Setup User Deleted Hook
        $deleteCall = array( self::$userClass , "deleted");
        if (is_callable($deleteCall)) {
            add_action('deleted_user', $deleteCall, 10, 1);
        }
    }

    /**
     * User Create Hook Action
     *
     * @param int|string $postId
     *
     * @return void
     */
    public static function created($postId): void
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Do Commit
        Splash::commit("ThirdParty", $postId, SPL_A_CREATE, "Wordpress", "User Created");
        //====================================================================//
        // Do Commit for User Address
        if (Local::hasWooCommerce() && !Splash::isDebugMode()) {
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
        // Prevent Repeated Commit if Needed
        if (Splash::object("ThirdParty")->isLocked()) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit("ThirdParty", $postId, SPL_A_UPDATE, "Wordpress", "User Updated");
        //====================================================================//
        // Do Commit for User Address
        if (Local::hasWooCommerce() && !Splash::isDebugMode()) {
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
     * @param int|string $postId
     *
     * @return void
     */
    public static function deleted($postId): void
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Do Commit
        Splash::commit("ThirdParty", $postId, SPL_A_DELETE, "Wordpress", "User Deleted");
        //====================================================================//
        // Do Commit for User Address
        if (Local::hasWooCommerce() && !Splash::isDebugMode()) {
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
