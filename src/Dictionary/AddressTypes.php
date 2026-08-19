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

namespace Splash\Local\Dictionary;

/**
 * Dictionary of Splash Address Object Types
 *
 * Address IDs are encoded as "{type}-{id}": Customer Addresses use
 * WP User IDs, Logistic Addresses use WC Order IDs.
 */
class AddressTypes
{
    /**
     * Customer Delivery Address (WP User)
     */
    public const DELIVERY = "shipping";

    /**
     * Customer Billing Address (WP User)
     */
    public const BILLING = "billing";

    /**
     * Order Logistic Address (WC Order)
     */
    public const LOGISTIC = "logistic";

    /**
     * Order Billing Address (WC Order)
     */
    public const INVOICING = "invoicing";

    /**
     * Customer Addresses Types (WP User)
     *
     * @var string[]
     */
    public const USERS = array(self::DELIVERY, self::BILLING);

    /**
     * Order Addresses Types (WC Order)
     *
     * @var string[]
     */
    public const ORDERS = array(self::LOGISTIC, self::INVOICING);

    /**
     * Encode Splash Address ID for a given Type
     */
    public static function encode(string $addressType, string $objectId): string
    {
        return $addressType."-".$objectId;
    }
}
