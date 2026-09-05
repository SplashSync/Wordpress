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

namespace Splash\Local\Objects\CreditNote;

use Splash\Client\Splash;
use Splash\Local\Core\PrivacyManager;
use Splash\Local\Notifier;

/**
 * WooCommerce Credit Note WordPress Hooks
 *
 * The connector currently learns about a refund only indirectly, through the order
 * being saved, which tells a remote server that a total changed but not that a
 * credit note exists. These hooks commit the refund itself.
 */
trait HooksTrait
{
    /**
     * Splash Credit Note Class Name
     *
     * @var string
     */
    private static string $creditNoteClass = "\\Splash\\Local\\Objects\\CreditNote";

    /**
     * Register Credit Note Hooks
     *
     * @return void
     */
    public static function registerHooks(): void
    {
        //====================================================================//
        // Refund Created
        $createdCall = array(self::$creditNoteClass, "created");
        if (is_callable($createdCall)) {
            add_action('woocommerce_refund_created', $createdCall, 10, 2);
        }
        //====================================================================//
        // Refund Deleted
        $deletedCall = array(self::$creditNoteClass, "deleted");
        if (is_callable($deletedCall)) {
            add_action('woocommerce_refund_deleted', $deletedCall, 10, 2);
        }
    }

    /**
     * Commit a newly created Refund
     *
     * @param int   $refundId Refund Post Id
     * @param array $args     Refund arguments, as passed to wc_create_refund()
     *
     * @return void
     */
    public static function created(int $refundId, array $args = array()): void
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (empty($refundId)) {
            return;
        }
        //====================================================================//
        // Check Parent Order Not Anonymized
        $parentId = (int) ($args['order_id'] ?? 0);
        if ($parentId && PrivacyManager::isAnonymizeById($parentId)) {
            Splash::log()->war("Commit is Disabled for Anonymize Orders");

            return;
        }
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object("CreditNote")->isLocked()) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit("CreditNote", $refundId, SPL_A_CREATE, "Wordpress", "Wc Refund Created");
        //====================================================================//
        // The parent invoice changed too: its outstanding amount is no longer
        // the same. Commit it so both documents stay in step.
        if ($parentId) {
            Splash::commit("Invoice", $parentId, SPL_A_UPDATE, "Wordpress", "Wc Invoice Refunded");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }

    /**
     * Commit a deleted Refund
     *
     * @param int $refundId Refund Post Id
     * @param int $orderId  Parent Order Id
     *
     * @return void
     */
    public static function deleted(int $refundId, int $orderId): void
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (empty($refundId)) {
            return;
        }
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if (Splash::object("CreditNote")->isLocked()) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit("CreditNote", $refundId, SPL_A_DELETE, "Wordpress", "Wc Refund Deleted");
        if (!empty($orderId)) {
            Splash::commit("Invoice", $orderId, SPL_A_UPDATE, "Wordpress", "Wc Invoice Refund Removed");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }
}
