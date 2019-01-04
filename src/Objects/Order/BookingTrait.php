<?php
/*
 * Copyright (C) 2017   Splash Sync       <contact@splashsync.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

namespace Splash\Local\Objects\Order;

use Splash\Core\SplashCore      as Splash;

use WC_Booking;
use WC_Booking_Data_Store;

/**
 * @abstract    WooCommerce Bookings Order Data Access
 */
trait BookingTrait
{
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Fields using FieldFactory
    */
    private function buildBookingFields()
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (!Splash::local()->hasWooCommerceBooking()) {
            return;
        }

        //====================================================================//
        // Delivry Estimated Date
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("booking_details")
                ->Name(__("Booking Details"))
                ->Description(__("Booking Details as Simple raw String"))
                ->isReadOnly();
                ;
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//
    
    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return       void
     */
    private function getBookingFields($Key, $FieldName)
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (!Splash::local()->hasWooCommerceBooking()) {
            return;
        }        
        
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            case 'booking_details':
                //====================================================================//
                // Load All Booking Objects Attached to this Order
                $Bookings   =   WC_Booking_Data_Store::get_booking_ids_from_order_id($this->Object->ID);
                //====================================================================//
                // Build Booking Details String
                $BookingStr =   null;
                foreach ($Bookings as $BookingId) {
                    $BookingStr .= self::getBookingDetailsStr($BookingId);
                }
                $this->Out[$FieldName] = $BookingStr;
                break;
            
            default:
                return;
        }
        
        unset($this->In[$Key]);
    }
    
    private static function getBookingDetailsStr($BookingId)
    {
        //====================================================================//
        // Load Booking Object
        $Booking    =   new WC_Booking($BookingId);
        if (!$Booking) {
            return null;
        }
        //====================================================================//
        // Create Booking Infos String
        $Result = "Booking " . $BookingId;
        $Result .= " from " . $Booking->get_start_date(SPL_T_DATETIMECAST) . " to " . $Booking->get_end_date(SPL_T_DATETIMECAST);
        $Result .= "</br>";
        //====================================================================//
        // Return String
        return $Result;
    }    

}
