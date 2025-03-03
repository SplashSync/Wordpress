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

use Exception;
use Splash\Core\SplashCore as Splash;
use Splash\Models\AbstractObject;
use Splash\Models\Objects;
use WC_Order;

/**
 * WooCommerce Order Object
 */
class Order extends AbstractObject
{
    //====================================================================//
    // Splash Php Core Traits
    use Objects\IntelParserTrait;
    use Objects\SimpleFieldsTrait;
    use Objects\GenericFieldsTrait;
    use Objects\PricesTrait;
    use Objects\ImagesTrait;
    use Objects\ListsTrait;

    //====================================================================//
    // Core Fields
    use Core\WooCommerceObjectTrait;        // Trigger WooCommerce Module Activation
    use Core\DokanTrait;                    // Dokan Infos

    //====================================================================//
    // Post Fields
    use Post\CustomTrait;                   // Custom Fields
    use Post\CounterTrait;                  // Posts Counter

    //====================================================================//
    // WooCommerce Order Field
    use Order\CRUDTrait;                    // Objects CRUD
    use Order\ObjectListTrait;              // Objects Listing
    use Order\HooksTrait;                   // Objects CRUD
    use Order\CoreTrait;                    // Order Core Infos
    use Order\ItemsTrait;                   // Order Items List
    use Order\PaymentsTrait;                // Order Payments List
    use Order\TotalsTrait;                  // Order Totals
    use Order\StatusTrait;                  // Order Status Infos
    use Order\StatusFlagsTrait;             // Order Status Flags
    use Order\AddressTrait;                 // Order Billing & Delivery Infos
    use Order\DeliveryTrait;                // Order Delivery Address Details
    use Order\TrackingTrait;                // Order Tracking Details
    use Order\BookingTrait;                 // Order Booking Infos
    use Order\WcSendCloudTrait;             // Wc SendCloud Infos
    use Order\WcPdfInvoiceTrait;            // Wc Pdf Invoices Infos

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static string $name = "Order";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static string $description = "WooCommerce Order Object";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-shopping-cart";

    //====================================================================//
    // Object Synchronization Limitations
    //
    // This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
    //====================================================================//

    /**
     * Disable Creation Of New Local Objects when Not Existing
     *
     * {@inheritdoc}
     */
    protected static bool $enablePushCreated = false;

    /**
     * Disable Update Of Existing Local Objects when Modified Remotely
     *
     * {@inheritdoc}
     */
    protected static bool $enablePushUpdated = false;

    /**
     * Disable Delete Of Existing Local Objects when Deleted Remotely
     *
     * {@inheritdoc}
     */
    protected static bool $enablePushDeleted = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var WC_Order
     */
    protected object $object;

    /**
     * @var string
     */
    protected string $postType = "shop_order";

    //====================================================================//
    // Class Constructor
    //====================================================================//

    /**
     * Order constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        self::setGenericMethodsFormat("snake_case");
    }
}
