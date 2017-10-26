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

use Splash\Client\Splash;

/**
 * @abstract    WooCommerce Customers Short Address Data Access
 */
trait AddressTrait {
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Address Fields using FieldFactory
    */
    private function buildAddressFields()   {

        /**
         * Check if WooCommerce is active
         **/
        if ( !Splash::Local()->hasWooCommerce() ) {
            return;
        }   
        
        //====================================================================//
        // Addess
        $this->FieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("address_1")
                ->Name(__("Address line 1"))
                ->MicroData("http://schema.org/PostalAddress","streetAddress")
                ->ReadOnly();

        //====================================================================//
        // Zip Code
        $this->FieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("postcode")
                ->Name( __("Postcode / ZIP"))
                ->MicroData("http://schema.org/PostalAddress","postalCode")
                ->ReadOnly();
        
        //====================================================================//
        // City Name
        $this->FieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("city")
                ->Name(__("City"))
                ->MicroData("http://schema.org/PostalAddress","addressLocality")
                ->ReadOnly();
        
        //====================================================================//
        // Country ISO Code
        $this->FieldsFactory()->Create(SPL_T_COUNTRY)
                ->Identifier("country")
                ->Name(__("Country"))
                ->MicroData("http://schema.org/PostalAddress","addressCountry")
                ->ReadOnly();

        //====================================================================//
        // State code
        $this->FieldsFactory()->Create(SPL_T_STATE)
                ->Identifier("state")
                ->Name(__("State / County"))
                ->MicroData("http://schema.org/PostalAddress","addressRegion")
                ->ReadOnly();

        //====================================================================//
        // Phone Pro
        $this->FieldsFactory()->Create(SPL_T_PHONE)
                ->Identifier("phone")
                ->Name(__("Phone"))
                ->MicroData("http://schema.org/Person","telephone")
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
    private function getAddressFields($Key,$FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName)
        {
            case 'address_1':
            case 'postcode':
            case 'city':
            case 'country':
            case 'state':
            case 'phone':
            case 'email':
                $this->Out[$FieldName] = get_user_meta( $this->Object->ID, "billing_" . $FieldName, True );
                break;            
            
            default:
                return;
        }
        unset($this->In[$Key]);
    }
        
}
