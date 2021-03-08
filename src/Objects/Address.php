<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Local\Objects;

use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * WooCommerce Customer Address Object
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Address extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;

    // Core Fields
    use \Splash\Local\Objects\Core\WooCommerceObjectTrait;      // Trigger WooCommerce Module Activation

    // User Fields
    use \Splash\Local\Objects\Users\HooksTrait;

    // Address Traits
    use \Splash\Local\Objects\Address\CRUDTrait;
    use \Splash\Local\Objects\Address\ObjectListTrait;
    use \Splash\Local\Objects\Address\UserTrait;
    use \Splash\Local\Objects\Address\MainTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $NAME = "Customer Address";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "Wordpress Customer Address Object";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static $ICO = "fa fa-envelope-o";

    //====================================================================//
    // Object Synchronization Limitations
    //
    // This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
    //====================================================================//
    /**
     * Allow Creation Of New Local Objects
     *
     * {@inheritdoc}
     */
    protected static $ALLOW_PUSH_CREATED = false;

    /**
     * Allow Update Of Existing Local Objects
     *
     * {@inheritdoc}
     */
    protected static $ALLOW_PUSH_UPDATED = true;

    /**
     * Allow Delete Of Existing Local Objects
     *
     * {@inheritdoc}
     */
    protected static $ALLOW_PUSH_DELETED = false;

    /**
     * Enable Creation Of New Local Objects when Not Existing
     *
     * {@inheritdoc}
     */
    protected static $ENABLE_PUSH_CREATED = false;

    /**
     * Enable Update Of Existing Local Objects when Modified Remotly
     *
     * {@inheritdoc}
     */
    //
    protected static $ENABLE_PUSH_UPDATED = true;

    /**
     * Enable Delete Of Existing Local Objects when Deleted Remotly
     *
     * {@inheritdoc}
     */
    protected static $ENABLE_PUSH_DELETED = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var string
     */
    protected static $delivery = "shipping";

    /**
     * @var string
     */
    protected static $billing = "billing";

    /**
     * @var string
     */
    protected $addressType;

    /**
     * Encode User Delivery Id
     *
     * @param string $userId Encoded User Address Id
     *
     * @return string
     */
    public static function encodeDeliveryId($userId)
    {
        return static::$delivery."-".$userId;
    }

    /**
     * Encode User Billing Id
     *
     * @param string $userId Encoded User Address Id
     *
     * @return string
     */
    public static function encodeBillingId($userId)
    {
        return static::$billing."-".$userId;
    }

    /**
     * Decode User Id
     *
     * @param string $addressIdString Encoded User Address Id
     *
     * @return null|string
     */
    protected function decodeUserId($addressIdString)
    {
        //====================================================================//
        // Decode Delivery Ids
        if (0 === strpos($addressIdString, static::$delivery."-")) {
            $this->addressType = static::$delivery;

            return substr($addressIdString, strlen(static::$delivery."-"));
        }
        //====================================================================//
        // Decode Billing Ids
        if (0 === strpos($addressIdString, static::$billing."-")) {
            $this->addressType = static::$billing;

            return substr($addressIdString, strlen(static::$billing."-"));
        }

        return null;
    }

    /**
     * Encode User Address Field Id
     *
     * @param string      $fieldId Encoded User Address Id
     * @param null|string $mode
     *
     * @return string
     */
    protected function encodeFieldId($fieldId, $mode = null)
    {
        if ($mode) {
            return $mode."_".$fieldId;
        }

        return $this->addressType."_".$fieldId;
    }
}
