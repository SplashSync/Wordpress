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

use Splash\Models\Objects\Invoice\Status as InvoiceStatus;

class InvoiceStatusManager
{
    /**
     * List of Known Statuses
     *
     * @var array<string, string[]>
     */
    const KNOWN_STATUSES = array(
        InvoiceStatus::DRAFT => array("pending", "checkout-draft"),
        InvoiceStatus::PAYMENT_DUE => array("on-hold"),
        InvoiceStatus::COMPLETE => array(
            "completed", "processing",
            "awaiting-shipment", "shipped",
            "lpc_ready_to_ship", "lpc_transit", "lpc_partial_exp", "lpc_anomaly", "lpc_delivered"
        ),
        InvoiceStatus::CANCELED => array(
            "cancelled", "refunded", "failed", "trash",
        ),
    );

    /**
     * Wp Filter used to Prepend Splash Known Statuses
     */
    const STATUSES_FILTER = "splash_prepend_invoices_statuses";

    /**
     * Encode WC Order Status to Splash Standard Invoice Status
     *
     * @param string $status
     *
     * @return null|string
     */
    public static function encode(string $status): ?string
    {
        static $knownStatuses;

        //====================================================================//
        // Load List of Splash Known Statuses with Wp Filter
        if (!isset($knownStatuses)) {
            /** @var array<string, string[]> $knownStatuses */
            $knownStatuses = apply_filters(self::STATUSES_FILTER, self::KNOWN_STATUSES);
        }

        foreach ($knownStatuses as $splash => $wcStatuses) {
            if (in_array($status, $wcStatuses, true)) {
                return $splash;
            }
        }

        return null;
    }
}
