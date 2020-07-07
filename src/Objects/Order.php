<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;
use Splash\Local\Core\PrivacyManager;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\GenericFieldsTrait;
use Splash\Models\Objects\ImagesTrait;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ListsTrait;
use Splash\Models\Objects\PricesTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use WP_Post;

/**
 * WooCommerce Order Object
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Order extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use GenericFieldsTrait;
    use PricesTrait;
    use ImagesTrait;
    use ListsTrait;

    // Core Fields
    use \Splash\Local\Objects\Core\WooCommerceObjectTrait;      // Trigger WooCommerce Module Activation

    // Post Fields
    use Post\CustomTrait;                   // Custom Fields
    use Post\CounterTrait;                  // Posts Counter

    // WooCommerce Order Field
    use Order\CRUDTrait;                  // Objects CRUD
    use Order\HooksTrait;                 // Objects CRUD
    use Order\CoreTrait;                  // Order Core Infos
    use Order\ItemsTrait;                 // Order Items List
    use Order\PaymentsTrait;              // Order Payments List
    use Order\TotalsTrait;                // Order Totals
    use Order\StatusTrait;                // Order Status Infos
    use Order\AddressTrait;               // Order Billing & Delivery Infos
    use Order\BookingTrait;               // Order Booking Infos

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $NAME = "Order";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "WooCommerce Order Object";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static $ICO = "fa fa-shopping-cart";

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
    protected static $ENABLE_PUSH_CREATED = false;

    /**
     * Disable Update Of Existing Local Objects when Modified Remotly
     *
     * {@inheritdoc}
     */
    protected static $ENABLE_PUSH_UPDATED = false;

    /**
     * Disable Delete Of Existing Local Objects when Deleted Remotly
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
    protected $postType = "shop_order";

    /**
     * {@inheritdoc}
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        $data = array();
        $stats = get_page_statuses();

        //====================================================================//
        // Load Dta From DataBase
        $rawData = get_posts(array(
            'post_type' => $this->postType,
            'post_status' => array_keys(wc_get_order_statuses()),
            'numberposts' => (!empty($params["max"])        ? $params["max"] : 10),
            'offset' => (!empty($params["offset"])     ? $params["offset"] : 0),
            'orderby' => (!empty($params["sortfield"])  ? $params["sortfield"] : 'id'),
            'order' => (!empty($params["sortorder"])  ? $params["sortorder"] : 'ASC'),
            's' => (!empty($filter)  ? $filter : ''),
        ));

        //====================================================================//
        // Store Meta Total & Current values
        $data["meta"]["total"] = $this->countPostsByTypes(array($this->postType));
        $data["meta"]["current"] = count($rawData);

        //====================================================================//
        // For each result, read information and add to $data
        /** @var WP_Post $wcOrder */
        foreach ($rawData as $wcOrder) {
            //====================================================================//
            // Prepare Status Prefix
            $statusPrefix = PrivacyManager::isAnonymizedById($wcOrder->ID) ? "[A] " : "";
            //====================================================================//
            // Prepare List Data
            $data[] = array(
                "id" => $wcOrder->ID,
                "post_title" => $wcOrder->post_title,
                "post_name" => $wcOrder->post_name,
                "post_status" => (isset($stats[$wcOrder->post_status]) ? $stats[$wcOrder->post_status] : "...?"),
                "status" => $statusPrefix.$wcOrder->post_status,
                "total" => get_post_meta($wcOrder->ID, "_order_total", true),
                "reference" => "#".$wcOrder->ID
            );
        }

        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " ".count($rawData)." Orders Found.");

        return $data;
    }
}
