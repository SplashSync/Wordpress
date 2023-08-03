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

use Splash\Client\Splash;
use Splash\Models\Objects\Order\Status as OrderStatus;

class OrderStatusManager
{
    /**
     * List of Known Statuses
     *
     * @var array<string, string[]>
     */
    const KNOWN_STATUSES = array(
        OrderStatus::DRAFT => array("pending", "checkout-draft"),
        OrderStatus::PAYMENT_DUE => array("on-hold"),
        OrderStatus::PROCESSING => array("processing"),
        OrderStatus::PROCESSED => array("awaiting-shipment"),
        OrderStatus::OUT_OF_STOCK => array("awaiting-shipment"),
        OrderStatus::TO_SHIP => array("awaiting-shipment", "lpc_ready_to_ship"),
        OrderStatus::IN_TRANSIT => array("shipped", "lpc_transit", "lpc_partial_exp"),
        OrderStatus::PICKUP => array("shipped"),
        OrderStatus::PROBLEM => array("shipped", "lpc_anomaly"),
        OrderStatus::DELIVERED => array("completed", "lpc_delivered"),
        OrderStatus::CANCELED => array("cancelled", "refunded", "failed", "trash"),
    );

    /**
     * Wp Filter used to Prepend Splash Known Statuses
     */
    const STATUSES_FILTER = "splash_prepend_order_statuses";

    /**
     * Encode WC Order Status Choices
     *
     * @return array<string, string>
     */
    public static function getOrderStatusChoices(): array
    {
        $choices = array();
        foreach (self::getAllFiltered() as $splashStatus => $wcStatuses) {
            $first = array_shift($wcStatuses);
            if ($first) {
                $choices[$splashStatus] = wc_get_order_status_name($first);
            }
        }

        return $choices;
    }

    /**
     * Encode WC Order Status to Splash Standard Order Status
     *
     * @return string
     */
    public static function encode(string $status): ?string
    {
        foreach (self::getAllFiltered() as $splashStatus => $wcStatuses) {
            if (in_array($status, $wcStatuses, true)) {
                return $splashStatus;
            }
        }

        return null;
    }

    /**
     * Encode Splash Standard Order Status to WC Order Status
     *
     * @return string
     */
    public static function decode(string $status): ?string
    {
        $possibleStatus = self::getAllFiltered()[$status] ?? array();
        if (is_string($first = array_shift($possibleStatus))) {
            return $first;
        }

        return null;
    }

    /**
     * Get List of All Known Order Status filtered by Existing Statuses
     *
     * @return array<string, string[]>
     */
    private static function getAllFiltered(): array
    {
        static $filteredStatuses;

        if (!isset($filteredStatuses)) {
            $filteredStatuses = array();
            //====================================================================//
            // Get List of All Available Statuses
            $rawWcStatuses = wc_get_order_statuses();
            //====================================================================//
            // Remove All wc- Prefixes
            $allWcStatus = array_map(function ($status) {
                return str_replace("wc-", "", $status);
            }, array_keys($rawWcStatuses));
            //====================================================================//
            // Load List of Splash Known Statuses with Wp Filter
            /** @var array<string, string[]> $knownStatuses */
            $knownStatuses = apply_filters(self::STATUSES_FILTER, self::KNOWN_STATUSES);
            //====================================================================//
            // Filter List of Splash Known Statuses
            foreach ($knownStatuses as $splash => $wcStatuses) {
                $filtered = array_intersect($wcStatuses, $allWcStatus);
                if (!empty($filtered)) {
                    $filteredStatuses[$splash] = $filtered;
                }
            }
        }

        return $filteredStatuses;
    }
}
