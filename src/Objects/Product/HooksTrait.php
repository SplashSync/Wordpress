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

namespace Splash\Local\Objects\Product;

use Splash\Client\Splash      as Splash;

/**
 * @abstract    Wordpress Taximony Data Access
 */
trait HooksTrait {

    static $PostClass    =   "\Splash\Local\Objects\Product";
    
    /**
    *   @abstract     Register Product Hooks
    */
    static public function registeHooks()   {
        add_action( 'woocommerce_new_product_variation',        [ static::$PostClass , "Created"],  10, 1);                      
        add_action( 'woocommerce_update_product_variation',     [ static::$PostClass , "Updated"],  10, 1);                      
    }    

    static public function Updated( $Id ) {
        //====================================================================//
        // Stack Trace
        Splash::Log()->Trace(__CLASS__,__FUNCTION__ . "(" . $Id . ")");            
        //====================================================================//
        // Prepare Commit Parameters
        $ObjectType     =   "Product";
        $Comment        =   $ObjectType .  " Updated on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if ( Splash::Object($ObjectType)->isLocked() ) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::Commit($ObjectType, $Id, SPL_A_UPDATE, "Wordpress", $Comment);
    }
    
    
    static public function Created( $Id ) {
        //====================================================================//
        // Stack Trace
        Splash::Log()->Trace(__CLASS__,__FUNCTION__ . "(" . $Id . ")");            
        //====================================================================//
        // Prepare Commit Parameters
        $ObjectType     =   "Product";
        $Comment        =   $ObjectType .  " Created on Wordpress";
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if ( Splash::Object($ObjectType)->isLocked() ) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::Commit($ObjectType, $Id, SPL_A_CREATE, "Wordpress", $Comment);
    }
    
}
