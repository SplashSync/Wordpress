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

use Splash\Core\SplashCore      as Splash;
use Splash\Local\Core\PrivacyManager;
use WC_Order;
use WP_Error;

/**
 * WordPress Order CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $postId Object id
     *
     * @return null|WC_Order
     */
    public function load(string $postId): ?WC_Order
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Init Object
        $wcOrder = wc_get_order((int) $postId);
        if (is_wp_error($wcOrder) || !($wcOrder instanceof WC_Order)) {
            Splash::log()->errTrace("Unable to load ".$this->postType." (".$postId.").");

            return null;
        }
        //====================================================================//
        // Check Order Not Anonymize
        if (PrivacyManager::isAnonymize($wcOrder)) {
            return Splash::log()->errNull("Reading Anonymized Orders is Forbidden");
        }

        return $wcOrder;
    }

    /**
     * Create Request Object
     *
     * @return null|WC_Order
     */
    public function create(): ?WC_Order
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        $wcOrder = wc_create_order();
        if (is_wp_error($wcOrder) || ($wcOrder instanceof WP_Error)) {
            Splash::log()->errTrace(
                "Unable to Create ".$this->postType.". ".$wcOrder->get_error_message()
            );

            return null;
        }

        return $wcOrder;
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Update User Object
        if ($needed) {
            // Update Totals
            $this->object->update_taxes();
            $this->object->calculate_totals(false);
            // Save Order
            $result = $this->object->save();
            if (is_wp_error($result)) {
                Splash::log()->errTrace(
                    "Unable to Update ".$this->postType.". ".$result->get_error_message()
                );

                return null;
            }
        }

        return $this->getObjectIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $postId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Delete Object
        $result = wp_delete_post((int) $postId);
        if (is_wp_error($result)) {
            return Splash::log()->errTrace(
                "Unable to Delete ".$this->postType.". ".$result->get_error_message()
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        $orderId = $this->object->get_id();
        if (empty($orderId)) {
            return null;
        }

        return (string) $orderId;
    }
}
