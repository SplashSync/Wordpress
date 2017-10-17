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

namespace Splash\Local\Objects\Address;

/**
 * @abstract    Wordpress Users Address User Link Access
 */
trait UserTrait {
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Address User Link Fields using FieldFactory
    */
    private function buildUserFields()   {

        //====================================================================//
        // Customer
        $this->FieldsFactory()->Create(self::Objects()->Encode( "ThirdParty" , SPL_T_ID))
                ->Identifier("user")
                ->Name(__("Customer"))
                ->MicroData("http://schema.org/Organization","ID")
                ->ReadOnly();  
        
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
     *  @return         none
     */
    private function getUserFields($Key,$FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName)
        {
            case 'user':
                $this->Out[$FieldName] = self::Objects()->Encode( "ThirdParty" , $this->Object->ID);
                break;            
            
            default:
                return;
        }
        
        unset($this->In[$Key]);
    }
    
}
