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

namespace Splash\Local\Objects;

use Splash\Local\Dictionary\AddressTypes;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use WC_Order;
use WP_User;

/**
 * WooCommerce Customer Address Object
 */
class Address extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;

    // Core Fields
    use Core\WooCommerceObjectTrait;      // Trigger WooCommerce Module Activation

    // User Fields
    use Users\HooksTrait;

    // Address Traits
    use Address\CRUDTrait;
    use Address\ObjectListTrait;
    use Address\UserTrait;
    use Address\AliasTrait;
    use Address\MainTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static string $name = "Customer Address";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static string $description = "Wordpress Customer Address Object";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-envelope-o";

    //====================================================================//
    // Object Synchronization Limitations
    //
    // This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushCreated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushUpdated = true;

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushDeleted = false;

    /**
     * Enable Creation Of New Local Objects when Not Existing
     *
     * {@inheritdoc}
     */
    protected static bool $enablePushCreated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushUpdated = true;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushDeleted = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @phpstan-var WP_User|WC_Order;
     */
    protected object $object;

    /**
     * @var string
     */
    protected string $addressType;

    /**
     * Decode User ID
     *
     * @param string $addressIdString Encoded User Address ID
     *
     * @return null|string
     */
    protected function decodeUserId(string $addressIdString): ?string
    {
        //====================================================================//
        // Decode Delivery Ids
        if (0 === strpos($addressIdString, AddressTypes::DELIVERY."-")) {
            $this->addressType = AddressTypes::DELIVERY;

            return substr($addressIdString, strlen(AddressTypes::DELIVERY."-"));
        }
        //====================================================================//
        // Decode Billing Ids
        if (0 === strpos($addressIdString, AddressTypes::BILLING."-")) {
            $this->addressType = AddressTypes::BILLING;

            return substr($addressIdString, strlen(AddressTypes::BILLING."-"));
        }

        return null;
    }

    /**
     * Decode Order ID
     *
     * @param string $addressIdString Encoded Order Address ID
     *
     * @return null|string
     */
    protected function decodeOrderId(string $addressIdString): ?string
    {
        //====================================================================//
        // Decode Order Addresses Ids
        foreach (AddressTypes::ORDERS as $addressType) {
            if (0 === strpos($addressIdString, $addressType."-")) {
                $this->addressType = $addressType;

                return substr($addressIdString, strlen($addressType."-"));
            }
        }

        return null;
    }

    /**
     * Encode User Address Field ID
     *
     * @param string      $fieldId Encoded User Address ID
     * @param null|string $mode
     *
     * @return string
     */
    protected function encodeFieldId(string $fieldId, string $mode = null): string
    {
        if ($mode) {
            return $mode."_".$fieldId;
        }

        return $this->addressType."_".$fieldId;
    }
}
