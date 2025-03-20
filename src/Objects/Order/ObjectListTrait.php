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

use Splash\Core\SplashCore as Splash;
use Splash\Local\Core as Managers;
use WC_Order;

trait ObjectListTrait
{
    /**
     * {@inheritdoc}
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        $data = array();
        //====================================================================//
        // Load Dta From DataBase
        $rawData = wc_get_orders(array(
            'type' => 'shop_order',
            'post_status' => array_keys(wc_get_order_statuses()),
            'numberposts' => (!empty($params["max"])        ? $params["max"] : 10),
            'offset' => (!empty($params["offset"])     ? $params["offset"] : 0),
            'orderby' => (!empty($params["sortfield"])  ? $params["sortfield"] : 'id'),
            'order' => (!empty($params["sortorder"])  ? $params["sortorder"] : 'ASC'),
            's' => (!empty($filter)  ? $filter : ''),
        ));
        if (!is_array($rawData)) {
            $rawData = array();
        }

        //====================================================================//
        // Store Meta Total & Current values
        $data["meta"]["total"] = $this->countOrdersByStatus();
        $data["meta"]["current"] = count($rawData);

        //====================================================================//
        // For each result, read information and add to $data
        /** @var WC_Order $wcOrder */
        foreach ($rawData as $wcOrder) {
            //====================================================================//
            // Prepare List Data
            $data[] = $this->toListOrder($wcOrder);
        }

        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " ".count($rawData)." Orders Found.");

        return $data;
    }

    /**
     * Count Number of Orders By Status
     *
     * @param null|string[] $statuses
     */
    private function countOrdersByStatus(array $statuses = null): int
    {
        $total = 0;
        //====================================================================//
        // All Order Status
        $statuses ??= array_keys(wc_get_order_statuses());
        //====================================================================//
        // Walk on Order Status
        foreach ($statuses as $status) {
            //====================================================================//
            // Get Order Counts by Status
            $total += wc_orders_count($status);
        }

        return $total;
    }

    /**
     * Convert Order to Object Line Array
     */
    private function toListOrder(WC_Order $wcOrder): array
    {
        //====================================================================//
        // Prepare Status Prefix
        $statusPrefix = Managers\PrivacyManager::isAnonymizeById($wcOrder->get_id()) ? "[A] " : "";
        $orderStatus = str_replace("wc-", "", $wcOrder->get_status());
        $orderDate = $wcOrder->get_date_created();

        //====================================================================//
        // Prepare List Data
        return array(
            "id" => $wcOrder->get_id(),
            "reference" => "#".$wcOrder->get_order_number(),
            "_datetime_created" => $orderDate ? $orderDate->format(SPL_T_DATETIMECAST) : null,
            "status" => $statusPrefix.(Managers\OrderStatusManager::encode($orderStatus) ?? $orderStatus),
            "invoice_status" => $statusPrefix.(Managers\InvoiceStatusManager::encode($orderStatus) ?? $orderStatus),
            "total" => $wcOrder->get_total(),
        );
    }
}
