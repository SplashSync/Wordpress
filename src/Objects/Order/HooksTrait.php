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

namespace Splash\Local\Objects\Order;

use Exception;
use Splash\Client\Splash      as Splash;
use Splash\Local\Core\PrivacyManager;
use Splash\Local\Notifier;
use WC_Order;

/**
 * WordPress Users Hooks
 */
trait HooksTrait
{
    /**
     * @var string
     */
    private static string $orderClass = "\\Splash\\Local\\Objects\\Order";

    /**
     * Register Users Hooks
     *
     * @return void
     */
    public static function registerHooks()
    {
        //====================================================================//
        // Setup Order Updated Hook
        $updateCall = array(self::$orderClass, "updated");
        if (is_callable($updateCall)) {
            add_action('woocommerce_before_order_object_save', $updateCall, 10, 1);
        }
    }

    /**
     * WooCommerce Order Updated HookAction
     *
     * @param WC_Order $order
     *
     * @throws Exception
     *
     * @return void
     */
    public static function updated(WC_Order $order): void
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Id is Not Empty
        if (empty($order->get_id())) {
            return;
        }
        //====================================================================//
        // Check Order Not Anonymize
        if (PrivacyManager::isAnonymize($order)) {
            Splash::log()->war("Commit is Disabled for Anonymize Orders");

            return;
        }
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object("Order")->isLocked()) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit("Order", $order->get_id(), SPL_A_UPDATE, "Wordpress", "Wc Order Updated");
        Splash::commit("Invoice", $order->get_id(), SPL_A_UPDATE, "Wordpress", "Wc Invoice Updated");
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
