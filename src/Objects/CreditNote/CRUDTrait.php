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

use Splash\Core\SplashCore as Splash;
use Splash\Local\Core\PrivacyManager;
use WC_Order;
use WC_Order_Refund;

/**
 * WooCommerce Credit Note CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $postId Object id
     *
     * @return null|WC_Order_Refund
     */
    public function load(string $postId): ?WC_Order_Refund
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Init Object
        // wc_get_order() returns the right class for any order-like post type,
        // so a refund id gives back a WC_Order_Refund.
        $wcRefund = wc_get_order((int) $postId);
        if (!$wcRefund instanceof WC_Order_Refund) {
            return Splash::log()->errNull(
                "Unable to load ".$this->postType." (".$postId.")."
            );
        }
        //====================================================================//
        // Check Parent Order Not Anonymized
        // A refund carries no personal data of its own: it inherits the customer
        // from its parent order, so the parent is what has to be checked.
        $parent = $this->getParentOrder($wcRefund);
        if ($parent && PrivacyManager::isAnonymize($parent)) {
            return Splash::log()->errNull("Reading Anonymized Orders is Forbidden");
        }

        return $wcRefund;
    }

    /**
     * Create Request Object
     *
     * Refunds are read-only: creating one is a money-moving operation that may
     * trigger a gateway refund, so it is deliberately not exposed.
     *
     * @return null|WC_Order_Refund
     */
    public function create(): ?WC_Order_Refund
    {
        return Splash::log()->errNull(
            "Creating WooCommerce Refunds from Splash is not allowed."
        );
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string Object ID
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Object is Read-Only: nothing is ever written back.
        if ($needed) {
            Splash::log()->war("WooCommerce Refunds are Read-Only. Changes were ignored.");
        }

        return $this->getObjectIdentifier();
    }

    /**
     * Delete Request Object
     *
     * @param string $postId Object id
     *
     * @return bool
     */
    public function delete(string $postId): bool
    {
        return Splash::log()->warTrace(
            "Deleting WooCommerce Refunds from Splash is not allowed. ID ".$postId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        $refundId = $this->object->get_id();

        return empty($refundId) ? null : (string) $refundId;
    }

    /**
     * Get the Order this Refund belongs to
     *
     * This is the WooCommerce counterpart of Dolibarr's `fk_facture_source`.
     *
     * @param WC_Order_Refund $wcRefund
     *
     * @return null|WC_Order
     */
    protected function getParentOrder(WC_Order_Refund $wcRefund): ?WC_Order
    {
        $parentId = $wcRefund->get_parent_id();
        if (empty($parentId)) {
            return null;
        }
        $parent = wc_get_order($parentId);

        return ($parent instanceof WC_Order) ? $parent : null;
    }
}
