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

namespace Splash\Local\Objects\Address;

use Splash\Core\SplashCore as Splash;
use Splash\Local\Objects\Users\CRUDTrait as UserCRUDTrait;
use WC_Order;
use WP_User;

/**
 * WordPress Customer Address CRUD Functions
 */
trait CRUDTrait
{
    use UserCRUDTrait;

    /**
     * {@inheritdoc}
     *
     * @return WC_Order|WP_User
     */
    public function load(string $objectId): ?object
    {
        $wpObject = null;
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Decode Address User Id
        if ($userId = $this->decodeUserId((string) $objectId)) {
            //====================================================================//
            // Init User Object
            $wpObject = get_user_by("ID", $userId);
            if (is_wp_error($wpObject)) {
                Splash::log()->errTrace("Unable to load User for Address (".$objectId.").");

                return null;
            }
        }
        //====================================================================//
        // Decode Address Order Id
        if ($orderId = $this->decodeOrderId((string) $objectId)) {
            //====================================================================//
            // Init User Object
            $wpObject = wc_get_order((int) $orderId);
            if (is_wp_error($wpObject) || !($wpObject instanceof WC_Order)) {
                Splash::log()->errTrace("Unable to load Order for Address (".$objectId.").");

                return null;
            }
        }

        return $wpObject ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function create(): ?WP_User
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        //====================================================================//
        // Not Allowed
        return Splash::log()->errNull("Creation of Customer Address Not Allowed.");
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(string $postId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        //====================================================================//
        // Not Allowed
        return Splash::log()->warTrace("Delete of Customer Address Not Allowed.");
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        //====================================================================//
        // From Wc Order
        if ($this->object instanceof WC_Order) {
            return empty($this->object->get_id()) ? null : (string) $this->object->get_id();
        }

        //====================================================================//
        // From Wp User
        return empty($this->object->ID) ? null : (string) $this->object->ID;
    }
}
