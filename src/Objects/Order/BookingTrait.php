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

use Splash\Local\Local;
use WC_Booking;
use WC_Booking_Data_Store;

/**
 * WooCommerce Bookings Order Data Access
 */
trait BookingTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    private function buildBookingFields(): void
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (!Local::hasWooCommerceBooking()) {
            return;
        }

        //====================================================================//
        // Delivery Estimated Date
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("booking_details")
            ->name(__("Booking Details"))
            ->description(__("Booking Details as Simple raw String"))
            ->isReadOnly()
        ;
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getBookingFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (!Local::hasWooCommerceBooking()) {
            return;
        }

        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'booking_details':
                //====================================================================//
                // Load All Booking Objects Attached to this Order
                /** @phpstan-ignore-next-line */
                $bookings = WC_Booking_Data_Store::get_booking_ids_from_order_id($this->object->ID);
                //====================================================================//
                // Build Booking Details String
                $bookingStr = null;
                foreach ($bookings as $bookingId) {
                    $bookingStr .= self::getBookingDetailsStr($bookingId);
                }
                $this->out[$fieldName] = $bookingStr;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Get Booking Details as String
     *
     * @param int $bookingId
     *
     * @return null|string
     */
    private static function getBookingDetailsStr(int $bookingId): ?string
    {
        //====================================================================//
        // Load Booking Object
        /** @phpstan-ignore-next-line */
        $booking = new WC_Booking($bookingId);
        /** @phpstan-ignore-next-line */
        if (empty($booking->get_id())) {
            return null;
        }
        //====================================================================//
        // Create Booking Infos String
        $result = "Booking ".$bookingId;
        /** @phpstan-ignore-next-line */
        $result .= " from ".$booking->get_start_date(SPL_T_DATETIMECAST);
        /** @phpstan-ignore-next-line */
        $result .= " to ".$booking->get_end_date(SPL_T_DATETIMECAST);
        $result .= "</br>";
        //====================================================================//
        // Return String
        return $result;
    }
}
