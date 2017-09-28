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

namespace Splash\Local\Objects\Users;

use Splash\Client\Splash      as Splash;
use Splash\Local\Notifier;

/**
 * Wordpress Users Hooks
 */
trait HooksTrait {

    static $UserClass    =   "\Splash\Local\Objects\ThirdParty";
    
    /**
    *   @abstract     Register Users Hooks
    */
    static public function registeHooks()   {

        add_action( 'user_register',    [ static::$UserClass , "Created"],  10, 1);                
        add_action( 'profile_update',   [ static::$UserClass , "Updated"],  10, 1);                
        add_action( 'deleted_user',     [ static::$UserClass , "Deleted"],  10, 1);       
                
    }    
    
    static public function Created( $Id ) {
        //====================================================================//
        // Stack Trace
        Splash::Log()->Trace(__CLASS__,__FUNCTION__ . "(" . $Id . ")");            
        //====================================================================//
        // Do Commit
        Splash::Commit("ThirdParty", $Id, SPL_A_CREATE, "Wordpress", "User Created");
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();           
    }

    static public function Updated( $Id ) {
        //====================================================================//
        // Stack Trace
         //====================================================================//
        // Prevent Repeated Commit if Needed
        if ( Splash::Object("ThirdParty")->isLocked() ) {
            return;
        }       Splash::Log()->Trace(__CLASS__,__FUNCTION__ . "(" . $Id . ")");    
        //====================================================================//
        // Do Commit
        Splash::Commit("ThirdParty", $Id, SPL_A_UPDATE, "Wordpress", "User Updated");
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();   
    }
    
    static public function Deleted( $Id ) {
        //====================================================================//
        // Stack Trace
        Splash::Log()->Trace(__CLASS__,__FUNCTION__ . "(" . $Id . ")");
        //====================================================================//
        // Do Commit
        Splash::Commit("ThirdParty", $Id, SPL_A_DELETE, "Wordpress", "User Deleted");
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();           
    }    
    
}
