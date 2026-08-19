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

namespace Splash\Local\Core;

use Splash\Local\Dictionary\AddressTypes;

/**
 * Manage Access to Splash Addresses Synchronization Options
 *
 * Customers addresses options use inverted semantics: unchecked (default)
 * means synchronization is active, checked makes them invisible to Splash.
 * Orders addresses options are positive: unchecked (default) means invisible,
 * checked exposes them as read-only Address objects.
 */
class AddressesManager
{
    /**
     * Option: Disable Customers Shipping Addresses Synchronization
     */
    public const OPT_NO_USER_SHIPPING = "splash_no_user_shipping";

    /**
     * Option: Disable Customers Billing Addresses Synchronization
     */
    public const OPT_NO_USER_BILLING = "splash_no_user_billing";

    /**
     * Option: Enable Orders Delivery Addresses Synchronization
     */
    public const OPT_SYNC_ORDER_SHIPPING = "splash_sync_order_shipping";

    /**
     * Option: Enable Orders Billing Addresses Synchronization
     */
    public const OPT_SYNC_ORDER_BILLING = "splash_sync_order_billing";

    /**
     * Check if an Address Type Synchronization is Active
     */
    public static function isActive(string $addressType): bool
    {
        switch ($addressType) {
            case AddressTypes::DELIVERY:
                return empty(\get_option(self::OPT_NO_USER_SHIPPING, false));
            case AddressTypes::BILLING:
                return empty(\get_option(self::OPT_NO_USER_BILLING, false));
            case AddressTypes::LOGISTIC:
                return !empty(\get_option(self::OPT_SYNC_ORDER_SHIPPING, false));
            case AddressTypes::INVOICING:
                return !empty(\get_option(self::OPT_SYNC_ORDER_BILLING, false));
        }

        return false;
    }

    /**
     * Get List of Active Address Types for a Context
     *
     * @param string[] $addressTypes
     *
     * @return string[]
     */
    public static function getActiveTypes(array $addressTypes): array
    {
        return array_values(array_filter($addressTypes, array(self::class, "isActive")));
    }

    /**
     * Check if an Address Type is an Order (Read-Only) Address
     */
    public static function isOrderType(string $addressType): bool
    {
        return in_array($addressType, AddressTypes::ORDERS, true);
    }
}
