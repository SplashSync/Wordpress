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

use Splash\Client\Splash      as Splash;

/**
 * Wordpress Users Hooks
 */
trait HooksTrait {

    static $OrderClass    =   "\Splash\Local\Objects\Order";
    
    /**
    *   @abstract     Register Users Hooks
    */
    static public function registeHooks()   {

        add_action( 'woocommerce_before_order_object_save',    [ static::$OrderClass , "Updated"],  10, 1);                
                
    }    

    static public function Updated( $Order ) {
        //====================================================================//
        // Stack Trace
         //====================================================================//
        // Prevent Repeated Commit if Needed
        if ( Splash::Object("Order")->isLocked() ) {
            return;
        }       Splash::Log()->Trace(__CLASS__,__FUNCTION__ . "(" . $Order->id . ")");    
        //====================================================================//
        // Do Commit
        Splash::Commit("Order", $Order->id, SPL_A_UPDATE, "Wordpress", "Wc Order Updated");
        Splash::Commit("Invoice", $Order->id, SPL_A_UPDATE, "Wordpress", "Wc Invoice Updated");
    }
        
}
